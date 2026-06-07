<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Mesa;
use App\Models\Product;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\SessionService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolesAndPermissionsSeeder::class, SettingsSeeder::class]);
    }

    private function userWithRole(string $slug): User
    {
        return User::factory()->create(['role_id' => Role::where('slug', $slug)->value('id')]);
    }

    public static function adminRoutes(): array
    {
        return [
            ['admin.dashboard'], ['admin.products'], ['admin.categories'],
            ['admin.users'], ['admin.orders'], ['admin.statistics'],
            ['admin.settings'], ['admin.mesas.index'],
        ];
    }

    #[DataProvider('adminRoutes')]
    public function test_admin_can_open_every_admin_page(string $route): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->get(route($route))
            ->assertOk();
    }

    public function test_non_admin_is_forbidden_from_admin(): void
    {
        $this->actingAs($this->userWithRole('cocina'))->get(route('admin.products'))->assertForbidden();
        $this->actingAs($this->userWithRole('mesero'))->get(route('admin.users'))->assertForbidden();
    }

    public function test_livewire_components_reauthorize_on_every_request(): void
    {
        // El guard boot() corre en mount Y en cada update (hydrate), así que
        // un no-admin no puede operar el componente aunque obtuviera un snapshot.
        $this->actingAs($this->userWithRole('cocina'));

        $this->expectException(\Throwable::class);

        // El guard boot() aborta la mutación de un no-admin.
        Livewire::test('pages::admin.products')->call('create');
    }

    public function test_admin_can_create_a_product(): void
    {
        $this->actingAs($this->userWithRole('admin'));
        $category = Category::create(['slug' => 'entradas', 'name' => 'Entradas', 'display_order' => 1]);

        Livewire::test('pages::admin.products')
            ->call('create')
            ->set('name', 'Ceviche')
            ->set('category_id', $category->id)
            ->set('price', '28000')
            ->set('tipo_preparacion', 'cocina')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', ['name' => 'Ceviche', 'price' => 28000]);
    }

    public function test_admin_can_save_settings(): void
    {
        $this->actingAs($this->userWithRole('admin'));

        Livewire::test('pages::admin.settings')
            ->set('values.whatsapp_number', '573009998877')
            ->set('values.bank_account', '999-888777-66')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('573009998877', Setting::get('whatsapp_number'));
        $this->assertSame('999-888777-66', Setting::get('bank_account'));
    }

    public function test_product_availability_toggle(): void
    {
        $this->actingAs($this->userWithRole('admin'));
        $category = Category::create(['slug' => 'c', 'name' => 'C', 'display_order' => 1]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'X', 'price' => 1000, 'tipo_preparacion' => 'cocina', 'is_available' => true]);

        Livewire::test('pages::admin.products')->call('toggleAvailable', $product->id);

        $this->assertFalse($product->fresh()->is_available);
    }

    public function test_admin_orders_page_groups_orders_by_table(): void
    {
        $this->actingAs($this->userWithRole('admin'));

        $category = Category::create(['slug' => 'ordenes', 'name' => 'Ordenes', 'display_order' => 1]);
        $sushi = Product::create(['category_id' => $category->id, 'name' => 'Sushi', 'price' => 44000, 'tipo_preparacion' => 'cocina', 'is_available' => true]);
        $brochetas = Product::create(['category_id' => $category->id, 'name' => 'Brochetas de Salmón', 'price' => 38500, 'tipo_preparacion' => 'cocina', 'is_available' => true]);
        $parrilla = Product::create(['category_id' => $category->id, 'name' => 'Parrilla Piso 4', 'price' => 59500, 'tipo_preparacion' => 'cocina', 'is_available' => true]);

        $mesa3 = Mesa::create(['numero' => 3, 'qr_token' => 'tok-3', 'estado' => 'disponible']);
        $session3 = app(SessionService::class)->openOrGetActiveSession($mesa3);
        $felipe = app(SessionService::class)->addParticipant($session3, 'Felipe Mor');
        $carmen = app(SessionService::class)->addParticipant($session3, 'Carmen');

        app(CartService::class)->add($felipe, $sushi, 1);
        app(OrderService::class)->submitOrder($felipe);
        app(CartService::class)->add($felipe, $brochetas, 1);
        app(OrderService::class)->submitOrder($felipe);
        app(CartService::class)->add($carmen, $parrilla, 1);
        app(OrderService::class)->submitOrder($carmen);

        $this->get(route('admin.orders'))
            ->assertOk()
            ->assertSee('Mesa 3')
            ->assertSee('3 pedidos')
            ->assertSee('Clientes: Felipe Mor, Carmen')
            ->assertSee('Total mesa')
            ->assertSee('$142.000')
            ->assertSee('Pedido #1')
            ->assertSee('Pedido #2')
            ->assertSee('Pedido #3');
    }
}
