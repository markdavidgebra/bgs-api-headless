@extends('frontend.layouts.master')
@section('title', 'FAQ || Careon || Careon Laravel Template')
@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/page-header.css') }}" />
@endpush
@section('content')

<x-strickyHeader />

<!--Page Header Start-->
<section class="page-header">
    <div class="page-header__bg" style="background-image: url({{ $faqPageHeaderBgUrl ?? \App\Support\PageHeaderConfig::faqBackgroundUrl() }});"></div>
    <div class="container">
        <div class="page-header__inner">
            <!-- <h2>{{ $title ?? 'FAQ' }}</h2> -->
            <ul class="thm-breadcrumb list-unstyled">
                <!-- <li><a href="{{ url('/') }}">Home</a></li>
                <li><span>-</span></li>
                <li>FAQ</li> -->
            </ul>
        </div>
    </div>
</section>
<!--Page Header End-->


<!--Faq Page Start-->
<section class="faq-page">
    <div class="container">
        <div class="row">
            @php
                $leftFaqs = $faqs->values()->filter(fn ($_, $index) => $index % 2 === 0)->values();
                $rightFaqs = $faqs->values()->filter(fn ($_, $index) => $index % 2 === 1)->values();
            @endphp

            <div class="col-xl-6 col-lg-6">
                <div class="faq-one__left">
                    <div class="accrodion-grp faq-one-accrodion" data-grp-name="faq-one-accrodion-left">
                        @forelse ($leftFaqs as $faq)
                            <div class="accrodion {{ $loop->first ? 'active' : '' }}">
                                <div class="accrodion-title">
                                    <div class="faq-one-accrodion__count"></div>
                                    <h4>{{ $faq->question }}</h4>
                                </div>
                                <div class="accrodion-content">
                                    <div class="inner">
                                        <p>{{ $faq->answer }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="accrodion active">
                                <div class="accrodion-title">
                                    <div class="faq-one-accrodion__count"></div>
                                    <h4>No FAQs available yet.</h4>
                                </div>
                                <div class="accrodion-content">
                                    <div class="inner">
                                        <p>Please add FAQs from Admin &gt; Pages &gt; FAQs.</p>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-6">
                <div class="faq-one__left">
                    <div class="accrodion-grp faq-one-accrodion" data-grp-name="faq-one-accrodion-right">
                        @forelse ($rightFaqs as $faq)
                            <div class="accrodion {{ $loop->first ? 'active' : '' }}">
                                <div class="accrodion-title">
                                    <div class="faq-one-accrodion__count"></div>
                                    <h4>{{ $faq->question }}</h4>
                                </div>
                                <div class="accrodion-content">
                                    <div class="inner">
                                        <p>{{ $faq->answer }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            @if ($leftFaqs->count() > 0)
                                <div class="accrodion active">
                                    <div class="accrodion-title">
                                        <div class="faq-one-accrodion__count"></div>
                                        <h4>More FAQs coming soon.</h4>
                                    </div>
                                    <div class="accrodion-content">
                                        <div class="inner">
                                            <p>Additional questions will appear here as you add more FAQ entries.</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--Faq Page End-->


<x-mobileMenu />
<x-searchPopup />
<x-scroll-to-top />
@endsection