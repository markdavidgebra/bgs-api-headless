@extends('clinical-staff.layouts.master')

@section('title', 'Patient Records')

@section('content')
  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Clinical staff <span></span> Patient Records
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
                  <div class="section-title mb-20">
                    <h3>Patient Records</h3>
                    <p class="mb-0">Patients you handled through appointments.</p>
                  </div>

                  <div class="card mb-25">
                    <div class="card-body">
                      <form method="GET" action="{{ route('clinical_staff.patient-records') }}" class="row g-3 align-items-end">
                        <div class="col-md-8">
                          <label for="search" class="form-label">Search patient</label>
                          <input type="text" id="search" name="search" class="form-control"
                            value="{{ $search }}" placeholder="Name, email, or phone">
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                          <button type="submit" class="btn btn-sm">Search</button>
                          <a href="{{ route('clinical_staff.patient-records') }}" class="btn btn-sm btn-outline">Reset</a>
                        </div>
                      </form>
                    </div>
                  </div>

                  <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                      <h5 class="mb-0">Patient List</h5>
                      <span class="small text-muted">{{ $records->count() }} patient(s)</span>
                    </div>
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="table mb-0">
                          <thead>
                            <tr>
                              <th>Patient</th>
                              <th>Contact</th>
                              <th>Status</th>
                              <th>Total Visits</th>
                              <th>Completed</th>
                              <th>Cancelled</th>
                              <th>Last Appointment</th>
                              <th>Next Appointment</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse ($records as $record)
                              <tr>
                                <td>{{ $record->patient->name ?? '—' }}</td>
                                <td>
                                  <div>{{ $record->patient->email ?? '—' }}</div>
                                  <small class="text-muted">{{ $record->patient->phone ?? '—' }}</small>
                                </td>
                                <td>{{ ucfirst((string) ($record->patient->status ?? 'active')) }}</td>
                                <td>{{ $record->total_appointments }}</td>
                                <td>{{ $record->completed_appointments }}</td>
                                <td>{{ $record->cancelled_appointments }}</td>
                                <td>
                                  @if ($record->last_appointment)
                                    {{ $record->last_appointment->date_display }} {{ $record->last_appointment->time_display }}
                                  @else
                                    —
                                  @endif
                                </td>
                                <td>
                                  @if ($record->next_appointment)
                                    {{ $record->next_appointment->date_display }} {{ $record->next_appointment->time_display }}
                                  @else
                                    —
                                  @endif
                                </td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="8" class="text-center text-secondary py-4">
                                  No patient records found.
                                </td>
                              </tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
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
