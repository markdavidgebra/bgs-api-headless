<?php

namespace App\Services;

use App\Models\AffiliateCode;
use App\Models\MembershipPlan;
use App\Models\Product;
use App\Models\Service;
use App\Models\TreatmentPackage;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PosAffiliateCodeService
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array{
     *     affiliate_code: AffiliateCode,
     *     lines: array<int, array<string, mixed>>,
     *     totals: array{subtotal: float, discount: float, total: float},
     *     discount_applied: bool
     * }
     */
    /**
     * Build cart lines from all active catalog items linked to the affiliate code.
     *
     * @return array{
     *     affiliate_code: AffiliateCode,
     *     lines: array<int, array<string, mixed>>,
     *     totals: array{subtotal: float, discount: float, total: float},
     *     discount_applied: bool
     * }
     */
    public function cartItemsForCode(string $code): array
    {
        $affiliateCode = $this->resolveActiveCode($code);
        $affiliateCode->load([
            'products' => fn ($query) => $query
                ->where('status', 'active')
                ->where('is_available_for_sale', true)
                ->orderBy('name'),
            'services' => fn ($query) => $query
                ->where('status', 'active')
                ->where('is_bookable', true)
                ->orderBy('name'),
            'treatmentPackages' => fn ($query) => $query
                ->where('status', 'active')
                ->orderBy('name'),
            'membershipPlans' => fn ($query) => $query
                ->where('status', 'active')
                ->orderBy('name'),
        ]);

        $rows = [];

        foreach ($affiliateCode->products as $product) {
            $rows[] = ['type' => 'product', 'id' => $product->id, 'quantity' => 1];
        }

        foreach ($affiliateCode->services as $service) {
            $rows[] = ['type' => 'service', 'id' => $service->id, 'quantity' => 1];
        }

        foreach ($affiliateCode->treatmentPackages as $package) {
            $rows[] = ['type' => 'package', 'id' => $package->id, 'quantity' => 1];
        }

        foreach ($affiliateCode->membershipPlans as $membership) {
            $rows[] = ['type' => 'membership', 'id' => $membership->id, 'quantity' => 1];
        }

        if ($rows === []) {
            throw ValidationException::withMessages([
                'code' => ['This affiliate code has no available products, services, packages, or membership plans.'],
            ]);
        }

        $preview = $this->preview(
            $code,
            $rows,
            function (string $type, int $id) use ($affiliateCode): float {
                return $this->unitPriceFromLoadedRelations($affiliateCode, $type, $id);
            },
        );

        $preview['lines'] = array_map(
            fn (array $line): array => $this->enrichLineWithCatalog($line, $affiliateCode),
            $preview['lines'],
        );

        return $preview;
    }

    public function preview(string $code, array $items, callable $resolveUnitPrice): array
    {
        $affiliateCode = $this->resolveActiveCode($code);
        $affiliateCode->load(['services:id', 'treatmentPackages:id', 'membershipPlans:id', 'products:id']);

        $lines = [];
        $subtotal = 0.0;
        $discountTotal = 0.0;

        foreach ($items as $row) {
            $type = (string) $row['type'];
            $recordId = (int) $row['id'];
            $quantity = max(1, (int) ($row['quantity'] ?? 1));

            $unitPrice = array_key_exists('unit_price', $row)
                ? (float) $row['unit_price']
                : $resolveUnitPrice($type, $recordId);

            $lineSubtotal = round($unitPrice * $quantity, 2);
            $eligible = $this->itemIsEligible($affiliateCode, $type, $recordId);
            $lineDiscount = $eligible
                ? $this->discountForLine($affiliateCode, $lineSubtotal)
                : 0.0;
            $lineTotal = round(max(0, $lineSubtotal - $lineDiscount), 2);

            $lines[] = [
                'type' => $type,
                'id' => $recordId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $lineSubtotal,
                'discount' => $lineDiscount,
                'total' => $lineTotal,
                'eligible' => $eligible,
            ];

            $subtotal += $lineSubtotal;
            $discountTotal += $lineDiscount;
        }

        $subtotal = round($subtotal, 2);
        $discountTotal = round($discountTotal, 2);

        return [
            'affiliate_code' => $affiliateCode,
            'lines' => $lines,
            'totals' => [
                'subtotal' => $subtotal,
                'discount' => $discountTotal,
                'total' => round(max(0, $subtotal - $discountTotal), 2),
            ],
            'discount_applied' => $discountTotal > 0,
        ];
    }

    public function resolveActiveCode(string $code): AffiliateCode
    {
        $normalized = strtoupper(trim($code));
        if ($normalized === '') {
            throw ValidationException::withMessages([
                'affiliate_code' => ['Affiliate code is required.'],
            ]);
        }

        $affiliateCode = AffiliateCode::query()
            ->where('code', $normalized)
            ->first();

        if (! $affiliateCode) {
            throw ValidationException::withMessages([
                'affiliate_code' => ['Affiliate code not found.'],
            ]);
        }

        if ($affiliateCode->status !== 'active') {
            throw ValidationException::withMessages([
                'affiliate_code' => ['This affiliate code is not active.'],
            ]);
        }

        $today = now()->startOfDay();

        if ($affiliateCode->effective_from && $today->lt($affiliateCode->effective_from->copy()->startOfDay())) {
            throw ValidationException::withMessages([
                'affiliate_code' => ['This affiliate code is not effective yet.'],
            ]);
        }

        if ($affiliateCode->effective_to && $today->gt($affiliateCode->effective_to->copy()->endOfDay())) {
            throw ValidationException::withMessages([
                'affiliate_code' => ['This affiliate code has expired.'],
            ]);
        }

        return $affiliateCode;
    }

    public function itemIsEligible(AffiliateCode $affiliateCode, string $type, int $recordId): bool
    {
        return match ($type) {
            'service' => $this->idInRelation($affiliateCode->services, $recordId),
            'package' => $this->idInRelation($affiliateCode->treatmentPackages, $recordId),
            'membership' => $this->idInRelation($affiliateCode->membershipPlans, $recordId),
            'product' => $this->idInRelation($affiliateCode->products, $recordId),
            default => false,
        };
    }

    public function discountForLine(AffiliateCode $affiliateCode, float $lineSubtotal): float
    {
        if ($lineSubtotal <= 0) {
            return 0.0;
        }

        $discount = match ($affiliateCode->discount_method) {
            'percentage' => $lineSubtotal * ((float) $affiliateCode->discount_value / 100),
            'fixed' => (float) $affiliateCode->discount_value,
            default => 0.0,
        };

        return round(min($discount, $lineSubtotal), 2);
    }

    /**
     * @param  Collection<int, \Illuminate\Database\Eloquent\Model>  $relation
     */
    private function idInRelation(Collection $relation, int $recordId): bool
    {
        return $relation->contains('id', $recordId);
    }

    /**
     * @param  array<int, array<string, mixed>>  $previewLines
     */
    public function lineDiscountFor(array $previewLines, string $type, int $recordId): float
    {
        foreach ($previewLines as $line) {
            if (($line['type'] ?? null) === $type && (int) ($line['id'] ?? 0) === $recordId) {
                return (float) ($line['discount'] ?? 0);
            }
        }

        return 0.0;
    }

    public function formatAffiliateCodePayload(AffiliateCode $affiliateCode): array
    {
        return [
            'id' => $affiliateCode->id,
            'code' => $affiliateCode->code,
            'label' => $affiliateCode->label,
            'discount_method' => $affiliateCode->discount_method,
            'discount_value' => (float) $affiliateCode->discount_value,
            'discount_label' => $affiliateCode->discount_label,
        ];
    }

    private function unitPriceFromLoadedRelations(AffiliateCode $affiliateCode, string $type, int $recordId): float
    {
        return match ($type) {
            'product' => (float) ($affiliateCode->products->firstWhere('id', $recordId)?->final_price ?? 0),
            'service' => (float) ($affiliateCode->services->firstWhere('id', $recordId)?->promo_price
                ?? $affiliateCode->services->firstWhere('id', $recordId)?->price
                ?? 0),
            'package' => (float) ($affiliateCode->treatmentPackages->firstWhere('id', $recordId)?->price ?? 0),
            'membership' => (float) ($affiliateCode->membershipPlans->firstWhere('id', $recordId)?->price ?? 0),
            default => 0.0,
        };
    }

    /**
     * @param  array<string, mixed>  $line
     * @return array<string, mixed>
     */
    private function enrichLineWithCatalog(array $line, AffiliateCode $affiliateCode): array
    {
        $type = (string) $line['type'];
        $recordId = (int) $line['id'];
        $unitPrice = (float) $line['unit_price'];

        $catalog = match ($type) {
            'product' => $this->catalogFieldsForProduct($affiliateCode->products->firstWhere('id', $recordId)),
            'service' => $this->catalogFieldsForService($affiliateCode->services->firstWhere('id', $recordId)),
            'package' => $this->catalogFieldsForPackage($affiliateCode->treatmentPackages->firstWhere('id', $recordId)),
            'membership' => $this->catalogFieldsForMembership($affiliateCode->membershipPlans->firstWhere('id', $recordId)),
            default => [],
        };

        return array_merge($catalog, $line, [
            'price' => $unitPrice,
            'selected' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function catalogFieldsForProduct(?Product $product): array
    {
        if (! $product) {
            return ['type' => 'product', 'name' => 'Product'];
        }

        return [
            'type' => 'product',
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'stock_quantity' => (int) $product->stock_quantity,
            'unit' => $product->unit,
            'stock_status' => $product->stock_status,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function catalogFieldsForService(?Service $service): array
    {
        if (! $service) {
            return ['type' => 'service', 'name' => 'Service'];
        }

        return [
            'type' => 'service',
            'id' => $service->id,
            'name' => $service->name,
            'duration_minutes' => $service->duration_minutes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function catalogFieldsForPackage(?TreatmentPackage $package): array
    {
        if (! $package) {
            return ['type' => 'package', 'name' => 'Package'];
        }

        return [
            'type' => 'package',
            'id' => $package->id,
            'name' => $package->name,
            'validity_label' => $package->validity_label,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function catalogFieldsForMembership(?MembershipPlan $plan): array
    {
        if (! $plan) {
            return ['type' => 'membership', 'name' => 'Membership'];
        }

        return [
            'type' => 'membership',
            'id' => $plan->id,
            'name' => $plan->name,
            'duration_label' => $plan->duration_label,
        ];
    }
}
