@extends('admin.layouts.master')

@php
  $status = $plan->status ?? 'inactive';
  $statusBadge =
      $status === 'active'
          ? 'bg-green-lt'
          : ($status === 'paused'
              ? 'bg-yellow-lt'
              : 'bg-secondary-lt');
  $typeLabel = $plan->billing_cycle
      ? ucfirst($plan->billing_cycle)
      : ucfirst((string) ($plan->type ?? '—'));
  $price = (float) $plan->price;
  $planInitial = strtoupper(\Illuminate\Support\Str::substr($plan->name, 0, 1));
  $durationSummary =
      $plan->duration_value && $plan->duration_type
          ? $plan->duration_value .
            ' ' .
            \Illuminate\Support\Str::plural($plan->duration_type, $plan->duration_value)
          : '—';
@endphp

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col-auto">
          <span class="avatar avatar-xl rounded bg-azure-lt text-azure">{{ $planInitial }}</span>
        </div>
        <div class="col">
          <div class="page-pretitle text-secondary">Membership</div>
          <h2 class="page-title mb-0">{{ $plan->name }}</h2>
          <div class="text-secondary small mt-1">
            ID: <span class="font-monospace">#{{ $plan->id }}</span>
            @if ($plan->slug)
              · <span class="font-monospace">{{ $plan->slug }}</span>
            @endif
            · {{ $typeLabel }}
            · <span class="badge {{ $statusBadge }}">{{ ucfirst((string) $status) }}</span>
          </div>
        </div>
        <div class="col-auto ms-auto">
          <div class="btn-list">
            <a href="{{ route('admin.subscriptions') }}" class="btn">Back</a>
            <a href="{{ route('admin.subscriptions.edit', $plan) }}" class="btn btn-primary">Edit plan</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Price</div>
              <div class="h2 mb-0">₱{{ number_format($price, 2) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Plan type</div>
              <div class="h2 mb-0">{{ $typeLabel }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Included services</div>
              <div class="h2 mb-0">{{ $plan->services->count() }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Subscribers</div>
              <div class="h2 mb-0">{{ number_format((int) $plan->patient_subscriptions_count) }}</div>
              <div class="text-secondary small">
                {{ number_format((int) $plan->active_patient_subscriptions_count) }} active
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-lg-8">
          <div class="card">
            <div class="card-header border-0">
              <ul class="nav nav-tabs card-header-tabs nav-fill bg-transparent" data-bs-toggle="tabs">
                <li class="nav-item"><a href="#tab-details" class="nav-link active" data-bs-toggle="tab">Plan details</a></li>
                <li class="nav-item"><a href="#tab-services" class="nav-link" data-bs-toggle="tab">Included services</a></li>
                <li class="nav-item"><a href="#tab-subscribers" class="nav-link" data-bs-toggle="tab">Subscribers</a></li>
                <li class="nav-item"><a href="#tab-rules" class="nav-link" data-bs-toggle="tab">Usage rules</a></li>
                <li class="nav-item"><a href="#tab-notes" class="nav-link" data-bs-toggle="tab">Notes</a></li>
              </ul>
            </div>
            <div class="card-body">
              <div class="tab-content">
                <div class="tab-pane active show" id="tab-details">
                  <div class="datagrid">
                    <div class="datagrid-item">
                      <div class="datagrid-title">Plan name</div>
                      <div class="datagrid-content">{{ $plan->name }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Slug</div>
                      <div class="datagrid-content font-monospace">{{ $plan->slug ?? '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Type</div>
                      <div class="datagrid-content">{{ $typeLabel }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Billing cycle</div>
                      <div class="datagrid-content">{{ $plan->billing_cycle ? ucfirst($plan->billing_cycle) : '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Duration</div>
                      <div class="datagrid-content">{{ $durationSummary }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Description</div>
                      <div class="datagrid-content text-break">{{ $plan->description ? $plan->description : '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Status</div>
                      <div class="datagrid-content"><span class="badge {{ $statusBadge }}">{{ ucfirst((string) $status) }}</span></div>
                    </div>
                  </div>
                </div>

                <div class="tab-pane" id="tab-services">
                  <div class="table-responsive">
                    <table class="table table-vcenter">
                      <thead>
                        <tr>
                          <th>Service</th>
                          <th class="text-end">Sessions (plan)</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse ($plan->services as $service)
                          <tr>
                            <td>
                              <div class="fw-medium">{{ $service->name }}</div>
                              @if ($service->slug)
                                <div class="text-secondary small font-monospace">{{ $service->slug }}</div>
                              @endif
                            </td>
                            <td class="text-end">{{ (int) ($service->pivot->sessions ?? 1) }}</td>
                          </tr>
                        @empty
                          <tr>
                            <td colspan="2" class="text-center text-secondary py-4">No included services yet.</td>
                          </tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                </div>

                <div class="tab-pane" id="tab-subscribers">
                  <div class="table-responsive">
                    <table class="table table-vcenter">
                      <thead>
                        <tr>
                          <th>Subscriber</th>
                          <th>Start date</th>
                          <th>Renewal date</th>
                          <th>End date</th>
                          <th>Status</th>
                          <th class="text-end">Sessions used</th>
                          <th class="text-end">Sessions remaining</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse ($plan->patientSubscriptions as $sub)
                          @php
                            $ss = strtolower((string) $sub->status);
                            $subscriberBadge = match ($ss) {
                              'active' => 'bg-green-lt',
                              'expired' => 'bg-yellow-lt',
                              'paused' => 'bg-azure-lt',
                              'cancelled' => 'bg-red-lt',
                              default => 'bg-secondary-lt',
                            };
                            $patientLabel = $sub->patient
                                ? ($sub->patient->name ?: $sub->patient->email)
                                : '—';
                          @endphp
                          <tr>
                            <td>{{ $patientLabel }}</td>
                            <td>{{ $sub->start_date?->format('M j, Y') ?? '—' }}</td>
                            <td>{{ $sub->renewal_date?->format('M j, Y') ?? '—' }}</td>
                            <td>{{ $sub->end_date?->format('M j, Y') ?? '—' }}</td>
                            <td>
                              <span class="badge {{ $subscriberBadge }}">{{ ucfirst((string) $sub->status) }}</span>
                            </td>
                            <td class="text-end">{{ (int) $sub->sessions_used }}</td>
                            <td class="text-end">{{ (int) $sub->sessions_remaining }}</td>
                          </tr>
                        @empty
                          <tr>
                            <td colspan="7" class="text-center text-secondary py-4">No subscribers yet.</td>
                          </tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                </div>

                <div class="tab-pane" id="tab-rules">
                  <div class="datagrid">
                    <div class="datagrid-item">
                      <div class="datagrid-title">Billing cycle</div>
                      <div class="datagrid-content">{{ $plan->billing_cycle ? ucfirst($plan->billing_cycle) : '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Max usage per month</div>
                      <div class="datagrid-content">{{ $plan->max_usage_per_month !== null ? $plan->max_usage_per_month : '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Rollover unused sessions</div>
                      <div class="datagrid-content">{{ $plan->rollover_unused_sessions ? 'Yes' : 'No' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Cancellation allowed</div>
                      <div class="datagrid-content">{{ $plan->cancellation_allowed ? 'Yes' : 'No' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Pause allowed</div>
                      <div class="datagrid-content">{{ $plan->pause_allowed ? 'Yes' : 'No' }}</div>
                    </div>
                  </div>
                </div>

                <div class="tab-pane" id="tab-notes">
                  <div class="datagrid">
                    <div class="datagrid-item">
                      <div class="datagrid-title">Terms &amp; conditions</div>
                      <div class="datagrid-content text-break">{{ $plan->terms_and_conditions ? $plan->terms_and_conditions : '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Before care</div>
                      <div class="datagrid-content text-break">{{ $plan->before_care ? $plan->before_care : '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Aftercare</div>
                      <div class="datagrid-content text-break">{{ $plan->aftercare ? $plan->aftercare : '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Internal notes</div>
                      <div class="datagrid-content text-break">{{ $plan->internal_notes ? $plan->internal_notes : '—' }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="card mb-3">
            <div class="card-header">
              <h3 class="card-title">Snapshot</h3>
            </div>
            <div class="card-body">
              <div class="datagrid mb-0">
                <div class="datagrid-item">
                  <div class="datagrid-title">Plan ID</div>
                  <div class="datagrid-content font-monospace">#{{ $plan->id }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Price</div>
                  <div class="datagrid-content">₱{{ number_format($price, 2) }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Subscribers</div>
                  <div class="datagrid-content">{{ number_format((int) $plan->patient_subscriptions_count) }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Status</div>
                  <div class="datagrid-content"><span class="badge {{ $statusBadge }}">{{ ucfirst((string) $status) }}</span></div>
                </div>
              </div>
            </div>
          </div>
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Quick actions</h3>
            </div>
            <div class="card-body">
              <div class="d-grid gap-2">
                <a href="{{ route('admin.subscriptions.edit', $plan) }}" class="btn btn-primary">Edit plan</a>
                <a href="{{ route('admin.subscriptions') }}" class="btn btn-outline-secondary">Back to list</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
