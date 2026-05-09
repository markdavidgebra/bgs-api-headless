<section class="team-two">
    <style>
        .team-two .team-two__img {
            width: min(300px, 100%);
            aspect-ratio: 1 / 1;
            margin: 0 auto;
            border-radius: 50%;
            overflow: hidden;
            background: #ececec;
        }

        .team-two .team-two__img .team-two__doctor-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
        }

        /* Show all social links cleanly inside the popup */
        .team-two .team-two__social {
            top: -170px;
            left: -18px;
            right: -18px;
            flex-direction: row;
            flex-wrap: wrap;
            justify-content: center;
            gap: 9px;
            padding: 12px 12px;
            max-height: 150px;
            overflow-y: auto;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(199, 129, 157, 0.18);
            box-shadow: 0 8px 24px rgba(43, 25, 35, 0.14);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
        }

        .team-two .team-two__social a {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f9f1f5;
            border: 1px solid rgba(199, 129, 157, 0.2);
            color: #9b5f7d;
            font-size: 14px;
            line-height: 1;
            transition: transform 0.16s ease, box-shadow 0.16s ease, background-color 0.16s ease, color 0.16s ease;
        }

        .team-two .team-two__social a:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(199, 129, 157, 0.28);
            background: #c7819d;
            color: #fff;
        }

        .team-two .team-two__social::-webkit-scrollbar {
            width: 6px;
        }

        .team-two .team-two__social::-webkit-scrollbar-thumb {
            background: rgba(199, 129, 157, 0.45);
            border-radius: 999px;
        }
    </style>
    <div class="container">
        <div class="section-title-two text-center sec-title-animation animation-style1">
            <h6 class="section-title-two__tagline">Our Team Member
            </h6>
            <h3 class="section-title-two__title title-animation">Trust in Health Caring Every<br> Step Heal with
                Heart
            </h3>
        </div>
        <div class="row">
            @php
                $fallbackImages = [
                    asset('frontend/assets/images/team/team-2-1.jpg'),
                    asset('frontend/assets/images/team/team-2-2.jpg'),
                    asset('frontend/assets/images/team/team-2-3.jpg'),
                ];
            @endphp

            @forelse (($homeDoctors ?? collect()) as $index => $doctor)
                @php
                    $delay = ($index + 1) * 100;
                    $animationClass = match ($index % 3) {
                        0 => 'fadeInLeft',
                        1 => 'fadeInUp',
                        default => 'fadeInRight',
                    };
                    $doctorImage = $doctor->image_url ?: $fallbackImages[$index % count($fallbackImages)];
                    $doctorSocialLinks = is_array($doctor->social_links) ? $doctor->social_links : [];
                @endphp
                <div class="col-xl-4 col-lg-4 wow {{ $animationClass }}" data-wow-delay="{{ $delay }}ms">
                    <div class="team-two__single">
                        <div class="team-two__img-box">
                            <div class="team-two__img">
                                <img
                                    class="team-two__doctor-photo"
                                    src="{{ $doctorImage }}"
                                    alt="{{ $doctor->name }}"
                                    loading="lazy"
                                    onerror="this.onerror=null;this.src='{{ $fallbackImages[$index % count($fallbackImages)] }}';">
                            </div>
                            <div class="team-two__plus-and-social">
                                <div class="team-two__plus">
                                    <span class="icon-plus"></span>
                                </div>
                                <div class="team-two__social">
                                    @php
                                        $socialIconMap = [
                                            'facebook_url' => 'fab fa-facebook-f',
                                            'x_url' => 'fab fa-twitter',
                                            'linkedin_url' => 'fab fa-linkedin-in',
                                            'instagram_url' => 'fab fa-instagram',
                                            'pinterest_url' => 'fab fa-pinterest-p',
                                            'youtube_url' => 'fab fa-youtube',
                                            'tiktok_url' => 'fas fa-music',
                                            'threads_url' => 'fas fa-at',
                                            'telegram_url' => 'fab fa-telegram-plane',
                                            'whatsapp_url' => 'fab fa-whatsapp',
                                            'snapchat_url' => 'fab fa-snapchat-ghost',
                                            'reddit_url' => 'fab fa-reddit-alien',
                                            'tumblr_url' => 'fab fa-tumblr',
                                            'discord_url' => 'fab fa-discord',
                                            'twitch_url' => 'fab fa-twitch',
                                            'github_url' => 'fab fa-github',
                                            'behance_url' => 'fab fa-behance',
                                            'dribbble_url' => 'fab fa-dribbble',
                                            'medium_url' => 'fab fa-medium-m',
                                            'vimeo_url' => 'fab fa-vimeo-v',
                                            'website_url' => 'fas fa-globe',
                                        ];
                                    @endphp
                                    @foreach ($doctorSocialLinks as $platformKey => $platformUrl)
                                        @continue(blank($platformUrl))
                                        <a href="{{ $platformUrl }}" target="_blank" rel="noopener noreferrer" title="{{ $platformKey }}">
                                            <i class="{{ $socialIconMap[$platformKey] ?? 'fas fa-link' }}"></i>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="team-two__content">
                            <h3 class="team-two__title"><a href="{{ route('doctor') }}">{{ $doctor->name }}</a></h3>
                            <p class="team-two__sub-title">
                                {{ $doctor->specialty && trim($doctor->specialty) !== '' ? $doctor->specialty : 'General Practitioner' }}
                            </p>
                       
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center">
                    <p class="text-muted mb-0">Our doctors will be listed here soon.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
