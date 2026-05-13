@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Clinic</div>
          <h2 class="page-title">Add patient</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.patients') }}" class="btn">Cancel</a>
            <button type="submit" form="patient-create-form" class="btn btn-primary">Create patient</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <form id="patient-create-form" method="POST" action="{{ route('admin.patients.store') }}">
        @csrf
        <div class="card">
          <div class="card-body">
            @if ($canManageStatus)
              <p class="text-secondary small mb-3">
                {{ __('Active patients receive login details by email. Pending patients cannot sign in until you approve them from the patient list.') }}
              </p>
            @endif
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label required" for="name">Name</label>
                <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label required" for="email">Email</label>
                <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="off">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label required" for="password">Password</label>
                <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label required" for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="phone">Phone</label>
                <input id="phone" type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label" for="birthdate">Birthdate</label>
                <input id="birthdate" type="date" name="birthdate" class="form-control @error('birthdate') is-invalid @enderror" value="{{ old('birthdate') }}">
                @error('birthdate') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label" for="gender">Gender</label>
                <select id="gender" name="gender" class="form-select @error('gender') is-invalid @enderror">
                  <option value="">Select gender</option>
                  <option value="male" @selected(old('gender') === 'male')>Male</option>
                  <option value="female" @selected(old('gender') === 'female')>Female</option>
                  <option value="other" @selected(old('gender') === 'other')>Other</option>
                </select>
                @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              @if ($canManageStatus)
                <div class="col-md-6">
                  <label class="form-label required" for="status">Status</label>
                  <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                    <option value="pending" @selected(old('status') === 'pending')>Pending</option>
                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                  </select>
                  @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              @endif
              <div class="col-12">
                <label class="form-label" for="address">Address</label>
                <input id="address" type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address') }}">
                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label" for="emergency_contact">Emergency contact</label>
                <input id="emergency_contact" type="text" name="emergency_contact" class="form-control @error('emergency_contact') is-invalid @enderror" value="{{ old('emergency_contact') }}">
                @error('emergency_contact') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-12">
                <label class="form-label" for="history_summary">History summary</label>
                <textarea id="history_summary" name="history_summary" rows="4" class="form-control @error('history_summary') is-invalid @enderror">{{ old('history_summary') }}</textarea>
                @error('history_summary') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
@endsection
