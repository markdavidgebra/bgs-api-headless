@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col">
          <div class="page-pretitle text-secondary">Pages</div>
          <h2 class="page-title mb-0">Blog posts</h2>
          <div class="text-secondary small mt-1">Manage posts that appear on the frontend blog page.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary">New blog post</a>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="card">
        <div class="table-responsive">
          <table class="table table-vcenter card-table">
            <thead>
              <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Author</th>
                <th>Status</th>
                <th>Published</th>
                <th class="w-1">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($blogs as $blog)
                <tr>
                  <td>
                    <div class="fw-medium">{{ $blog->title }}</div>
                    <div class="text-secondary small">/{{ $blog->slug }}</div>
                  </td>
                  <td>{{ $blog->category ?: '—' }}</td>
                  <td>{{ $blog->author_name ?: 'Admin' }}</td>
                  <td>
                    <span class="badge {{ $blog->status === 'published' ? 'bg-green-lt text-green' : 'bg-secondary-lt text-secondary' }}">
                      {{ ucfirst($blog->status) }}
                    </span>
                  </td>
                  <td>{{ $blog->published_at?->format('M d, Y H:i') ?? '—' }}</td>
                  <td>
                    <div class="btn-list flex-nowrap">
                      <a href="{{ route('admin.blogs.show', $blog->id) }}" class="btn btn-sm btn-primary">View</a>
                      <a href="{{ route('admin.blogs.edit', $blog->id) }}" class="btn btn-sm">Edit</a>
                      <form method="POST" action="{{ route('admin.blogs.destroy', $blog->id) }}"
                        onsubmit="return confirm('Delete this blog post?');" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center text-secondary py-5">No blog posts yet.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($blogs->hasPages())
          <div class="card-footer">
            {{ $blogs->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection
