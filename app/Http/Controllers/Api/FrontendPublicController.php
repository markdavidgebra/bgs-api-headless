<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\About;
use App\Models\Blog;
use App\Models\Doctor;
use App\Models\Faq;
use App\Models\Inquiry;
use App\Models\MembershipPlan;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\Slide;
use App\Models\Testimonial;
use App\Models\TreatmentPackage;
use App\Support\PageHeaderConfig;
use App\Support\SiteFooterConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Read-only JSON API for the Next.js public frontend (bgs-front-end).
 */
class FrontendPublicController extends Controller
{
    public function home(): JsonResponse
    {
        $homeDoctors = Doctor::query()
            ->where('status', 'active')
            ->orderByRaw('CASE WHEN image_path IS NULL OR image_path = "" THEN 1 ELSE 0 END')
            ->orderBy('name')
            ->limit(3)
            ->get();

        $homeServices = Service::query()
            ->where('status', 'active')
            ->orderByDesc('is_featured')
            ->orderBy('name')
            ->limit(9)
            ->get();

        $homeSlides = Slide::query()->active()->ordered()->get();
        $homeAbout = About::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        $homePromotions = Promotion::query()
            ->orderBy('start_date')
            ->orderByDesc('id')
            ->limit(12)
            ->get()
            ->each->append(['validity_label']);

        return response()->json([
            'slides' => $homeSlides,
            'about' => $homeAbout,
            'doctors' => $homeDoctors,
            'services' => $homeServices,
            'promotions' => $homePromotions,
        ]);
    }

    public function siteFooter(): JsonResponse
    {
        return response()->json(SiteFooterConfig::get());
    }

    public function about(): JsonResponse
    {
        $about = About::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        return response()->json($about);
    }

    public function services(): JsonResponse
    {
        $services = Service::query()
            ->where('status', 'active')
            ->orderByDesc('is_featured')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $services]);
    }

    public function serviceShow(string $slug): JsonResponse
    {
        $service = Service::query()
            ->where('status', 'active')
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json($service);
    }

    public function doctors(): JsonResponse
    {
        $doctors = Doctor::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $doctors]);
    }

    public function faqs(): JsonResponse
    {
        $faqs = Faq::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json(['data' => $faqs]);
    }

    public function testimonials(): JsonResponse
    {
        $testimonials = Testimonial::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json(['data' => $testimonials]);
    }

    public function blogs(): JsonResponse
    {
        $posts = Blog::query()
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $posts]);
    }

    public function blogShow(string $slug): JsonResponse
    {
        $blog = Blog::query()
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();

        return response()->json($blog);
    }

    public function products(): JsonResponse
    {
        $products = Product::query()
            ->with('categoryItem')
            ->where('status', 'active')
            ->where('is_available_for_sale', true)
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $products]);
    }

    public function packages(): JsonResponse
    {
        $packages = TreatmentPackage::query()
            ->where('status', 'active')
            ->with(['services' => fn ($q) => $q->orderBy('name')])
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $packages]);
    }

    public function membershipPlans(): JsonResponse
    {
        $plans = MembershipPlan::query()
            ->where('status', 'active')
            ->with(['services' => fn ($q) => $q->orderBy('name')])
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $plans]);
    }

    public function promotions(): JsonResponse
    {
        $promotions = Promotion::query()
            ->orderBy('start_date')
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->each->append(['validity_label']);

        return response()->json(['data' => $promotions]);
    }

    public function pageHeader(string $key): JsonResponse
    {
        $url = match ($key) {
            'about' => PageHeaderConfig::aboutBackgroundUrl(),
            'appointment' => PageHeaderConfig::appointmentBackgroundUrl(),
            'contact' => PageHeaderConfig::contactBackgroundUrl(),
            'doctor' => PageHeaderConfig::doctorBackgroundUrl(),
            'doctor-details' => PageHeaderConfig::doctorDetailsBackgroundUrl(),
            'faq' => PageHeaderConfig::faqBackgroundUrl(),
            'pricing' => PageHeaderConfig::pricingBackgroundUrl(),
            'products' => PageHeaderConfig::productsBackgroundUrl(),
            'product-show' => PageHeaderConfig::productShowBackgroundUrl(),
            'services' => PageHeaderConfig::servicesBackgroundUrl(),
            'service-show' => PageHeaderConfig::serviceShowBackgroundUrl(),
            'testimonials' => PageHeaderConfig::testimonialsBackgroundUrl(),
            'testimonial-show' => PageHeaderConfig::testimonialShowBackgroundUrl(),
            'not-found' => PageHeaderConfig::notFoundBackgroundUrl(),
            'login-page' => PageHeaderConfig::loginPageBackgroundUrl(),
            'sign-up-page' => PageHeaderConfig::signUpPageBackgroundUrl(),
            default => null,
        };

        if ($url === null) {
            return response()->json(['message' => 'Unknown page header key.'], 404);
        }

        return response()->json(['background_url' => $url]);
    }

    public function contactInquiry(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'number' => ['required', 'string', 'max:100'],
            'date' => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:10000'],
        ]);

        Inquiry::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['number'],
            'preferred_date' => $validated['date'] ?? null,
            'message' => $validated['message'] ?? null,
        ]);

        return response()->json([
            'message' => __('Thank you. Your inquiry has been sent—we will get back to you soon.'),
        ], 201);
    }
}
