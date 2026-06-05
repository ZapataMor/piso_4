<?php

namespace App\Concerns;

use App\Enums\OrderItemStatus;
use App\Enums\PreparationType;
use App\Models\OrderItem;
use App\Services\OrderService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;

/**
 * Lógica compartida por los tableros de cocina y bar. Cada componente
 * define su estación con stationType(). Las acciones quedan acotadas a la
 * estación a nivel de consulta (un usuario de bar nunca puede tocar una
 * línea de cocina aunque manipule el id).
 */
trait HasStationBoard
{
    abstract public function stationType(): PreparationType;

    /** @return array<string, Collection<int, OrderItem>> */
    #[Computed]
    public function columns(): array
    {
        $items = OrderItem::query()
            ->where('tipo_preparacion', $this->stationType()->value)
            ->whereIn('estado', [
                OrderItemStatus::Pendiente->value,
                OrderItemStatus::EnPreparacion->value,
                OrderItemStatus::Listo->value,
            ])
            ->with(['order.mesa', 'order.participant'])
            ->orderBy('created_at')
            ->get();

        return [
            'pendiente' => $items->where('estado', OrderItemStatus::Pendiente)->values(),
            'en_preparacion' => $items->where('estado', OrderItemStatus::EnPreparacion)->values(),
            'listo' => $items->where('estado', OrderItemStatus::Listo)->values(),
        ];
    }

    public function start(int $itemId, OrderService $orders): void
    {
        if ($item = $this->stationItem($itemId)) {
            $orders->startItem($item, auth()->user());
            unset($this->columns);
        }
    }

    public function ready(int $itemId, OrderService $orders): void
    {
        if ($item = $this->stationItem($itemId)) {
            $orders->markItemReady($item, auth()->user());
            unset($this->columns);
        }
    }

    public function refreshBoard(): void
    {
        unset($this->columns);
    }

    private function stationItem(int $itemId): ?OrderItem
    {
        return OrderItem::where('tipo_preparacion', $this->stationType()->value)->find($itemId);
    }
}
