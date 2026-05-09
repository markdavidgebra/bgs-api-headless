@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-3 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Promotions</div>
          <h2 class="page-title">Email Blast</h2>
          <div class="text-secondary small mt-1">
            Send promo updates to patients with active email addresses.
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
      @endif

      <div class="row g-3">
        <div class="col-lg-8">
          <form method="POST" action="{{ route('admin.promotions.email.send') }}">
            @csrf
            <div class="card">
              <div class="card-header">
                <h3 class="card-title mb-0">Compose blast</h3>
              </div>
              <div class="card-body">
                <div class="mb-3">
                  <label class="form-label required" for="promotion_id">Promotion</label>
                  <select id="promotion_id" name="promotion_id" class="form-select @error('promotion_id') is-invalid @enderror" required>
                    <option value="">Select active promo</option>
                    @foreach ($promotions as $promotion)
                      <option value="{{ $promotion->id }}" @selected((string) old('promotion_id') === (string) $promotion->id)>
                        {{ $promotion->name }}
                        @if ($promotion->code)
                          ({{ $promotion->code }})
                        @endif
                      </option>
                    @endforeach
                  </select>
                  @error('promotion_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                  <label class="form-label" for="subject">Email subject (optional)</label>
                  <input id="subject" name="subject" type="text" class="form-control @error('subject') is-invalid @enderror"
                    value="{{ old('subject') }}" maxlength="255" placeholder="Uses default promo subject when empty">
                  @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-0">
                  <label class="form-label" for="message">Message (optional)</label>
                  <textarea id="message" name="message" rows="6" class="form-control @error('message') is-invalid @enderror"
                    placeholder="Add a custom message to include in the blast.">{{ old('message') }}</textarea>
                  @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>
              <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary" @disabled($recipientCount <= 0 || $promotions->isEmpty())>
                  Send email blast
                </button>
              </div>
            </div>
          </form>
        </div>

        <div class="col-lg-4">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title mb-0">Audience</h3>
            </div>
            <div class="card-body">
              <div class="h2 mb-1">{{ number_format($recipientCount) }}</div>
              <div class="text-secondary">Patients with valid email records</div>
              @if ($recipientCount <= 0)
                <div class="alert alert-warning mt-3 mb-0">
                  No recipients found. Add patient email addresses first.
                </div>
              @endif
              @if ($promotions->isEmpty())
                <div class="alert alert-warning mt-3 mb-0">
                  No active promotions found. Activate a promo before sending.
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
