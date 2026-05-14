@extends('doctor.layouts.master')

@section('title', 'Appointment Details')

@section('content')
  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Doctor <span></span> Appointment Details
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
                    <h3 class="mb-0">Appointment #{{ $appointment->appointment_no }}</h3>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                      @if ($appointment->patient_id)
                        <a href="{{ route('doctor.patient-records.show', $appointment->patient_id) }}" class="btn btn-sm btn-outline-primary">Patient History</a>
                      @endif
                      <a href="{{ route('doctor.appointments') }}" class="btn btn-sm btn-outline">Back to list</a>
                    </div>
                  </div>

                  @if (session('success'))
                    <div class="alert alert-success mb-20">{{ session('success') }}</div>
                  @endif
                  @if (session('info'))
                    <div class="alert alert-info mb-20">{{ session('info') }}</div>
                  @endif

                  @php
                    $apptStatus = strtolower((string) ($appointment->status ?? ''));
                    $canStartSession = in_array($apptStatus, ['pending', 'rescheduled'], true);
                  @endphp
                  @if ($canStartSession)
                    <div class="mb-20">
                      <form method="POST" action="{{ route('doctor.appointments.start-session', $appointment) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary">{{ __('Start session') }}</button>
                      </form>
                    </div>
                  @endif

                  <div class="card mb-20">
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-6 mb-2"><strong>Date:</strong> {{ $appointment->date_display }}</div>
                        <div class="col-md-6 mb-2"><strong>Time:</strong> {{ $appointment->time_display }}</div>
                        <div class="col-md-6 mb-2"><strong>Patient:</strong> {{ $appointment->patient_name }}</div>
                        <div class="col-md-6 mb-2"><strong>Clinician:</strong> {{ $appointment->doctor_name }}</div>
                        <div class="col-md-6 mb-2"><strong>Service:</strong> {{ $appointment->service_name }}</div>
                        <div class="col-md-6 mb-2"><strong>Status:</strong> {{ $appointment->status_label }}</div>
                        <div class="col-md-6 mb-2">
                          <strong>Treatment package(s):</strong>
                          @if (($patientPackages ?? collect())->isEmpty())
                            —
                          @else
                            {{ $patientPackages->pluck('treatmentPackage.name')->filter()->unique()->implode(', ') ?: '—' }}
                          @endif
                        </div>
                        <div class="col-md-6 mb-2">
                          <form method="POST" action="{{ route('doctor.appointments.session-done', $appointment) }}" class="d-inline-block">
                            @csrf
                            <div class="form-check">
                              <input
                                class="form-check-input"
                                type="checkbox"
                                value="1"
                                id="session_done"
                                name="session_done"
                                onchange="this.form.submit()"
                                @checked(($appointment->status ?? '') === 'completed')
                              >
                              <label class="form-check-label" for="session_done">
                                Session done
                              </label>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="card mb-20">
                    <div class="card-header">
                      <h5 class="mb-0">Treatment progress</h5>
                    </div>
                    <div class="card-body">
                      @if ($patientPackage === null)
                        <div class="text-muted">
                          No linked treatment package found for this appointment service.
                        </div>
                      @else
                        <p class="mb-3">
                          <strong>Package:</strong> {{ $patientPackage->treatmentPackage?->name ?? 'Treatment package' }}<br>
                          <strong>Sessions:</strong> {{ (int) $patientPackage->used_sessions }} / {{ (int) $patientPackage->total_sessions }} done
                        </p>

                        <form method="POST" action="{{ route('doctor.appointments.treatment-progress', $appointment) }}">
                          @csrf
                          @php
                            $serviceGroups = collect($serviceChecklist)->groupBy('service_name');
                          @endphp
                          <div style="max-height: 320px; overflow:auto;">
                            @forelse ($serviceGroups as $serviceName => $serviceRows)
                              <div class="card mb-3 border">
                                <div class="card-header py-2 bg-light">
                                  <strong>{{ $serviceName }}</strong>
                                </div>
                                <div class="card-body py-2">
                                  @foreach ($serviceRows as $sessionRow)
                                    @php $sessionInputId = 'session-done-'.str_replace(':', '-', $sessionRow['key']); @endphp
                                    <div class="form-check mb-2">
                                      <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="checked_service_sessions[]"
                                        value="{{ $sessionRow['key'] }}"
                                        id="{{ $sessionInputId }}"
                                        @checked(in_array($sessionRow['key'], $checkedServiceSessionKeys, true))
                                      >
                                      <label class="form-check-label" for="{{ $sessionInputId }}">
                                        Session {{ $sessionRow['session_no'] }} of {{ $sessionRow['required_sessions'] }}
                                      </label>
                                    </div>
                                  @endforeach
                                </div>
                              </div>
                            @empty
                              <div class="text-muted">This package has no configured services/sessions.</div>
                            @endforelse
                          </div>
                          <div class="mt-3">
                            <button type="submit" class="btn btn-sm btn-primary">Save treatment progress</button>
                          </div>
                        </form>
                      @endif
                    </div>
                  </div>

                  @php
                    $notesCreateUrl = route('doctor.appointments.notes.create', $appointment);
                  @endphp
                  <div class="card">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                      <h5 class="mb-0">Treatment Notes</h5>
                      <a href="{{ $notesCreateUrl }}" class="btn btn-sm">Add notes</a>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <div class="d-flex justify-content-between align-items-baseline gap-2 mb-1">
                            <label class="form-label mb-0">Patient concern</label>
                            <a href="{{ $notesCreateUrl }}#patient_concern" class="btn btn-xs btn-outline flex-shrink-0">Add</a>
                          </div>
                          <div class="form-control bg-light" style="min-height: 44px;">
                            {{ optional($appointment->note)->patient_concern ?: '—' }}
                          </div>
                        </div>
                        <div class="col-md-6 mb-3">
                          <div class="d-flex justify-content-between align-items-baseline gap-2 mb-1">
                            <label class="form-label mb-0">Post procedures</label>
                            <a href="{{ $notesCreateUrl }}#appointment_remarks" class="btn btn-xs btn-outline flex-shrink-0">Add</a>
                          </div>
                          <div class="form-control bg-light" style="min-height: 44px;">
                            {{ optional($appointment->note)->appointment_remarks ?: '—' }}
                          </div>
                        </div>
                        <div class="col-md-6 mb-3">
                          <div class="d-flex justify-content-between align-items-baseline gap-2 mb-1">
                            <label class="form-label mb-0">Medical history</label>
                            <a href="{{ $notesCreateUrl }}#admin_notes" class="btn btn-xs btn-outline flex-shrink-0">Add</a>
                          </div>
                          <div class="form-control bg-light" style="min-height: 44px;">
                            {{ optional($appointment->note)->admin_notes ?: '—' }}
                          </div>
                        </div>
                        <div class="col-md-6 mb-3">
                          <div class="d-flex justify-content-between align-items-baseline gap-2 mb-1">
                            <label class="form-label mb-0">Doctor notes</label>
                            <a href="{{ $notesCreateUrl }}#doctor_notes" class="btn btn-xs btn-outline flex-shrink-0">Add</a>
                          </div>
                          <div class="form-control bg-light" style="min-height: 44px;">
                            {{ optional($appointment->note)->doctor_notes ?: '—' }}
                          </div>
                        </div>
                        <div class="col-md-6 mb-3">
                          <div class="d-flex justify-content-between align-items-baseline gap-2 mb-1">
                            <label class="form-label mb-0">Take home medications</label>
                            <a href="{{ $notesCreateUrl }}#instructions" class="btn btn-xs btn-outline flex-shrink-0">Add</a>
                          </div>
                          <div class="form-control bg-light" style="min-height: 44px;">
                            {{ optional($appointment->note)->instructions ?: '—' }}
                          </div>
                        </div>
                        <div class="col-md-6 mb-3">
                          <div class="d-flex justify-content-between align-items-baseline gap-2 mb-1">
                            <label class="form-label mb-0">Allergy</label>
                            <a href="{{ $notesCreateUrl }}#alerts" class="btn btn-xs btn-outline flex-shrink-0">Add</a>
                          </div>
                          <div class="form-control bg-light" style="min-height: 44px;">
                            {{ optional($appointment->note)->alerts ?: '—' }}
                          </div>
                        </div>
                        <div class="col-12 mb-3">
                          <div class="d-flex justify-content-between align-items-baseline gap-2 mb-1">
                            <label class="form-label mb-0">Vital signs</label>
                            <a href="{{ $notesCreateUrl }}#vital_blood_pressure" class="btn btn-xs btn-outline flex-shrink-0">Add</a>
                          </div>
                          <div class="form-control bg-light" style="min-height: 44px;">
                            {{ optional($appointment->note)->vitalSignsSummary() ?: '—' }}
                          </div>
                        </div>
                        @if (optional($appointment->note)->hasBodyAnalyzerImagePath())
                          <div class="col-12 col-md-4 mb-3 text-center">
                            <label class="form-label mb-0 d-block">Body analyzer</label>
                            @php($baShowUrl = $appointment->note->bodyAnalyzerImageUrl())
                            @if ($baShowUrl)
                              <div class="mt-2">
                                <img src="{{ $baShowUrl }}" alt="{{ __('Body analyzer image') }}" class="img-thumbnail mx-auto d-block shadow-sm" style="max-width: min(100%, 360px); max-height: 400px; width: auto; height: auto; object-fit: contain;">
                              </div>
                            @else
                              <p class="small text-warning mt-2 mb-0">{{ __('Body analyzer image is recorded but the file is missing on the server.') }}</p>
                            @endif
                          </div>
                        @endif
                        @if (optional($appointment->note)->hasBottleCitrusImagePath())
                          <div class="col-12 col-md-4 mb-3 text-center">
                            <label class="form-label mb-0 d-block">Bottle citrus</label>
                            @php($bcShowUrl = $appointment->note->bottleCitrusImageUrl())
                            @if ($bcShowUrl)
                              <div class="mt-2">
                                <img src="{{ $bcShowUrl }}" alt="{{ __('Bottle citrus image') }}" class="img-thumbnail mx-auto d-block shadow-sm" style="max-width: min(100%, 360px); max-height: 400px; width: auto; height: auto; object-fit: contain;">
                              </div>
                            @else
                              <p class="small text-warning mt-2 mb-0">{{ __('Bottle citrus image is recorded but the file is missing on the server.') }}</p>
                            @endif
                          </div>
                        @endif
                        @if (optional($appointment->note)->hasLemonBottleImagePath())
                          <div class="col-12 col-md-4 mb-3 text-center">
                            <label class="form-label mb-0 d-block">Lemon bottle</label>
                            @php($lbShowUrl = $appointment->note->lemonBottleImageUrl())
                            @if ($lbShowUrl)
                              <div class="mt-2">
                                <img src="{{ $lbShowUrl }}" alt="{{ __('Lemon bottle image') }}" class="img-thumbnail mx-auto d-block shadow-sm" style="max-width: min(100%, 360px); max-height: 400px; width: auto; height: auto; object-fit: contain;">
                              </div>
                            @else
                              <p class="small text-warning mt-2 mb-0">{{ __('Lemon bottle image is recorded but the file is missing on the server.') }}</p>
                            @endif
                          </div>
                        @endif
                        @if (optional($appointment->note)->hasAqualyxImagePath())
                          <div class="col-12 col-md-4 mb-3 text-center">
                            <label class="form-label mb-0 d-block">Aqualyx</label>
                            @php($aqShowUrl = $appointment->note->aqualyxImageUrl())
                            @if ($aqShowUrl)
                              <div class="mt-2">
                                <img src="{{ $aqShowUrl }}" alt="{{ __('Aqualyx image') }}" class="img-thumbnail mx-auto d-block shadow-sm" style="max-width: min(100%, 360px); max-height: 400px; width: auto; height: auto; object-fit: contain;">
                              </div>
                            @else
                              <p class="small text-warning mt-2 mb-0">{{ __('Aqualyx image is recorded but the file is missing on the server.') }}</p>
                            @endif
                          </div>
                        @endif
                        @if (optional($appointment->note)->hasDripImagePath())
                          <div class="col-12 col-md-4 mb-3 text-center">
                            <label class="form-label mb-0 d-block">Drip</label>
                            @php($dripShowUrl = $appointment->note->dripImageUrl())
                            @if ($dripShowUrl)
                              <div class="mt-2">
                                <img src="{{ $dripShowUrl }}" alt="{{ __('Drip image') }}" class="img-thumbnail mx-auto d-block shadow-sm" style="max-width: min(100%, 360px); max-height: 400px; width: auto; height: auto; object-fit: contain;">
                              </div>
                            @else
                              <p class="small text-warning mt-2 mb-0">{{ __('Drip image is recorded but the file is missing on the server.') }}</p>
                            @endif
                          </div>
                        @endif
                        @if (optional($appointment->note)->hasMicroNeedlingImagePath())
                          <div class="col-12 col-md-4 mb-3 text-center">
                            <label class="form-label mb-0 d-block">Micro needling</label>
                            @php($mnShowUrl = $appointment->note->microNeedlingImageUrl())
                            @if ($mnShowUrl)
                              <div class="mt-2">
                                <img src="{{ $mnShowUrl }}" alt="{{ __('Micro needling image') }}" class="img-thumbnail mx-auto d-block shadow-sm" style="max-width: min(100%, 360px); max-height: 400px; width: auto; height: auto; object-fit: contain;">
                              </div>
                            @else
                              <p class="small text-warning mt-2 mb-0">{{ __('Micro needling image is recorded but the file is missing on the server.') }}</p>
                            @endif
                          </div>
                        @endif
                      </div>
                    </div>
                  </div>

                  @if ($appointment->prescribedProducts->isNotEmpty())
                    <div class="card mt-20">
                      <div class="card-header">
                        <h5 class="mb-0">Prescribed products</h5>
                      </div>
                      <div class="card-body p-0">
                        <div class="table-responsive">
                          <table class="table table-striped mb-0">
                            <thead>
                              <tr>
                                <th>Product</th>
                                <th class="text-center">Qty</th>
                              </tr>
                            </thead>
                            <tbody>
                              @foreach ($appointment->prescribedProducts as $p)
                                <tr>
                                  <td>{{ $p->name }}</td>
                                  <td class="text-center">{{ (int) ($p->pivot->quantity ?? 1) }}</td>
                                </tr>
                              @endforeach
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
@endsection
