@extends('frontend.layouts.master')
@section('title', 'About')
@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/counter.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/page-header.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/why-choose.css') }}" />
@endpush
@section('content')
    @php
        $meta = $about?->meta ?? [];
        $clinicHours = collect(data_get($meta, 'clinic_hours', []))
            ->map(function ($row) {
                $day = trim((string) data_get($row, 'day', ''));
                $time = trim((string) data_get($row, 'time', ''));
                if ($day === '' && $time === '') {
                    return null;
                }

                return ['day' => $day, 'time' => $time];
            })
            ->filter()
            ->values();
        if ($clinicHours->isEmpty()) {
            $clinicHours = collect([
                ['day' => 'Monday - Friday', 'time' => '8:00 AM - 7:00 PM'],
                ['day' => 'Saturday', 'time' => '9:00 AM - 5:00 PM'],
                ['day' => 'Sunday', 'time' => 'By Appointment'],
                ['day' => 'Emergency', 'time' => '24/7 Support Line'],
            ]);
        }
        $storyPoints = collect(data_get($meta, 'story_points', []))
            ->map(fn ($point) => trim((string) $point))
            ->filter(fn ($point) => $point !== '')
            ->values();
        if ($storyPoints->isEmpty()) {
            $storyPoints = collect([
                data_get($meta, 'story_point_1'),
                data_get($meta, 'story_point_2'),
                data_get($meta, 'story_point_3'),
            ])->map(fn ($point) => trim((string) $point))->filter(fn ($point) => $point !== '')->values();
        }
        if ($storyPoints->isEmpty()) {
            $storyPoints = collect([
                'Patient-first care in every visit',
                'Clear communication and transparent treatment',
                'Experienced team and modern facilities',
            ]);
        }
        $resolveUrl = function (?string $url, string $fallbackRoute) {
            $url = trim((string) $url);
            if ($url === '') {
                return route($fallbackRoute);
            }
            if (\Illuminate\Support\Str::startsWith($url, ['http://', 'https://', '/', '#'])) {
                return $url;
            }
            if (str_contains($url, ':')) {
                return $url;
            }
            return url($url);
        };
    @endphp
    <x-strickyHeader />

    <!--Page Header Start-->
    <section class="page-header">
        <div class="page-header__bg" style="background-image: url({{ $aboutPageHeaderBgUrl ?? \App\Support\PageHeaderConfig::aboutBackgroundUrl() }});"></div>
        <div class="container">
            <div class="page-header__inner">
                <!-- <h2>{{ $about?->title ?: 'About Us' }}</h2> -->
                <ul class="thm-breadcrumb list-unstyled">
                    <!-- <li><a href="{{ url('/') }}">Home</a></li>
                    <li><span>-</span></li>
                    <li>About</li> -->
                </ul>
            </div>
        </div>
    </section>
    <!--Page Header End-->

    <!-- About Story Start -->
    <section class="about-one about-six">
        <div class="container">
            <div class="about-one__inner">
                <div class="about-one__img-box">
                    <div class="about-one__content-box wow slideInLeft" data-wow-delay="100ms" data-wow-duration="2500ms">
                        <div class="section-title text-left sec-title-animation animation-style2">
                            <h6 class="section-title__tagline">
                                <span class="icon-broken-bone"></span>{{ $about?->subtitle ?: 'Our Story' }}
                            </h6>
                            <h3 class="section-title__title title-animation">
                                {{ $about?->title ?: 'Care that feels personal, modern, and reliable.' }}
                            </h3>
                        </div>
                        <p class="about-one__text">
                            {{ $about?->content ?: 'We build trusted healthcare experiences focused on prevention, timely care, and long-term wellness for every patient.' }}
                        </p>
                        <ul class="about-one__points-box list-unstyled">
                            @foreach ($storyPoints as $point)
                                <li>
                                    <div class="icon"><span class="icon-plus"></span></div>
                                    <p>{{ $point }}</p>
                                </li>
                            @endforeach
                        </ul>
                        <div class="about-two__btn-box">
                            <a href="{{ route('appointment') }}" class="thm-btn">Book an Appointment <span class="icon-plus"></span></a>
                        </div>
                    </div>
                    <div class="about-one__img">
                        <img src="{{ $about?->image_url ?: asset('frontend/assets/images/resources/about-one-img-1.jpg') }}" alt="{{ $about?->title ?: 'About image' }}">
                    </div>
                    <div class="about-one__working-hour wow slideInRight" data-wow-delay="100ms" data-wow-duration="2500ms">
                        <h3 class="about-one__working-hour-title">{{ data_get($meta, 'clinic_hours_title') ?: 'Clinic Hours' }}</h3>
                        <ul class="about-one__working-hour-list list-unstyled">
                            @foreach ($clinicHours as $row)
                                <li>
                                    <span>{{ data_get($row, 'day', '') }}</span>
                                    <p>{{ data_get($row, 'time', '') !== '' ? data_get($row, 'time', '') : 'Closed' }}</p>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    

<x-mobileMenu />
<x-searchPopup />
<x-scroll-to-top />
@endsection