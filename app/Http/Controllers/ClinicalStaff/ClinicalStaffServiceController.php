<?php

namespace App\Http\Controllers\ClinicalStaff;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Contracts\View\View;

class ClinicalStaffServiceController extends Controller
{
    public function index(): View
    {
        $services = Service::query()->orderBy('name')->get();

        $activeCount = $services->where('status', 'active')->count();

        return view('clinical-staff.services.index', [
            'services' => $services,
            'activeCount' => $activeCount,
            'inactiveCount' => max(0, $services->count() - $activeCount),
        ]);
    }
}
