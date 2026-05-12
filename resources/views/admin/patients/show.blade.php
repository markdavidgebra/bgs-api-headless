@extends('admin.layouts.master')

@section('content')
  @php
    $activeSubscription = $subscriptions->firstWhere('status', 'active');
  @endphp
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col-auto">
          <span class="avatar avatar-xl rounded bg-azure-lt text-azure">{{ $patient->initial }}</span>
        </div>
        <div class="col">
          <div class="page-pretitle text-secondary">Patient profile</div>
          <h2 class="page-title mb-1">{{ $patient->name ?? 'Patient' }}</h2>
          <ul class="list-inline list-inline-dots text-secondary mb-0">
            <li class="list-inline-item">{{ $patient->email ?? '—' }}</li>
            <li class="list-inline-item">{{ $patient->phone ?? '—' }}</li>
            <li class="list-inline-item"><span class="badge {{ $patient->status_badge }}">{{ ucfirst((string) $patient->status_label) }}</span></li>
          </ul>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <a href="{{ route('admin.patients') }}" class="btn">Back</a>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row row-deck row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
          <div class="card"><div class="card-body"><div class="subheader text-secondary">Age</div><div class="h3 mb-0">{{ $patient->age ?? '—' }}</div></div></div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card"><div class="card-body"><div class="subheader text-secondary">Gender</div><div class="h3 mb-0">{{ $patient->gender ?? '—' }}</div></div></div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card"><div class="card-body"><div class="subheader text-secondary">Active plan</div><div class="h3 mb-0 text-truncate" title="{{ $activeSubscription?->membershipPlan?->name ?? '—' }}">{{ $activeSubscription?->membershipPlan?->name ?? '—' }}</div></div></div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card"><div class="card-body"><div class="subheader text-secondary">Database records</div><div class="h3 mb-0">{{ $appointments->count() + $payments->count() + $subscriptions->count() + $patientPackages->count() }}</div></div></div>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-lg-8">
          <div class="card">
            <div class="card-header border-0">
              <ul class="nav nav-tabs card-header-tabs nav-fill bg-transparent" data-bs-toggle="tabs">
                <li class="nav-item"><a href="#tab-patient-overview" class="nav-link active" data-bs-toggle="tab">Overview</a></li>
                <li class="nav-item"><a href="#tab-patient-appointments" class="nav-link" data-bs-toggle="tab">Appointments</a></li>
                <li class="nav-item"><a href="#tab-patient-payments" class="nav-link" data-bs-toggle="tab">Payments</a></li>
                <li class="nav-item"><a href="#tab-patient-product-orders" class="nav-link" data-bs-toggle="tab">Product orders</a></li>
                <li class="nav-item"><a href="#tab-patient-subscription" class="nav-link" data-bs-toggle="tab">Subscriptions</a></li>
                <li class="nav-item"><a href="#tab-patient-packages" class="nav-link" data-bs-toggle="tab">Packages</a></li>
                <li class="nav-item"><a href="#tab-patient-extra" class="nav-link" data-bs-toggle="tab">Notes / Legacy</a></li>
              </ul>
            </div>
            <div class="card-body">
              <div class="tab-content">
                <div class="tab-pane active show" id="tab-patient-overview">
                  <div class="datagrid mb-0">
                    <div class="datagrid-item"><div class="datagrid-title">Name</div><div class="datagrid-content fw-medium">{{ $patient->name ?? '—' }}</div></div>
                    <div class="datagrid-item"><div class="datagrid-title">Email</div><div class="datagrid-content">@if ($patient->email)<a href="mailto:{{ $patient->email }}" class="text-reset">{{ $patient->email }}</a>@else — @endif</div></div>
                    <div class="datagrid-item"><div class="datagrid-title">Phone</div><div class="datagrid-content">{{ $patient->phone ?? '—' }}</div></div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Birthdate</div>
                      <div class="datagrid-content">{{ $patient->birthdate?->format('M j, Y') ?? '—' }}</div>
                    </div>
                    <div class="datagrid-item"><div class="datagrid-title">Address</div><div class="datagrid-content">{{ $patient->address ?? '—' }}</div></div>
                    <div class="datagrid-item"><div class="datagrid-title">Emergency contact</div><div class="datagrid-content">{{ $patient->emergency_contact ?? '—' }}</div></div>
                    <div class="datagrid-item"><div class="datagrid-title">History summary</div><div class="datagrid-content text-secondary">{{ $patient->history_summary ?? '—' }}</div></div>
                    <div class="datagrid-item"><div class="datagrid-title">Skin type</div><div class="datagrid-content">{{ $patient->skin_type ?? '—' }}</div></div>
                    <div class="datagrid-item"><div class="datagrid-title">Skin concerns</div><div class="datagrid-content">{{ $patient->skin_concerns ?? '—' }}</div></div>
                  </div>
                </div>

                <div class="tab-pane" id="tab-patient-appointments">
                  <div class="table-responsive">
                    <table class="table table-vcenter table-sm">
                      <thead>
                        <tr>
                          <th>Date</th>
                          <th>Time</th>
                          <th>No.</th>
                          <th>Service</th>
                          <th>Doctor</th>
                          <th>Status</th>
                          <th class="w-1"></th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse ($appointments as $a)
                          <tr>
                            <td>{{ $a->appointment_date?->format('M j, Y') ?? '—' }}</td>
                            <td>{{ $a->time_display }}</td>
                            <td class="font-monospace">{{ $a->appointment_no ?? '—' }}</td>
                            <td>{{ $a->service?->name ?? '—' }}</td>
                            <td>{{ $a->doctor?->name ?? '—' }}</td>
                            <td><span class="badge {{ $a->status_badge }}">{{ $a->status_label }}</span></td>
                            <td class="text-end text-nowrap">
                              <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#doctor-notes-modal-{{ $a->id }}">
                                View
                              </button>
                            </td>
                          </tr>
                        @empty
                          <tr><td colspan="7" class="text-secondary text-center py-4">No appointment records.</td></tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>

                  @foreach ($appointments as $a)
                    @php
                      $dn = $a->note;
                      $errApptId = session('appointment_note_error_id');
                      $noteField = static function (string $key) use ($a, $dn, $errApptId): string {
                          if ((int) $errApptId === (int) $a->id) {
                              return (string) old($key, '');
                          }

                          return (string) ($dn?->{$key} ?? '');
                      };
                      $noteSectionLabels = [
                          'patient_concern' => 'Patient concern',
                          'doctor_notes' => 'Doctor notes',
                          'instructions' => 'Instructions',
                          'alerts' => 'Alerts',
                          'appointment_remarks' => 'Appointment remarks',
                          'admin_notes' => 'Admin notes',
                      ];
                      $initialNote = [
                          'patient_concern' => $noteField('patient_concern'),
                          'doctor_notes' => $noteField('doctor_notes'),
                          'instructions' => $noteField('instructions'),
                          'alerts' => $noteField('alerts'),
                          'appointment_remarks' => $noteField('appointment_remarks'),
                          'admin_notes' => $noteField('admin_notes'),
                      ];
                      $defaultEditField = 'patient_concern';
                      if ($dn) {
                          foreach (array_keys($noteSectionLabels) as $k) {
                              if (filled($dn->{$k})) {
                                  $defaultEditField = $k;
                                  break;
                              }
                          }
                      }
                      $showEditOnLoad = (int) $errApptId === (int) $a->id;
                      $editFieldOnLoad = $showEditOnLoad ? (string) session('appointment_note_error_field', $defaultEditField) : null;
                      if ($editFieldOnLoad !== null && ! array_key_exists($editFieldOnLoad, $noteSectionLabels)) {
                          $editFieldOnLoad = $defaultEditField;
                      }
                      if (! $canManagePatientRecords) {
                          $showEditOnLoad = false;
                          $editFieldOnLoad = null;
                      }
                    @endphp
                    <div class="modal fade" id="doctor-notes-modal-{{ $a->id }}" tabindex="-1" aria-labelledby="doctor-notes-modal-label-{{ $a->id }}" aria-hidden="true" data-default-edit-field="{{ $defaultEditField }}" data-initial-note-json="{{ e(json_encode($initialNote)) }}">
                      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="doctor-notes-modal-label-{{ $a->id }}">
                              {{ $a->appointment_no ?? 'Appointment #'.$a->id }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <div id="appt-note-view-{{ $a->id }}" class="@if ($showEditOnLoad) d-none @endif">
                              @if ($dn)
                                @php
                                  $anySectionFilled = collect(array_keys($noteSectionLabels))->contains(fn (string $k): bool => filled($dn->{$k}));
                                @endphp
                                @foreach ($noteSectionLabels as $fieldKey => $fieldLabel)
                                  @if (filled($dn->{$fieldKey}))
                                    <div class="mb-3">
                                      <span class="text-secondary small">{{ $fieldLabel }}</span>
                                      @php
                                        $sectionAuthors = is_array($dn->section_authors) ? $dn->section_authors : [];
                                        $createdByLabel = \App\Models\AppointmentNote::creatorLabelForSection(
                                            $sectionAuthors,
                                            $fieldKey,
                                            $a->patient,
                                            $a->doctor,
                                        );
                                      @endphp
                                      <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                                        <div class="text-break flex-grow-1">{!! nl2br(e($dn->{$fieldKey})) !!}</div>
                                        <div class="d-flex flex-shrink-0 flex-column align-items-end gap-1">
                                          @if ($createdByLabel)
                                            <span class="text-secondary small text-end" style="max-width: 12rem;">{{ __('By :name', ['name' => $createdByLabel]) }}</span>
                                          @endif
                                          @if ($canManagePatientRecords)
                                            <div class="d-flex align-items-center gap-1 mb-0 flex-wrap justify-content-end">
                                              <button type="button" class="btn btn-link p-0 text-decoration-underline js-appt-note-open-edit" data-appt-id="{{ $a->id }}" data-focus-field="{{ $fieldKey }}">edit</button>
                                              <span class="text-secondary small">·</span>
                                              <form method="POST" action="{{ route('admin.patients.appointments.appointment-notes.field.destroy', [$patient->id, $a->id, $fieldKey]) }}" class="mb-0 d-inline" onsubmit="return confirm(@json(__('Remove this section?')));">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-link p-0 text-danger text-decoration-underline">delete</button>
                                              </form>
                                            </div>
                                          @endif
                                        </div>
                                      </div>
                                    </div>
                                  @endif
                                @endforeach
                                @if (! $anySectionFilled)
                                  <p class="text-secondary mb-0">No note text yet.</p>
                                @endif
                                @if ($dn->updated_at)
                                  <p class="text-secondary small mb-0 mt-2">{{ $dn->updated_at->timezone('Asia/Manila')->format('M j, Y h:i A') }}</p>
                                @endif
                              @else
                                <p class="text-secondary mb-0">No note yet.</p>
                              @endif
                              @if (! $dn && $canManagePatientRecords)
                                <p class="mb-0 mt-3">
                                  <button type="button" class="btn btn-link p-0 align-baseline text-decoration-underline js-appt-note-open-edit" data-appt-id="{{ $a->id }}" data-focus-field="patient_concern">add</button>
                                </p>
                              @endif
                            </div>
                            @if ($canManagePatientRecords)
                            <div id="appt-note-edit-{{ $a->id }}" class="@if (! $showEditOnLoad) d-none @endif">
                              @foreach ($noteSectionLabels as $activeFieldKey => $activeFieldLabel)
                                <form id="appt-note-form-{{ $a->id }}-{{ $activeFieldKey }}" class="appt-note-single-form @if (! $showEditOnLoad || $editFieldOnLoad !== $activeFieldKey) d-none @endif" method="POST" action="{{ route('admin.patients.appointments.appointment-notes.update', [$patient->id, $a->id]) }}">
                                  @csrf
                                  @method('PUT')
                                  <p class="text-secondary small mb-2">{{ $activeFieldLabel }}</p>
                                  @foreach ($noteSectionLabels as $k => $lbl)
                                    @if ($k === $activeFieldKey)
                                      <textarea id="appt-note-ta-{{ $a->id }}-{{ $activeFieldKey }}" name="{{ $k }}" class="form-control form-control-sm @if ((int) $errApptId === (int) $a->id && $errors->has($k)) is-invalid @endif" rows="{{ $k === 'doctor_notes' ? 5 : 4 }}">{{ $noteField($k) }}</textarea>
                                      @if ((int) $errApptId === (int) $a->id)
                                        @error($k)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                      @endif
                                    @else
                                      <input type="hidden" name="{{ $k }}" value="{{ e($noteField($k)) }}">
                                    @endif
                                  @endforeach
                                  <p class="mb-0 mt-2">
                                    <button type="submit" class="btn btn-link p-0 align-baseline text-decoration-underline">save</button>
                                    <span class="text-secondary"> · </span>
                                    <button type="button" class="btn btn-link p-0 align-baseline text-decoration-underline text-secondary js-appt-note-cancel-edit" data-appt-id="{{ $a->id }}">cancel</button>
                                  </p>
                                </form>
                              @endforeach
                            </div>
                            @endif
                          </div>
                          <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-link p-0 text-secondary" data-bs-dismiss="modal">close</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>

                <div class="tab-pane" id="tab-patient-payments">
                  <h4 class="mb-3">Payment Records</h4>
                  <div class="table-responsive">
                    <table class="table table-vcenter table-sm">
                      <thead>
                        <tr><th>Payment ID</th><th>Date</th><th>Reference</th><th>Item</th><th>Method</th><th>Status</th><th class="text-end">Amount</th></tr>
                      </thead>
                      <tbody>
                        @forelse ($payments as $p)
                          <tr>
                            <td class="font-monospace">{{ $p->payment_id }}</td>
                            <td>{{ $p->payment_date?->format('M j, Y') ?? '—' }}</td>
                            <td>{{ $p->reference_type_label }}</td>
                            <td>{{ $p->reference_name ?? '—' }}</td>
                            <td>{{ $p->method_label }}</td>
                            <td><span class="badge {{ $p->status_badge }}">{{ ucfirst((string) $p->payment_status) }}</span></td>
                            <td class="text-end font-monospace">{{ $p->formatted_amount }}</td>
                          </tr>
                        @empty
                          <tr><td colspan="7" class="text-secondary text-center py-4">No payment records.</td></tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>

                  <hr class="my-4">

                  <h4 class="mb-3">Appointment Payment Records</h4>
                  <div class="table-responsive">
                    <table class="table table-vcenter table-sm">
                      <thead>
                        <tr><th>Invoice</th><th>Appointment</th><th>Method</th><th>Status</th><th>Paid at</th><th class="text-end">Amount</th></tr>
                      </thead>
                      <tbody>
                        @forelse ($appointmentPayments as $ap)
                          <tr>
                            <td class="font-monospace">{{ $ap->invoice_no ?? '—' }}</td>
                            <td>{{ $ap->appointment?->appointment_no ?? '—' }}</td>
                            <td>{{ $ap->payment_method ?? '—' }}</td>
                            <td>{{ $ap->payment_status ?? '—' }}</td>
                            <td>{{ $ap->paid_at?->format('M j, Y H:i') ?? '—' }}</td>
                            <td class="text-end font-monospace">₱{{ number_format((float) $ap->amount, 2) }}</td>
                          </tr>
                        @empty
                          <tr><td colspan="6" class="text-secondary text-center py-4">No appointment payment records.</td></tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                </div>

                <div class="tab-pane" id="tab-patient-subscription">
                  <div class="table-responsive">
                    <table class="table table-vcenter table-sm">
                      <thead>
                        <tr><th>Plan</th><th>Start</th><th>Renewal</th><th>End</th><th>Status</th><th class="text-end">Used</th><th class="text-end">Remaining</th></tr>
                      </thead>
                      <tbody>
                        @forelse ($subscriptions as $sub)
                          <tr>
                            <td>{{ $sub->membershipPlan?->name ?? '—' }}</td>
                            <td>{{ $sub->start_date?->format('M j, Y') ?? '—' }}</td>
                            <td>{{ $sub->renewal_date?->format('M j, Y') ?? '—' }}</td>
                            <td>{{ $sub->end_date?->format('M j, Y') ?? '—' }}</td>
                            <td><span class="badge {{ $sub->status_badge }}">{{ ucfirst((string) $sub->status) }}</span></td>
                            <td class="text-end">{{ (int) $sub->sessions_used }}</td>
                            <td class="text-end">{{ (int) $sub->sessions_remaining }}</td>
                          </tr>
                        @empty
                          <tr><td colspan="7" class="text-secondary text-center py-4">No subscription records.</td></tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                </div>

                <div class="tab-pane" id="tab-patient-product-orders">
                  <h4 class="mb-3">Product Orders from POS / Payments</h4>
                  <div class="table-responsive">
                    <table class="table table-vcenter table-sm">
                      <thead>
                        <tr><th>Payment ID</th><th>Date</th><th>Product</th><th>SKU</th><th>Method</th><th>Status</th><th class="text-end">Amount</th></tr>
                      </thead>
                      <tbody>
                        @forelse ($productOrders as $order)
                          <tr>
                            <td class="font-monospace">{{ $order->payment_id }}</td>
                            <td>{{ $order->payment_date?->format('M j, Y') ?? '—' }}</td>
                            <td>{{ $order->referenceProduct?->name ?? $order->reference_name ?? '—' }}</td>
                            <td class="font-monospace">{{ $order->referenceProduct?->sku ?? '—' }}</td>
                            <td>{{ $order->method_label }}</td>
                            <td><span class="badge {{ $order->status_badge }}">{{ ucfirst((string) $order->payment_status) }}</span></td>
                            <td class="text-end font-monospace">{{ $order->formatted_amount }}</td>
                          </tr>
                        @empty
                          <tr><td colspan="7" class="text-secondary text-center py-4">No product orders found for this patient.</td></tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                </div>

                <div class="tab-pane" id="tab-patient-packages">
                  <h4 class="mb-3">Purchased Packages</h4>
                  <div class="table-responsive">
                    <table class="table table-vcenter table-sm">
                      <thead>
                        <tr><th>Package</th><th>Purchased</th><th>Status</th><th class="text-end">Total</th><th class="text-end">Used</th><th class="text-end">Remaining</th></tr>
                      </thead>
                      <tbody>
                        @forelse ($patientPackages as $pkg)
                          <tr>
                            <td>{{ $pkg->treatmentPackage?->name ?? '—' }}</td>
                            <td>{{ $pkg->purchased_at?->format('M j, Y') ?? '—' }}</td>
                            <td>{{ ucfirst((string) ($pkg->status ?? '—')) }}</td>
                            <td class="text-end">{{ (int) $pkg->total_sessions }}</td>
                            <td class="text-end">{{ (int) $pkg->used_sessions }}</td>
                            <td class="text-end">{{ (int) $pkg->remaining_sessions }}</td>
                          </tr>
                        @empty
                          <tr><td colspan="6" class="text-secondary text-center py-4">No package purchase records.</td></tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>

                  <hr class="my-4">

                  <h4 class="mb-3">Package Usage History</h4>
                  <div class="table-responsive">
                    <table class="table table-vcenter table-sm">
                      <thead>
                        <tr><th>Date</th><th>Package</th><th>Service</th><th>Status</th><th class="text-end">Session change</th><th>Notes</th></tr>
                      </thead>
                      <tbody>
                        @forelse ($packageUsageHistory as $u)
                          <tr>
                            <td>{{ $u->created_at?->timezone('Asia/Manila')->format('M j, Y h:i A') ?? $u->used_on?->timezone('Asia/Manila')->format('M j, Y') ?? '—' }}</td>
                            <td>{{ $u->patientPackage?->treatmentPackage?->name ?? '—' }}</td>
                            <td>{{ $u->service?->name ?? '—' }}</td>
                            <td>{{ $u->status ?? '—' }}</td>
                            <td class="text-end">{{ (int) $u->session_change }}</td>
                            <td>{{ $u->notes ?: '—' }}</td>
                          </tr>
                        @empty
                          <tr><td colspan="6" class="text-secondary text-center py-4">No package usage history.</td></tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                </div>

                <div class="tab-pane" id="tab-patient-extra">
                  <h4 class="mb-3">Legacy JSON Notes</h4>
                  @if (! empty($legacyNotes))
                    <div class="datagrid mb-4">
                      @foreach ($legacyNotes as $key => $value)
                        <div class="datagrid-item">
                          <div class="datagrid-title">{{ \Illuminate\Support\Str::headline((string) $key) }}</div>
                          <div class="datagrid-content">{{ is_scalar($value) ? (string) $value : json_encode($value) }}</div>
                        </div>
                      @endforeach
                    </div>
                  @else
                    <p class="text-secondary">No legacy JSON notes.</p>
                  @endif

                  <h4 class="mb-3">Legacy JSON Subscription</h4>
                  @if (! empty($legacySubscription))
                    <div class="datagrid mb-4">
                      @foreach ($legacySubscription as $key => $value)
                        <div class="datagrid-item">
                          <div class="datagrid-title">{{ \Illuminate\Support\Str::headline((string) $key) }}</div>
                          <div class="datagrid-content">{{ is_scalar($value) ? (string) $value : json_encode($value) }}</div>
                        </div>
                      @endforeach
                    </div>
                  @else
                    <p class="text-secondary">No legacy JSON subscription data.</p>
                  @endif

                  <h4 class="mb-3">Legacy Appointment History JSON</h4>
                  <div class="table-responsive">
                    <table class="table table-vcenter table-sm">
                      <thead><tr><th>Date</th><th>Time</th><th>Type</th><th>Status</th><th>Notes</th></tr></thead>
                      <tbody>
                        @forelse ($legacyAppointmentHistory as $h)
                          <tr>
                            <td>{{ $h['date'] ?? '—' }}</td>
                            <td>{{ $h['time'] ?? '—' }}</td>
                            <td>{{ $h['type'] ?? '—' }}</td>
                            <td>{{ $h['status'] ?? '—' }}</td>
                            <td>{{ $h['notes'] ?? '—' }}</td>
                          </tr>
                        @empty
                          <tr><td colspan="5" class="text-secondary text-center py-4">No legacy appointment history JSON.</td></tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="row row-cards">
            <div class="col-12">
              <div class="card">
                <div class="card-body">
                  <h3 class="card-title">Snapshot</h3>
                  <div class="datagrid mb-0">
                    <div class="datagrid-item"><div class="datagrid-title">Patient ID</div><div class="datagrid-content font-monospace">#{{ $patient->id }}</div></div>
                    <div class="datagrid-item"><div class="datagrid-title">Status</div><div class="datagrid-content"><span class="badge {{ $patient->status_badge }}">{{ ucfirst((string) $patient->status_label) }}</span></div></div>
                    <div class="datagrid-item"><div class="datagrid-title">Appointments</div><div class="datagrid-content">{{ number_format($appointments->count()) }}</div></div>
                    <div class="datagrid-item"><div class="datagrid-title">Payments</div><div class="datagrid-content">{{ number_format($payments->count()) }}</div></div>
                    <div class="datagrid-item"><div class="datagrid-title">Product orders</div><div class="datagrid-content">{{ number_format($productOrders->count()) }}</div></div>
                    <div class="datagrid-item"><div class="datagrid-title">Subscriptions</div><div class="datagrid-content">{{ number_format($subscriptions->count()) }}</div></div>
                    <div class="datagrid-item"><div class="datagrid-title">Package purchases</div><div class="datagrid-content">{{ number_format($patientPackages->count()) }}</div></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="card">
                <div class="card-body">
                  <h3 class="card-title">Quick actions</h3>
                  @if ($canManagePatientRecords)
                    <div class="btn-list">
                      <a href="{{ route('admin.patients.edit', $patient->id) }}" class="btn btn-outline-primary">Edit patient profile</a>
                      <form method="POST" action="{{ route('admin.patients.password.reset-link', $patient->id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">Send password reset link</button>
                      </form>
                    </div>
                    <hr class="my-3">
                    <form method="POST" action="{{ route('admin.patients.password.update', $patient->id) }}" class="row g-2">
                      @csrf
                      <div class="col-12">
                        <label class="form-label" for="patient-password">Set new password</label>
                        <input id="patient-password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="8" required>
                      </div>
                      <div class="col-12">
                        <label class="form-label" for="patient-password-confirmation">Confirm new password</label>
                        <input id="patient-password-confirmation" type="password" name="password_confirmation" class="form-control" minlength="8" required>
                      </div>
                      <div class="col-12">
                        <button type="submit" class="btn btn-primary">Update password</button>
                      </div>
                    </form>
                    @if (! $canManageStatus)
                      <div class="text-secondary small mt-2">Your role can edit this profile but cannot change patient account status.</div>
                    @endif
                  @else
                    <p class="text-secondary small mb-0">You have view-only access to this patient. Appointment notes and profile changes require the <strong>Patients (edit &amp; clinical notes)</strong> permission.</p>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var noteKeys = ['patient_concern', 'doctor_notes', 'instructions', 'alerts', 'appointment_remarks', 'admin_notes'];

      function applyInitialNoteToApptForms(apptId) {
        var modal = document.getElementById('doctor-notes-modal-' + apptId);
        if (!modal) return;
        var raw = modal.getAttribute('data-initial-note-json');
        if (!raw) return;
        var data;
        try {
          data = JSON.parse(raw);
        } catch (err) {
          return;
        }
        var editWrap = document.getElementById('appt-note-edit-' + apptId);
        if (!editWrap) return;
        editWrap.querySelectorAll('form.appt-note-single-form').forEach(function (form) {
          noteKeys.forEach(function (k) {
            if (!form.elements[k]) return;
            form.elements[k].value = data[k] != null ? data[k] : '';
          });
        });
      }

      function showApptNoteView(apptId) {
        var view = document.getElementById('appt-note-view-' + apptId);
        var edit = document.getElementById('appt-note-edit-' + apptId);
        if (edit) {
          edit.classList.add('d-none');
          edit.querySelectorAll('form.appt-note-single-form').forEach(function (f) {
            f.classList.add('d-none');
          });
        }
        if (view) view.classList.remove('d-none');
        applyInitialNoteToApptForms(apptId);
      }

      function showApptNoteEdit(apptId, focusField) {
        var modal = document.getElementById('doctor-notes-modal-' + apptId);
        var view = document.getElementById('appt-note-view-' + apptId);
        var edit = document.getElementById('appt-note-edit-' + apptId);
        var field = focusField || (modal && modal.getAttribute('data-default-edit-field')) || 'patient_concern';
        if (view) view.classList.add('d-none');
        if (edit) {
          edit.classList.remove('d-none');
          edit.querySelectorAll('form.appt-note-single-form').forEach(function (f) {
            f.classList.add('d-none');
          });
          var target = document.getElementById('appt-note-form-' + apptId + '-' + field);
          if (target) {
            target.classList.remove('d-none');
          } else {
            var fallback = document.getElementById('appt-note-form-' + apptId + '-patient_concern');
            if (fallback) fallback.classList.remove('d-none');
            field = 'patient_concern';
          }
        }
        requestAnimationFrame(function () {
          var ta = document.getElementById('appt-note-ta-' + apptId + '-' + field);
          if (ta) {
            ta.focus();
            ta.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
          }
        });
      }

      document.addEventListener('click', function (e) {
        var open = e.target.closest('.js-appt-note-open-edit');
        if (open) {
          e.preventDefault();
          var apptId = open.getAttribute('data-appt-id');
          var focusField = open.getAttribute('data-focus-field');
          showApptNoteEdit(apptId, focusField || null);
          return;
        }
        var cancel = e.target.closest('.js-appt-note-cancel-edit');
        if (cancel) {
          e.preventDefault();
          showApptNoteView(cancel.getAttribute('data-appt-id'));
          return;
        }
      });

      document.querySelectorAll('[id^="doctor-notes-modal-"]').forEach(function (modal) {
        modal.addEventListener('hidden.bs.modal', function () {
          var id = modal.id.replace('doctor-notes-modal-', '');
          if (!/^\d+$/.test(id)) return;
          showApptNoteView(id);
        });
      });

      var hash = window.location.hash;
      if (hash === '#tab-patient-appointments') {
        var tabTrigger = document.querySelector('a.nav-link[href="' + hash + '"]');
        if (tabTrigger && window.bootstrap && window.bootstrap.Tab) {
          new bootstrap.Tab(tabTrigger).show();
        }
      }
      @if (session()->has('appointment_note_error_id'))
        (function () {
          var errApptId = '{{ session('appointment_note_error_id') }}';
          var errField = @json(session('appointment_note_error_field', 'patient_concern'));
          var noteModal = document.getElementById('doctor-notes-modal-' + errApptId);
          if (!noteModal || !window.bootstrap || !window.bootstrap.Modal) return;
          noteModal.addEventListener('shown.bs.modal', function onShown() {
            noteModal.removeEventListener('shown.bs.modal', onShown);
            showApptNoteEdit(errApptId, errField);
          });
          var m = bootstrap.Modal.getInstance(noteModal) || new bootstrap.Modal(noteModal);
          m.show();
        })();
      @endif
    });
  </script>
@endpush