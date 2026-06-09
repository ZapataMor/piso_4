<?php

namespace Tests\Feature\Customer;

use App\Enums\OrderStatus;
use App\Enums\PreparationType;
use App\Models\Category;
use App\Models\Mesa;
use App\Models\Product;
use App\Models\SessionParticipant;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\SessionService;
use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    private function participant(): SessionParticipant
    {
        $mesa = Mesa::create(['numero' => 1, 'qr_token' => 'tok-1', 'estado' => 'disponible']);
        $session = app(SessionService::class)->openOrGetActiveSession($mesa);

        return app(SessionService::class)->addParticipant($session, 'Juan');
    }

    private function product(string $tipo = 'cocina', int $price = 10000): Product
    {
        $category = Category::firstOrCreate(['slug' => 'cat-'.$tipo], ['name' => 'Cat '.$tipo, 'display_order' => 1]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Prod '.uniqid(),
            'price' => $price,
            'tipo_preparacion' => $tipo,
            'is_available' => true,
        ]);
    }

    public function test_cart_merges_same_product_and_notes(): void
    {
        $cart = app(CartService::class);
        $p = $this->participant();
        $product = $this->product();

        $cart->add($p, $product, 1);
        $cart->add($p, $product, 2);

        $this->assertCount(1, $p->cartItems()->get());
        $this->assertSame(3, $p->cartItems()->first()->quantity);
    }

    public function test_cart_keeps_separate_lines_for_different_notes(): void
    {
        $cart = app(CartService::class);
        $p = $this->participant();
        $product = $this->product();

        $cart->add($p, $product, 1, 'sin cebolla');
        $cart->add($p, $product, 1, 'extra queso');

        $this->assertCount(2, $p->cartItems()->get());
    }

    public function test_submit_order_creates_snapshots_and_clears_cart(): void
    {
        $cart = app(CartService::class);
        $orders = app(OrderService::class);
        $p = $this->participant();

        $cart->add($p, $this->product('cocina', 20000), 2);
        $cart->add($p, $this->product('bar', 5000), 1);

        $order = $orders->submitOrder($p);

        $this->assertSame(1, $order->numero);
        $this->assertSame(OrderStatus::Pendiente, $order->estado);
        $this->assertNotNull($order->placed_at);
        $this->assertCount(2, $order->items);
        $this->assertEquals(45000.0, (float) $order->fresh()->subtotal);
        $this->assertCount(0, $p->cartItems()->get(), 'cart should be empty after submit');

        $item = $order->items->firstWhere('tipo_preparacion', PreparationType::Cocina);
        $this->assertNotNull($item->product_name);
        $this->assertEquals(20000.0, (float) $item->unit_price);
    }

    public function test_submitting_an_empty_cart_throws(): void
    {
        $this->expectException(RuntimeException::class);
        app(OrderService::class)->submitOrder($this->participant());
    }

    public function test_order_state_machine_progresses_with_items(): void
    {
        $cart = app(CartService::class);
        $orders = app(OrderService::class);
        $p = $this->participant();

        $cart->add($p, $this->product('cocina'), 1);
        $cart->add($p, $this->product('bar'), 1);
        $order = $orders->submitOrder($p);
        [$a, $b] = $order->items->all();

        $orders->startItem($a);
        $this->assertSame(OrderStatus::EnPreparacion, $order->fresh()->estado);
        $this->assertNotNull($order->fresh()->started_at);

        $orders->markItemReady($a);
        $orders->markItemReady($b);
        $this->assertSame(OrderStatus::Listo, $order->fresh()->estado);
        $this->assertNotNull($order->fresh()->ready_at);

        $orders->deliverItem($a);
        $orders->deliverItem($b);
        $this->assertSame(OrderStatus::Entregado, $order->fresh()->estado);
        $this->assertNotNull($order->fresh()->delivered_at);
    }

    public function test_cancelling_all_items_cancels_the_order(): void
    {
        $cart = app(CartService::class);
        $orders = app(OrderService::class);
        $p = $this->participant();

        $cart->add($p, $this->product('cocina'), 1);
        $order = $orders->submitOrder($p);

        $orders->cancelItem($order->items->first());

        $this->assertSame(OrderStatus::Cancelado, $order->fresh()->estado);
    }

    public function test_menu_and_orders_pages_render_for_participant(): void
    {
        $this->seed(MenuSeeder::class);
        $mesa = Mesa::create(['numero' => 1, 'qr_token' => 'tok-1', 'estado' => 'disponible']);
        $this->get(route('mesa.show', $mesa));
        $join = $this->post(route('mesa.join', $mesa), ['nombre' => 'Juan']);
        $token = $join->getCookie('participant_token', false)->getValue();

        $this->withUnencryptedCookie('participant_token', $token)
            ->get(route('mesa.menu', $mesa))
            ->assertOk()
            ->assertSee('Entradas')
            ->assertSee('Cócteles')
            ->assertDontSee('Mojito');

        $cocteles = Category::where('slug', 'cocteles')->firstOrFail();

        Livewire::withCookie('participant_token', $token)
            ->test('pages::customer.menu', ['mesa' => $mesa])
            ->assertDontSee('Mojito')
            ->call('toggleCategory', $cocteles->id)
            ->assertSee('Mojito')
            ->call('toggleCategory', $cocteles->id)
            ->assertDontSee('Mojito')
            ->call('toggleCategory', $cocteles->id)
            ->assertSee('Mojito')
            ->call('toggleCategory', $cocteles->id)
            ->assertDontSee('Mojito');

        $participant = SessionParticipant::where('token', $token)->firstOrFail();
        app(CartService::class)->add($participant, Product::first(), 1);
        app(OrderService::class)->submitOrder($participant);

        $this->withUnencryptedCookie('participant_token', $token)
            ->get(route('mesa.orders', $mesa))
            ->assertOk()
            ->assertSee('Pedido #1');
    }

    public function test_orders_page_groups_table_orders_by_customer_with_subtotals_and_table_total(): void
    {
        $mesa = Mesa::create(['numero' => 3, 'qr_token' => 'tok-3', 'estado' => 'disponible']);
        $session = app(SessionService::class)->openOrGetActiveSession($mesa);
        $felipe = app(SessionService::class)->addParticipant($session, 'Felipe');
        $maria = app(SessionService::class)->addParticipant($session, 'Maria');

        app(CartService::class)->add($felipe, $this->product('cocina', 12000), 1);
        app(OrderService::class)->submitOrder($felipe);
        app(CartService::class)->add($maria, $this->product('cocina', 9000), 1);
        app(OrderService::class)->submitOrder($maria);
        app(CartService::class)->add($felipe, $this->product('bar', 8000), 1);
        app(OrderService::class)->submitOrder($felipe);

        $this->withUnencryptedCookie('participant_token', $felipe->token)
            ->get(route('mesa.orders', $mesa))
            ->assertOk()
            ->assertSee('Pedidos de la mesa')
            ->assertSee('Felipe')
            ->assertSee('2 pedidos')
            ->assertSee('Subtotal de Felipe')
            ->assertSee('$20.000')
            ->assertSee('Maria')
            ->assertSee('1 pedido')
            ->assertSee('Subtotal de Maria')
            ->assertSee('$9.000')
            ->assertSee('Total de la mesa')
            ->assertSee('$29.000');
    }
}
