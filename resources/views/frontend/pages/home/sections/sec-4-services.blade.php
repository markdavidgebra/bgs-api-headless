@include('frontend.partials.service-cards-grid', [
    'services' => $homeServices ?? collect(),
    'emptyCtaRoute' => route('our-services'),
    'emptyCtaLabel' => 'View all services',
])
