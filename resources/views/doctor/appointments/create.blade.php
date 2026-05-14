@extends('doctor.layouts.master')

@section('title', 'Treatment Notes')

@section('content')
  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Doctor <span></span> Treatment Notes
        </div>
      </div>
    </div>

    <div class="page-content pt-70 pb-60">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="row">
              @include('doctor.layouts.sidebar')

              <div class="col-12 col-md-9">
                <div class="account dashboard-content pl-50">
                  <div class="section-title mb-20 d-flex justify-content-between align-items-center">
                    <div>
                      <h3 class="mb-5">Treatment Notes</h3>
                      <p class="mb-0">
                        Appointment: <strong>{{ $appointment->appointment_no }}</strong> |
                        Patient: <strong>{{ $appointment->patient_name }}</strong>
                      </p>
                    </div>
                    <a href="{{ route('doctor.appointments') }}" class="btn btn-sm btn-outline">Back to Appointments</a>
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

                  @php
                    $prescribedMap = $appointment->prescribedProducts->keyBy('id');
                  @endphp

                  <form method="POST" action="{{ route('doctor.appointments.notes', $appointment) }}">
                    @csrf

                  <div class="card mb-25 shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom py-3">
                      <h5 class="mb-0">Clinical notes</h5>
                      <p class="text-muted font-sm mb-0 mt-5">Document the encounter. You can save notes only, products only,
                        or both.</p>
                    </div>
                    <div class="card-body pt-25">
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label for="patient_concern" class="form-label">Patient concern</label>
                            <textarea id="patient_concern" name="patient_concern" rows="3" class="form-control"
                              placeholder="Enter patient concern...">{{ old('patient_concern', optional($appointmentNote)->patient_concern) }}</textarea>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label for="appointment_remarks" class="form-label">Appointment remarks</label>
                            <textarea id="appointment_remarks" name="appointment_remarks" rows="3" class="form-control"
                              placeholder="Enter appointment remarks...">{{ old('appointment_remarks', optional($appointmentNote)->appointment_remarks) }}</textarea>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label for="admin_notes" class="form-label">Admin notes</label>
                            <textarea id="admin_notes" name="admin_notes" rows="3" class="form-control"
                              placeholder="Enter admin notes...">{{ old('admin_notes', optional($appointmentNote)->admin_notes) }}</textarea>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label for="doctor_notes" class="form-label">Doctor notes</label>
                            <textarea id="doctor_notes" name="doctor_notes" rows="3" class="form-control"
                              placeholder="Enter doctor notes...">{{ old('doctor_notes', optional($appointmentNote)->doctor_notes) }}</textarea>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label for="instructions" class="form-label">Instructions</label>
                            <textarea id="instructions" name="instructions" rows="3" class="form-control"
                              placeholder="Enter instructions...">{{ old('instructions', optional($appointmentNote)->instructions) }}</textarea>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label for="alerts" class="form-label">Alerts</label>
                            <textarea id="alerts" name="alerts" rows="3" class="form-control"
                              placeholder="Enter alerts...">{{ old('alerts', optional($appointmentNote)->alerts) }}</textarea>
                          </div>
                        </div>
                    </div>
                  </div>

                  <div class="card mb-25 shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom py-3">
                      <h5 class="mb-0">Vital signs</h5>
                      <p class="text-muted font-sm mb-0 mt-5">Optional. Use the units your clinic prefers (e.g. BP mmHg, HR bpm, temp °C, RR /min, SpO2 %, weight kg, height cm).</p>
                    </div>
                    <div class="card-body pt-25">
                      <div class="row">
                        <div class="col-md-4 mb-3">
                          <label for="vital_blood_pressure" class="form-label">Blood pressure</label>
                          <input type="text" id="vital_blood_pressure" name="vital_blood_pressure" class="form-control"
                            placeholder="e.g. 120/80" maxlength="50"
                            value="{{ old('vital_blood_pressure', optional($appointmentNote)->vital_blood_pressure) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                          <label for="vital_heart_rate" class="form-label">Heart rate (pulse)</label>
                          <input type="text" id="vital_heart_rate" name="vital_heart_rate" class="form-control"
                            placeholder="e.g. 72 bpm" maxlength="32"
                            value="{{ old('vital_heart_rate', optional($appointmentNote)->vital_heart_rate) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                          <label for="vital_temperature" class="form-label">Temperature</label>
                          <input type="text" id="vital_temperature" name="vital_temperature" class="form-control"
                            placeholder="e.g. 36.6 °C" maxlength="32"
                            value="{{ old('vital_temperature', optional($appointmentNote)->vital_temperature) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                          <label for="vital_respiratory_rate" class="form-label">Respiratory rate</label>
                          <input type="text" id="vital_respiratory_rate" name="vital_respiratory_rate" class="form-control"
                            placeholder="e.g. 16 /min" maxlength="32"
                            value="{{ old('vital_respiratory_rate', optional($appointmentNote)->vital_respiratory_rate) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                          <label for="vital_oxygen_saturation" class="form-label">Oxygen (SpO2)</label>
                          <input type="text" id="vital_oxygen_saturation" name="vital_oxygen_saturation" class="form-control"
                            placeholder="e.g. 98%" maxlength="32"
                            value="{{ old('vital_oxygen_saturation', optional($appointmentNote)->vital_oxygen_saturation) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                          <label for="vital_weight" class="form-label">Weight</label>
                          <input type="text" id="vital_weight" name="vital_weight" class="form-control"
                            placeholder="e.g. 65 kg" maxlength="32"
                            value="{{ old('vital_weight', optional($appointmentNote)->vital_weight) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                          <label for="vital_height" class="form-label">Height</label>
                          <input type="text" id="vital_height" name="vital_height" class="form-control"
                            placeholder="e.g. 170 cm" maxlength="32"
                            value="{{ old('vital_height', optional($appointmentNote)->vital_height) }}">
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="card mb-25 shadow-sm border-0 prescribe-products-card" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom py-15 prescribe-products-card-header">
                      <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 w-100">
                        <div class="prescribe-header-lead flex-grow-1 min-w-0">
                          <h5 class="mb-0">Prescribe products</h5>
                          <p class="prescribe-header-note font-sm mb-0 mt-5">On-hand from inventory. Does not deduct stock.</p>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2 gap-md-3 prescribe-header-actions">
                          <div class="prescribe-total-pill" id="prescribe-products-total-wrap" aria-live="polite">
                            <span class="prescribe-total-pill__label">Estimated total</span>
                            <span class="prescribe-total-pill__value" id="prescribe-products-total">₱0.00</span>
                          </div>
                          <a href="{{ route('doctor.products') }}" class="btn btn-sm btn-outline-primary flex-shrink-0">
                            View full inventory
                          </a>
                        </div>
                      </div>
                    </div>
                    <div class="card-body px-20 py-15">
                        @if ($products->isEmpty())
                          <p class="text-secondary small mb-0">No active products in the catalog.</p>
                        @else
                          <div class="row prescribe-product-grid g-2">
                            @foreach ($products as $product)
                              @php
                                $onHand = (int) $product->stock_quantity;
                                $unitPrice = (float) ($product->discount_price ?? $product->selling_price ?? 0);
                                $cardTone = match ($product->stock_status) {
                                    'out_of_stock' => 'prescribe-card--oos',
                                    'low_stock' => 'prescribe-card--low',
                                    default => 'prescribe-card--ok',
                                };
                              @endphp
                              <div class="col-12 col-sm-6 col-lg-3 prescribe-product-col" data-unit-price="{{ $unitPrice }}">
                                <div class="card prescribe-product-item h-100 border shadow-sm {{ $cardTone }}">
                                  <div class="card-body prescribe-card-body d-flex flex-column">
                                    <div class="d-flex gap-2 align-items-start mb-5">
                                      <input type="checkbox" class="form-check-input prescribe-card-check flex-shrink-0"
                                        name="prescribe[{{ $product->id }}]" value="1" id="prescribe-{{ $product->id }}"
                                        @checked(old('prescribe.'.$product->id, $prescribedMap->has($product->id)))>
                                      <div class="prescribe-card-title-wrap">
                                        <label class="form-check-label mb-0 prescribe-card-title" for="prescribe-{{ $product->id }}">{{ $product->name }}</label>
                                      </div>
                                    </div>

                                    @if ($product->sku)
                                      <div class="mb-5">
                                        <span class="font-monospace prescribe-card-meta prescribe-card-meta-sku">SKU {{ $product->sku }}</span>
                                      </div>
                                    @endif

                                    <div class="mb-5 prescribe-card-price-wrap">
                                      <div class="prescribe-card-label">Price</div>
                                      <div class="prescribe-card-price">₱{{ $product->final_price }}</div>
                                    </div>

                                    <div class="mb-8 prescribe-card-hand-wrap">
                                      <div class="prescribe-card-hand-block">
                                        <div class="prescribe-card-label">On hand</div>
                                        <div class="font-monospace prescribe-card-stock">
                                          <strong>{{ number_format($onHand) }}</strong>@if ($product->unit)<span class="prescribe-card-unit">{{ $product->unit }}</span>@endif
                                        </div>
                                      </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-2 mt-auto pt-8 border-top prescribe-card-qty-row">
                                      <label class="mb-0 prescribe-card-label" for="qty-{{ $product->id }}">Qty</label>
                                      <input type="number" id="qty-{{ $product->id }}" name="qty[{{ $product->id }}]"
                                        class="form-control form-control-sm prescribe-card-qty-input flex-shrink-0"
                                        min="1" max="99999" step="1"
                                        title="Clinic on hand: {{ number_format($onHand) }} {{ $product->unit ?? '' }}"
                                        value="{{ old('qty.'.$product->id, $prescribedMap->get($product->id)?->pivot->quantity ?? 1) }}"
                                        aria-label="Quantity for {{ $product->name }}">
                                    </div>
                                  </div>
                                </div>
                              </div>
                            @endforeach
                          </div>
                        @endif
                    </div>
                    <div class="card-footer bg-white border-top py-15">
                        <div class="d-flex flex-wrap gap-2">
                          <button type="submit" class="btn btn-sm btn-primary">Save notes &amp; prescriptions</button>
                          <a href="{{ route('doctor.appointments') }}" class="btn btn-sm btn-outline">Cancel</a>
                        </div>
                    </div>
                  </div>

                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <style>
    .prescribe-products-card .prescribe-product-grid {
      margin-bottom: 0;
    }
    .prescribe-products-card .prescribe-card-body {
      padding: 0.65rem 0.7rem !important;
    }
    .prescribe-products-card .prescribe-product-item {
      border-radius: 8px;
      transition: box-shadow 0.15s ease;
    }
    .prescribe-products-card .prescribe-product-item:hover {
      box-shadow: 0 0.25rem 0.65rem rgba(33, 37, 41, 0.07) !important;
    }
    .prescribe-products-card .prescribe-card-title-wrap {
      flex: 1 1 0;
      min-width: 0;
    }
    .prescribe-products-card .prescribe-card-title {
      font-size: 0.8125rem;
      font-weight: 600;
      line-height: 1.35;
      display: block;
      overflow-wrap: anywhere;
      word-break: break-word;
    }
    .prescribe-products-card .prescribe-card-check {
      width: 0.95rem;
      height: 0.95rem;
      margin-top: 0.15rem !important;
    }
    .prescribe-products-card .prescribe-card-meta {
      color: #343a40;
    }
    .prescribe-products-card .prescribe-card-meta-sku {
      flex: 1 1 auto;
      min-width: 0;
      font-size: 0.75rem;
      overflow-wrap: anywhere;
      word-break: break-word;
    }
    .prescribe-products-card .prescribe-card-label {
      font-size: 0.6875rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      color: #495057;
      line-height: 1.3;
    }
    .prescribe-products-card .prescribe-card-stock {
      font-size: 1rem;
      line-height: 1.3;
      color: #212529;
    }
    .prescribe-products-card .prescribe-card-stock strong {
      font-weight: 700;
    }
    .prescribe-products-card .prescribe-card-unit {
      font-size: 0.75rem;
      margin-left: 0.2rem;
      color: #495057;
    }
    .prescribe-products-card .prescribe-card-hand-block {
      min-width: 0;
    }
    .prescribe-products-card .prescribe-card-price {
      font-size: 1rem;
      font-weight: 700;
      line-height: 1.3;
      color: var(--primary);
    }
    .prescribe-products-card .prescribe-card-hand-wrap {
      padding-bottom: 0.2rem;
    }
    .prescribe-products-card .prescribe-card-qty-row {
      padding-top: 0.55rem !important;
      margin-top: 0.25rem !important;
    }
    .prescribe-products-card .prescribe-card-qty-input {
      width: 3.5rem;
      max-width: 3.5rem;
      min-height: 1.4rem;
      padding: 0.1rem 0.3rem;
      font-size: 0.75rem;
      line-height: 1.2;
      margin-left: auto;
      text-align: center;
    }
    .prescribe-products-card .prescribe-card--oos {
      border-color: rgba(220, 53, 69, 0.35) !important;
      background: linear-gradient(180deg, rgba(220, 53, 69, 0.05) 0%, #fff 55%);
    }
    .prescribe-products-card .prescribe-card--low {
      border-color: rgba(255, 193, 7, 0.45) !important;
      background: linear-gradient(180deg, rgba(255, 193, 7, 0.07) 0%, #fff 55%);
    }
    .prescribe-products-card .prescribe-card--ok {
      border-color: rgba(0, 0, 0, 0.06);
    }
    .prescribe-products-card-header .prescribe-header-note {
      line-height: 1.35;
      color: var(--text-secondary);
    }
    .prescribe-total-pill {
      display: inline-flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 0.1rem;
      padding: 0.5rem 0.95rem;
      border-radius: 10px;
      background: var(--surface);
      border: 1px solid color-mix(in srgb, var(--primary) 22%, transparent);
      box-shadow: 0 1px 2px rgba(47, 35, 44, 0.04);
    }
    .prescribe-total-pill__label {
      font-size: 0.625rem;
      font-weight: 600;
      letter-spacing: 0.07em;
      text-transform: uppercase;
      color: var(--text-secondary);
    }
    .prescribe-total-pill__value {
      font-size: 1.125rem;
      font-weight: 700;
      color: var(--primary);
      font-variant-numeric: tabular-nums;
      letter-spacing: -0.02em;
      line-height: 1.15;
    }
    @media (max-width: 575.98px) {
      .prescribe-header-actions {
        width: 100%;
        justify-content: space-between;
      }
      .prescribe-total-pill {
        align-items: flex-start;
      }
    }
  </style>
  @if (! $products->isEmpty())
    <script>
      (function () {
        var root = document.querySelector('.prescribe-products-card');
        var totalEl = document.getElementById('prescribe-products-total');
        if (!root || !totalEl) return;

        function parseQty(val) {
          var n = parseInt(String(val), 10);
          return (isNaN(n) || n < 1) ? 0 : n;
        }

        function formatMoney(n) {
          return n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function updateTotal() {
          var sum = 0;
          root.querySelectorAll('.prescribe-product-col').forEach(function (col) {
            var unit = parseFloat(col.getAttribute('data-unit-price'));
            if (isNaN(unit)) unit = 0;
            var cb = col.querySelector('.prescribe-card-check');
            var qtyInput = col.querySelector('.prescribe-card-qty-input');
            if (!cb || !qtyInput) return;
            if (cb.checked) {
              sum += unit * parseQty(qtyInput.value);
            }
          });
          totalEl.textContent = '₱' + formatMoney(sum);
        }

        root.addEventListener('change', function (e) {
          var t = e.target;
          if (t && (t.matches('.prescribe-card-check') || t.matches('.prescribe-card-qty-input'))) {
            updateTotal();
          }
        });
        root.addEventListener('input', function (e) {
          if (e.target.classList && e.target.classList.contains('prescribe-card-qty-input')) {
            updateTotal();
          }
        });
        updateTotal();
      })();
    </script>
  @endif

  @push('scripts')
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        var id = (window.location.hash || '').replace(/^#/, '');
        if (!id) return;
        var el = document.getElementById(id);
        if (!el || typeof el.focus !== 'function') return;
        el.focus({ preventScroll: false });
      });
    </script>
  @endpush
@endsection
