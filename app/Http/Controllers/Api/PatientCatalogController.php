<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use App\Models\Product;
use App\Models\Service;
use App\Models\TreatmentPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientCatalogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $type = $request->string('type')->toString();
        $search = trim($request->string('search')->toString());
        $limit = max(1, min((int) $request->integer('limit', 50), 200));

        $payload = [];

        if ($type === '' || $type === 'product') {
            $payload['products'] = Product::query()
                ->where('status', 'active')
                ->where('is_available_for_sale', true)
                ->when($search !== '', function ($q) use ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
                })
                ->orderBy('name')
                ->limit($limit)
                ->get()
                ->map(fn (Product $p) => [
                    'type' => 'product',
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'sku' => $p->sku,
                    'description' => $p->description,
                    'category' => $p->category,
                    'price' => (float) $p->final_price,
                    'image_url' => $p->image_url,
                    'stock_status' => $p->stock_status,
                ])
                ->values();
        }

        if ($type === '' || $type === 'service') {
            $payload['services'] = Service::query()
                ->where('status', 'active')
                ->where('is_bookable', true)
                ->when($search !== '', function ($q) use ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('short_description', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
                })
                ->orderBy('name')
                ->limit($limit)
                ->get()
                ->map(fn (Service $s) => [
                    'type' => 'service',
                    'id' => $s->id,
                    'name' => $s->name,
                    'slug' => $s->slug,
                    'description' => $s->description,
                    'price' => (float) ($s->promo_price ?? $s->price ?? 0),
                    'duration_minutes' => $s->duration_minutes,
                    'image_url' => $s->image_url,
                ])
                ->values();
        }

        if ($type === '' || $type === 'package') {
            $payload['packages'] = TreatmentPackage::query()
                ->with(['services' => fn ($q) => $q->select('services.id', 'services.name')])
                ->where('status', 'active')
                ->when($search !== '', function ($q) use ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
                })
                ->orderBy('name')
                ->limit($limit)
                ->get()
                ->map(fn (TreatmentPackage $p) => [
                    'type' => 'package',
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'description' => $p->description,
                    'price' => (float) ($p->price ?? 0),
                    'validity_label' => $p->validity_label,
                    'image_url' => $p->image_url,
                    'included_services' => $p->services
                        ->map(fn (Service $service) => [
                            'id' => $service->id,
                            'name' => $service->name,
                            'sessions' => (int) ($service->pivot->sessions ?? 0),
                        ])
                        ->values(),
                ])
                ->values();
        }

        if ($type === '' || $type === 'membership') {
            $payload['memberships'] = MembershipPlan::query()
                ->with(['services' => fn ($q) => $q->select('services.id', 'services.name')])
                ->where('status', 'active')
                ->when($search !== '', function ($q) use ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhere('terms_and_conditions', 'like', "%{$search}%");
                    });
                })
                ->orderBy('name')
                ->limit($limit)
                ->get()
                ->map(fn (MembershipPlan $m) => [
                    'type' => 'membership',
                    'id' => $m->id,
                    'name' => $m->name,
                    'slug' => $m->slug,
                    'description' => $m->description,
                    'price' => (float) ($m->price ?? 0),
                    'duration_label' => $m->duration_label,
                    'image_url' => $m->image_url,
                    'included_services' => $m->services
                        ->map(fn (Service $service) => [
                            'id' => $service->id,
                            'name' => $service->name,
                            'sessions' => (int) ($service->pivot->sessions ?? 0),
                        ])
                        ->values(),
                ])
                ->values();
        }

        return response()->json($payload);
    }
}
