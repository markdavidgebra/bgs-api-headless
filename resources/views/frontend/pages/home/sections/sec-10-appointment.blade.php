<section class="appiontment-one">
    <div class="container">
        <div class="appiontment-one__inner">
            <div class="appiontment-one__img">
                <img src="{{ asset("frontend/assets/images/resources/appiontment-one-img-1.jpg") }}" alt="">
                <div class="appiontment-one__appoin-and-working-hour">
                    <div class="appiontment-one__appion-box wow slideInLeft" data-wow-delay="100ms"
                        data-wow-duration="2500ms">
                        <h3 class="appiontment-one__appion-title">Appiontment Now</h3>
                        <form class="contact-form-validated appiontment-one__appion-form" method="POST" action="assets/inc/sendemail.php" novalidate="novalidate">
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="appiontment-one__appion-input-box">
                                        <input type="text" name="name" placeholder="Your Name" required="">
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="appiontment-one__appion-input-box">
                                        <input type="email" name="email" placeholder="Your Email" required="">
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="appiontment-one__appion-input-box">
                                        <input type="text" name="number" placeholder="Your Number" required="">
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="appiontment-one__appion-input-box text-message-box">
                                        <textarea name="message" placeholder="Message here.."></textarea>
                                    </div>
                                    <div class="appiontment-one__appion-btn-box">
                                        <button type="submit" class="thm-btn">Appointment Now<span
                                                class="icon-plus"></span></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="result"></div>
                    </div>
                    <div class="appiontment-one__working-hour wow slideInRight" data-wow-delay="100ms"
                        data-wow-duration="2500ms">
                        <h3 class="appiontment-one__working-hour-title">Working Hours</h3>
                        <p class="appiontment-one__working-hour-text">Health care is a vital aspect of maintain
                            overall well-being, encompassing a range</p>
                        <ul class="appiontment-one__working-hour-list list-unstyled">
                            <li>
                                <span>Saturday-Sunday</span>
                                <p>9 Am To 5 Pm</p>
                            </li>
                            <li>
                                <span>Monday-Tuesday</span>
                                <p>1 Pm To 7 Pm</p>
                            </li>
                            <li>
                                <span>Wednesday-Thusday</span>
                                <p>2 Am To 6 Pm</p>
                            </li>
                            <li>
                                <span>Friday</span>
                                <p>Off Day</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>