@extends('clinical-staff.layouts.master')

@section('title', 'Patient Records')

@section('content')
  <style>
    .record-btn {
      border-radius: 8px;
      font-weight: 700;
      font-size: 12px;
      line-height: 1.2;
      padding: 8px 12px;
    }

    .record-btn-primary {
      border: 1px solid #1d4ed8;
      background: #1d4ed8;
      color: #fff !important;
    }

    .record-btn-primary:hover,
    .record-btn-primary:focus {
      background: #1e40af;
      border-color: #1e40af;
      color: #fff !important;
    }

    .record-btn-light {
      border: 1px solid #94a3b8;
      background: #ffffff;
      color: #0f172a !important;
    }

    .record-btn-light:hover,
    .record-btn-light:focus {
      border-color: #64748b;
      background: #f8fafc;
      color: #0f172a !important;
    }
  </style>

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
                    <p class="mb-0">Overview of your handled patients.</p>
                  </div>

                  <div class="card mb-25">
                    <div class="card-body">
                      <form method="GET" action="{{ route('doctor.patient-records') }}" class="row g-3 align-items-end">
                        <div class="col-md-8">
                          <label for="search" class="form-label">Search patient</label>
                          <input type="text" id="search" name="search" class="form-control"
                            value="{{ $search }}" placeholder="Name, email, or phone">
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                          <button type="submit" class="btn btn-sm record-btn record-btn-primary">Search</button>
                          <a href="{{ route('doctor.patient-records') }}" class="btn btn-sm record-btn record-btn-light">Reset</a>
                        </div>
                      </form>
                    </div>
                  </div>

                  <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                      <h5 class="mb-0">Patient List</h5>
                      <span class="small text-muted">{{ $records->total() }} patient(s) total</span>
                    </div>
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="table mb-0">
                          <thead>
                            <tr>
                              <th>Patient Name</th>
                              <th>Last Visit</th>
                              <th>Total Visits</th>
                              <th>Active Plan (Membership/Package)</th>
                              <th>Status (Active/Inactive)</th>
                              <th>Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse ($records as $record)
                              <tr>
                                <td>
                                  <div class="font-weight-bold">{{ $record->patient->name ?? '—' }}</div>
                                  @if (filled($record->patient->email ?? null))
                                    <div class="small text-muted">{{ $record->patient->email }}</div>
                                  @endif
                                  @if (filled($record->patient->phone ?? null))
                                    <div class="small text-muted">{{ $record->patient->phone }}</div>
                                  @endif
                                </td>
                                <td>
                                  @if ($record->last_appointment)
                                    {{ $record->last_appointment->date_display }} {{ $record->last_appointment->time_display }}
                                  @else
                                    —
                                  @endif
                                </td>
                                <td>{{ $record->total_appointments }}</td>
                                <td>{{ $record->active_plan ?? 'No active plan' }}</td>
                                <td>{{ ucfirst((string) ($record->patient->status ?? 'active')) }}</td>
                                <td>
                                  <a href="{{ route('doctor.patient-records.show', $record->patient->id) }}"
                                    class="btn btn-xs record-btn record-btn-light">View profile</a>
                                </td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="6" class="text-center text-secondary py-4">No patient records found.</td>
                              </tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
                      <div class="card-footer d-flex justify-content-center">
                        {{ $records->links() }}
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
