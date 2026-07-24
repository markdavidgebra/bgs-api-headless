<?php

use App\Http\Controllers\Api\DoctorPortalController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\PatientPortalController;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Doctor\DoctorAppointmentController;
use App\Http\Controllers\Doctor\DoctorAvailabilityController;
use App\Http\Controllers\Doctor\DoctorDashboardController;
use App\Http\Controllers\Doctor\DoctorNotificationController;
use App\Http\Controllers\Doctor\DoctorPatientRecordController;
use App\Http\Controllers\Doctor\DoctorProductInventoryController;
use App\Http\Controllers\Doctor\DoctorProfileController;
use App\Http\Controllers\Doctor\DoctorServiceController;
use App\Http\Controllers\Doctor\DoctorTreatmentNoteController;
use App\Http\Controllers\Frontend\ContactInquiryController;
use App\Http\Controllers\Frontend\FrontEndController;
use App\Http\Controllers\Patient\PatientAftercareInstructionController;
use App\Http\Controllers\Patient\PatientAppointmentController;
use App\Http\Controllers\Patient\PatientDashboardController;
use App\Http\Controllers\Patient\PatientMembershipController;
use App\Http\Controllers\Patient\PatientNotificationController;
use App\Http\Controllers\Patient\PatientPackageController;
use App\Http\Controllers\Patient\PatientPaymentController;
use App\Http\Controllers\Patient\PatientProfileController;
use App\Http\Controllers\Patient\PatientPromotionController;
use App\Http\Controllers\Patient\PatientTreatmentController;
use App\Http\Middleware\EnsureDoctorPortalPermission;
use Illuminate\Support\Facades\Route;

/*
*==========================
* Frontend Routes
*==========================
*/

Route::get('/', [FrontEndController::class, 'index'])->name('home');
Route::get('/about-us', [FrontEndController::class, 'about'])->name('about');
Route::get('/appointment', [FrontEndController::class, 'appointment'])->name('appointment');
// Route::get('/blog', [FrontEndController::class, 'blog'])->name('blog');
// Route::get('/blog-carousel', [FrontEndController::class, 'blogCarousel'])->name('blog-carousel');
Route::get('/blog', [FrontEndController::class, 'blog'])->name('blog');
Route::get('/blog/{blog:slug}', [FrontEndController::class, 'blogShow'])->name('blog.show');
// Route::get('/blog-list-2', [FrontEndController::class, 'blogList2'])->name('blog-list-2');
Route::get('/cart', [FrontEndController::class, 'cart'])->name('cart');
Route::get('/checkout', [FrontEndController::class, 'checkout'])->name('checkout');
Route::get('/contact', [FrontEndController::class, 'contact'])->name('contact');
Route::post('/contact', [ContactInquiryController::class, 'store'])->name('contact.inquiry.store');
Route::get('/doctor', [FrontEndController::class, 'doctor'])->name('doctor');
Route::get('/doctor-carousel', [FrontEndController::class, 'doctorCarousel'])->name('doctor-carousel');
Route::get('/doctor-details', [FrontEndController::class, 'doctorDetails'])->name('doctor-details');
Route::get('/evergreen-medical-center', [FrontEndController::class, 'evergreenMedicalCenter'])->name('evergreen-medical-center');
Route::get('/faq', [FrontEndController::class, 'faq'])->name('faq');
Route::get('/harmony-family-health-medical', [FrontEndController::class, 'harmonyFamilyHealthMedical'])->name('harmony-family-health-medical');
Route::get('/pricing', [FrontEndController::class, 'pricing'])->name('pricing');
Route::get('/product-details', [FrontEndController::class, 'productDetails'])->name('product-details');
Route::get('/our-products', [FrontEndController::class, 'products'])->name('our-products');
Route::get('/our-products/{product:slug}', [FrontEndController::class, 'productShow'])->name('our-products.show');
Route::get('/pure-life-health-services', [FrontEndController::class, 'pureLifeHealthServices'])->name('pure-life-health-services');
Route::get('/service-carousel', [FrontEndController::class, 'serviceCarousel'])->name('service-carousel');
Route::get('/our-services', [FrontEndController::class, 'ourServices'])->name('our-services');
Route::get('/our-packages', [FrontEndController::class, 'packages'])->name('our-packages');
Route::get('/our-packages/{package:slug}', [FrontEndController::class, 'packageShow'])->name('our-packages.show');
Route::get('/services/{service:slug}', [FrontEndController::class, 'serviceShow'])->name('services.show');
Route::get('/sign-up', [FrontEndController::class, 'signUp'])->name('sign-up');
Route::get('/testimonial-carousel', [FrontEndController::class, 'testimonialCarousel'])->name('testimonial-carousel');
Route::get('/our-testimonials', [FrontEndController::class, 'testimonials'])->name('testimonials');
Route::get('/our-testimonials/{testimonial}', [FrontEndController::class, 'testimonialShow'])->name('testimonials.show');
Route::get('/vitality-health-solutions', [FrontEndController::class, 'vitalityHealthSolutions'])->name('vitality-health-solutions');
Route::get('/wellSpring-wellness-center', [FrontEndController::class, 'wellSpringWellnessCenter'])->name('wellSpring-wellness-center');
Route::get('/wishlist', [FrontEndController::class, 'wishlist'])->name('wishlist');

