<?php

use App\Http\Controllers\Admin\AboutsController;
use App\Http\Controllers\Admin\AdminRolesController;
use App\Http\Controllers\Admin\AppointmentsController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Admin\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Admin\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Admin\Auth\NewPasswordController;
use App\Http\Controllers\Admin\Auth\PasswordController;
use App\Http\Controllers\Admin\Auth\PasswordResetLinkController;
use App\Http\Controllers\Admin\Auth\VerifyEmailController;
use App\Http\Controllers\Admin\BlogsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ClinicalStaffRolesController;
use App\Http\Controllers\Admin\ClinicalStaffController;
use App\Http\Controllers\Admin\FaqsController;
use App\Http\Controllers\Admin\FooterSettingsController;
use App\Http\Controllers\Admin\InquiriesController;
use App\Http\Controllers\Admin\PackagesController;
use App\Http\Controllers\Admin\PageHeadersController;
use App\Http\Controllers\Admin\PatientRegistrationsController;
use App\Http\Controllers\Admin\PatientsController;
use App\Http\Controllers\Admin\PaymentsController;
use App\Http\Controllers\Admin\ProductsController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\AffiliateCodesController;
use App\Http\Controllers\Admin\PromotionsController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\ServicesController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SlidesController;
use App\Http\Controllers\Admin\StaffsController;
use App\Http\Controllers\Admin\SubscriptionsController;
use App\Http\Controllers\Admin\TestimonialsController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'guest:admin,web,doctor', 'prefix' => 'admin', 'as' => 'admin.'], function () {

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::group(['middleware' => ['auth:admin', 'admin_approved'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
    Route::middleware('admin_permission:settings.manage')->group(function () {
        Route::get('settings', [SettingsController::class, 'index'])->name('settings');
        Route::get('settings/footer', [FooterSettingsController::class, 'edit'])->name('settings.footer');
        Route::put('settings/footer', [FooterSettingsController::class, 'update'])->name('settings.footer.update');
        Route::post('settings/logo', [SettingsController::class, 'updateLogo'])->name('settings.logo.update');
        Route::post('settings/favicon', [SettingsController::class, 'updateFavicon'])->name('settings.favicon.update');
    });
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
    Route::middleware('admin_permission:dashboard.view')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });
    Route::middleware('admin_permission:appointments.manage')->group(function () {
        Route::get('appointments', [AppointmentsController::class, 'index'])->name('appointments');
        Route::get('appointments/calendar', [AppointmentsController::class, 'calendar'])->name('appointments.calendar');
        Route::get('appointments/{id}', [AppointmentsController::class, 'show'])->name('appointments.show');
    });
    Route::middleware('admin_permission:inquiries.manage')->group(function () {
        Route::get('inquiries', [InquiriesController::class, 'index'])->name('inquiries');
        Route::get('inquiries/{id}', [InquiriesController::class, 'show'])->name('inquiries.show');
        Route::delete('inquiries/{id}', [InquiriesController::class, 'destroy'])->name('inquiries.destroy');
    });
    Route::middleware('admin_permission:registrations.manage')->group(function () {
        Route::get('registrations', [PatientRegistrationsController::class, 'index'])->name('registrations');
        Route::post('registrations/{id}/approve', [PatientRegistrationsController::class, 'approve'])->name('registrations.approve');
        Route::post('registrations/{id}/disapprove', [PatientRegistrationsController::class, 'disapprove'])->name('registrations.disapprove');
    });
    Route::middleware('admin_permission:staff.manage')->group(function () {
        Route::get('staff', [StaffsController::class, 'index'])->name('staffs');
        Route::get('staff/create', [StaffsController::class, 'create'])->name('staffs.create');
        Route::post('staff', [StaffsController::class, 'store'])->name('staffs.store');
        Route::get('staff/{id}', [StaffsController::class, 'show'])->name('staffs.show');
        Route::get('staff/{id}/edit', [StaffsController::class, 'edit'])->name('staffs.edit');
        Route::put('staff/{id}', [StaffsController::class, 'update'])->name('staffs.update');
        Route::delete('staff/{id}', [StaffsController::class, 'destroy'])->name('staffs.destroy');
        Route::post('staff/{id}/status', [StaffsController::class, 'updateStatus'])->name('staffs.status');
    });
    Route::middleware('admin_permission:doctors.manage')->group(function () {
        Route::get('doctor-roles', [ClinicalStaffRolesController::class, 'index'])->name('doctor-roles.index');
        Route::get('doctor-roles/create', [ClinicalStaffRolesController::class, 'create'])->name('doctor-roles.create');
        Route::post('doctor-roles', [ClinicalStaffRolesController::class, 'store'])->name('doctor-roles.store');
        Route::get('doctor-roles/{id}/edit', [ClinicalStaffRolesController::class, 'edit'])->name('doctor-roles.edit');
        Route::put('doctor-roles/{id}', [ClinicalStaffRolesController::class, 'update'])->name('doctor-roles.update');

        Route::post('doctors/{id}/role', [ClinicalStaffController::class, 'updateRole'])->name('doctors.role');
        Route::get('doctors', [ClinicalStaffController::class, 'index'])->name('doctors');
        Route::get('doctors/create', [ClinicalStaffController::class, 'create'])->name('doctors.create');
        Route::post('doctors', [ClinicalStaffController::class, 'store'])->name('doctors.store');
        Route::get('doctors/{id}/edit', [ClinicalStaffController::class, 'edit'])->name('doctors.edit');
        Route::put('doctors/{id}', [ClinicalStaffController::class, 'update'])->name('doctors.update');
        Route::post('doctors/{id}/status', [ClinicalStaffController::class, 'updateStatus'])->name('doctors.status');
        Route::delete('doctors/{id}', [ClinicalStaffController::class, 'destroy'])->name('doctors.destroy');
        Route::get('doctors/{id}', [ClinicalStaffController::class, 'show'])->name('doctors.show');
    });
    Route::middleware('admin_permission:patients.view,patients.manage')->group(function () {
        Route::get('patients', [PatientsController::class, 'index'])->name('patients');
    });
    Route::middleware('admin_permission:patients.manage')->group(function () {
        Route::get('patients/create', [PatientsController::class, 'create'])->name('patients.create');
        Route::post('patients', [PatientsController::class, 'store'])->name('patients.store');
        Route::get('patients/{id}/edit', [PatientsController::class, 'edit'])->name('patients.edit');
        Route::put('patients/{id}', [PatientsController::class, 'update'])->name('patients.update');
        Route::delete('patients/{id}', [PatientsController::class, 'destroy'])->name('patients.destroy');
        Route::post('patients/{id}/treatment-packages', [PatientsController::class, 'storePatientTreatmentPackage'])->name('patients.treatment-packages.store');
        Route::post('patients/{id}/status', [PatientsController::class, 'updateStatus'])->name('patients.status');
        Route::post('patients/{id}/password', [PatientsController::class, 'updatePassword'])->name('patients.password.update');
        Route::post('patients/{id}/password-reset-link', [PatientsController::class, 'sendPasswordReset'])->name('patients.password.reset-link');
        Route::put('patients/{patient}/appointments/{appointment}/appointment-notes', [PatientsController::class, 'upsertAppointmentNote'])->name('patients.appointments.appointment-notes.update');
        Route::delete('patients/{patient}/appointments/{appointment}/appointment-notes/field/{field}', [PatientsController::class, 'clearAppointmentNoteField'])->name('patients.appointments.appointment-notes.field.destroy')->where('field', 'patient_concern|doctor_notes|instructions|alerts|appointment_remarks|admin_notes');
        Route::delete('patients/{patient}/appointments/{appointment}/appointment-notes', [PatientsController::class, 'destroyAppointmentNote'])->name('patients.appointments.appointment-notes.destroy');
    });
    Route::middleware('admin_permission:patients.view,patients.manage')->group(function () {
        Route::get('patients/{id}', [PatientsController::class, 'show'])->name('patients.show');
    });
    Route::middleware('admin_permission:roles.manage')->group(function () {
        Route::get('roles', [AdminRolesController::class, 'index'])->name('roles.index');
        Route::get('roles/create', [AdminRolesController::class, 'create'])->name('roles.create');
        Route::post('roles', [AdminRolesController::class, 'store'])->name('roles.store');
        Route::get('roles/{id}/edit', [AdminRolesController::class, 'edit'])->name('roles.edit');
        Route::put('roles/{id}', [AdminRolesController::class, 'update'])->name('roles.update');
        Route::delete('roles/{id}', [AdminRolesController::class, 'destroy'])->name('roles.destroy');
    });
    Route::middleware('admin_permission:pages.manage')->group(function () {
        Route::get('slides', [SlidesController::class, 'index'])->name('slides');
        Route::post('slides', [SlidesController::class, 'store'])->name('slides.store');
        Route::get('slides/create', [SlidesController::class, 'create'])->name('slides.create');
        Route::post('slides/{slide}/move-up', [SlidesController::class, 'moveUp'])->name('slides.move-up');
        Route::post('slides/{slide}/move-down', [SlidesController::class, 'moveDown'])->name('slides.move-down');
        Route::get('slides/{id}/edit', [SlidesController::class, 'edit'])->name('slides.edit');
        Route::put('slides/{id}', [SlidesController::class, 'update'])->name('slides.update');
        Route::delete('slides/{id}', [SlidesController::class, 'destroy'])->name('slides.destroy');
        Route::get('abouts', [AboutsController::class, 'index'])->name('abouts');
        Route::get('abouts/create', [AboutsController::class, 'create'])->name('abouts.create');
        Route::post('abouts', [AboutsController::class, 'store'])->name('abouts.store');
        Route::get('abouts/{id}', [AboutsController::class, 'show'])->name('abouts.show');
        Route::get('abouts/{id}/edit', [AboutsController::class, 'edit'])->name('abouts.edit');
        Route::put('abouts/{id}', [AboutsController::class, 'update'])->name('abouts.update');
        Route::delete('abouts/{id}', [AboutsController::class, 'destroy'])->name('abouts.destroy');
        Route::get('page-headers/about', [PageHeadersController::class, 'editAbout'])->name('page-headers.about');
        Route::post('page-headers/about', [PageHeadersController::class, 'updateAbout'])->name('page-headers.about.update');
        Route::post('page-headers/about/reset', [PageHeadersController::class, 'resetAbout'])->name('page-headers.about.reset');
        Route::get('page-headers/appointment', [PageHeadersController::class, 'editAppointment'])->name('page-headers.appointment');
        Route::post('page-headers/appointment', [PageHeadersController::class, 'updateAppointment'])->name('page-headers.appointment.update');
        Route::post('page-headers/appointment/reset', [PageHeadersController::class, 'resetAppointment'])->name('page-headers.appointment.reset');
        Route::get('page-headers/contact', [PageHeadersController::class, 'editContact'])->name('page-headers.contact');
        Route::post('page-headers/contact', [PageHeadersController::class, 'updateContact'])->name('page-headers.contact.update');
        Route::post('page-headers/contact/reset', [PageHeadersController::class, 'resetContact'])->name('page-headers.contact.reset');
        Route::get('page-headers/doctor', [PageHeadersController::class, 'editDoctor'])->name('page-headers.doctor');
        Route::post('page-headers/doctor', [PageHeadersController::class, 'updateDoctor'])->name('page-headers.doctor.update');
        Route::post('page-headers/doctor/reset', [PageHeadersController::class, 'resetDoctor'])->name('page-headers.doctor.reset');
        Route::get('page-headers/doctor-details', [PageHeadersController::class, 'editDoctorDetails'])->name('page-headers.doctor-details');
        Route::post('page-headers/doctor-details', [PageHeadersController::class, 'updateDoctorDetails'])->name('page-headers.doctor-details.update');
        Route::post('page-headers/doctor-details/reset', [PageHeadersController::class, 'resetDoctorDetails'])->name('page-headers.doctor-details.reset');
        Route::get('page-headers/faq', [PageHeadersController::class, 'editFaq'])->name('page-headers.faq');
        Route::post('page-headers/faq', [PageHeadersController::class, 'updateFaq'])->name('page-headers.faq.update');
        Route::post('page-headers/faq/reset', [PageHeadersController::class, 'resetFaq'])->name('page-headers.faq.reset');
        Route::get('page-headers/pricing', [PageHeadersController::class, 'editPricing'])->name('page-headers.pricing');
        Route::post('page-headers/pricing', [PageHeadersController::class, 'updatePricing'])->name('page-headers.pricing.update');
        Route::post('page-headers/pricing/reset', [PageHeadersController::class, 'resetPricing'])->name('page-headers.pricing.reset');
        Route::get('page-headers/products', [PageHeadersController::class, 'editProducts'])->name('page-headers.products');
        Route::post('page-headers/products', [PageHeadersController::class, 'updateProducts'])->name('page-headers.products.update');
        Route::post('page-headers/products/reset', [PageHeadersController::class, 'resetProducts'])->name('page-headers.products.reset');
        Route::get('page-headers/product-show', [PageHeadersController::class, 'editProductShow'])->name('page-headers.product-show');
        Route::post('page-headers/product-show', [PageHeadersController::class, 'updateProductShow'])->name('page-headers.product-show.update');
        Route::post('page-headers/product-show/reset', [PageHeadersController::class, 'resetProductShow'])->name('page-headers.product-show.reset');
        Route::get('page-headers/services', [PageHeadersController::class, 'editServices'])->name('page-headers.services');
        Route::post('page-headers/services', [PageHeadersController::class, 'updateServices'])->name('page-headers.services.update');
        Route::post('page-headers/services/reset', [PageHeadersController::class, 'resetServices'])->name('page-headers.services.reset');
        Route::get('page-headers/service-show', [PageHeadersController::class, 'editServiceShow'])->name('page-headers.service-show');
        Route::post('page-headers/service-show', [PageHeadersController::class, 'updateServiceShow'])->name('page-headers.service-show.update');
        Route::post('page-headers/service-show/reset', [PageHeadersController::class, 'resetServiceShow'])->name('page-headers.service-show.reset');
        Route::get('page-headers/testimonials', [PageHeadersController::class, 'editTestimonials'])->name('page-headers.testimonials');
        Route::post('page-headers/testimonials', [PageHeadersController::class, 'updateTestimonials'])->name('page-headers.testimonials.update');
        Route::post('page-headers/testimonials/reset', [PageHeadersController::class, 'resetTestimonials'])->name('page-headers.testimonials.reset');
        Route::get('page-headers/testimonial-show', [PageHeadersController::class, 'editTestimonialShow'])->name('page-headers.testimonial-show');
        Route::post('page-headers/testimonial-show', [PageHeadersController::class, 'updateTestimonialShow'])->name('page-headers.testimonial-show.update');
        Route::post('page-headers/testimonial-show/reset', [PageHeadersController::class, 'resetTestimonialShow'])->name('page-headers.testimonial-show.reset');
        Route::get('page-headers/not-found', [PageHeadersController::class, 'editNotFound'])->name('page-headers.not-found');
        Route::post('page-headers/not-found', [PageHeadersController::class, 'updateNotFound'])->name('page-headers.not-found.update');
        Route::post('page-headers/not-found/reset', [PageHeadersController::class, 'resetNotFound'])->name('page-headers.not-found.reset');
        Route::get('page-headers/login-page', [PageHeadersController::class, 'editLoginPage'])->name('page-headers.login-page');
        Route::post('page-headers/login-page', [PageHeadersController::class, 'updateLoginPage'])->name('page-headers.login-page.update');
        Route::post('page-headers/login-page/reset', [PageHeadersController::class, 'resetLoginPage'])->name('page-headers.login-page.reset');
        Route::get('page-headers/sign-up-page', [PageHeadersController::class, 'editSignUpPage'])->name('page-headers.sign-up-page');
        Route::post('page-headers/sign-up-page', [PageHeadersController::class, 'updateSignUpPage'])->name('page-headers.sign-up-page.update');
        Route::post('page-headers/sign-up-page/reset', [PageHeadersController::class, 'resetSignUpPage'])->name('page-headers.sign-up-page.reset');
        Route::get('blogs', [BlogsController::class, 'index'])->name('blogs');
        Route::get('blogs/create', [BlogsController::class, 'create'])->name('blogs.create');
        Route::post('blogs', [BlogsController::class, 'store'])->name('blogs.store');
        Route::get('blogs/{id}', [BlogsController::class, 'show'])->name('blogs.show');
        Route::get('blogs/{id}/edit', [BlogsController::class, 'edit'])->name('blogs.edit');
        Route::put('blogs/{id}', [BlogsController::class, 'update'])->name('blogs.update');
        Route::delete('blogs/{id}', [BlogsController::class, 'destroy'])->name('blogs.destroy');
        Route::get('faqs', [FaqsController::class, 'index'])->name('faqs');
        Route::get('faqs/create', [FaqsController::class, 'create'])->name('faqs.create');
        Route::post('faqs', [FaqsController::class, 'store'])->name('faqs.store');
        Route::get('faqs/{id}/edit', [FaqsController::class, 'edit'])->name('faqs.edit');
        Route::put('faqs/{id}', [FaqsController::class, 'update'])->name('faqs.update');
        Route::delete('faqs/{id}', [FaqsController::class, 'destroy'])->name('faqs.destroy');
        Route::get('testimonials', [TestimonialsController::class, 'index'])->name('testimonials');
        Route::get('testimonials/create', [TestimonialsController::class, 'create'])->name('testimonials.create');
        Route::post('testimonials', [TestimonialsController::class, 'store'])->name('testimonials.store');
        Route::get('testimonials/{id}', [TestimonialsController::class, 'show'])->name('testimonials.show');
        Route::get('testimonials/{id}/edit', [TestimonialsController::class, 'edit'])->name('testimonials.edit');
        Route::put('testimonials/{id}', [TestimonialsController::class, 'update'])->name('testimonials.update');
        Route::delete('testimonials/{id}', [TestimonialsController::class, 'destroy'])->name('testimonials.destroy');
    });

    Route::middleware('admin_permission:services.manage')->group(function () {
        Route::get('services', [ServicesController::class, 'index'])->name('services');
        Route::post('services', [ServicesController::class, 'store'])->name('services.store');
        Route::get('services/create', [ServicesController::class, 'create'])->name('services.create');
        Route::get('services/edit/{id}', [ServicesController::class, 'edit'])->name('services.edit');
        Route::put('services/{id}', [ServicesController::class, 'update'])->name('services.update');
        Route::delete('services/{id}', [ServicesController::class, 'destroy'])->name('services.destroy');
        Route::get('services/{id}', [ServicesController::class, 'show'])->name('services.show');
    });
    Route::middleware('admin_permission:packages.manage')->group(function () {
        Route::get('packages', [PackagesController::class, 'index'])->name('packages');
        Route::post('packages', [PackagesController::class, 'store'])->name('packages.store');
        Route::get('packages/create', [PackagesController::class, 'create'])->name('packages.create');
        Route::get('packages/edit/{id}', [PackagesController::class, 'edit'])->name('packages.edit');
        Route::put('packages/{id}', [PackagesController::class, 'update'])->name('packages.update');
        Route::get('packages/{id}', [PackagesController::class, 'show'])->name('packages.show');
        Route::delete('packages/{id}', [PackagesController::class, 'destroy'])->name('packages.destroy');
    });
    Route::middleware('admin_permission:subscriptions.manage')->group(function () {
        Route::get('subscriptions', [SubscriptionsController::class, 'index'])->name('subscriptions');
        Route::post('subscriptions', [SubscriptionsController::class, 'store'])->name('subscriptions.store');
        Route::get('subscriptions/create', [SubscriptionsController::class, 'create'])->name('subscriptions.create');
        Route::get('subscriptions/show/{id}', [SubscriptionsController::class, 'show'])->name('subscriptions.show');
        Route::get('subscriptions/edit/{id}', [SubscriptionsController::class, 'edit'])->name('subscriptions.edit');
        Route::put('subscriptions/{id}', [SubscriptionsController::class, 'update'])->name('subscriptions.update');
        Route::delete('subscriptions/{id}', [SubscriptionsController::class, 'destroy'])->name('subscriptions.destroy');
    });
    Route::middleware('admin_permission:payments.manage')->group(function () {
        Route::get('payments', [PaymentsController::class, 'index'])->name('payments');
        Route::get('payments/create', [PaymentsController::class, 'create'])->name('payments.create');
        Route::post('payments', [PaymentsController::class, 'store'])->name('payments.store');
        Route::get('payments/show/{id}', [PaymentsController::class, 'show'])->name('payments.show');
    });
    Route::middleware('admin_permission:promotions.manage')->group(function () {
        Route::get('promotions', [PromotionsController::class, 'index'])->name('promotions');
        Route::get('promotions/create', [PromotionsController::class, 'create'])->name('promotions.create');
        Route::post('promotions', [PromotionsController::class, 'store'])->name('promotions.store');
        Route::get('promotions/show/{id}', [PromotionsController::class, 'show'])->name('promotions.show');
        Route::get('promotions/edit/{id}', [PromotionsController::class, 'edit'])->name('promotions.edit');
        Route::put('promotions/{id}', [PromotionsController::class, 'update'])->name('promotions.update');
        Route::get('promotions/email', [PromotionsController::class, 'emailForm'])->name('promotions.email');
        Route::post('promotions/email', [PromotionsController::class, 'sendEmailBlast'])->name('promotions.email.send');
        Route::get('affiliate-codes', [AffiliateCodesController::class, 'index'])->name('affiliate-codes');
        Route::get('affiliate-codes/create', [AffiliateCodesController::class, 'create'])->name('affiliate-codes.create');
        Route::post('affiliate-codes', [AffiliateCodesController::class, 'store'])->name('affiliate-codes.store');
        Route::get('affiliate-codes/edit/{affiliateCode}', [AffiliateCodesController::class, 'edit'])->name('affiliate-codes.edit');
        Route::put('affiliate-codes/{affiliateCode}', [AffiliateCodesController::class, 'update'])->name('affiliate-codes.update');
        Route::delete('affiliate-codes/{affiliateCode}', [AffiliateCodesController::class, 'destroy'])->name('affiliate-codes.destroy');
    });
    Route::middleware('admin_permission:reports.view')->group(function () {
        Route::get('reports', [ReportsController::class, 'index'])->name('reports');
        Route::get('reports/revenue', [ReportsController::class, 'revenue'])->name('reports.revenue');
        Route::get('reports/appointments', [ReportsController::class, 'appointments'])->name('reports.appointments');
        Route::get('reports/services', [ReportsController::class, 'services'])->name('reports.services');
        Route::get('reports/patients', [ReportsController::class, 'patients'])->name('reports.patients');
        Route::get('reports/subscriptions', [ReportsController::class, 'subscriptions'])->name('reports.subscriptions');
    });
    Route::middleware('admin_permission:products.manage')->group(function () {
        Route::get('products', [ProductsController::class, 'index'])->name('products');
        Route::post('products', [ProductsController::class, 'store'])->name('products.store');
        Route::get('products/categories', [ProductsController::class, 'categories'])->name('products.categories');
        Route::get('products/categories/create', [ProductsController::class, 'categoriesCreate'])->name('products.categories.create');
        Route::post('products/categories', [ProductsController::class, 'categoriesStore'])->name('products.categories.store');
        Route::get('products/inventory', [ProductsController::class, 'inventory'])->name('products.inventory');
        Route::get('products/stock-movements', [ProductsController::class, 'stockMovements'])->name('products.stock-movements');
        Route::get('products/pages', [ProductsController::class, 'editCatalogPage'])->name('products.pages');
        Route::put('products/pages', [ProductsController::class, 'updateCatalogPage'])->name('products.pages.update');
        Route::get('products/create', [ProductsController::class, 'create'])->name('products.create');
        Route::get('products/show/{id}', [ProductsController::class, 'show'])->name('products.show');
        Route::get('products/edit/{id}', [ProductsController::class, 'edit'])->name('products.edit');
        // POST + PUT: multipart file uploads are more reliable as real POST (some stacks mishandle spoofed PUT).
        Route::match(['post', 'put'], 'products/{id}', [ProductsController::class, 'update'])->name('products.update');
        Route::delete('products/{id}', [ProductsController::class, 'destroy'])->name('products.destroy');
    });

});
