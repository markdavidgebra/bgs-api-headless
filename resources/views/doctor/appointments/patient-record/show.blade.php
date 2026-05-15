@extends('doctor.layouts.master')

@section('title', 'Patient Details')

@section('content')
  <style>
    .tab-btn {
      border: 1px solid #cbd5e1;
      background: #fff;
      color: #1f2937;
      border-radius: 8px;
      padding: 8px 12px;
      font-size: 12px;
      font-weight: 700;
      cursor: pointer;
    }

    .tab-btn.active {
      background: #1d4ed8;
      border-color: #1d4ed8;
      color: #fff;
    }

    .tab-panel {
      display: none;
    }

    .tab-panel.active {
      display: block;
    }

    .vital-open-btn {
      white-space: normal;
      word-break: break-word;
      line-height: 1.35;
    }

    #tab-body-analyzer .body-analyzer-thumb {
      background-color: #f4f4f5;
    }

    #tab-body-analyzer .body-analyzer-thumb > img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    #tab-body-analyzer .body-analyzer-card-header {
      text-align: left !important;
    }

    #tab-overview .patient-info-section {
      border-top: 1px solid #e5e7eb;
      padding-top: 1rem;
      margin-top: 0.5rem;
    }

    #tab-overview .patient-info-section-title {
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      color: #64748b;
      margin-bottom: 0.75rem;
    }

    #tab-overview .patient-info-field-label {
      font-size: 0.8125rem;
      font-weight: 600;
      color: #475569;
      margin-bottom: 0.25rem;
    }

    #tab-overview .patient-info-field-value {
      font-size: 0.9375rem;
      color: #1f2937;
      white-space: pre-wrap;
      word-break: break-word;
    }

    #tab-overview .patient-info-allergy {
      background: #fff7ed;
      border: 1px solid #fed7aa;
      border-radius: 8px;
      padding: 0.75rem 1rem;
    }

    #tab-overview .patient-info-badge {
      display: inline-block;
      font-size: 0.75rem;
      font-weight: 600;
      padding: 0.25rem 0.5rem;
      border-radius: 6px;
      background: #eff6ff;
      color: #1d4ed8;
      border: 1px solid #bfdbfe;
    }

    #bottle-citrus-section .bottle-citrus-thumb {
      background-color: #f4f4f5;
    }

    #bottle-citrus-section .bottle-citrus-thumb > img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    #bottle-citrus-section .bottle-citrus-card-header {
      text-align: left !important;
    }

    #lemon-bottle-section .lemon-bottle-thumb {
      background-color: #f4f4f5;
    }

    #lemon-bottle-section .lemon-bottle-thumb > img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    #lemon-bottle-section .lemon-bottle-card-header {
      text-align: left !important;
    }

    #aqualyx-section .aqualyx-thumb {
      background-color: #f4f4f5;
    }

    #aqualyx-section .aqualyx-thumb > img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    #aqualyx-section .aqualyx-card-header {
      text-align: left !important;
    }

    #drip-section .drip-thumb {
      background-color: #f4f4f5;
    }

    #drip-section .drip-thumb > img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    #drip-section .drip-card-header {
      text-align: left !important;
    }

    #micro-needling-section .micro-needling-thumb {
      background-color: #f4f4f5;
    }

    #micro-needling-section .micro-needling-thumb > img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    #micro-needling-section .micro-needling-card-header {
      text-align: left !important;
    }
  </style>

  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Doctor <span></span> Patient Details
        </div>
      </div>
    </div>

    <div class="page-content pt-70 pb-60">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="row">
              @include('doctor.layouts.sidebar')
              <div class="col-12">
                <div class="account dashboard-content pl-50">
                  <div class="section-title mb-20 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h3 class="mb-0">{{ $patient->name }} - Patient Details</h3>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                      @if ($lastVisit)
                        <a href="{{ route('doctor.appointments.show', $lastVisit) }}" class="btn btn-sm btn-outline-primary">View appointment</a>
                      @endif
                      <a href="{{ route('doctor.patient-records') }}" class="btn btn-sm btn-outline">Back to records</a>
                    </div>
                  </div>

                  @if (session('success'))
                    <div class="alert alert-success mb-20">{{ session('success') }}</div>
                  @endif

                  @if ($errors->any())
                    <div class="alert alert-danger mb-20">
                      <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                          <li>{{ $error }}</li>
                        @endforeach
                      </ul>
                    </div>
                  @endif

                  <div class="d-flex flex-wrap gap-2 mb-20" id="tabButtons">
                    <button type="button" class="tab-btn active" data-target="tab-appointments">Appointments</button>
                    <button type="button" class="tab-btn" data-target="tab-overview">Patients Info</button>
                    <button type="button" class="tab-btn" data-target="tab-assessment">{{ __('Assessment Checklist') }}</button>
                    <button type="button" class="tab-btn" data-target="tab-notes">Treatment Notes</button>
                    <button type="button" class="tab-btn" data-target="tab-packages">Packages / Memberships</button>
                    <button type="button" class="tab-btn" data-target="tab-payments">Payments</button>
                    <button type="button" class="tab-btn" data-target="tab-body-analyzer">Images</button>
                  </div>

                  <div class="card tab-panel" id="tab-overview">
                    <div class="card-header"><h5 class="mb-0">Patients Info</h5></div>
                    <div class="card-body">
                      <div class="patient-info-section pt-0 mt-0 border-0">
                        <div class="patient-info-section-title">{{ __('Contact & profile') }}</div>
                        <div class="row">
                          <div class="col-md-6 mb-2"><strong>Patient:</strong> {{ $patient->name }}</div>
                          <div class="col-md-6 mb-2"><strong>Email:</strong> {{ $patient->email ?? '—' }}</div>
                          <div class="col-md-6 mb-2"><strong>Contact:</strong> {{ $patient->phone ?? '—' }}</div>
                          <div class="col-md-6 mb-2"><strong>Birthdate:</strong> {{ $patient->birthdate?->format('Y-m-d') ?? '—' }}</div>
                          <div class="col-md-6 mb-2"><strong>Gender:</strong> {{ $patient->gender ?? '—' }}</div>
                          <div class="col-md-6 mb-2"><strong>Address:</strong> {{ $patient->address ?? '—' }}</div>
                          <div class="col-md-6 mb-2"><strong>Status:</strong> {{ ucfirst((string) ($patient->status ?? 'active')) }}</div>
                        </div>
                      </div>

                      <div class="patient-info-section">
                        <div class="patient-info-section-title">{{ __('Visits with you') }}</div>
                        <div class="row">
                          <div class="col-md-6 mb-2"><strong>Last visit:</strong>
                            @if ($lastVisit)
                              {{ $lastVisit->date_display }} {{ $lastVisit->time_display }}
                            @else
                              —
                            @endif
                          </div>
                          <div class="col-md-6 mb-2"><strong>Total visits:</strong> {{ $totalVisits }}</div>
                        </div>
                      </div>

                      @php
                        $clinicalFields = $latestNote ? $latestNote->patientInfoClinicalFields() : [];
                        $imageAttachments = $latestNote ? $latestNote->patientInfoImageAttachments() : [];
                        $hasClinicalOverview = $clinicalFields !== [] || $imageAttachments !== [];
                      @endphp

                      <div class="patient-info-section">
                        <div class="patient-info-section-title">{{ __('Latest clinical notes') }}</div>
                        @if ($latestNoteAppointment ?? null)
                          <p class="small text-muted mb-3">
                            {{ __('From visit') }}: {{ $latestNoteAppointment->date_display }} {{ $latestNoteAppointment->time_display }}
                            @if ($latestNoteAppointment->service_name)
                              · {{ $latestNoteAppointment->service_name }}
                            @endif
                          </p>
                        @endif
                        @if (! $hasClinicalOverview)
                          <p class="text-secondary mb-0">{{ __('No clinical notes on file yet.') }}</p>
                        @else
                          @if ($clinicalFields !== [])
                            <div class="row">
                              @foreach ($clinicalFields as $field)
                                <div class="col-md-6 mb-3">
                                  <div class="patient-info-field-label">{{ $field['label'] }}</div>
                                  <div class="patient-info-field-value">{{ $field['value'] }}</div>
                                </div>
                              @endforeach
                            </div>
                          @endif
                          @if ($imageAttachments !== [])
                            <div class="mt-1">
                              <div class="patient-info-field-label mb-2">{{ __('Images on file') }}</div>
                              <div class="d-flex flex-wrap gap-2">
                                @foreach ($imageAttachments as $attachmentLabel)
                                  <span class="patient-info-badge">{{ $attachmentLabel }}</span>
                                @endforeach
                              </div>
                              <p class="small text-muted mt-2 mb-0">{{ __('Open the Images tab to view uploads.') }}</p>
                            </div>
                          @endif
                        @endif
                      </div>

                      <div class="patient-info-section">
                        <div class="patient-info-section-title">{{ __('Allergy & alerts') }}</div>
                        <div class="patient-info-allergy">
                          <div class="patient-info-field-label mb-1">{{ __('Allergy') }}</div>
                          <div class="patient-info-field-value mb-0">{{ $latestAlerts ?: __('None recorded') }}</div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="card tab-panel" id="tab-assessment">
                    <div class="card-header"><h5 class="mb-0">{{ __('Assessment Checklist') }}</h5></div>
                    <div class="card-body">
                      <p class="text-secondary small mb-3">{{ __('Mobility is saved per appointment. Use Record or Edit to open the appointment and update the checklist on the clinical notes tab.') }}</p>
                      @if ($assessmentHistory->isEmpty())
                        <p class="text-secondary mb-0">{{ __('No appointments on record.') }}</p>
                      @else
                        <div class="table-responsive">
                          <table class="table mb-0">
                            <thead>
                              <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Doctor') }}</th>
                                <th>{{ __('Service') }}</th>
                                <th>{{ __('Mobility') }}</th>
                                <th class="text-nowrap">{{ __('Actions') }}</th>
                              </tr>
                            </thead>
                            <tbody>
                              @foreach ($assessmentHistory as $row)
                                <tr>
                                  <td>{{ $row->appointment->date_display }} {{ $row->appointment->time_display }}</td>
                                  <td>{{ $row->appointment->doctor_name }}</td>
                                  <td>{{ $row->appointment->service_name }}</td>
                                  <td>{{ $row->mobility_label ?? '—' }}</td>
                                  <td>
                                    @if ($row->can_edit)
                                      <a href="{{ route('doctor.appointments.show', $row->appointment) }}#clinical-notes-assessment" class="btn btn-sm btn-outline-primary">{{ $row->mobility_label ? __('Edit') : __('Record') }}</a>
                                    @else
                                      <span class="text-secondary small">—</span>
                                    @endif
                                  </td>
                                </tr>
                              @endforeach
                            </tbody>
                          </table>
                        </div>
                      @endif
                    </div>
                  </div>

                  <div class="card tab-panel" id="tab-notes">
                    <div class="card-header"><h5 class="mb-0">Treatment Notes</h5></div>
                    <div class="card-body">
                      <h6 class="mb-10">Past Notes</h6>
                      <div class="table-responsive mb-20">
                        <table class="table mb-0">
                          <thead>
                            <tr>
                              <th>Date</th>
                              <th>Doctor</th>
                              <th>Vitals</th>
                              <th>Observation</th>
                              <th>Post procedures</th>
                              <th>Take home medications</th>
                              <th>Allergy</th>
                              <th class="text-nowrap">Edit</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse ($notesHistory as $row)
                              <tr>
                                <td>{{ $row->appointment->date_display }} {{ $row->appointment->time_display }}</td>
                                <td>{{ $row->appointment->doctor_name }}</td>
                                <td class="small">
                                  @php
                                    $vitalLine = $row->note->vitalSignsSummary();
                                  @endphp
                                  @if ($vitalLine !== '')
                                    <button type="button"
                                      class="btn btn-sm btn-outline-primary vital-open-btn"
                                      aria-label="View vital signs: {{ $vitalLine }}"
                                      data-vital-bp="{{ $row->note->vital_blood_pressure }}"
                                      data-vital-hr="{{ $row->note->vital_heart_rate }}"
                                      data-vital-temp="{{ $row->note->vital_temperature }}"
                                      data-vital-rr="{{ $row->note->vital_respiratory_rate }}"
                                      data-vital-spo2="{{ $row->note->vital_oxygen_saturation }}"
                                      data-vital-wt="{{ $row->note->vital_weight }}"
                                      data-vital-ht="{{ $row->note->vital_height }}"
                                      data-vital-when="{{ $row->appointment->date_display }} {{ $row->appointment->time_display }} · {{ $row->appointment->doctor_name }}">
                                      View
                                    </button>
                                  @else
                                    —
                                  @endif
                                </td>
                                <td>{{ $row->note->doctor_notes ?: $row->note->patient_concern ?: '—' }}</td>
                                <td>{{ $row->note->appointment_remarks ?: '—' }}</td>
                                <td>{{ $row->note->instructions ?: '—' }}</td>
                                <td>{{ $row->note->alerts ?: '—' }}</td>
                                <td class="text-nowrap">
                                  @if ((int) $row->appointment->doctor_id === (int) auth('doctor')->id())
                                    <a href="{{ route('doctor.appointments.notes.create', $row->appointment) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                  @else
                                    —
                                  @endif
                                </td>
                              </tr>
                            @empty
                              <tr><td colspan="8" class="text-center text-secondary py-4">No notes yet.</td></tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>

                      <h6 class="mb-10">Add New Note</h6>
                      @if ($myAppointments->isEmpty())
                        <p class="text-secondary mb-0">You do not have any appointments with this patient yet. Notes can be added once an appointment exists between you and this patient.</p>
                      @else
                        <form method="POST" action="{{ route('doctor.patient-records.notes.store', $patient) }}" class="row g-3">
                          @csrf
                          <div class="col-md-6">
                            <label class="form-label">Appointment</label>
                            <select name="appointment_id" class="form-control">
                              @foreach ($myAppointments as $appointment)
                                <option value="{{ $appointment->id }}">
                                  {{ $appointment->appointment_no }} - {{ $appointment->date_display }} {{ $appointment->time_display }}
                                </option>
                              @endforeach
                            </select>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label">Observation</label>
                            <textarea name="observation" class="form-control" rows="2">{{ old('observation') }}</textarea>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label">Post procedures</label>
                            <textarea name="procedure_done" class="form-control" rows="2">{{ old('procedure_done') }}</textarea>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label">Take home medications</label>
                            <textarea name="recommendation" class="form-control" rows="2">{{ old('recommendation') }}</textarea>
                          </div>
                          <div class="col-md-12">
                            <label class="form-label">Allergy</label>
                            <textarea name="follow_up" class="form-control" rows="2">{{ old('follow_up') }}</textarea>
                          </div>
                          <div class="col-md-12">
                            <button type="submit" class="btn btn-sm">Save Note</button>
                          </div>
                        </form>
                      @endif
                    </div>
                  </div>

                  <div class="card tab-panel active" id="tab-appointments">
                    <div class="card-header"><h5 class="mb-0">Appointments</h5></div>
                    <div class="card-body text-start">
                      <h6 class="mb-10 text-start">Upcoming Appointments</h6>
                      <div class="table-responsive mb-20">
                        <table class="table table-striped mb-0">
                          <thead>
                            <tr>
                              <th class="text-nowrap">Date</th>
                              <th class="text-nowrap">Time</th>
                              <th>Appointment</th>
                              <th>Doctor</th>
                              <th>Service</th>
                              <th class="text-nowrap">Status</th>
                              <th class="text-nowrap">Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse ($upcomingAppointments as $appointment)
                              <tr>
                                <td class="text-nowrap">{{ $appointment->date_display }}</td>
                                <td class="text-nowrap">{{ $appointment->time_display }}</td>
                                <td class="text-nowrap">{{ $appointment->appointment_no ?? '—' }}</td>
                                <td>{{ $appointment->doctor_name }}</td>
                                <td>{{ $appointment->service_name }}</td>
                                <td class="text-nowrap">
                                  <span class="badge {{ $appointment->status_badge }}">{{ $appointment->status_label }}</span>
                                </td>
                                <td class="text-nowrap">
                                  @if ((int) $appointment->doctor_id === (int) auth('doctor')->id())
                                    <a href="{{ route('doctor.appointments.show', $appointment) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    <a href="{{ route('doctor.appointments.notes.create', $appointment) }}" class="btn btn-sm">Add note</a>
                                    @if (in_array(strtolower((string) $appointment->status), ['pending', 'rescheduled'], true))
                                      <form method="POST" action="{{ route('doctor.appointments.approve', $appointment) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                      </form>
                                    @endif
                                  @else
                                    —
                                  @endif
                                </td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="7" class="text-center text-secondary py-4">No upcoming appointments.</td>
                              </tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>

                      <h6 class="mb-10 text-start">Past Appointments</h6>
                      <div class="table-responsive">
                        <table class="table table-striped mb-0">
                          <thead>
                            <tr>
                              <th class="text-nowrap">Date</th>
                              <th class="text-nowrap">Time</th>
                              <th>Appointment</th>
                              <th>Doctor</th>
                              <th>Service</th>
                              <th class="text-nowrap">Status</th>
                              <th class="text-nowrap">Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse ($pastAppointments as $appointment)
                              <tr>
                                <td class="text-nowrap">{{ $appointment->date_display }}</td>
                                <td class="text-nowrap">{{ $appointment->time_display }}</td>
                                <td class="text-nowrap">{{ $appointment->appointment_no ?? '—' }}</td>
                                <td>{{ $appointment->doctor_name }}</td>
                                <td>{{ $appointment->service_name }}</td>
                                <td class="text-nowrap">
                                  <span class="badge {{ $appointment->status_badge }}">{{ $appointment->status_label }}</span>
                                </td>
                                <td class="text-nowrap">
                                  @if ((int) $appointment->doctor_id === (int) auth('doctor')->id())
                                    <a href="{{ route('doctor.appointments.show', $appointment) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    <a href="{{ route('doctor.appointments.notes.create', $appointment) }}" class="btn btn-sm">Add note</a>
                                    @if (in_array(strtolower((string) $appointment->status), ['pending', 'rescheduled'], true))
                                      <form method="POST" action="{{ route('doctor.appointments.approve', $appointment) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                      </form>
                                    @endif
                                  @else
                                    —
                                  @endif
                                </td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="7" class="text-center text-secondary py-4">No past appointments.</td>
                              </tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>

                  <div class="card tab-panel" id="tab-packages">
                    <div class="card-header"><h5 class="mb-0">Packages / Memberships</h5></div>
                    <div class="card-body">
                      <h6 class="mb-10">Packages</h6>
                      <div class="mb-20">
                        @forelse ($patientPackageProgress as $pp)
                          @php
                            $package = $pp->package;
                            $pkgName = $package->treatmentPackage->name ?? '—';
                            $pkgTotal = max(0, (int) $package->total_sessions);
                            $pkgUsed = max(0, min($pkgTotal, (int) $package->used_sessions));
                          @endphp
                          <div class="border rounded p-3 mb-15">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                              <strong>{{ $pkgName }}</strong>
                              <span class="text-secondary small">{{ $package->end_date?->format('Y-m-d') ?? '—' }}</span>
                            </div>

                            @if ($pp->has_breakdown)
                              <form method="POST" action="{{ route('doctor.patient-records.packages.sessions.update', ['patient' => $patient, 'patientPackage' => $package]) }}" class="pkg-per-service-form">
                                @csrf
                                @method('PATCH')
                                @foreach ($pp->rows->groupBy('service_id') as $serviceId => $sessionRows)
                                  @php $serviceName = $sessionRows->first()['service_name']; @endphp
                                  <div class="mb-3">
                                    <div class="small fw-semibold text-muted mb-1">{{ $serviceName }}</div>
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                      <span class="small text-muted">{{ $sessionRows->where('is_done', true)->count() }}/{{ $sessionRows->count() }}</span>
                                      <div class="d-flex flex-wrap gap-1 pt-1" role="group" aria-label="Sessions for {{ $serviceName }}">
                                        @foreach ($sessionRows as $row)
                                          <input type="checkbox"
                                            name="checked_service_sessions[]"
                                            value="{{ $row['key'] }}"
                                            class="form-check-input m-0"
                                            style="width: 1.1rem; height: 1.1rem; cursor: pointer;"
                                            title="{{ __('Session :n', ['n' => $row['session_no']]) }}"
                                            @checked($row['is_done'])>
                                        @endforeach
                                      </div>
                                    </div>
                                  </div>
                                @endforeach
                                @php
                                  $doneAll = $pp->rows->where('is_done', true)->count();
                                  $totalAll = $pp->rows->count();
                                @endphp
                                <div class="small text-muted mb-2">{{ $doneAll }}/{{ $totalAll }} {{ __('sessions overall') }}</div>
                                <button type="submit" class="btn btn-sm btn-primary">{{ __('Save package progress') }}</button>
                              </form>
                            @elseif ($pkgTotal > 0)
                              <form method="POST" action="{{ route('doctor.patient-records.packages.sessions.update', ['patient' => $patient, 'patientPackage' => $package]) }}" class="pkg-aggregate-form">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="used_sessions" value="{{ $pkgUsed }}" class="pkg-used-input">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                  <span class="small text-muted">{{ $pkgUsed }}/{{ $pkgTotal }}</span>
                                  <div class="d-flex flex-wrap gap-1 pt-1" role="group" aria-label="Sessions completed">
                                    @for ($i = 1; $i <= $pkgTotal; $i++)
                                      <input type="checkbox"
                                        class="form-check-input m-0 pkg-session-cb"
                                        style="width: 1.1rem; height: 1.1rem; cursor: pointer;"
                                        title="{{ __('Session :n', ['n' => $i]) }}"
                                        data-index="{{ $i }}"
                                        @checked($i <= $pkgUsed)>
                                    @endfor
                                  </div>
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary mt-2">{{ __('Save package progress') }}</button>
                              </form>
                            @else
                              <p class="text-secondary small mb-0">{{ __('No session slots configured for this package.') }}</p>
                            @endif
                          </div>
                        @empty
                          <p class="text-center text-secondary py-4 mb-0">{{ __('No package records.') }}</p>
                        @endforelse
                      </div>

                      <h6 class="mb-10">Memberships</h6>
                      <div class="table-responsive">
                        <table class="table mb-0">
                          <thead>
                            <tr><th>Plan Name</th><th>Sessions Used</th><th>Sessions Remaining</th><th>Expiry Date</th></tr>
                          </thead>
                          <tbody>
                            @forelse ($subscriptions as $subscription)
                              <tr>
                                <td>{{ $subscription->membershipPlan->name ?? '—' }}</td>
                                <td>{{ (int) $subscription->sessions_used }}</td>
                                <td>{{ (int) $subscription->sessions_remaining }}</td>
                                <td>{{ $subscription->end_date?->format('Y-m-d') ?? '—' }}</td>
                              </tr>
                            @empty
                              <tr><td colspan="4" class="text-center text-secondary py-4">No membership records.</td></tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>

                  <div class="card tab-panel" id="tab-payments">
                    <div class="card-header"><h5 class="mb-0">Payments</h5></div>
                    <div class="card-body">
                      <h6 class="mb-10">Payment History</h6>
                      <div class="table-responsive mb-20">
                        <table class="table mb-0">
                          <thead>
                            <tr><th>Date</th><th>Amount</th><th>Status</th><th>Reference</th></tr>
                          </thead>
                          <tbody>
                            @forelse ($payments as $payment)
                              <tr>
                                <td>{{ $payment->payment_date?->format('Y-m-d') ?? '—' }}</td>
                                <td>{{ $payment->formatted_amount }}</td>
                                <td>{{ ucfirst((string) $payment->payment_status) }}</td>
                                <td>{{ $payment->reference_type_label }}</td>
                              </tr>
                            @empty
                              <tr><td colspan="4" class="text-center text-secondary py-4">No payment records.</td></tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>

                      <h6 class="mb-10">Appointment Payments</h6>
                      <div class="table-responsive">
                        <table class="table mb-0">
                          <thead>
                            <tr><th>Invoice</th><th>Amount</th><th>Status</th><th>Paid At</th></tr>
                          </thead>
                          <tbody>
                            @forelse ($appointmentPayments as $payment)
                              <tr>
                                <td>{{ $payment->invoice_no ?? '—' }}</td>
                                <td>₱{{ number_format((float) $payment->amount, 2) }}</td>
                                <td>{{ ucfirst((string) ($payment->payment_status ?? 'pending')) }}</td>
                                <td>{{ $payment->paid_at?->format('Y-m-d H:i') ?? '—' }}</td>
                              </tr>
                            @empty
                              <tr><td colspan="4" class="text-center text-secondary py-4">No appointment payment records.</td></tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>

                  <div class="tab-panel" id="tab-body-analyzer">
                    <div class="card mb-20">
                      <div class="card-header body-analyzer-card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h5 class="mb-0">Body Analyzer</h5>
                        @if ($bodyAnalyzerImages->isNotEmpty())
                          <div class="d-flex flex-wrap gap-2 ba-view-toggle" role="group" aria-label="{{ __('Body analyzer gallery view') }}">
                            <button type="button" class="btn btn-sm btn-primary ba-view-btn active" data-ba-view="all">{{ __('View all images') }}</button>
                            <button type="button" class="btn btn-sm btn-outline-primary ba-view-btn" data-ba-view="ends">{{ __('View first and last image') }}</button>
                          </div>
                        @endif
                      </div>
                      <div class="card-body text-start">
                        @if ($bodyAnalyzerImages->isEmpty())
                          <p class="text-secondary mb-0">{{ __('No body analyzer images uploaded for this patient yet.') }}</p>
                        @else
                          <div class="row g-3" id="bodyAnalyzerGrid">
                            @foreach ($bodyAnalyzerImages as $row)
                              <div class="col-12 col-sm-6 col-lg-3 body-analyzer-grid-item" data-ba-index="{{ $loop->index }}">
                                <div class="body-analyzer-cell">
                                  <div class="small text-muted mb-2">
                                    {{ $row->appointment->appointment_date?->format('M/j/Y') ?? '—' }} {{ $row->appointment->time_display }}
                                  </div>
                                  <a href="{{ $row->url }}" target="_blank" rel="noopener noreferrer" class="d-block text-decoration-none">
                                    <div class="ratio ratio-4x3 rounded border overflow-hidden body-analyzer-thumb">
                                      <img src="{{ $row->url }}" alt="{{ __('Body analyzer image') }}"
                                        loading="lazy"
                                        onerror="this.style.display='none'; this.closest('.body-analyzer-cell')?.querySelector('.body-analyzer-err')?.classList.remove('d-none');">
                                    </div>
                                  </a>
                                  <p class="body-analyzer-err text-secondary small mb-0 mt-2 d-none text-center">{{ __('Image could not be loaded.') }}</p>
                                </div>
                              </div>
                            @endforeach
                          </div>
                        @endif
                      </div>
                    </div>

                    <div class="card mb-20" id="bottle-citrus-section">
                      <div class="card-header bottle-citrus-card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h5 class="mb-0">Bottle Citrus</h5>
                        @if ($bottleCitrusImages->isNotEmpty())
                          <div class="d-flex flex-wrap gap-2 bc-view-toggle" role="group" aria-label="{{ __('Bottle citrus gallery view') }}">
                            <button type="button" class="btn btn-sm btn-primary bc-view-btn active" data-bc-view="all">{{ __('View all images') }}</button>
                            <button type="button" class="btn btn-sm btn-outline-primary bc-view-btn" data-bc-view="ends">{{ __('View first and last image') }}</button>
                          </div>
                        @endif
                      </div>
                      <div class="card-body text-start">
                        @if ($bottleCitrusImages->isEmpty())
                          <p class="text-secondary mb-0">{{ __('No bottle citrus images uploaded for this patient yet.') }}</p>
                        @else
                          <div class="row g-3" id="bottleCitrusGrid">
                            @foreach ($bottleCitrusImages as $row)
                              <div class="col-12 col-sm-6 col-lg-3 bottle-citrus-grid-item" data-bc-index="{{ $loop->index }}">
                                <div class="bottle-citrus-cell">
                                  <div class="small text-muted mb-2">
                                    {{ $row->appointment->appointment_date?->format('M/j/Y') ?? '—' }} {{ $row->appointment->time_display }}
                                  </div>
                                  <a href="{{ $row->url }}" target="_blank" rel="noopener noreferrer" class="d-block text-decoration-none">
                                    <div class="ratio ratio-4x3 rounded border overflow-hidden bottle-citrus-thumb">
                                      <img src="{{ $row->url }}" alt="{{ __('Bottle citrus image') }}"
                                        loading="lazy"
                                        onerror="this.style.display='none'; this.closest('.bottle-citrus-cell')?.querySelector('.bottle-citrus-err')?.classList.remove('d-none');">
                                    </div>
                                  </a>
                                  <p class="bottle-citrus-err text-secondary small mb-0 mt-2 d-none text-center">{{ __('Image could not be loaded.') }}</p>
                                </div>
                              </div>
                            @endforeach
                          </div>
                        @endif
                      </div>
                    </div>

                    <div class="card mb-20" id="lemon-bottle-section">
                      <div class="card-header lemon-bottle-card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h5 class="mb-0">Lemon Bottle</h5>
                        @if ($lemonBottleImages->isNotEmpty())
                          <div class="d-flex flex-wrap gap-2 lb-view-toggle" role="group" aria-label="{{ __('Lemon bottle gallery view') }}">
                            <button type="button" class="btn btn-sm btn-primary lb-view-btn active" data-lb-view="all">{{ __('View all images') }}</button>
                            <button type="button" class="btn btn-sm btn-outline-primary lb-view-btn" data-lb-view="ends">{{ __('View first and last image') }}</button>
                          </div>
                        @endif
                      </div>
                      <div class="card-body text-start">
                        @if ($lemonBottleImages->isEmpty())
                          <p class="text-secondary mb-0">{{ __('No lemon bottle images uploaded for this patient yet.') }}</p>
                        @else
                          <div class="row g-3" id="lemonBottleGrid">
                            @foreach ($lemonBottleImages as $row)
                              <div class="col-12 col-sm-6 col-lg-3 lemon-bottle-grid-item" data-lb-index="{{ $loop->index }}">
                                <div class="lemon-bottle-cell">
                                  <div class="small text-muted mb-2">
                                    {{ $row->appointment->appointment_date?->format('M/j/Y') ?? '—' }} {{ $row->appointment->time_display }}
                                  </div>
                                  <a href="{{ $row->url }}" target="_blank" rel="noopener noreferrer" class="d-block text-decoration-none">
                                    <div class="ratio ratio-4x3 rounded border overflow-hidden lemon-bottle-thumb">
                                      <img src="{{ $row->url }}" alt="{{ __('Lemon bottle image') }}"
                                        loading="lazy"
                                        onerror="this.style.display='none'; this.closest('.lemon-bottle-cell')?.querySelector('.lemon-bottle-err')?.classList.remove('d-none');">
                                    </div>
                                  </a>
                                  <p class="lemon-bottle-err text-secondary small mb-0 mt-2 d-none text-center">{{ __('Image could not be loaded.') }}</p>
                                </div>
                              </div>
                            @endforeach
                          </div>
                        @endif
                      </div>
                    </div>

                    <div class="card mb-20" id="aqualyx-section">
                      <div class="card-header aqualyx-card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h5 class="mb-0">Aqualyx</h5>
                        @if ($aqualyxImages->isNotEmpty())
                          <div class="d-flex flex-wrap gap-2 aqualyx-view-toggle" role="group" aria-label="{{ __('Aqualyx gallery view') }}">
                            <button type="button" class="btn btn-sm btn-primary aqualyx-view-btn active" data-aqualyx-view="all">{{ __('View all images') }}</button>
                            <button type="button" class="btn btn-sm btn-outline-primary aqualyx-view-btn" data-aqualyx-view="ends">{{ __('View first and last image') }}</button>
                          </div>
                        @endif
                      </div>
                      <div class="card-body text-start">
                        @if ($aqualyxImages->isEmpty())
                          <p class="text-secondary mb-0">{{ __('No Aqualyx images uploaded for this patient yet.') }}</p>
                        @else
                          <div class="row g-3" id="aqualyxGrid">
                            @foreach ($aqualyxImages as $row)
                              <div class="col-12 col-sm-6 col-lg-3 aqualyx-grid-item" data-aqualyx-index="{{ $loop->index }}">
                                <div class="aqualyx-cell">
                                  <div class="small text-muted mb-2">
                                    {{ $row->appointment->appointment_date?->format('M/j/Y') ?? '—' }} {{ $row->appointment->time_display }}
                                  </div>
                                  <a href="{{ $row->url }}" target="_blank" rel="noopener noreferrer" class="d-block text-decoration-none">
                                    <div class="ratio ratio-4x3 rounded border overflow-hidden aqualyx-thumb">
                                      <img src="{{ $row->url }}" alt="{{ __('Aqualyx image') }}"
                                        loading="lazy"
                                        onerror="this.style.display='none'; this.closest('.aqualyx-cell')?.querySelector('.aqualyx-err')?.classList.remove('d-none');">
                                    </div>
                                  </a>
                                  <p class="aqualyx-err text-secondary small mb-0 mt-2 d-none text-center">{{ __('Image could not be loaded.') }}</p>
                                </div>
                              </div>
                            @endforeach
                          </div>
                        @endif
                      </div>
                    </div>

                    <div class="card mb-20" id="drip-section">
                      <div class="card-header drip-card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h5 class="mb-0">Drip</h5>
                        @if ($dripImages->isNotEmpty())
                          <div class="d-flex flex-wrap gap-2 drip-view-toggle" role="group" aria-label="{{ __('Drip gallery view') }}">
                            <button type="button" class="btn btn-sm btn-primary drip-view-btn active" data-drip-view="all">{{ __('View all images') }}</button>
                            <button type="button" class="btn btn-sm btn-outline-primary drip-view-btn" data-drip-view="ends">{{ __('View first and last image') }}</button>
                          </div>
                        @endif
                      </div>
                      <div class="card-body text-start">
                        @if ($dripImages->isEmpty())
                          <p class="text-secondary mb-0">{{ __('No drip images uploaded for this patient yet.') }}</p>
                        @else
                          <div class="row g-3" id="dripGrid">
                            @foreach ($dripImages as $row)
                              <div class="col-12 col-sm-6 col-lg-3 drip-grid-item" data-drip-index="{{ $loop->index }}">
                                <div class="drip-cell">
                                  <div class="small text-muted mb-2">
                                    {{ $row->appointment->appointment_date?->format('M/j/Y') ?? '—' }} {{ $row->appointment->time_display }}
                                  </div>
                                  <a href="{{ $row->url }}" target="_blank" rel="noopener noreferrer" class="d-block text-decoration-none">
                                    <div class="ratio ratio-4x3 rounded border overflow-hidden drip-thumb">
                                      <img src="{{ $row->url }}" alt="{{ __('Drip image') }}"
                                        loading="lazy"
                                        onerror="this.style.display='none'; this.closest('.drip-cell')?.querySelector('.drip-err')?.classList.remove('d-none');">
                                    </div>
                                  </a>
                                  <p class="drip-err text-secondary small mb-0 mt-2 d-none text-center">{{ __('Image could not be loaded.') }}</p>
                                </div>
                              </div>
                            @endforeach
                          </div>
                        @endif
                      </div>
                    </div>

                    <div class="card" id="micro-needling-section">
                      <div class="card-header micro-needling-card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h5 class="mb-0">Micro Needling</h5>
                        @if ($microNeedlingImages->isNotEmpty())
                          <div class="d-flex flex-wrap gap-2 micro-needling-view-toggle" role="group" aria-label="{{ __('Micro needling gallery view') }}">
                            <button type="button" class="btn btn-sm btn-primary micro-needling-view-btn active" data-micro-needling-view="all">{{ __('View all images') }}</button>
                            <button type="button" class="btn btn-sm btn-outline-primary micro-needling-view-btn" data-micro-needling-view="ends">{{ __('View first and last image') }}</button>
                          </div>
                        @endif
                      </div>
                      <div class="card-body text-start">
                        @if ($microNeedlingImages->isEmpty())
                          <p class="text-secondary mb-0">{{ __('No micro needling images uploaded for this patient yet.') }}</p>
                        @else
                          <div class="row g-3" id="microNeedlingGrid">
                            @foreach ($microNeedlingImages as $row)
                              <div class="col-12 col-sm-6 col-lg-3 micro-needling-grid-item" data-micro-needling-index="{{ $loop->index }}">
                                <div class="micro-needling-cell">
                                  <div class="small text-muted mb-2">
                                    {{ $row->appointment->appointment_date?->format('M/j/Y') ?? '—' }} {{ $row->appointment->time_display }}
                                  </div>
                                  <a href="{{ $row->url }}" target="_blank" rel="noopener noreferrer" class="d-block text-decoration-none">
                                    <div class="ratio ratio-4x3 rounded border overflow-hidden micro-needling-thumb">
                                      <img src="{{ $row->url }}" alt="{{ __('Micro needling image') }}"
                                        loading="lazy"
                                        onerror="this.style.display='none'; this.closest('.micro-needling-cell')?.querySelector('.micro-needling-err')?.classList.remove('d-none');">
                                    </div>
                                  </a>
                                  <p class="micro-needling-err text-secondary small mb-0 mt-2 d-none text-center">{{ __('Image could not be loaded.') }}</p>
                                </div>
                              </div>
                            @endforeach
                          </div>
                        @endif
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

  <div class="modal fade" id="vitalSignsModal" tabindex="-1" aria-labelledby="vitalSignsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title mb-0" id="vitalSignsModalLabel">Vital signs</h5>
            <div class="small text-muted mt-5" id="vitalSignsModalWhen"></div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0">
          <table class="table table-striped mb-0" id="vitalSignsModalTable">
            <tbody></tbody>
          </table>
        </div>
        <div class="modal-footer py-2">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const buttons = document.querySelectorAll('#tabButtons .tab-btn');
      const panels = document.querySelectorAll('.tab-panel');

      function activatePatientRecordTab(panelId) {
        const panel = document.getElementById(panelId);
        const btn = document.querySelector(`#tabButtons .tab-btn[data-target="${panelId}"]`);
        if (!panel || !btn) {
          return;
        }
        buttons.forEach((b) => b.classList.remove('active'));
        panels.forEach((p) => p.classList.remove('active'));
        btn.classList.add('active');
        panel.classList.add('active');
      }

      const tabFromHash = window.location.hash.replace(/^#/, '');
      if (tabFromHash) {
        activatePatientRecordTab(tabFromHash);
      }

      buttons.forEach((button) => {
        button.addEventListener('click', () => {
          activatePatientRecordTab(button.dataset.target);
        });
      });

      const vitalModalEl = document.getElementById('vitalSignsModal');
      const vitalModalWhen = document.getElementById('vitalSignsModalWhen');
      const vitalModalTbody = document.querySelector('#vitalSignsModalTable tbody');
      if (vitalModalEl && vitalModalWhen && vitalModalTbody && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        let vitalModalInstance = null;
        function getVitalModalInstance() {
          if (!vitalModalInstance) {
            vitalModalInstance = bootstrap.Modal.getInstance(vitalModalEl);
            if (!vitalModalInstance) {
              vitalModalInstance = new bootstrap.Modal(vitalModalEl);
            }
          }
          return vitalModalInstance;
        }
        const rows = [
          ['Blood pressure', 'vitalBp'],
          ['Heart rate (pulse)', 'vitalHr'],
          ['Temperature', 'vitalTemp'],
          ['Respiratory rate', 'vitalRr'],
          ['Oxygen (SpO2)', 'vitalSpo2'],
          ['Weight', 'vitalWt'],
          ['Height', 'vitalHt'],
        ];
        document.querySelectorAll('.vital-open-btn').forEach((btn) => {
          btn.addEventListener('click', function () {
            const d = this.dataset;
            vitalModalWhen.textContent = d.vitalWhen || '';
            vitalModalTbody.innerHTML = '';
            rows.forEach(([label, key]) => {
              const raw = d[key];
              const val = raw && String(raw).trim() !== '' ? String(raw).trim() : '—';
              const tr = document.createElement('tr');
              const th = document.createElement('th');
              th.className = 'ps-4 py-2';
              th.scope = 'row';
              th.textContent = label;
              const td = document.createElement('td');
              td.className = 'py-2 pe-4';
              td.textContent = val;
              tr.appendChild(th);
              tr.appendChild(td);
              vitalModalTbody.appendChild(tr);
            });
            getVitalModalInstance().show();
          });
        });
      }

      const baPanel = document.getElementById('tab-body-analyzer');
      const baGridItems = () => (baPanel ? baPanel.querySelectorAll('.body-analyzer-grid-item') : []);
      const baViewBtns = () => (baPanel ? baPanel.querySelectorAll('.ba-view-btn') : []);

      function applyBodyAnalyzerView(mode) {
        const items = baGridItems();
        const n = items.length;
        items.forEach((el, i) => {
          if (mode === 'ends' && n > 1) {
            el.classList.toggle('d-none', i !== 0 && i !== n - 1);
          } else {
            el.classList.remove('d-none');
          }
        });
        baViewBtns().forEach((btn) => {
          const active = btn.dataset.baView === mode;
          btn.classList.toggle('active', active);
          btn.classList.toggle('btn-primary', active);
          btn.classList.toggle('btn-outline-primary', !active);
        });
      }

      baViewBtns().forEach((btn) => {
        btn.addEventListener('click', function () {
          const mode = this.dataset.baView;
          if (mode === 'all' || mode === 'ends') {
            applyBodyAnalyzerView(mode);
          }
        });
      });
      applyBodyAnalyzerView('all');

      const bcPanel = document.getElementById('bottle-citrus-section');
      const bcGridItems = () => (bcPanel ? bcPanel.querySelectorAll('.bottle-citrus-grid-item') : []);
      const bcViewBtns = () => (bcPanel ? bcPanel.querySelectorAll('.bc-view-btn') : []);

      function applyBottleCitrusView(mode) {
        const items = bcGridItems();
        const n = items.length;
        items.forEach((el, i) => {
          if (mode === 'ends' && n > 1) {
            el.classList.toggle('d-none', i !== 0 && i !== n - 1);
          } else {
            el.classList.remove('d-none');
          }
        });
        bcViewBtns().forEach((btn) => {
          const active = btn.dataset.bcView === mode;
          btn.classList.toggle('active', active);
          btn.classList.toggle('btn-primary', active);
          btn.classList.toggle('btn-outline-primary', !active);
        });
      }

      bcViewBtns().forEach((btn) => {
        btn.addEventListener('click', function () {
          const mode = this.dataset.bcView;
          if (mode === 'all' || mode === 'ends') {
            applyBottleCitrusView(mode);
          }
        });
      });
      applyBottleCitrusView('all');

      const lbPanel = document.getElementById('lemon-bottle-section');
      const lbGridItems = () => (lbPanel ? lbPanel.querySelectorAll('.lemon-bottle-grid-item') : []);
      const lbViewBtns = () => (lbPanel ? lbPanel.querySelectorAll('.lb-view-btn') : []);

      function applyLemonBottleView(mode) {
        const items = lbGridItems();
        const n = items.length;
        items.forEach((el, i) => {
          if (mode === 'ends' && n > 1) {
            el.classList.toggle('d-none', i !== 0 && i !== n - 1);
          } else {
            el.classList.remove('d-none');
          }
        });
        lbViewBtns().forEach((btn) => {
          const active = btn.dataset.lbView === mode;
          btn.classList.toggle('active', active);
          btn.classList.toggle('btn-primary', active);
          btn.classList.toggle('btn-outline-primary', !active);
        });
      }

      lbViewBtns().forEach((btn) => {
        btn.addEventListener('click', function () {
          const mode = this.dataset.lbView;
          if (mode === 'all' || mode === 'ends') {
            applyLemonBottleView(mode);
          }
        });
      });
      applyLemonBottleView('all');

      const aqPanel = document.getElementById('aqualyx-section');
      const aqGridItems = () => (aqPanel ? aqPanel.querySelectorAll('.aqualyx-grid-item') : []);
      const aqViewBtns = () => (aqPanel ? aqPanel.querySelectorAll('.aqualyx-view-btn') : []);

      function applyAqualyxView(mode) {
        const items = aqGridItems();
        const n = items.length;
        items.forEach((el, i) => {
          if (mode === 'ends' && n > 1) {
            el.classList.toggle('d-none', i !== 0 && i !== n - 1);
          } else {
            el.classList.remove('d-none');
          }
        });
        aqViewBtns().forEach((btn) => {
          const active = btn.dataset.aqualyxView === mode;
          btn.classList.toggle('active', active);
          btn.classList.toggle('btn-primary', active);
          btn.classList.toggle('btn-outline-primary', !active);
        });
      }

      aqViewBtns().forEach((btn) => {
        btn.addEventListener('click', function () {
          const mode = this.dataset.aqualyxView;
          if (mode === 'all' || mode === 'ends') {
            applyAqualyxView(mode);
          }
        });
      });
      applyAqualyxView('all');

      const dripPanel = document.getElementById('drip-section');
      const dripGridItems = () => (dripPanel ? dripPanel.querySelectorAll('.drip-grid-item') : []);
      const dripViewBtns = () => (dripPanel ? dripPanel.querySelectorAll('.drip-view-btn') : []);

      function applyDripView(mode) {
        const items = dripGridItems();
        const n = items.length;
        items.forEach((el, i) => {
          if (mode === 'ends' && n > 1) {
            el.classList.toggle('d-none', i !== 0 && i !== n - 1);
          } else {
            el.classList.remove('d-none');
          }
        });
        dripViewBtns().forEach((btn) => {
          const active = btn.dataset.dripView === mode;
          btn.classList.toggle('active', active);
          btn.classList.toggle('btn-primary', active);
          btn.classList.toggle('btn-outline-primary', !active);
        });
      }

      dripViewBtns().forEach((btn) => {
        btn.addEventListener('click', function () {
          const mode = this.dataset.dripView;
          if (mode === 'all' || mode === 'ends') {
            applyDripView(mode);
          }
        });
      });
      applyDripView('all');

      const mnPanel = document.getElementById('micro-needling-section');
      const mnGridItems = () => (mnPanel ? mnPanel.querySelectorAll('.micro-needling-grid-item') : []);
      const mnViewBtns = () => (mnPanel ? mnPanel.querySelectorAll('.micro-needling-view-btn') : []);

      function applyMicroNeedlingView(mode) {
        const items = mnGridItems();
        const n = items.length;
        items.forEach((el, i) => {
          if (mode === 'ends' && n > 1) {
            el.classList.toggle('d-none', i !== 0 && i !== n - 1);
          } else {
            el.classList.remove('d-none');
          }
        });
        mnViewBtns().forEach((btn) => {
          const active = btn.dataset.microNeedlingView === mode;
          btn.classList.toggle('active', active);
          btn.classList.toggle('btn-primary', active);
          btn.classList.toggle('btn-outline-primary', !active);
        });
      }

      mnViewBtns().forEach((btn) => {
        btn.addEventListener('click', function () {
          const mode = this.dataset.microNeedlingView;
          if (mode === 'all' || mode === 'ends') {
            applyMicroNeedlingView(mode);
          }
        });
      });
      applyMicroNeedlingView('all');

      document.querySelectorAll('.pkg-aggregate-form').forEach((form) => {
        const hidden = form.querySelector('.pkg-used-input');
        const boxes = Array.from(form.querySelectorAll('.pkg-session-cb'));
        if (!hidden || boxes.length === 0) {
          return;
        }
        boxes.forEach((cb) => {
          cb.addEventListener('change', () => {
            const idx = parseInt(cb.getAttribute('data-index'), 10);
            if (Number.isNaN(idx)) {
              return;
            }
            let used = parseInt(hidden.value, 10) || 0;
            if (cb.checked) {
              used = idx;
            } else {
              used = idx - 1;
            }
            used = Math.max(0, Math.min(boxes.length, used));
            hidden.value = String(used);
            boxes.forEach((b, j) => {
              b.checked = (j + 1) <= used;
            });
          });
        });
      });
    });
  </script>
@endsection
