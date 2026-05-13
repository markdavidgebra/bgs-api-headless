@extends('doctor.layouts.master')

@section('title', 'Profile')

@section('content')
  @php
    /** @var \App\Models\Doctor $doctor */
    $imageUrl = $doctor->image_url;
    $statusClass = fn (?string $status) => match (strtolower((string) $status)) {
        'active' => 'text-success',
        'inactive' => 'text-danger',
        default => 'text-muted',
    };
  @endphp

  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Doctor <span></span> Profile
        </div>
      </div>
    </div>

    <div class="page-content pt-70 pb-60">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="row">
              @include('doctor.layouts.sidebar')

              <div class="col-12 col-md-9">
                <div class="account dashboard-content pl-50">
                  <div class="section-title mb-20">
                    <h3 class="mb-5">My profile</h3>
                    <p class="mb-0 text-secondary">Update your professional details, photo, login email, and password.</p>
                  </div>

                  @if (session('success'))
                    <div class="alert alert-success mb-20">{{ session('success') }}</div>
                  @endif

                  @if ($errors->any())
                    <div class="alert alert-danger mb-20">
                      <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                          <li>{{ $error }}</li>
                        @endforeach
                      </ul>
                    </div>
                  @endif

                  <div class="card mb-25">
                    <div class="card-header">
                      <h5 class="mb-0">Profile &amp; professional information</h5>
                    </div>
                    <div class="card-body">
                      <form method="POST" action="{{ route('doctor.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="row align-items-start mb-20">
                          <div class="col-auto mb-15">
                            @if ($imageUrl)
                              <img src="{{ $imageUrl }}" alt="" class="rounded-circle border"
                                style="width: 96px; height: 96px; object-fit: cover;">
                            @else
                              <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center fw-bold text-secondary"
                                style="width: 96px; height: 96px; font-size: 2rem;">
                                {{ $doctor->initial }}
                              </div>
                            @endif
                          </div>
                          <div class="col-md-8 mb-15">
                            <label class="form-label">Profile photo</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                            <p class="text-muted small mb-0 mt-5">JPG or PNG, up to 2&nbsp;MB. Replaces your current
                              photo.</p>
                          </div>
                        </div>

                        <div class="row">
                          <div class="col-md-6 mb-15">
                            <label class="form-label">Full name</label>
                            <input type="text" name="name" class="form-control"
                              value="{{ old('name', $doctor->name) }}" required autocomplete="name">
                          </div>
                          <div class="col-md-6 mb-15">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                              value="{{ old('email', $doctor->email) }}" required autocomplete="email">
                          </div>
                          <div class="col-md-6 mb-15">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control"
                              value="{{ old('phone', $doctor->phone) }}" autocomplete="tel">
                          </div>
                          <div class="col-md-6 mb-15">
                            <label class="form-label">Account status</label>
                            <p class="mb-0 pt-8"><span class="{{ $statusClass($doctor->status) }}">{{ ucfirst((string) ($doctor->status ?? 'active')) }}</span>
                              <span class="text-muted small">&mdash; contact admin to change.</span>
                            </p>
                          </div>
                          <div class="col-md-6 mb-15">
                            <label class="form-label">Specialty</label>
                            <input type="text" name="specialty" class="form-control"
                              value="{{ old('specialty', $doctor->specialty) }}">
                          </div>
                          <div class="col-md-6 mb-15">
                            <label class="form-label">License no.</label>
                            <input type="text" name="license_no" class="form-control"
                              value="{{ old('license_no', $doctor->license_no) }}">
                          </div>
                          <div class="col-md-6 mb-15">
                            <label class="form-label">Years of experience</label>
                            <input type="number" name="experience_years" class="form-control" min="0" max="80"
                              value="{{ old('experience_years', $doctor->experience_years) }}">
                          </div>
                          <div class="col-12 mb-15">
                            <label class="form-label">Bio</label>
                            <textarea name="bio" class="form-control" rows="4">{{ old('bio', $doctor->bio) }}</textarea>
                          </div>
                          @php
                            $socialPlatforms = [
                                'facebook_url' => 'Facebook',
                                'linkedin_url' => 'LinkedIn',
                                'x_url' => 'X (Twitter)',
                                'pinterest_url' => 'Pinterest',
                                'instagram_url' => 'Instagram',
                                'youtube_url' => 'YouTube',
                                'tiktok_url' => 'TikTok',
                                'threads_url' => 'Threads',
                                'telegram_url' => 'Telegram',
                                'whatsapp_url' => 'WhatsApp',
                                'snapchat_url' => 'Snapchat',
                                'reddit_url' => 'Reddit',
                                'tumblr_url' => 'Tumblr',
                                'discord_url' => 'Discord',
                                'twitch_url' => 'Twitch',
                                'github_url' => 'GitHub',
                                'behance_url' => 'Behance',
                                'dribbble_url' => 'Dribbble',
                                'medium_url' => 'Medium',
                                'vimeo_url' => 'Vimeo',
                                'website_url' => 'Website',
                            ];
                            $socialPlatformIcons = [
                                'facebook_url' => 'fa-brands fa-facebook-f',
                                'linkedin_url' => 'fa-brands fa-linkedin-in',
                                'x_url' => 'fa-brands fa-x-twitter',
                                'pinterest_url' => 'fa-brands fa-pinterest-p',
                                'instagram_url' => 'fa-brands fa-instagram',
                                'youtube_url' => 'fa-brands fa-youtube',
                                'tiktok_url' => 'fa-brands fa-tiktok',
                                'threads_url' => 'fa-brands fa-threads',
                                'telegram_url' => 'fa-brands fa-telegram',
                                'whatsapp_url' => 'fa-brands fa-whatsapp',
                                'snapchat_url' => 'fa-brands fa-snapchat',
                                'reddit_url' => 'fa-brands fa-reddit-alien',
                                'tumblr_url' => 'fa-brands fa-tumblr',
                                'discord_url' => 'fa-brands fa-discord',
                                'twitch_url' => 'fa-brands fa-twitch',
                                'github_url' => 'fa-brands fa-github',
                                'behance_url' => 'fa-brands fa-behance',
                                'dribbble_url' => 'fa-brands fa-dribbble',
                                'medium_url' => 'fa-brands fa-medium',
                                'vimeo_url' => 'fa-brands fa-vimeo-v',
                                'website_url' => 'fa-solid fa-globe',
                            ];

                            $savedSocialLinks = collect((array) ($doctor->social_links ?? []))
                                ->filter(fn ($url, $platform) => filled($platform) && filled($url))
                                ->map(fn ($url, $platform) => [
                                    'platform' => (string) $platform,
                                    'label' => $socialPlatforms[(string) $platform] ?? (string) $platform,
                                    'url' => (string) $url,
                                ])
                                ->values();

                            $socialLinkRows = old('social_links');
                            if (! is_array($socialLinkRows) || $socialLinkRows === []) {
                                $socialLinkRows = [['platform' => '', 'url' => '']];
                            }
                          @endphp
                          <div class="col-12 mb-15">
                            <div class="d-flex justify-content-between align-items-center mb-8">
                              <label class="form-label mb-0">Social links</label>
                            </div>
                            <p class="text-muted small mb-10">Select a platform, then paste your profile URL.</p>

                            <div id="existing-social-hidden-inputs">
                              @foreach ($savedSocialLinks as $savedRow)
                                <input type="hidden" name="social_existing[{{ $savedRow['platform'] }}]"
                                  value="{{ $savedRow['url'] }}" data-platform="{{ $savedRow['platform'] }}">
                              @endforeach
                            </div>

                            <div id="social-link-rows" class="d-grid gap-2">
                              @forelse ($socialLinkRows as $row)
                                <div class="row g-2 social-link-row">
                                  <div class="col-md-4">
                                    <select class="form-select social-platform-select" name="social_links[0][platform]">
                                      <option value="">Select platform</option>
                                      @foreach ($socialPlatforms as $platformKey => $platformLabel)
                                        <option value="{{ $platformKey }}" @selected(($row['platform'] ?? '') === $platformKey)>{{ $platformLabel }}</option>
                                      @endforeach
                                    </select>
                                  </div>
                                  <div class="col-md-8">
                                    <input type="url" class="form-control social-url-input" name="social_links[0][url]"
                                      value="{{ $row['url'] ?? '' }}" placeholder="https://...">
                                  </div>
                                </div>
                              @empty
                                <div class="row g-2 social-link-row">
                                  <div class="col-md-4">
                                    <select class="form-select social-platform-select" name="social_links[0][platform]">
                                      <option value="">Select platform</option>
                                      @foreach ($socialPlatforms as $platformKey => $platformLabel)
                                        <option value="{{ $platformKey }}">{{ $platformLabel }}</option>
                                      @endforeach
                                    </select>
                                  </div>
                                  <div class="col-md-8">
                                    <input type="url" class="form-control social-url-input" name="social_links[0][url]" placeholder="https://...">
                                  </div>
                                </div>
                              @endforelse
                            </div>

                            <div class="mt-12">
                              <p class="text-muted small mb-8">Saved social links</p>
                              @if ($savedSocialLinks->isNotEmpty())
                                <ul class="list-unstyled d-flex flex-wrap gap-2 mb-0" id="saved-social-links-list">
                                  @foreach ($savedSocialLinks as $savedRow)
                                    @php
                                      $platformKey = (string) ($savedRow['platform'] ?? '');
                                      $platformLabel = $socialPlatforms[$platformKey] ?? $platformKey;
                                      $platformUrl = (string) ($savedRow['url'] ?? '');
                                      $platformIcon = $socialPlatformIcons[$platformKey] ?? 'icon-link';
                                    @endphp
                                    <li class="small mb-0 d-flex align-items-center gap-1 saved-social-link-item"
                                      data-platform="{{ $platformKey }}" data-url="{{ $platformUrl }}">
                                      <a href="{{ $platformUrl }}" target="_blank" rel="noopener noreferrer"
                                        class="saved-social-link-btn"
                                        title="{{ $platformLabel }}"
                                        aria-label="{{ $platformLabel }}">
                                        <i class="{{ $platformIcon }}"></i>
                                        <span class="saved-social-link-btn__label">{{ $platformLabel }}</span>
                                      </a>
                                      <button type="button" class="saved-social-link-remove-btn remove-saved-social-link-btn" title="Remove">
                                        &times;
                                      </button>
                                    </li>
                                  @endforeach
                                </ul>
                              @else
                                <p class="text-muted small mb-0">No social links added yet.</p>
                              @endif
                            </div>
                          </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save profile</button>
                      </form>

                    </div>
                  </div>

                  <div class="card mb-0">
                    <div class="card-header">
                      <h5 class="mb-0">Change password</h5>
                    </div>
                    <div class="card-body">
                      <form method="POST" action="{{ route('doctor.profile.password') }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                          <div class="col-md-6 mb-15">
                            <label class="form-label">Current password</label>
                            <input type="password" name="current_password" class="form-control" required
                              autocomplete="current-password">
                          </div>
                          <div class="w-100"></div>
                          <div class="col-md-6 mb-15">
                            <label class="form-label">New password</label>
                            <input type="password" name="password" class="form-control" required
                              autocomplete="new-password">
                          </div>
                          <div class="col-md-6 mb-15">
                            <label class="form-label">Confirm new password</label>
                            <input type="password" name="password_confirmation" class="form-control" required
                              autocomplete="new-password">
                          </div>
                        </div>

                        <button type="submit" class="btn btn-outline-primary">Update password</button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <script>
    (function () {
      const form = document.querySelector('form[action="{{ route('doctor.profile.update') }}"]');
      const rowsWrap = document.getElementById('social-link-rows');
      if (!form || !rowsWrap) return;

      function reindexRows() {
        rowsWrap.querySelectorAll('.social-link-row').forEach((row, index) => {
          const platformInput = row.querySelector('.social-platform-select');
          const urlInput = row.querySelector('.social-url-input');
          if (platformInput) platformInput.name = `social_links[${index}][platform]`;
          if (urlInput) urlInput.name = `social_links[${index}][url]`;
        });
      }

      document.querySelectorAll('.remove-saved-social-link-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
          const item = btn.closest('.saved-social-link-item');
          if (!item) return;

          const platform = item.getAttribute('data-platform') || '';
          const hiddenInput = document.querySelector(`#existing-social-hidden-inputs input[data-platform="${platform}"]`);
          if (hiddenInput) hiddenInput.remove();

          item.remove();
          reindexRows();
        });
      });

      form.addEventListener('submit', function () {
        reindexRows();
      });
      reindexRows();
    })();
  </script>
  <style>
    .saved-social-link-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      border: 1px solid #e3d1dc;
      border-radius: 999px;
      background: #fff;
      color: #8f5e78;
      font-size: 0.8rem;
      padding: 0.25rem 0.6rem;
      text-decoration: none;
    }
    .saved-social-link-btn:hover {
      border-color: #c7819d;
      color: #a4537a;
      text-decoration: none;
    }
    .saved-social-link-btn__label {
      font-weight: 600;
      line-height: 1;
    }
    .saved-social-link-remove-btn {
      width: 1.65rem;
      height: 1.65rem;
      border: 1px solid #efdae4;
      border-radius: 999px;
      background: #fff;
      color: #be7d9a;
      font-weight: 700;
      line-height: 1;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0;
    }
    .saved-social-link-remove-btn:hover {
      border-color: #c7819d;
      color: #9f5579;
    }
  </style>
@endsection
