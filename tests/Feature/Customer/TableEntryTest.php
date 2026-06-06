<?php

namespace Tests\Feature\Customer;

use App\Models\Mesa;
use App\Models\RestaurantSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableEntryTest extends TestCase
{
    use RefreshDatabase;

    private function mesa(string $estado = 'disponible'): Mesa
    {
        return Mesa::create(['numero' => 1, 'qr_token' => 'tok-1', 'estado' => $estado]);
    }

    public function test_scanning_qr_opens_a_session_and_shows_name_form(): void
    {
        $mesa = $this->mesa();

        $this->get(route('mesa.show', $mesa))
            ->assertOk()
            ->assertSee('Bienvenido a Piso Cuatro');

        $this->assertDatabaseHas('restaurant_sessions', ['mesa_id' => $mesa->id, 'estado' => 'activa']);
        $this->assertSame('ocupada', $mesa->fresh()->estado->value);
    }

    public function test_only_one_active_session_per_mesa(): void
    {
        $mesa = $this->mesa();

        $this->get(route('mesa.show', $mesa));
        $this->get(route('mesa.show', $mesa));

        $this->assertSame(1, RestaurantSession::where('mesa_id', $mesa->id)->where('estado', 'activa')->count());
    }

    public function test_joining_creates_participant_and_redirects_to_menu(): void
    {
        $mesa = $this->mesa();
        $this->get(route('mesa.show', $mesa));

        $this->post(route('mesa.join', $mesa), ['nombre' => 'Juan'])
            ->assertRedirect(route('mesa.menu', $mesa));

        $this->assertDatabaseHas('session_participants', ['nombre' => 'Juan', 'is_host' => true]);
    }

    public function test_name_is_required(): void
    {
        $mesa = $this->mesa();
        $this->get(route('mesa.show', $mesa));

        $this->post(route('mesa.join', $mesa), ['nombre' => ''])
            ->assertSessionHasErrors('nombre');
    }

    public function test_menu_requires_a_participant(): void
    {
        $mesa = $this->mesa();
        $this->get(route('mesa.show', $mesa));

        $this->get(route('mesa.menu', $mesa))->assertRedirect(route('mesa.show', $mesa));
    }

    public function test_menu_renders_for_a_participant(): void
    {
        $mesa = $this->mesa();
        $this->get(route('mesa.show', $mesa));
        $join = $this->post(route('mesa.join', $mesa), ['nombre' => 'Juan']);

        $join->assertCookie('participant_token');
        $token = $join->getCookie('participant_token', false)->getValue();

        $session = $mesa->fresh()->activeSession;
        $this->assertNotNull($session, 'active session missing');
        $this->assertDatabaseHas('session_participants', [
            'token' => $token,
            'restaurant_session_id' => $session->id,
        ]);

        $this->withUnencryptedCookie('participant_token', $token)
            ->get(route('mesa.menu', $mesa))
            ->assertOk()
            ->assertSee('Mesa '.$mesa->numero);
    }

    public function test_fuera_de_servicio_is_blocked(): void
    {
        $mesa = $this->mesa('fuera_de_servicio');

        $this->get(route('mesa.show', $mesa))
            ->assertStatus(503)
            ->assertSee('fuera de servicio');
    }

    public function test_a_participant_cannot_access_another_mesas_pages(): void
    {
        $mesaA = Mesa::create(['numero' => 1, 'qr_token' => 'tok-a', 'estado' => 'disponible']);
        $mesaB = Mesa::create(['numero' => 2, 'qr_token' => 'tok-b', 'estado' => 'disponible']);

        $this->get(route('mesa.show', $mesaA));
        $join = $this->post(route('mesa.join', $mesaA), ['nombre' => 'Juan']);
        $token = $join->getCookie('participant_token', false)->getValue();

        $this->get(route('mesa.show', $mesaB)); // abre la sesión de B

        // El token de A no es válido para la sesión de B -> reenvío al ingreso.
        $this->withUnencryptedCookie('participant_token', $token)
            ->get(route('mesa.menu', $mesaB))
            ->assertRedirect(route('mesa.show', $mesaB));
    }

    public function test_join_is_rate_limited(): void
    {
        $mesa = $this->mesa();
        $this->get(route('mesa.show', $mesa));

        for ($i = 0; $i < 15; $i++) {
            $this->post(route('mesa.join', $mesa), ['nombre' => 'P'.$i])->assertRedirect();
        }

        $this->post(route('mesa.join', $mesa), ['nombre' => 'P16'])->assertStatus(429);
    }
}
