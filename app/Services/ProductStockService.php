<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductStockService
{
    /**
     * Record a stock-in or stock-out movement and update product quantity.
     *
     * @throws ValidationException
     */
    public function recordInOrOut(
        Product $product,
        string $type,
        int $quantity,
        ?string $reference = null,
        ?string $notes = null,
    ): StockMovement {
        if (! in_array($type, ['in', 'out'], true)) {
            throw ValidationException::withMessages([
                'type' => ['Movement type must be in or out.'],
            ]);
        }

        if ($quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => ['Quantity must be at least 1.'],
            ]);
        }

        return DB::transaction(function () use ($product, $type, $quantity, $reference, $notes) {
            $locked = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();

            if ($type === 'out' && (int) $locked->stock_quantity < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => [
                        "Insufficient stock for {$locked->name}. Requested {$quantity}, available {$locked->stock_quantity}.",
                    ],
                ]);
            }

            if ($type === 'in') {
                $locked->increment('stock_quantity', $quantity);
            } else {
                $locked->decrement('stock_quantity', $quantity);
            }

            return StockMovement::query()->create([
                'product_id' => $locked->id,
                'type' => $type,
                'quantity' => $quantity,
                'reference' => $reference,
                'notes' => $notes,
            ]);
        });
    }
}
