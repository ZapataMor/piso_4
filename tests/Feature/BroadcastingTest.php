<?php

namespace Tests\Feature;

use App\Enums\BillModality;
use App\Events\BillRequested;
use App\Events\OrderItemStatusChanged;
use App\Events\OrderPlaced;
use App\Events\PaymentConfirmed;
use App\Events\WaiterCalled;
use App\Models\Category;
use App\Models\Mesa;
use App\Models\Product;
use App\Models\Role;
use App\Models\SessionParticipant;
use App\Models\User;
use App\Services\BillService;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\SessionService;
use App\Services\WaiterService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BroadcastingTest extends TestCase
{
    use RefreshDatabase;

    private function participant(): SessionParticipant
    {
        $mesa = Mesa::create(['numero' => 5, 'qr_token' => 'tok-5', 'estado' => 'disponible']);
        $session = app(SessionService::class)->openOrGetActiveSession($mesa);

        return app(SessionService::class)->addParticipant($session, 'Juan');
    }

    private function product(string $tipo = 'cocina'): Product
    {
        $cat = Category::firstOrCreate(['slug' => 'c-'.$tipo], ['name' => 'C', 'display_order' => 1]);

        return Product::create(['category_id' => $cat->id, 'name' => 'P'.uniqid(), 'price' => 10000, 'tipo_preparacion' => $tipo, 'is_available' => true]);
    }

    public function test_submitting_an_order_broadcasts_order_placed(): void
    {
        Event::fake([OrderPlaced::class]);
        $p = $this->participant();
        app(CartService::class)->add($p, $this->product('cocina'), 1);

        app(OrderService::class)->submitOrder($p);

        Event::assertDispatched(OrderPlaced::class);
    }

    public function test_item_transition_broadcasts_status_change(): void
    {
        Event::fake([OrderItemStatusChanged::class]);
        $p = $this->participant();
        app(CartService::class)->add($p, $this->product('cocina'), 1);
        $order = app(OrderService::class)->submitOrder($p);

        app(OrderService::class)->markItemReady($order->items->first());

        Event::assertDispatched(OrderItemStatusChanged::class);
    }

    public function test_waiter_call_broadcasts(): void
    {
        Event::fake([WaiterCalled::class]);
        $p = $this->participant();

        app(WaiterService::class)->call($p->session, $p);

        Event::assertDispatched(WaiterCalled::class);
    }

    public function test_bill_request_broadcasts_once(): void
    {
        Event::fake([BillRequested::class]);
        $p = $this->participant();
        app(CartService::class)->add($p, $this->product('cocina'), 1);
        app(OrderService::class)->submitOrder($p);

        $bills = app(BillService::class);
        $bills->requestBill($p->session, $p);
        $bills->requestBill($p->session, $p); // idempotente: no vuelve a emitir

        Event::assertDispatchedTimes(BillRequested::class, 1);
    }

    public function test_payment_confirmation_broadcasts(): void
    {
        Event::fake([PaymentConfirmed::class]);
        $p = $this->participant();
        app(CartService::class)->add($p, $this->product('cocina'), 1);
        app(OrderService::class)->submitOrder($p);
        $bill = app(BillService::class)->requestBill($p->session, $p);
        $payment = app(PaymentService::class)->generate($bill, BillModality::Unica)->first();

        app(PaymentService::class)->confirm($payment, User::factory()->create());

        Event::assertDispatched(PaymentConfirmed::class);
    }

    public function test_station_channels_authorize_by_role(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb' => [
                'driver' => 'reverb', 'key' => 'test', 'secret' => 'test', 'app_id' => 'test',
                'options' => ['host' => 'localhost', 'port' => 8080, 'scheme' => 'http', 'useTLS' => false],
            ],
        ]);

        // Re-registra los canales sobre el broadcaster reverb recién activado.
        require base_path('routes/channels.php');

        $cocina = User::factory()->create(['role_id' => Role::where('slug', 'cocina')->value('id')]);
        $bar = User::factory()->create(['role_id' => Role::where('slug', 'bar')->value('id')]);

        $this->actingAs($cocina)
            ->post('/broadcasting/auth', ['channel_name' => 'private-kitchen', 'socket_id' => '1234.5678'])
            ->assertOk();

        $this->actingAs($bar)
            ->post('/broadcasting/auth', ['channel_name' => 'private-kitchen', 'socket_id' => '1234.5678'])
            ->assertForbidden();
    }
}
