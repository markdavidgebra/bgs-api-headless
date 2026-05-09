<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockMovement;
use App\Support\ProductCatalogPageConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductsController extends Controller
{
    public function editCatalogPage(): View
    {
        return view('admin.products.pages', [
            'tagline' => old('tagline', AppSetting::getValue(ProductCatalogPageConfig::TAGLINE_KEY) ?? ProductCatalogPageConfig::defaultTagline()),
            'heading' => old('heading', AppSetting::getValue(ProductCatalogPageConfig::HEADING_KEY) ?? ProductCatalogPageConfig::defaultHeading()),
            'lede' => old('lede', AppSetting::getValue(ProductCatalogPageConfig::LEDE_KEY) ?? ProductCatalogPageConfig::defaultLede()),
            'trustItems' => old('trust_items', ProductCatalogPageConfig::trustItemsForForm()),
            'iconOptions' => ProductCatalogPageConfig::iconOptions(),
            'defaultTrustIcon' => ProductCatalogPageConfig::presetIconValues()[0] ?? 'fa-leaf-heart',
        ]);
    }

    public function updateCatalogPage(Request $request): RedirectResponse
    {
        $presets = ProductCatalogPageConfig::presetIconValues();

        $validated = $request->validate([
            'tagline' => ['required', 'string', 'max:255'],
            'heading' => ['required', 'string', 'max:1000'],
            'lede' => ['required', 'string', 'max:5000'],
            'trust_items' => ['required', 'array', 'min:1', 'max:20'],
            'trust_items.*.icon' => ['required', 'string'],
            'trust_items.*.icon_custom' => ['nullable', 'string', 'max:80'],
            'trust_items.*.label' => ['required', 'string', 'max:255'],
        ]);

        $heading = preg_replace("/\r\n|\r/", "\n", $validated['heading']);

        $trustClean = [];
        foreach ($validated['trust_items'] as $i => $row) {
            $iconKey = $row['icon'];
            if ($iconKey === 'custom') {
                $icon = trim((string) ($row['icon_custom'] ?? ''));
                if (! preg_match('/^fa-[a-z0-9-]+$/', $icon)) {
                    throw ValidationException::withMessages([
                        "trust_items.$i.icon_custom" => __('Enter a Font Awesome solid class such as fa-heart.'),
                    ]);
                }
            } elseif (in_array($iconKey, $presets, true)) {
                $icon = $iconKey;
            } else {
                throw ValidationException::withMessages([
                    "trust_items.$i.icon" => __('Invalid icon selection.'),
                ]);
            }
            $trustClean[] = ['icon' => $icon, 'label' => trim($row['label'])];
        }

        AppSetting::setValue(ProductCatalogPageConfig::TAGLINE_KEY, trim($validated['tagline']));
        AppSetting::setValue(ProductCatalogPageConfig::HEADING_KEY, trim($heading));
        AppSetting::setValue(ProductCatalogPageConfig::LEDE_KEY, trim($validated['lede']));
        AppSetting::setValue(ProductCatalogPageConfig::TRUST_ITEMS_KEY, json_encode($trustClean));

        return redirect()->route('admin.products.pages')->with('status', __('Products catalog page copy updated.'));
    }

    public function index(Request $request): View
    {
        $query = Product::query()
            ->with('categoryItem')
            ->orderBy('name');

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhere('brand', 'like', "%{$term}%");
            });
        }

        if ($request->filled('category')) {
            $category = $request->string('category')->toString();
            $query->whereHas('categoryItem', function ($q) use ($category) {
                $q->where('name', 'like', "%{$category}%");
            });
        }

        if ($request->filled('status')) {
            match ($request->string('status')->toString()) {
                'in_stock' => $query->where('stock_quantity', '>', 0)
                    ->whereColumn('stock_quantity', '>', 'minimum_stock_alert'),
                'low_stock' => $query->where('stock_quantity', '>', 0)
                    ->whereColumn('stock_quantity', '<=', 'minimum_stock_alert'),
                'out_of_stock' => $query->where('stock_quantity', '<=', 0),
                default => null,
            };
        }

        $products = $query->paginate(15)->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function inventory(Request $request): View
    {
        $allProducts = Product::query()->orderBy('name')->get();

        $totalSkus = $allProducts->count();
        $inStockCount = $allProducts->where('stock_status', 'in_stock')->count();
        $lowStockCount = $allProducts->where('stock_status', 'low_stock')->count();
        $outStockCount = $allProducts->where('stock_status', 'out_of_stock')->count();

        $lowStockProducts = $allProducts->where('stock_status', 'low_stock')->values();
        $outStockProducts = $allProducts->where('stock_status', 'out_of_stock')->values();

        $query = Product::query()->orderBy('name');

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function ($q) use ($term) {
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

        $products = $query->get();

        return view('admin.products.inventory', compact(
            'products',
            'totalSkus',
            'inStockCount',
            'lowStockCount',
            'outStockCount',
            'lowStockProducts',
            'outStockProducts',
        ));
    }

    public function stockMovements(Request $request): View
    {
        $statsQuery = StockMovement::query();
        $totalRecords = (clone $statsQuery)->count();
        $countIn = (clone $statsQuery)->where('type', 'in')->count();
        $countOut = (clone $statsQuery)->where('type', 'out')->count();
        $countAdj = (clone $statsQuery)->where('type', 'adjustment')->count();

        $query = StockMovement::query()
            ->with(['product' => fn ($q) => $q->select('id', 'name', 'sku')])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->whereHas('product', function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%");
            });
        }

        if ($request->filled('movement_type')) {
            match ($request->string('movement_type')->toString()) {
                'in' => $query->where('type', 'in'),
                'out' => $query->where('type', 'out'),
                'adjustment' => $query->where('type', 'adjustment'),
                default => null,
            };
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        $movements = $query->get();

        return view('admin.products.stock-movements', compact(
            'movements',
            'totalRecords',
            'countIn',
            'countOut',
            'countAdj',
        ));
    }

    public function categories(Request $request): View
    {
        $query = ProductCategory::query()
            ->leftJoin('products', 'products.category_id', '=', 'product_categories.id')
            ->select('product_categories.id', 'product_categories.name')
            ->selectRaw('COUNT(products.id) as total_products')
            ->groupBy('product_categories.id', 'product_categories.name')
            ->orderBy('product_categories.name');

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where('product_categories.name', 'like', "%{$term}%");
        }

        $categories = $query->paginate(20)->withQueryString();

        return view('admin.products.categories.index', compact('categories'));
    }

    public function categoriesCreate(): View
    {
        return view('admin.products.categories.create');
    }

    public function categoriesStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('product_categories', 'name')],
        ]);

        ProductCategory::query()->create([
            'name' => trim($validated['name']),
        ]);

        return redirect()
            ->route('admin.products.categories')
            ->with('status', __('Category created.'));
    }

    public function create(): View
    {
        $categories = ProductCategory::query()->orderBy('name')->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rulesForProduct());

        $slugInput = trim((string) ($validated['slug'] ?? ''));
        $slug = $slugInput !== ''
            ? $this->uniqueProductSlug(Str::slug($slugInput) ?: Str::slug($validated['name']), null)
            : $this->uniqueProductSlug(Str::slug($validated['name']) ?: 'product-'.Str::lower(Str::random(8)), null);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->storeProductImage($request->file('image'));
        }

        $sku = isset($validated['sku']) && $validated['sku'] !== '' ? $validated['sku'] : null;

        $discountPrice = $this->resolveDiscountPrice($request, $validated);

        $assuranceLines = $this->normalizedShowcaseAssuranceLines($request);

        $product = Product::query()->create([
            'name' => $validated['name'],
            'slug' => $slug,
            'category_id' => $validated['category_id'],
            'brand' => $validated['brand'] ?? null,
            'sku' => $sku,
            'description' => $validated['description'] ?? null,
            'showcase_assurance_lines' => $assuranceLines,
            'image' => $imagePath,
            'cost_price' => $validated['cost_price'],
            'selling_price' => $validated['selling_price'],
            'discount_price' => $discountPrice,
            'stock_quantity' => $validated['stock_quantity'],
            'minimum_stock_alert' => $validated['minimum_stock_alert'] ?? 0,
            'unit' => $validated['unit'] ?? null,
            'status' => $validated['status'],
            'is_available_for_sale' => $request->boolean('is_available_for_sale'),
            'expiry_date' => $validated['expiry_date'] ?? null,
            'batch_number' => $validated['batch_number'] ?? null,
            'supplier' => $validated['supplier'] ?? null,
        ]);

        $openingStock = (int) $validated['stock_quantity'];
        if ($openingStock > 0) {
            StockMovement::query()->create([
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => $openingStock,
                'reference' => 'OPENING',
                'notes' => 'Initial stock on product creation.',
            ]);
        }

        return redirect()
            ->route('admin.products.show', $product)
            ->with('status', __('Product created.'));
    }

    public function show(int $id): View
    {
        $product = Product::query()
            ->with([
                'categoryItem',
                'stockMovements' => function ($q) {
                    $q->orderByDesc('created_at')->orderByDesc('id');
                },
            ])
            ->findOrFail($id);

        return view('admin.products.show', compact('product'));
    }

    public function edit(int $id): View
    {
        $product = Product::query()->with('categoryItem')->findOrFail($id);
        $categories = ProductCategory::query()->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $product = Product::query()->findOrFail($id);
        $validated = $request->validate($this->rulesForProduct($product->id));

        $slugInput = trim((string) ($validated['slug'] ?? ''));
        $slug = $slugInput !== ''
            ? $this->uniqueProductSlug(Str::slug($slugInput) ?: Str::slug($validated['name']), $product->id)
            : $this->uniqueProductSlug(Str::slug($validated['name']) ?: ($product->slug ?? ''), $product->id);

        $sku = isset($validated['sku']) && $validated['sku'] !== '' ? $validated['sku'] : null;

        $discountPrice = $this->resolveDiscountPrice($request, $validated);

        $payload = [
            'name' => $validated['name'],
            'slug' => $slug,
            'category_id' => $validated['category_id'],
            'brand' => $validated['brand'] ?? null,
            'sku' => $sku,
            'description' => $validated['description'] ?? null,
            'cost_price' => $validated['cost_price'],
            'selling_price' => $validated['selling_price'],
            'discount_price' => $discountPrice,
            'stock_quantity' => $validated['stock_quantity'],
            'minimum_stock_alert' => $validated['minimum_stock_alert'] ?? 0,
            'unit' => $validated['unit'] ?? null,
            'status' => $validated['status'],
            'is_available_for_sale' => $request->boolean('is_available_for_sale'),
            'expiry_date' => $validated['expiry_date'] ?? null,
            'batch_number' => $validated['batch_number'] ?? null,
            'supplier' => $validated['supplier'] ?? null,
            'showcase_assurance_lines' => $this->normalizedShowcaseAssuranceLines($request),
        ];

        $uploadedImage = $request->file('image');
        if ($uploadedImage instanceof UploadedFile && $uploadedImage->isValid()) {
            $this->deleteStoredProductImage($product->image);
            $payload['image'] = $this->storeProductImage($uploadedImage);
        }

        $oldQty = (int) $product->stock_quantity;
        $newQty = (int) $validated['stock_quantity'];

        $product->update($payload);
        $product->refresh();

        $delta = $newQty - $oldQty;
        if ($delta !== 0) {
            StockMovement::query()->create([
                'product_id' => $product->id,
                'type' => 'adjustment',
                'quantity' => $delta,
                'reference' => 'EDIT',
                'notes' => 'Stock quantity changed in product edit.',
            ]);
        }

        return redirect()
            ->route('admin.products.show', ['id' => $product->id])
            ->with('status', __('Product updated.'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $product = Product::query()->findOrFail($id);

        if ($product->prescribedOnAppointments()->exists()) {
            return redirect()
                ->route('admin.products')
                ->with('error', __('Cannot delete this product because it is already linked to appointment records.'));
        }

        $this->deleteStoredProductImage($product->image);
        $product->delete();

        return redirect()
            ->route('admin.products')
            ->with('status', __('Product deleted.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function rulesForProduct(?int $ignoreProductId = null): array
    {
        $skuRule = Rule::unique('products', 'sku');
        if ($ignoreProductId !== null) {
            $skuRule = $skuRule->ignore($ignoreProductId);
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'category_id' => ['required', 'integer', Rule::exists('product_categories', 'id')],
            'brand' => ['nullable', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255', $skuRule],
            'description' => ['nullable', 'string'],
            'showcase_assurance_lines' => ['nullable', 'array', 'max:15'],
            'showcase_assurance_lines.*' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'max:5120'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'discount_mode' => ['nullable', 'string', 'in:fixed,percentage'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lte:selling_price'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'minimum_stock_alert' => ['nullable', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:active,inactive,archived'],
            'expiry_date' => ['nullable', 'date'],
            'batch_number' => ['nullable', 'string', 'max:255'],
            'supplier' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function resolveDiscountPrice(Request $request, array $validated): ?float
    {
        $mode = $request->string('discount_mode')->toString() ?: 'fixed';

        if ($mode === 'percentage') {
            $raw = $request->input('discount_percent');
            if ($raw === null || $raw === '') {
                return null;
            }
            $pct = (float) $raw;
            if ($pct <= 0) {
                return null;
            }
            $selling = (float) $validated['selling_price'];

            return round(max(0, $selling * (1 - ($pct / 100))), 2);
        }

        $fixed = $validated['discount_price'] ?? null;
        if ($fixed === null || $fixed === '') {
            return null;
        }

        return (float) $fixed;
    }

    /**
     * @return list<string>|null Null when the key was not submitted (should not happen on our forms).
     */
    private function normalizedShowcaseAssuranceLines(Request $request): ?array
    {
        if (! $request->has('showcase_assurance_lines')) {
            return null;
        }

        $lines = $request->input('showcase_assurance_lines');
        if (! is_array($lines)) {
            return [];
        }

        $out = [];
        foreach ($lines as $line) {
            $t = is_string($line) ? trim($line) : '';
            if ($t !== '') {
                $out[] = $t;
            }
        }

        return $out;
    }

    private function uniqueProductSlug(string $slug, ?int $ignoreId): string
    {
        if ($slug === '') {
            $slug = 'product-'.Str::lower(Str::random(8));
        }

        $base = $slug;
        $i = 1;
        while (Product::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    private function storeProductImage(UploadedFile $file): string
    {
        $dir = public_path('products');
        File::ensureDirectoryExists($dir);

        $name = $file->hashName();
        $file->move($dir, $name);

        return 'products/'.$name;
    }

    private function deleteStoredProductImage(?string $path): void
    {
        if (! $path || Str::startsWith($path, ['http://', 'https://'])) {
            return;
        }

        $publicFile = public_path($path);
        if (is_file($publicFile)) {
            @unlink($publicFile);

            return;
        }

        Storage::disk('public')->delete($path);
    }
}
