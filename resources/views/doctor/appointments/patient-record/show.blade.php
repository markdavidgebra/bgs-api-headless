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
                    <button type="button" class="tab-btn active" data-target="tab-overview">Overview</button>
                    <button type="button" class="tab-btn" data-target="tab-notes">Treatment Notes</button>
                    <button type="button" class="tab-btn" data-target="tab-appointments">Appointments</button>
                    <button type="button" class="tab-btn" data-target="tab-packages">Packages / Memberships</button>
                    <button type="button" class="tab-btn" data-target="tab-payments">Payments</button>
                    <button type="button" class="tab-btn" data-target="tab-body-analyzer">Images</button>
                  </div>

                  <div class="card tab-panel active" id="tab-overview">
                    <div class="card-header"><h5 class="mb-0">Overview</h5></div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-6 mb-2"><strong>Patient:</strong> {{ $patient->name }}</div>
                        <div class="col-md-6 mb-2"><strong>Email:</strong> {{ $patient->email ?? '—' }}</div>
                        <div class="col-md-6 mb-2"><strong>Contact:</strong> {{ $patient->phone ?? '—' }}</div>
                        <div class="col-md-6 mb-2"><strong>Birthdate:</strong> {{ $patient->birthdate?->format('Y-m-d') ?? '—' }}</div>
                        <div class="col-md-6 mb-2"><strong>Gender:</strong> {{ $patient->gender ?? '—' }}</div>
                        <div class="col-md-6 mb-2"><strong>Address:</strong> {{ $patient->address ?? '—' }}</div>
                        <div class="col-md-6 mb-2"><strong>Status:</strong> {{ ucfirst((string) ($patient->status ?? 'active')) }}</div>
                        <div class="col-md-6 mb-2"><strong>Last visit (with you):</strong>
                          @if ($lastVisit)
                            {{ $lastVisit->date_display }} {{ $lastVisit->time_display }}
                          @else
                            —
                          @endif
                        </div>
                        <div class="col-md-6 mb-2"><strong>Total visits (with you):</strong> {{ $totalVisits }}</div>
                        <div class="col-12 mt-2"><strong>Notes summary (clinic):</strong>
                          @php
                            $summary = $latestNote ? $latestNote->treatmentSummarySnippet(180) : '';
                          @endphp
                          {{ $summary !== '' ? $summary : 'No notes yet.' }}</div>
                        <div class="col-12 mt-2"><strong>Allergy:</strong> {{ $latestAlerts ?: 'None' }}</div>
                      </div>
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

                  <div class="card tab-panel" id="tab-appointments">
                    <div class="card-header"><h5 class="mb-0">Appointments</h5></div>
                    <div class="card-body">
                      <h6 class="mb-10">Upcoming Appointments</h6>
                      <ul class="mb-20 ps-3">
                        @forelse ($upcomingAppointments as $appointment)
                          <li class="mb-2">{{ $appointment->date_display }} {{ $appointment->time_display }} — {{ $appointment->doctor_name }} — {{ $appointment->service_name }} ({{ $appointment->status_label }})</li>
                        @empty
                          <li>No upcoming appointments.</li>
                        @endforelse
                      </ul>

                      <h6 class="mb-10">Past Appointments</h6>
                      <ul class="mb-0 ps-3">
                        @forelse ($pastAppointments as $appointment)
                          <li class="mb-2">{{ $appointment->date_display }} {{ $appointment->time_display }} — {{ $appointment->doctor_name }} — {{ $appointment->service_name }} ({{ $appointment->status_label }})</li>
                        @empty
                          <li>No past appointments.</li>
                        @endforelse
                      </ul>
                    </div>
                  </div>

                  <div class="card tab-panel" id="tab-packages">
                    <div class="card-header"><h5 class="mb-0">Packages / Memberships</h5></div>
                    <div class="card-body">
                      <h6 class="mb-10">Packages</h6>
                      <div class="table-responsive mb-20">
                        <table class="table mb-0">
                          <thead>
                            <tr><th>Plan Name</th><th>Sessions Used</th><th>Sessions Remaining</th><th>Expiry Date</th></tr>
                          </thead>
                          <tbody>
                            @forelse ($packages as $package)
                              <tr>
                                <td>{{ $package->treatmentPackage->name ?? '—' }}</td>
                                <td>{{ (int) $package->used_sessions }}</td>
                                <td>{{ (int) $package->remaining_sessions }}</td>
                                <td>{{ $package->end_date?->format('Y-m-d') ?? '—' }}</td>
                              </tr>
                            @empty
                              <tr><td colspan="4" class="text-center text-secondary py-4">No package records.</td></tr>
                            @endforelse
                          </tbody>
                        </table>
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
      buttons.forEach((button) => {
        button.addEventListener('click', () => {
          buttons.forEach((b) => b.classList.remove('active'));
          panels.forEach((p) => p.classList.remove('active'));
          button.classList.add('active');
          const panel = document.getElementById(button.dataset.target);
          if (panel) panel.classList.add('active');
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
    });
  </script>
@endsection
