@extends('frontend.layouts.master')
@section('title', 'Products')
@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/shop.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/page-header.css') }}" />
@endpush

@php
    $shopFallbackImg = asset('frontend/assets/images/shop/shop-product-1-1.jpg');
    $productHref = function (\App\Models\Product $p): string {
        return $p->slug
            ? route('our-products.show', $p->slug)
       
            : route('our-products');
    };
    $catalogLink = fn (array $params) => route('our-products', array_filter($params));
    $fallbackJs = "this.onerror=null;this.src='".$shopFallbackImg."';";
    $catalogProductImg = fn (\App\Models\Product $p): string => $p->image_url ?: $shopFallbackImg;
@endphp

@section('content')

<x-strickyHeader />

<!--Page Header Start-->
<section class="page-header">
    <div class="page-header__bg" style="background-image: url({{ $productsPageHeaderBgUrl ?? \App\Support\PageHeaderConfig::productsBackgroundUrl() }});"></div>
    <div class="container">
        <div class="page-header__inner">
            <h2>{{ $title ?? 'Products' }}</h2>
            <ul class="thm-breadcrumb list-unstyled">
                <li><a href="{{ url('/') }}">Home</a></li>
                <li><span>-</span></li>
                <li>{{ $subtitle ?? 'Products' }}</li>
            </ul>
        </div>
    </div>
</section>
<!--Page Header End-->

