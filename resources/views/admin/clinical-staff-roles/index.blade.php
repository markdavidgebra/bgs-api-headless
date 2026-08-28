@extends('admin.layouts.master')

@section('content')
  @php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $roles */
  @endphp
  @if (session('status'))
    <div class="container-xl mb-3">
      <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    </div>
  @endif
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Clinical staff</div>
          <h2 class="page-title">Clinical roles</h2>
          <div class="text-secondary small mt-1">Roles for the doctor portal only. These are separate from admin Role Management.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.doctor-roles.create') }}" class="btn btn-primary">Add clinical role</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="card">
        <div class="card-body">
          <form method="GET" action="{{ route('admin.doctor-roles.index') }}" class="row g-3 align-items-end">
            <div class="col-md-6">
              <label class="form-label" for="search">Search</label>
              <input id="search" type="text" name="search" class="form-control" placeholder="Name or description" value="{{ request('search') }}">
            </div>
            <div class="col-auto">
              <button class="btn btn-primary" type="submit">Search</button>
            </div>
            @if (request()->filled('search'))
              <div class="col-auto">
                <a class="btn" href="{{ route('admin.doctor-roles.index') }}">Clear</a>
              </div>
            @endif
          </form>
          @if (request()->filled('search'))
            <div class="alert alert-info mt-3 mb-0" role="alert">
              Showing {{ number_format($roles->total()) }} result(s) for "<strong>{{ request('search') }}</strong>".
            </div>
          @endif
        </div>
        <div class="table-responsive">
          <table class="table table-vcenter card-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Role value</th>
                <th>Permissions</th>
                <th>Description</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody>
              @forelse ($roles as $role)
                <tr>
                  <td class="fw-medium">{{ $role->name }}</td>
                  <td><code>{{ $role->role_value }}</code></td>
                  <td>{{ is_array($role->permissions) ? count($role->permissions) : 0 }}</td>
                  <td class="text-secondary">{{ $role->description ?: '—' }}</td>
                  <td>
                    <a href="{{ route('admin.doctor-roles.edit', $role->id) }}" class="btn btn-sm">Edit</a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center text-secondary py-4">No clinical roles found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          {{ $roles->links() }}
        </div>
      </div>
    </div>
  </div>
@endsection
