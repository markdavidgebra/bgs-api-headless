@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col">
          <div class="page-pretitle text-secondary">Homepage</div>
          <h2 class="page-title mb-0">New slide</h2>
          <div class="text-secondary small mt-1">Fill in the text and add a photo. Slide order is set automatically—you can rearrange slides on the list page.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.slides') }}" class="btn">Cancel</a>
            <button type="submit" form="slide-create-form" class="btn btn-primary">Save slide</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl pb-5">
      <form action="{{ route('admin.slides.store') }}" method="POST" enctype="multipart/form-data" id="slide-create-form"
        class="row g-4 align-items-start">
        @csrf

        <div class="col-lg-7">
          @include('admin.slides.partials.form-fields', ['slide' => null])
        </div>

        <div class="col-lg-5">
          <div class="sticky-top pt-0" style="top: 1rem;">
            <div class="card border-0 shadow-sm mb-3">
              <div class="card-body">
                <h3 class="card-title mb-1">Photo</h3>
                <p class="text-secondary small mb-3">This image fills the large area on the right of the slide.</p>

                <label for="image" data-slide-dropzone
                  class="slide-image-dropzone border border-2 border-dashed rounded-3 p-4 text-center mb-3 mb-md-0 d-block">
                  <input id="image" name="image" type="file" accept="image/*" required
                    class="d-none @error('image') is-invalid @enderror">
                  <span class="avatar avatar-xl bg-secondary-lt text-secondary mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="28" height="28" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" /><path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5" /><path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l3 3" /></svg>
                  </span>
                  <span class="d-block text-secondary small">Drop a photo here, or click to browse</span>
                  <span class="d-block text-muted mt-1" style="font-size: .7rem;">JPG, PNG or WebP · up to 8&nbsp;MB</span>
                </label>
                @error('image')
                  <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror

                <div id="slide-image-preview"
                  class="rounded ratio ratio-4x3 border bg-light mt-3"
                  style="background-size: cover; background-position: center;"></div>

                <div class="mt-3">
                  <label class="form-label" for="image_alt">Short description of the photo</label>
                  <input id="image_alt" name="image_alt" type="text" autocomplete="off"
                    class="form-control @error('image_alt') is-invalid @enderror" value="{{ old('image_alt') }}"
                    placeholder="Optional — helps accessibility; we use your headline if empty">
                  @error('image_alt')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="card border-0 shadow-sm slide-live-preview">
              <div class="card-header border-0 pb-0">
                <h3 class="card-title mb-0">Live preview</h3>
                <div class="text-secondary small">Updates as you type</div>
              </div>
              <div class="card-body pt-2">
                <p class="slide-live-preview__sub mb-0" data-preview-subtitle>—</p>
                <h4 class="slide-live-preview__title mb-0" data-preview-title>Your headline will appear here</h4>
                <div data-preview-accent-wrap style="display: none;">
                  <span class="d-block text-primary fw-bold mt-1" style="white-space: pre-line; font-size: 1.1rem;"
                    data-preview-span></span>
                </div>
                <p class="slide-live-preview__text mb-0" data-preview-description style="display: none;"></p>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
@endsection
