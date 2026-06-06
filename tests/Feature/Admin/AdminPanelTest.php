<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
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
}