// POS route aliases for external React apps (mirror routes/api.php but without /api prefix).
// With Vite devServer.proxy → these hit Laravel at /pos/catalog, etc.
Route::middleware('throttle:10,1')->post('/pos/login', [PosController::class, 'login'])->name('pos/login');
Route::middleware(['auth:admin', 'admin_role:admin,cashier'])->prefix('pos')->group(function () {
    Route::post('/logout', [PosController::class, 'logout'])->name('pos/logout');
    Route::get('/me', [PosController::class, 'me'])->name('pos/me');
    Route::get('/catalog', [PosController::class, 'catalog'])->name('pos/catalog');
    Route::get('/patients', [PosController::class, 'patients'])->name('pos/patients');
    Route::get('/promotions', [PosController::class, 'promotions'])->name('pos/promotions');
    Route::post('/affiliate-codes/validate', [PosController::class, 'validateAffiliateCode'])->name('pos/affiliate-codes/validate');
    Route::post('/checkout', [PosController::class, 'checkout'])->name('pos/checkout');
});

/*
|--------------------------------------------------------------------------
| POS JSON API (/api/pos/*)
|--------------------------------------------------------------------------
|
| Registered here (not only in routes/api.php) so these endpoints always load
| with the web routes file. A missing or outdated api routes file / route cache
| was causing 404 "The route api/pos/affiliate-codes/validate could not be found."
| for the React POS while /pos/* mirrors still worked.
|
*/
Route::prefix('api')->group(function () {
    Route::middleware('throttle:10,1')->prefix('pos')->group(function () {
        Route::post('login', [PosController::class, 'login']);
    });

    Route::middleware(['auth:admin', 'admin_role:admin,cashier'])
        ->prefix('pos')
        ->group(function () {
            Route::get('me', [PosController::class, 'me']);
            Route::post('logout', [PosController::class, 'logout']);
            Route::get('catalog', [PosController::class, 'catalog']);
            Route::get('patients', [PosController::class, 'patients']);
            Route::get('promotions', [PosController::class, 'promotions']);
            Route::post('affiliate-codes/validate', [PosController::class, 'validateAffiliateCode']);
            Route::post('checkout', [PosController::class, 'checkout']);
        });
});

