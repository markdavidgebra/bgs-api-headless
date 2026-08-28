@php
    $pd ??= fn (string $path) => asset('patients/' . ltrim($path, '/'));
@endphp
<header class="header-area header-style-1 header-style-5 header-height-2 bgs-portal-scroll-header">
        
        
        
        <div class="header-bottom header-bottom-bg-color sticky-bar">
            <div class="container">
                <div class="header-wrap header-space-between position-relative">
                    <div class="logo logo-width-1 d-block d-lg-none">
                            <a href="index.html"><img src="{{ $pd('imgs/theme/bgs.png') }}" alt="logo" /></a>
                    </div>
                    <div class="header-nav d-none d-lg-flex">
                        
                        <div class="main-menu main-menu-padding-1 main-menu-lh-2 d-none d-lg-block font-heading">
                            <nav>
                                <ul>
                                    <li>
                                        <a href="{{ route('home') }}">Home</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('about') }}">About</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('doctor') }}">Clinical staff</a>
                                        
                                    </li>
                                    <li>
                                        <a href="{{ route('testimonials') }}">Testimonials</a>
                                        
                                    </li>
                                    <li>
                                        <a href="{{ route('pricing') }}">Pricing</a>
                                        
                                    </li>
                                    <li>
                                        <a href="{{ url('our-services') }}">Services</a>
                                    </li>
                                    <li>
                                        <a href="{{ url('blog') }}">Blog</a>
                                    </li>
                                    <li>
                                        <a href="{{ url('faq') }}">FAQ</a>
                                    </li>
                                    <li>
                                        <a href="{{ url('contact') }}">Contact</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                    
                    <div class="header-action-icon-2 d-block d-lg-none">
                        <div class="burger-icon burger-icon-white">
                            <span class="burger-icon-top"></span>
                            <span class="burger-icon-mid"></span>
                            <span class="burger-icon-bottom"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>