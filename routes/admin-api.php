<?php

use App\Http\Controllers\Api\Admin\AdminAboutsController;
use App\Http\Controllers\Api\Admin\AdminAffiliateCodesController;
use App\Http\Controllers\Api\Admin\AdminAppointmentsController;
use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\AdminBlogsController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\AdminDoctorRolesController;
use App\Http\Controllers\Api\Admin\AdminDoctorsController;
use App\Http\Controllers\Api\Admin\AdminFaqsController;
use App\Http\Controllers\Api\Admin\AdminFooterSettingsController;
use App\Http\Controllers\Api\Admin\AdminInquiriesController;
use App\Http\Controllers\Api\Admin\AdminNavBadgesController;
use App\Http\Controllers\Api\Admin\AdminPackagesController;
use App\Http\Controllers\Api\Admin\AdminPageHeadersController;
use App\Http\Controllers\Api\Admin\AdminPatientsController;
use App\Http\Controllers\Api\Admin\AdminPaymentsController;
use App\Http\Controllers\Api\Admin\AdminProductsController;
use App\Http\Controllers\Api\Admin\AdminProfileController;
use App\Http\Controllers\Api\Admin\AdminPromotionsController;
use App\Http\Controllers\Api\Admin\AdminRegistrationsController;
use App\Http\Controllers\Api\Admin\AdminReportsController;
use App\Http\Controllers\Api\Admin\AdminRolesController;
use App\Http\Controllers\Api\Admin\AdminServicesController;
use App\Http\Controllers\Api\Admin\AdminSettingsController;
use App\Http\Controllers\Api\Admin\AdminSlidesController;
use App\Http\Controllers\Api\Admin\AdminStaffController;
use App\Http\Controllers\Api\Admin\AdminSubscriptionsController;
use App\Http\Controllers\Api\Admin\AdminTestimonialsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin portal JSON API (/api/admin/*)
|--------------------------------------------------------------------------
|
| Session-based API for the React admin dashboard (mirrors routes/admin.php).
|
*/
Route::prefix('api/admin')->group(function () {
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('login', [AdminAuthController::class, 'login']);
        Route::post('forgot-password', [AdminAuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AdminAuthController::class, 'resetPassword']);
    });

    Route::middleware(['prevent_cross_guard:admin', 'auth:admin', 'admin_approved'])->group(function () {
        Route::get('me', [AdminAuthController::class, 'me']);
        Route::post('logout', [AdminAuthController::class, 'logout']);

        Route::get('profile', [AdminProfileController::class, 'show']);
        Route::patch('profile', [AdminProfileController::class, 'update']);
        Route::put('profile/password', [AdminProfileController::class, 'updatePassword']);

        Route::middleware('admin_permission:dashboard.view')
            ->get('dashboard', [AdminDashboardController::class, 'index']);

        Route::get('nav-badges', [AdminNavBadgesController::class, 'index']);

        Route::middleware('admin_permission:appointments.manage')->prefix('appointments')->group(function () {
            Route::get('/', [AdminAppointmentsController::class, 'index']);
            Route::post('/', [AdminAppointmentsController::class, 'store']);
            Route::get('calendar', [AdminAppointmentsController::class, 'calendar']);
            Route::get('book-options', [AdminAppointmentsController::class, 'bookOptions']);
            Route::get('bookable-doctors', [AdminAppointmentsController::class, 'bookableDoctors']);
            Route::get('{id}', [AdminAppointmentsController::class, 'show'])->whereNumber('id');
        });

        Route::middleware('admin_permission:inquiries.manage')->prefix('inquiries')->group(function () {
            Route::get('/', [AdminInquiriesController::class, 'index']);
            Route::get('{id}', [AdminInquiriesController::class, 'show']);
            Route::delete('{id}', [AdminInquiriesController::class, 'destroy']);
        });

        Route::middleware('admin_permission:registrations.manage')->prefix('registrations')->group(function () {
            Route::get('/', [AdminRegistrationsController::class, 'index']);
            Route::post('{id}/approve', [AdminRegistrationsController::class, 'approve'])->whereNumber('id');
            Route::post('{id}/disapprove', [AdminRegistrationsController::class, 'disapprove'])->whereNumber('id');
        });

        Route::middleware('admin_permission:staff.manage')->prefix('staff')->group(function () {
            Route::get('/', [AdminStaffController::class, 'index']);
            Route::get('create', [AdminStaffController::class, 'create']);
            Route::post('/', [AdminStaffController::class, 'store']);
            Route::get('{id}', [AdminStaffController::class, 'show'])->whereNumber('id');
            Route::get('{id}/edit', [AdminStaffController::class, 'edit'])->whereNumber('id');
            Route::put('{id}', [AdminStaffController::class, 'update'])->whereNumber('id');
            Route::delete('{id}', [AdminStaffController::class, 'destroy'])->whereNumber('id');
            Route::post('{id}/status', [AdminStaffController::class, 'updateStatus'])->whereNumber('id');
        });

        Route::middleware('admin_permission:doctors.manage')->group(function () {
            Route::prefix('doctor-roles')->group(function () {
                Route::get('/', [AdminDoctorRolesController::class, 'index']);
                Route::get('create', [AdminDoctorRolesController::class, 'create']);
                Route::post('/', [AdminDoctorRolesController::class, 'store']);
                Route::get('{id}/edit', [AdminDoctorRolesController::class, 'edit'])->whereNumber('id');
                Route::put('{id}', [AdminDoctorRolesController::class, 'update'])->whereNumber('id');
            });

            Route::prefix('doctors')->group(function () {
                Route::get('/', [AdminDoctorsController::class, 'index']);
                Route::get('create', [AdminDoctorsController::class, 'create']);
                Route::post('/', [AdminDoctorsController::class, 'store']);
                Route::get('{id}', [AdminDoctorsController::class, 'show'])->whereNumber('id');
                Route::get('{id}/edit', [AdminDoctorsController::class, 'edit'])->whereNumber('id');
                Route::put('{id}', [AdminDoctorsController::class, 'update'])->whereNumber('id');
                Route::delete('{id}', [AdminDoctorsController::class, 'destroy'])->whereNumber('id');
                Route::post('{id}/status', [AdminDoctorsController::class, 'updateStatus'])->whereNumber('id');
                Route::post('{id}/role', [AdminDoctorsController::class, 'updateRole'])->whereNumber('id');
            });
        });

        Route::middleware('admin_permission:patients.view,patients.manage')->prefix('patients')->group(function () {
            Route::get('/', [AdminPatientsController::class, 'index']);
            Route::get('{id}', [AdminPatientsController::class, 'show'])->whereNumber('id');
        });

        Route::middleware('admin_permission:patients.manage')->prefix('patients')->group(function () {
            Route::get('create', [AdminPatientsController::class, 'create']);
            Route::post('/', [AdminPatientsController::class, 'store']);
            Route::get('{id}/edit', [AdminPatientsController::class, 'edit'])->whereNumber('id');
            Route::put('{id}', [AdminPatientsController::class, 'update'])->whereNumber('id');
            Route::delete('{id}', [AdminPatientsController::class, 'destroy'])->whereNumber('id');
            Route::post('{id}/status', [AdminPatientsController::class, 'updateStatus'])->whereNumber('id');
            Route::post('{id}/password', [AdminPatientsController::class, 'updatePassword'])->whereNumber('id');
            Route::post('{id}/password-reset-link', [AdminPatientsController::class, 'sendPasswordReset'])->whereNumber('id');
            Route::post('{id}/treatment-packages', [AdminPatientsController::class, 'storeTreatmentPackage'])->whereNumber('id');
            Route::put('{patient}/appointments/{appointment}/appointment-notes', [AdminPatientsController::class, 'upsertAppointmentNote'])->whereNumber(['patient', 'appointment']);
            Route::delete('{patient}/appointments/{appointment}/appointment-notes/field/{field}', [AdminPatientsController::class, 'clearAppointmentNoteField'])
                ->whereNumber(['patient', 'appointment'])
                ->where('field', 'patient_concern|doctor_notes|instructions|alerts|appointment_remarks|admin_notes');
            Route::delete('{patient}/appointments/{appointment}/appointment-notes', [AdminPatientsController::class, 'destroyAppointmentNote'])
                ->whereNumber(['patient', 'appointment']);
        });

        Route::middleware('admin_permission:roles.manage')->prefix('roles')->group(function () {
            Route::get('/', [AdminRolesController::class, 'index']);
            Route::get('create', [AdminRolesController::class, 'create']);
            Route::post('/', [AdminRolesController::class, 'store']);
            Route::get('{id}/edit', [AdminRolesController::class, 'edit'])->whereNumber('id');
            Route::put('{id}', [AdminRolesController::class, 'update'])->whereNumber('id');
            Route::delete('{id}', [AdminRolesController::class, 'destroy'])->whereNumber('id');
        });

        Route::middleware('admin_permission:services.manage')->prefix('services')->group(function () {
            Route::get('/', [AdminServicesController::class, 'index']);
            Route::get('create', [AdminServicesController::class, 'create']);
            Route::post('/', [AdminServicesController::class, 'store']);
            Route::get('edit/{id}', [AdminServicesController::class, 'edit']);
            Route::get('{id}', [AdminServicesController::class, 'show']);
            Route::match(['post', 'put'], '{id}', [AdminServicesController::class, 'update']);
            Route::delete('{id}', [AdminServicesController::class, 'destroy']);
        });

        Route::middleware('admin_permission:packages.manage')->prefix('packages')->group(function () {
            Route::get('/', [AdminPackagesController::class, 'index']);
            Route::get('create', [AdminPackagesController::class, 'create']);
            Route::post('/', [AdminPackagesController::class, 'store']);
            Route::get('edit/{id}', [AdminPackagesController::class, 'edit']);
            Route::get('{id}', [AdminPackagesController::class, 'show']);
            Route::match(['post', 'put'], '{id}', [AdminPackagesController::class, 'update']);
            Route::delete('{id}', [AdminPackagesController::class, 'destroy']);
        });

        Route::middleware('admin_permission:subscriptions.manage')->prefix('subscriptions')->group(function () {
            Route::get('/', [AdminSubscriptionsController::class, 'index']);
            Route::get('create', [AdminSubscriptionsController::class, 'create']);
            Route::post('/', [AdminSubscriptionsController::class, 'store']);
            Route::get('show/{id}', [AdminSubscriptionsController::class, 'show'])->whereNumber('id');
            Route::get('edit/{id}', [AdminSubscriptionsController::class, 'edit'])->whereNumber('id');
            Route::put('{id}', [AdminSubscriptionsController::class, 'update'])->whereNumber('id');
            Route::delete('{id}', [AdminSubscriptionsController::class, 'destroy'])->whereNumber('id');
        });

        Route::middleware('admin_permission:payments.manage')->prefix('payments')->group(function () {
            Route::get('/', [AdminPaymentsController::class, 'index']);
            Route::get('create', [AdminPaymentsController::class, 'create']);
            Route::post('/', [AdminPaymentsController::class, 'store']);
            Route::get('show/{id}', [AdminPaymentsController::class, 'show']);
        });

        Route::middleware('admin_permission:promotions.manage')->group(function () {
            Route::prefix('promotions')->group(function () {
                Route::get('/', [AdminPromotionsController::class, 'index']);
                Route::get('create', [AdminPromotionsController::class, 'create']);
                Route::post('/', [AdminPromotionsController::class, 'store']);
                Route::get('email', [AdminPromotionsController::class, 'emailForm']);
                Route::post('email', [AdminPromotionsController::class, 'sendEmail']);
                Route::get('show/{id}', [AdminPromotionsController::class, 'show'])->whereNumber('id');
                Route::get('edit/{id}', [AdminPromotionsController::class, 'edit'])->whereNumber('id');
                Route::put('{id}', [AdminPromotionsController::class, 'update'])->whereNumber('id');
                Route::delete('{id}', [AdminPromotionsController::class, 'destroy'])->whereNumber('id');
            });

            Route::prefix('affiliate-codes')->group(function () {
                Route::get('/', [AdminAffiliateCodesController::class, 'index']);
                Route::get('create', [AdminAffiliateCodesController::class, 'create']);
                Route::post('/', [AdminAffiliateCodesController::class, 'store']);
                Route::get('edit/{affiliateCode}', [AdminAffiliateCodesController::class, 'edit']);
                Route::put('{affiliateCode}', [AdminAffiliateCodesController::class, 'update']);
                Route::delete('{affiliateCode}', [AdminAffiliateCodesController::class, 'destroy']);
            });
        });

        Route::middleware('admin_permission:reports.view')->prefix('reports')->group(function () {
            Route::get('/', [AdminReportsController::class, 'index']);
            Route::get('revenue', [AdminReportsController::class, 'revenue']);
            Route::get('appointments', [AdminReportsController::class, 'appointments']);
            Route::get('services', [AdminReportsController::class, 'services']);
            Route::get('patients', [AdminReportsController::class, 'patients']);
            Route::get('subscriptions', [AdminReportsController::class, 'subscriptions']);
        });

        Route::middleware('admin_permission:products.manage')->prefix('products')->group(function () {
            Route::get('/', [AdminProductsController::class, 'index']);
            Route::get('categories', [AdminProductsController::class, 'categories']);
            Route::get('categories/create', [AdminProductsController::class, 'categoriesCreate']);
            Route::post('categories', [AdminProductsController::class, 'categoriesStore']);
            Route::get('inventory', [AdminProductsController::class, 'inventory']);
            Route::get('stock-movements', [AdminProductsController::class, 'stockMovements']);
            Route::get('pages', [AdminProductsController::class, 'pages']);
            Route::put('pages', [AdminProductsController::class, 'pagesUpdate']);
            Route::get('create', [AdminProductsController::class, 'create']);
            Route::post('/', [AdminProductsController::class, 'store']);
            Route::get('show/{id}', [AdminProductsController::class, 'show'])->whereNumber('id');
            Route::get('edit/{id}', [AdminProductsController::class, 'edit'])->whereNumber('id');
            Route::match(['post', 'put'], '{id}', [AdminProductsController::class, 'update'])->whereNumber('id');
            Route::delete('{id}', [AdminProductsController::class, 'destroy'])->whereNumber('id');
        });

        Route::middleware('admin_permission:settings.manage')->group(function () {
            Route::get('settings', [AdminSettingsController::class, 'index']);
            Route::get('settings/footer', [AdminFooterSettingsController::class, 'edit']);
            Route::put('settings/footer', [AdminFooterSettingsController::class, 'update']);
            Route::post('settings/logo', [AdminSettingsController::class, 'updateLogo']);
            Route::post('settings/favicon', [AdminSettingsController::class, 'updateFavicon']);
        });

        Route::middleware('admin_permission:pages.manage')->group(function () {
            Route::prefix('slides')->group(function () {
                Route::get('/', [AdminSlidesController::class, 'index']);
                Route::get('create', [AdminSlidesController::class, 'create']);
                Route::post('/', [AdminSlidesController::class, 'store']);
                Route::get('{id}/edit', [AdminSlidesController::class, 'edit']);
                Route::put('{id}', [AdminSlidesController::class, 'update']);
                Route::delete('{id}', [AdminSlidesController::class, 'destroy']);
                Route::post('{slide}/move-up', [AdminSlidesController::class, 'moveUp']);
                Route::post('{slide}/move-down', [AdminSlidesController::class, 'moveDown']);
            });

            Route::prefix('abouts')->group(function () {
                Route::get('/', [AdminAboutsController::class, 'index']);
                Route::get('create', [AdminAboutsController::class, 'create']);
                Route::post('/', [AdminAboutsController::class, 'store']);
                Route::get('{id}', [AdminAboutsController::class, 'show']);
                Route::get('{id}/edit', [AdminAboutsController::class, 'edit']);
                Route::put('{id}', [AdminAboutsController::class, 'update']);
                Route::delete('{id}', [AdminAboutsController::class, 'destroy']);
            });

            Route::prefix('blogs')->group(function () {
                Route::get('/', [AdminBlogsController::class, 'index']);
                Route::get('create', [AdminBlogsController::class, 'create']);
                Route::post('/', [AdminBlogsController::class, 'store']);
                Route::get('{id}', [AdminBlogsController::class, 'show']);
                Route::get('{id}/edit', [AdminBlogsController::class, 'edit']);
                Route::put('{id}', [AdminBlogsController::class, 'update']);
                Route::delete('{id}', [AdminBlogsController::class, 'destroy']);
            });

            Route::prefix('faqs')->group(function () {
                Route::get('/', [AdminFaqsController::class, 'index']);
                Route::get('create', [AdminFaqsController::class, 'create']);
                Route::post('/', [AdminFaqsController::class, 'store']);
                Route::get('{id}/edit', [AdminFaqsController::class, 'edit']);
                Route::put('{id}', [AdminFaqsController::class, 'update']);
                Route::delete('{id}', [AdminFaqsController::class, 'destroy']);
            });

            Route::prefix('testimonials')->group(function () {
                Route::get('/', [AdminTestimonialsController::class, 'index']);
                Route::get('create', [AdminTestimonialsController::class, 'create']);
                Route::post('/', [AdminTestimonialsController::class, 'store']);
                Route::get('{id}', [AdminTestimonialsController::class, 'show']);
                Route::get('{id}/edit', [AdminTestimonialsController::class, 'edit']);
                Route::put('{id}', [AdminTestimonialsController::class, 'update']);
                Route::delete('{id}', [AdminTestimonialsController::class, 'destroy']);
            });

            Route::prefix('page-headers')->group(function () {
                Route::get('about', [AdminPageHeadersController::class, 'about']);
                Route::post('about', [AdminPageHeadersController::class, 'updateAbout']);
                Route::post('about/reset', [AdminPageHeadersController::class, 'resetAbout']);
                Route::get('appointment', [AdminPageHeadersController::class, 'appointment']);
                Route::post('appointment', [AdminPageHeadersController::class, 'updateAppointment']);
                Route::post('appointment/reset', [AdminPageHeadersController::class, 'resetAppointment']);
                Route::get('contact', [AdminPageHeadersController::class, 'contact']);
                Route::post('contact', [AdminPageHeadersController::class, 'updateContact']);
                Route::post('contact/reset', [AdminPageHeadersController::class, 'resetContact']);
                Route::get('doctor', [AdminPageHeadersController::class, 'doctor']);
                Route::post('doctor', [AdminPageHeadersController::class, 'updateDoctor']);
                Route::post('doctor/reset', [AdminPageHeadersController::class, 'resetDoctor']);
                Route::get('doctor-details', [AdminPageHeadersController::class, 'doctorDetails']);
                Route::post('doctor-details', [AdminPageHeadersController::class, 'updateDoctorDetails']);
                Route::post('doctor-details/reset', [AdminPageHeadersController::class, 'resetDoctorDetails']);
                Route::get('faq', [AdminPageHeadersController::class, 'faq']);
                Route::post('faq', [AdminPageHeadersController::class, 'updateFaq']);
                Route::post('faq/reset', [AdminPageHeadersController::class, 'resetFaq']);
                Route::get('pricing', [AdminPageHeadersController::class, 'pricing']);
                Route::post('pricing', [AdminPageHeadersController::class, 'updatePricing']);
                Route::post('pricing/reset', [AdminPageHeadersController::class, 'resetPricing']);
                Route::get('products', [AdminPageHeadersController::class, 'products']);
                Route::post('products', [AdminPageHeadersController::class, 'updateProducts']);
                Route::post('products/reset', [AdminPageHeadersController::class, 'resetProducts']);
                Route::get('product-show', [AdminPageHeadersController::class, 'productShow']);
                Route::post('product-show', [AdminPageHeadersController::class, 'updateProductShow']);
                Route::post('product-show/reset', [AdminPageHeadersController::class, 'resetProductShow']);
                Route::get('services', [AdminPageHeadersController::class, 'services']);
                Route::post('services', [AdminPageHeadersController::class, 'updateServices']);
                Route::post('services/reset', [AdminPageHeadersController::class, 'resetServices']);
                Route::get('service-show', [AdminPageHeadersController::class, 'serviceShow']);
                Route::post('service-show', [AdminPageHeadersController::class, 'updateServiceShow']);
                Route::post('service-show/reset', [AdminPageHeadersController::class, 'resetServiceShow']);
                Route::get('testimonials', [AdminPageHeadersController::class, 'testimonials']);
                Route::post('testimonials', [AdminPageHeadersController::class, 'updateTestimonials']);
                Route::post('testimonials/reset', [AdminPageHeadersController::class, 'resetTestimonials']);
                Route::get('testimonial-show', [AdminPageHeadersController::class, 'testimonialShow']);
                Route::post('testimonial-show', [AdminPageHeadersController::class, 'updateTestimonialShow']);
                Route::post('testimonial-show/reset', [AdminPageHeadersController::class, 'resetTestimonialShow']);
                Route::get('not-found', [AdminPageHeadersController::class, 'notFound']);
                Route::post('not-found', [AdminPageHeadersController::class, 'updateNotFound']);
                Route::post('not-found/reset', [AdminPageHeadersController::class, 'resetNotFound']);
                Route::get('login-page', [AdminPageHeadersController::class, 'loginPage']);
                Route::post('login-page', [AdminPageHeadersController::class, 'updateLoginPage']);
                Route::post('login-page/reset', [AdminPageHeadersController::class, 'resetLoginPage']);
                Route::get('sign-up-page', [AdminPageHeadersController::class, 'signUpPage']);
                Route::post('sign-up-page', [AdminPageHeadersController::class, 'updateSignUpPage']);
                Route::post('sign-up-page/reset', [AdminPageHeadersController::class, 'resetSignUpPage']);
            });
        });
    });
});