/*
|--------------------------------------------------------------------------
| Doctor portal JSON API (/api/doctor/*)
|--------------------------------------------------------------------------
|
| Session-based API for a React doctor portal (mirrors /doctor/* web routes).
|
*/
Route::prefix('api')->group(function () {
    Route::get('doctor/clinical-images/{appointment}/{type}', [DoctorPortalController::class, 'clinicalImage'])
        ->middleware('signed')
        ->name('doctor.clinical-image');

    Route::middleware('throttle:10,1')->prefix('doctor')->group(function () {
        Route::post('login', [DoctorPortalController::class, 'login']);
    });

    Route::middleware([
        'prevent_cross_guard:doctor',
        'auth:doctor',
        'doctor_approved',
        'verified',
    ])->prefix('doctor')->group(function () {
        Route::post('logout', [DoctorPortalController::class, 'logout']);
        Route::get('me', [DoctorPortalController::class, 'me']);

        Route::middleware('doctor.permission:doctor.dashboard')->get('dashboard', [DoctorPortalController::class, 'dashboard']);

        Route::middleware('doctor.permission:doctor.appointments')->group(function () {
            Route::get('appointments', [DoctorPortalController::class, 'appointmentsIndex']);
            Route::get('appointments/{appointment}', [DoctorPortalController::class, 'appointmentShow']);
            Route::get('appointments/{appointment}/notes-form', [DoctorPortalController::class, 'appointmentNotesForm']);
            Route::post('appointments/{appointment}/approve', [DoctorPortalController::class, 'appointmentApprove']);
            Route::post('appointments/{appointment}/start-session', [DoctorPortalController::class, 'appointmentStartSession']);
            Route::post('appointments/{appointment}/complete', [DoctorPortalController::class, 'appointmentComplete']);
            Route::post('appointments/{appointment}/session-done', [DoctorPortalController::class, 'appointmentSessionDone']);
            Route::post('appointments/{appointment}/no-show', [DoctorPortalController::class, 'appointmentNoShow']);
            Route::post('appointments/{appointment}/reschedule', [DoctorPortalController::class, 'appointmentReschedule']);
            Route::post('appointments/{appointment}/treatment-progress', [DoctorPortalController::class, 'appointmentTreatmentProgress']);
            Route::post('appointments/{appointment}/notes', [DoctorPortalController::class, 'appointmentNotes']);
            Route::post('appointments/{appointment}/assessment', [DoctorPortalController::class, 'appointmentAssessment']);
            Route::post('appointments/{appointment}/consent', [DoctorPortalController::class, 'appointmentConsent']);
        });

        Route::middleware('doctor.permission:doctor.patient_records')->prefix('patient-records')->group(function () {
            Route::get('/', [DoctorPortalController::class, 'patientRecordsIndex']);
            Route::get('{patient}', [DoctorPortalController::class, 'patientRecordShow']);
            Route::post('{patient}/notes', [DoctorPortalController::class, 'patientRecordStoreNote']);
            Route::patch('{patient}/packages/{patientPackage}/sessions', [DoctorPortalController::class, 'patientRecordUpdatePackageSessions']);
        });

        Route::middleware('doctor.permission:doctor.treatment_notes')->prefix('treatment-notes')->group(function () {
            Route::get('/', [DoctorPortalController::class, 'treatmentNotesIndex']);
            Route::get('{appointment}', [DoctorPortalController::class, 'treatmentNoteShow']);
        });

        Route::middleware('doctor.permission:doctor.notifications')->prefix('notifications')->group(function () {
            Route::get('/', [DoctorPortalController::class, 'notificationsIndex']);
            Route::post('mark-all-read', [DoctorPortalController::class, 'notificationsMarkAllRead']);
            Route::post('clear-read', [DoctorPortalController::class, 'notificationsClearRead']);
            Route::get('{notification}', [DoctorPortalController::class, 'notificationShow']);
            Route::patch('{notification}/read', [DoctorPortalController::class, 'notificationMarkRead']);
            Route::delete('{notification}', [DoctorPortalController::class, 'notificationDestroy']);
        });

        Route::middleware('doctor.permission:doctor.products')->get('products', [DoctorPortalController::class, 'productsIndex']);
        Route::middleware('doctor.permission:doctor.services')->get('services', [DoctorPortalController::class, 'servicesIndex']);

        Route::middleware('doctor.permission:doctor.profile')->group(function () {
            Route::get('profile', [DoctorPortalController::class, 'profileShow']);
            Route::patch('profile', [DoctorPortalController::class, 'profileUpdate']);
            Route::put('profile/password', [DoctorPortalController::class, 'profileUpdatePassword']);
        });

        Route::middleware('doctor.permission:doctor.availability')->prefix('availability')->group(function () {
            Route::get('/', [DoctorPortalController::class, 'availabilityIndex']);
            Route::get('weekday/{weekday}', [DoctorPortalController::class, 'availabilityWeekday'])->whereNumber('weekday');
            Route::patch('weekday/{weekday}', [DoctorPortalController::class, 'availabilityUpdateWeekday'])->whereNumber('weekday');
            Route::post('weekday/{weekday}/toggle', [DoctorPortalController::class, 'availabilityToggleDay'])->whereNumber('weekday');
            Route::post('blocked-dates', [DoctorPortalController::class, 'availabilityStoreBlockedDate']);
            Route::delete('blocked-dates/{blockedDate}', [DoctorPortalController::class, 'availabilityDestroyBlockedDate']);
        });
    });
});

