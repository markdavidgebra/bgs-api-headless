@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Website</div>
          <h2 class="page-title">Homepage slides</h2>
          <div class="text-secondary small mt-1">These appear in order on your homepage hero. Use the arrows to change order—no numbers to edit.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <a href="{{ route('admin.slides.create') }}" class="btn btn-primary">New slide</a>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      @if (session('status'))
        <div class="alert alert-success alert-dismissible" role="alert">
          {{ session('status') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      <div class="card">
        <div class="table-responsive">
          <table class="table table-vcenter card-table">
            <thead>
              <tr>
                <th class="w-1 text-center">Order</th>
                <th>Preview</th>
                <th>Subtitle / title</th>
                <th>Active</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody>
              @forelse ($slides as $slide)
                <tr>
                  <td class="text-center text-nowrap">
                    <form action="{{ route('admin.slides.move-up', $slide) }}" method="POST" class="d-inline">
                      @csrf
                      <button type="submit" class="btn btn-ghost-secondary btn-icon btn-sm" title="Move up"
                        @disabled($loop->first) aria-label="Move up">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M18 11l-6 -6l-6 6" /></svg>
                      </button>
                    </form>
                    <form action="{{ route('admin.slides.move-down', $slide) }}" method="POST" class="d-inline">
                      @csrf
                      <button type="submit" class="btn btn-ghost-secondary btn-icon btn-sm" title="Move down"
                        @disabled($loop->last) aria-label="Move down">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M18 13l-6 6l-6 -6" /></svg>
                      </button>
                    </form>
                  </td>
                  <td>
                    <span class="avatar avatar-md rounded"
                      style="background-image: url('{{ $slide->image_url }}')"></span>
                  </td>
                  <td>
                    <div class="text-secondary small">{{ $slide->subtitle ?: '—' }}</div>
                    <div class="text-truncate" style="max-width: 28rem;">
                      {{ \Illuminate\Support\Str::limit(strip_tags($slide->title), 80) }}
                    </div>
                  </td>
                  <td>
                    @if ($slide->is_active)
                      <span class="badge bg-green-lt">Yes</span>
                    @else
                      <span class="badge bg-secondary-lt">No</span>
                    @endif
                  </td>
                  <td class="text-nowrap">
                    <a href="{{ route('admin.slides.edit', $slide) }}" class="btn btn-sm btn-ghost-primary">Edit</a>
                    <form action="{{ route('admin.slides.destroy', $slide) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Remove this slide?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-ghost-danger">Delete</button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-secondary text-center py-4">No slides yet. Create one or run
                    <code>SlideSeeder</code>.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection
