<?php

namespace Tests\Feature\Staff;

use App\Enums\TableStatus;
use App\Enums\WaiterCallStatus;
use App\Models\Category;
use App\Models\Mesa;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\SessionParticipant;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\SessionService;
use App\Services\WaiterService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffBoardsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function userWithRole(string $slug): User
    {
        return User::factory()->create(['role_id' => Role::where('slug', $slug)->value('id')]);
    }

    /** @return array{0: Mesa, 1: SessionParticipant, 2: Order} */
    private function seedOrder(): array
    {
        $mesa = Mesa::create(['numero' => 3, 'qr_token' => 'tok-3', 'estado' => 'disponible']);
        $session = app(SessionService::class)->openOrGetActiveSession($mesa);
        $participant = app(SessionService::class)->addParticipant($session, 'Juan');

        $cat = Category::create(['slug' => 'c', 'name' => 'C', 'display_order' => 1]);
        $cocina = Product::create(['category_id' => $cat->id, 'name' => 'Bandeja Paisa', 'price' => 20000, 'tipo_preparacion' => 'cocina', 'is_available' => true]);
        $bar = Product::create(['category_id' => $cat->id, 'name' => 'Mojito', 'price' => 22000, 'tipo_preparacion' => 'bar', 'is_available' => true]);

        app(CartService::class)->add($participant, $cocina, 1);
        app(CartService::class)->add($participant, $bar, 1);
        $order = app(OrderService::class)->submitOrder($participant);

        return [$mesa, $participant, $order];
    }

    public function test_kitchen_board_shows_only_cocina_items(): void
    {
        $this->seedOrder();

        $this->actingAs($this->userWithRole('cocina'))
            ->get(route('kitchen.board'))
            ->assertOk()
            ->assertSee('Bandeja Paisa')
            ->assertDontSee('Mojito');
    }

    public function test_bar_board_shows_only_bar_items(): void
    {
        $this->seedOrder();

        $this->actingAs($this->userWithRole('bar'))
            ->get(route('bar.board'))
            ->assertOk()
            ->assertSee('Mojito')
            ->assertDontSee('Bandeja Paisa');
    }

    public function test_stations_are_isolated_by_role(): void
    {
        $cocina = $this->userWithRole('cocina');
        $bar = $this->userWithRole('bar');

        $this->actingAs($cocina)->get(route('bar.board'))->assertForbidden();
        $this->actingAs($bar)->get(route('kitchen.board'))->assertForbidden();
        $this->actingAs($cocina)->get(route('waiter.dashboard'))->assertForbidden();
    }

    public function test_admin_can_access_every_board(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->get(route('kitchen.board'))->assertOk();
        $this->actingAs($admin)->get(route('bar.board'))->assertOk();
        $this->actingAs($admin)->get(route('waiter.dashboard'))->assertOk();
    }

    public function test_waiter_board_renders_ready_items_and_active_tables(): void
    {
        [$mesa, $participant, $order] = $this->seedOrder();

        // Marca una línea como lista para que aparezca en "Productos listos".
        app(OrderService::class)->markItemReady($order->items->first());

        $this->actingAs($this->userWithRole('mesero'))
            ->get(route('waiter.dashboard'))
            ->assertOk()
            ->assertSee('Panel de Meseros')
            ->assertSee('Mesa '.$mesa->numero);
    }

    public function test_waiter_service_records_and_attends_calls(): void
    {
        [$mesa, $participant] = $this->seedOrder();
        $waiters = app(WaiterService::class);
        $staff = $this->userWithRole('mesero');

        $call = $waiters->call($participant->session, $participant);
        $this->assertSame(WaiterCallStatus::Pendiente, $call->estado);

        $waiters->attend($call, $staff);
        $this->assertSame(WaiterCallStatus::Atendido, $call->fresh()->estado);
        $this->assertSame($staff->id, $call->fresh()->attended_by);
    }

    public function test_closing_a_session_frees_the_mesa(): void
    {
        [$mesa, $participant] = $this->seedOrder();
        $this->assertSame(TableStatus::Ocupada, $mesa->fresh()->estado);

        app(SessionService::class)->closeSession($participant->session);

        $this->assertSame(TableStatus::Disponible, $mesa->fresh()->estado);
        $this->assertSame('cerrada', $participant->session->fresh()->estado->value);
    }
}
