@php
    $pd ??= fn (string $path) => asset('patients/' . ltrim($path, '/'));
@endphp
<footer class="main pt-10">
        
       
        <section class="footer-mid pt-70 pb-65 mt-45">
            <div class="container">
                <div class="row justify-content-between">
                    <div class="col-xl-3">
                        <div class="widget-about font-md mb-md-3 mb-lg-3 mb-xl-0 wow animate__animated animate__fadeInUp"
                            data-wow-delay="0">
                            <div class="logo mb-30">
                                <a href="{{ route('home') }}" class="mb-15"><img src="{{ $pd('imgs/theme/bgs.png') }}"
                                        alt="logo" /></a>
                                <p class="font-lg text-heading">Awesome eCommerce store website template</p>
                            </div>
                            <ul class="contact-infor">
                                <li><img src="{{ $pd('imgs/theme/bgs.png') }}" alt="" /><strong>Address:
                                    </strong> <span>233 North Michigan Avenue, Suite 1800, Chicago, IL 60601</span>
                                </li>
                                <li><img src="{{ $pd('imgs/theme/bgs.png') }}" alt="" /><strong>Call
                                        Us:</strong><span>+9625415 546666464</span></li>
                                <li><img src="{{ $pd('imgs/theme/bgs.png') }}"
                                        alt="" /><strong>Email:</strong><span>sale@shopx.com</span></li>
                                <li><img src="{{ $pd('imgs/theme/bgs.png') }}"
                                        alt="" /><strong>Hours:</strong><span>10:00 - 18:00, Mon - Sat</span></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xl-2 col-sm-6 col-lg-3">
                        <div class="footer-link-widget wow animate__animated animate__fadeInUp" data-wow-delay=".1s">
                            <h4 class="widget-title">Company</h4>
                            <ul class="footer-list mb-sm-5 mb-md-0">
                                <li><a href="#">About Us</a></li>
                                <li><a href="#">Delivery Information</a></li>
                                <li><a href="#">Privacy Policy</a></li>
                                <li><a href="#">Terms &amp; Conditions</a></li>
                                <li><a href="#">Contact Us</a></li>
                                <li><a href="#">Support Center</a></li>
                                <li><a href="#">Careers</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xl-2 col-sm-6 col-lg-3">
                        <div class="footer-link-widget wow animate__animated animate__fadeInUp" data-wow-delay=".2s">
                            <h4 class="widget-title">Account</h4>
                            <ul class="footer-list mb-sm-5 mb-md-0">
                                <li><a href="#">Sign In</a></li>
                                <li><a href="#">View Cart</a></li>
                                <li><a href="#">My Wishlist</a></li>
                                <li><a href="#">Track My Order</a></li>
                                <li><a href="#">Help Ticket</a></li>
                                <li><a href="#">Shipping Details</a></li>
                                <li><a href="#">Compare products</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xl-2 col-sm-6 col-lg-3">
                        <div class="footer-link-widget  wow animate__animated animate__fadeInUp" data-wow-delay=".3s">
                            <h4 class="widget-title">Corporate</h4>
                            <ul class="footer-list mb-sm-5 mb-md-0">
                                <li><a href="#">Become a Vendor</a></li>
                                <li><a href="#">Affiliate Program</a></li>
                                <li><a href="#">Farm Business</a></li>
                                <li><a href="#">Farm Careers</a></li>
                                <li><a href="#">Our Suppliers</a></li>
                                <li><a href="#">Accessibility</a></li>
                                <li><a href="#">Promotions</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xl-2 col-sm-6 col-lg-3">
                        <div class="footer-link-widget wow animate__animated animate__fadeInUp" data-wow-delay=".4s">
                            <h4 class="widget-title">Popular</h4>
                            <ul class="footer-list mb-sm-5 mb-md-0">
                                <li><a href="#">Milk & Flavoured Milk</a></li>
                                <li><a href="#">Butter and Margarine</a></li>
                                <li><a href="#">Eggs Substitutes</a></li>
                                <li><a href="#">Marmalades</a></li>
                                <li><a href="#">Sour Accossorice and Dips</a></li>
                                <li><a href="#">Tea & Kombucha</a></li>
                                <li><a href="#">Cheese</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
        </section>
        <div class="container pb-30 wow animate__animated animate__fadeInUp" data-wow-delay="0">
            <div class="row align-items-center">
                <div class="col-12 mb-30">
                    <div class="footer-bottom"></div>
                </div>
                <div class="col-xl-4 col-lg-6 col-md-6">
                    <p class="font-sm mb-0">&copy; 2025, <strong class="text-brand">ShopX</strong> - HTML Ecommerce
                        Template <br />All rights reserved</p>
                </div>
                <div class="col-xl-4 col-lg-6 text-center d-none d-xl-block">
                    <div class="hotline d-lg-inline-flex">
                        <img src="{{ $pd('imgs/theme/bgs.png') }}" alt="hotline" />
                        <p>0000-000<span>24/7 Support Center</span></p>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-6 col-md-6 text-end d-none d-md-block">
                    <div class="mobile-social-icon">
                        <h6>Follow Us</h6>
                            <a href="#"><img src="{{ $pd('imgs/theme/bgs.png') }}" alt="" /></a>
                            <a href="#"><img src="{{ $pd('imgs/theme/bgs.png') }}" alt="" /></a>
                            <a href="#"><img src="{{ $pd('imgs/theme/bgs.png') }}" alt="" /></a>
                    </div>
                    <p class="font-sm">Up to 15% discount on your first subscribe</p>
                </div>
            </div>
        </div>
    </footer>