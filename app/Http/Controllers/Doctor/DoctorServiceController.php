<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class DoctorServiceController extends Controller
{
    public function index(): View
    {
        $doctor = auth('doctor')->user();

        $services = $doctor
            ?->services()
            ->orderBy('name')
            ->get() ?? collect();

        $activeCount = $services->where('status', 'active')->count();
        $avgPrice = (float) $services
            ->map(fn ($service) => $service->promo_price ?? $service->price)
            ->filter(fn ($price) => $price !== null)
            ->avg();

        return view('doctor.services.index', [
            'services' => $services,
            'activeCount' => $activeCount,
            'inactiveCount' => max(0, $services->count() - $activeCount),
            'avgPrice' => $avgPrice,
        ]);
    }
}
