<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\About;
use App\Models\Blog;
use App\Models\Doctor;
use App\Models\Faq;
use App\Models\MembershipPlan;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\Slide;
use App\Models\Testimonial;
use App\Models\TreatmentPackage;
use App\Support\PageHeaderConfig;
use App\Support\SiteFooterConfig;
use Illuminate\Contracts\View\View;

class FrontEndController extends Controller
{
    public function about(): View
    {
        $about = About::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        return view('frontend.pages.about.about', [
            'about' => $about,
            'aboutPageHeaderBgUrl' => PageHeaderConfig::aboutBackgroundUrl(),
        ]);
    }

    public function appointment(): View
    {
        $about = About::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        return view('frontend.pages.appointments.appointment', [
            'about' => $about,
            'appointmentPageHeaderBgUrl' => PageHeaderConfig::appointmentBackgroundUrl(),
        ]);
    }

    public function blog(): View
    {
        $posts = Blog::query()
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(6);

        $recentPosts = Blog::query()
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $categories = Blog::query()
            ->published()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->select('category')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        return view('frontend.pages.blogs.blog', compact('posts', 'recentPosts', 'categories'));
    }

    public function blogShow(Blog $blog): View
    {
        abort_unless(
            $blog->status === 'published' && ($blog->published_at === null || $blog->published_at->lte(now())),
            404
        );

        $recentPosts = Blog::query()
            ->published()
            ->where('id', '!=', $blog->id)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $categories = Blog::query()
            ->published()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->select('category')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        return view('frontend.pages.blogs.show', compact('blog', 'recentPosts', 'categories'));
    }

    public function cart(): View
    {
        return view('frontend.pages.products.cart');
    }

    public function checkout(): View
    {
        return view('frontend.pages.products.checkout');
    }

    public function contact(): View
    {
        return view('frontend.pages.contacts.contact', [
            'siteFooter' => SiteFooterConfig::get(),
            'contactPageHeaderBgUrl' => PageHeaderConfig::contactBackgroundUrl(),
        ]);
    }

    public function doctor(): View
    {
        return view('frontend.pages.doctors.doctor', [
            'doctorPageHeaderBgUrl' => PageHeaderConfig::doctorBackgroundUrl(),
        ]);
    }

    public function doctorDetails(): View
    {
        return view('frontend.pages.doctors.doctor-details', [
            'doctorDetailsPageHeaderBgUrl' => PageHeaderConfig::doctorDetailsBackgroundUrl(),
        ]);
    }

    public function evergreenMedicalCenter(): View
    {
        return view('frontend.pages.services.evergreen-medical-center');
    }

    public function faq(): View
    {
        $faqs = Faq::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('frontend.pages.faqs.faq', [
            'faqs' => $faqs,
            'faqPageHeaderBgUrl' => PageHeaderConfig::faqBackgroundUrl(),
        ]);
    }

    public function harmonyFamilyHealthMedical(): View
    {
        return view('frontend.pages.services.harmony-family-health-medical');
    }

