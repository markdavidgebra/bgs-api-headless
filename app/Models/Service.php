<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Service extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceFactory> */
    use HasFactory;

    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(Doctor::class, 'doctor_service')->withTimestamps();
    }

    /**
     * Font Awesome 5 class strings for home listing cards. Hand-curated options appear first; the rest are read from
     * `public/frontend/assets/css/font-awesome-all.css` (~1.9k glyphs in this bundle) with auto-generated labels.
     *
     * @return array<string, string> class string => label
     */
    public static function iconClassSelectOptions(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $curated = self::curatedIconClassSelectOptions();
        $fromCss = self::bulkIconClassSelectOptionsFromFontAwesomeCss($curated);

        return $cache = $curated + $fromCss;
    }

    /**
     * Preferred icons (shown first with custom labels).
     *
     * @return array<string, string>
     */
    protected static function curatedIconClassSelectOptions(): array
    {
        return [
            '' => 'Default (rotate icons on home)',
            'fas fa-spa' => 'Spa & relaxation',
            'fas fa-hot-tub' => 'Hydrotherapy',
            'fas fa-bath' => 'Bath & soak',
            'fas fa-magic' => 'Glow & magic',
            'fas fa-wand-magic' => 'Transformation',
            'fas fa-hand-holding-magic' => 'Expert touch',
            'fas fa-star' => 'Star',
            'fas fa-stars' => 'Luxury sparkle',
            'fas fa-heart' => 'Care & love',
            'fas fa-gem' => 'Premium / jewel',
            'fas fa-crown' => 'VIP treatment',
            'fas fa-ribbon' => 'Special offer feel',
            'fas fa-award' => 'Award quality',
            'fas fa-sun' => 'Radiance',
            'fas fa-moon' => 'Night repair',
            'fas fa-cloud-sun' => 'Fresh glow',
            'fas fa-rainbow' => 'Brightening',
            'fas fa-feather' => 'Soft & gentle',
            'fas fa-feather-alt' => 'Light touch',
            'fas fa-leaf' => 'Botanical',
            'fas fa-leaf-heart' => 'Natural care',
            'fas fa-seedling' => 'Renewal',
            'fas fa-flower' => 'Floral',
            'fas fa-flower-tulip' => 'Spring fresh',
            'fas fa-flower-daffodil' => 'Bright floral',
            'fas fa-eye' => 'Eye focus',
            'fas fa-eye-dropper' => 'Serum / precision',
            'fas fa-smile' => 'Smile',
            'fas fa-smile-beam' => 'Beaming smile',
            'fas fa-kiss-wink-heart' => 'Playful glam',
            'fas fa-grin-hearts' => 'Delight',
            'fas fa-palette' => 'Color & contour',
            'fas fa-paint-brush' => 'Makeup brush',
            'fas fa-paint-brush-alt' => 'Artful detail',
            'fas fa-paint-roller' => 'Even finish',
            'fas fa-brush' => 'Hair brush / styling',
            'fas fa-spray-can' => 'Mist & spray',
            'fas fa-cut' => 'Cut & shape',
            'fas fa-hand-holding-heart' => 'Nurturing care',
            'fas fa-hands-heart' => 'Healing hands',
            'fas fa-hands' => 'Hands-on care',
            'fas fa-snowflake' => 'Cooling treatment',
            'fas fa-icicles' => 'Cryo / cool',
            'fas fa-venus' => 'Feminine silhouette',
            'fas fa-tshirt' => 'Style & wardrobe',
            'fas fa-camera-retro' => 'Before & after',
            'fas fa-image' => 'Gallery / results',
            'fas fa-wine-glass-alt' => 'Spa lounge',
            'fas fa-infinity' => 'Timeless beauty',
            'fas fa-om' => 'Mind-body balance',
            'fas fa-apple-alt' => 'Fresh vitality',
            'fas fa-lips' => 'Lips & glam',
            'fas fa-water' => 'Hydration',
            'fas fa-tint' => 'Essence / fluid',
            'fas fa-diamond' => 'Diamond',
            'fas fa-ring' => 'Elegance ring',
            'fas fa-rings-wedding' => 'Bridal glow',
            'fas fa-mug-hot' => 'Warm tea / tonic',
            'fas fa-coffee' => 'Coffee lounge',
            'fas fa-cocktail' => 'Lounge cocktail',
            'fas fa-glass-champagne' => 'Champagne',
            'fas fa-glass-cheers' => 'Cheers / celebrate',
            'fas fa-wine-bottle' => 'Wine lounge',
            'fas fa-shower' => 'Refresh shower',
            'fas fa-theater-masks' => 'Face mask',
            'fas fa-mask' => 'Treatment mask',
            'fas fa-toothbrush' => 'Smile care',
            'fas fa-teeth' => 'Smile line',
            'fas fa-praying-hands' => 'Mindful moment',
            'fas fa-umbrella-beach' => 'Resort beach',
            'fas fa-tree-palm' => 'Palm escape',
            'fas fa-sunset' => 'Golden hour',
            'fas fa-sunrise' => 'Morning renewal',
            'fas fa-cloud-moon' => 'Evening calm',
            'fas fa-clouds-sun' => 'Soft daylight',
            'fas fa-wind' => 'Fresh breeze',
            'fas fa-lightbulb-on' => 'Bright idea / glow',
            'fas fa-candle-holder' => 'Candlelit calm',
            'fas fa-hand-holding-water' => 'Hydration ritual',
            'fas fa-hand-holding-seedling' => 'Growth & care',
            'fas fa-glasses' => 'Styling frames',
            'fas fa-camera' => 'Photo ready',
            'fas fa-gift' => 'Gift & pamper',
            'fas fa-wand' => 'Wand accent',
            'fas fa-heart-circle' => 'Care circle',
            'fas fa-smile-plus' => 'Extra smile',
            'fas fa-grin-stars' => 'Joy & stars',
            'fas fa-kiss-beam' => 'Beaming kiss',
            'fas fa-leaf-oak' => 'Oak botanical',
            'fas fa-mountain' => 'Retreat',
            'fas fa-umbrella' => 'Gentle cover',
            'fas fa-calendar-star' => 'Special booking',
            'fas fa-music' => 'Ambient calm',
            'fas fa-headphones' => 'Sound relax',
            'fas fa-hourglass-half' => 'Care over time',
            'fas fa-bolt' => 'Energy & lift',
            'fas fa-fire-alt' => 'Warm glow',
            'fas fa-female' => 'Silhouette',
            'fas fa-balance-scale' => 'Balance & harmony',

            'fas fa-dove' => 'Serenity',
            'fas fa-peace' => 'Peace symbol',
            'fas fa-yin-yang' => 'Balance (yin & yang)',
            'fas fa-meteor' => 'Shooting star',
            'fas fa-cloud-sun-rain' => 'Soft rain & sun',
            'fas fa-cloud-moon-rain' => 'Evening shower',
            'fas fa-pray' => 'Quiet reflection',
            'fas fa-lightbulb' => 'Brighten up',
            'fas fa-couch' => 'Lounge',
            'fas fa-bed' => 'Rest & recovery',
            'fas fa-concierge-bell' => 'Concierge service',
            'fas fa-glass-whiskey' => 'Spirits lounge',
            'fas fa-cookie' => 'Sweet treat',
            'fas fa-ice-cream' => 'Frozen treat',
            'fas fa-lemon' => 'Citrus fresh',
            'fas fa-swimmer' => 'Aquatic wellness',
            'fas fa-swimming-pool' => 'Pool / hydro',
            'fas fa-anchor' => 'Grounded calm',
            'fas fa-medal' => 'Excellence',
            'fas fa-certificate' => 'Certified quality',
            'fas fa-scroll' => 'Ritual / heritage',
            'fas fa-torii-gate' => 'Zen gate',
            'fas fa-place-of-worship' => 'Sacred moment',
            'fas fa-dharmachakra' => 'Wellness wheel',
            'fas fa-archway' => 'Grand entrance',
            'fas fa-landmark' => 'Destination',
            'fas fa-igloo' => 'Cool escape',
            'fas fa-portrait' => 'Portrait beauty',
            'fas fa-fill-drip' => 'Color fill',
            'fas fa-splotch' => 'Organic shape',
            'fas fa-socks' => 'Cozy detail',
            'fas fa-shopping-bag' => 'Boutique bag',
            'fas fa-shopping-basket' => 'Shopping basket',
            'fas fa-laugh-beam' => 'Radiant laugh',
            'fas fa-surprise' => 'Delight',
            'fas fa-grin-wink' => 'Playful wink',
            'fas fa-smile-wink' => 'Friendly wink',
            'fas fa-kiss' => 'Kiss',
            'fas fa-user-friends' => 'Together / duo',
            'fas fa-users' => 'Group experience',
            'fas fa-user' => 'You & care',
            'fas fa-baby' => 'Gentle care',
            'fas fa-paw' => 'Pet-friendly spa',
            'fas fa-horse' => 'Elegant strength',
            'fas fa-cat' => 'Calm whiskers',
            'fas fa-dog' => 'Welcoming vibe',
            'fas fa-candy-cane' => 'Holiday glow',
            'fas fa-holly-berry' => 'Winter berries',
            'fas fa-snowman' => 'Winter play',
            'fas fa-birthday-cake' => 'Celebrate',
            'fas fa-star-half' => 'Half star',
            'fas fa-star-half-alt' => 'Rating highlight',
            'fas fa-cloud' => 'Soft clouds',
            'fas fa-wine-glass' => 'Wine glass',
            'fas fa-glass-martini' => 'Classic martini',
            'fas fa-beer' => 'Craft unwind',
            'fas fa-cheese' => 'Cheese board',
            'fas fa-bread-slice' => 'Bakery moment',
            'fas fa-egg' => 'Fresh start',
            'fas fa-burn' => 'Warmth & energy',
            'fas fa-highlighter' => 'Highlight stroke',
            'fas fa-tape' => 'Tape / tuck',
            'fas fa-suitcase' => 'Travel escape',
            'fas fa-suitcase-rolling' => 'Rolling luggage',
            'fas fa-plane-departure' => 'Getaway',
            'fas fa-ship' => 'Voyage calm',
            'fas fa-bicycle' => 'Light activity',
            'fas fa-skiing' => 'Alpine fresh',
            'fas fa-snowboarding' => 'Mountain play',
            'fas fa-hotdog' => 'Casual bite',
            'fas fa-pepper-hot' => 'Spicy warmth',
            'fas fa-carrot' => 'Garden glow',
            'fas fa-hat-wizard' => 'Whimsical glam',
            'fas fa-tooth' => 'Tooth focus',
            'fas fa-swatchbook' => 'Swatch book',
            'fas fa-pen-nib' => 'Fine liner',
            'fas fa-pen-fancy' => 'Calligraphy pen',
            'fas fa-marker' => 'Bold marker',
            'fas fa-bezier-curve' => 'Smooth curves',
            'fas fa-vector-square' => 'Layout precision',
            'fas fa-drafting-compass' => 'Precision contour',
            'fas fa-mitten' => 'Cozy winter',
            'fas fa-campground' => 'Outdoor retreat',
        ];
    }

    /**
     * Every `.fa-{name}:before` in the bundled CSS not already in the curated list. Labels are generated from the slug.
     *
     * @param  array<string, string>  $curated
     * @return array<string, string>
     */
    protected static function bulkIconClassSelectOptionsFromFontAwesomeCss(array $curated): array
    {
        $skip = array_flip(array_keys($curated));
        $solid = self::fontAwesomeSolidSlugLookup();
        if ($solid === []) {
            return [];
        }

        $out = [];

        foreach (self::fontAwesomeIconNamesFromCss() as $slug) {
            if (! isset($solid[$slug])) {
                continue;
            }

            if (self::shouldExcludeFontAwesomeCssIconSlug($slug)) {
                continue;
            }

            if (self::shouldExcludeClinicalOrHospitalIconSlug($slug)) {
                continue;
            }

            $class = 'fas fa-'.$slug;
            if (isset($skip[$class])) {
                continue;
            }

            $out[$class] = Str::headline(str_replace('-', ' ', $slug));
        }

        uksort($out, 'strcasecmp');

        return $out;
    }

    /**
     * Slugs that have a solid glyph in Font Awesome 5 (from community metadata; matches `fas`).
     * JSON is derived from Font Awesome 5.15.4 `metadata/icons.json` (icons whose `styles` include `solid`).
     *
     * @return array<string, true>
     */
    protected static function fontAwesomeSolidSlugLookup(): array
    {
        static $lookup = null;

        if ($lookup !== null) {
            return $lookup;
        }

        $path = resource_path('data/fontawesome5-solid-slugs.json');
        if (! is_readable($path)) {
            return $lookup = [];
        }

        $json = file_get_contents($path);
        if ($json === false) {
            return $lookup = [];
        }

        $list = json_decode($json, true);
        if (! is_array($list)) {
            return $lookup = [];
        }

        $lookup = array_fill_keys($list, true);

        return $lookup;
    }

    /**
     * Icon slugs parsed once from the project Font Awesome CSS (Pro all.css).
     *
     * @return list<string>
     */
    protected static function fontAwesomeIconNamesFromCss(): array
    {
        static $names = null;

        if ($names !== null) {
            return $names;
        }

        $path = public_path('frontend/assets/css/font-awesome-all.css');
        if (! is_readable($path)) {
            return $names = [];
        }

        $css = file_get_contents($path);
        if ($css === false) {
            return $names = [];
        }

        if (! preg_match_all('/\.fa-([a-z0-9-]+):before\s*\{/', $css, $matches)) {
            return $names = [];
        }

        $names = array_values(array_unique($matches[1]));
        sort($names);

        return $names;
    }

    /**
     * Drop glyphs that are mostly payment UI or Font Awesome “logo” marks; `fas` often matches these poorly anyway.
     */
    protected static function shouldExcludeFontAwesomeCssIconSlug(string $slug): bool
    {
        if (str_starts_with($slug, 'cc-')) {
            return true;
        }

        if (str_starts_with($slug, 'font-awesome')) {
            return true;
        }

        return false;
    }

    /**
     * Remove obvious clinical / emergency imagery from the auto-generated bulk set (curated list is unchanged).
     */
    protected static function shouldExcludeClinicalOrHospitalIconSlug(string $slug): bool
    {
        return in_array($slug, [
            'ambulance',
            'hospital',
            'hospital-alt',
            'hospital-symbol',
            'hospital-user',
            'hospitals',
            'user-md',
            'user-nurse',
            'stethoscope',
            'syringe',
            'pills',
            'capsules',
            'prescription',
            'prescription-bottle',
            'prescription-bottle-alt',
            'band-aid',
            'medkit',
            'notes-medical',
            'clinic-medical',
            'briefcase-medical',
            'book-medical',
            'file-medical',
            'file-medical-alt',
            'first-aid',
            'heartbeat',
            'disease',
            'diagnoses',
            'microscope',
            'x-ray',
            'crutch',
            'vials',
            'vial',
            'tablets',
            'laptop-medical',
            'comment-medical',
            'hand-holding-medical',
            'pump-medical',
            'head-side-virus',
            'head-side-cough',
            'head-side-cough-slash',
            'lungs-virus',
            'virus',
            'viruses',
            'shield-virus',
            'bacteria',
            'bacterium',
            'biohazard',
            'radiation',
            'radiation-alt',
            'dna',
        ], true);
    }

    /**
     * Legacy Flaticon-style classes — mapped to aesthetic Font Awesome (not clinical icons).
     *
     * @return array<string, string>
     */
    public static function legacyFlaticonToFontAwesomeMap(): array
    {
        return [
            'icon-pills' => 'fas fa-gem',
            'icon-broken-bone' => 'fas fa-feather',
            'icon-pills-3' => 'fas fa-star',
            'icon-injection' => 'fas fa-eye-dropper',
            'icon-injection-2' => 'fas fa-magic',
            'icon-quaity-care' => 'fas fa-spa',
            'icon-quaity-care-2' => 'fas fa-hands-heart',
            'icon-quaity-care-3' => 'fas fa-leaf-heart',
            'icon-quaity-care-4' => 'fas fa-crown',
            'icon-call' => 'fas fa-hand-holding-heart',
            'icon-quote' => 'fas fa-ribbon',
            'icon-star' => 'fas fa-star',
        ];
    }

    /**
     * Values accepted on create/update (presets plus legacy DB values until re-saved).
     *
     * @return list<string>
     */
    public static function allowedIconClassesForValidation(): array
    {
        return array_values(array_unique(array_merge(
            array_keys(self::iconClassSelectOptions()),
            array_keys(self::legacyFlaticonToFontAwesomeMap()),
        )));
    }

    /**
     * Font Awesome classes to show in the admin icon dropdown (preview for each option value).
     */
    public static function iconPreviewClassForPicker(?string $stored): string
    {
        if (trim((string) $stored) === '') {
            return 'fas fa-sync-alt';
        }

        $icon = (new self(['icon_class' => $stored]))->display_icon_class;

        return $icon !== '' ? $icon : 'fas fa-question-circle';
    }

    /**
     * Options for the edit form, including saved class if it is not in the preset list.
     *
     * @return array<string, string>
     */
    public static function iconClassSelectOptionsForEdit(?string $savedIconClass): array
    {
        $options = self::iconClassSelectOptions();
        $saved = trim((string) $savedIconClass);
        if ($saved !== '' && ! array_key_exists($saved, $options)) {
            $options = [$saved => $saved.' (saved)'] + $options;
        }

        return $options;
    }

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'price',
        'promo_price',
        'duration_minutes',
        'session_count',
        'image',
        'icon_class',
        'recovery_time',
        'max_appointments_per_day',
        'status',
        'is_featured',
        'is_bookable',
        'before_care',
        'after_care',
        'notes',
    ];

    /**
     * Computed attributes exposed on JSON responses for the admin UI.
     *
     * @var list<string>
     */
    protected $appends = [
        'image_url',
        'duration_label',
        'summary_text',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'promo_price' => 'decimal:2',
            'duration_minutes' => 'integer',
            'session_count' => 'integer',
            'max_appointments_per_day' => 'integer',
            'is_featured' => 'boolean',
            'is_bookable' => 'boolean',
        ];
    }

    public function getStatusLabelAttribute()
    {
        return $this->status ?? 'active';
    }

    public function getStatusBadgeAttribute()
    {
        return ($this->status ?? 'active') === 'active'
            ? 'bg-green-lt'
            : 'bg-secondary-lt';
    }

    public function getDurationLabelAttribute()
    {
        return $this->duration_minutes !== null
            ? $this->duration_minutes.' mins'
            : '—';
    }

    public function getSummaryTextAttribute()
    {
        if ($this->short_description) {
            return $this->short_description;
        }

        $description = Str::limit(strip_tags($this->description ?? ''), 80);

        return $description ?: '—';
    }

    /**
     * Font Awesome class string for the home listing card. Empty means use rotation fallback.
     */
    public function getDisplayIconClassAttribute(): string
    {
        $raw = trim((string) ($this->icon_class ?? ''));

        if ($raw === '') {
            return '';
        }

        $legacy = self::legacyFlaticonToFontAwesomeMap();
        if (isset($legacy[$raw])) {
            return $legacy[$raw];
        }

        $sanitized = trim((string) preg_replace('/[^a-z0-9\s_-]/i', '', $raw));
        $sanitized = trim(preg_replace('/\s+/', ' ', $sanitized));

        return $sanitized;
    }

    public function getImageUrlAttribute()
    {
        $fallbackImg = asset('frontend/assets/images/resources/service-details-img-1.jpg');

        if (! $this->image) {
            return $fallbackImg;
        }

        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->image;
        }

        if (is_file(public_path($this->image))) {
            return asset($this->image);
        }

        return asset('storage/'.$this->image);
    }
}
