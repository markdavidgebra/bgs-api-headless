<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class PatientPackageController extends Controller
{
    public function index(): View
    {
        return view('patient.placeholder', [
            'title' => 'My packages',
            'breadcrumb' => 'Packages',
            'heading' => 'Treatment packages',
            'message' => 'Purchased packages and sessions will be listed here when available.',
        ]);
    }
}
