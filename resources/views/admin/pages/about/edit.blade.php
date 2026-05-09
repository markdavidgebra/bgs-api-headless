@extends('admin.layouts.master')

@section('content')
  {{-- ============================= --}}
  {{-- Data preparation --}}
  {{-- ============================= --}}
  @php($meta = optional($about)->meta ?? [])
  {{-- Clinic hours --}}
  @php($clinicHours = old('meta.clinic_hours', data_get($meta, 'clinic_hours', [])))
  {{-- Story points --}}
  @php($storyPoints = old('meta.story_points', data_get($meta, 'story_points', [])))
  @if (!is_array($storyPoints) || count($storyPoints) === 0)
    @php($storyPoints = [data_get($meta, 'story_point_1'), data_get($meta, 'story_point_2'), data_get($meta, 'story_point_3')])
  @endif
  @php($storyPoints = collect($storyPoints)->filter(fn ($point) => filled($point))->values()->all())
  @if (count($storyPoints) === 0)
    @php($storyPoints = [''])
  @endif
  {{-- Feature boxes --}}
  @php($features = old('meta.features', data_get($meta, 'features', [])))
  @if (!is_array($features) || count($features) === 0)
    @php($features = [
      ['title' => data_get($meta, 'feature_1_title'), 'text' => data_get($meta, 'feature_1_text')],
      ['title' => data_get($meta, 'feature_2_title'), 'text' => data_get($meta, 'feature_2_text')],
    ])
  @endif
  @php($features = collect($features)->map(function ($row) {
    return [
      'icon' => trim((string) data_get($row, 'icon', '')),
      'title' => trim((string) data_get($row, 'title', '')),
      'text' => trim((string) data_get($row, 'text', '')),
    ];
  })->filter(fn ($row) => $row['icon'] !== '' || $row['title'] !== '' || $row['text'] !== '')->values()->all())
  @if (count($features) === 0)
    @php($features = [['icon' => '', 'title' => '', 'text' => '']])
  @endif
  {{-- List points --}}
  @php($listPoints = old('meta.list_points', data_get($meta, 'list_points', [])))
  @if (!is_array($listPoints) || count($listPoints) === 0)
    @php($listPoints = [
      data_get($meta, 'list_point_1'),
      data_get($meta, 'list_point_2'),
      data_get($meta, 'list_point_3'),
      data_get($meta, 'list_point_4'),
    ])
  @endif
  @php($listPoints = collect($listPoints)->map(fn ($point) => trim((string) $point))->filter(fn ($point) => $point !== '')->values()->all())
  @if (count($listPoints) === 0)
    @php($listPoints = [''])
  @endif
  {{-- Feature icon picker options --}}
  @php($featureIconOptions = [
    'icon-plaster' => '🩹',
    'icon-medicine-2-2' => '💊',
    'icon-broken-bone' => '🦴',
    'icon-doctor' => '🧑‍⚕️',
    'icon-stethoscope' => '🩺',
    'icon-health-check' => '✅',
    'icon-medical-kit' => '🧰',
    'icon-heartbeat' => '❤️',
  ])

  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col">
          <div class="page-pretitle text-secondary">Pages</div>
          <h2 class="page-title mb-0">Edit About Content</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.abouts') }}" class="btn">Cancel</a>
            <button type="submit" form="about-edit-form" class="btn btn-primary">Save changes</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <form id="about-edit-form" action="{{ route('admin.abouts.update', optional($about)->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card">
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label required" for="title">Title</label>
                <input id="title" name="title" type="text" class="form-control @error('title') is-invalid @enderror"
                  value="{{ old('title', optional($about)->title) }}" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label" for="subtitle">Subtitle</label>
                <input id="subtitle" name="subtitle" type="text" class="form-control @error('subtitle') is-invalid @enderror"
                  value="{{ old('subtitle', optional($about)->subtitle) }}">
                @error('subtitle')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-12">
                <label class="form-label required" for="content">Content</label>
                <textarea id="content" name="content" rows="7" class="form-control @error('content') is-invalid @enderror" required>{{ old('content', optional($about)->content) }}</textarea>
                @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-12">
                <hr class="my-2">
                <h3 class="card-title mb-1">Home About Section</h3>
                <div class="text-secondary small">Controls frontend About blocks (home + about page).</div>
              </div>

              <div class="col-12">
                <label class="form-label">Story points</label>
                <div id="story-points-list" class="d-flex flex-column gap-2">
                  @foreach ($storyPoints as $point)
                    <div class="input-group story-point-row">
                      <span class="input-group-text">+</span>
                      <input type="text" name="meta[story_points][]" class="form-control @error('meta.story_points.*') is-invalid @enderror"
                        placeholder="Enter story point" value="{{ $point }}">
                      <button type="button" class="btn btn-outline-danger story-point-remove">Remove</button>
                    </div>
                  @endforeach
                </div>
                <button type="button" id="story-point-add" class="btn btn-outline-primary btn-sm mt-2">+ Add point</button>
                @error('meta.story_points.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>

              <div class="col-12">
                <label class="form-label">Feature boxes</label>
                <div id="features-list" class="d-flex flex-column gap-2">
                  @foreach ($features as $index => $feature)
                    <div class="border rounded p-2 feature-row">
                      <div class="d-flex justify-content-end mb-2">
                        <button type="button" class="btn btn-outline-danger btn-sm feature-remove">Remove</button>
                      </div>
                      <div class="row g-2">
                        <div class="col-md-4">
                          <select name="meta[features][{{ $index }}][icon]" class="form-select feature-icon-input @error('meta.features.*.icon') is-invalid @enderror">
                            <option value="">⬇️</option>
                            @foreach ($featureIconOptions as $iconClass => $iconLabel)
                              <option value="{{ $iconClass }}" @selected(data_get($feature, 'icon', '') === $iconClass)>{{ $iconLabel }}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="col-md-6">
                          <input type="text" name="meta[features][{{ $index }}][title]" class="form-control feature-title-input @error('meta.features.*.title') is-invalid @enderror"
                            placeholder="Feature title" value="{{ data_get($feature, 'title', '') }}">
                        </div>
                        <div class="col-md-12">
                          <textarea name="meta[features][{{ $index }}][text]" rows="2" class="form-control feature-text-input @error('meta.features.*.text') is-invalid @enderror"
                            placeholder="Feature text">{{ data_get($feature, 'text', '') }}</textarea>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
                <button type="button" id="feature-add" class="btn btn-outline-primary btn-sm mt-2">+ Add feature</button>
                @error('meta.features.*.icon')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('meta.features.*.title')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('meta.features.*.text')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>

              <div class="col-12">
                <label class="form-label">List points</label>
                <div id="list-points-list" class="d-flex flex-column gap-2">
                  @foreach ($listPoints as $point)
                    <div class="input-group list-point-row">
                      <span class="input-group-text">+</span>
                      <input type="text" name="meta[list_points][]" class="form-control list-point-input @error('meta.list_points.*') is-invalid @enderror"
                        placeholder="Enter list point" value="{{ $point }}">
                      <button type="button" class="btn btn-outline-danger list-point-remove">Remove</button>
                    </div>
                  @endforeach
                </div>
                <button type="button" id="list-point-add" class="btn btn-outline-primary btn-sm mt-2">+ Add list point</button>
                @error('meta.list_points.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>

              <div class="col-md-6">
                <label class="form-label" for="meta_button_text">Button text</label>
                <input id="meta_button_text" name="meta[button_text]" type="text" class="form-control"
                  value="{{ old('meta.button_text', data_get($meta, 'button_text')) }}">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="meta_button_url">Button URL</label>
                <input id="meta_button_url" name="meta[button_url]" type="text" class="form-control"
                  value="{{ old('meta.button_url', data_get($meta, 'button_url')) }}">
              </div>
              <div class="col-12">
                <label class="form-label" for="meta_home_bottom_text">Home about bottom text</label>
                <textarea id="meta_home_bottom_text" name="meta[home_bottom_text]" rows="2" class="form-control @error('meta.home_bottom_text') is-invalid @enderror"
                  placeholder="Text shown under list points in home about section">{{ old('meta.home_bottom_text', data_get($meta, 'home_bottom_text')) }}</textarea>
                @error('meta.home_bottom_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-md-6">
                <label class="form-label" for="meta_clinic_hours_title">Clinic hours title</label>
                <input id="meta_clinic_hours_title" name="meta[clinic_hours_title]" type="text" class="form-control"
                  value="{{ old('meta.clinic_hours_title', data_get($meta, 'clinic_hours_title')) }}">
              </div>
              <div class="col-12">
                <label class="form-label">Clinic hours list</label>
                <div class="row g-2">
                  @for ($i = 0; $i < 7; $i++)
                    <div class="col-md-6">
                      <input type="text" name="meta[clinic_hours][{{ $i }}][day]" class="form-control"
                        placeholder="Day (e.g. Monday - Friday)" value="{{ data_get($clinicHours, "$i.day") }}">
                    </div>
                    <div class="col-md-6">
                      <input type="text" name="meta[clinic_hours][{{ $i }}][time]" class="form-control"
                        placeholder="Time (e.g. 8:00 AM - 7:00 PM)" value="{{ data_get($clinicHours, "$i.time") }}">
                    </div>
                  @endfor
                </div>
              </div>

              <div class="col-md-4">
                <label class="form-label" for="sort_order">Order</label>
                <input id="sort_order" name="sort_order" type="number" min="0" class="form-control"
                  value="{{ old('sort_order', optional($about)->sort_order) }}">
              </div>
              <div class="col-md-4">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-select">
                  <option value="published" @selected(old('status', optional($about)->status) === 'published')>Published</option>
                  <option value="draft" @selected(old('status', optional($about)->status) === 'draft')>Draft</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label" for="image">Main image</label>
                <input id="image" name="image" type="file" accept="image/*" class="form-control">
              </div>
              <div class="col-md-4">
                <label class="form-label" for="secondary_image">Secondary image</label>
                <input id="secondary_image" name="secondary_image" type="file" accept="image/*" class="form-control">
              </div>

              @if (optional($about)->image_url)
                <div class="col-12">
                  <div class="text-secondary small mb-1">Current main image</div>
                  <img src="{{ optional($about)->image_url }}" alt="{{ optional($about)->title }}" class="img-fluid rounded" style="max-width: 260px;">
                </div>
              @endif
              @if (filled(data_get($meta, 'secondary_image')))
                <div class="col-12">
                  <div class="text-secondary small mb-1">Current secondary image</div>
                  <img src="{{ asset(data_get($meta, 'secondary_image')) }}" alt="Secondary image" class="img-fluid rounded" style="max-width: 220px;">
                </div>
              @endif
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // =============================
    // DOM references
    // =============================
    const list = document.getElementById('story-points-list');
    const addBtn = document.getElementById('story-point-add');
    const featuresList = document.getElementById('features-list');
    const featureAddBtn = document.getElementById('feature-add');
    const listPointsList = document.getElementById('list-points-list');
    const listPointAddBtn = document.getElementById('list-point-add');

    // =============================
    // Story points
    // =============================
    function createStoryPointRow(value = '') {
      const row = document.createElement('div');
      row.className = 'input-group story-point-row';
      row.innerHTML = `
        <span class="input-group-text">+</span>
        <input type="text" name="meta[story_points][]" class="form-control" placeholder="Enter story point">
        <button type="button" class="btn btn-outline-danger story-point-remove">Remove</button>
      `;
      row.querySelector('input').value = value;
      return row;
    }

    function bindStoryPoints() {
      if (addBtn && list) {
        addBtn.addEventListener('click', function () {
          list.appendChild(createStoryPointRow());
        });
      }

      if (!list) return;
      list.addEventListener('click', function (event) {
        const removeBtn = event.target.closest('.story-point-remove');
        if (!removeBtn) return;

        const rows = list.querySelectorAll('.story-point-row');
        if (rows.length <= 1) {
          const firstInput = rows[0] ? rows[0].querySelector('input') : null;
          if (firstInput) firstInput.value = '';
          return;
        }
        const row = removeBtn.closest('.story-point-row');
        if (row) row.remove();
      });
    }

    // =============================
    // Feature boxes
    // =============================
    function reindexFeatures() {
      if (!featuresList) return;
      const rows = featuresList.querySelectorAll('.feature-row');
      rows.forEach(function (row, index) {
        const iconInput = row.querySelector('.feature-icon-input');
        const titleInput = row.querySelector('.feature-title-input');
        const textInput = row.querySelector('.feature-text-input');
        if (iconInput) {
          iconInput.name = `meta[features][${index}][icon]`;
        }
        if (titleInput) {
          titleInput.name = `meta[features][${index}][title]`;
        }
        if (textInput) {
          textInput.name = `meta[features][${index}][text]`;
        }
      });
    }

    function createFeatureRow() {
      const row = document.createElement('div');
      row.className = 'border rounded p-2 feature-row';
      row.innerHTML = `
        <div class="d-flex justify-content-end mb-2">
          <button type="button" class="btn btn-outline-danger btn-sm feature-remove">Remove</button>
        </div>
        <div class="row g-2">
          <div class="col-md-4">
            <select class="form-select feature-icon-input">
              <option value="">⬇️</option>
              <option value="icon-plaster">🩹</option>
              <option value="icon-medicine-2-2">💊</option>
              <option value="icon-broken-bone">🦴</option>
              <option value="icon-doctor">🧑‍⚕️</option>
              <option value="icon-stethoscope">🩺</option>
              <option value="icon-health-check">✅</option>
              <option value="icon-medical-kit">🧰</option>
              <option value="icon-heartbeat">❤️</option>
            </select>
          </div>
          <div class="col-md-6">
            <input type="text" class="form-control feature-title-input" placeholder="Feature title">
          </div>
          <div class="col-md-12">
            <textarea rows="2" class="form-control feature-text-input" placeholder="Feature text"></textarea>
          </div>
        </div>
      `;
      return row;
    }

    function bindFeatures() {
      if (featureAddBtn && featuresList) {
        featureAddBtn.addEventListener('click', function () {
          featuresList.appendChild(createFeatureRow());
          reindexFeatures();
        });
      }

      if (!featuresList) return;
      featuresList.addEventListener('click', function (event) {
        const removeBtn = event.target.closest('.feature-remove');
        if (!removeBtn) return;
        const rows = featuresList.querySelectorAll('.feature-row');
        if (rows.length <= 1) {
          const firstRow = rows[0];
          if (firstRow) {
            const firstInput = firstRow.querySelector('input');
            const firstTextarea = firstRow.querySelector('textarea');
            if (firstInput) firstInput.value = '';
            if (firstTextarea) firstTextarea.value = '';
          }
          reindexFeatures();
          return;
        }
        const row = removeBtn.closest('.feature-row');
        if (row) row.remove();
        reindexFeatures();
      });
    }

    // =============================
    // List points
    // =============================
    function createListPointRow() {
      const row = document.createElement('div');
      row.className = 'input-group list-point-row';
      row.innerHTML = `
        <span class="input-group-text">+</span>
        <input type="text" name="meta[list_points][]" class="form-control list-point-input" placeholder="Enter list point">
        <button type="button" class="btn btn-outline-danger list-point-remove">Remove</button>
      `;
      return row;
    }

    function bindListPoints() {
      if (listPointAddBtn && listPointsList) {
        listPointAddBtn.addEventListener('click', function () {
          listPointsList.appendChild(createListPointRow());
        });
      }

      if (!listPointsList) return;
      listPointsList.addEventListener('click', function (event) {
        const removeBtn = event.target.closest('.list-point-remove');
        if (!removeBtn) return;
        const rows = listPointsList.querySelectorAll('.list-point-row');
        if (rows.length <= 1) {
          const firstInput = rows[0] ? rows[0].querySelector('.list-point-input') : null;
          if (firstInput) firstInput.value = '';
          return;
        }
        const row = removeBtn.closest('.list-point-row');
        if (row) row.remove();
      });
    }

    // Initialize grouped handlers
    bindStoryPoints();
    bindFeatures();
    bindListPoints();
    reindexFeatures();
  });
</script>
@endpush
