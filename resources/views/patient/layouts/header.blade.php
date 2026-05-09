@php
    $pd ??= fn (string $path) => asset('patients/' . ltrim($path, '/'));
@endphp
<header class="header-area header-style-1 header-style-5 header-height-2">
        <div class="mobile-promotion">
            <span>Grand opening, <strong>up to 15%</strong> off all items. Only <strong>3 days</strong> left</span>
        </div>
        <div class="header-top header-top-ptb-1 d-none d-lg-block">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-xxl-3 col-xl-4 col-lg-7">
                        <div class="header-info">
                            <ul>
                                <li><a href="#">About Us</a></li>
                                <li><a href="{{ auth('web')->check() ? route('patient.dashboard') : route('login') }}">Patient
                                        portal</a></li>
                                <li><a href="#">Wishlist</a></li>
                                <li><a href="#">Order Tracking</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xxl-6 col-xl-5 col-lg-4 d-none d-xl-block">
                        <div class="text-center">
                            <div id="news-flash" class="d-inline-block">
                                <ul>
                                    <li>100% Secure delivery without contacting the courier</li>
                                    <li>Supper Value Deals - Save more with coupons</li>
                                    <li>Trendy 25silver jewelry, save up 35% off today</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-xl-3 col-lg-5">
                        <div class="header-info header-info-right">
                            <ul>
                                <li>Need help? Call Us: <strong class="text-brand"> +0000-000</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="header-middle header-middle-ptb-1 d-none d-lg-block">
            <div class="container">
                <div class="header-wrap">
                    <div class="logo logo-width-1">
                        <a href="index.html"><img src="{{ $pd('imgs/theme/bgs.png') }}" alt="logo" /></a>
                    </div>
                    <div class="header-right">
                        <div class="search-style-2">
                            <form action="#">
                                <select class="select-active">
                                    <option>All Categories</option>
                                    <option>Laptops & Computers</option>
                                    <option>Smart Home Devices</option>
                                    <option>Wearable Technology</option>
                                    <option>Cameras & Drones</option>
                                    <option>Men's Clothing</option>
                                    <option>Women's Clothing</option>
                                    <option>Shoes & Footwear</option>
                                    <option>Bags & Accessories</option>
                                    <option>Jewelry & Watches</option>
                                </select>
                                <input type="text" placeholder="Search for items..." />
                            </form>
                        </div>
                        <div class="header-action-right">
                            <div class="header-action-2">
                                <div class="header-action-icon-2">
                                    <a href="#">
                                        <img class="svgInject" alt="ShopX"
                                            src="{{ $pd('imgs/theme/icons/icon-compare.svg') }}" />
                                        <span class="pro-count blue">3</span>
                                    </a>
                                    <a href="#"><span class="lable ml-0">Compare</span></a>
                                </div>
                                <div class="header-action-icon-2">
                                    <a href="#">
                                        <img class="svgInject" alt="ShopX"
                                            src="{{ $pd('imgs/theme/icons/icon-heart.svg') }}" />
                                        <span class="pro-count blue">6</span>
                                    </a>
                                    <a href="#"><span class="lable">Wishlist</span></a>
                                </div>
                                <div class="header-action-icon-2">
                                    <a class="mini-cart-icon" href="#">
                                        <img alt="ShopX" src="{{ $pd('imgs/theme/icons/icon-cart.svg') }}" />
                                        <span class="pro-count blue">2</span>
                                    </a>
                                    <a href="#"><span class="lable">Cart</span></a>
                                    <div class="cart-dropdown-wrap cart-dropdown-hm2">
                                        <ul>
                                            <li>
                                                <div class="shopping-cart-img">
                                                    <a href="#"><img alt="ShopX"
                                                            src="{{ $pd('imgs/shop/thumbnail-3.jpg') }}" /></a>
                                                </div>
                                                <div class="shopping-cart-title">
                                                    <h4><a href="#">Daisy Casual Bag</a></h4>
                                                    <h4><span>1 × </span>₱800.00</h4>
                                                </div>
                                                <div class="shopping-cart-delete">
                                                    <a href="#"><i class="fi-rs-cross-small"></i></a>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="shopping-cart-img">
                                                    <a href="#"><img alt="ShopX"
                                                            src="{{ $pd('imgs/shop/thumbnail-2.jpg') }}" /></a>
                                                </div>
                                                <div class="shopping-cart-title">
                                                    <h4><a href="#">Corduroy Shirts</a></h4>
                                                    <h4><span>1 × </span>₱3200.00</h4>
                                                </div>
                                                <div class="shopping-cart-delete">
                                                    <a href="#"><i class="fi-rs-cross-small"></i></a>
                                                </div>
                                            </li>
                                        </ul>
                                        <div class="shopping-cart-footer">
                                            <div class="shopping-cart-total">
                                                <h4>Total <span>₱4000.00</span></h4>
                                            </div>
                                            <div class="shopping-cart-button">
                                                <a href="shop-cart.html" class="outline">View cart</a>
                                                <a href="shop-checkout.html">Checkout</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="header-action-icon-2">
                                    <a href="#">
                                        <img class="svgInject" alt="ShopX"
                                            src="{{ $pd('imgs/theme/icons/icon-user.svg') }}" />
                                    </a>
                                    <a href="#"><span class="lable ml-0">Account</span></a>
                                    <div class="cart-dropdown-wrap cart-dropdown-hm2 account-dropdown">
                                        <ul>
                                            <li>
                                                <a href="{{ route('patient.dashboard') }}"><i class="fi fi-rs-user mr-10"></i>Patient
                                                    dashboard</a>
                                            </li>
                                            <li>
                                                <a href="#"><i class="fi fi-rs-location-alt mr-10"></i>Order
                                                    Tracking</a>
                                            </li>
                                            <li>
                                                <a href="#"><i class="fi fi-rs-label mr-10"></i>My
                                                    Voucher</a>
                                            </li>
                                            <li>
                                                <a href="#"><i class="fi fi-rs-heart mr-10"></i>My
                                                    Wishlist</a>
                                            </li>
                                            <li>
                                                <a href="#"><i class="fi fi-rs-settings-sliders mr-10"></i>Setting</a>
                                            </li>
                                            <li>
                                                <a href="#"><i class="fi fi-rs-sign-out mr-10"></i>Sign
                                                    out</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="header-bottom header-bottom-bg-color sticky-bar">
            <div class="container">
                <div class="header-wrap header-space-between position-relative">
                    <div class="logo logo-width-1 d-block d-lg-none">
                            <a href="index.html"><img src="{{ $pd('imgs/theme/bgs.png') }}" alt="logo" /></a>
                    </div>
                    <div class="header-nav d-none d-lg-flex">
                        <div class="main-categori-wrap d-none d-lg-block">
                            <a class="categories-button-active" href="#">
                                <span class="fi-rs-apps"></span> Categories
                                <i class="fi-rs-angle-down"></i>
                            </a>
                            <div
                                class="categories-dropdown-wrap style-2 font-heading categories-dropdown-active-large font-heading">
                                <div class="d-flex categori-dropdown-inner">
                                    <ul>
                                        <li>
                                            <a href="#">
                                                <img src="{{ $pd('imgs/theme/icons/category-1.svg') }}" alt="" />
                                                Men's Clothing
                                            </a>
                                            <ul>
                                                <li><a href="#">Menu 01</a></li>
                                                <li><a href="#">Menu 02</a></li>
                                                <li><a href="#">Menu 03</a></li>
                                                <li><a href="#">Menu 04</a></li>
                                                <li><a href="#">Menu 05</a></li>
                                            </ul>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <img src="{{ $pd('imgs/theme/icons/category-2.svg') }}" alt="" />
                                                Women's Clothing
                                            </a>
                                            <ul>
                                                <li><a href="#">Menu 01</a></li>
                                                <li><a href="#">Menu 02</a></li>
                                                <li><a href="#">Menu 03</a></li>
                                                <li><a href="#">Menu 04</a></li>
                                                <li><a href="#">Menu 05</a></li>
                                            </ul>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <img src="{{ $pd('imgs/theme/icons/category-3.svg') }}" alt="" />
                                                Jewelry & Fashion
                                            </a>
                                            <ul>
                                                <li><a href="#">Menu 01</a></li>
                                                <li><a href="#">Menu 02</a></li>
                                                <li><a href="#">Menu 03</a></li>
                                                <li><a href="#">Menu 04</a></li>
                                                <li><a href="#">Menu 05</a></li>
                                            </ul>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <img src="{{ $pd('imgs/theme/icons/category-4.svg') }}" alt="" />
                                                Sports Apparel
                                            </a>
                                            <ul>
                                                <li><a href="#">Menu 01</a></li>
                                                <li><a href="#">Menu 02</a></li>
                                                <li><a href="#">Menu 03</a></li>
                                                <li><a href="#">Menu 04</a></li>
                                                <li><a href="#">Menu 05</a></li>
                                            </ul>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <img src="{{ $pd('imgs/theme/icons/category-5.svg') }}" alt="" />
                                                Skincare
                                            </a>
                                            <ul>
                                                <li><a href="#">Menu 01</a></li>
                                                <li><a href="#">Menu 02</a></li>
                                                <li><a href="#">Menu 03</a></li>
                                                <li><a href="#">Menu 04</a></li>
                                                <li><a href="#">Menu 05</a></li>
                                            </ul>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <img src="{{ $pd('imgs/theme/icons/category-6.svg') }}" alt="" />
                                                Exercise & Fitness
                                            </a>
                                            <ul>
                                                <li><a href="#">Menu 01</a></li>
                                                <li><a href="#">Menu 02</a></li>
                                                <li><a href="#">Menu 03</a></li>
                                                <li><a href="#">Menu 04</a></li>
                                                <li><a href="#">Menu 05</a></li>
                                            </ul>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <img src="{{ $pd('imgs/theme/icons/category-7.svg') }}" alt="" />
                                                Toys & Games
                                            </a>
                                            <ul>
                                                <li><a href="#">Menu 01</a></li>
                                                <li><a href="#">Menu 02</a></li>
                                                <li><a href="#">Menu 03</a></li>
                                                <li><a href="#">Menu 04</a></li>
                                                <li><a href="#">Menu 05</a></li>
                                            </ul>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <img src="{{ $pd('imgs/theme/icons/category-8.svg') }}" alt="" />
                                                Sunglasses
                                            </a>
                                            <ul>
                                                <li><a href="#">Menu 01</a></li>
                                                <li><a href="#">Menu 02</a></li>
                                                <li><a href="#">Menu 03</a></li>
                                                <li><a href="#">Menu 04</a></li>
                                                <li><a href="#">Menu 05</a></li>
                                            </ul>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <img src="{{ $pd('imgs/theme/icons/category-9.svg') }}" alt="" />
                                                Denim Collection
                                            </a>
                                            <ul>
                                                <li><a href="#">Menu 01</a></li>
                                                <li><a href="#">Menu 02</a></li>
                                                <li><a href="#">Menu 03</a></li>
                                                <li><a href="#">Menu 04</a></li>
                                                <li><a href="#">Menu 05</a></li>
                                            </ul>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <img src="{{ $pd('imgs/theme/icons/category-1.svg') }}" alt="" />
                                                Men's Clothing
                                            </a>
                                            <ul>
                                                <li><a href="#">Menu 01</a></li>
                                                <li><a href="#">Menu 02</a></li>
                                                <li><a href="#">Menu 03</a></li>
                                                <li><a href="#">Menu 04</a></li>
                                                <li><a href="#">Menu 05</a></li>
                                            </ul>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <img src="{{ $pd('imgs/theme/icons/category-2.svg') }}" alt="" />
                                                Women's Clothing
                                            </a>
                                            <ul>
                                                <li><a href="#">Menu 01</a></li>
                                                <li><a href="#">Menu 02</a></li>
                                                <li><a href="#">Menu 03</a></li>
                                                <li><a href="#">Menu 04</a></li>
                                                <li><a href="#">Menu 05</a></li>
                                            </ul>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <img src="{{ $pd('imgs/theme/icons/category-3.svg') }}" alt="" />
                                                Jewelry & Fashion
                                            </a>
                                            <ul>
                                                <li><a href="#">Menu 01</a></li>
                                                <li><a href="#">Menu 02</a></li>
                                                <li><a href="#">Menu 03</a></li>
                                                <li><a href="#">Menu 04</a></li>
                                                <li><a href="#">Menu 05</a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                                <a href="#" class="more_categories">
                                    view all
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
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
                                        <a href="{{ route('doctor') }}">Doctors</a>
                                        
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
                    <div class="hotline d-none d-lg-flex">
                        <img src="{{ $pd('imgs/theme/icons/icon-headphone-white.svg') }}" alt="hotline" />
                        <p>0000-000<span>24/7 Support Center</span></p>
                    </div>
                    <div class="header-action-icon-2 d-block d-lg-none">
                        <div class="burger-icon burger-icon-white">
                            <span class="burger-icon-top"></span>
                            <span class="burger-icon-mid"></span>
                            <span class="burger-icon-bottom"></span>
                        </div>
                    </div>
                    <div class="header-action-right d-block d-lg-none">
                        <div class="header-action-2">
                            <div class="header-action-icon-2">
                                <a href="shop-wishlist.html">
                                    <img alt="ShopX" src="{{ $pd('imgs/theme/icons/icon-heart.svg') }}" />
                                    <span class="pro-count white">4</span>
                                </a>
                            </div>
                            <div class="header-action-icon-2">
                                <a class="mini-cart-icon" href="#">
                                    <img alt="ShopX" src="{{ $pd('imgs/theme/icons/icon-cart.svg') }}" />
                                    <span class="pro-count white">2</span>
                                </a>
                                <div class="cart-dropdown-wrap cart-dropdown-hm2">
                                    <ul>
                                        <li>
                                            <div class="shopping-cart-img">
                                                <a href="#"><img alt="ShopX"
                                                        src="{{ $pd('imgs/shop/thumbnail-3.jpg') }}" /></a>
                                            </div>
                                            <div class="shopping-cart-title">
                                                <h4><a href="#">Plain Striola Shirts</a></h4>
                                                <h3><span>1 × </span>₱800.00</h3>
                                            </div>
                                            <div class="shopping-cart-delete">
                                                <a href="#"><i class="fi-rs-cross-small"></i></a>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="shopping-cart-img">
                                                <a href="#"><img alt="ShopX"
                                                        src="{{ $pd('imgs/shop/thumbnail-4.jpg') }}" /></a>
                                            </div>
                                            <div class="shopping-cart-title">
                                                <h4><a href="#">Macbook Pro 2025</a></h4>
                                                <h3><span>1 × </span>₱3500.00</h3>
                                            </div>
                                            <div class="shopping-cart-delete">
                                                <a href="#"><i class="fi-rs-cross-small"></i></a>
                                            </div>
                                        </li>
                                    </ul>
                                    <div class="shopping-cart-footer">
                                        <div class="shopping-cart-total">
                                            <h4>Total <span>₱383.00</span></h4>
                                        </div>
                                        <div class="shopping-cart-button">
                                            <a href="shop-cart.html">View cart</a>
                                            <a href="shop-checkout.html">Checkout</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>