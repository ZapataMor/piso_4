<?php

namespace App\Services;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\RestaurantSession;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Estadísticas del negocio. "Ventas" = pagos confirmados (dinero real
 * recibido), fechadas por confirmed_at. Las agregaciones por fecha/hora
 * se resuelven en PHP para ser independientes del motor de BD.
 */
class StatsService
{
    public function salesBetween(CarbonInterface $from, CarbonInterface $to): float
    {
        return (float) Payment::confirmed()->whereBetween('confirmed_at', [$from, $to])->sum('monto');
    }

    public function salesToday(): float
    {
        return $this->salesBetween(now()->startOfDay(), now()->endOfDay());
    }

    public function salesThisWeek(): float
    {
        return $this->salesBetween(now()->startOfWeek(), now()->endOfWeek());
    }

    public function salesThisMonth(): float
    {
        return $this->salesBetween(now()->startOfMonth(), now()->endOfMonth());
    }

    /** Ventas por día de los últimos N días. */
    public function salesByDay(int $days = 7): Collection
    {
        $from = now()->subDays($days - 1)->startOfDay();
        $to   = now()->endOfDay();

        $totals = Payment::confirmed()
            ->whereBetween('confirmed_at', [$from, $to])
            ->selectRaw('DATE(confirmed_at) as day, SUM(monto) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return collect(range($days - 1, 0))->map(function (int $i) use ($totals) {
            $day = now()->subDays($i);

            return [
                'date'  => $day->toDateString(),
                'label' => ucfirst($day->isoFormat('ddd D')),
                'total' => (float) ($totals[$day->toDateString()] ?? 0),
            ];
        })->values();
    }

    /** Productos más vendidos (por cantidad). */
    public function topProducts(int $limit = 10): Collection
    {
        return OrderItem::query()
            ->where('estado', '!=', OrderItemStatus::Cancelado->value)
            ->selectRaw('product_name, SUM(quantity) as qty, SUM(unit_price * quantity) as revenue')
            ->groupBy('product_name')
            ->orderByDesc('qty')
            ->limit($limit)
            ->get();
    }

    /** Mesas más utilizadas (por número de pedidos). */
    public function topTables(int $limit = 10): Collection
    {
        return Order::query()
            ->where('estado', '!=', OrderStatus::Cancelado->value)
            ->selectRaw('mesa_id, COUNT(*) as orders_count, SUM(subtotal) as revenue')
            ->groupBy('mesa_id')
            ->orderByDesc('orders_count')
            ->limit($limit)
            ->with('mesa')
            ->get();
    }

    /** Tiempo promedio de preparación (placed_at → ready_at), en minutos. */
    public function averagePrepMinutes(): ?float
    {
        $orders = Order::query()
            ->whereNotNull('placed_at')
            ->whereNotNull('ready_at')
            ->get(['placed_at', 'ready_at']);

        if ($orders->isEmpty()) {
            return null;
        }

        return round($orders->avg(fn (Order $o) => abs($o->placed_at->diffInSeconds($o->ready_at)) / 60), 1);
    }

    /** Pedidos por hora del día (0–23). */
    public function ordersByHour(): Collection
    {
        $byHour = Order::query()
            ->whereNotNull('placed_at')
            ->get(['placed_at'])
            ->groupBy(fn (Order $o) => (int) $o->placed_at->format('G'))
            ->map->count();

        return collect(range(0, 23))->map(fn (int $h) => [
            'hour' => $h,
            'count' => (int) ($byHour[$h] ?? 0),
        ]);
    }

    /** Ingresos por método de pago (pagos confirmados). */
    public function incomeByMethod(): Collection
    {
        return Payment::confirmed()
            ->selectRaw('metodo, SUM(monto) as total, COUNT(*) as count')
            ->groupBy('metodo')
            ->get();
    }

    /** Métricas rápidas para el panel principal. */
    public function liveSnapshot(): array
    {
        return Cache::remember('stats.live_snapshot', 5, function () {
            return [
                'sales_today'   => $this->salesToday(),
                'active_tables' => RestaurantSession::where('estado', 'activa')->count(),
                'pending_items' => OrderItem::whereIn('estado', [
                    OrderItemStatus::Pendiente->value,
                    OrderItemStatus::EnPreparacion->value,
                ])->count(),
                'orders_today'  => Order::whereBetween('placed_at', [now()->startOfDay(), now()->endOfDay()])->count(),
            ];
        });
    }
}
