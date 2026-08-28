@extends('clinical-staff.layouts.master')

@section('title', 'Edit '.$schedule->day_label)

@section('content')
  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Clinical staff <span></span> Availability
        </div>
      </div>
    </div>

    <div class="page-content pt-70 pb-60">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="row">
              @include('clinical-staff.layouts.sidebar')
              <div class="col-12">
                <div class="account dashboard-content pl-50">
                  <div class="section-title mb-20 d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Edit {{ $schedule->day_label }}</h3>
                    <a href="{{ route('clinical_staff.availability') }}" class="btn btn-sm btn-outline">Back</a>
                  </div>

                  @if ($errors->any())
                    <div class="alert alert-danger mb-20">
                      <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                          <li>{{ $error }}</li>
                        @endforeach
                      </ul>
                    </div>
                  @endif

                  <div class="card">
                    <div class="card-body">
                      <form method="POST" action="{{ route('clinical_staff.availability.day.update', $schedule->weekday) }}">
                        @csrf
                        @method('PATCH')

                        <div class="form-check mb-20">
                          <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                            {{ old('is_active', $schedule->is_active) ? 'checked' : '' }}>
                          <label class="form-check-label" for="is_active">Active (available this day)</label>
                        </div>

                        <div class="row g-3">
                          <div class="col-md-6">
                            <label for="start_time" class="form-label">Start time</label>
                            <input type="time" id="start_time" name="start_time" class="form-control"
                              value="{{ old('start_time', $schedule->start_time ? substr((string) $schedule->start_time, 0, 5) : '09:00') }}">
                          </div>
                          <div class="col-md-6">
                            <label for="end_time" class="form-label">End time</label>
                            <input type="time" id="end_time" name="end_time" class="form-control"
                              value="{{ old('end_time', $schedule->end_time ? substr((string) $schedule->end_time, 0, 5) : '17:00') }}">
                          </div>
                        </div>

                        <div class="mt-25">
                          <button type="submit" class="btn btn-sm">Save</button>
                        </div>
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
