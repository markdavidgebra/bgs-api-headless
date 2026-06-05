<?php

use App\Http\Controllers\Api\PatientCatalogController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:30,1')->group(function () {
    Route::get('catalog', [PatientCatalogController::class, 'index']);
});

/*
| Public frontend (/api/public/*), POS /api/pos/*, and doctor /api/doctor/* routes are
| defined in routes/web.php so they always register with the primary route file
| (SPA + Laragon + partial deploys) and are not double-prefixed by withRouting(api: ...).
*/
