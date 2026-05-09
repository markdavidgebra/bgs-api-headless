@extends('admin.layouts.master')

@section('content')
  @php
    /** @var \App\Models\Admin $admin */
    $initial = strtoupper(substr($admin->name ?? 'A', 0, 1));
    $imageUrl = $admin->image_url;
  @endphp

  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Account</div>
          <h2 class="page-title">My profile</h2>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      @if (session('status') === 'profile-updated')
        <div class="alert alert-success">Profile updated successfully.</div>
      @endif

      @if (session('status') === 'password-updated')
        <div class="alert alert-success">Password updated successfully.</div>
      @endif

      @if ($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="row g-3">
        <div class="col-12 col-lg-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Profile information</h3>
            </div>
            <div class="card-body">
              <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="d-flex align-items-center mb-4">
                  <label for="photo" class="me-3 mb-0" title="Click to change profile image" style="cursor: pointer;">
                    @if ($imageUrl)
                      <span class="avatar avatar-lg rounded" style="background-image: url('{{ $imageUrl }}')"></span>
                    @else
                      <span class="avatar avatar-lg rounded bg-azure-lt text-azure">{{ $initial }}</span>
                    @endif
                  </label>
                  <div>
                    <div class="fw-medium">{{ $admin->name }}</div>
                    <div class="text-secondary small mb-1">{{ ucfirst((string) ($admin->role ?? 'administrator')) }}</div>
                    <label for="photo" class="btn btn-sm btn-outline-primary mb-0">Change photo</label>
                    <div class="form-hint">Accepted image file under 500KB.</div>
                  </div>
                </div>

                <input id="photo" type="file" name="photo" class="d-none" accept="image/*">
                @error('photo')
                  <div class="text-danger small mb-3">{{ $message }}</div>
                @enderror

                <div class="mb-3">
                  <label class="form-label" for="name">Full name</label>
                  <input id="name" type="text" name="name" class="form-control" value="{{ old('name', $admin->name) }}"
                    required autocomplete="name">
                </div>

                <div class="mb-3">
                  <label class="form-label" for="email">Email</label>
                  <input id="email" type="email" name="email" class="form-control"
                    value="{{ old('email', $admin->email) }}" required autocomplete="email">
                </div>

                <div class="mb-4">
                  <label class="form-label">Role</label>
                  <div class="form-control-plaintext">{{ ucfirst((string) ($admin->role ?? 'administrator')) }}</div>
                  <div class="form-hint">Role updates are managed from admin records.</div>
                </div>

                <button type="submit" class="btn btn-primary">Save profile</button>
              </form>
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Change password</h3>
            </div>
            <div class="card-body">
              <form method="POST" action="{{ route('admin.password.update') }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                  <label class="form-label" for="current_password">Current password</label>
                  <input id="current_password" type="password" name="current_password" class="form-control" required
                    autocomplete="current-password">
                  @error('current_password', 'updatePassword')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                  @enderror
                </div>

                <div class="mb-3">
                  <label class="form-label" for="password">New password</label>
                  <input id="password" type="password" name="password" class="form-control" required
                    autocomplete="new-password">
                  @error('password', 'updatePassword')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                  @enderror
                </div>

                <div class="mb-4">
                  <label class="form-label" for="password_confirmation">Confirm new password</label>
                  <input id="password_confirmation" type="password" name="password_confirmation" class="form-control"
                    required autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-outline-primary">Update password</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
