<?php

namespace Tests\Feature;

use App\Enums\BillModality;
use App\Models\Category;
use App\Models\Mesa;
use App\Models\Product;
use App\Models\User;
use App\Services\BillService;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\SessionService;
use App\Services\StatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatisticsTest extends TestCase
{
    use RefreshDatabase;

    private function stats(): StatsService
    {
        return app(StatsService::class);
    }

    /** Crea una venta confirmada hoy (Bandeja 20.000 + Jugo 10.000 = 30.000). */
    private function confirmedSale(int $numero = 1): void
    {
        $mesa = Mesa::create(['numero' => $numero, 'qr_token' => 'tok-'.$numero, 'estado' => 'disponible']);
        $session = app(SessionService::class)->openOrGetActiveSession($mesa);
        $p = app(SessionService::class)->addParticipant($session, 'Juan');

        $cat = Category::firstOrCreate(['slug' => 'c'], ['name' => 'C', 'display_order' => 1]);
        $cocina = Product::create(['category_id' => $cat->id, 'name' => 'Bandeja', 'price' => 20000, 'tipo_preparacion' => 'cocina', 'is_available' => true]);
        $bar = Product::create(['category_id' => $cat->id, 'name' => 'Jugo', 'price' => 10000, 'tipo_preparacion' => 'bar', 'is_available' => true]);

        app(CartService::class)->add($p, $cocina, 1);
        app(CartService::class)->add($p, $bar, 1);
        $order = app(OrderService::class)->submitOrder($p);

        foreach ($order->items as $item) {
            app(OrderService::class)->markItemReady($item); // genera ready_at para el tiempo de preparación
        }

        $bill = app(BillService::class)->requestBill($session, $p);
        $payment = app(PaymentService::class)->generate($bill, BillModality::Unica)->first();
        app(PaymentService::class)->confirm($payment, User::factory()->create());
    }

    public function test_sales_today_sums_confirmed_payments(): void
    {
        $this->confirmedSale();

        $this->assertEquals(30000.0, $this->stats()->salesToday());
        $this->assertEquals(30000.0, $this->stats()->salesThisMonth());
    }

    public function test_top_products(): void
    {
        $this->confirmedSale();

        $top = $this->stats()->topProducts();

        $this->assertCount(2, $top);
        $this->assertEqualsCanonicalizing(['Bandeja', 'Jugo'], $top->pluck('product_name')->all());
    }

    public function test_income_by_method(): void
    {
        $this->confirmedSale();

        $income = $this->stats()->incomeByMethod();

        $this->assertSame('efectivo', $income->first()->metodo->value);
        $this->assertEquals(30000.0, (float) $income->first()->total);
    }

    public function test_average_prep_minutes_is_computed(): void
    {
        $this->confirmedSale();

        $this->assertNotNull($this->stats()->averagePrepMinutes());
    }

    public function test_live_snapshot(): void
    {
        $this->confirmedSale();

        $snap = $this->stats()->liveSnapshot();

        $this->assertEquals(30000.0, $snap['sales_today']);
        $this->assertSame(1, $snap['active_tables']);
        $this->assertSame(1, $snap['orders_today']);
    }

    public function test_sales_by_day_has_seven_entries(): void
    {
        $this->assertCount(7, $this->stats()->salesByDay(7));
        $this->assertCount(24, $this->stats()->ordersByHour());
    }
}
