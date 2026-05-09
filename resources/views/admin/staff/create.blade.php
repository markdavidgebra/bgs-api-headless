@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Admin</div>
          <h2 class="page-title">Add Staff</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.staffs') }}" class="btn">Cancel</a>
            <button type="submit" form="staff-create-form" class="btn btn-primary">Save staff</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <form id="staff-create-form" method="POST" action="{{ route('admin.staffs.store') }}">
        @csrf
        <div class="card">
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label required" for="name">Name</label>
                <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label required" for="email">Email</label>
                <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label required" for="role">Role</label>
                <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                  <option value="" disabled @selected(old('role') === null)>Select role</option>
                  @foreach ($roleOptions as $roleOption)
                    <option value="{{ $roleOption }}" @selected(old('role') === $roleOption)>{{ \Illuminate\Support\Str::headline($roleOption) }}</option>
                  @endforeach
                </select>
                @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label required" for="password">Password</label>
                <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
@endsection
