@extends('frontend.layouts.master')
@section('title', 'About Us | BGS Beauty and Wellness Hub')
@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/page-header.css') }}" />
    <style>
        .bgs-about-page {
            position: relative;
            padding: clamp(3rem, 7vw, 5.5rem) 0 clamp(4rem, 9vw, 6.5rem);
            background:
                radial-gradient(ellipse 80% 50% at 50% -20%, rgba(199, 129, 157, 0.12), transparent 55%),
                linear-gradient(180deg, var(--surface) 0%, #fff 28%, #fff 72%, var(--surface) 100%);
        }
        .bgs-about-page::before {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(199, 129, 157, 0.25), transparent);
            pointer-events: none;
        }
        .bgs-about-shell {
            max-width: min(1480px, 100%);
            margin: 0 auto;
        }
        .bgs-about-inner {
            font-family: var(--font);
            position: relative;
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 24px;
            padding: clamp(2rem, 5vw, 3.25rem) clamp(1.5rem, 4vw, 3rem);
            border: 1px solid rgba(228, 207, 228, 0.85);
            box-shadow:
                0 1px 2px rgba(47, 35, 44, 0.04),
                0 24px 48px -24px rgba(47, 35, 44, 0.12);
        }
        .bgs-about-grid {
            display: grid;
            gap: 2.25rem;
            align-items: start;
        }
        @media (min-width: 992px) {
            .bgs-about-grid {
                grid-template-columns: minmax(0, 1.15fr) minmax(0, 0.85fr);
                gap: clamp(2rem, 4vw, 4rem);
                column-gap: clamp(2.25rem, 4.5vw, 4.5rem);
            }
            .bgs-about-col-aside {
                position: sticky;
                top: 6.5rem;
            }
        }
        .bgs-about-doc-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1.75rem;
            border-bottom: 1px solid rgba(199, 129, 157, 0.18);
        }
        @media (min-width: 992px) {
            .bgs-about-doc-header {
                text-align: left;
            }
        }
        .bgs-about-eyebrow {
            display: inline-block;
            font-size: 0.6875rem;
            font-weight: 600;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        .bgs-about-doc-header .bgs-about-lead {
            font-size: clamp(1.35rem, 3.2vw, 1.65rem);
            font-weight: 500;
            line-height: 1.55;
            color: var(--text-primary);
            margin: 0;
            letter-spacing: -0.01em;
        }
        .bgs-about-prose {
            font-size: 1rem;
            line-height: 1.85;
            color: var(--text-secondary);
        }
        .bgs-about-prose p {
            margin-bottom: 1.35rem;
        }
        .bgs-about-prose p:last-child {
            margin-bottom: 0;
        }
        .bgs-about-pullquote {
            margin: 2rem 0 2.25rem;
            padding: 1.35rem 1.5rem 1.35rem 1.35rem;
            border-left: 3px solid var(--primary);
            background: linear-gradient(90deg, rgba(199, 129, 157, 0.08), transparent);
            border-radius: 0 12px 12px 0;
        }
        .bgs-about-pullquote p {
            font-size: clamp(1.2rem, 2.8vw, 1.45rem);
            font-style: italic;
            font-weight: 500;
            line-height: 1.5;
            color: var(--text-primary);
            margin: 0;
        }
        .bgs-about-inner h2 {
            font-size: clamp(1.4rem, 2.6vw, 1.65rem);
            font-weight: 600;
            color: var(--text-primary);
            letter-spacing: -0.02em;
            margin: 0 0 1rem;
            padding-bottom: 0.5rem;
            position: relative;
            display: inline-block;
        }
        .bgs-about-inner h2::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 2.5rem;
            height: 2px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            border-radius: 2px;
        }
        .bgs-about-col-aside > h2 {
            margin-top: 2.25rem;
        }
        @media (min-width: 992px) {
            .bgs-about-col-aside > h2 {
                margin-top: 0;
            }
        }
        .bgs-about-pillars {
            list-style: none;
            margin: 0 0 1.5rem;
            padding: 0;
            display: grid;
            gap: 0.65rem;
            grid-template-columns: 1fr;
        }
        @media (min-width: 576px) and (max-width: 991.98px) {
            .bgs-about-pillars {
                grid-template-columns: 1fr 1fr;
                gap: 0.75rem 1.25rem;
            }
        }
        .bgs-about-pillars li {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            font-size: 0.9375rem;
            line-height: 1.5;
            color: var(--text-secondary);
            padding: 0.65rem 0.85rem;
            background: var(--surface);
            border-radius: 10px;
            border: 1px solid rgba(228, 207, 228, 0.6);
            transition: border-color 0.25s ease, box-shadow 0.25s ease;
        }
        .bgs-about-pillars li:hover {
            border-color: rgba(199, 129, 157, 0.35);
            box-shadow: 0 4px 14px -6px rgba(47, 35, 44, 0.1);
        }
        .bgs-about-pillars li::before {
            content: '';
            flex-shrink: 0;
            width: 6px;
            height: 6px;
            margin-top: 0.45rem;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            box-shadow: 0 0 0 3px rgba(199, 129, 157, 0.15);
        }
        .bgs-about-vm {
            display: grid;
            gap: 1.1rem;
            margin-top: 0.25rem;
            grid-template-columns: 1fr;
        }
        .bgs-about-vm-card {
            padding: 1.35rem 1.4rem;
            border-radius: 14px;
            background: linear-gradient(165deg, #fff 0%, var(--surface) 100%);
            border: 1px solid rgba(228, 207, 228, 0.75);
        }
        .bgs-about-vm-card h2 {
            margin: 0 0 0.85rem;
            font-size: clamp(1.25rem, 2.2vw, 1.45rem);
        }
        .bgs-about-vm-card h2::after {
            width: 2rem;
        }
        .bgs-about-vm-card p {
            margin: 0;
            font-size: 0.9375rem;
            line-height: 1.75;
            color: var(--text-secondary);
        }
        .bgs-about-tagline {
            margin-top: 2.5rem;
            padding: 2rem 1.25rem 0;
            text-align: center;
            border-top: 1px solid rgba(228, 207, 228, 0.9);
        }
        .bgs-about-tagline .bgs-about-tagline-main {
            font-size: clamp(1.35rem, 3vw, 1.65rem);
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--text-primary);
            margin: 0 0 0.5rem;
        }
        .bgs-about-tagline .bgs-about-closing {
            font-size: 0.9rem;
            font-weight: 400;
            color: var(--text-secondary);
            margin: 0;
            letter-spacing: 0.04em;
        }
    </style>
