<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Support\PageHeaderConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PageHeadersController extends Controller
{
    public function editAbout(): View
    {
        return view('admin.page-headers.about', [
            'currentPath' => PageHeaderConfig::aboutBackgroundStoredPath(),
            'previewUrl' => PageHeaderConfig::aboutBackgroundUrl(),
        ]);
    }

    public function updateAbout(Request $request): RedirectResponse
    {
        return $this->updateBackground(
            $request,
            PageHeaderConfig::ABOUT_BACKGROUND_KEY,
            'about-header',
            'admin.page-headers.about',
            __('About page header image updated.')
        );
    }

    public function resetAbout(): RedirectResponse
    {
        return $this->resetBackground(
            PageHeaderConfig::ABOUT_BACKGROUND_KEY,
            'admin.page-headers.about',
            __('About page header reset to default image.')
        );
    }

    public function editAppointment(): View
    {
        return view('admin.page-headers.appointment', [
            'currentPath' => PageHeaderConfig::appointmentBackgroundStoredPath(),
            'previewUrl' => PageHeaderConfig::appointmentBackgroundUrl(),
        ]);
    }

    public function updateAppointment(Request $request): RedirectResponse
    {
        return $this->updateBackground(
            $request,
            PageHeaderConfig::APPOINTMENT_BACKGROUND_KEY,
            'appointment-header',
            'admin.page-headers.appointment',
            __('Appointment page header image updated.')
        );
    }

    public function resetAppointment(): RedirectResponse
    {
        return $this->resetBackground(
            PageHeaderConfig::APPOINTMENT_BACKGROUND_KEY,
            'admin.page-headers.appointment',
            __('Appointment page header reset to default image.')
        );
    }

    public function editContact(): View
    {
        return view('admin.page-headers.contact', [
            'currentPath' => PageHeaderConfig::contactBackgroundStoredPath(),
            'previewUrl' => PageHeaderConfig::contactBackgroundUrl(),
        ]);
    }

    public function updateContact(Request $request): RedirectResponse
    {
        return $this->updateBackground(
            $request,
            PageHeaderConfig::CONTACT_BACKGROUND_KEY,
            'contact-header',
            'admin.page-headers.contact',
            __('Contact page header image updated.')
        );
    }

    public function resetContact(): RedirectResponse
    {
        return $this->resetBackground(
            PageHeaderConfig::CONTACT_BACKGROUND_KEY,
            'admin.page-headers.contact',
            __('Contact page header reset to default image.')
        );
    }

    public function editDoctor(): View
    {
        return view('admin.page-headers.clinical-staff', [
            'currentPath' => PageHeaderConfig::doctorBackgroundStoredPath(),
            'previewUrl' => PageHeaderConfig::doctorBackgroundUrl(),
        ]);
    }

    public function updateDoctor(Request $request): RedirectResponse
    {
        return $this->updateBackground(
            $request,
            PageHeaderConfig::DOCTOR_BACKGROUND_KEY,
            'doctor-header',
            'admin.page-headers.doctor',
            __('Clinical staff page header image updated.')
        );
    }

    public function resetDoctor(): RedirectResponse
    {
        return $this->resetBackground(
            PageHeaderConfig::DOCTOR_BACKGROUND_KEY,
            'admin.page-headers.doctor',
            __('Clinical staff page header reset to default image.')
        );
    }

    public function editDoctorDetails(): View
    {
        return view('admin.page-headers.clinical-staff-details', [
            'currentPath' => PageHeaderConfig::doctorDetailsBackgroundStoredPath(),
            'previewUrl' => PageHeaderConfig::doctorDetailsBackgroundUrl(),
        ]);
    }

    public function updateDoctorDetails(Request $request): RedirectResponse
    {
        return $this->updateBackground(
            $request,
            PageHeaderConfig::DOCTOR_DETAILS_BACKGROUND_KEY,
            'doctor-details-header',
            'admin.page-headers.doctor-details',
            __('Clinical staff details page header image updated.')
        );
    }

    public function resetDoctorDetails(): RedirectResponse
    {
        return $this->resetBackground(
            PageHeaderConfig::DOCTOR_DETAILS_BACKGROUND_KEY,
            'admin.page-headers.doctor-details',
            __('Clinical staff details page header reset to default image.')
        );
    }

    public function editFaq(): View
    {
        return view('admin.page-headers.faq', [
            'currentPath' => PageHeaderConfig::faqBackgroundStoredPath(),
            'previewUrl' => PageHeaderConfig::faqBackgroundUrl(),
        ]);
    }

    public function updateFaq(Request $request): RedirectResponse
    {
        return $this->updateBackground(
            $request,
            PageHeaderConfig::FAQ_BACKGROUND_KEY,
            'faq-header',
            'admin.page-headers.faq',
            __('FAQ page header image updated.')
        );
    }

    public function resetFaq(): RedirectResponse
    {
        return $this->resetBackground(
            PageHeaderConfig::FAQ_BACKGROUND_KEY,
            'admin.page-headers.faq',
            __('FAQ page header reset to default image.')
        );
    }

    public function editPricing(): View
    {
        return view('admin.page-headers.pricing', [
            'currentPath' => PageHeaderConfig::pricingBackgroundStoredPath(),
            'previewUrl' => PageHeaderConfig::pricingBackgroundUrl(),
        ]);
    }

    public function updatePricing(Request $request): RedirectResponse
    {
        return $this->updateBackground(
            $request,
            PageHeaderConfig::PRICING_BACKGROUND_KEY,
            'pricing-header',
            'admin.page-headers.pricing',
            __('Pricing page header image updated.')
        );
    }

    public function resetPricing(): RedirectResponse
    {
        return $this->resetBackground(
            PageHeaderConfig::PRICING_BACKGROUND_KEY,
            'admin.page-headers.pricing',
            __('Pricing page header reset to default image.')
        );
    }

    public function editProducts(): View
    {
        return view('admin.page-headers.products', [
            'currentPath' => PageHeaderConfig::productsBackgroundStoredPath(),
            'previewUrl' => PageHeaderConfig::productsBackgroundUrl(),
        ]);
    }

    public function updateProducts(Request $request): RedirectResponse
    {
        return $this->updateBackground(
            $request,
            PageHeaderConfig::PRODUCTS_BACKGROUND_KEY,
            'products-header',
            'admin.page-headers.products',
            __('Products page header image updated.')
        );
    }

    public function resetProducts(): RedirectResponse
    {
        return $this->resetBackground(
            PageHeaderConfig::PRODUCTS_BACKGROUND_KEY,
            'admin.page-headers.products',
            __('Products page header reset to default image.')
        );
    }

    public function editProductShow(): View
    {
        return view('admin.page-headers.product-show', [
            'currentPath' => PageHeaderConfig::productShowBackgroundStoredPath(),
            'previewUrl' => PageHeaderConfig::productShowBackgroundUrl(),
        ]);
    }

    public function updateProductShow(Request $request): RedirectResponse
    {
        return $this->updateBackground(
            $request,
            PageHeaderConfig::PRODUCT_SHOW_BACKGROUND_KEY,
            'product-show-header',
            'admin.page-headers.product-show',
            __('Product detail page header image updated.')
        );
    }

    public function resetProductShow(): RedirectResponse
    {
        return $this->resetBackground(
            PageHeaderConfig::PRODUCT_SHOW_BACKGROUND_KEY,
            'admin.page-headers.product-show',
            __('Product detail page header reset to default image.')
        );
    }

    public function editServices(): View
    {
        return view('admin.page-headers.services', [
            'currentPath' => PageHeaderConfig::servicesBackgroundStoredPath(),
            'previewUrl' => PageHeaderConfig::servicesBackgroundUrl(),
        ]);
    }

    public function updateServices(Request $request): RedirectResponse
    {
        return $this->updateBackground(
            $request,
            PageHeaderConfig::SERVICES_BACKGROUND_KEY,
            'services-header',
            'admin.page-headers.services',
            __('Our Services page header image updated.')
        );
    }

    public function resetServices(): RedirectResponse
    {
        return $this->resetBackground(
            PageHeaderConfig::SERVICES_BACKGROUND_KEY,
            'admin.page-headers.services',
            __('Our Services page header reset to default image.')
        );
    }

    public function editServiceShow(): View
    {
        return view('admin.page-headers.service-show', [
            'currentPath' => PageHeaderConfig::serviceShowBackgroundStoredPath(),
            'previewUrl' => PageHeaderConfig::serviceShowBackgroundUrl(),
        ]);
    }

    public function updateServiceShow(Request $request): RedirectResponse
    {
        return $this->updateBackground(
            $request,
            PageHeaderConfig::SERVICE_SHOW_BACKGROUND_KEY,
            'service-show-header',
            'admin.page-headers.service-show',
            __('Service detail page header image updated.')
        );
    }

    public function resetServiceShow(): RedirectResponse
    {
        return $this->resetBackground(
            PageHeaderConfig::SERVICE_SHOW_BACKGROUND_KEY,
            'admin.page-headers.service-show',
            __('Service detail page header reset to default image.')
        );
    }

    public function editTestimonials(): View
    {
        return view('admin.page-headers.testimonials', [
            'currentPath' => PageHeaderConfig::testimonialsBackgroundStoredPath(),
            'previewUrl' => PageHeaderConfig::testimonialsBackgroundUrl(),
        ]);
    }

    public function updateTestimonials(Request $request): RedirectResponse
    {
        return $this->updateBackground(
            $request,
            PageHeaderConfig::TESTIMONIALS_BACKGROUND_KEY,
            'testimonials-header',
            'admin.page-headers.testimonials',
            __('Testimonials page header image updated.')
        );
    }

    public function resetTestimonials(): RedirectResponse
    {
        return $this->resetBackground(
            PageHeaderConfig::TESTIMONIALS_BACKGROUND_KEY,
            'admin.page-headers.testimonials',
            __('Testimonials page header reset to default image.')
        );
    }

    public function editTestimonialShow(): View
    {
        return view('admin.page-headers.testimonial-show', [
            'currentPath' => PageHeaderConfig::testimonialShowBackgroundStoredPath(),
            'previewUrl' => PageHeaderConfig::testimonialShowBackgroundUrl(),
        ]);
    }

    public function updateTestimonialShow(Request $request): RedirectResponse
    {
        return $this->updateBackground(
            $request,
            PageHeaderConfig::TESTIMONIAL_SHOW_BACKGROUND_KEY,
            'testimonial-show-header',
            'admin.page-headers.testimonial-show',
            __('Testimonial detail page header image updated.')
        );
    }

    public function resetTestimonialShow(): RedirectResponse
    {
        return $this->resetBackground(
            PageHeaderConfig::TESTIMONIAL_SHOW_BACKGROUND_KEY,
            'admin.page-headers.testimonial-show',
            __('Testimonial detail page header reset to default image.')
        );
    }

    public function editNotFound(): View
    {
        return view('admin.page-headers.not-found', [
            'currentPath' => PageHeaderConfig::notFoundBackgroundStoredPath(),
            'previewUrl' => PageHeaderConfig::notFoundBackgroundUrl(),
        ]);
    }

    public function updateNotFound(Request $request): RedirectResponse
    {
        return $this->updateBackground(
            $request,
            PageHeaderConfig::NOT_FOUND_BACKGROUND_KEY,
            '404-header',
            'admin.page-headers.not-found',
            __('404 page header image updated.')
        );
    }

    public function resetNotFound(): RedirectResponse
    {
        return $this->resetBackground(
            PageHeaderConfig::NOT_FOUND_BACKGROUND_KEY,
            'admin.page-headers.not-found',
            __('404 page header reset to default image.')
        );
    }

    public function editLoginPage(): View
    {
        return view('admin.page-headers.login-page', [
            'currentPath' => PageHeaderConfig::loginPageBackgroundStoredPath(),
            'previewUrl' => PageHeaderConfig::loginPageBackgroundUrl(),
        ]);
    }

    public function updateLoginPage(Request $request): RedirectResponse
    {
        return $this->updateBackground(
            $request,
            PageHeaderConfig::LOGIN_PAGE_BACKGROUND_KEY,
            'login-header',
            'admin.page-headers.login-page',
            __('Login page header image updated.')
        );
    }

    public function resetLoginPage(): RedirectResponse
    {
        return $this->resetBackground(
            PageHeaderConfig::LOGIN_PAGE_BACKGROUND_KEY,
            'admin.page-headers.login-page',
            __('Login page header reset to default image.')
        );
    }

    public function editSignUpPage(): View
    {
        return view('admin.page-headers.sign-up-page', [
            'currentPath' => PageHeaderConfig::signUpPageBackgroundStoredPath(),
            'previewUrl' => PageHeaderConfig::signUpPageBackgroundUrl(),
        ]);
    }

    public function updateSignUpPage(Request $request): RedirectResponse
    {
        return $this->updateBackground(
            $request,
            PageHeaderConfig::SIGN_UP_PAGE_BACKGROUND_KEY,
            'sign-up-header',
            'admin.page-headers.sign-up-page',
            __('Sign-up page header image updated.')
        );
    }

    public function resetSignUpPage(): RedirectResponse
    {
        return $this->resetBackground(
            PageHeaderConfig::SIGN_UP_PAGE_BACKGROUND_KEY,
            'admin.page-headers.sign-up-page',
            __('Sign-up page header reset to default image.')
        );
    }

    private function updateBackground(
        Request $request,
        string $settingKey,
        string $filePrefix,
        string $redirectRouteName,
        string $successMessage
    ): RedirectResponse {
        $validated = $request->validate([
            'background' => ['required', 'image', 'max:5120'],
        ]);

        $existing = AppSetting::getValue($settingKey);
        if ($existing) {
            $this->removeStoredUpload($existing);
        }

        $dir = public_path('uploads/page-headers');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file = $validated['background'];
        $ext = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg';
        $filename = $filePrefix.'-'.uniqid('', true).'.'.$ext;
        $file->move($dir, $filename);

        AppSetting::setValue($settingKey, 'uploads/page-headers/'.$filename);

        return redirect()->route($redirectRouteName)->with('status', $successMessage);
    }

    private function resetBackground(string $settingKey, string $redirectRouteName, string $successMessage): RedirectResponse
    {
        $existing = AppSetting::getValue($settingKey);
        if ($existing) {
            $this->removeStoredUpload($existing);
        }
        AppSetting::query()->where('key', $settingKey)->delete();

        return redirect()->route($redirectRouteName)->with('status', $successMessage);
    }

    private function removeStoredUpload(string $path): void
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        $normalized = ltrim($path, '/');

        if (str_starts_with($normalized, 'uploads/page-headers/')) {
            $fullPath = public_path($normalized);
            if (is_file($fullPath)) {
                unlink($fullPath);
            }

            return;
        }

        Storage::disk('public')->delete($normalized);
    }
}
