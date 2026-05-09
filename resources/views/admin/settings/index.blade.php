@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Administration</div>
          <h2 class="page-title">Settings</h2>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      @if (session('status') === 'site-logo-updated')
        <div class="alert alert-success">Site logo updated successfully.</div>
      @endif

      @if (session('status') === 'site-favicon-updated')
        <div class="alert alert-success">Browser tab icon (favicon) updated successfully.</div>
      @endif

      @if ($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="row g-3">
        <div class="col-12 col-lg-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Site footer</h3>
            </div>
            <div class="card-body">
              <p class="text-secondary mb-3">Edit the public site footer: newsletter text, social icons, department and page links, contact details, and copyright.</p>
              <a href="{{ route('admin.settings.footer') }}" class="btn btn-primary">Edit site footer</a>
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Account settings</h3>
            </div>
            <div class="card-body">
              <p class="text-secondary mb-3">Manage your profile details, profile photo, and account password.</p>
              <a href="{{ route('admin.profile') }}" class="btn btn-primary">Open profile settings</a>
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Site logo</h3>
            </div>
            <div class="card-body">
              <div class="mb-3">
                @if ($siteLogo)
                  <img src="{{ asset($siteLogo) }}" alt="Site logo" class="img-fluid"
                    style="max-height: 72px; width: auto;">
                @else
                  <img src="{{ asset('admin/assets/static/logo.svg') }}" alt="Site logo" class="img-fluid"
                    style="max-height: 72px; width: auto;">
                @endif
              </div>

              <form method="POST" action="{{ route('admin.settings.logo.update') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                  <label class="form-label" for="site_logo">Upload new logo</label>
                  <input id="site_logo" type="file" name="site_logo" class="form-control" accept="image/*" required>
                  <div class="form-hint">PNG, JPG, SVG or WebP up to 1MB.</div>
                </div>

                <button type="submit" class="btn btn-primary">Save logo</button>
              </form>
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Browser tab icon</h3>
            </div>
            <div class="card-body">
              <p class="text-secondary mb-3">This icon appears in the browser tab next to the page title (favicon).</p>
              <div class="mb-3 d-flex align-items-center gap-3">
                @if ($siteFavicon && is_file(public_path(ltrim($siteFavicon, '/'))))
                  <img src="{{ asset($siteFavicon) }}" alt="Current favicon" class="rounded border bg-white p-1"
                    style="width: 48px; height: 48px; object-fit: contain;">
                @elseif ($siteFavicon && \Illuminate\Support\Str::startsWith($siteFavicon, ['http://', 'https://']))
                  <img src="{{ $siteFavicon }}" alt="Current favicon" class="rounded border bg-white p-1"
                    style="width: 48px; height: 48px; object-fit: contain;">
                @else
                  <div class="rounded border bg-secondary-lt d-flex align-items-center justify-content-center text-secondary small"
                    style="width: 48px; height: 48px;">Default</div>
                @endif
                <span class="text-secondary small">Shown after you save and refresh admin pages.</span>
              </div>

              <form method="POST" action="{{ route('admin.settings.favicon.update') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                  <label class="form-label" for="site_favicon">Upload favicon</label>
                  <input id="site_favicon" type="file" name="site_favicon" class="form-control @error('site_favicon') is-invalid @enderror" accept=".ico,.png,.jpg,.jpeg,.gif,.svg,.webp,image/x-icon,image/png,image/jpeg,image/gif,image/svg+xml,image/webp" required>
                  <div class="form-hint">ICO, PNG, JPG, GIF, SVG or WebP. Max 512 KB. Square images (e.g. 32×32 or 64×64) work best.</div>
                  @error('site_favicon')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <button type="submit" class="btn btn-primary">Save favicon</button>
              </form>
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Current account</h3>
            </div>
            <div class="card-body">
              <div class="mb-2">
                <span class="text-secondary">Name:</span>
                <span class="fw-medium">{{ $admin?->name ?? 'Admin User' }}</span>
              </div>
              <div class="mb-2">
                <span class="text-secondary">Email:</span>
                <span class="fw-medium">{{ $admin?->email ?? '—' }}</span>
              </div>
              <div>
                <span class="text-secondary">Role:</span>
                <span class="fw-medium">{{ ucfirst((string) ($admin?->role ?? 'administrator')) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