/*
|--------------------------------------------------------------------------
| Inventory officer JSON API (/api/inventory/*)
|--------------------------------------------------------------------------
|
| Session-based API for the React inventory officer portal (gbs-inventory).
|
*/
Route::prefix('api')->group(function () {
    Route::middleware('throttle:10,1')->prefix('inventory')->group(function () {
        Route::post('login', [InventoryController::class, 'login']);
        Route::get('me', [InventoryController::class, 'me']);
    });

    Route::middleware(['auth:admin', 'admin_role:inventory_officer'])
        ->prefix('inventory')
        ->group(function () {
            Route::post('logout', [InventoryController::class, 'logout']);
            Route::patch('profile', [InventoryController::class, 'updateProfile']);
            Route::put('profile/password', [InventoryController::class, 'updatePassword']);
            Route::get('summary', [InventoryController::class, 'summary']);
            Route::get('products', [InventoryController::class, 'products']);
            Route::get('products/{id}', [InventoryController::class, 'showProduct'])->whereNumber('id');
            Route::get('low-stock', [InventoryController::class, 'lowStock']);
            Route::get('movements', [InventoryController::class, 'movements']);
            Route::post('stock-movements', [InventoryController::class, 'storeMovement']);
        });
});

/*
|--------------------------------------------------------------------------
| Patient portal JSON API (/api/patient/*)
|--------------------------------------------------------------------------
|
| Session-based API for the React patient portal (mirrors /patient/* web routes).
|
*/
Route::prefix('api')->group(function () {
    Route::middleware('throttle:10,1')->prefix('patient')->group(function () {
        Route::post('login', [PatientPortalController::class, 'login']);
        Route::post('forgot-password', [PatientPortalController::class, 'forgotPassword']);
        Route::post('reset-password', [PatientPortalController::class, 'resetPassword']);
    });

    Route::middleware(['prevent_cross_guard:web', 'auth:web', 'verified'])
        ->prefix('patient')
        ->group(function () {
            Route::post('logout', [PatientPortalController::class, 'logout']);
            Route::get('me', [PatientPortalController::class, 'me']);
            Route::get('dashboard', [PatientPortalController::class, 'dashboard']);

            Route::get('appointments', [PatientPortalController::class, 'appointments']);
            Route::get('appointments/book', [PatientPortalController::class, 'bookOptions']);
            Route::get('appointments/book/doctors', [PatientPortalController::class, 'bookableDoctors']);
            Route::post('appointments/book', [PatientPortalController::class, 'bookAppointment']);
            Route::get('appointments/{appointment}', [PatientPortalController::class, 'appointment']);
            Route::post('appointments/{appointment}/reschedule', [PatientPortalController::class, 'rescheduleAppointment']);
            Route::post('appointments/{appointment}/cancel', [PatientPortalController::class, 'cancelAppointment']);

            Route::get('notifications', [PatientPortalController::class, 'notifications']);
            Route::post('notifications/read-all', [PatientPortalController::class, 'markAllNotificationsRead']);
            Route::post('notifications/{notification}/read', [PatientPortalController::class, 'markNotificationRead']);

            Route::get('profile', [PatientPortalController::class, 'profile']);
            Route::post('profile', [PatientPortalController::class, 'updateProfile']);
            Route::post('profile/avatar', [PatientPortalController::class, 'uploadAvatar']);
            Route::post('profile/avatar/remove', [PatientPortalController::class, 'removeAvatar']);

            Route::get('treatments', [PatientPortalController::class, 'treatments']);
            Route::get('treatments/{patientPackage}', [PatientPortalController::class, 'treatment']);

            Route::get('memberships', [PatientPortalController::class, 'memberships']);

            Route::get('payments', [PatientPortalController::class, 'payments']);
            Route::get('payments/{payment}', [PatientPortalController::class, 'payment'])->whereNumber('payment');

            Route::get('promotions', [PatientPortalController::class, 'promotions']);
            Route::get('promotions/{promotion}', [PatientPortalController::class, 'promotion'])->whereNumber('promotion');

            Route::get('aftercare-instructions', [PatientPortalController::class, 'aftercare']);
            Route::get('aftercare-instructions/{source}/{record}', [PatientPortalController::class, 'aftercareItem'])
                ->whereNumber('record');
        });
});

