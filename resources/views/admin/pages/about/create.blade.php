@extends('admin.layouts.master')

@section('content')
  @php($clinicHours = old('meta.clinic_hours', []))
  @php($storyPoints = old('meta.story_points', ['']))
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col">
          <div class="page-pretitle text-secondary">Pages</div>
          <h2 class="page-title mb-0">Add About Content</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.abouts') }}" class="btn">Cancel</a>
            <button type="submit" form="about-create-form" class="btn btn-primary">Save</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <form id="about-create-form" action="{{ route('admin.abouts.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card">
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label required" for="title">Title</label>
                <input id="title" name="title" type="text" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label" for="subtitle">Subtitle</label>
                <input id="subtitle" name="subtitle" type="text" class="form-control @error('subtitle') is-invalid @enderror" value="{{ old('subtitle') }}">
                @error('subtitle')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-12">
                <label class="form-label required" for="content">Content</label>
                <textarea id="content" name="content" rows="8" class="form-control @error('content') is-invalid @enderror" required>{{ old('content') }}</textarea>
                @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-12">
                <hr class="my-2">
                <h3 class="card-title mb-1">About Story Section</h3>
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
              <div class="col-md-6">
                <label class="form-label" for="meta_clinic_hours_title">Clinic hours title</label>
                <input id="meta_clinic_hours_title" name="meta[clinic_hours_title]" type="text" class="form-control @error('meta.clinic_hours_title') is-invalid @enderror" value="{{ old('meta.clinic_hours_title') }}">
              </div>
              <div class="col-12">
                <label class="form-label">Clinic hours (day and time)</label>
                <div class="row g-2">
                  @for ($i = 0; $i < 7; $i++)
                    <div class="col-md-6">
                      <input type="text" name="meta[clinic_hours][{{ $i }}][day]" class="form-control @error("meta.clinic_hours.$i.day") is-invalid @enderror" placeholder="Day (e.g. Monday - Friday)" value="{{ data_get($clinicHours, "$i.day") }}">
                    </div>
                    <div class="col-md-6">
                      <input type="text" name="meta[clinic_hours][{{ $i }}][time]" class="form-control @error("meta.clinic_hours.$i.time") is-invalid @enderror" placeholder="Time (e.g. 8:00 AM - 7:00 PM)" value="{{ data_get($clinicHours, "$i.time") }}">
                    </div>
                  @endfor
                </div>
              </div>
              <div class="col-12">
                <label class="form-label" for="meta_home_bottom_text">Home about bottom text</label>
                <textarea id="meta_home_bottom_text" name="meta[home_bottom_text]" rows="2" class="form-control @error('meta.home_bottom_text') is-invalid @enderror"
                  placeholder="Text shown under list points in home about section">{{ old('meta.home_bottom_text') }}</textarea>
                @error('meta.home_bottom_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-md-4">
                <label class="form-label" for="sort_order">Order</label>
                <input id="sort_order" name="sort_order" type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', 0) }}">
              </div>
              <div class="col-md-4">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                  <option value="published" @selected(old('status', 'published') === 'published')>Published</option>
                  <option value="draft" @selected(old('status') === 'draft')>Draft</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label" for="image">Image</label>
                <input id="image" name="image" type="file" accept="image/*" class="form-control @error('image') is-invalid @enderror">
                @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
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
    const list = document.getElementById('story-points-list');
    const addBtn = document.getElementById('story-point-add');

    function createRow(value = '') {
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

    addBtn?.addEventListener('click', function () {
      list?.appendChild(createRow());
    });

    list?.addEventListener('click', function (event) {
      if (!event.target.classList.contains('story-point-remove')) {
        return;
      }

      const rows = list.querySelectorAll('.story-point-row');
      if (rows.length <= 1) {
        rows[0].querySelector('input').value = '';
        return;
      }
      event.target.closest('.story-point-row')?.remove();
    });
  });
</script>
@endpush
