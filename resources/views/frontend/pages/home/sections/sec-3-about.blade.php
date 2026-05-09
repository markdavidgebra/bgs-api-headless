<section class="about-two">
    <style>
        .about-two .about-two__right {
            padding: 24px;
        }

        .about-two .about-two__points-list li .icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            line-height: 1;
        }

        .about-two .about-two__left {
            height: 100%;
        }

        .about-two .about-two__img {
            height: 100%;
            min-height: 460px;
        }

        .about-two .about-two__img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .about-two .about-two__right .section-title-two {
            margin-bottom: 16px;
        }

        .about-two .about-two__points-list {
            margin-bottom: 16px;
        }

        .about-two .about-two__points-list li {
            background: #f8f7fb;
            border-radius: 14px;
            padding: 10px 12px;
        }

        .about-two .about-two__points-list li + li {
            margin-top: 12px;
        }

        .about-two .about-two__img-2 {
            margin-bottom: 14px;
        }

        .about-two .about-two__img-2 img {
            width: 100%;
            border-radius: 14px;
            display: block;
        }

        .about-two .about-two__points-box {
            margin-bottom: 14px;
        }

        .about-two .about-two__points-2 li + li {
            margin-top: 10px;
        }

        .about-two .about-two__points-2 li {
            padding: 6px 0;
        }

        .about-two .about-two__text-1 {
            margin-bottom: 14px;
            line-height: 1.7;
        }

        .about-two .about-two__btn-box {
            margin-top: 0;
        }

        @media (max-width: 1199.98px) {
            .about-two .about-two__right {
                margin-top: 18px;
            }

            .about-two .about-two__img {
                min-height: 360px;
            }
        }
    </style>
    @php
        $aboutMeta = $homeAbout?->meta ?? [];
        $aboutFeatures = collect(data_get($aboutMeta, 'features', []))
            ->map(function ($row) {
                $icon = trim((string) data_get($row, 'icon', ''));
                $title = trim((string) data_get($row, 'title', ''));
                $text = trim((string) data_get($row, 'text', ''));
                if ($icon === '' && $title === '' && $text === '') {
                    return null;
                }

                return ['icon' => $icon, 'title' => $title, 'text' => $text];
            })
            ->filter()
            ->values();

        if ($aboutFeatures->isEmpty()) {
            $aboutFeatures = collect([
                [
                    'title' => trim((string) data_get($aboutMeta, 'feature_1_title', '')),
                    'text' => trim((string) data_get($aboutMeta, 'feature_1_text', '')),
                ],
                [
                    'title' => trim((string) data_get($aboutMeta, 'feature_2_title', '')),
                    'text' => trim((string) data_get($aboutMeta, 'feature_2_text', '')),
                ],
            ])->filter(fn ($row) => ($row['title'] ?? '') !== '' || ($row['text'] ?? '') !== '')->values();
        }

        $aboutListPoints = collect(data_get($aboutMeta, 'list_points', []))
            ->map(fn ($point) => trim((string) $point))
            ->filter(fn ($point) => $point !== '')
            ->values();

        if ($aboutListPoints->isEmpty()) {
            $aboutListPoints = collect([
                data_get($aboutMeta, 'list_point_1'),
                data_get($aboutMeta, 'list_point_2'),
                data_get($aboutMeta, 'list_point_3'),
                data_get($aboutMeta, 'list_point_4'),
            ])->map(fn ($point) => trim((string) $point))->filter(fn ($point) => $point !== '')->values();
        }

        if ($aboutListPoints->isEmpty()) {
            $aboutListPoints = collect([
                'Where Health Matters Most',
                'Caring for You, Always',
                'Enhancing Lives Through Care',
                'Quality Care, Exceptional Service',
            ]);
        }
        $aboutListPointsLeft = $aboutListPoints
            ->filter(fn ($_, $index) => $index % 2 === 0)
            ->values();
        $aboutListPointsRight = $aboutListPoints
            ->filter(fn ($_, $index) => $index % 2 !== 0)
            ->values();
        $homeBottomText = trim((string) data_get($aboutMeta, 'home_bottom_text', ''));
        if ($homeBottomText === '') {
            $homeBottomText = 'Health care is a vital aspect of maintaining overall well-being, encompassing a range of services from preventive care to treatment';
        }

        if ($aboutFeatures->isEmpty()) {
            $aboutFeatures = collect([
                [
                    'title' => $homeAbout?->title ?: 'About Us',
                    'text' => 'Health care is a vital aspect of maintaining overall well-being, encompassing a range of services from preventive care to treatment',
                ],
            ]);
        }

        $aboutFeatureIcons = ['icon-plaster', 'icon-medicine-2-2', 'icon-broken-bone', 'icon-doctor'];
        $aboutFeatureIconEmoji = [
            'icon-plaster' => '🩹',
            'icon-medicine-2-2' => '💊',
            'icon-broken-bone' => '🦴',
            'icon-doctor' => '🧑‍⚕️',
        ];
    @endphp
    <div class="container">
        <div class="about-two__inner">
            <div class="row">
                <div class="col-xl-6">
                    <div class="about-two__left">
                        <div class="about-two__img">
                            <img src="{{ $homeAbout?->image_url ?: asset("frontend/assets/images/resources/about-two-img-1.jpg") }}" alt="{{ $homeAbout?->title ?: 'About image' }}">
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="about-two__right">
                        <div class="section-title-two text-left sec-title-animation animation-style2">
                            <h6 class="section-title-two__tagline">{{ $homeAbout?->subtitle ?: 'About Us' }}
                            </h6>
                            <h3 class="section-title-two__title title-animation">{{ $homeAbout?->title ?: 'About Us' }}
                            </h3>
                        </div>
                        <ul class="about-two__points-list list-unstyled">
                            @foreach ($aboutFeatures as $index => $feature)
                                @php
                                    $savedIcon = trim((string) data_get($feature, 'icon', ''));
                                    $iconClass = in_array($savedIcon, $aboutFeatureIcons, true)
                                        ? $savedIcon
                                        : $aboutFeatureIcons[$index % count($aboutFeatureIcons)];
                                    $iconEmoji = data_get($aboutFeatureIconEmoji, $iconClass, '🩺');
                                @endphp
                                <li>
                                    <div class="icon">
                                        <span aria-hidden="true">{{ $iconEmoji }}</span>
                                    </div>
                                    <div class="content">
                                        <h3>{{ data_get($feature, 'title') !== '' ? data_get($feature, 'title') : ($homeAbout?->title ?: 'About Us') }}</h3>
                                        <p>{{ \Illuminate\Support\Str::limit(data_get($feature, 'text') !== '' ? data_get($feature, 'text') : ($homeAbout?->content ?: 'Health care is a vital aspect of maintaining overall well-being, encompassing a range of services from preventive care to treatment.'), 130) }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <div class="about-two__img-2">
                            <img src="{{ filled(data_get($aboutMeta, 'secondary_image')) ? asset(data_get($aboutMeta, 'secondary_image')) : asset("frontend/assets/images/resources/about-two-img-2.jpg") }}" alt="Secondary about image">
                        </div>
                        <div class="about-two__points-box">
                            <ul class="about-two__points-2 list-unstyled">
                                @foreach ($aboutListPointsLeft as $point)
                                    <li>
                                        <div class="icon">
                                            <span class="icon-left-arrows"></span>
                                        </div>
                                        <p>{{ $point }}</p>
                                    </li>
                                @endforeach
                            </ul>
                            <ul class="about-two__points-2 list-unstyled">
                                @foreach ($aboutListPointsRight as $point)
                                    <li>
                                        <div class="icon">
                                            <span class="icon-left-arrows"></span>
                                        </div>
                                        <p>{{ $point }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <p class="about-two__text-1">{{ $homeBottomText }}</p>
                        <div class="about-two__btn-box">
                            <a href="{{ route("about") }}" class="thm-btn">Read More <span class="icon-plus"></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>