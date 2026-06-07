<?php

namespace Tests\Feature\Admin;

use App\Models\Mesa;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MesaManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function userWithRole(string $slug): User
    {
        return User::factory()->create([
            'role_id' => Role::where('slug', $slug)->value('id'),
        ]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('admin.mesas.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_view_the_mesas_index(): void
    {
        $this->actingAs($this->userWithRole('admin'));

        $this->get(route('admin.mesas.index'))
            ->assertOk()
            ->assertSee('Mesas y QR');
    }

    public function test_non_admin_roles_are_forbidden(): void
    {
        $this->actingAs($this->userWithRole('cocina'));

        $this->get(route('admin.mesas.index'))->assertForbidden();
    }

    public function test_admin_can_create_a_mesa_and_a_token_is_generated(): void
    {
        $this->actingAs($this->userWithRole('admin'));

        $this->post(route('admin.mesas.store'), [
            'numero' => 20,
            'nombre' => 'Terraza',
            'capacidad' => 6,
            'estado' => 'disponible',
        ])->assertRedirect();

        $mesa = Mesa::where('numero', 20)->first();
        $this->assertNotNull($mesa);
        $this->assertNotEmpty($mesa->qr_token);
        $this->assertSame('Terraza', $mesa->nombre);
    }

    public function test_duplicate_numero_is_rejected(): void
    {
        $this->actingAs($this->userWithRole('admin'));
        Mesa::create(['numero' => 5, 'qr_token' => 'tok-5', 'estado' => 'disponible']);

        $this->post(route('admin.mesas.store'), [
            'numero' => 5,
            'estado' => 'disponible',
        ])->assertSessionHasErrors('numero');
    }

    public function test_edit_page_renders_the_qr(): void
    {
        $this->actingAs($this->userWithRole('admin'));
        $mesa = Mesa::create(['numero' => 7, 'qr_token' => 'tok-7', 'estado' => 'disponible']);

        $this->get(route('admin.mesas.edit', $mesa))
            ->assertOk()
            ->assertSee('Código QR')
            ->assertSee('<svg', false);
    }

    public function test_admin_can_download_the_qr_svg(): void
    {
        $this->actingAs($this->userWithRole('admin'));
        $mesa = Mesa::create(['numero' => 8, 'qr_token' => 'tok-8', 'estado' => 'disponible']);

        $this->get(route('admin.mesas.qr', $mesa))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml');
    }

    public function test_admin_can_regenerate_the_token(): void
    {
        $this->actingAs($this->userWithRole('admin'));
        $mesa = Mesa::create(['numero' => 9, 'qr_token' => 'old-token', 'estado' => 'disponible']);

        $this->post(route('admin.mesas.regenerate', $mesa))->assertRedirect();

        $this->assertNotSame('old-token', $mesa->fresh()->qr_token);
    }

    public function test_admin_can_soft_delete_a_mesa(): void
    {
        $this->actingAs($this->userWithRole('admin'));
        $mesa = Mesa::create(['numero' => 10, 'qr_token' => 'tok-10', 'estado' => 'disponible']);

        $this->delete(route('admin.mesas.destroy', $mesa))->assertRedirect(route('admin.mesas.index'));

        $this->assertSoftDeleted('mesas', ['id' => $mesa->id]);
    }

    public function test_index_refreshes_table_status_on_session_event(): void
    {
        $this->actingAs($this->userWithRole('admin'));
        $mesa = Mesa::create(['numero' => 11, 'qr_token' => 'tok-11', 'estado' => 'disponible']);

        $component = Livewire::test('pages::admin.mesas')->assertSee('Disponible');

        // Simula la apertura de sesión (escaneo de QR): la mesa pasa a ocupada
        // y el broadcast `session.changed` llega al componente del admin.
        $mesa->update(['estado' => 'ocupada']);
        $component->dispatch('echo-private:waiters,.session.changed')
            ->assertSee('Ocupada');
    }
}
