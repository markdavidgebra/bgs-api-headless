@extends('admin.layouts.master')

@section('content')
  @php($meta = is_array($about->meta ?? null) ? $about->meta : [])
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col">
          <div class="page-pretitle text-secondary">Pages</div>
          <h2 class="page-title mb-0">About</h2>
          <div class="text-secondary small mt-1">Manage the current About page content displayed on the frontend.</div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      @if ($about)
        <div class="card">
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-8">
                <div class="text-secondary small">Current about content</div>
                <h3 class="mt-1 mb-2">{{ $about->title }}</h3>
                <div class="text-secondary mb-2">{{ $about->subtitle ?: '—' }}</div>
                <p class="mb-3">{{ $about->content ?: '—' }}</p>

                <div class="row g-2">
                  <div class="col-md-6">
                    <div class="border rounded p-2 h-100">
                      <div class="text-secondary small mb-1">Story points</div>
                      <ul class="mb-0 ps-3">
                        <li>{{ data_get($meta, 'story_point_1') ?: '—' }}</li>
                        <li>{{ data_get($meta, 'story_point_2') ?: '—' }}</li>
                        <li>{{ data_get($meta, 'story_point_3') ?: '—' }}</li>
                      </ul>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="border rounded p-2 h-100">
                      <div class="text-secondary small mb-1">Feature boxes</div>
                      <div><strong>{{ data_get($meta, 'feature_1_title') ?: '—' }}</strong></div>
                      <div class="small text-secondary mb-2">{{ data_get($meta, 'feature_1_text') ?: '—' }}</div>
                      <div><strong>{{ data_get($meta, 'feature_2_title') ?: '—' }}</strong></div>
                      <div class="small text-secondary">{{ data_get($meta, 'feature_2_text') ?: '—' }}</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="border rounded p-2 h-100">
                      <div class="text-secondary small mb-1">List points</div>
                      <ul class="mb-0 ps-3">
                        <li>{{ data_get($meta, 'list_point_1') ?: '—' }}</li>
                        <li>{{ data_get($meta, 'list_point_2') ?: '—' }}</li>
                        <li>{{ data_get($meta, 'list_point_3') ?: '—' }}</li>
                        <li>{{ data_get($meta, 'list_point_4') ?: '—' }}</li>
                      </ul>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="border rounded p-2 h-100">
                      <div class="text-secondary small mb-1">Button</div>
                      <div><strong>Text:</strong> {{ data_get($meta, 'button_text') ?: '—' }}</div>
                      <div class="small text-secondary"><strong>URL:</strong> {{ data_get($meta, 'button_url') ?: '—' }}</div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                @if ($about->image_url)
                  <div class="text-secondary small mb-1">Main image</div>
                  <img src="{{ $about->image_url }}" alt="{{ $about->title }}" class="img-fluid rounded mb-3">
                @endif
                @if (filled(data_get($meta, 'secondary_image')))
                  <div class="text-secondary small mb-1">Secondary image</div>
                  <img src="{{ asset(data_get($meta, 'secondary_image')) }}" alt="Secondary image" class="img-fluid rounded mb-3">
                @endif
                <div class="mb-2"><strong>Order:</strong> {{ $about->sort_order }}</div>
                <div class="mb-3">
                  <strong>Status:</strong>
                  <span class="badge {{ $about->status === 'published' ? 'bg-green-lt text-green' : 'bg-secondary-lt text-secondary' }}">
                    {{ ucfirst($about->status) }}
                  </span>
                </div>
                <div class="btn-list">
                  <a href="{{ route('admin.abouts.edit', $about->id) }}" class="btn btn-primary">Edit</a>
                  <form method="POST" action="{{ route('admin.abouts.destroy', $about->id) }}" class="m-0"
                    onsubmit="return confirm('Delete this about content?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      @else
        <div class="card">
          <div class="card-body text-center py-5">
            <h3 class="mb-2">No About content yet</h3>
            <p class="text-secondary mb-3">Create your first About content to show it on the frontend page.</p>
            <a href="{{ route('admin.abouts.create') }}" class="btn btn-primary">Add about content</a>
          </div>
        </div>
      @endif
    </div>
  </div>
@endsection
