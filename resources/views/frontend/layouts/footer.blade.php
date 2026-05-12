      
        @php($sf = $siteFooter ?? \App\Support\SiteFooterConfig::get())
        <footer class="site-footer-two">
            <div class="site-footer-two__bg-shape"
                style="background-image: url({{ asset('frontend/assets/images/shapes/site-footer-two-bg-shape.png') }});"></div>
            <div class="site-footer-two__newsletter">
                <div class="container">
                    <div class="site-footer-two__newsletter-inner">
                        <div class="site-footer-two__newsletter-inner-title-box">
                            
                            <h2 class="site-footer-two__newsletter-title">{!! nl2br(e($sf['newsletter_title'] ?? '')) !!}</h2>
                        </div>
                        <div class="site-footer-two__social">
                            @foreach ($sf['social_links'] ?? [] as $social)
                                @if (trim((string) ($social['icon'] ?? '')) !== '' || trim((string) ($social['url'] ?? '')) !== '')
                                    @php($sUrl = \App\Support\SiteFooterConfig::publicUrl($social['url'] ?? ''))
                                    <a href="{{ $sUrl }}" @if($sUrl !== '#' && \Illuminate\Support\Str::startsWith($sUrl, ['http://', 'https://'])) target="_blank" rel="noopener noreferrer" @endif><span class="{{ \App\Support\SiteFooterConfig::iconClass($social['icon'] ?? '') }}"></span></a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="site-footer-two__top">
                <div class="container">
                    <div class="site-footer-two__top-inner">
                        <div class="row">
                            <div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay="100ms">
                                <div class="footer-widget-two__newsletter-box">
                                    <form class="footer-widget-two__newsletter" data-url="MC_FORM_URL" novalidate="novalidate">
                                        <input type="email" placeholder="{{ e($sf['newsletter_email_placeholder'] ?? 'Enter your email') }}" name="email">
                                        <button type="submit" class="footer-widget-two__newsletter-btn"><span
                                                class="icon-up-arrow"></span></button>
                                    </form>
                                    <p class="footer-widget-two__newsletter-text">{{ $sf['newsletter_blurb'] ?? '' }}</p>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay="200ms">
                                <div class="footer-widget-two__services">
                                    <h4 class="footer-widget-two__title">{{ $sf['department_title'] ?? 'Department' }}</h4>
                                    <ul class="footer-widget-two__services-link-list list-unstyled">
                                        @foreach ($sf['department_links'] ?? [] as $link)
                                            @if (trim((string) ($link['label'] ?? '')) !== '')
                                                <li>
                                                    <a href="{{ \App\Support\SiteFooterConfig::publicUrl($link['url'] ?? '#') }}">{{ $link['label'] }}</a>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay="300ms">
                                <div class="footer-widget-two__contact-info">
                                    <h4 class="footer-widget-two__title">{{ $sf['contact_title'] ?? 'Contact' }}</h4>
                                    <ul class="footer-widget-two__contact-list list-unstyled">
                                        <li>
                                            <div class="footer-widget-two__contact-icon">
                                                <span class="icon-pin"></span>
                                            </div>
                                            <div class="footer-widget-two__contact-content">
                                                <span>{{ $sf['contact_address_label'] ?? 'Address' }}</span>
                                                <p class="footer-widget-two__contact-text">{{ $sf['contact_address'] ?? '' }}</p>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="footer-widget-two__contact-icon">
                                                <span class="icon-call"></span>
                                            </div>
                                            <div class="footer-widget-two__contact-content">
                                                <span>{{ $sf['contact_phone_label'] ?? 'Phone Number' }}</span>
                                                <p class="footer-widget-two__contact-text"><a
                                                        href="{{ \App\Support\SiteFooterConfig::telHref($sf['contact_phone'] ?? '') }}">{{ $sf['contact_phone'] ?? '' }}</a></p>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="footer-widget-two__contact-icon">
                                                <span class="icon-envolope"></span>
                                            </div>
                                            <div class="footer-widget-two__contact-content">
                                                <span>{{ $sf['contact_email_label'] ?? 'Email' }}</span>
                                                <p class="footer-widget-two__contact-text"><a
                                                        href="{{ \App\Support\SiteFooterConfig::mailtoHref($sf['contact_email'] ?? '') }}">{{ $sf['contact_email'] ?? '' }}</a></p>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- <div class="col-xl-2 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay="400ms">
                                <div class="footer-widget-two__page-link">
                                    <h4 class="footer-widget-two__title">{{ $sf['page_links_title'] ?? 'Page' }}</h4>
                                    <ul class="footer-widget-two__services-link-list list-unstyled">
                                        @foreach ($sf['page_links'] ?? [] as $link)
                                            @if (trim((string) ($link['label'] ?? '')) !== '')
                                                <li>
                                                    <a href="{{ \App\Support\SiteFooterConfig::publicUrl($link['url'] ?? '#') }}">{{ $link['label'] }}</a>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="site-footer-two__bottom">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="site-footer-two__bottom-inner">
                                <div class="site-footer-two__copyright">
                                    <p class="site-footer-two__copyright-text">©<a href="{{ \App\Support\SiteFooterConfig::publicUrl($sf['copyright_brand_url'] ?? '#') }}">{{ $sf['copyright_brand'] ?? '' }}</a>. {{ $sf['copyright_year'] ?? '' }} |
                                        {{ $sf['copyright_suffix'] ?? '' }}</p>
                                </div>
                                <div class="site-footer-two__bottom-menu-box">
                                    <ul class="list-unstyled site-footer-two__bottom-menu">
                                        @foreach ($sf['bottom_links'] ?? [] as $link)
                                            @if (trim((string) ($link['label'] ?? '')) !== '')
                                                <li><a href="{{ \App\Support\SiteFooterConfig::publicUrl($link['url'] ?? '#') }}">{{ $link['label'] }}</a></li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        
</div>

