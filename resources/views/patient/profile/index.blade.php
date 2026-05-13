@extends('patient.layouts.master')

@section('title', 'My profile')

@section('content')
  @php
    /** @var \App\Models\Patient|null $patient */
    $statusClass = fn (?string $status) => match (strtolower((string) $status)) {
        'active' => 'text-success',
        'inactive' => 'text-danger',
        default => 'text-muted',
    };
  @endphp

  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Patient <span></span> Profile
        </div>
      </div>
    </div>

    <div class="page-content pt-70 pb-60">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="row">
              @include('patient.layouts.sidebar')
              <div class="col-12 col-md-9">
                <div class="account dashboard-content pl-50">
                  <div class="card mb-25">
                    <div class="card-header p-0 pb-10">
                      <div>
                        <h3 class="mb-0">My profile</h3>
                        <p class="mb-0 text-muted font-sm">Update your account and personal information here.</p>
                      </div>
                    </div>
                  </div>

                  <div class="card">
                    <div class="card-body">
                      @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                      @endif

                      @if ($errors->any())
                        <div class="alert alert-danger">
                          <ul class="mb-0 pl-15">
                            @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                            @endforeach
                          </ul>
                        </div>
                      @endif

                      <form method="POST" action="{{ route('patient.profile.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="row mb-10">
                          <div class="col-md-6 mb-15">
                            <label class="form-label">Full name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $patient?->name) }}" required>
                          </div>
                          <div class="col-md-6 mb-15">
                            <label class="form-label">Email address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $patient?->email) }}" required>
                          </div>
                          <div class="col-md-6 mb-15">
                            <label class="form-label">Phone number</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $patient?->phone) }}">
                          </div>
                          <div class="col-md-6 mb-15">
                            <label class="form-label">Status</label>
                            <p class="mb-0 pt-8"><span class="{{ $statusClass($patient?->status) }}">{{ ucfirst((string) ($patient?->status ?? 'active')) }}</span></p>
                          </div>
                          <div class="col-md-6 mb-15">
                            <label class="form-label">Birthdate</label>
                            <input type="date" name="birthdate" class="form-control" value="{{ old('birthdate', !empty($patient?->birthdate) ? \Illuminate\Support\Carbon::parse((string) $patient->birthdate)->format('Y-m-d') : '') }}">
                          </div>
                          <div class="col-md-6 mb-15">
                            <label class="form-label">Gender</label>
                            <input type="text" name="gender" class="form-control" value="{{ old('gender', $patient?->gender) }}">
                          </div>
                          <div class="col-md-12 mb-15">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address', $patient?->address) }}">
                          </div>
                          <div class="col-md-12 mb-15">
                            <label class="form-label">Emergency contact</label>
                            <input type="text" name="emergency_contact" class="form-control" value="{{ old('emergency_contact', $patient?->emergency_contact) }}">
                          </div>
                        </div>

                        <hr>

                        <div class="row">
                          <div class="col-md-6 mb-15">
                            <label class="form-label">Skin type</label>
                            <input type="text" name="skin_type" class="form-control" value="{{ old('skin_type', $patient?->skin_type) }}">
                          </div>
                          <div class="col-md-6 mb-15">
                            <label class="form-label">Skin concerns</label>
                            <input type="text" name="skin_concerns" class="form-control" value="{{ old('skin_concerns', $patient?->skin_concerns) }}">
                          </div>
                          <div class="col-md-6 mb-15">
                            <label class="form-label">Recovery time preference</label>
                            <input type="text" name="recovery_time" class="form-control" value="{{ old('recovery_time', $patient?->recovery_time) }}">
                          </div>
                          <div class="col-md-6 mb-15">
                            <label class="form-label">Max appointments per day</label>
                            <input type="number" min="0" max="20" name="max_appointments_per_day" class="form-control"
                              value="{{ old('max_appointments_per_day', $patient?->max_appointments_per_day) }}">
                          </div>
                          <div class="col-md-12 mb-15">
                            <label class="form-label">History summary</label>
                            <textarea name="history_summary" class="form-control" rows="3">{{ old('history_summary', $patient?->history_summary) }}</textarea>
                          </div>
                          <div class="col-md-12 mb-20">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3">{{ old('notes', $patient?->notes) }}</textarea>
                          </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Changes</button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
@endsection
