@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col">
          <div class="page-pretitle text-secondary">Homepage</div>
          <h2 class="page-title mb-0">Edit slide</h2>
          <div class="text-secondary small mt-1">Change the text or photo anytime. Reorder from the slides list.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.slides') }}" class="btn">Back to list</a>
            <button type="submit" form="slide-edit-form" class="btn btn-primary">Save changes</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl pb-5">
      <form action="{{ route('admin.slides.update', $slide) }}" method="POST" enctype="multipart/form-data"
        id="slide-edit-form" class="row g-4 align-items-start">
        @csrf
        @method('PUT')

        <div class="col-lg-7">
          @include('admin.slides.partials.form-fields', ['slide' => $slide])
        </div>

        <div class="col-lg-5">
          <div class="sticky-top pt-0" style="top: 1rem;">
            <div class="card border-0 shadow-sm mb-3">
              <div class="card-body">
                <h3 class="card-title mb-1">Photo</h3>
                <p class="text-secondary small mb-3">Upload a new file only if you want to replace the current image.</p>

                <div id="slide-image-preview"
                  class="rounded ratio ratio-4x3 border mb-3 bg-light"
                  style="background-size: cover; background-position: center; background-image: url('{{ $slide->image_url }}');"></div>

                <label for="image" data-slide-dropzone
                  class="slide-image-dropzone border border-2 border-dashed rounded-3 p-4 text-center d-block">
                  <input id="image" name="image" type="file" accept="image/*"
                    class="d-none @error('image') is-invalid @enderror">
                  <span class="text-secondary small">Drop a new photo here, or click to replace</span>
                </label>
                @error('image')
                  <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror

                <div class="mt-3">
                  <label class="form-label" for="image_alt">Short description of the photo</label>
                  <input id="image_alt" name="image_alt" type="text" autocomplete="off"
                    class="form-control @error('image_alt') is-invalid @enderror"
                    value="{{ old('image_alt', $slide->image_alt) }}"
                    placeholder="Optional — we use your headline if empty">
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