@endpush
@section('content')
    <x-strickyHeader />

    <section class="page-header">
        <div class="page-header__bg" style="background-image: url({{ $aboutPageHeaderBgUrl ?? \App\Support\PageHeaderConfig::aboutBackgroundUrl() }});"></div>
        <div class="container">
            <div class="page-header__inner">
                <h2>About Us</h2>
                <ul class="thm-breadcrumb list-unstyled">
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li><span>-</span></li>
                    <li>About Us</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="bgs-about-page">
        <div class="container-fluid px-3 px-md-4 px-xl-5">
            <div class="bgs-about-shell">
                <article class="bgs-about-inner">
                    <div class="bgs-about-grid">
                        <div class="bgs-about-col bgs-about-col-main">
                            <header class="bgs-about-doc-header">
                                <span class="bgs-about-eyebrow">BGS Beauty and Wellness Hub</span>
                                <p class="bgs-about-lead">
                                    Welcome to BGS Beauty and Wellness Hub — a premium longevity-focused wellness clinic redefining the future of medically guided wellness in Metro Davao.
                                </p>
                            </header>

                            <div class="bgs-about-prose">
                                <p>
                                    Founded with the vision of merging science, sophistication, and personalized care, BioGlow Solutions was created to provide elevated wellness experiences centered on longevity, recovery, and overall well-being. Our clinic brings together advanced wellness technologies, physician-guided wellness programs, regenerative therapies, premium IV infusions, body recovery systems, and professional wellness treatments — all within a refined clinical environment designed for comfort, privacy, and discretion.
                                </p>

                                <blockquote class="bgs-about-pullquote">
                                    <p>At BGS, we believe that health is the ultimate luxury.</p>
                                </blockquote>

                                <p>
                                    Our holistic approach focuses not only on aesthetics, but on helping patients feel revitalized from within through carefully curated wellness protocols tailored to modern lifestyles. From vitality support and skin rejuvenation to recovery therapies, body wellness, and medically supervised transformation programs, every experience at BGS is designed with precision, safety, and sophistication in mind.
                                </p>

                                <p>
                                    Located in the heart of Metro Davao, BGS Beauty and Wellness Hub stands as one of the pioneering premium longevity-focused wellness clinics in Visayas and Mindanao — offering a new standard of wellness access for individuals seeking refined, science-backed care.
                                </p>
                            </div>
                        </div>

                        <aside class="bgs-about-col bgs-about-col-aside" aria-label="Pillars, vision, and mission">
                            <h2>Our Core Pillars</h2>
                            <ul class="bgs-about-pillars">
                                <li>Longevity &amp; Preventive Wellness</li>
                                <li>Physician-Guided Wellness Programs</li>
                                <li>Advanced IV Infusion Therapy</li>
                                <li>Regenerative &amp; Recovery Protocols</li>
                                <li>Luxury Aesthetic &amp; Skin Wellness</li>
                                <li>Precision Body &amp; Wellness Support</li>
                                <li>Elevated Client Experience</li>
                            </ul>

                            <div class="bgs-about-vm">
                                <div class="bgs-about-vm-card">
                                    <h2>Our Vision</h2>
                                    <p>
                                        To become a leading destination for modern longevity and wellness in Metro Davao — where science meets sophistication.
                                    </p>
                                </div>
                                <div class="bgs-about-vm-card">
                                    <h2>Our Mission</h2>
                                    <p>
                                        To provide accessible, medically guided, and elevated wellness experiences that empower individuals to invest in their long-term health, confidence, recovery, and quality of life.
                                    </p>
                                </div>
                            </div>
                        </aside>
                    </div>

                    <footer class="bgs-about-tagline">
                        <p class="bgs-about-tagline-main">Health · Longevity · Wellness</p>
                        <p class="bgs-about-closing">Redefined by BGS Beauty and Wellness Hub.</p>
                    </footer>
                </article>
            </div>
        </div>
    </section>

    <x-mobileMenu />
    <x-searchPopup />
    <x-scroll-to-top />
@endsection
