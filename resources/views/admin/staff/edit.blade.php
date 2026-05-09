@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Admin</div>
          <h2 class="page-title">Edit Staff</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.staffs.show', $staff->id) }}" class="btn">Cancel</a>
            <button type="submit" form="staff-edit-form" class="btn btn-primary">Save changes</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <form id="staff-edit-form" method="POST" action="{{ route('admin.staffs.update', $staff->id) }}">
        @csrf
        @method('PUT')
        <div class="card">
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label required" for="name">Name</label>
                <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $staff->name) }}" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label required" for="email">Email</label>
                <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $staff->email) }}" required>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label required" for="role">Role</label>
                <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                  <option value="" disabled>Select role</option>
                  @foreach ($roleOptions as $roleOption)
                    <option value="{{ $roleOption }}" @selected(old('role', $staff->role) === $roleOption)>{{ \Illuminate\Support\Str::headline($roleOption) }}</option>
                  @endforeach
                </select>
                @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label" for="password">New Password (optional)</label>
                <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                <small class="form-hint">Leave blank if you do not want to change password.</small>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
@endsection
