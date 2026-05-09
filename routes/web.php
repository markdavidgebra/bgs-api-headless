<?php

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
use App\Http\Controllers\Patient\PatientPackageController;
use App\Http\Controllers\Patient\PatientPaymentController;
use App\Http\Controllers\Patient\PatientProfileController;
use App\Http\Controllers\Patient\PatientPromotionController;
use App\Http\Controllers\Patient\PatientTreatmentController;
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
Route::get('/services/{service:slug}', [FrontEndController::class, 'serviceShow'])->name('services.show');
Route::get('/sign-up', [FrontEndController::class, 'signUp'])->name('sign-up');
Route::get('/testimonial-carousel', [FrontEndController::class, 'testimonialCarousel'])->name('testimonial-carousel');
Route::get('/our-testimonials', [FrontEndController::class, 'testimonials'])->name('testimonials');
Route::get('/our-testimonials/{testimonial}', [FrontEndController::class, 'testimonialShow'])->name('testimonials.show');
Route::get('/vitality-health-solutions', [FrontEndController::class, 'vitalityHealthSolutions'])->name('vitality-health-solutions');
Route::get('/wellSpring-wellness-center', [FrontEndController::class, 'wellSpringWellnessCenter'])->name('wellSpring-wellness-center');
Route::get('/wishlist', [FrontEndController::class, 'wishlist'])->name('wishlist');

// POS route aliases for external React apps that post to /pos/*
Route::post('/pos/login', [PosController::class, 'login'])->name('pos/login');
Route::middleware(['auth:admin', 'admin_role:admin,cashier'])->prefix('pos')->group(function () {
    Route::post('/logout', [PosController::class, 'logout'])->name('pos/logout');
    Route::get('/me', [PosController::class, 'me'])->name('pos/me');
});

/*
*==========================
* Patient portal
*==========================
*/

Route::group(['middleware' => ['prevent_cross_guard:web', 'auth:web', 'verified'], 'prefix' => 'patient', 'as' => 'patient.'], function () {
    Route::get('/dashboard', [PatientDashboardController::class, 'index'])->name('dashboard');
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

Route::group(['middleware' => ['prevent_cross_guard:doctor', 'auth:doctor', 'verified'], 'prefix' => 'doctor', 'as' => 'doctor.'], function () {
    Route::get('/dashboard', [DoctorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/products', [DoctorProductInventoryController::class, 'index'])->name('products');
    Route::get('/services', [DoctorServiceController::class, 'index'])->name('services');
    Route::get('/appointments', [DoctorAppointmentController::class, 'index'])->name('appointments');
    Route::get('/appointments/{appointment}/notes/create', [DoctorAppointmentController::class, 'createNotes'])->name('appointments.notes.create');
    Route::get('/appointments/{appointment}', [DoctorAppointmentController::class, 'show'])->name('appointments.show');
    Route::get('/patient-records', [DoctorPatientRecordController::class, 'index'])->name('patient-records');
    Route::get('/patient-records/{patient}', [DoctorPatientRecordController::class, 'show'])->name('patient-records.show');
    Route::post('/patient-records/{patient}/notes', [DoctorPatientRecordController::class, 'storeNote'])->name('patient-records.notes.store');
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
    Route::post('/appointments/{appointment}/start-session', [DoctorAppointmentController::class, 'startSession'])->name('appointments.start-session');
    Route::post('/appointments/{appointment}/complete', [DoctorAppointmentController::class, 'markCompleted'])->name('appointments.complete');
    Route::post('/appointments/{appointment}/notes', [DoctorAppointmentController::class, 'addNotes'])->name('appointments.notes');
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
