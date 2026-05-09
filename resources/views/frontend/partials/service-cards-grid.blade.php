@php
  $services = $services ?? collect();
  $icons = ['fas fa-spa', 'fas fa-gem', 'fas fa-magic'];
  $tagline = $tagline ?? 'Latest service';
  $titleLine1 = $titleLine1 ?? 'Compassionate Care Always';
  $titleLine2 = $titleLine2 ?? 'There Health First';
  $emptyCtaRoute = $emptyCtaRoute ?? null;
  $emptyCtaLabel = $emptyCtaLabel ?? 'Book a visit';
@endphp

<section class="services-two services-two--home-cards @isset($sectionClass) {{ $sectionClass }} @endisset">
  <div class="container">
    <div class="section-title-two text-center sec-title-animation animation-style1">
      <h6 class="section-title-two__tagline">{{ $tagline }}</h6>
      <h3 class="section-title-two__title title-animation">
        {{ $titleLine1 }}<br> {{ $titleLine2 }}
      </h3>
    </div>

    <div class="services-two__inner">
      <ul class="row g-4 list-unstyled justify-content-center">
        @forelse ($services as $index => $service)
          @php
            $icon = $service->display_icon_class ?: $icons[$index % count($icons)];
            $href = $service->slug
                ? route('services.show', $service->slug)
                : route('our-services');
            $summary = $service->summary_text;
            $excerpt = $summary !== '—' ? $summary : 'Learn more about this service.';
            $metaParts = array_filter([
                $service->duration_minutes ? $service->duration_label : null,
                $service->session_count ? $service->session_count.' session'.($service->session_count !== 1 ? 's' : '') : null,
            ]);
            $meta = $metaParts ? implode(' · ', $metaParts) : null;
          @endphp
          <li class="col-xl-4 col-lg-6 d-flex">
            <article class="home-service-card w-100">
              <a href="{{ $href }}" class="home-service-card__media">
                <img src="{{ $service->image_url }}" alt="{{ $service->name }}" loading="lazy" width="480" height="320">
              </a>
              <div class="home-service-card__body">
                <div class="home-service-card__icon" aria-hidden="true">
                  <i class="{{ $icon }}"></i>
                </div>
                <h3 class="home-service-card__title">
                  <a href="{{ $href }}">{{ $service->name }}</a>
                </h3>
                @if ($meta)
                  <p class="home-service-card__meta">{{ $meta }}</p>
                @endif
                <p class="home-service-card__text">{{ $excerpt }}</p>
                <div class="home-service-card__actions">
                  <a href="{{ $href }}" class="thm-btn">
                    Read more <span class="icon-plus"></span>
                  </a>
                </div>
              </div>
            </article>
          </li>
        @empty
          <li class="col-12">
            <div class="home-service-card text-center py-5 px-4">
              <p class="mb-3 home-service-card__text">No services are available yet. Please check back soon.</p>
              @if ($emptyCtaRoute)
                <a href="{{ $emptyCtaRoute }}" class="thm-btn">{{ $emptyCtaLabel }}</a>
              @endif
            </div>
          </li>
        @endforelse
      </ul>
    </div>
  </div>
</section>
