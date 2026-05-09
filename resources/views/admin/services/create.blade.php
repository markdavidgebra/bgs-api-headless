@extends('admin.layouts.master')

@php
  $oldDocs = (array) old('assigned_doctors', []);
@endphp

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col-auto">
          <span class="avatar avatar-xl rounded bg-azure-lt text-azure">S</span>
        </div>
        <div class="col">
          <div class="page-pretitle text-secondary">Catalog</div>
          <h2 class="page-title mb-0">{{ old('name', 'New service') }}</h2>
          <div class="text-secondary small mt-1">Create a treatment service. The slug updates from the name as you type.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.services') }}" class="btn">Cancel</a>
            <button type="submit" form="service-create-form" class="btn btn-primary">Save service</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row g-3">
        <div class="col-lg-8">
          <form action="{{ route('admin.services.store') }}" method="POST" enctype="multipart/form-data"
            id="service-create-form">
            @csrf
            <div class="card">
              <div class="card-header">
                <h3 class="card-title mb-0">Service details</h3>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label required" for="name">Name</label>
                    <input id="name" name="name" type="text"
                      class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                      placeholder="e.g. Hydra Facial" required autocomplete="off">
                    @error('name')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-12">
                    <label class="form-label" for="slug">Slug</label>
                    <input id="slug" type="text"
                      class="form-control bg-secondary-lt @error('slug') is-invalid @enderror" value="{{ old('slug', old('name') !== null && old('name') !== '' ? \Illuminate\Support\Str::slug(old('name')) : '') }}"
                      disabled autocomplete="off">
                    <input type="hidden" name="slug" id="slug-hidden" value="{{ old('slug', old('name') !== null && old('name') !== '' ? \Illuminate\Support\Str::slug(old('name')) : '') }}">
                    <div class="form-hint">Generated from the name. Submitted automatically.</div>
                    @error('slug')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-12">
                    <label class="form-label required" for="short_description">Short description</label>
                    <input id="short_description" name="short_description" type="text"
                      class="form-control @error('short_description') is-invalid @enderror"
                      value="{{ old('short_description') }}" placeholder="One-line summary for listings" required>
                    @error('short_description')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-12">
                    <label class="form-label required" for="description">Description</label>
                    <textarea id="description" name="description" rows="5"
                      class="form-control @error('description') is-invalid @enderror" required
                      placeholder="Full description">{{ old('description') }}</textarea>
                    @error('description')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-md-6">
                    <label class="form-label required" for="price">Price</label>
                    <div class="input-group">
                      <span class="input-group-text">$</span>
                      <input id="price" name="price" type="number" step="0.01" min="0"
                        class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}" required>
                      @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label" for="promo_price">Promo price</label>
                    <div class="input-group">
                      <span class="input-group-text">$</span>
                      <input id="promo_price" name="promo_price" type="number" step="0.01" min="0"
                        class="form-control @error('promo_price') is-invalid @enderror" value="{{ old('promo_price') }}">
                      @error('promo_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label" for="duration_minutes">Duration (minutes)</label>
                    <input id="duration_minutes" name="duration_minutes" type="number" min="0" step="1"
                      class="form-control @error('duration_minutes') is-invalid @enderror"
                      value="{{ old('duration_minutes') }}">
                    @error('duration_minutes')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-md-6">
                    <label class="form-label" for="session_count">Session count</label>
                    <input id="session_count" name="session_count" type="number" min="0" step="1"
                      class="form-control @error('session_count') is-invalid @enderror"
                      value="{{ old('session_count') }}">
                    @error('session_count')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-12">
                    <label class="form-label required" for="image">Image</label>
                    <input id="image" name="image" type="file" accept="image/*"
                      class="form-control @error('image') is-invalid @enderror" required>
                    @error('image')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-12">
                    @include('admin.services.partials.icon-class-dropdown', [
                      'name' => 'icon_class',
                      'selected' => old('icon_class', ''),
                      'options' => \App\Models\Service::iconClassSelectOptions(),
                    ])
                  </div>

                  <div class="col-md-6">
                    <label class="form-label" for="recovery_time">Recovery time</label>
                    <input id="recovery_time" name="recovery_time" type="text"
                      class="form-control @error('recovery_time') is-invalid @enderror"
                      value="{{ old('recovery_time') }}" placeholder="e.g. 48 hours">
                    @error('recovery_time')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-md-6">
                    <label class="form-label" for="max_appointments_per_day">Max appointments per day</label>
                    <input id="max_appointments_per_day" name="max_appointments_per_day" type="number" min="0"
                      max="255" step="1" class="form-control @error('max_appointments_per_day') is-invalid @enderror"
                      value="{{ old('max_appointments_per_day') }}">
                    @error('max_appointments_per_day')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-md-6">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                      <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                      <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                    </select>
                    @error('status')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-md-3">
                    <label class="form-check mt-4 pt-2">
                      <input class="form-check-input" type="checkbox" name="is_featured" value="1"
                        @checked(old('is_featured'))>
                      <span class="form-check-label">Featured</span>
                    </label>
                  </div>

                  <div class="col-md-3">
                    <label class="form-check mt-4 pt-2">
                      <input class="form-check-input" type="checkbox" name="is_bookable" value="1"
                          @checked(old('is_bookable') === '1' || ! count(old()) > 0)>
                      <span class="form-check-label">Bookable</span>
                    </label>
                  </div>

                  <div class="col-12">
                    <span id="assigned-doctors-label" class="form-label d-block">Assigned doctors</span>
                    @if ($doctors->isEmpty())
                      <p class="text-secondary small mb-0">No doctors in the system yet.</p>
                    @else
                      <div
                        class="border rounded p-3 bg-secondary-lt row row-cols-1 row-cols-md-2 g-2 @error('assigned_doctors') border-danger @enderror"
                        role="group" aria-labelledby="assigned-doctors-label">
                        @foreach ($doctors as $doc)
                          <div class="col">
                            <label class="form-check mb-0">
                              <input type="checkbox" class="form-check-input" name="assigned_doctors[]"
                                value="{{ $doc->id }}" id="assigned-doctor-{{ $doc->id }}"
                                @checked(in_array((string) $doc->id, $oldDocs, true))>
                              <span class="form-check-label">{{ $doc->name }}</span>
                            </label>
                          </div>
                        @endforeach
                      </div>
                    @endif
                    <small class="form-hint">Optional — check all doctors who may deliver this service. If none are
                      checked, any doctor with an active schedule can be booked for this service (patient portal).</small>
                    @error('assigned_doctors')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-12">
                    <label class="form-label" for="before_care">Before care</label>
                    <textarea id="before_care" name="before_care" rows="3"
                      class="form-control @error('before_care') is-invalid @enderror">{{ old('before_care') }}</textarea>
                    @error('before_care')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-12">
                    <label class="form-label" for="after_care">After care</label>
                    <textarea id="after_care" name="after_care" rows="3"
                      class="form-control @error('after_care') is-invalid @enderror">{{ old('after_care') }}</textarea>
                    @error('after_care')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-12">
                    <label class="form-label" for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3"
                      class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                    @error('notes')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>

        <div class="col-lg-4">
          <div class="card mb-3">
            <div class="card-header">
              <h3 class="card-title">Checklist</h3>
            </div>
            <div class="card-body">
              <ul class="text-secondary mb-0 ps-3">
                <li>Required: name, short description, full description, price, image.</li>
                <li>Optional: choose a listing icon for home page cards (or use default rotation).</li>
                <li>Slug is filled from the name automatically.</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    (function () {
      function slugify(text) {
        return text
          .toString()
          .normalize('NFKD')
          .replace(/[\u0300-\u036f]/g, '')
          .trim()
          .toLowerCase()
          .replace(/[^a-z0-9]+/g, '-')
          .replace(/^-+|-+$/g, '');
      }

      var nameEl = document.getElementById('name');
      var slugEl = document.getElementById('slug');
      var slugHidden = document.getElementById('slug-hidden');
      if (!nameEl || !slugEl || !slugHidden) return;

      function syncSlug() {
        var s = slugify(nameEl.value || '');
        slugEl.value = s;
        slugHidden.value = s;
      }

      nameEl.addEventListener('input', syncSlug);
      nameEl.addEventListener('change', syncSlug);
      syncSlug();
    })();
  </script>
@endpush
