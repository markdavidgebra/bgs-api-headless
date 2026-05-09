<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Contracts\View\View;

class PatientPromotionController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();

        $promotions = Promotion::query()
            ->where('status', 'active')
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')->orWhereDate('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
            })
            ->orderByDesc('discount_value')
            ->orderBy('end_date')
            ->get();

        $featuredPromo = $promotions->first();

        return view('patient.promotions.index', [
            'promotions' => $promotions,
            'featuredPromo' => $featuredPromo,
        ]);
    }

    public function show(int $promotion): View
    {
        $today = now()->toDateString();

        $promotionRecord = Promotion::query()
            ->whereKey($promotion)
            ->where('status', 'active')
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')->orWhereDate('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
            })
            ->with(['services', 'treatmentPackages', 'membershipPlans', 'products'])
            ->firstOrFail();

        return view('patient.promotions.show', [
            'promotion' => $promotionRecord,
        ]);
    }
}