/*
*==========================
* Patient portal
*==========================
*/

Route::group(['middleware' => ['prevent_cross_guard:web', 'auth:web', 'verified'], 'prefix' => 'patient', 'as' => 'patient.'], function () {
    Route::get('/dashboard', [PatientDashboardController::class, 'index'])->name('dashboard');
    Route::get('/notifications', [PatientNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [PatientNotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [PatientNotificationController::class, 'markRead'])->name('notifications.read');
    Route::get('/appointments', [PatientAppointmentController::class, 'index'])->name('appointments');
    Route::get('/appointments/book', [PatientAppointmentController::class, 'book'])->name('appointments.book');
    Route::get('/appointments/book/doctors', [PatientAppointmentController::class, 'doctorsForBookingDate'])->name('appointments.book.doctors');
    Route::post('/appointments/book', [PatientAppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/appointments/{appointment}', [PatientAppointmentController::class, 'show'])->name('appointments.show');
    Route::get('/appointments/{appointment}/reschedule', [PatientAppointmentController::class, 'editReschedule'])->name('appointments.reschedule');
    Route::post('/appointments/{appointment}/reschedule', [PatientAppointmentController::class, 'updateReschedule'])->name('appointments.reschedule.update');
    Route::post('/appointments/{appointment}/cancel', [PatientAppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::get('/packages', [PatientPackageController::class, 'index'])->name('packages');
    Route::get('/memberships', [PatientMembershipController::class, 'index'])->name('memberships');
    Route::get('/payments', [PatientPaymentController::class, 'index'])->name('payments');
    Route::get('/payments/{payment}', [PatientPaymentController::class, 'show'])->name('payments.show');
    Route::get('/promotions', [PatientPromotionController::class, 'index'])->name('promotions');
    Route::get('/promotions/{promotion}', [PatientPromotionController::class, 'show'])->name('promotions.show');
    Route::get('/profile', [PatientProfileController::class, 'index'])->name('profile');
    Route::patch('/profile', [PatientProfileController::class, 'update'])->name('profile.update');
    Route::get('/treatments', [PatientTreatmentController::class, 'index'])->name('treatments');
    Route::get('/treatments/{patientPackage}', [PatientTreatmentController::class, 'show'])->name('treatments.show');
    Route::get('/aftercare-instructions', [PatientAftercareInstructionController::class, 'index'])->name('aftercare-instructions');
    Route::get('/aftercare-instructions/{source}/{record}', [PatientAftercareInstructionController::class, 'show'])->name('aftercare-instructions.show');
});

/*
*==========================
* Doctor Dashboard Route
*==========================
*/

Route::group(['middleware' => ['prevent_cross_guard:doctor', 'auth:doctor', 'doctor_approved', EnsureDoctorPortalPermission::class, 'verified'], 'prefix' => 'doctor', 'as' => 'doctor.'], function () {
    Route::get('/dashboard', [DoctorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/products', [DoctorProductInventoryController::class, 'index'])->name('products');
    Route::get('/services', [DoctorServiceController::class, 'index'])->name('services');
    Route::get('/appointments', [DoctorAppointmentController::class, 'index'])->name('appointments');
    Route::get('/appointments/{appointment}/notes/create', [DoctorAppointmentController::class, 'createNotes'])->name('appointments.notes.create');
    Route::get('/appointments/{appointment}', [DoctorAppointmentController::class, 'show'])->name('appointments.show');
    Route::get('/patient-records', [DoctorPatientRecordController::class, 'index'])->name('patient-records');
    Route::get('/patient-records/{patient}', [DoctorPatientRecordController::class, 'show'])->name('patient-records.show');
    Route::post('/patient-records/{patient}/notes', [DoctorPatientRecordController::class, 'storeNote'])->name('patient-records.notes.store');
    Route::patch('/patient-records/{patient}/packages/{patientPackage}/sessions', [DoctorPatientRecordController::class, 'updatePatientPackageSessions'])->name('patient-records.packages.sessions.update');
    Route::get('/treatment-notes', [DoctorTreatmentNoteController::class, 'index'])->name('treatment-notes');
    Route::get('/treatment-notes/{appointment}', [DoctorTreatmentNoteController::class, 'show'])->name('treatment-notes.show');
    Route::get('/notifications', [DoctorNotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/mark-all-read', [DoctorNotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/clear-read', [DoctorNotificationController::class, 'clearRead'])->name('notifications.clear-read');
    Route::get('/notifications/{notification}', [DoctorNotificationController::class, 'show'])->name('notifications.show');
    Route::patch('/notifications/{notification}/read', [DoctorNotificationController::class, 'markRead'])->name('notifications.read');
    Route::delete('/notifications/{notification}', [DoctorNotificationController::class, 'destroy'])->name('notifications.destroy');

    Route::get('/profile', [DoctorProfileController::class, 'edit'])->name('profile');
    Route::patch('/profile', [DoctorProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [DoctorProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('/availability', [DoctorAvailabilityController::class, 'index'])->name('availability');
    Route::get('/availability/day/{weekday}/edit', [DoctorAvailabilityController::class, 'editWeekday'])->name('availability.day.edit')->whereNumber('weekday');
    Route::patch('/availability/day/{weekday}', [DoctorAvailabilityController::class, 'updateWeekday'])->name('availability.day.update')->whereNumber('weekday');
    Route::post('/availability/day/{weekday}/toggle', [DoctorAvailabilityController::class, 'toggleDay'])->name('availability.toggle')->whereNumber('weekday');
    Route::post('/availability/blocked-dates', [DoctorAvailabilityController::class, 'storeBlockedDate'])->name('availability.blocked.store');
    Route::delete('/availability/blocked-dates/{blockedDate}', [DoctorAvailabilityController::class, 'destroyBlockedDate'])->name('availability.blocked.destroy');
    Route::post('/appointments/{appointment}/approve', [DoctorAppointmentController::class, 'approve'])->name('appointments.approve');
    Route::match(['get', 'post'], '/appointments/{appointment}/start-session', [DoctorAppointmentController::class, 'startSession'])->name('appointments.start-session');
    Route::post('/appointments/{appointment}/complete', [DoctorAppointmentController::class, 'markCompleted'])->name('appointments.complete');
    Route::post('/appointments/{appointment}/session-done', [DoctorAppointmentController::class, 'updateSessionDone'])->name('appointments.session-done');
    Route::post('/appointments/{appointment}/treatment-progress', [DoctorAppointmentController::class, 'updateTreatmentProgress'])->name('appointments.treatment-progress');
    Route::post('/appointments/{appointment}/notes', [DoctorAppointmentController::class, 'addNotes'])->name('appointments.notes');
    Route::post('/appointments/{appointment}/notes/assessment', [DoctorAppointmentController::class, 'updateAssessmentChecklist'])->name('appointments.notes.assessment');
    Route::post('/appointments/{appointment}/reschedule', [DoctorAppointmentController::class, 'reschedule'])->name('appointments.reschedule');
    Route::post('/appointments/{appointment}/mark-no-show', [DoctorAppointmentController::class, 'markNoShow'])->name('appointments.mark-no-show');
});

/*
*==========================
* Admin Dashboard Route
*==========================
*/

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/admin-api.php';
require __DIR__.'/public-api.php';
