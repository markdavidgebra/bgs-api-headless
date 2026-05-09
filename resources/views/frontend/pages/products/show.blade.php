@extends('frontend.layouts.master')
@section('title', $product->name)
@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/shop.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/page-header.css') }}" />
@endpush

@php
    $shopFallbackImg = asset('frontend/assets/images/shop/shop-product-1-1.jpg');
    $img = $product->image_url ?: $shopFallbackImg;
    $fallbackJs = "this.onerror=null;this.src='".$shopFallbackImg."';";
@endphp

@section('content')

<x-strickyHeader />

<section class="page-header">
    <div class="page-header__bg" style="background-image: url({{ $productShowPageHeaderBgUrl ?? \App\Support\PageHeaderConfig::productShowBackgroundUrl() }});"></div>
    <div class="container">
        <div class="page-header__inner">
            <h2>{{ $product->name }}</h2>
            <ul class="thm-breadcrumb list-unstyled">
                <li><a href="{{ url('/') }}">Home</a></li>
                <li><span>-</span></li>
                <li><a href="{{ route('our-products') }}">Products</a></li>
                <li><span>-</span></li>
                <li>{{ $product->name }}</li>
            </ul>
        </div>
    </div>
</section>

<section class="product-details product-showcase">
    <div class="container">
        <div class="row align-items-start">
            <div class="col-lg-6 col-xl-6 mb-4 mb-lg-0">
                <div class="product-details__left">
                    <div class="product-details__img product-showcase__media">
                        <img src="{{ $img }}" alt="{{ $product->name }}" width="600" height="600" class="img-fluid product-show-image" onerror="{{ $fallbackJs }}">
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-xl-6">
                <div class="product-details__right">
                    <div class="product-details__top">
                        <h3 class="product-details__title">{{ $product->name }}</h3>
                    </div>
                    <div class="product-showcase__meta mb-3">
                        @if (filled($product->category))
                            <span class="product-showcase__chip">{{ $product->category }}</span>
                        @endif
                       
                        
                    </div>
                    <div class="product-details__content">
                        @if (filled($product->description))
                            <p class="product-details__content-text1" style="white-space: pre-line;">{{ $product->description }}</p>
                        @endif
                    </div>
                    @php
                        $assuranceLines = $product->resolvedShowcaseAssuranceLines();
                    @endphp
                    @if ($assuranceLines !== [])
                        <ul class="product-showcase__assurance list-unstyled">
                            @foreach ($assuranceLines as $line)
                                <li><i class="fas fa-check-circle" aria-hidden="true"></i> {{ $line }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <a href="{{ route('our-products') }}" class="thm-btn">Back Products <span class="icon-arrow-right"></span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<x-mobileMenu />
<x-searchPopup />
<x-scroll-to-top />
@endsection
