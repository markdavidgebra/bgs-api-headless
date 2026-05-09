@extends('admin.layouts.master')

@section('content')
  @php
    $refTypeOld = old('reference_type', 'appointment');
    $methodOptions = [
      'cash' => 'Cash',
      'gcash' => 'GCash',
      'maya' => 'Maya',
      'card' => 'Card',
      'bank_transfer' => 'Bank transfer',
    ];
    $statusOptions = [
      'paid' => 'Paid',
      'unpaid' => 'Unpaid',
      'partial' => 'Partial',
      'refunded' => 'Refunded',
      'cancelled' => 'Cancelled',
    ];
    $typeOptions = [
      'appointment' => 'Appointment',
      'package' => 'Treatment package',
      'membership' => 'Membership plan',
      'product' => 'Product',
    ];
  @endphp

  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col-auto">
          <span class="avatar avatar-xl rounded bg-azure-lt text-azure">₱</span>
        </div>
        <div class="col">
          <div class="page-pretitle text-secondary">Payments</div>
          <h2 class="page-title mb-0">Add payment</h2>
          <div class="text-secondary small mt-1">Record a payment against a patient and optional catalog reference.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.payments') }}" class="btn">Cancel</a>
            <button type="submit" form="payment-create-form" class="btn btn-primary">Save payment</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row g-3">
        <div class="col-lg-8">
          <form id="payment-create-form" method="POST" action="{{ route('admin.payments.store') }}">
            @csrf

            <div class="card mb-3">
              <div class="card-header">
                <h3 class="card-title mb-0">Payment details</h3>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label required" for="patient_id">Patient</label>
                    <select id="patient_id" name="patient_id" class="form-select @error('patient_id') is-invalid @enderror"
                      required>
                      <option value="">Select patient (user)</option>
                      @foreach ($patients as $user)
                        <option value="{{ $user->id }}" @selected((string) old('patient_id') === (string) $user->id)>
                          {{ $user->name }} @if ($user->email)<span class="text-secondary">· {{ $user->email }}</span>@endif
                        </option>
                      @endforeach
                    </select>
                    @error('patient_id')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-md-6">
                    <label class="form-label required" for="reference_type">Reference type</label>
                    <select id="reference_type" name="reference_type"
                      class="form-select @error('reference_type') is-invalid @enderror" required>
                      @foreach ($typeOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('reference_type', 'appointment') === $value)>{{ $label }}
                        </option>
                      @endforeach
                    </select>
                    @error('reference_type')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-hint">Then choose a specific row below, or leave “None” if not linked yet.</small>
                  </div>

                  <div class="col-12">
                    <label class="form-label" for="reference_id_appointment">Linked record</label>

                    <div class="ref-block {{ $refTypeOld === 'appointment' ? '' : 'd-none' }}" data-ref-block="appointment">
                      <select id="reference_id_appointment" name="reference_id"
                        class="form-select ref-select @error('reference_id') is-invalid @enderror"
                        {{ $refTypeOld === 'appointment' ? '' : 'disabled' }}>
                        <option value="">— None —</option>
                        @foreach ($appointments as $apt)
                          <option value="{{ $apt->id }}" @selected((string) old('reference_id') === (string) $apt->id)>
                            {{ $apt->appointment_no }}
                            @if ($apt->service)
                              · {{ $apt->service->name }}
                            @endif
                            · {{ $apt->appointment_date?->format('Y-m-d') ?? '—' }}
                          </option>
                        @endforeach
                      </select>
                    </div>

                    <div class="ref-block {{ $refTypeOld === 'package' ? '' : 'd-none' }}" data-ref-block="package">
                      <select id="reference_id_package" name="reference_id"
                        class="form-select ref-select @error('reference_id') is-invalid @enderror"
                        {{ $refTypeOld === 'package' ? '' : 'disabled' }}>
                        <option value="">— None —</option>
                        @foreach ($packages as $pkg)
                          <option value="{{ $pkg->id }}" @selected((string) old('reference_id') === (string) $pkg->id)>
                            {{ $pkg->name }}</option>
                        @endforeach
                      </select>
                    </div>

                    <div class="ref-block {{ $refTypeOld === 'membership' ? '' : 'd-none' }}" data-ref-block="membership">
                      <select id="reference_id_membership" name="reference_id"
                        class="form-select ref-select @error('reference_id') is-invalid @enderror"
                        {{ $refTypeOld === 'membership' ? '' : 'disabled' }}>
                        <option value="">— None —</option>
                        @foreach ($memberships as $plan)
                          <option value="{{ $plan->id }}" @selected((string) old('reference_id') === (string) $plan->id)>
                            {{ $plan->name }}</option>
                        @endforeach
                      </select>
                    </div>

                    <div class="ref-block {{ $refTypeOld === 'product' ? '' : 'd-none' }}" data-ref-block="product">
                      <select id="reference_id_product" name="reference_id"
                        class="form-select ref-select @error('reference_id') is-invalid @enderror"
                        {{ $refTypeOld === 'product' ? '' : 'disabled' }}>
                        <option value="">— None —</option>
                        @foreach ($products as $prod)
                          <option value="{{ $prod->id }}" @selected((string) old('reference_id') === (string) $prod->id)>
                            {{ $prod->name }}@if ($prod->sku) ({{ $prod->sku }}) @endif</option>
                        @endforeach
                      </select>
                    </div>
                    @error('reference_id')
                      <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-md-4">
                    <label class="form-label required" for="amount">Amount (₱)</label>
                    <input id="amount" name="amount" type="number" min="0" step="0.01" required
                      class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}"
                      placeholder="0.00">
                    @error('amount')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-md-4">
                    <label class="form-label required" for="payment_method">Payment method</label>
                    <select id="payment_method" name="payment_method"
                      class="form-select @error('payment_method') is-invalid @enderror" required>
                      <option value="">— Select —</option>
                      @foreach ($methodOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ $label }}</option>
                      @endforeach
                    </select>
                    @error('payment_method')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-md-4">
                    <label class="form-label required" for="payment_status">Status</label>
                    <select id="payment_status" name="payment_status"
                      class="form-select @error('payment_status') is-invalid @enderror" required>
                      @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('payment_status', 'paid') === $value)>{{ $label }}
                        </option>
                      @endforeach
                    </select>
                    @error('payment_status')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-md-6">
                    <label class="form-label" for="payment_date">Payment date</label>
                    <input id="payment_date" name="payment_date" type="date"
                      class="form-control @error('payment_date') is-invalid @enderror"
                      value="{{ old('payment_date', now()->toDateString()) }}">
                    @error('payment_date')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-md-6">
                    <label class="form-label" for="transaction_reference">Transaction reference</label>
                    <input id="transaction_reference" name="transaction_reference" type="text" maxlength="255"
                      class="form-control @error('transaction_reference') is-invalid @enderror"
                      value="{{ old('transaction_reference') }}" placeholder="Gateway / bank ref">
                    @error('transaction_reference')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-12">
                    <label class="form-label" for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3"
                      class="form-control @error('notes') is-invalid @enderror"
                      placeholder="Cashier or reconciliation notes">{{ old('notes') }}</textarea>
                    @error('notes')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>

        <div class="col-lg-4">
          <div class="card mb-3">
            <div class="card-header">
              <h3 class="card-title">Checklist</h3>
            </div>
            <div class="card-body">
              <ul class="text-secondary mb-0 ps-3 small">
                <li><strong>Payment ID</strong> is generated automatically (e.g. PAY-0007).</li>
                <li>Patients are <strong>users</strong> (same as appointments).</li>
                <li>Link the correct reference type so reports match the catalog.</li>
              </ul>
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
      var typeEl = document.getElementById('reference_type');
      if (!typeEl) return;

      function sync() {
        var t = typeEl.value;
        document.querySelectorAll('[data-ref-block]').forEach(function (wrap) {
          var on = wrap.getAttribute('data-ref-block') === t;
          wrap.classList.toggle('d-none', !on);
          var sel = wrap.querySelector('select');
          if (sel) sel.disabled = !on;
        });
      }

      typeEl.addEventListener('change', sync);
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', sync);
      } else {
        sync();
      }
    })();
  </script>
@endpush
