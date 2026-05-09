@extends('admin.layouts.master')

@php
  $categoryLabels = [
    'wellness' => 'Wellness',
    'rehab' => 'Rehabilitation',
    'chronic' => 'Chronic care',
  ];
  $expiryLabels = [
    'after_purchase' => 'Starts after purchase',
    'after_first_use' => 'Starts after first use',
  ];
  $unitLabels = [
    'days' => 'days',
    'months' => 'months',
    'years' => 'years',
  ];
  $totalSessions = (int) $package->services->sum(fn ($s) => (int) $s->pivot->sessions);
  $descriptionLead = str($package->description ?? '')->squish()->limit(220);
  $validitySummary =
    $package->validity_value === null || ! $package->validity_type
      ? '—'
      : $package->validity_value .
        ' ' .
        ($unitLabels[$package->validity_type] ?? $package->validity_type);
@endphp

{{--
  Layout inspired by Tabler reference pages in resources/views/1_admin-template:
  profile.html (hero header + two-column body), datagrid.html (definition grids), widgets (stat cards).
--}}

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col-auto">
          @if (! empty($package->image_url))
            <span class="avatar avatar-xl rounded"
              style="background-image: url({{ $package->image_url }})"></span>
          @else
            <span class="avatar avatar-xl rounded bg-azure-lt text-azure">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="24" height="24"
                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                stroke-linejoin="round" aria-hidden="true">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" />
                <path d="M12 12l8 -4.5" />
                <path d="M12 12l0 9" />
                <path d="M12 12l-8 -4.5" />
              </svg>
            </span>
          @endif
        </div>
        <div class="col">
          <div class="page-pretitle text-secondary">Treatment package</div>
          <h1 class="fw-bold mb-2">{{ $package->name }}</h1>
          @if ($descriptionLead->isNotEmpty())
            <p class="text-secondary mb-2 mb-md-3">{{ $descriptionLead }}</p>
          @endif
          <ul class="list-inline list-inline-dots text-secondary mb-0">
            <li class="list-inline-item">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-inline text-secondary" width="20" height="20"
                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                stroke-linejoin="round" aria-hidden="true">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" />
                <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" />
                <path d="M16 5l3 3" />
              </svg>
              {{ $categoryLabels[$package->category ?? ''] ?? ($package->category ?? 'Uncategorized') }}
            </li>
            <li class="list-inline-item">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-inline text-secondary" width="20" height="20"
                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                stroke-linejoin="round" aria-hidden="true">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                <path d="M16 3v4" />
                <path d="M8 3v4" />
                <path d="M4 11h16" />
              </svg>
              Valid {{ $validitySummary }}
              · {{ $expiryLabels[$package->expiry_rule ?? ''] ?? ($package->expiry_rule ?? '') }}
            </li>
          </ul>
        </div>
        <div class="col-auto ms-auto">
          <div class="btn-list">
            <a href="{{ route('admin.packages') }}" class="btn">Back</a>
            <a href="{{ route('admin.packages.edit', $package) }}" class="btn btn-primary">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24"
                stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"
                aria-hidden="true">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" />
                <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" />
              </svg>
              Edit
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row row-deck row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="subheader text-secondary">Total price</div>
              <div class="d-flex align-items-center">
                <div class="h2 mb-0">₱{{ number_format((float) $package->price, 2) }}</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="subheader text-secondary">Sessions in bundle</div>
              <div class="d-flex align-items-center">
                <div class="h2 mb-0">{{ $totalSessions }}</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="subheader text-secondary">Validity</div>
              <div class="h3 mb-0 text-truncate" title="{{ $validitySummary }}">{{ $validitySummary }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="subheader text-secondary">Status</div>
              <div class="mt-1">
                @php $st = $package->status ?? 'active'; @endphp
                @if ($st === 'active')
                  <span class="badge bg-green-lt">Active</span>
                @elseif ($st === 'pending')
                  <span class="badge bg-yellow-lt">Pending</span>
                @elseif ($st === 'archived')
                  <span class="badge bg-secondary-lt">Archived</span>
                @else
                  <span class="badge bg-secondary-lt">{{ ucfirst($st) }}</span>
                @endif
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
                <li class="nav-item">
                  <a href="#tab-show-info" class="nav-link active" data-bs-toggle="tab">Overview</a>
                </li>
                <li class="nav-item">
                  <a href="#tab-show-services" class="nav-link" data-bs-toggle="tab">Services</a>
                </li>
                <li class="nav-item">
                  <a href="#tab-show-pricing" class="nav-link" data-bs-toggle="tab">Pricing &amp; validity</a>
                </li>
                <li class="nav-item">
                  <a href="#tab-show-rules" class="nav-link" data-bs-toggle="tab">Rules &amp; staff</a>
                </li>
                <li class="nav-item">
                  <a href="#tab-show-care" class="nav-link" data-bs-toggle="tab">Care &amp; notes</a>
                </li>
                <li class="nav-item">
                  <a href="#tab-show-patients" class="nav-link" data-bs-toggle="tab">Patients</a>
                </li>
                <li class="nav-item">
                  <a href="#tab-show-usage" class="nav-link" data-bs-toggle="tab">Usage history</a>
                </li>
              </ul>
            </div>
            <div class="card-body">
              <div class="tab-content">
                <div class="tab-pane active show" id="tab-show-info">
                  <div class="row g-4">
                    <div class="col-md-6">
                      <div class="card card-sm bg-light-lt border-0">
                        <div class="card-body">
                          <div class="text-secondary small mb-1">Hero image</div>
                          @if (! empty($package->image_url))
                            <div class="rounded overflow-hidden border">
                              <div class="ratio ratio-4x3 bg-light">
                                <img src="{{ $package->image_url }}" class="w-100 h-100" style="object-fit: cover" alt="">
                              </div>
                            </div>
                          @else
                            <div
                              class="rounded border border-dashed d-flex align-items-center justify-content-center text-secondary py-5">
                              No image
                            </div>
                          @endif
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="datagrid mb-0">
                        <div class="datagrid-item">
                          <div class="datagrid-title">Package name</div>
                          <div class="datagrid-content fw-medium">{{ $package->name }}</div>
                        </div>
                        <div class="datagrid-item">
                          <div class="datagrid-title">Status</div>
                          <div class="datagrid-content">
                            @if (($package->status ?? 'active') === 'active')
                              <span class="status status-green">Active</span>
                            @elseif (($package->status ?? '') === 'pending')
                              <span class="status status-yellow">Pending</span>
                            @else
                              <span class="status status-secondary">{{ ucfirst($package->status ?? 'inactive') }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="datagrid-item">
                          <div class="datagrid-title">Category</div>
                          <div class="datagrid-content">
                            {{ $categoryLabels[$package->category ?? ''] ?? ($package->category ?? '—') }}
                          </div>
                        </div>
                        <div class="datagrid-item">
                          <div class="datagrid-title">Description</div>
                          <div class="datagrid-content text-secondary">{{ $package->description ?? '—' }}</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="tab-pane" id="tab-show-services">
                  <div class="table-responsive">
                    <table class="table table-vcenter card-table table-striped">
                      <thead>
                        <tr>
                          <th>Service</th>
                          <th class="w-1 text-end">Sessions</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse ($package->services as $row)
                          <tr>
                            <td class="fw-medium">{{ $row->name }}</td>
                            <td class="text-end"><span class="badge bg-blue-lt">{{ $row->pivot->sessions }}</span></td>
                          </tr>
                        @empty
                          <tr>
                            <td colspan="2" class="text-secondary">No services linked.</td>
                          </tr>
                        @endforelse
                      </tbody>
                      @if ($package->services->isNotEmpty())
                        <tfoot class="table-light">
                          <tr>
                            <td class="fw-semibold">Total</td>
                            <td class="text-end fw-semibold">{{ $totalSessions }}</td>
                          </tr>
                        </tfoot>
                      @endif
                    </table>
                  </div>
                </div>

                <div class="tab-pane" id="tab-show-pricing">
                  <h3 class="card-title mb-3">Pricing</h3>
                  <div class="datagrid mb-4">
                    <div class="datagrid-item">
                      <div class="datagrid-title">Total price</div>
                      <div class="datagrid-content fw-semibold">₱ {{ number_format((float) $package->price, 2) }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Original price</div>
                      <div class="datagrid-content">
                        @if ($package->original_price !== null && $package->original_price !== '')
                          <span class="text-decoration-line-through text-secondary">₱
                            {{ number_format((float) $package->original_price, 2) }}</span>
                        @else
                          —
                        @endif
                      </div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Discount</div>
                      <div class="datagrid-content">
                        @if ($package->discount_percent !== null && $package->discount_percent !== '')
                          {{ rtrim(rtrim(number_format((float) $package->discount_percent, 2), '0'), '.') }}%
                        @else
                          —
                        @endif
                      </div>
                    </div>
                  </div>
                  <h3 class="card-title mb-3">Validity</h3>
                  <div class="datagrid mb-0">
                    <div class="datagrid-item">
                      <div class="datagrid-title">Duration</div>
                      <div class="datagrid-content">{{ $validitySummary }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Expiry rule</div>
                      <div class="datagrid-content">
                        {{ $expiryLabels[$package->expiry_rule ?? ''] ?? ($package->expiry_rule ?? '—') }}
                      </div>
                    </div>
                  </div>
                </div>

                <div class="tab-pane" id="tab-show-rules">
                  <h3 class="card-title mb-3">Limits / rules</h3>
                  <div class="datagrid mb-4">
                    <div class="datagrid-item">
                      <div class="datagrid-title">Max usage / day</div>
                      <div class="datagrid-content">{{ $package->max_usage_per_day ?? '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Allow sharing</div>
                      <div class="datagrid-content">
                        @if ($package->allow_sharing)
                          <svg xmlns="http://www.w3.org/2000/svg" class="icon text-green me-1" width="20" height="20"
                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                            stroke-linejoin="round" aria-hidden="true">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M5 12l5 5l10 -10" />
                          </svg>
                          Yes
                        @else
                          <svg xmlns="http://www.w3.org/2000/svg" class="icon text-secondary me-1" width="20" height="20"
                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                            stroke-linejoin="round" aria-hidden="true">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M18 6l-12 12" />
                            <path d="M6 6l12 12" />
                          </svg>
                          No
                        @endif
                      </div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Refundable</div>
                      <div class="datagrid-content">
                        @if ($package->refundable)
                          <svg xmlns="http://www.w3.org/2000/svg" class="icon text-green me-1" width="20" height="20"
                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                            stroke-linejoin="round" aria-hidden="true">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M5 12l5 5l10 -10" />
                          </svg>
                          Yes
                        @else
                          No
                        @endif
                      </div>
                    </div>
                  </div>
                  <h3 class="card-title mb-3">Assigned doctors</h3>
                  <div class="datagrid mb-0">
                    <div class="datagrid-item">
                      <div class="datagrid-title">Doctors</div>
                      <div class="datagrid-content">
                        @forelse ($package->doctors as $doc)
                          <div>{{ $doc->name }}</div>
                        @empty
                          <span class="text-secondary">—</span>
                        @endforelse
                      </div>
                    </div>
                  </div>
                </div>

                <div class="tab-pane" id="tab-show-care">
                  <div class="card card-sm border-0 bg-secondary-lt">
                    <div class="card-body">
                      <h3 class="card-title">Package care instructions</h3>
                      <p class="text-secondary small mb-3">Default before / aftercare for patients and internal staff
                        notes for this package.</p>
                      <div class="mb-3">
                        <div class="text-secondary small">Before care</div>
                        <div class="text-wrap" style="white-space: pre-line;">{{ filled($package->before_care) ? $package->before_care : '—' }}</div>
                      </div>
                      <div class="mb-3">
                        <div class="text-secondary small">Aftercare</div>
                        <div class="text-wrap" style="white-space: pre-line;">{{ filled($package->aftercare) ? $package->aftercare : '—' }}</div>
                      </div>
                      <div class="mb-0">
                        <div class="text-secondary small">Internal notes</div>
                        <div class="text-wrap" style="white-space: pre-line;">{{ filled($package->internal_notes) ? $package->internal_notes : '—' }}</div>
                      </div>
                    </div>
                  </div>

                  @php
                    $purchaseNotes = $package->patientPackages->filter(fn ($pp) => filled($pp->notes));
                  @endphp
                  @if ($purchaseNotes->isNotEmpty())
                    <div class="card card-sm border-0 bg-secondary-lt mt-3">
                      <div class="card-body">
                        <h3 class="card-title">Notes per purchase</h3>
                        <p class="text-secondary small mb-3">Extra notes stored on each patient's purchase of this
                          package.</p>
                        <div class="list-group list-group-flush rounded border">
                          @foreach ($purchaseNotes as $pp)
                            <div class="list-group-item px-3 py-3">
                              <div class="fw-medium">{{ $pp->patient->name ?? 'Patient #'.$pp->patient_id }}</div>
                              <div class="text-secondary small">{{ $pp->purchased_at?->format('Y-m-d') ?? '—' }}</div>
                              <div class="mt-2 text-wrap" style="white-space: pre-line;">{{ $pp->notes }}</div>
                            </div>
                          @endforeach
                        </div>
                      </div>
                    </div>
                  @endif
                </div>

                <div class="tab-pane" id="tab-show-patients">
                  <p class="text-secondary small mb-3">Patients who purchased this package. Scroll when the list is long.</p>
                  <div class="border rounded">
                    <div class="table-responsive" style="max-height: min(70vh, 36rem); overflow-y: auto;">
                      <table class="table table-vcenter card-table table-sm table-striped mb-0">
                        <thead class="sticky-top bg-body border-bottom">
                          <tr>
                            <th>Patient</th>
                            <th>Purchased</th>
                            <th class="text-end">Usage</th>
                          </tr>
                        </thead>
                        <tbody>
                          @forelse ($package->patientPackages as $p)
                            <tr>
                              <td>{{ $p->patient->name ?? '—' }}</td>
                              <td class="text-secondary">{{ $p->purchased_at?->format('Y-m-d') ?? '—' }}</td>
                              <td class="text-end">{{ $p->used_sessions }} / {{ $p->total_sessions }}</td>
                            </tr>
                          @empty
                            <tr>
                              <td colspan="3" class="text-secondary">No purchase data yet.</td>
                            </tr>
                          @endforelse
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>

                <div class="tab-pane" id="tab-show-usage">
                  <p class="text-secondary small mb-3">Session redemptions and usage events. Scroll when the list is long.</p>
                  <div class="border rounded">
                    <div class="table-responsive" style="max-height: min(70vh, 36rem); overflow-y: auto;">
                      <table class="table table-vcenter card-table table-sm table-striped mb-0">
                        <thead class="sticky-top bg-body border-bottom">
                          <tr>
                            <th>Date</th>
                            <th>Patient</th>
                            <th>Service</th>
                            <th>Change</th>
                            <th class="w-1"></th>
                          </tr>
                        </thead>
                        <tbody>
                          @forelse ($usageHistories as $u)
                            @php
                              $ch = (int) $u->session_change;
                              $deltaLabel =
                                  $ch > 0
                                      ? '+' . $ch . ' session(s)'
                                      : ($ch . ' session(s)');
                            @endphp
                            <tr>
                              <td class="text-secondary text-nowrap">{{ $u->used_on?->format('Y-m-d') ?? '—' }}</td>
                              <td class="fw-medium">{{ $u->patient->name ?? '—' }}</td>
                              <td>{{ $u->service->name ?? '—' }}</td>
                              <td>{{ $deltaLabel }}</td>
                              <td><span class="badge bg-green-lt">{{ ucfirst($u->status ?? '') }}</span></td>
                            </tr>
                          @empty
                            <tr>
                              <td colspan="5" class="text-secondary">No usage logged yet.</td>
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

        <div class="col-lg-4">
          <div class="row row-cards">
            <div class="col-12">
              <div class="card">
                <div class="card-body">
                  <h3 class="card-title">Snapshot</h3>
                  <div class="datagrid mb-0">
                    <div class="datagrid-item">
                      <div class="datagrid-title">Package ID</div>
                      <div class="datagrid-content font-monospace">#{{ $package->id }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Listed price</div>
                      <div class="datagrid-content fw-semibold">₱{{ number_format((float) $package->price, 2) }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Bundle sessions</div>
                      <div class="datagrid-content">{{ $totalSessions }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Discount</div>
                      <div class="datagrid-content">
                        @if ($package->discount_percent !== null && $package->discount_percent !== '')
                          {{ rtrim(rtrim(number_format((float) $package->discount_percent, 2), '0'), '.') }}%
                        @else
                          —
                        @endif
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="card">
                <div class="card-body">
                  <h3 class="card-title">Assigned doctors</h3>
                  @if ($package->doctors->isNotEmpty())
                    <div>
                      @foreach ($package->doctors as $doc)
                        @php
                          $docName = $doc->name ?? '?';
                          $initial = strtoupper(substr($docName, 0, 1));
                        @endphp
                        <div class="py-2 d-flex align-items-center @if (! $loop->last) border-bottom @endif">
                          <span class="avatar avatar-sm rounded me-2 bg-secondary-lt">{{ $initial }}</span>
                          <span class="fw-medium">{{ $docName }}</span>
                        </div>
                      @endforeach
                    </div>
                  @else
                    <p class="text-secondary mb-0 small">No doctors assigned.</p>
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
