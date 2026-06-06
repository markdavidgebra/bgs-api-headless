<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\ProductStockService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    private const INVENTORY_ROLE = 'inventory_officer';

    public function __construct(
        private readonly ProductStockService $stock,
    ) {}

    private function inventoryOfficerOrNull(?Admin $admin): ?array
    {
        if (! $admin || strtolower((string) $admin->role) !== self::INVENTORY_ROLE) {
            return null;
        }

        return $this->officerPayload($admin);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $emailInput = trim((string) ($request->input('email') ?? $request->input('username') ?? ''));
        if ($emailInput === '' || ! filter_var($emailInput, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                'email' => ['A valid email is required.'],
            ]);
        }

        $credentials = [
            'email' => $emailInput,
            'password' => (string) $request->input('password'),
        ];

        if (! Auth::guard('admin')->attempt($credentials, true)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $request->session()->regenerate();

        $admin = Auth::guard('admin')->user();
        if (! $admin || strtolower((string) $admin->role) !== self::INVENTORY_ROLE) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'message' => 'Forbidden. Only inventory officers may sign in here.',
            ], 403);
        }

        if (strtolower((string) ($admin->status ?? '')) !== 'approved') {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'message' => 'Your staff account is not approved yet.',
            ], 403);
        }

        return response()->json([
            'message' => 'Inventory login successful.',
            'csrf_token' => csrf_token(),
            'officer' => $this->officerPayload($admin),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'csrf_token' => csrf_token(),
            'officer' => $this->inventoryOfficerOrNull($request->user('admin')),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $admin = $request->user('admin');
        if (! $this->inventoryOfficerOrNull($admin)) {
            return response()->json([
                'message' => 'Forbidden. Only inventory officers may update this profile.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('admins', 'email')->ignore($admin->id),
            ],
        ]);

        $admin->fill($validated)->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'csrf_token' => csrf_token(),
            'officer' => $this->officerPayload($admin->fresh()),
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $admin = $request->user('admin');
        if (! $this->inventoryOfficerOrNull($admin)) {
            return response()->json([
                'message' => 'Forbidden. Only inventory officers may update this profile.',
            ], 403);
        }

        $validated = $request->validate([
            'current_password' => ['required', 'current_password:admin'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $admin->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }

    public function summary(): JsonResponse
    {
        $products = Product::query()->get(['id', 'stock_quantity', 'minimum_stock_alert']);

        $totalSkus = $products->count();
        $inStock = 0;
        $lowStock = 0;
        $outOfStock = 0;
        $totalUnits = 0;

        foreach ($products as $product) {
            $totalUnits += (int) $product->stock_quantity;
            match ($product->stock_status) {
                'in_stock' => $inStock++,
                'low_stock' => $lowStock++,
                'out_of_stock' => $outOfStock++,
                default => null,
            };
        }

        return response()->json([
            'summary' => [
                'total_skus' => $totalSkus,
                'in_stock' => $inStock,
                'low_stock' => $lowStock,
                'out_of_stock' => $outOfStock,
                'total_units' => $totalUnits,
            ],
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $limit = max(1, min((int) $request->integer('limit', 25), 100));

        $query = Product::query()
            ->with('categoryItem:id,name')
            ->orderBy('name');

        $this->applyProductFilters($query, $request);

        $paginator = $query->paginate($limit)->withQueryString();

        return response()->json([
            'products' => $paginator->getCollection()->map(fn (Product $p) => $this->productPayload($p))->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function showProduct(int $id): JsonResponse
    {
        $product = Product::query()
            ->with([
                'categoryItem:id,name',
                'stockMovements' => function ($q) {
                    $q->orderByDesc('created_at')->orderByDesc('id')->limit(50);
                },
            ])
            ->findOrFail($id);

        return response()->json([
            'product' => $this->productPayload($product, includeMovements: true),
        ]);
    }

    public function lowStock(Request $request): JsonResponse
    {
        $query = Product::query()
            ->with('categoryItem:id,name')
            ->orderBy('stock_quantity')
            ->orderBy('name');

        $status = $request->string('status')->toString();
        if ($status === 'out_of_stock') {
            $query->where('stock_quantity', '<=', 0);
        } elseif ($status === 'low_stock') {
            $query->where('stock_quantity', '>', 0)
                ->whereColumn('stock_quantity', '<=', 'minimum_stock_alert');
        } else {
            $query->where(function (Builder $q) {
                $q->where('stock_quantity', '<=', 0)
                    ->orWhere(function (Builder $sub) {
                        $sub->where('stock_quantity', '>', 0)
                            ->whereColumn('stock_quantity', '<=', 'minimum_stock_alert');
                    });
            });
        }

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhere('brand', 'like', "%{$term}%");
            });
        }

        $products = $query->get();

        return response()->json([
            'products' => $products->map(fn (Product $p) => $this->productPayload($p))->values(),
            'counts' => [
                'low_stock' => Product::query()
                    ->where('stock_quantity', '>', 0)
                    ->whereColumn('stock_quantity', '<=', 'minimum_stock_alert')
                    ->count(),
                'out_of_stock' => Product::query()->where('stock_quantity', '<=', 0)->count(),
            ],
        ]);
    }

    public function movements(Request $request): JsonResponse
    {
        $limit = max(1, min((int) $request->integer('limit', 50), 200));

        $statsQuery = StockMovement::query();
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'in' => (clone $statsQuery)->where('type', 'in')->count(),
            'out' => (clone $statsQuery)->where('type', 'out')->count(),
            'adjustment' => (clone $statsQuery)->where('type', 'adjustment')->count(),
        ];

        $query = StockMovement::query()
            ->with(['product' => fn ($q) => $q->select('id', 'name', 'sku', 'unit')])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->whereHas('product', function (Builder $q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%");
            });
        }

        if ($request->filled('type')) {
            $type = $request->string('type')->toString();
            if (in_array($type, ['in', 'out', 'adjustment'], true)) {
                $query->where('type', $type);
            }
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', (int) $request->integer('product_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        $paginator = $query->paginate($limit)->withQueryString();

        return response()->json([
            'stats' => $stats,
            'movements' => $paginator->getCollection()->map(fn (StockMovement $m) => $this->movementPayload($m))->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function storeMovement(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'type' => ['required', 'string', 'in:in,out'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $product = Product::query()->findOrFail((int) $validated['product_id']);

        $movement = $this->stock->recordInOrOut(
            $product,
            $validated['type'],
            (int) $validated['quantity'],
            isset($validated['reference']) ? trim((string) $validated['reference']) : null,
            isset($validated['notes']) ? trim((string) $validated['notes']) : null,
        );

        $movement->load(['product:id,name,sku,unit,stock_quantity,minimum_stock_alert']);

        return response()->json([
            'message' => $validated['type'] === 'in' ? 'Stock in recorded.' : 'Stock out recorded.',
            'movement' => $this->movementPayload($movement),
            'product' => $this->productPayload($movement->product),
        ], 201);
    }

    /**
     * @param  Builder<Product>  $query
     */
    private function applyProductFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhere('brand', 'like', "%{$term}%");
            });
        }

        if ($request->filled('stock_status')) {
            match ($request->string('stock_status')->toString()) {
                'in_stock' => $query->where('stock_quantity', '>', 0)
                    ->whereColumn('stock_quantity', '>', 'minimum_stock_alert'),
                'low_stock' => $query->where('stock_quantity', '>', 0)
                    ->whereColumn('stock_quantity', '<=', 'minimum_stock_alert'),
                'out_of_stock' => $query->where('stock_quantity', '<=', 0),
                default => null,
            };
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('category')) {
            $category = $request->string('category')->toString();
            $query->whereHas('categoryItem', function (Builder $q) use ($category) {
                $q->where('name', 'like', "%{$category}%");
            });
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function officerPayload(Admin $admin): array
    {
        return [
            'id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => $admin->role,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function productPayload(Product $product, bool $includeMovements = false): array
    {
        $payload = [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'brand' => $product->brand,
            'category' => $product->category,
            'unit' => $product->unit,
            'status' => $product->status,
            'stock_quantity' => (int) $product->stock_quantity,
            'minimum_stock_alert' => (int) $product->minimum_stock_alert,
            'stock_status' => $product->stock_status,
            'cost_price' => $product->cost_price !== null ? (float) $product->cost_price : null,
            'selling_price' => $product->selling_price !== null ? (float) $product->selling_price : null,
            'batch_number' => $product->batch_number,
            'expiry_date' => $product->expiry_date?->toDateString(),
            'supplier' => $product->supplier,
            'image_url' => $product->image_url,
        ];

        if ($includeMovements && $product->relationLoaded('stockMovements')) {
            $payload['recent_movements'] = $product->stockMovements
                ->map(fn (StockMovement $m) => $this->movementPayload($m))
                ->values()
                ->all();
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function movementPayload(StockMovement $movement): array
    {
        $product = $movement->relationLoaded('product') ? $movement->product : null;

        return [
            'id' => $movement->id,
            'product_id' => $movement->product_id,
            'product_name' => $product?->name,
            'product_sku' => $product?->sku,
            'product_unit' => $product?->unit,
            'type' => $movement->type,
            'type_label' => $movement->type_label,
            'quantity' => (int) $movement->quantity,
            'signed_quantity' => $movement->signed_quantity,
            'reference' => $movement->reference,
            'notes' => $movement->notes,
            'created_at' => $movement->created_at?->toIso8601String(),
            'product_stock_quantity' => $product ? (int) $product->stock_quantity : null,
        ];
    }
}
