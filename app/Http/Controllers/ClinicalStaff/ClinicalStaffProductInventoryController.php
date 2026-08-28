<?php

namespace App\Http\Controllers\ClinicalStaff;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class ClinicalStaffProductInventoryController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->with('categoryItem:id,name')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('clinical-staff.products.index', [
            'products' => $products,
            'doctorProductInventorySummary' => self::inventorySummary(),
        ]);
    }

    /**
     * @return array{in_stock: int, low_stock: int, out_of_stock: int, total_units: int, sku_count: int}
     */
    public static function inventorySummary(): array
    {
        return once(function (): array {
            $base = Product::query()->where('status', 'active');

            $outOfStock = (clone $base)->where('stock_quantity', '<=', 0)->count();
            $lowStock = (clone $base)->where('stock_quantity', '>', 0)
                ->whereColumn('stock_quantity', '<=', 'minimum_stock_alert')
                ->count();
            $inStock = (clone $base)->where('stock_quantity', '>', 0)
                ->whereColumn('stock_quantity', '>', 'minimum_stock_alert')
                ->count();

            $totalUnits = (clone $base)->sum('stock_quantity');

            return [
                'in_stock' => $inStock,
                'low_stock' => $lowStock,
                'out_of_stock' => $outOfStock,
                'total_units' => (int) $totalUnits,
                'sku_count' => (clone $base)->count(),
            ];
        });
    }
}