    public function index(): View
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
        $homeProducts = Product::query()
            ->with('categoryItem')
            ->where('status', 'active')
            ->where('is_available_for_sale', true)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get();
        $homeProductCategories = ProductCategory::query()
            ->whereHas('products', function ($q) {
                $q->where('status', 'active')
                    ->where('is_available_for_sale', true);
            })
            ->orderBy('name')
            ->limit(5)
            ->pluck('name');
        $homePromotions = Promotion::query()
            ->orderBy('start_date')
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        return view('frontend.pages.home.sections.index', [
            'homeDoctors' => $homeDoctors,
            'homeServices' => $homeServices,
            'homeSlides' => $homeSlides,
            'homeAbout' => $homeAbout,
            'homeProducts' => $homeProducts,
            'homeProductCategories' => $homeProductCategories,
            'homePromotions' => $homePromotions,
        ]);
    }

    public function pricing(): View
    {
        $membershipPlans = MembershipPlan::query()
            ->where('status', 'active')
            ->with(['services' => function ($query) {
                $query->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        return view('frontend.pages.pricing.pricing', [
            'membershipPlans' => $membershipPlans,
            'pricingPageHeaderBgUrl' => PageHeaderConfig::pricingBackgroundUrl(),
        ]);
    }

    public function packages(): View
    {
        $packages = TreatmentPackage::query()
            ->where('status', 'active')
            ->with(['services' => function ($query) {
                $query->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        return view('frontend.pages.packages.packages', [
            'packages' => $packages,
            'packagesPageHeaderBgUrl' => PageHeaderConfig::servicesBackgroundUrl(),
        ]);
    }

    public function packageShow(TreatmentPackage $package): View
    {
        abort_unless($package->status === 'active', 404);

        $package->load(['services' => function ($query) {
            $query->orderBy('name');
        }]);

        return view('frontend.pages.packages.show', [
            'package' => $package,
            'packagesPageHeaderBgUrl' => PageHeaderConfig::servicesBackgroundUrl(),
        ]);
    }

    public function productDetails(): View
    {
        return view('frontend.pages.products.product-details');
    }

    public function products(): View
    {
        $query = Product::query()
            ->with('categoryItem')
            ->where('status', 'active')
            ->where('is_available_for_sale', true);

        if ($category = request('category')) {
            $query->whereHas('categoryItem', function ($q) use ($category) {
                $q->where('name', $category);
            });
        }

        $search = trim((string) request('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhere('sku', 'like', '%'.$search.'%')
                    ->orWhere('brand', 'like', '%'.$search.'%');
            });
        }

        $products = (clone $query)->orderBy('name')->get();

        $categories = ProductCategory::query()
            ->whereHas('products', function ($q) {
                $q->where('status', 'active')
                    ->where('is_available_for_sale', true);
            })
            ->orderBy('name')
            ->pluck('name');

        $recentProducts = Product::query()
            ->where('status', 'active')
            ->where('is_available_for_sale', true)
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        $tagBrands = Product::query()
            ->where('status', 'active')
            ->where('is_available_for_sale', true)
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand')
            ->take(12);

        return view('frontend.pages.products.products', [
            'products' => $products,
            'categories' => $categories,
            'recentProducts' => $recentProducts,
            'tagBrands' => $tagBrands,
            'productsPageHeaderBgUrl' => PageHeaderConfig::productsBackgroundUrl(),
        ]);
    }

    public function productShow(Product $product): View
    {
        abort_unless(
            $product->status === 'active' && $product->is_available_for_sale,
            404
        );

        return view('frontend.pages.products.show', [
            'product' => $product,
            'productShowPageHeaderBgUrl' => PageHeaderConfig::productShowBackgroundUrl(),
        ]);
    }

    public function pureLifeHealthServices(): View
    {
        return view('frontend.pages.services.pure-life-health-services');
    }

    public function serviceCarousel(): View
    {
        return view('frontend.pages.services.service-carousel');
    }

    public function ourServices(): View
    {
        $services = Service::query()
            ->where('status', 'active')
            ->orderByDesc('is_featured')
            ->orderBy('name')
            ->get();

        return view('frontend.pages.services.our-services', [
            'services' => $services,
            'servicesPageHeaderBgUrl' => PageHeaderConfig::servicesBackgroundUrl(),
        ]);
    }

    public function serviceShow(Service $service): View
    {
        abort_unless($service->status === 'active', 404);

        $sidebarServices = Service::query()
            ->where('status', 'active')
            ->orderByDesc('is_featured')
            ->orderBy('name')
            ->get();

        return view('frontend.pages.services.show', [
            'service' => $service,
            'sidebarServices' => $sidebarServices,
            'serviceShowPageHeaderBgUrl' => PageHeaderConfig::serviceShowBackgroundUrl(),
        ]);
    }

    public function signUp(): View
    {
        return view('frontend.pages.sign-up', [
            'signUpPageHeaderBgUrl' => PageHeaderConfig::signUpPageBackgroundUrl(),
        ]);
    }

    public function testimonials(): View
    {
        $testimonials = Testimonial::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('frontend.pages.testimonies.testimonials', [
            'testimonials' => $testimonials,
            'testimonialsPageHeaderBgUrl' => PageHeaderConfig::testimonialsBackgroundUrl(),
        ]);
    }

    public function testimonialShow(Testimonial $testimonial): View
    {
        abort_unless($testimonial->status === 'published', 404);

        $recentTestimonials = Testimonial::query()
            ->published()
            ->where('id', '!=', $testimonial->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(6)
            ->get();

        return view('frontend.pages.testimonies.show', [
            'testimonial' => $testimonial,
            'recentTestimonials' => $recentTestimonials,
            'testimonialShowPageHeaderBgUrl' => PageHeaderConfig::testimonialShowBackgroundUrl(),
        ]);
    }

    public function vitalityHealthSolutions(): View
    {
        return view('frontend.pages.services.vitality-health-solutions');
    }

    public function wellSpringWellnessCenter(): View
    {
        return view('frontend.pages.services.wellSpring-wellness-center');
    }

    public function wishlist(): View
    {
        return view('frontend.pages.products.wishlist');
    }
}
