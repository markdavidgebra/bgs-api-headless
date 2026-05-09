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
                          <th>Date</th><th>Time</th><th>No.</th><th>Service</th><th>Doctor</th><th>Status</th>
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
                          </tr>
                        @empty
                          <tr><td colspan="6" class="text-secondary text-center py-4">No appointment records.</td></tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>

                  <hr class="my-4">

                  <h4 class="mb-3">Appointment Notes</h4>
                  <div class="table-responsive">
                    <table class="table table-vcenter table-sm">
                      <thead>
                        <tr><th>Appointment</th><th>Concern</th><th>Doctor notes</th><th>Alerts</th></tr>
                      </thead>
                      <tbody>
                        @forelse ($appointmentNotes as $n)
                          <tr>
                            <td>{{ $n->appointment?->appointment_no ?? '—' }}</td>
                            <td>{{ $n->patient_concern ?: '—' }}</td>
                            <td>{{ $n->doctor_notes ?: '—' }}</td>
                            <td>{{ $n->alerts ?: '—' }}</td>
                          </tr>
                        @empty
                          <tr><td colspan="4" class="text-secondary text-center py-4">No appointment notes.</td></tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
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
                            <td>{{ $u->used_on?->format('M j, Y') ?? '—' }}</td>
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
                  <div class="btn-list">
                    <a href="#" class="btn btn-outline-secondary disabled" aria-disabled="true">Book appointment</a>
                    <a href="#" class="btn btn-outline-secondary disabled" aria-disabled="true">Edit patient</a>
                  </div>
                  <div class="text-secondary small mt-2">Enable these when backend actions are ready.</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection