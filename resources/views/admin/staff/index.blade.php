@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Admin</div>
          <h2 class="page-title">Staff</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.staffs.create') }}" class="btn btn-primary">Add staff</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="card">
        <div class="card-body">
          <form method="GET" action="{{ route('admin.staffs') }}" class="row g-3 align-items-end">
            <div class="col-md-6">
              <label class="form-label" for="search">Search staff</label>
              <input id="search" type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Name, email, role">
            </div>
            <div class="col-auto">
              <button type="submit" class="btn btn-primary">Search</button>
            </div>
            @if (request()->filled('search'))
              <div class="col-auto">
                <a href="{{ route('admin.staffs') }}" class="btn">Clear</a>
              </div>
            @endif
          </form>
        </div>

        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th>Staff</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody>
              @forelse ($staffs as $staff)
                <tr>
                  <td class="fw-medium">{{ $staff->name }}</td>
                  <td>{{ $staff->email }}</td>
                  <td><span class="badge bg-azure-lt">{{ $staff->role ?: '—' }}</span></td>
                  <td>
                    @php $staffStatus = strtolower((string) ($staff->status ?? 'draft')); @endphp
                    <form method="POST" action="{{ route('admin.staffs.status', $staff->id) }}">
                      @csrf
                      <select
                        name="status"
                        class="form-select form-select-sm"
                        onchange="this.form.submit()"
                        aria-label="Update staff status"
                      >
                        <option value="draft" @selected($staffStatus === 'draft')>Draft</option>
                        <option value="approved" @selected($staffStatus === 'approved')>Approved</option>
                        <option value="disapproved" @selected($staffStatus === 'disapproved')>Disapproved</option>
                      </select>
                    </form>
                  </td>
                  <td>
                    <div class="btn-list flex-nowrap">
                      <a href="{{ route('admin.staffs.show', $staff->id) }}" class="btn btn-sm btn-primary">View</a>
                      <a href="{{ route('admin.staffs.edit', $staff->id) }}" class="btn btn-sm">Edit</a>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-secondary text-center py-4">No staff found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if ($staffs instanceof \Illuminate\Pagination\LengthAwarePaginator && $staffs->hasPages())
          <div class="card-footer">
            {{ $staffs->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection
