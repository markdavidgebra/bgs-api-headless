@extends('admin.layouts.master')

@php
  /** @var \App\Models\Payment $payment */

  $statusLabel = ucfirst((string) $payment->payment_status);
  $statusClass = $payment->status_badge;
  $formattedDate = $payment->payment_date?->format('M d, Y') ?? '—';
  $transactionReference = $payment->transaction_reference ?? '—';
  $methodLabel = $payment->method_label;
  $referenceTypeLabel = $payment->reference_type_label;
  $referenceName = $payment->reference_name ?? '—';
  $assignedDoctorName = $payment->assigned_doctor_name ?? '—';
  $assignedDoctorId = $payment->reference_type === 'appointment'
    ? $payment->referenceAppointment?->doctor_id
    : null;

  $relatedRecordUrl = $payment->reference_id
    ? match ($payment->reference_type) {
        'appointment' => route('admin.appointments.show', $payment->reference_id),
        'service' => route('admin.services.show', $payment->reference_id),
        'package' => route('admin.packages.show', $payment->reference_id),
        'membership' => route('admin.subscriptions.show', $payment->reference_id),
        'product' => route('admin.products.show', $payment->reference_id),
        default => null,
      }
    : null;

  $assignedDoctorUrl = $assignedDoctorId
    ? route('admin.doctors.show', $assignedDoctorId)
    : null;

  $patientUrl = route('admin.patients.show', $payment->patient_id);
@endphp

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col-auto">
          <span class="avatar avatar-xl rounded bg-azure-lt text-azure">₱</span>
        </div>
        <div class="col">
          <div class="page-pretitle text-secondary">Payments</div>
          <h2 class="page-title mb-0">{{ $payment->payment_id }}</h2>
          <div class="text-secondary small mt-1">
            {{ $referenceTypeLabel }} · {{ $formattedDate }} ·
            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
          </div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.payments') }}" class="btn">Back to payments</a>
            <a href="{{ route('admin.payments.create') }}" class="btn btn-outline-primary">Add payment</a>
            <button type="button" class="btn btn-primary" onclick="window.print()">Print</button>
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
              <div class="text-secondary">Amount</div>
              <div class="h2 mb-0">{{ $payment->formatted_amount }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Method</div>
              <div class="h3 mb-0 fw-normal">{{ $methodLabel }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Status</div>
              <div class="h3 mb-0 fw-normal"><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Database ID</div>
              <div class="h3 mb-0 font-monospace fw-normal">{{ $payment->id }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-lg-8">
          <div class="card mb-3">
            <div class="card-header">
              <h3 class="card-title">Payment information</h3>
            </div>
            <div class="card-body">
              <div class="datagrid">
                <div class="datagrid-item">
                  <div class="datagrid-title">Payment ID</div>
                  <div class="datagrid-content font-monospace">{{ $payment->payment_id }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Internal ID</div>
                  <div class="datagrid-content font-monospace">{{ $payment->id }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Amount</div>
                  <div class="datagrid-content">{{ $payment->formatted_amount }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Payment method</div>
                  <div class="datagrid-content">{{ $methodLabel }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Payment status</div>
                  <div class="datagrid-content"><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Payment date</div>
                  <div class="datagrid-content">{{ $formattedDate }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Transaction reference</div>
                  <div class="datagrid-content font-monospace">{{ $transactionReference }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Recorded</div>
                  <div class="datagrid-content">
                    {{ $payment->created_at?->timezone(config('app.timezone'))->format('M d, Y H:i') ?? '—' }}
                    @if ($payment->updated_at && ! $payment->updated_at->equalTo($payment->created_at))
                      <span class="text-secondary small d-block mt-1">
                        Updated {{ $payment->updated_at->timezone(config('app.timezone'))->format('M d, Y H:i') }}
                      </span>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="card mb-3">
            <div class="card-header">
              <h3 class="card-title">Patient</h3>
            </div>
            <div class="card-body">
              <div class="datagrid">
                <div class="datagrid-item">
                  <div class="datagrid-title">Name</div>
                  <div class="datagrid-content">
                    <a href="{{ $patientUrl }}">{{ $payment->patient->name ?? '—' }}</a>
                  </div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Contact</div>
                  <div class="datagrid-content">{{ $payment->patient->phone ?? '—' }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Email</div>
                  <div class="datagrid-content">{{ $payment->patient->email ?? '—' }}</div>
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Related record</h3>
            </div>
            <div class="card-body">
              <div class="datagrid">
                <div class="datagrid-item">
                  <div class="datagrid-title">Reference type</div>
                  <div class="datagrid-content">{{ $referenceTypeLabel }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Reference ID</div>
                  <div class="datagrid-content font-monospace">
                    @if ($payment->reference_id !== null)
                      @if ($relatedRecordUrl)
                        <a href="{{ $relatedRecordUrl }}">{{ $payment->reference_id }}</a>
                      @else
                        {{ $payment->reference_id }}
                      @endif
                    @else
                      —
                    @endif
                  </div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Name</div>
                  <div class="datagrid-content">
                    @if ($relatedRecordUrl && $referenceName !== '—')
                      <a href="{{ $relatedRecordUrl }}">{{ $referenceName }}</a>
                    @else
                      {{ $referenceName }}
                    @endif
                  </div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Assigned doctor</div>
                  <div class="datagrid-content">
                    @if ($assignedDoctorUrl && $assignedDoctorName !== '—')
                      <a href="{{ $assignedDoctorUrl }}">{{ $assignedDoctorName }}</a>
                    @else
                      {{ $assignedDoctorName }}
                    @endif
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="card mb-3">
            <div class="card-header">
              <h3 class="card-title">Notes</h3>
            </div>
            <div class="card-body">
              @if ($payment->notes)
                <div class="text-secondary small mb-1">Staff notes</div>
                <div>{{ $payment->notes }}</div>
              @else
                <div class="text-secondary">No notes for this payment.</div>
              @endif
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Receipt</h3>
            </div>
            <div class="card-body">
              <p class="text-secondary small mb-3">
                Use print to save or share this page as a payment summary. PDF export can be added later if you need a standalone file.
              </p>
              <div class="datagrid mb-3">
                <div class="datagrid-item">
                  <div class="datagrid-title">Payment ID</div>
                  <div class="datagrid-content font-monospace">{{ $payment->payment_id }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Amount</div>
                  <div class="datagrid-content">{{ $payment->formatted_amount }}</div>
                </div>
              </div>
              <div class="d-grid gap-2 d-print-none">
                <button type="button" class="btn btn-primary" onclick="window.print()">Print receipt</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
