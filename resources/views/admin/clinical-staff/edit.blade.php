@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col-auto">
          @if ($doctor->image_url)
            <span class="avatar avatar-xl rounded shadow-sm" style="background-image: url({{ $doctor->image_url }})"></span>
          @else
            <span class="avatar avatar-xl rounded bg-azure-lt text-azure shadow-sm d-flex align-items-center justify-content-center">
              {{ $doctor->initial }}
            </span>
          @endif
        </div>
        <div class="col">
          <div class="page-pretitle text-secondary">Clinic</div>
          <h2 class="page-title mb-0">{{ old('name', $doctor->name) }}</h2>
          <div class="text-secondary small mt-1">{{ __('Update profile details and portal role. Password is unchanged unless the doctor resets it from the portal.') }}</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.doctors.show', $doctor) }}" class="btn">{{ __('Cancel') }}</a>
            <button type="submit" form="doctor-edit-form" class="btn btn-primary">{{ __('Save changes') }}</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <form id="doctor-edit-form" method="POST" action="{{ route('admin.doctors.update', $doctor->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row g-3">
          <div class="col-lg-8">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title mb-0">{{ __('Clinical staff details') }}</h3>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label required" for="name">{{ __('Name') }}</label>
                    <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror"
                      value="{{ old('name', $doctor->name) }}" required autocomplete="name">
                    @error('name')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label required" for="email">{{ __('Email') }}</label>
                    <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                      value="{{ old('email', $doctor->email) }}" required autocomplete="email">
                    @error('email')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="phone">{{ __('Phone') }}</label>
                    <input id="phone" name="phone" type="text" class="form-control @error('phone') is-invalid @enderror"
                      value="{{ old('phone', $doctor->phone) }}" autocomplete="tel">
                    @error('phone')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="specialty">{{ __('Specialty') }}</label>
                    <input id="specialty" name="specialty" type="text" class="form-control @error('specialty') is-invalid @enderror"
                      value="{{ old('specialty', $doctor->specialty) }}">
                    @error('specialty')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="license_no">{{ __('License no.') }}</label>
                    <input id="license_no" name="license_no" type="text" class="form-control @error('license_no') is-invalid @enderror"
                      value="{{ old('license_no', $doctor->license_no) }}">
                    @error('license_no')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="experience_years">{{ __('Years of experience') }}</label>
                    <input id="experience_years" name="experience_years" type="number" min="0" max="80"
                      class="form-control @error('experience_years') is-invalid @enderror"
                      value="{{ old('experience_years', $doctor->experience_years) }}">
                    @error('experience_years')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="bio">{{ __('Bio') }}</label>
                    <textarea id="bio" name="bio" rows="4" class="form-control @error('bio') is-invalid @enderror">{{ old('bio', $doctor->bio) }}</textarea>
                    @error('bio')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="doctor_role_id">{{ __('Clinical portal role') }}</label>
                    <select id="doctor_role_id" name="doctor_role_id" class="form-select @error('doctor_role_id') is-invalid @enderror">
                      <option value="" @selected(old('doctor_role_id', $doctor->doctor_role_id) === null || old('doctor_role_id', $doctor->doctor_role_id) === '')>{{ __('Full portal access (default)') }}</option>
                      @foreach ($doctorRoles as $role)
                        <option value="{{ $role->id }}" @selected((string) old('doctor_role_id', $doctor->doctor_role_id) === (string) $role->id)>{{ $role->name }}</option>
                      @endforeach
                    </select>
                    <small class="form-hint">
                      <a href="{{ route('admin.doctor-roles.index') }}">{{ __('Manage clinical roles') }}</a>
                    </small>
                    @error('doctor_role_id')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="photo">{{ __('Profile photo') }}</label>
                    <input id="photo" name="photo" type="file" accept="image/*"
                      class="form-control @error('photo') is-invalid @enderror">
                    @error('photo')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if ($doctor->image_path)
                      <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="remove_photo" id="remove_photo" value="1"
                          @checked(old('remove_photo'))>
                        <label class="form-check-label" for="remove_photo">{{ __('Remove current photo') }}</label>
                      </div>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="card">
              <div class="card-body">
                <h3 class="card-title">{{ __('Status') }}</h3>
                <p class="text-secondary small mb-0">
                  {{ __('Approval and account status are still controlled from the list or this doctor’s profile using the status control.') }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
@endsection
