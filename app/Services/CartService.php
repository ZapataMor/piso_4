<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\SessionParticipant;

/**
 * Carrito borrador por participante. No crea pedidos: solo prepara las
 * líneas hasta que el cliente pulse "Enviar Pedido" (ver OrderService).
 */
class CartService
{
    public function add(SessionParticipant $participant, Product $product, int $quantity = 1, ?string $notes = null): CartItem
    {
        $quantity = max(1, $quantity);
        $notes = $notes !== null && trim($notes) !== '' ? trim($notes) : null;

        // Une líneas iguales (mismo producto y mismas notas) sumando cantidad.
        $existing = $participant->cartItems()
            ->where('product_id', $product->id)
            ->when($notes === null,
                fn ($q) => $q->whereNull('notes'),
                fn ($q) => $q->where('notes', $notes),
            )
            ->first();

        if ($existing) {
            $existing->increment('quantity', $quantity);

            return $existing;
        }

        return $participant->cartItems()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'notes' => $notes,
        ]);
    }

    public function setQuantity(CartItem $item, int $quantity): void
    {
        if ($quantity <= 0) {
            $item->delete();

            return;
        }

        $item->update(['quantity' => $quantity]);
    }

    public function remove(CartItem $item): void
    {
        $item->delete();
    }

    public function clear(SessionParticipant $participant): void
    {
        $participant->cartItems()->delete();
    }

    public function total(SessionParticipant $participant): float
    {
        return $participant->cartItems()
            ->with('product')
            ->get()
            ->sum(fn (CartItem $item) => (float) ($item->product->price ?? 0) * $item->quantity);
    }
}
