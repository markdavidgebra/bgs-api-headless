@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Overview</div>
          <h2 class="page-title mb-0">Dashboard</h2>
          <div class="text-secondary small mt-1">Summary first—open a module for full detail.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list flex-wrap">
            <a href="{{ route('admin.appointments') }}" class="btn btn-sm btn-primary">Appointments</a>
            <a href="{{ route('admin.slides') }}" class="btn btn-sm btn-outline-primary">Homepage slides</a>
            <a href="{{ route('admin.reports') }}" class="btn btn-sm">Reports</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body admin-dashboard-layout">
    <style>
      /* Equal-width columns when multiple widgets visible; full width when only one */
      .admin-dashboard-layout .dashboard-flex-row > .dashboard-widget {
        min-width: 0;
      }
      @media (min-width: 768px) {
        .admin-dashboard-layout .dashboard-flex-row > .dashboard-widget.col-md {
          flex: 1 1 0;
        }
        .admin-dashboard-layout .dashboard-flex-row > .dashboard-widget-stack.col-md {
          flex: 1 1 0;
          min-width: min(100%, 20rem);
        }
      }
      .admin-dashboard-layout .dashboard-widget .card.h-100 {
        min-height: 0;
      }
    </style>
    <div class="container-xl">
      {{-- Dashboard visibility (saved in this browser) --}}
      <div class="card mb-3 d-print-none border-secondary-lt">
        <div class="card-body py-2">
          <div class="d-flex flex-wrap align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-layout-dashboard text-secondary" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 4h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-6a1 1 0 0 1 1 -1" /><path d="M5 16h4a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-2a1 1 0 0 1 1 -1" /><path d="M15 12h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-6a1 1 0 0 1 1 -1" /><path d="M15 4h4a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-2a1 1 0 0 1 1 -1" /></svg>
              <span class="fw-medium">Dashboard layout</span>
            </div>
            <div class="dropdown">
              <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                Show / hide sections
              </button>
              <div class="dropdown-menu dropdown-menu-start shadow-lg p-3" style="min-width: 20rem; max-height: 70vh; overflow-y: auto;" onclick="event.stopPropagation()">
                <div class="text-secondary small mb-2">Choose visible cards. Your choices are saved in this browser.</div>
                <div class="vstack gap-2" id="dashboard-widget-toggles">
                  @foreach ([
                    ['id' => 'quick_actions', 'label' => 'Quick actions'],
                    ['id' => 'kpi_stats', 'label' => 'KPI summary (6 tiles)'],
                    ['id' => 'today_schedule', 'label' => "Today's schedule"],
                    ['id' => 'needs_attention', 'label' => 'Needs attention'],
                    ['id' => 'revenue_snapshot', 'label' => 'Revenue snapshot'],
                    ['id' => 'appointments_status', 'label' => 'Appointments by status'],
                    ['id' => 'membership_overview', 'label' => 'Membership overview'],
                    ['id' => 'doctor_activity', 'label' => 'Clinical staff activity today'],
                    ['id' => 'top_services', 'label' => 'Top services'],
                    ['id' => 'recent_payments', 'label' => 'Recent payments'],
                    ['id' => 'new_patients', 'label' => 'New patients'],
                    ['id' => 'new_patient_appts', 'label' => 'New patient appointments (30d)'],
                    ['id' => 'low_stock', 'label' => 'Low stock'],
                    ['id' => 'out_of_stock', 'label' => 'Out of stock'],
                    ['id' => 'expiring_memberships', 'label' => 'Expiring memberships'],
                    ['id' => 'stock_movements', 'label' => 'Recent stock movements'],
                  ] as $dw)
                    <label class="form-check form-check-sm m-0">
                      <input type="checkbox" class="form-check-input" data-dashboard-widget-toggle="{{ $dw['id'] }}" id="dw-toggle-{{ $dw['id'] }}" checked>
                      <span class="form-check-label">{{ $dw['label'] }}</span>
                    </label>
                  @endforeach
                </div>
                <button type="button" class="btn btn-sm btn-link text-secondary px-0 mt-3" id="dashboard-widgets-reset">Show all sections</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Quick actions --}}
      <div class="dashboard-widget mb-3" data-dashboard-widget="quick_actions">
      <div class="card mb-0">
        <div class="card-header">
          <h3 class="card-title">Quick actions</h3>
        </div>
        <div class="card-body py-2">
          <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.slides') }}" class="btn btn-outline-primary btn-sm">Homepage hero slides
              <span class="badge bg-secondary-lt ms-1">{{ number_format($heroSlidesCount) }}</span>
            </a>
            <a href="{{ route('admin.appointments') }}" class="btn btn-outline-primary btn-sm">Add / view appointments</a>
            <a href="{{ route('admin.patients') }}" class="btn btn-outline-primary btn-sm">Patients</a>
            <a href="{{ route('register') }}" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener">Register patient</a>
            <a href="{{ route('admin.services.create') }}" class="btn btn-outline-primary btn-sm">Add service</a>
            <a href="{{ route('admin.products.create') }}" class="btn btn-outline-primary btn-sm">Add product</a>
            <a href="{{ route('admin.payments.create') }}" class="btn btn-outline-primary btn-sm">Add payment</a>
            <a href="{{ route('admin.promotions.create') }}" class="btn btn-outline-primary btn-sm">Create promotion</a>
          </div>
        </div>
      </div>
      </div>

      {{-- Row 1: KPI cards --}}
      <div class="dashboard-widget mb-3" data-dashboard-widget="kpi_stats">
      <div class="row row-cards mb-0">
        <div class="col-sm-6 col-lg-4 col-xl-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Today's appointments</div>
              <div class="h3 mb-0">{{ number_format($todayAppointments) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-4 col-xl-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Pending appointments</div>
              <div class="h3 mb-0">{{ number_format($pendingAppointments) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-4 col-xl-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Total patients</div>
              <div class="h3 mb-0">{{ number_format($totalPatients) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-4 col-xl-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Active memberships</div>
              <div class="h3 mb-0">{{ number_format($activeMemberships) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-4 col-xl-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Low stock products</div>
              <div class="h3 mb-0">{{ number_format($lowStockProducts) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-4 col-xl-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Today / month revenue</div>
              <div class="h3 mb-0">₱{{ number_format($todayRevenue, 2) }}</div>
              <div class="text-secondary small">Month: ₱{{ number_format($monthlyRevenue, 2) }}</div>
            </div>
          </div>
        </div>
      </div>
      </div>

      <div class="row row-cards mb-3 g-3 dashboard-flex-row">
        {{-- Today's schedule --}}
        <div class="col-12 col-md dashboard-widget" data-dashboard-widget="today_schedule">
          <div class="card h-100">
            <div class="card-header d-flex align-items-center">
              <h3 class="card-title mb-0">Today's schedule</h3>
              <a href="{{ route('admin.appointments') }}" class="btn btn-sm ms-auto">View all</a>
            </div>
            <div class="card-body border-bottom py-3">
              <div class="text-secondary small mb-2">Volume by hour (6am–8pm)</div>
              <div id="dashboard-chart-schedule-hour" class="apex-chart"></div>
            </div>
            <div class="table-responsive">
              <table class="table table-vcenter card-table table-sm">
                <thead>
                  <tr>
                    <th>Patient</th>
                    <th>Service</th>
                    <th>Clinical staff</th>
                    <th>Time</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($todaysSchedule as $appt)
                    <tr>
                      <td>
                        <a href="{{ route('admin.patients.show', $appt->patient_id) }}">{{ $appt->patient->name ?? '—' }}</a>
                      </td>
                      <td>{{ $appt->service->name ?? '—' }}</td>
                      <td>{{ $appt->clinicalStaff->name ?? '—' }}</td>
                      <td class="font-monospace">{{ $appt->time_display }}</td>
                      <td><span class="badge {{ $appt->status_badge }}">{{ $appt->status_label }}</span></td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="5" class="text-secondary text-center py-3">No appointments today.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>

        {{-- Pending actions --}}
        <div class="col-12 col-md dashboard-widget" data-dashboard-widget="needs_attention">
          <div class="card h-100">
            <div class="card-header">
              <h3 class="card-title">Needs attention</h3>
            </div>
            <div class="card-body border-bottom py-2">
              <div id="dashboard-chart-needs-attention" class="apex-chart"></div>
            </div>
            <div class="list-group list-group-flush">
              <a href="{{ route('admin.appointments') }}?status=pending" class="list-group-item list-group-item-action d-flex justify-content-between">
                <span>Pending appointments</span>
                <span class="badge bg-yellow-lt">{{ number_format($pendingAppointments) }}</span>
              </a>
              <a href="{{ route('admin.reports.subscriptions') }}" class="list-group-item list-group-item-action d-flex justify-content-between">
                <span>Memberships expiring (30 days)</span>
                <span class="badge bg-azure-lt">{{ number_format($expiringMembershipsCount) }}</span>
              </a>
              <a href="{{ route('admin.payments') }}?payment_status=unpaid" class="list-group-item list-group-item-action d-flex justify-content-between">
                <span>Unpaid payments</span>
                <span class="badge bg-red-lt">{{ number_format($unpaidPaymentsCount) }}</span>
              </a>
              <a href="{{ route('admin.products.inventory') }}" class="list-group-item list-group-item-action d-flex justify-content-between">
                <span>Low stock (at/below alert)</span>
                <span class="badge bg-secondary-lt">{{ number_format($lowStockProducts) }}</span>
              </a>
              <a href="{{ route('admin.appointments') }}?status=cancelled" class="list-group-item list-group-item-action d-flex justify-content-between">
                <span>Recent cancelled (review)</span>
                <span class="badge bg-secondary-lt">{{ number_format($cancelledReviewCount) }}</span>
              </a>
            </div>
            <div class="card-footer text-secondary small">
              Renewals due (14 days): <strong>{{ number_format($renewalsDueCount) }}</strong>
            </div>
          </div>
        </div>
      </div>

      <div class="row row-cards mb-3 g-3 dashboard-flex-row">
        {{-- Revenue snapshot --}}
        <div class="col-12 col-md dashboard-widget" data-dashboard-widget="revenue_snapshot">
          <div class="card h-100">
            <div class="card-header d-flex align-items-center">
              <h3 class="card-title mb-0">Revenue snapshot</h3>
              <a href="{{ route('admin.reports.revenue') }}" class="btn btn-sm ms-auto">Detail</a>
            </div>
            <div class="card-body">
              <div class="row g-3 mb-2">
                <div class="col-4">
                  <div class="text-secondary small">Today</div>
                  <div class="fw-semibold">₱{{ number_format($todayRevenue, 2) }}</div>
                </div>
                <div class="col-4">
                  <div class="text-secondary small">This week</div>
                  <div class="fw-semibold">₱{{ number_format($weekRevenue, 2) }}</div>
                </div>
                <div class="col-4">
                  <div class="text-secondary small">This month</div>
                  <div class="fw-semibold">₱{{ number_format($monthlyRevenue, 2) }}</div>
                </div>
              </div>
              <div class="text-secondary small mb-2">Last 7 days (paid + partial)</div>
              <div id="dashboard-chart-revenue" class="apex-chart"></div>
            </div>
          </div>
        </div>

        {{-- Appointment status overview --}}
        <div class="col-12 col-lg dashboard-widget" data-dashboard-widget="appointments_status">
          <div class="card h-100">
            <div class="card-header d-flex align-items-center">
              <h3 class="card-title mb-0">Appointments by status</h3>
              <a href="{{ route('admin.reports.appointments') }}" class="btn btn-sm ms-auto">Report</a>
            </div>
            <div class="card-body">
              @php
                $statusColorTokens = [
                  'confirmed' => 'green',
                  'pending' => 'yellow',
                  'completed' => 'azure',
                  'cancelled' => 'red',
                  'rescheduled' => 'orange',
                ];
                $appointmentStatusChart = collect($appointmentStatusOrder)
                  ->map(fn ($st) => [
                    'label' => ucfirst($st),
                    'value' => (int) ($appointmentStatusCounts[$st] ?? 0),
                    'colorToken' => $statusColorTokens[$st] ?? 'secondary',
                  ])
                  ->filter(fn ($row) => $row['value'] > 0)
                  ->values();
              @endphp
              <div id="dashboard-chart-appointments-status" class="apex-chart"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="row row-cards mb-3 g-3 dashboard-flex-row">
        {{-- Membership overview --}}
        <div class="col-12 col-md dashboard-widget" data-dashboard-widget="membership_overview">
          <div class="card h-100">
            <div class="card-header">
              <h3 class="card-title">Membership overview</h3>
            </div>
            <div class="card-body">
              <ul class="list-unstyled space-y-2 mb-0">
                <li class="d-flex justify-content-between"><span class="text-secondary">Active</span><strong>{{ number_format($activeMemberships) }}</strong></li>
                <li class="d-flex justify-content-between"><span class="text-secondary">Expiring soon (30d)</span><strong>{{ number_format($expiringMembershipsCount) }}</strong></li>
                <li class="d-flex justify-content-between"><span class="text-secondary">Renewals due (14d)</span><strong>{{ number_format($renewalsDueCount) }}</strong></li>
                <li class="d-flex justify-content-between"><span class="text-secondary">New this month</span><strong>{{ number_format($newSubscribersThisMonth) }}</strong></li>
              </ul>
              <div class="text-secondary small mt-3 mb-2">At a glance</div>
              <div id="dashboard-chart-membership" class="apex-chart"></div>
              <a href="{{ route('admin.reports.subscriptions') }}" class="btn btn-sm btn-outline-primary w-100 mt-3">Subscriptions report</a>
            </div>
          </div>
        </div>

        {{-- Clinical staff activity (today) --}}
        <div class="col-12 col-md dashboard-widget" data-dashboard-widget="doctor_activity">
          <div class="card h-100">
            <div class="card-header">
              <h3 class="card-title">Clinical staff activity today</h3>
            </div>
            <div class="card-body">
              <div class="text-secondary small mb-2">
                {{ number_format($doctorsOnDutyToday) }} doctor(s) with appointments · busiest: <strong>{{ $topDoctorTodayName }}</strong>
              </div>
              <div id="dashboard-chart-doctors" class="apex-chart mb-3"></div>
              <div class="table-responsive">
                <table class="table table-sm table-vcenter mb-0">
                  <thead>
                    <tr><th>Clinical staff</th><th class="text-end">Today</th></tr>
                  </thead>
                  <tbody>
                    @forelse ($doctorActivityToday as $dr)
                      <tr>
                        <td>{{ $dr->name }}</td>
                        <td class="text-end">{{ number_format((int) $dr->appointment_count) }}</td>
                      </tr>
                    @empty
                      <tr><td colspan="2" class="text-secondary">No appointments today.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        {{-- Most booked services --}}
        <div class="col-12 col-md dashboard-widget" data-dashboard-widget="top_services">
          <div class="card h-100">
            <div class="card-header d-flex align-items-center">
              <h3 class="card-title mb-0">Top services</h3>
              <a href="{{ route('admin.reports.services') }}" class="btn btn-sm ms-auto">Report</a>
            </div>
            <div class="card-body">
              <div id="dashboard-chart-top-services" class="apex-chart"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="row row-cards mb-3 g-3 dashboard-flex-row">
        {{-- Recent payments --}}
        <div class="col-12 col-md dashboard-widget" data-dashboard-widget="recent_payments">
          <div class="card h-100">
            <div class="card-header d-flex align-items-center">
              <h3 class="card-title mb-0">Recent payments</h3>
              <a href="{{ route('admin.payments') }}" class="btn btn-sm ms-auto">All</a>
            </div>
            <div class="card-body border-bottom py-3">
              <div class="text-secondary small mb-2">Amounts (oldest → newest in this list)</div>
              <div id="dashboard-chart-recent-payments" class="apex-chart"></div>
            </div>
            <div class="table-responsive">
              <table class="table table-vcenter card-table table-sm">
                <thead>
                  <tr>
                    <th>Patient</th>
                    <th>Type</th>
                    <th class="text-end">Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($recentPayments as $pay)
                    <tr>
                      <td>{{ $pay->patient->name ?? '—' }}</td>
                      <td>{{ $pay->reference_type_label }}</td>
                      <td class="text-end">₱{{ number_format((float) $pay->amount, 2) }}</td>
                      <td>{{ $pay->method_label }}</td>
                      <td><span class="badge {{ $pay->status_badge }}">{{ ucfirst((string) $pay->payment_status) }}</span></td>
                      <td class="text-secondary small">{{ $pay->payment_date?->format('M j, Y') ?? '—' }}</td>
                    </tr>
                  @empty
                    <tr><td colspan="6" class="text-secondary text-center py-3">No payments yet.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>

        {{-- New patients & their appointments --}}
        <div class="col-12 col-md dashboard-widget-stack d-flex flex-column gap-3">
          <div class="card dashboard-widget" data-dashboard-widget="new_patients">
            <div class="card-header d-flex align-items-center">
              <h3 class="card-title mb-0">New patients</h3>
              <a href="{{ route('admin.reports.patients') }}" class="btn btn-sm ms-auto">Report</a>
            </div>
            <div class="card-body border-bottom py-3">
              <div class="text-secondary small mb-2">Registrations (last 7 days)</div>
              <div id="dashboard-chart-new-patients" class="apex-chart"></div>
            </div>
            <div class="table-responsive">
              <table class="table table-vcenter card-table table-sm">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Registered</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($newPatientsList as $np)
                    <tr>
                      <td><a href="{{ route('admin.patients.show', $np->id) }}">{{ $np->name }}</a></td>
                      <td class="text-secondary small">{{ $np->created_at?->format('M j, Y') ?? '—' }}</td>
                    </tr>
                  @empty
                    <tr><td colspan="2" class="text-secondary text-center py-2">No patients.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
          <div class="card mb-0 dashboard-widget" data-dashboard-widget="new_patient_appts">
            <div class="card-header">
              <h3 class="card-title">Recent appointments · new patients (30d)</h3>
            </div>
            <div class="table-responsive">
              <table class="table table-vcenter card-table table-sm">
                <thead>
                  <tr>
                    <th>Patient</th>
                    <th>Service</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($newPatientAppointments as $na)
                    <tr>
                      <td>{{ $na->patient->name ?? '—' }}</td>
                      <td>{{ $na->service->name ?? '—' }}</td>
                      <td class="text-secondary small">{{ $na->appointment_date?->format('M j, Y') ?? '—' }}</td>
                    </tr>
                  @empty
                    <tr><td colspan="3" class="text-secondary text-center py-2">No recent appointments for new patients.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="row row-cards mb-3 g-3 dashboard-flex-row">
        {{-- Inventory --}}
        <div class="col-12 col-md dashboard-widget-stack d-flex flex-column gap-3">
          <div class="card dashboard-widget" data-dashboard-widget="low_stock">
            <div class="card-header d-flex align-items-center">
              <h3 class="card-title mb-0">Low stock</h3>
              <a href="{{ route('admin.products.inventory') }}" class="btn btn-sm ms-auto">Inventory</a>
            </div>
            <div class="card-body border-bottom py-3">
              <div class="text-secondary small mb-2">Quantity on hand</div>
              <div id="dashboard-chart-low-stock" class="apex-chart"></div>
            </div>
            <div class="table-responsive">
              <table class="table table-vcenter card-table table-sm">
                <thead>
                  <tr>
                    <th>Product</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Alert</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($lowStockItems as $p)
                    <tr>
                      <td><a href="{{ route('admin.products.show', $p->id) }}">{{ $p->name }}</a></td>
                      <td class="text-end">{{ number_format((int) $p->stock_quantity) }}</td>
                      <td class="text-end">{{ number_format((int) $p->minimum_stock_alert) }}</td>
                    </tr>
                  @empty
                    <tr><td colspan="3" class="text-secondary text-center py-2">None below alert.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
          <div class="card mb-0 dashboard-widget" data-dashboard-widget="out_of_stock">
            <div class="card-header">
              <h3 class="card-title">Out of stock</h3>
            </div>
            <div class="table-responsive">
              <table class="table table-vcenter card-table table-sm">
                <thead><tr><th>Product</th><th>SKU</th></tr></thead>
                <tbody>
                  @forelse ($outOfStockItems as $p)
                    <tr>
                      <td><a href="{{ route('admin.products.show', $p->id) }}">{{ $p->name }}</a></td>
                      <td class="font-monospace small">{{ $p->sku ?? '—' }}</td>
                    </tr>
                  @empty
                    <tr><td colspan="2" class="text-secondary text-center py-2">No zero-stock rows.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-12 col-md dashboard-widget-stack d-flex flex-column gap-3">
          <div class="card dashboard-widget" data-dashboard-widget="expiring_memberships">
            <div class="card-header d-flex align-items-center">
              <h3 class="card-title mb-0">Expiring memberships</h3>
              <a href="{{ route('admin.reports.subscriptions') }}" class="btn btn-sm ms-auto">Report</a>
            </div>
            <div class="table-responsive">
              <table class="table table-vcenter card-table table-sm">
                <thead>
                  <tr>
                    <th>Patient</th>
                    <th>Plan</th>
                    <th>End / renewal</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($expiringMembershipsList as $sub)
                    @php
                      $exp = $sub->end_date ?? $sub->renewal_date;
                    @endphp
                    <tr>
                      <td>{{ $sub->patient->name ?? '—' }}</td>
                      <td>{{ $sub->membershipPlan->name ?? '—' }}</td>
                      <td class="small">{{ $exp?->format('M j, Y') ?? '—' }}</td>
                    </tr>
                  @empty
                    <tr><td colspan="3" class="text-secondary text-center py-2">None in the next 30 days.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
          <div class="card mb-0 dashboard-widget" data-dashboard-widget="stock_movements">
            <div class="card-header d-flex align-items-center">
              <h3 class="card-title mb-0">Recent stock movements</h3>
              <a href="{{ route('admin.products.stock-movements') }}" class="btn btn-sm ms-auto">Log</a>
            </div>
            <div class="table-responsive">
              <table class="table table-vcenter card-table table-sm">
                <thead>
                  <tr>
                    <th>Product</th>
                    <th>Type</th>
                    <th class="text-end">Qty</th>
                    <th>When</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($stockMovementsRecent as $mv)
                    <tr>
                      <td>{{ $mv->product->name ?? '—' }}</td>
                      <td>{{ $mv->type_label }}</td>
                      <td class="text-end">{{ $mv->quantity > 0 ? '+' : '' }}{{ number_format((int) $mv->quantity) }}</td>
                      <td class="text-secondary small">{{ $mv->created_at?->format('M j, H:i') ?? '—' }}</td>
                    </tr>
                  @empty
                    <tr><td colspan="4" class="text-secondary text-center py-2">No movements.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    (function () {
      var DASH_WIDGET_STORAGE = 'bgs_admin_dashboard_widgets_v1';
      var DASH_WIDGET_IDS = [
        'quick_actions', 'kpi_stats', 'today_schedule', 'needs_attention',
        'revenue_snapshot', 'appointments_status', 'membership_overview', 'doctor_activity',
        'top_services', 'recent_payments', 'new_patients', 'new_patient_appts',
        'low_stock', 'out_of_stock', 'expiring_memberships', 'stock_movements',
      ];
      var DASH_CHART_WIDGET_IDS = [
        'revenue_snapshot', 'appointments_status', 'today_schedule', 'needs_attention',
        'membership_overview', 'doctor_activity', 'top_services', 'recent_payments',
        'new_patients', 'low_stock',
      ];

      function dashGetWidgetState() {
        var o = {};
        DASH_WIDGET_IDS.forEach(function (id) {
          o[id] = true;
        });
        try {
          var raw = localStorage.getItem(DASH_WIDGET_STORAGE);
          if (raw) {
            var parsed = JSON.parse(raw);
            DASH_WIDGET_IDS.forEach(function (id) {
              if (typeof parsed[id] === 'boolean') {
                o[id] = parsed[id];
              }
            });
          }
        } catch (e) {}
        return o;
      }

      function dashApplyState(st) {
        document.querySelectorAll('[data-dashboard-widget]').forEach(function (el) {
          var id = el.getAttribute('data-dashboard-widget');
          if (st[id] === false) {
            el.classList.add('d-none');
          } else {
            el.classList.remove('d-none');
          }
        });
        document.querySelectorAll('[data-dashboard-widget-toggle]').forEach(function (inp) {
          var tid = inp.getAttribute('data-dashboard-widget-toggle');
          inp.checked = st[tid] !== false;
        });
      }

      function dashReadStateFromUi() {
        var st = {};
        DASH_WIDGET_IDS.forEach(function (id) {
          st[id] = true;
        });
        document.querySelectorAll('[data-dashboard-widget-toggle]').forEach(function (inp) {
          st[inp.getAttribute('data-dashboard-widget-toggle')] = inp.checked;
        });
        return st;
      }

      function dashWidgetVisible(id) {
        var el = document.querySelector('[data-dashboard-widget="' + id + '"]');
        return el && !el.classList.contains('d-none');
      }

      window.__DASH_WIDGET_STORAGE = DASH_WIDGET_STORAGE;
      window.__DASH_CHART_WIDGET_IDS = DASH_CHART_WIDGET_IDS;
      window.dashApplyState = dashApplyState;
      window.dashWidgetVisible = dashWidgetVisible;

      document.addEventListener('DOMContentLoaded', function () {
        dashApplyState(dashGetWidgetState());
        document.querySelectorAll('[data-dashboard-widget-toggle]').forEach(function (inp) {
          inp.addEventListener('change', function () {
            var st = dashReadStateFromUi();
            try {
              localStorage.setItem(DASH_WIDGET_STORAGE, JSON.stringify(st));
            } catch (e) {}
            dashApplyState(st);
            if (window.__dashboardChartsReady) {
              var wid = this.getAttribute('data-dashboard-widget-toggle');
              if (DASH_CHART_WIDGET_IDS.indexOf(wid) !== -1) {
                if (this.checked) {
                  window.__dashboardDestroyChart(wid);
                  window.__dashboardInitChart(wid);
                  setTimeout(function () {
                    window.__dashboardResizeCharts();
                  }, 200);
                } else {
                  window.__dashboardDestroyChart(wid);
                }
              }
            }
          });
        });
        var resetBtn = document.getElementById('dashboard-widgets-reset');
        if (resetBtn) {
          resetBtn.addEventListener('click', function (e) {
            e.preventDefault();
            try {
              localStorage.removeItem(DASH_WIDGET_STORAGE);
            } catch (err) {}
            dashApplyState(dashGetWidgetState());
            if (window.__dashboardChartsReady) {
              DASH_CHART_WIDGET_IDS.forEach(function (id) {
                window.__dashboardDestroyChart(id);
              });
              setTimeout(function () {
                window.__dashboardInitAllCharts();
                window.__dashboardResizeCharts();
              }, 80);
            }
          });
        }
      });
    })();
  </script>
  <script src="{{ asset('admin/assets/dist/libs/apexcharts/dist/apexcharts.min.js') }}" defer></script>
  <script>
    window.addEventListener('load', function () {
      if (!window.ApexCharts || typeof tabler === 'undefined' || !tabler.getColor) {
        return;
      }

      window.__dashboardChartInstances = window.__dashboardChartInstances || {};

      window.__dashboardDestroyChart = function (widgetId) {
        var c = window.__dashboardChartInstances[widgetId];
        if (c) {
          try {
            c.destroy();
          } catch (e) {}
          window.__dashboardChartInstances[widgetId] = null;
        }
      };

      window.__dashboardResizeCharts = function () {
        Object.keys(window.__dashboardChartInstances).forEach(function (k) {
          var ch = window.__dashboardChartInstances[k];
          if (ch) {
            try {
              ch.resize();
            } catch (e) {}
          }
        });
      };

      var revLabels = @json(collect($revenueTrend)->pluck('label')->values());
      var revValues = @json(collect($revenueTrend)->pluck('value')->map(fn ($v) => (float) $v)->values());
      var fmtMoney = function (v) {
        return '₱' + Number(v).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      };

      var gridPad = { top: -12, right: 0, left: -4, bottom: -4 };
      var scheduleHour = @json($todayScheduleByHour);
      var needRows = @json($needsAttentionChart);
      var memRows = @json($membershipOverviewChart);
      var docRows = @json($doctorActivityChart);
      var svcRows = @json($topServicesChart);
      var payRows = @json($recentPaymentsChart);
      var npRows = @json($newPatientsByDay);
      var lowRows = @json($lowStockChart);
      var statusRows = @json($appointmentStatusChart);

      function storeChart(widgetId, chart) {
        window.__dashboardDestroyChart(widgetId);
        window.__dashboardChartInstances[widgetId] = chart;
      }

      window.__dashboardInitChart = function (widgetId) {
        if (!window.dashWidgetVisible(widgetId)) {
          return;
        }

        switch (widgetId) {
          case 'revenue_snapshot':
            initRevenueChart();
            break;
          case 'appointments_status':
            initStatusChart();
            break;
          case 'today_schedule':
            initScheduleChart();
            break;
          case 'needs_attention':
            initNeedsChart();
            break;
          case 'membership_overview':
            initMembershipChart();
            break;
          case 'doctor_activity':
            initDoctorChart();
            break;
          case 'top_services':
            initTopServicesChart();
            break;
          case 'recent_payments':
            initPaymentsChart();
            break;
          case 'new_patients':
            initNewPatientsChart();
            break;
          case 'low_stock':
            initLowStockChart();
            break;
          default:
        }
      };

      window.__dashboardInitAllCharts = function () {
        (window.__DASH_CHART_WIDGET_IDS || []).forEach(function (id) {
          window.__dashboardInitChart(id);
        });
      };

      function initRevenueChart() {
        var id = 'revenue_snapshot';
        if (window.__dashboardChartInstances[id] || !window.dashWidgetVisible(id)) {
          return;
        }
        var elRev = document.querySelector('#dashboard-chart-revenue');
        if (!elRev) {
          return;
        }
        elRev.innerHTML = '';
        var chart = new ApexCharts(elRev, {
          chart: {
            type: 'area',
            fontFamily: 'inherit',
            height: 260,
            parentHeightOffset: 0,
            toolbar: { show: false },
            zoom: { enabled: false },
            animations: { enabled: true },
          },
          dataLabels: { enabled: false },
          fill: {
            opacity: 0.16,
            type: 'solid',
          },
          stroke: {
            width: 2,
            lineCap: 'round',
            curve: 'smooth',
          },
          series: [{
            name: 'Revenue',
            data: revValues,
          }],
          tooltip: {
            theme: 'dark',
            y: {
              formatter: fmtMoney,
            },
          },
          grid: {
            padding: { top: -12, right: 0, left: -4, bottom: -4 },
            strokeDashArray: 4,
          },
          xaxis: {
            labels: { padding: 0 },
            tooltip: { enabled: false },
            axisBorder: { show: false },
            categories: revLabels,
          },
          yaxis: {
            labels: {
              padding: 4,
              formatter: function (val) {
                return fmtMoney(val);
              },
            },
          },
          colors: [tabler.getColor('primary')],
          legend: { show: false },
        });
        chart.render();
        storeChart(id, chart);
      }

      function initStatusChart() {
        var id = 'appointments_status';
        if (window.__dashboardChartInstances[id] || !window.dashWidgetVisible(id)) {
          return;
        }
        var el = document.querySelector('#dashboard-chart-appointments-status');
        if (!el) {
          return;
        }
        el.innerHTML = '';
        var statusSeries = statusRows.map(function (r) { return r.value; });
        var statusLabels = statusRows.map(function (r) { return r.label; });
        var statusColors = statusRows.map(function (r) {
          return tabler.getColor(r.colorToken);
        });
        var statusTotal = statusSeries.reduce(function (a, b) { return a + b; }, 0);
        if (statusTotal < 1) {
          el.innerHTML = '<p class="text-secondary text-center py-5 mb-0">No appointments to chart yet.</p>';
          return;
        }
        var chart = new ApexCharts(el, {
          chart: {
            type: 'donut',
            fontFamily: 'inherit',
            height: 280,
            sparkline: { enabled: true },
            animations: { enabled: true },
          },
          fill: { opacity: 1 },
          series: statusSeries,
          labels: statusLabels,
          tooltip: {
            theme: 'dark',
            y: {
              formatter: function (val) {
                return val + (statusTotal ? ' (' + Math.round((val / statusTotal) * 100) + '%)' : '');
              },
            },
          },
          grid: { strokeDashArray: 4 },
          colors: statusColors,
          legend: {
            show: true,
            position: 'bottom',
            offsetY: 8,
            markers: {
              width: 10,
              height: 10,
              radius: 100,
            },
            itemMargin: {
              horizontal: 8,
              vertical: 6,
            },
          },
          plotOptions: {
            pie: {
              donut: {
                size: '68%',
              },
            },
          },
        });
        chart.render();
        storeChart(id, chart);
      }

      function initScheduleChart() {
        var id = 'today_schedule';
        if (window.__dashboardChartInstances[id] || !window.dashWidgetVisible(id)) {
          return;
        }
        var el = document.querySelector('#dashboard-chart-schedule-hour');
        if (!el) {
          return;
        }
        el.innerHTML = '';
        var schedSum = scheduleHour.reduce(function (s, r) { return s + r.count; }, 0);
        if (schedSum < 1) {
          el.innerHTML = '<p class="text-secondary text-center py-4 mb-0">No bookings in these hours today.</p>';
          return;
        }
        var chart = new ApexCharts(el, {
          chart: {
            type: 'bar',
            fontFamily: 'inherit',
            height: 220,
            toolbar: { show: false },
            animations: { enabled: true },
          },
          plotOptions: { bar: { columnWidth: '55%', borderRadius: 4 } },
          dataLabels: { enabled: false },
          series: [{ name: 'Appointments', data: scheduleHour.map(function (r) { return r.count; }) }],
          xaxis: {
            categories: scheduleHour.map(function (r) { return r.label; }),
            labels: { padding: 0, rotate: -45 },
            axisBorder: { show: false },
            tooltip: { enabled: false },
          },
          yaxis: { labels: { padding: 4 } },
          tooltip: { theme: 'dark' },
          grid: { padding: gridPad, strokeDashArray: 4 },
          colors: [tabler.getColor('cyan')],
          legend: { show: false },
        });
        chart.render();
        storeChart(id, chart);
      }

      function initNeedsChart() {
        var id = 'needs_attention';
        if (window.__dashboardChartInstances[id] || !window.dashWidgetVisible(id)) {
          return;
        }
        var el = document.querySelector('#dashboard-chart-needs-attention');
        if (!el) {
          return;
        }
        el.innerHTML = '';
        var chart = new ApexCharts(el, {
          chart: {
            type: 'bar',
            fontFamily: 'inherit',
            height: 200,
            toolbar: { show: false },
            animations: { enabled: true },
          },
          plotOptions: {
            bar: {
              horizontal: true,
              borderRadius: 4,
              barHeight: '72%',
              distributed: true,
            },
          },
          dataLabels: { enabled: false },
          series: [{ data: needRows.map(function (r) { return r.value; }) }],
          xaxis: {
            categories: needRows.map(function (r) { return r.label; }),
            labels: { padding: 4 },
          },
          yaxis: { labels: { maxWidth: 120 } },
          tooltip: { theme: 'dark' },
          grid: { padding: { top: 0, right: 0, left: 0, bottom: 0 }, strokeDashArray: 4 },
          colors: [
            tabler.getColor('yellow'),
            tabler.getColor('azure'),
            tabler.getColor('red'),
            tabler.getColor('secondary'),
            tabler.getColor('orange'),
          ],
          legend: { show: false },
        });
        chart.render();
        storeChart(id, chart);
      }

      function initMembershipChart() {
        var id = 'membership_overview';
        if (window.__dashboardChartInstances[id] || !window.dashWidgetVisible(id)) {
          return;
        }
        var el = document.querySelector('#dashboard-chart-membership');
        if (!el) {
          return;
        }
        el.innerHTML = '';
        var chart = new ApexCharts(el, {
          chart: {
            type: 'bar',
            fontFamily: 'inherit',
            height: 200,
            toolbar: { show: false },
            animations: { enabled: true },
          },
          plotOptions: { bar: { columnWidth: '50%', borderRadius: 4 } },
          dataLabels: { enabled: false },
          series: [{ name: 'Count', data: memRows.map(function (r) { return r.value; }) }],
          xaxis: {
            categories: memRows.map(function (r) { return r.label; }),
            labels: { padding: 0, rotate: -25 },
            axisBorder: { show: false },
            tooltip: { enabled: false },
          },
          yaxis: { labels: { padding: 4 } },
          tooltip: { theme: 'dark' },
          grid: { padding: gridPad, strokeDashArray: 4 },
          colors: [tabler.getColor('primary')],
          legend: { show: false },
        });
        chart.render();
        storeChart(id, chart);
      }

      function initDoctorChart() {
        var id = 'doctor_activity';
        if (window.__dashboardChartInstances[id] || !window.dashWidgetVisible(id)) {
          return;
        }
        var el = document.querySelector('#dashboard-chart-doctors');
        if (!el) {
          return;
        }
        el.innerHTML = '';
        var docSum = docRows.reduce(function (s, r) { return s + r.count; }, 0);
        if (docSum < 1) {
          el.innerHTML = '<p class="text-secondary small mb-0">No doctor data for today.</p>';
          return;
        }
        var chart = new ApexCharts(el, {
          chart: {
            type: 'bar',
            fontFamily: 'inherit',
            height: Math.max(160, docRows.length * 36 + 40),
            toolbar: { show: false },
            animations: { enabled: true },
          },
          plotOptions: {
            bar: { horizontal: true, borderRadius: 4, barHeight: '75%' },
          },
          dataLabels: { enabled: false },
          series: [{ name: 'Appointments', data: docRows.map(function (r) { return r.count; }) }],
          xaxis: {
            categories: docRows.map(function (r) { return r.label; }),
            labels: { padding: 4 },
          },
          yaxis: { labels: { maxWidth: 110 } },
          tooltip: { theme: 'dark' },
          grid: { padding: { top: 0, right: 8, left: 0, bottom: 0 }, strokeDashArray: 4 },
          colors: [tabler.getColor('indigo')],
          legend: { show: false },
        });
        chart.render();
        storeChart(id, chart);
      }

      function initTopServicesChart() {
        var id = 'top_services';
        if (window.__dashboardChartInstances[id] || !window.dashWidgetVisible(id)) {
          return;
        }
        var el = document.querySelector('#dashboard-chart-top-services');
        if (!el) {
          return;
        }
        el.innerHTML = '';
        if (!svcRows.length) {
          el.innerHTML = '<p class="text-secondary small mb-0">No booking data yet.</p>';
          return;
        }
        var chart = new ApexCharts(el, {
          chart: {
            type: 'bar',
            fontFamily: 'inherit',
            height: Math.max(200, svcRows.length * 40 + 48),
            toolbar: { show: false },
            animations: { enabled: true },
          },
          plotOptions: {
            bar: { horizontal: true, borderRadius: 4, barHeight: '72%' },
          },
          dataLabels: { enabled: false },
          series: [{ name: 'Bookings', data: svcRows.map(function (r) { return r.count; }) }],
          xaxis: {
            categories: svcRows.map(function (r) { return r.label; }),
            labels: { padding: 4 },
          },
          yaxis: { labels: { maxWidth: 140 } },
          tooltip: { theme: 'dark' },
          grid: { padding: { top: 0, right: 8, left: 0, bottom: 0 }, strokeDashArray: 4 },
          colors: [tabler.getColor('teal')],
          legend: { show: false },
        });
        chart.render();
        storeChart(id, chart);
      }

      function initPaymentsChart() {
        var id = 'recent_payments';
        if (window.__dashboardChartInstances[id] || !window.dashWidgetVisible(id)) {
          return;
        }
        var el = document.querySelector('#dashboard-chart-recent-payments');
        if (!el) {
          return;
        }
        el.innerHTML = '';
        if (!payRows.length) {
          el.innerHTML = '<p class="text-secondary text-center py-3 mb-0">No payments in this list.</p>';
          return;
        }
        var chart = new ApexCharts(el, {
          chart: {
            type: 'bar',
            fontFamily: 'inherit',
            height: 240,
            toolbar: { show: false },
            animations: { enabled: true },
          },
          plotOptions: { bar: { columnWidth: '62%', borderRadius: 4 } },
          dataLabels: { enabled: false },
          series: [{ name: 'Amount', data: payRows.map(function (r) { return r.amount; }) }],
          xaxis: {
            categories: payRows.map(function (r) { return r.label; }),
            labels: { padding: 0, rotate: -35, maxHeight: 120 },
            axisBorder: { show: false },
            tooltip: { enabled: false },
          },
          yaxis: {
            labels: {
              padding: 4,
              formatter: function (val) { return fmtMoney(val); },
            },
          },
          tooltip: {
            theme: 'dark',
            y: { formatter: fmtMoney },
          },
          grid: { padding: gridPad, strokeDashArray: 4 },
          colors: [tabler.getColor('green')],
          legend: { show: false },
        });
        chart.render();
        storeChart(id, chart);
      }

      function initNewPatientsChart() {
        var id = 'new_patients';
        if (window.__dashboardChartInstances[id] || !window.dashWidgetVisible(id)) {
          return;
        }
        var el = document.querySelector('#dashboard-chart-new-patients');
        if (!el) {
          return;
        }
        el.innerHTML = '';
        var chart = new ApexCharts(el, {
          chart: {
            type: 'area',
            fontFamily: 'inherit',
            height: 200,
            toolbar: { show: false },
            zoom: { enabled: false },
            animations: { enabled: true },
          },
          dataLabels: { enabled: false },
          fill: { opacity: 0.16, type: 'solid' },
          stroke: { width: 2, lineCap: 'round', curve: 'smooth' },
          series: [{ name: 'New patients', data: npRows.map(function (r) { return r.count; }) }],
          xaxis: {
            categories: npRows.map(function (r) { return r.label; }),
            labels: { padding: 0, rotate: -30 },
            axisBorder: { show: false },
            tooltip: { enabled: false },
          },
          yaxis: { labels: { padding: 4 } },
          tooltip: { theme: 'dark' },
          grid: { padding: gridPad, strokeDashArray: 4 },
          colors: [tabler.getColor('pink')],
          legend: { show: false },
        });
        chart.render();
        storeChart(id, chart);
      }

      function initLowStockChart() {
        var id = 'low_stock';
        if (window.__dashboardChartInstances[id] || !window.dashWidgetVisible(id)) {
          return;
        }
        var el = document.querySelector('#dashboard-chart-low-stock');
        if (!el) {
          return;
        }
        el.innerHTML = '';
        if (!lowRows.length) {
          el.innerHTML = '<p class="text-secondary text-center py-3 mb-0">Nothing below alert to chart.</p>';
          return;
        }
        var chart = new ApexCharts(el, {
          chart: {
            type: 'bar',
            fontFamily: 'inherit',
            height: Math.max(160, lowRows.length * 36 + 40),
            toolbar: { show: false },
            animations: { enabled: true },
          },
          plotOptions: {
            bar: { horizontal: true, borderRadius: 4, barHeight: '72%' },
          },
          dataLabels: { enabled: false },
          series: [{ name: 'Qty', data: lowRows.map(function (r) { return r.qty; }) }],
          xaxis: {
            categories: lowRows.map(function (r) { return r.label; }),
            labels: { padding: 4 },
          },
          yaxis: { labels: { maxWidth: 130 } },
          tooltip: { theme: 'dark' },
          grid: { padding: { top: 0, right: 8, left: 0, bottom: 0 }, strokeDashArray: 4 },
          colors: [tabler.getColor('orange')],
          legend: { show: false },
        });
        chart.render();
        storeChart(id, chart);
      }

      window.__dashboardChartsReady = true;
      window.__dashboardInitAllCharts();
      setTimeout(function () {
        window.__dashboardResizeCharts();
      }, 120);
    });
  </script>
@endpush
