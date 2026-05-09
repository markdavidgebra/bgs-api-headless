<?php

namespace App\Support;

use App\Models\AppSetting;

class ProductCatalogPageConfig
{
    public const TAGLINE_KEY = 'products_catalog_page_tagline';

    public const HEADING_KEY = 'products_catalog_page_heading';

    public const LEDE_KEY = 'products_catalog_page_lede';

    public const TRUST_ITEMS_KEY = 'products_catalog_trust_items';

    /**
     * Font Awesome 5 solid icon classes (without the leading "fas"), for the catalog trust row picker.
     *
     * @return array<string, string>
     */
    public static function iconOptions(): array
    {
        return [
            'fa-leaf-heart' => 'Leaf heart',
            'fa-shield-check' => 'Shield check',
            'fa-hand-holding-heart' => 'Hand holding heart',
            'fa-heart' => 'Heart',
            'fa-star' => 'Star',
            'fa-award' => 'Award',
            'fa-check-circle' => 'Check circle',
            'fa-spa' => 'Spa',
            'fa-seedling' => 'Seedling',
            'fa-pump-medical' => 'Medical',
            'fa-user-md' => 'Clinician',
            'fa-hand-sparkles' => 'Hand sparkles',
            'fa-certificate' => 'Certificate',
            'fa-truck' => 'Truck / delivery',
            'fa-box-open' => 'Box open',
            'fa-gift' => 'Gift',
            'fa-leaf' => 'Leaf',
            'fa-notes-medical' => 'Medical notes',
            'fa-briefcase-medical' => 'Briefcase medical',
            'fa-smile-beam' => 'Smile',
            'custom' => 'Custom class…',
        ];
    }

    /**
     * @return list<string>
     */
    public static function presetIconValues(): array
    {
        return array_values(array_filter(
            array_keys(self::iconOptions()),
            fn (string $k) => $k !== 'custom'
        ));
    }

    /**
     * @return list<array{icon: string, label: string}>
     */
    public static function defaultTrustItems(): array
    {
        return [
            ['icon' => 'fa-leaf-heart', 'label' => 'Treatment-informed picks'],
            ['icon' => 'fa-shield-check', 'label' => 'Authentic clinic supply'],
            ['icon' => 'fa-hand-holding-heart', 'label' => 'Staff favorites'],
        ];
    }

    /**
     * Rows for the admin form (includes icon select value + optional custom class).
     *
     * @return list<array{icon: string, icon_custom: string, label: string}>
     */
    public static function trustItemsForForm(): array
    {
        $raw = AppSetting::getValue(self::TRUST_ITEMS_KEY);
        if ($raw === null || trim($raw) === '') {
            return self::defaultTrustItemsForForm();
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded) || $decoded === []) {
            return self::defaultTrustItemsForForm();
        }

        $presets = self::presetIconValues();
        $out = [];
        foreach ($decoded as $row) {
            if (! is_array($row)) {
                continue;
            }
            $icon = isset($row['icon']) ? trim((string) $row['icon']) : '';
            $label = isset($row['label']) ? trim((string) $row['label']) : '';
            if ($icon === '' || $label === '' || ! preg_match('/^fa-[a-z0-9-]+$/', $icon)) {
                continue;
            }
            $out[] = [
                'icon' => in_array($icon, $presets, true) ? $icon : 'custom',
                'icon_custom' => in_array($icon, $presets, true) ? '' : $icon,
                'label' => $label,
            ];
        }

        return $out !== [] ? $out : self::defaultTrustItemsForForm();
    }

    /**
     * @return list<array{icon: string, icon_custom: string, label: string}>
     */
    private static function defaultTrustItemsForForm(): array
    {
        return array_map(fn (array $row) => [
            'icon' => $row['icon'],
            'icon_custom' => '',
            'label' => $row['label'],
        ], self::defaultTrustItems());
    }

    /**
     * @return list<array{icon: string, label: string}>
     */
    public static function trustItems(): array
    {
        $raw = AppSetting::getValue(self::TRUST_ITEMS_KEY);
        if ($raw === null || trim($raw) === '') {
            return self::defaultTrustItems();
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return self::defaultTrustItems();
        }

        $out = [];
        foreach ($decoded as $row) {
            if (! is_array($row)) {
                continue;
            }
            $icon = isset($row['icon']) ? trim((string) $row['icon']) : '';
            $label = isset($row['label']) ? trim((string) $row['label']) : '';
            if ($icon === '' || $label === '' || ! preg_match('/^fa-[a-z0-9-]+$/', $icon)) {
                continue;
            }
            $out[] = ['icon' => $icon, 'label' => $label];
        }

        return $out !== [] ? $out : self::defaultTrustItems();
    }

    public static function defaultTagline(): string
    {
        return 'Clinic boutique';
    }

    public static function defaultHeading(): string
    {
        return "Carefully chosen\nfor your home routine";
    }

    public static function defaultLede(): string
    {
        return 'Every item in our shop is selected to support the treatments you love—gentle, effective, and easy to weave into daily care.';
    }

    public static function tagline(): string
    {
        $v = AppSetting::getValue(self::TAGLINE_KEY);
        if ($v === null || trim($v) === '') {
            return self::defaultTagline();
        }

        return trim($v);
    }

    public static function heading(): string
    {
        $v = AppSetting::getValue(self::HEADING_KEY);
        if ($v === null || trim($v) === '') {
            return self::defaultHeading();
        }

        return $v;
    }

    public static function lede(): string
    {
        $v = AppSetting::getValue(self::LEDE_KEY);
        if ($v === null || trim($v) === '') {
            return self::defaultLede();
        }

        return trim($v);
    }
}
