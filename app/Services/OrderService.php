<?php

namespace App\Services;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SessionParticipant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Conversión del carrito en pedido real y la MÁQUINA DE ESTADOS de los
 * pedidos. El estado del Order es un agregado derivado de sus order_items;
 * las estaciones (cocina/bar) sólo tocan sus líneas y aquí se recalcula
 * el estado y los timestamps del pedido.
 */
class OrderService
{
    /** "Enviar Pedido": carrito -> order + order_items (con snapshots). */
    public function submitOrder(SessionParticipant $participant): Order
    {
        return DB::transaction(function () use ($participant) {
            $cartItems = $participant->cartItems()->with('product')->lockForUpdate()->get();

            if ($cartItems->isEmpty()) {
                throw new RuntimeException('El carrito está vacío.');
            }

            $session = $participant->session;

            $order = Order::create([
                'restaurant_session_id' => $session->id,
                'session_participant_id' => $participant->id,
                'mesa_id' => $session->mesa_id,
                'numero' => $this->nextOrderNumber($session->id),
                'estado' => OrderStatus::Pendiente,
                'placed_at' => now(),
            ]);

            $subtotal = 0;

            foreach ($cartItems as $cartItem) {
                $product = $cartItem->product;
                $unitPrice = (int) ($product->price ?? 0);
                $subtotal += $unitPrice * $cartItem->quantity;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $unitPrice,
                    'quantity' => $cartItem->quantity,
                    'tipo_preparacion' => $product->tipo_preparacion,
                    'estado' => OrderItemStatus::Pendiente,
                    'notes' => $cartItem->notes,
                ]);
            }

            $order->update(['subtotal' => $subtotal]);

            $participant->cartItems()->delete(); // vaciar carrito

            return $order->load('items');
        });
    }

    public function nextOrderNumber(int $sessionId): int
    {
        return (int) Order::where('restaurant_session_id', $sessionId)->max('numero') + 1;
    }

    // ----------------------------------------------------------------
    // Transiciones de línea (usadas por cocina, bar y meseros)
    // ----------------------------------------------------------------

    public function startItem(OrderItem $item, ?User $by = null): void
    {
        $item->update([
            'estado' => OrderItemStatus::EnPreparacion,
            'started_at' => $item->started_at ?? now(),
            'prepared_by' => $by?->id ?? $item->prepared_by,
        ]);

        $this->syncOrderStatus($item->order);
    }

    public function markItemReady(OrderItem $item, ?User $by = null): void
    {
        $item->update([
            'estado' => OrderItemStatus::Listo,
            'started_at' => $item->started_at ?? now(),
            'ready_at' => now(),
            'prepared_by' => $by?->id ?? $item->prepared_by,
        ]);

        $this->syncOrderStatus($item->order);
    }

    public function deliverItem(OrderItem $item): void
    {
        $item->update([
            'estado' => OrderItemStatus::Entregado,
            'delivered_at' => now(),
        ]);

        $this->syncOrderStatus($item->order);
    }

    public function cancelItem(OrderItem $item): void
    {
        $item->update(['estado' => OrderItemStatus::Cancelado]);

        $this->syncOrderStatus($item->order);
    }

    /**
     * Recalcula el estado y los timestamps del pedido a partir de sus
     * líneas (excluyendo las canceladas).
     */
    public function syncOrderStatus(Order $order): void
    {
        $items = $order->items()->get();
        $active = $items->where('estado', '!=', OrderItemStatus::Cancelado);

        // Todas canceladas -> pedido cancelado.
        if ($active->isEmpty()) {
            $order->update(['estado' => OrderStatus::Cancelado]);

            return;
        }

        $estado = match (true) {
            $active->every(fn ($i) => $i->estado === OrderItemStatus::Entregado) => OrderStatus::Entregado,
            $active->every(fn ($i) => in_array($i->estado, [OrderItemStatus::Listo, OrderItemStatus::Entregado], true)) => OrderStatus::Listo,
            $active->contains(fn ($i) => in_array($i->estado, [OrderItemStatus::EnPreparacion, OrderItemStatus::Listo, OrderItemStatus::Entregado], true)) => OrderStatus::EnPreparacion,
            default => OrderStatus::Pendiente,
        };

        $order->update([
            'estado' => $estado,
            'started_at' => $order->started_at ?? ($estado !== OrderStatus::Pendiente ? now() : null),
            'ready_at' => $estado === OrderStatus::Listo || $estado === OrderStatus::Entregado ? ($order->ready_at ?? now()) : $order->ready_at,
            'delivered_at' => $estado === OrderStatus::Entregado ? ($order->delivered_at ?? now()) : $order->delivered_at,
        ]);
    }
}
