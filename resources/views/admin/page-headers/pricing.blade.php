@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col">
          <div class="page-pretitle text-secondary">Pages · Page headers</div>
          <h2 class="page-title mb-0">Pricing page header</h2>
          <div class="text-secondary small mt-1">Background image behind the title on the public Pricing page (<code>/pricing</code>).</div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
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
              <h3 class="card-title">Current preview</h3>
            </div>
            <div class="card-body">
              <div class="rounded border overflow-hidden bg-secondary-lt" style="aspect-ratio: 21 / 9; max-height: 220px;">
                <div class="w-100 h-100" style="background-image: url({{ $previewUrl }}); background-size: cover; background-position: center; min-height: 160px;"></div>
              </div>
              @if ($currentPath)
                <p class="text-secondary small mt-2 mb-0">Custom image: <code class="user-select-all">{{ $currentPath }}</code></p>
              @else
                <p class="text-secondary small mt-2 mb-0">Using default theme image.</p>
              @endif
            </div>
          </div>
        </div>
        <div class="col-12 col-lg-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Upload new background</h3>
            </div>
            <div class="card-body">
              <form method="POST" action="{{ route('admin.page-headers.pricing.update') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                  <label class="form-label" for="background">Image file</label>
                  <input id="background" type="file" name="background" class="form-control @error('background') is-invalid @enderror" accept="image/*" required>
                  <div class="form-hint">JPG, PNG, WebP or GIF. Max 5 MB. Wide landscape images work best.</div>
                  @error('background')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <button type="submit" class="btn btn-primary">Save image</button>
              </form>
              @if ($currentPath)
                <hr class="my-3">
                <p class="text-secondary small mb-2">Remove the uploaded image and use the default background again.</p>
                <form method="POST" action="{{ route('admin.page-headers.pricing.reset') }}" onsubmit="return confirm('Reset to the default header image?');">
                  @csrf
                  <button type="submit" class="btn btn-outline-danger">Use default image</button>
                </form>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