<!--Product Start-->
<section class="product product--catalog">
    <div class="container">
        <div class="section-title-two text-center sec-title-animation animation-style1 product-catalog-intro">
            <h6 class="section-title-two__tagline">{{ \App\Support\ProductCatalogPageConfig::tagline() }}</h6>
            <h3 class="section-title-two__title title-animation">{!! nl2br(e(\App\Support\ProductCatalogPageConfig::heading())) !!}</h3>
            <p class="product-catalog-intro__lede">
                {{ \App\Support\ProductCatalogPageConfig::lede() }}
            </p>
        </div>

        <ul class="product-catalog-trust list-unstyled">
            @foreach (\App\Support\ProductCatalogPageConfig::trustItems() as $trustRow)
                <li class="product-catalog-trust__item">
                    <i class="fas {{ e($trustRow['icon']) }}" aria-hidden="true"></i>
                    <span>{{ $trustRow['label'] }}</span>
                </li>
            @endforeach
        </ul>

        <div class="row">

            <div class="col-xl-9 col-lg-12">
                <div class="product__items">
                    <div class="product-catalog-toolbar">
                        <div class="product__showing-result">
                            <div class="product__showing-text-box">
                                <p class="product__showing-text">
                                    @if ($products->isEmpty())
                                        @if (request()->filled('category') || request()->filled('q'))
                                            Nothing matches those filters—try widening your search.
                                        @else
                                            Our boutique is being restocked. Please check back soon.
                                        @endif
                                    @else
                                        <span class="text-dark">{{ $products->count() }}</span>
                                        {{ \Illuminate\Support\Str::plural('product', $products->count()) }} ready for you
                                        @if (request()->filled('category'))
                                            <span class="text-muted">·</span> <span>{{ request('category') }}</span>
                                        @endif
                                        @if (request()->filled('q'))
                                            <span class="text-muted">·</span> matching “{{ request('q') }}”
                                        @endif
                                    @endif
                                </p>
                            </div>
                            <div class="product-catalog-sort" role="group" aria-label="Sort products">
                                <span class="product-catalog-sort__label">Sort</span>
                                <a href="{{ $catalogLink(['category' => request('category'), 'q' => request('q')]) }}"
                                    class="product-catalog-sort__pill is-active">A–Z</a>
                            </div>
                        </div>

                        @if (request()->filled('category') || request()->filled('q'))
                            <div class="product-catalog-filters">
                                @if (request()->filled('category'))
                                    <span class="product-catalog-chip">
                                        {{ request('category') }}
                                        <a href="{{ $catalogLink(['q' => request('q')]) }}"
                                            class="product-catalog-chip__dismiss" title="Remove category" aria-label="Remove category filter">&times;</a>
                                    </span>
                                @endif
                                @if (request()->filled('q'))
                                    <span class="product-catalog-chip">
                                        “{{ request('q') }}”
                                        <a href="{{ $catalogLink(['category' => request('category')]) }}"
                                            class="product-catalog-chip__dismiss" title="Clear search" aria-label="Clear search">&times;</a>
                                    </span>
                                @endif
                                <span class="product-catalog-chip product-catalog-chip--clear">
                                    <a href="{{ route('our-products') }}">Clear all</a>
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="product__all">
                        <div class="product__all-tab">
                            <div class="product__all-tab-button">
                                <ul class="tabs-button-box clearfix">
                                    <li data-tab="#grid" class="tab-btn-item active-btn-item">
                                        <div class="product__all-tab-button-icon one">
                                            <i class="fa fa-solid fa-bars" aria-hidden="true"></i>
                                        </div>
                                    </li>
                                    <li data-tab="#list" class="tab-btn-item">
                                        <div class="product__all-tab-button-icon">
                                            <i class="fa fa-solid fa-list-ul" aria-hidden="true"></i>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            <div class="tabs-content-box">
                                <div class="tab-content-box-item tab-content-box-item-active" id="grid">
                                    <div class="product__all-tab-content-box-item">
                                        <div class="product__all-tab-single">
                                            <div class="row">
                                                @forelse ($products as $product)
                                                    @php
                                                        $img = $catalogProductImg($product);
                                                        $href = $productHref($product);
                                                        $aosDelay = min(80 * $loop->index, 320);

                                                        $badgeLabel = null;
                                                        $badgeTone = null;
                                                        if ($product->created_at && $product->created_at->gt(now()->subDays(45))) {
                                                            $badgeLabel = 'New';
                                                            $badgeTone = 'is-new';
                                                        }
                                                    @endphp
                                                    <div class="col-xl-4 col-lg-6 col-md-6 mb-4 d-flex" data-aos="fade-up" data-aos-duration="650" data-aos-delay="{{ $aosDelay }}">
                                                        <div class="single-product-style1 w-100">
                                                            <div class="single-product-style1__img">
                                                                <img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy" width="400" height="400" onerror="{{ $fallbackJs }}">
                                                                <img src="{{ $img }}" alt="" aria-hidden="true" loading="lazy" width="400" height="400" onerror="{{ $fallbackJs }}">
                                                                @if ($badgeLabel)
                                                                    <div class="product-catalog-status">
                                                                        <span class="product-catalog-badge {{ $badgeTone }}">{{ $badgeLabel }}</span>
                                                                    </div>
                                                                @endif
                                                                
                                                            </div>
                                                            <div class="single-product-style1__content">
                                                                <div class="single-product-style1__content-left">
                                                                    @if (filled($product->category))
                                                                        <p class="product-catalog-card__kicker">{{ $product->category }}</p>
                                                                    @endif
                                                                    <h4>
                                                                        <a href="{{ $href }}">{{ $product->name }}</a>
                                                                    </h4>
                                                                    @if (filled($product->description))
                                                                        <p class="product-catalog-card__excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($product->description), 96) }}</p>
                                                                    @endif
                                                                    <div class="product-catalog-card__cta">
                                                                        <a href="{{ $href }}">View details <span class="icon-plus"></span></a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="col-12" data-aos="fade-up">
                                                        <div class="product-catalog-empty">
                                                            <div class="product-catalog-empty__icon" aria-hidden="true">
                                                                <i class="fas fa-store-alt"></i>
                                                            </div>
                                                            <h4>We’re curating your next favorites</h4>
                                                            <p>
                                                                @if (request()->filled('category') || request()->filled('q'))
                                                                    Adjust your filters or reset the catalog to see everything we carry.
                                                                @else
                                                                    New arrivals land here first. Meanwhile, our team can help you choose the right product in person.
                                                                @endif
                                                            </p>
                                                            @if (request()->filled('category') || request()->filled('q'))
                                                                <a href="{{ route('our-products') }}" class="thm-btn">Reset catalog <span class="icon-plus"></span></a>
                                                                <a href="{{ route('contact') }}" class="thm-btn" style="background: var(--black);">Talk to us <span class="icon-arrow-right"></span></a>
                                                            @else
                                                                <a href="{{ route('our-services') }}" class="thm-btn">Explore services <span class="icon-plus"></span></a>
                                                                <a href="{{ route('contact') }}" class="thm-btn" style="background: var(--black);">Contact <span class="icon-arrow-right"></span></a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-content-box-item" id="list">
                                    <div class="product__all-tab-content-box-item">
                                        <div class="product__all-tab-single">
                                            <div class="row">
                                                @foreach ($products as $product)
                                                    @php
                                                        $img = $catalogProductImg($product);
                                                        $href = $productHref($product);
                                                        $aosDelay = min(60 * $loop->index, 240);
                                                    @endphp
                                                    <div class="col-xl-6 col-lg-6 mb-4 d-flex" data-aos="fade-up" data-aos-duration="600" data-aos-delay="{{ $aosDelay }}">
                                                        <div class="single-product-style2 w-100">
                                                            <div class="row align-items-center">
                                                                <div class="col-xl-6 col-lg-6 col-md-6">
                                                                    <div class="single-product-style2__img">
                                                                        <img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy" width="400" height="400" onerror="{{ $fallbackJs }}">
                                                                        <img src="{{ $img }}" alt="" aria-hidden="true" loading="lazy" width="400" height="400" onerror="{{ $fallbackJs }}">
                                                                    </div>
                                                                </div>
                                                                <div class="col-xl-6 col-lg-6 col-md-6">
                                                                    <div class="single-product-style2__content">
                                                                        @if (filled($product->category))
                                                                            <p class="product-catalog-card__kicker">{{ $product->category }}</p>
                                                                        @endif
                                                                        <div class="single-product-style2__review">
                                                                            @for ($i = 0; $i < 5; $i++)
                                                                                <i class="fa fa-star"></i>
                                                                            @endfor
                                                                        </div>
                                                                        <div class="single-product-style2__text">
                                                                            <h4>
                                                                                <a href="{{ $href }}">{{ $product->name }}</a>
                                                                            </h4>
                                                                            @if (filled($product->description))
                                                                                <p class="small text-muted mt-2 mb-0">{{ \Illuminate\Support\Str::limit(strip_tags($product->description), 160) }}</p>
                                                                            @endif
                                                                        </div>
                                                                        <div class="product-catalog-card__cta mt-2">
                                                                            <a href="{{ $href }}">View details <span class="icon-plus"></span></a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-12">
                <div class="product__sidebar product__sidebar--sticky">
                    <div class="shop-search product__sidebar-single">
                        <form action="{{ route('our-products') }}" method="get">
                            @if (request()->filled('category'))
                                <input type="hidden" name="category" value="{{ request('category') }}">
                            @endif
                            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by name or brand" autocomplete="off">
                            <button type="submit"><i class="fa fa-search" aria-hidden="true"></i></button>
                        </form>
                    </div>
                    <div class="shop-category product__sidebar-single">
                        <h3 class="product__sidebar-title">Categories</h3>
                        <ul class="list-unstyled">
                            <li class="{{ ! request()->filled('category') ? 'active' : '' }}">
                                <a href="{{ $catalogLink(['q' => request('q')]) }}">All products</a>
                            </li>
                            @foreach ($categories as $cat)
                                <li class="{{ request('category') === $cat ? 'active' : '' }}">
                                    <a href="{{ $catalogLink(['category' => $cat, 'q' => request('q')]) }}">{{ $cat }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="shop-product-recent-products product__sidebar-single">
                        <h3 class="product__sidebar-title">Just updated</h3>
                        <ul class="clearfix">
                            @foreach ($recentProducts as $rp)
                                @php
                                    $thumb = $catalogProductImg($rp);
                                    $rphref = $productHref($rp);
                                @endphp
                                <li>
                                    <div class="img">
                                        <img src="{{ $thumb }}" alt="{{ $rp->name }}" loading="lazy" width="80" height="80" onerror="{{ $fallbackJs }}">
                                        <a href="{{ $rphref }}" aria-label="View {{ $rp->name }}"><i class="fa fa-link" aria-hidden="true"></i></a>
                                    </div>
                                    <div class="content">
                                        <div class="title">
                                            <h5><a href="{{ $rphref }}">{{ \Illuminate\Support\Str::limit($rp->name, 44) }}</a></h5>
                                        </div>
                                        <div class="review">
                                            <span class="small text-muted">{{ $rp->category }}</span>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    @if ($tagBrands->isNotEmpty())
                        <div class="shop-product-tags product__sidebar-single">
                            <h3 class="product__sidebar-title">Brands</h3>
                            <div class="shop-product__tags-list">
                                @foreach ($tagBrands as $brand)
                                    <a href="{{ $catalogLink(['q' => $brand, 'category' => request('category')]) }}">{{ $brand }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="shop-product-tags product__sidebar-single style">
                        <h3 class="product__sidebar-title">Reviews</h3>
                        <div class="sidebar-rating-box sidebar-rating-box--style2">
                            <p class="product-catalog-sidebar-note mb-0">
                                <i class="fas fa-comments me-2" style="color: var(--base);" aria-hidden="true"></i>
                                Patient stories and ratings will appear here when reviews go live—thank you for your patience.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
<!--Product End-->

<x-mobileMenu />
<x-searchPopup />
<x-scroll-to-top />
@endsection
