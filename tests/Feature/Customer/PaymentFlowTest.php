<?php

namespace Tests\Feature\Customer;

use App\Contracts\WhatsAppGateway;
use App\Enums\BillModality;
use App\Enums\BillStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Category;
use App\Models\Mesa;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RestaurantSession;
use App\Models\SessionParticipant;
use App\Models\User;
use App\Services\BillService;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\SessionService;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SettingsSeeder::class);
    }

    /** Juan pide 20.000, María pide 10.000 → total 30.000. */
    private function scenario(): array
    {
        $mesa = Mesa::create(['numero' => 4, 'qr_token' => 'tok-4', 'estado' => 'disponible']);
        $session = app(SessionService::class)->openOrGetActiveSession($mesa);
        $juan = app(SessionService::class)->addParticipant($session, 'Juan');
        $maria = app(SessionService::class)->addParticipant($session, 'María');

        $cat = Category::create(['slug' => 'c', 'name' => 'C', 'display_order' => 1]);
        $bandeja = Product::create(['category_id' => $cat->id, 'name' => 'Bandeja', 'price' => 20000, 'tipo_preparacion' => 'cocina', 'is_available' => true]);
        $jugo = Product::create(['category_id' => $cat->id, 'name' => 'Jugo', 'price' => 10000, 'tipo_preparacion' => 'bar', 'is_available' => true]);

        app(CartService::class)->add($juan, $bandeja, 1);
        app(OrderService::class)->submitOrder($juan);
        app(CartService::class)->add($maria, $jugo, 1);
        app(OrderService::class)->submitOrder($maria);

        return [$session, $juan, $maria];
    }

    public function test_request_bill_computes_total(): void
    {
        [$session] = $this->scenario();

        $bill = app(BillService::class)->requestBill($session);

        $this->assertSame(BillStatus::Solicitada, $bill->estado);
        $this->assertEquals(30000.0, (float) $bill->total);
    }

    public function test_unica_creates_one_payment_for_the_total(): void
    {
        [$session] = $this->scenario();
        $bill = app(BillService::class)->requestBill($session);

        $payments = app(PaymentService::class)->generate($bill, BillModality::Unica);

        $this->assertCount(1, $payments);
        $this->assertEquals(30000.0, (float) $payments->first()->monto);
        $this->assertSame(BillStatus::EnPago, $bill->fresh()->estado);
    }

    public function test_automatica_splits_by_participant(): void
    {
        [$session, $juan, $maria] = $this->scenario();
        $bill = app(BillService::class)->requestBill($session);

        $payments = app(PaymentService::class)->generate($bill, BillModality::Automatica);

        $this->assertCount(2, $payments);
        $this->assertEquals(20000.0, (float) $payments->firstWhere('session_participant_id', $juan->id)->monto);
        $this->assertEquals(10000.0, (float) $payments->firstWhere('session_participant_id', $maria->id)->monto);
    }

    public function test_personalizada_assigns_items_to_a_payer(): void
    {
        [$session, $juan] = $this->scenario();
        $bill = app(BillService::class)->requestBill($session);

        // Juan paga TODO: asignamos ambas líneas a Juan.
        $assignments = OrderItem::query()->pluck('id')->mapWithKeys(fn ($id) => [$id => $juan->id])->all();

        $payments = app(PaymentService::class)->generate($bill, BillModality::Personalizada, $assignments);

        $this->assertCount(1, $payments);
        $this->assertEquals(30000.0, (float) $payments->first()->monto);
        $this->assertCount(2, $payments->first()->orderItems);
    }

    public function test_confirming_all_payments_marks_bill_paid_and_orders_facturado(): void
    {
        [$session] = $this->scenario();
        $payments = app(PaymentService::class)->generate(
            app(BillService::class)->requestBill($session), BillModality::Automatica,
        );
        $staff = User::factory()->create();

        foreach ($payments as $payment) {
            app(PaymentService::class)->confirm($payment, $staff);
        }

        $this->assertSame(BillStatus::Pagada, $session->bill->fresh()->estado);
        $this->assertSame(OrderStatus::Facturado, $session->orders()->first()->fresh()->estado);
    }

    public function test_partial_confirmation_keeps_bill_in_progress(): void
    {
        [$session] = $this->scenario();
        $payments = app(PaymentService::class)->generate(
            app(BillService::class)->requestBill($session), BillModality::Automatica,
        );

        app(PaymentService::class)->confirm($payments->first(), User::factory()->create());

        $this->assertSame(BillStatus::EnPago, $session->bill->fresh()->estado);
    }

    public function test_whatsapp_link_carries_amount_and_bank_details(): void
    {
        [$session] = $this->scenario();
        $bill = app(BillService::class)->requestBill($session);
        $payment = app(PaymentService::class)->generate($bill, BillModality::Unica)->first();
        app(PaymentService::class)->setMethod($payment, PaymentMethod::Transferencia, 'Juan', '3001234567');

        $gateway = app(WhatsAppGateway::class);
        $message = $gateway->paymentMessage($payment->fresh());
        $link = $gateway->paymentLink($payment->fresh());

        $this->assertStringContainsString('Mesa 4', $message);
        $this->assertStringContainsString('123-456789-00', $message); // cuenta bancaria
        $this->assertStringContainsString('30.000', $message);
        $this->assertStringStartsWith('https://wa.me/573000000000?text=', $link);
    }
}
