@extends('admin.layouts.master')

@section('content')
  @php
    /** @var \App\Models\Promotion|array<string, mixed> $promotion */
    $isModel = $promotion instanceof \App\Models\Promotion;

    if ($isModel) {
        $promotionId = $promotion->id;
        $promoName = $promotion->name;
        $promoType = match ($promotion->discount_method) {
            'percentage' => 'Percentage discount',
            'fixed' => 'Fixed amount',
            'free_service' => 'Free service',
            'bundle' => 'Bundle',
            default => $promotion->discount_method ? ucfirst(str_replace('_', ' ', $promotion->discount_method)) : '—',
        };
        $appliesTo = $promotion->scope_label;
        $appliesCategory = $promotion->applies_to ?? '—';
        $status = ucfirst((string) $promotion->status);
        $statusClass = $promotion->status_badge;
        $discount = $promotion->discount_label;
        $validityLabel = $promotion->validity_label;
        $validityStart = $promotion->start_date?->toDateString() ?? now()->toDateString();
        $validityEnd = $promotion->end_date?->toDateString() ?? now()->toDateString();
        $usageHistory = [];
        $eligiblePatients = [];
        $timesUsed = 0;
        $uniquePatients = 0;
        $promoTarget = $appliesTo;
        $promoRevenue = 0.0;
        $notesInternal = $promotion->internal_notes ?? '—';
        $notesDisplay = $promotion->display_note ?? '—';
        $terms = $promotion->terms_and_conditions ?? '—';
    } else {
        $promotion = is_array($promotion) ? $promotion : [];
        $promotionId = $promotion['id'] ?? null;
        $promoName = $promotion['name'] ?? 'Promotion';
        $promoType = $promotion['promo_type'] ?? 'Percentage Discount';
        $appliesTo = $promotion['applies_to'] ?? 'All Services';
        $appliesCategory = $promotion['applies_category'] ?? 'service';
        $status = $promotion['status'] ?? 'Draft';
        $discount = $promotion['discount'] ?? '—';
        $validityLabel = null;
        $validityStart = $promotion['validity_start'] ?? now()->toDateString();
        $validityEnd = $promotion['validity_end'] ?? now()->toDateString();
        $statusClass = match ($status) {
            'Active' => 'bg-green-lt text-green',
            'Scheduled' => 'bg-blue-lt text-blue',
            'Expired' => 'bg-red-lt text-red',
            'Inactive' => 'bg-secondary-lt text-secondary',
            default => 'bg-yellow-lt text-yellow',
        };
        $usageHistory = $promotion['usage_history'] ?? [
            ['date' => '2026-03-03', 'patient' => 'Maria Santos', 'target' => 'Hydra Facial', 'discount_value' => '₱500', 'revenue' => 2000],
            ['date' => '2026-03-08', 'patient' => 'Ana Reyes', 'target' => 'Hydra Facial', 'discount_value' => '₱500', 'revenue' => 2200],
            ['date' => '2026-03-11', 'patient' => 'John Cruz', 'target' => 'Hydra Facial', 'discount_value' => '₱500', 'revenue' => 2400],
        ];
        $eligiblePatients = $promotion['eligible_patients'] ?? [
            ['name' => 'Maria Santos', 'segment' => 'Returning', 'last_visit' => '2026-03-01', 'status' => 'Eligible'],
            ['name' => 'Ana Reyes', 'segment' => 'Membership', 'last_visit' => '2026-03-07', 'status' => 'Eligible'],
            ['name' => 'Liza Dela Rosa', 'segment' => 'New', 'last_visit' => '2026-02-27', 'status' => 'Pending'],
        ];
        $timesUsed = (int) ($promotion['usage'] ?? count($usageHistory));
        $uniquePatients = count(array_unique(array_map(fn ($u) => $u['patient'] ?? '', $usageHistory)));
        $promoTarget = $usageHistory[0]['target'] ?? $appliesTo;
        $promoRevenue = array_sum(array_map(fn ($u) => (float) ($u['revenue'] ?? 0), $usageHistory));
        $notesInternal = $promotion['internal_notes'] ?? '—';
        $notesDisplay = $promotion['display_note_website'] ?? '—';
        $terms = $promotion['terms_conditions'] ?? '—';
    }
  @endphp

  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col-auto">
          <span class="avatar avatar-xl rounded bg-pink-lt text-pink">%</span>
        </div>
        <div class="col">
          <div class="page-pretitle text-secondary">Promotions</div>
          <h2 class="page-title mb-0">{{ $promoName }}</h2>
          <div class="text-secondary small mt-1">
            <span class="font-monospace">#{{ $promotionId ?? 'N/A' }}</span>
            · {{ $promoType }}
            · <span class="badge {{ $statusClass }}">{{ $status }}</span>
          </div>
        </div>
        <div class="col-auto ms-auto">
          <div class="btn-list">
            <a href="{{ route('admin.promotions') }}" class="btn">Back</a>
            <a href="{{ route('admin.promotions.edit', ['id' => $promotionId ?? 1]) }}" class="btn btn-primary">Edit promo</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      @if (session('status'))
        <div class="alert alert-success alert-dismissible mb-3" role="alert">
          {{ session('status') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif
      <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Times used</div>
              <div class="h2 mb-0">{{ $timesUsed }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Patients used</div>
              <div class="h2 mb-0">{{ $uniquePatients }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Used on</div>
              <div class="h3 mb-0 text-truncate" title="{{ $promoTarget }}">{{ $promoTarget }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Revenue generated</div>
              <div class="h2 mb-0">₱{{ number_format($promoRevenue, 2) }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-lg-8">
          <div class="card">
            <div class="card-header border-0">
              <ul class="nav nav-tabs card-header-tabs nav-fill bg-transparent" data-bs-toggle="tabs">
                <li class="nav-item"><a href="#tab-details" class="nav-link active" data-bs-toggle="tab">Promo Details</a></li>
                <li class="nav-item"><a href="#tab-applies" class="nav-link" data-bs-toggle="tab">Applies To</a></li>
                <li class="nav-item"><a href="#tab-usage" class="nav-link" data-bs-toggle="tab">Usage History</a></li>
                <li class="nav-item"><a href="#tab-eligible" class="nav-link" data-bs-toggle="tab">Eligible Patients</a></li>
                <li class="nav-item"><a href="#tab-notes" class="nav-link" data-bs-toggle="tab">Notes</a></li>
              </ul>
            </div>
            <div class="card-body">
              <div class="tab-content">
                <div class="tab-pane active show" id="tab-details">
                  <div class="datagrid">
                    <div class="datagrid-item">
                      <div class="datagrid-title">Promo name</div>
                      <div class="datagrid-content">{{ $promoName }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Promo type</div>
                      <div class="datagrid-content">{{ $promoType }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Discount</div>
                      <div class="datagrid-content">{{ $discount }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Validity</div>
                      <div class="datagrid-content">
                        @if ($isModel)
                          {{ $validityLabel ?? 'No dates set' }}
                        @else
                          {{ \Illuminate\Support\Carbon::parse($validityStart)->format('M j, Y') }} –
                          {{ \Illuminate\Support\Carbon::parse($validityEnd)->format('M j, Y') }}
                        @endif
                      </div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Status</div>
                      <div class="datagrid-content"><span class="badge {{ $statusClass }}">{{ $status }}</span></div>
                    </div>
                  </div>
                </div>

                <div class="tab-pane" id="tab-applies">
                  <div class="datagrid">
                    <div class="datagrid-item">
                      <div class="datagrid-title">Applies to</div>
                      <div class="datagrid-content">{{ $appliesTo }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Scope key</div>
                      <div class="datagrid-content">{{ is_string($appliesCategory) ? ucfirst(str_replace('_', ' ', $appliesCategory)) : '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Linked catalog</div>
                      <div class="datagrid-content">
                        @if ($isModel)
                          @if ($promotion->services->isNotEmpty())
                            <div class="text-secondary small mb-1">Services</div>
                            <ul class="mb-3 ps-3">
                              @foreach ($promotion->services as $s)
                                <li>{{ $s->name }}</li>
                              @endforeach
                            </ul>
                          @endif
                          @if ($promotion->treatmentPackages->isNotEmpty())
                            <div class="text-secondary small mb-1">Treatment packages</div>
                            <ul class="mb-3 ps-3">
                              @foreach ($promotion->treatmentPackages as $pkg)
                                <li>{{ $pkg->name }}</li>
                              @endforeach
                            </ul>
                          @endif
                          @if ($promotion->membershipPlans->isNotEmpty())
                            <div class="text-secondary small mb-1">Membership plans</div>
                            <ul class="mb-3 ps-3">
                              @foreach ($promotion->membershipPlans as $plan)
                                <li>{{ $plan->name }}</li>
                              @endforeach
                            </ul>
                          @endif
                          @if ($promotion->products->isNotEmpty())
                            <div class="text-secondary small mb-1">Products</div>
                            <ul class="mb-0 ps-3">
                              @foreach ($promotion->products as $prod)
                                <li>{{ $prod->name }}@if ($prod->sku)<span class="text-secondary"> · {{ $prod->sku }}</span>@endif</li>
                              @endforeach
                            </ul>
                          @endif
                          @if (
                              $promotion->services->isEmpty() &&
                                  $promotion->treatmentPackages->isEmpty() &&
                                  $promotion->membershipPlans->isEmpty() &&
                                  $promotion->products->isEmpty())
                            <span class="text-secondary">No specific items linked yet.</span>
                          @endif
                        @else
                          <ul class="mb-0 ps-3">
                            <li>10% off facials</li>
                            <li>₱500 off laser treatment</li>
                            <li>Buy 3 sessions, get 1 free</li>
                            <li>Free consultation with membership signup</li>
                            <li>Bridal package discount</li>
                            <li>Birthday month promo</li>
                          </ul>
                        @endif
                      </div>
                    </div>
                  </div>
                </div>

                <div class="tab-pane" id="tab-usage">
                  <div class="table-responsive">
                    <table class="table table-vcenter">
                      <thead>
                        <tr>
                          <th>Date</th>
                          <th>Patient</th>
                          <th>Used On</th>
                          <th>Discount</th>
                          <th class="text-end">Revenue</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse ($usageHistory as $item)
                          <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($item['date'] ?? now())->format('M d, Y') }}</td>
                            <td>{{ $item['patient'] ?? '-' }}</td>
                            <td>{{ $item['target'] ?? '-' }}</td>
                            <td>{{ $item['discount_value'] ?? '-' }}</td>
                            <td class="text-end">₱{{ number_format((float) ($item['revenue'] ?? 0), 2) }}</td>
                          </tr>
                        @empty
                          <tr>
                            <td colspan="5" class="text-center text-secondary py-4">No usage history yet.</td>
                          </tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                </div>

                <div class="tab-pane" id="tab-eligible">
                  <div class="table-responsive">
                    <table class="table table-vcenter">
                      <thead>
                        <tr>
                          <th>Patient</th>
                          <th>Segment</th>
                          <th>Last Visit</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse ($eligiblePatients as $patient)
                          <tr>
                            <td>{{ $patient['name'] ?? '-' }}</td>
                            <td>{{ $patient['segment'] ?? '-' }}</td>
                            <td>{{ $patient['last_visit'] ?? '-' }}</td>
                            <td>
                              <span class="badge {{ ($patient['status'] ?? '') === 'Eligible' ? 'bg-green-lt' : 'bg-yellow-lt' }}">
                                {{ $patient['status'] ?? 'Pending' }}
                              </span>
                            </td>
                          </tr>
                        @empty
                          <tr>
                            <td colspan="4" class="text-center text-secondary py-4">No eligibility data yet.</td>
                          </tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                </div>

                <div class="tab-pane" id="tab-notes">
                  <div class="datagrid">
                    <div class="datagrid-item">
                      <div class="datagrid-title">Terms and conditions</div>
                      <div class="datagrid-content">{{ $terms }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Internal notes</div>
                      <div class="datagrid-content">{{ $notesInternal }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Website display note</div>
                      <div class="datagrid-content">{{ $notesDisplay }}</div>
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
              <h3 class="card-title">Quick summary</h3>
            </div>
            <div class="card-body">
              <div class="datagrid mb-0">
                <div class="datagrid-item">
                  <div class="datagrid-title">Promo ID</div>
                  <div class="datagrid-content font-monospace">#{{ $promotionId ?? 'N/A' }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Type</div>
                  <div class="datagrid-content">{{ $promoType }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Usage</div>
                  <div class="datagrid-content">{{ $timesUsed }} redemptions</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Revenue</div>
                  <div class="datagrid-content">₱{{ number_format($promoRevenue, 2) }}</div>
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Actions</h3>
            </div>
            <div class="card-body">
              <div class="d-grid gap-2">
                <a href="{{ route('admin.promotions.edit', ['id' => $promotionId ?? 1]) }}" class="btn btn-primary">Edit promotion</a>
                <a href="{{ route('admin.promotions') }}" class="btn btn-outline-secondary">Back to list</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection