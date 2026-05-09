<?php

namespace App\Support;

use App\Models\AppSetting;
use Illuminate\Support\Str;

class SiteFooterConfig
{
    public const SETTING_KEY = 'site_footer';

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'newsletter_title' => "Ready To Get Started\nWith Service",
            'newsletter_email_placeholder' => 'Enter your email',
            'newsletter_blurb' => 'Health care is a vital aspect of maintaining overall well-being, encompassing a range of services from preventive care to treatment for your life',
            'social_links' => [
                ['icon' => 'facebook', 'url' => '/contact'],
                ['icon' => 'twitter', 'url' => '/contact'],
                ['icon' => 'instagram', 'url' => '/contact'],
                ['icon' => 'pinterest', 'url' => '/contact'],
            ],
            'department_title' => 'Department',
            'department_links' => [
                ['label' => 'Vitality Vitals Clinic', 'url' => '/vitality-health-solutions'],
                ['label' => 'Medical Heath Care', 'url' => '/pure-life-health-services'],
                ['label' => 'Care Plus Family Physicians', 'url' => '/harmony-family-health-medical'],
                ['label' => 'Swift Care Urgent Center', 'url' => '/wellSpring-wellness-center'],
                ['label' => 'Renewal Rehab Services', 'url' => '/evergreen-medical-center'],
            ],
            'contact_title' => 'Contact',
            'contact_address_label' => 'Address',
            'contact_address' => '66 Broklyant,India',
            'contact_phone_label' => 'Phone Number',
            'contact_phone' => '012 345 678 9101',
            'contact_email_label' => 'Email',
            'contact_email' => 'abcd@gmail.com',
            'page_links_title' => 'Page',
            'page_links' => [
                ['label' => 'About Us', 'url' => '/about-us'],
                ['label' => 'Services', 'url' => '/our-services'],
                ['label' => 'Why Chose Us', 'url' => '/about-us'],
                ['label' => 'Doctors', 'url' => '/doctor'],
                ['label' => 'Blog And News', 'url' => '/blog'],
            ],
            'copyright_brand' => 'Careon',
            'copyright_brand_url' => '/about-us',
            'copyright_year' => (string) now()->year,
            'copyright_suffix' => 'All Rights Reserved',
            'bottom_links' => [
                ['label' => 'Terms & Condition', 'url' => '/about-us'],
                ['label' => 'Privacy Policy', 'url' => '/about-us'],
                ['label' => 'Contact Us', 'url' => '/contact'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function get(): array
    {
        $raw = AppSetting::getValue(self::SETTING_KEY);
        if (! $raw) {
            return self::defaults();
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return self::defaults();
        }

        return array_merge(self::defaults(), $decoded);
    }

    public static function iconClass(string $icon): string
    {
        $icon = strtolower(trim($icon));

        return match ($icon) {
            'facebook' => 'icon-facebook',
            'twitter' => 'icon-twitter',
            'instagram' => 'icon-instagram',
            'pinterest' => 'icon-pinterest',
            'linkedin' => 'icon-linkedin',
            'youtube' => 'icon-youtube',
            default => 'icon-facebook',
        };
    }

    public static function publicUrl(?string $href): string
    {
        $href = trim((string) $href);
        if ($href === '' || $href === '#') {
            return '#';
        }

        if (Str::startsWith($href, ['http://', 'https://', '//'])) {
            return $href;
        }

        if (Str::startsWith($href, '/')) {
            return $href;
        }

        return url($href);
    }

    public static function telHref(string $phone): string
    {
        $digits = preg_replace('/[^\d+]/', '', $phone) ?? '';

        return $digits !== '' ? 'tel:'.$digits : '#';
    }

    public static function mailtoHref(string $email): string
    {
        $email = trim($email);

        return $email !== '' ? 'mailto:'.$email : '#';
    }
}
