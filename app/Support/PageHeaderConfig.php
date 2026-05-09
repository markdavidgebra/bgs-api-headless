<?php

namespace App\Support;

use App\Models\AppSetting;
use Illuminate\Support\Str;

class PageHeaderConfig
{
    public const DEFAULT_BACKGROUND_PATH = 'frontend/assets/images/backgrounds/page-header-bg.jpg';

    public const ABOUT_BACKGROUND_KEY = 'page_header_about_background';

    public const APPOINTMENT_BACKGROUND_KEY = 'page_header_appointment_background';

    public const CONTACT_BACKGROUND_KEY = 'page_header_contact_background';

    public const DOCTOR_BACKGROUND_KEY = 'page_header_doctor_background';

    public const DOCTOR_DETAILS_BACKGROUND_KEY = 'page_header_doctor_details_background';

    public const FAQ_BACKGROUND_KEY = 'page_header_faq_background';

    public const PRICING_BACKGROUND_KEY = 'page_header_pricing_background';

    public const PRODUCTS_BACKGROUND_KEY = 'page_header_products_background';

    public const PRODUCT_SHOW_BACKGROUND_KEY = 'page_header_product_show_background';

    public const SERVICES_BACKGROUND_KEY = 'page_header_services_background';

    public const SERVICE_SHOW_BACKGROUND_KEY = 'page_header_service_show_background';

    public const TESTIMONIALS_BACKGROUND_KEY = 'page_header_testimonials_background';

    public const TESTIMONIAL_SHOW_BACKGROUND_KEY = 'page_header_testimonial_show_background';

    public const NOT_FOUND_BACKGROUND_KEY = 'page_header_404_background';

    public const LOGIN_PAGE_BACKGROUND_KEY = 'page_header_login_background';

    public const SIGN_UP_PAGE_BACKGROUND_KEY = 'page_header_sign_up_background';

    public static function defaultBackgroundPath(): string
    {
        return self::DEFAULT_BACKGROUND_PATH;
    }

    public static function aboutBackgroundUrl(): string
    {
        return self::resolveBackgroundUrl(self::ABOUT_BACKGROUND_KEY);
    }

    public static function aboutBackgroundStoredPath(): ?string
    {
        return self::storedPath(self::ABOUT_BACKGROUND_KEY);
    }

    public static function appointmentBackgroundUrl(): string
    {
        return self::resolveBackgroundUrl(self::APPOINTMENT_BACKGROUND_KEY);
    }

    public static function appointmentBackgroundStoredPath(): ?string
    {
        return self::storedPath(self::APPOINTMENT_BACKGROUND_KEY);
    }

    public static function contactBackgroundUrl(): string
    {
        return self::resolveBackgroundUrl(self::CONTACT_BACKGROUND_KEY);
    }

    public static function contactBackgroundStoredPath(): ?string
    {
        return self::storedPath(self::CONTACT_BACKGROUND_KEY);
    }

    public static function doctorBackgroundUrl(): string
    {
        return self::resolveBackgroundUrl(self::DOCTOR_BACKGROUND_KEY);
    }

    public static function doctorBackgroundStoredPath(): ?string
    {
        return self::storedPath(self::DOCTOR_BACKGROUND_KEY);
    }

    public static function doctorDetailsBackgroundUrl(): string
    {
        return self::resolveBackgroundUrl(self::DOCTOR_DETAILS_BACKGROUND_KEY);
    }

    public static function doctorDetailsBackgroundStoredPath(): ?string
    {
        return self::storedPath(self::DOCTOR_DETAILS_BACKGROUND_KEY);
    }

    public static function faqBackgroundUrl(): string
    {
        return self::resolveBackgroundUrl(self::FAQ_BACKGROUND_KEY);
    }

    public static function faqBackgroundStoredPath(): ?string
    {
        return self::storedPath(self::FAQ_BACKGROUND_KEY);
    }

    public static function pricingBackgroundUrl(): string
    {
        return self::resolveBackgroundUrl(self::PRICING_BACKGROUND_KEY);
    }

    public static function pricingBackgroundStoredPath(): ?string
    {
        return self::storedPath(self::PRICING_BACKGROUND_KEY);
    }

    public static function productsBackgroundUrl(): string
    {
        return self::resolveBackgroundUrl(self::PRODUCTS_BACKGROUND_KEY);
    }

    public static function productsBackgroundStoredPath(): ?string
    {
        return self::storedPath(self::PRODUCTS_BACKGROUND_KEY);
    }

    public static function productShowBackgroundUrl(): string
    {
        return self::resolveBackgroundUrl(self::PRODUCT_SHOW_BACKGROUND_KEY);
    }

    public static function productShowBackgroundStoredPath(): ?string
    {
        return self::storedPath(self::PRODUCT_SHOW_BACKGROUND_KEY);
    }

    public static function servicesBackgroundUrl(): string
    {
        return self::resolveBackgroundUrl(self::SERVICES_BACKGROUND_KEY);
    }

    public static function servicesBackgroundStoredPath(): ?string
    {
        return self::storedPath(self::SERVICES_BACKGROUND_KEY);
    }

    public static function serviceShowBackgroundUrl(): string
    {
        return self::resolveBackgroundUrl(self::SERVICE_SHOW_BACKGROUND_KEY);
    }

    public static function serviceShowBackgroundStoredPath(): ?string
    {
        return self::storedPath(self::SERVICE_SHOW_BACKGROUND_KEY);
    }

    public static function testimonialsBackgroundUrl(): string
    {
        return self::resolveBackgroundUrl(self::TESTIMONIALS_BACKGROUND_KEY);
    }

    public static function testimonialsBackgroundStoredPath(): ?string
    {
        return self::storedPath(self::TESTIMONIALS_BACKGROUND_KEY);
    }

    public static function testimonialShowBackgroundUrl(): string
    {
        return self::resolveBackgroundUrl(self::TESTIMONIAL_SHOW_BACKGROUND_KEY);
    }

    public static function testimonialShowBackgroundStoredPath(): ?string
    {
        return self::storedPath(self::TESTIMONIAL_SHOW_BACKGROUND_KEY);
    }

    public static function notFoundBackgroundUrl(): string
    {
        return self::resolveBackgroundUrl(self::NOT_FOUND_BACKGROUND_KEY);
    }

    public static function notFoundBackgroundStoredPath(): ?string
    {
        return self::storedPath(self::NOT_FOUND_BACKGROUND_KEY);
    }

    public static function loginPageBackgroundUrl(): string
    {
        return self::resolveBackgroundUrl(self::LOGIN_PAGE_BACKGROUND_KEY);
    }

    public static function loginPageBackgroundStoredPath(): ?string
    {
        return self::storedPath(self::LOGIN_PAGE_BACKGROUND_KEY);
    }

    public static function signUpPageBackgroundUrl(): string
    {
        return self::resolveBackgroundUrl(self::SIGN_UP_PAGE_BACKGROUND_KEY);
    }

    public static function signUpPageBackgroundStoredPath(): ?string
    {
        return self::storedPath(self::SIGN_UP_PAGE_BACKGROUND_KEY);
    }

    private static function resolveBackgroundUrl(string $key): string
    {
        $raw = AppSetting::getValue($key);
        if (! $raw) {
            return asset(self::defaultBackgroundPath());
        }

        $raw = trim($raw);
        if (Str::startsWith($raw, ['http://', 'https://', '//'])) {
            return $raw;
        }

        return asset(ltrim($raw, '/'));
    }

    private static function storedPath(string $key): ?string
    {
        $raw = AppSetting::getValue($key);

        return $raw ? trim($raw) : null;
    }
}
