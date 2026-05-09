@php
  $homeSlides = $homeSlides ?? collect();
@endphp

@if ($homeSlides->isEmpty())
  {{-- No DB slides: skip swiper (empty wrapper breaks Swiper). Run SlideSeeder or add slides in admin. --}}
@else
  <style>
    .main-slider .main-slider__content {
      display: grid;
      grid-template-columns: minmax(0, 1.05fr) minmax(320px, 0.95fr);
      align-items: center;
      gap: clamp(1.25rem, 3vw, 2.5rem);
    }

    .main-slider .main-slider__copy {
      position: relative;
      z-index: 2;
      max-width: 700px;
    }

    .main-slider .main-slider__text {
      max-width: 58ch;
    }

    .main-slider .main-slider__img-box {
      position: relative;
      top: auto;
      right: auto;
      width: min(100%, 560px);
      justify-self: end;
    }

    .main-slider .main-slider__img {
      height: clamp(300px, 36vw, 500px);
    }

    .main-slider .main-slider__img img {
      height: 100%;
      object-fit: cover;
      object-position: center;
    }

    @media (max-width: 1199.98px) {
      .main-slider .main-slider__content {
        grid-template-columns: 1fr;
        gap: 1.4rem;
      }

      .main-slider .main-slider__copy {
        max-width: 100%;
      }

      .main-slider .main-slider__img-box {
        width: min(100%, 620px);
        justify-self: center;
      }
    }
  </style>
  <section class="main-slider">
    <div class="swiper-container thm-swiper__slider" data-swiper-options='{"slidesPerView": 1, "loop": true,
                "effect": "fade",
                "pagination": {
                "el": "#main-slider-pagination",
                "type": "bullets",
                "clickable": true
                },
                "navigation": {
                "nextEl": "#main-slider__swiper-button-next",
                "prevEl": "#main-slider__swiper-button-prev"
                },
                "autoplay": {
                    "delay": 8000
                }
            }'>
      <div class="swiper-wrapper">
        @foreach ($homeSlides as $slide)
          @php
            $btnText = filled($slide->button_text) ? $slide->button_text : 'More About Us';
            $btnUrl = filled($slide->button_url) ? $slide->button_url : route('contact');
            $videoLabel = filled($slide->video_label) ? $slide->video_label : 'Watch Video';
            $videoUrl = $slide->video_url;
            $showVideo = $slide->show_video && filled($videoUrl);
          @endphp
          <div class="swiper-slide">
            <div class="main-slider__shape-1"></div>
            <div class="main-slider__shape-2"></div>
            <div class="main-slider__shape-3"></div>
            <div class="container">
              <div class="row">
                <div class="col-xl-12">
                  <div class="main-slider__content">
                    <div class="main-slider__copy">
                      @if (filled($slide->subtitle))
                        <p class="main-slider__sub-title">{{ $slide->subtitle }}</p>
                      @endif
                      <h2 class="main-slider__title">
                        {!! nl2br(e($slide->title)) !!}
                        @if (filled($slide->title_span))
                          <span>{!! nl2br(e($slide->title_span)) !!}</span>
                        @endif
                      </h2>
                      @if (filled($slide->description))
                        <p class="main-slider__text">{!! nl2br(e($slide->description)) !!}</p>
                      @endif
                      <div class="main-slider__btn-and-video-box">
                        <div class="main-slider__btn-box">
                          <a href="{{ $btnUrl }}" class="thm-btn">{{ $btnText }} <span class="icon-plus"></span></a>
                        </div>
                        @if ($showVideo)
                          <div class="main-slider__video-box">
                            <div class="main-slider__video-link">
                              <a href="{{ $videoUrl }}" class="video-popup">
                                <div class="main-slider__video-icon">
                                  <span class="fa fa-play"></span>
                                  <i class="ripple"></i>
                                </div>
                              </a>
                            </div>
                            <h4 class="main-slider__video-title">{{ $videoLabel }}</h4>
                          </div>
                        @endif
                      </div>
                    </div>
                    <div class="main-slider__img-box">
                      <div class="main-slider__img">
                        <img src="{{ $slide->image_url }}" alt="{{ $slide->image_alt ?? '' }}">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      <div class="swiper-pagination" id="main-slider-pagination"></div>
    </div>
  </section>
@endif
