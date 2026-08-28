@extends('clinical-staff.layouts.master')

@section('title', 'Treatment Notes')

@section('content')
  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Clinical staff <span></span> Treatment Notes
        </div>
      </div>
    </div>

    <div class="page-content pt-70 pb-60">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="row">
              @include('clinical-staff.layouts.sidebar')

              <div class="col-12">
                <div class="account dashboard-content pl-50">
                  <div class="section-title mb-20 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                      <h3 class="mb-5">Treatment Notes</h3>
                      <p class="mb-0">
                        Appointment: <strong>{{ $appointment->appointment_no }}</strong> |
                        Patient: <strong>{{ $appointment->patient_name }}</strong>
                      </p>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                      @if ($appointment->patient_id)
                        <a href="{{ route('clinical_staff.patient-records.show', $appointment->patient_id) }}" class="btn btn-sm btn-outline-primary">Patient History</a>
                      @endif
                      <a href="{{ route('clinical_staff.appointments.show', $appointment) }}" class="btn btn-sm btn-outline">Back to appointment</a>
                    </div>
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
                    $mobilityForm = old('mobility', optional($appointmentNote)->mobility);
                  @endphp

                  <form method="POST" action="{{ route('clinical_staff.appointments.notes', $appointment) }}" enctype="multipart/form-data">
                    @csrf

                  <div class="card mb-25 shadow-sm border-0" id="notes-create-doc" style="border-radius: 12px;"
                    data-initial-notes-tab="{{ $errors->has('mobility') ? 'assessment' : 'clinical' }}">
                    <div class="card-header bg-white border-bottom py-3">
                      <div class="d-flex flex-wrap align-items-center gap-2 mb-2" role="tablist" aria-label="{{ __('Encounter documentation') }}">
                        <button type="button" class="notes-create-tab-btn active" data-notes-tab="clinical" id="notes-tab-clinical-btn" role="tab" aria-selected="true" aria-controls="notes-panel-clinical">{{ __('Clinical notes') }}</button>
                        <button type="button" class="notes-create-tab-btn" data-notes-tab="assessment" id="notes-tab-assessment-btn" role="tab" aria-selected="false" aria-controls="notes-panel-assessment">{{ __('Assessment Checklist') }}</button>
                      </div>
                      <p class="text-muted font-sm mb-0 notes-create-tab-desc text-start" data-notes-desc="clinical">{{ __('Document the encounter. You can save notes only, products only, or both.') }}</p>
                      <p class="text-muted font-sm mb-0 notes-create-tab-desc text-start d-none" data-notes-desc="assessment">{{ __('Record how the patient moves for this visit.') }}</p>
                    </div>
                    <div class="card-body pt-25">
                      <div class="notes-create-tab-panel active" id="notes-panel-clinical" data-notes-panel="clinical" role="tabpanel" aria-labelledby="notes-tab-clinical-btn">
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label for="patient_concern" class="form-label">Patient concern</label>
                            <textarea id="patient_concern" name="patient_concern" rows="3" class="form-control"
                              placeholder="Enter patient concern...">{{ old('patient_concern', optional($appointmentNote)->patient_concern) }}</textarea>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label for="appointment_remarks" class="form-label">Post procedures</label>
                            <textarea id="appointment_remarks" name="appointment_remarks" rows="3" class="form-control"
                              placeholder="Enter post procedures...">{{ old('appointment_remarks', optional($appointmentNote)->appointment_remarks) }}</textarea>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label for="admin_notes" class="form-label">Medical history</label>
                            <textarea id="admin_notes" name="admin_notes" rows="3" class="form-control"
                              placeholder="Enter medical history...">{{ old('admin_notes', optional($appointmentNote)->admin_notes) }}</textarea>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label for="clinical_notes" class="form-label">Clinical notes</label>
                            <textarea id="clinical_notes" name="clinical_notes" rows="3" class="form-control"
                              placeholder="Enter clinical notes...">{{ old('clinical_notes', optional($appointmentNote)->clinical_notes) }}</textarea>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label for="alerts" class="form-label">Allergy</label>
                            <textarea id="alerts" name="alerts" rows="3" class="form-control"
                              placeholder="Enter allergies...">{{ old('alerts', optional($appointmentNote)->alerts) }}</textarea>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label for="instructions" class="form-label">Take home medications</label>
                            <textarea id="instructions" name="instructions" rows="3" class="form-control"
                              placeholder="Enter take home medications...">{{ old('instructions', optional($appointmentNote)->instructions) }}</textarea>
                          </div>
                        </div>
                      </div>
                      <div class="notes-create-tab-panel" id="notes-panel-assessment" data-notes-panel="assessment" role="tabpanel" aria-labelledby="notes-tab-assessment-btn">
                        <label class="form-label d-block mb-2 text-start">{{ __('Mobility') }}</label>
                        <div class="d-flex flex-column flex-sm-row flex-wrap gap-2 gap-sm-3">
                          <div class="form-check p-3 border rounded text-start" style="min-width: 140px;">
                            <input class="form-check-input" type="radio" name="mobility" id="create-mob-ambulatory" value="ambulatory" @checked($mobilityForm === 'ambulatory')>
                            <label class="form-check-label" for="create-mob-ambulatory">{{ __('Ambulatory') }}</label>
                          </div>
                          <div class="form-check p-3 border rounded text-start" style="min-width: 140px;">
                            <input class="form-check-input" type="radio" name="mobility" id="create-mob-assistive" value="with_assistive" @checked($mobilityForm === 'with_assistive')>
                            <label class="form-check-label" for="create-mob-assistive">{{ __('With assistive device') }}</label>
                          </div>
                          <div class="form-check p-3 border rounded text-start" style="min-width: 140px;">
                            <input class="form-check-input" type="radio" name="mobility" id="create-mob-wheelchair" value="wheelchair" @checked($mobilityForm === 'wheelchair')>
                            <label class="form-check-label" for="create-mob-wheelchair">{{ __('Wheelchair') }}</label>
                          </div>
                        </div>
                        @error('mobility')
                          <div class="text-danger small mt-2 text-start">{{ $message }}</div>
                        @enderror
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

                      <div class="row vital-signs-clinical-images g-3 mt-3 pt-3 border-top">
                        <div class="col-12 col-md-4 d-flex">
                          <div class="clinical-image-tile flex-grow-1 w-100 border rounded p-3 bg-light text-center body-analyzer-upload">
                            <label for="body_analyzer_image" class="form-label d-block">Body analyzer image</label>
                            <p class="text-muted font-sm mb-3">Optional. Upload a screenshot or export from your body composition analyzer (JPEG, PNG, GIF, or WebP — max 3&nbsp;MB).</p>
                            @if (optional($appointmentNote)->hasBodyAnalyzerImagePath())
                              @php
                                $baPreviewUrl = $appointmentNote?->bodyAnalyzerImageUrl();
                              @endphp
                              <div class="mb-3">
                                @if ($baPreviewUrl)
                                  <img src="{{ $baPreviewUrl }}" alt="{{ __('Current body analyzer image') }}" class="img-thumbnail mx-auto d-block shadow-sm" style="max-width: 100%; max-height: 220px; width: auto; height: auto; object-fit: contain;" id="body-analyzer-preview-img">
                                  <p id="body-analyzer-img-fallback" class="small text-warning mt-2 mb-0 d-none" role="status"></p>
                                  <p class="small text-secondary mb-0 mt-2">{{ __('Upload a new file to replace this image.') }}</p>
                                @else
                                  <p class="small text-warning mb-2">{{ __('A saved image path exists, but the file was not found on the server. Upload a new file to replace it.') }}</p>
                                @endif
                              </div>
                            @endif
                            <div class="body-analyzer-file-wrap mx-auto text-start">
                              <input type="file" id="body_analyzer_image" name="body_analyzer_image" class="form-control form-control-sm"
                                accept="image/jpeg,image/png,image/gif,image/webp,.jpg,.jpeg,.png,.gif,.webp"
                                data-max-bytes="3145728">
                            </div>
                            <p class="small text-danger mt-2 mb-0 d-none" id="body-analyzer-image-error" role="alert"></p>
                          </div>
                        </div>

                        <div class="col-12 col-md-4 d-flex">
                          <div class="clinical-image-tile flex-grow-1 w-100 border rounded p-3 bg-light text-center bottle-citrus-upload">
                            <label for="bottle_citrus_image" class="form-label d-block">Bottle citrus</label>
                            <p class="text-muted font-sm mb-3">Optional. Upload a photo of bottle citrus or related documentation (JPEG, PNG, GIF, or WebP — max 3&nbsp;MB).</p>
                            @if (optional($appointmentNote)->hasBottleCitrusImagePath())
                              @php
                                $bcPreviewUrl = $appointmentNote?->bottleCitrusImageUrl();
                              @endphp
                              <div class="mb-3">
                                @if ($bcPreviewUrl)
                                  <img src="{{ $bcPreviewUrl }}" alt="{{ __('Current bottle citrus image') }}" class="img-thumbnail mx-auto d-block shadow-sm" style="max-width: 100%; max-height: 220px; width: auto; height: auto; object-fit: contain;" id="bottle-citrus-preview-img">
                                  <p id="bottle-citrus-img-fallback" class="small text-warning mt-2 mb-0 d-none" role="status"></p>
                                  <p class="small text-secondary mb-0 mt-2">{{ __('Upload a new file to replace this image.') }}</p>
                                @else
                                  <p class="small text-warning mb-2">{{ __('A saved image path exists, but the file was not found on the server. Upload a new file to replace it.') }}</p>
                                @endif
                              </div>
                            @endif
                            <div class="bottle-citrus-file-wrap mx-auto text-start">
                              <input type="file" id="bottle_citrus_image" name="bottle_citrus_image" class="form-control form-control-sm"
                                accept="image/jpeg,image/png,image/gif,image/webp,.jpg,.jpeg,.png,.gif,.webp"
                                data-max-bytes="3145728">
                            </div>
                            <p class="small text-danger mt-2 mb-0 d-none" id="bottle-citrus-image-error" role="alert"></p>
                          </div>
                        </div>

                        <div class="col-12 col-md-4 d-flex">
                          <div class="clinical-image-tile flex-grow-1 w-100 border rounded p-3 bg-light text-center lemon-bottle-upload">
                            <label for="lemon_bottle_image" class="form-label d-block">Lemon bottle</label>
                            <p class="text-muted font-sm mb-3">Optional. Upload a photo of the lemon bottle or related documentation (JPEG, PNG, GIF, or WebP — max 3&nbsp;MB).</p>
                            @if (optional($appointmentNote)->hasLemonBottleImagePath())
                              @php
                                $lbPreviewUrl = $appointmentNote?->lemonBottleImageUrl();
                              @endphp
                              <div class="mb-3">
                                @if ($lbPreviewUrl)
                                  <img src="{{ $lbPreviewUrl }}" alt="{{ __('Current lemon bottle image') }}" class="img-thumbnail mx-auto d-block shadow-sm" style="max-width: 100%; max-height: 220px; width: auto; height: auto; object-fit: contain;" id="lemon-bottle-preview-img">
                                  <p id="lemon-bottle-img-fallback" class="small text-warning mt-2 mb-0 d-none" role="status"></p>
                                  <p class="small text-secondary mb-0 mt-2">{{ __('Upload a new file to replace this image.') }}</p>
                                @else
                                  <p class="small text-warning mb-2">{{ __('A saved image path exists, but the file was not found on the server. Upload a new file to replace it.') }}</p>
                                @endif
                              </div>
                            @endif
                            <div class="lemon-bottle-file-wrap mx-auto text-start">
                              <input type="file" id="lemon_bottle_image" name="lemon_bottle_image" class="form-control form-control-sm"
                                accept="image/jpeg,image/png,image/gif,image/webp,.jpg,.jpeg,.png,.gif,.webp"
                                data-max-bytes="3145728">
                            </div>
                            <p class="small text-danger mt-2 mb-0 d-none" id="lemon-bottle-image-error" role="alert"></p>
                          </div>
                        </div>

                        <div class="col-12 col-md-4 d-flex">
                          <div class="clinical-image-tile flex-grow-1 w-100 border rounded p-3 bg-light text-center aqualyx-upload">
                            <label for="aqualyx_image" class="form-label d-block">Aqualyx</label>
                            <p class="text-muted font-sm mb-3">Optional. Upload a photo of the Aqualyx product, vial, or related documentation (JPEG, PNG, GIF, or WebP — max 3&nbsp;MB).</p>
                            @if (optional($appointmentNote)->hasAqualyxImagePath())
                              @php
                                $aqPreviewUrl = $appointmentNote?->aqualyxImageUrl();
                              @endphp
                              <div class="mb-3">
                                @if ($aqPreviewUrl)
                                  <img src="{{ $aqPreviewUrl }}" alt="{{ __('Current Aqualyx image') }}" class="img-thumbnail mx-auto d-block shadow-sm" style="max-width: 100%; max-height: 220px; width: auto; height: auto; object-fit: contain;" id="aqualyx-preview-img">
                                  <p id="aqualyx-img-fallback" class="small text-warning mt-2 mb-0 d-none" role="status"></p>
                                  <p class="small text-secondary mb-0 mt-2">{{ __('Upload a new file to replace this image.') }}</p>
                                @else
                                  <p class="small text-warning mb-2">{{ __('A saved image path exists, but the file was not found on the server. Upload a new file to replace it.') }}</p>
                                @endif
                              </div>
                            @endif
                            <div class="aqualyx-file-wrap mx-auto text-start">
                              <input type="file" id="aqualyx_image" name="aqualyx_image" class="form-control form-control-sm"
                                accept="image/jpeg,image/png,image/gif,image/webp,.jpg,.jpeg,.png,.gif,.webp"
                                data-max-bytes="3145728">
                            </div>
                            <p class="small text-danger mt-2 mb-0 d-none" id="aqualyx-image-error" role="alert"></p>
                          </div>
                        </div>

                        <div class="col-12 col-md-4 d-flex">
                          <div class="clinical-image-tile flex-grow-1 w-100 border rounded p-3 bg-light text-center drip-upload">
                            <label for="drip_image" class="form-label d-block">Drip</label>
                            <p class="text-muted font-sm mb-3">Optional. Upload a photo of the IV drip setup, bag, line, or related documentation (JPEG, PNG, GIF, or WebP — max 3&nbsp;MB).</p>
                            @if (optional($appointmentNote)->hasDripImagePath())
                              @php
                                $dripPreviewUrl = $appointmentNote?->dripImageUrl();
                              @endphp
                              <div class="mb-3">
                                @if ($dripPreviewUrl)
                                  <img src="{{ $dripPreviewUrl }}" alt="{{ __('Current drip image') }}" class="img-thumbnail mx-auto d-block shadow-sm" style="max-width: 100%; max-height: 220px; width: auto; height: auto; object-fit: contain;" id="drip-preview-img">
                                  <p id="drip-img-fallback" class="small text-warning mt-2 mb-0 d-none" role="status"></p>
                                  <p class="small text-secondary mb-0 mt-2">{{ __('Upload a new file to replace this image.') }}</p>
                                @else
                                  <p class="small text-warning mb-2">{{ __('A saved image path exists, but the file was not found on the server. Upload a new file to replace it.') }}</p>
                                @endif
                              </div>
                            @endif
                            <div class="drip-file-wrap mx-auto text-start">
                              <input type="file" id="drip_image" name="drip_image" class="form-control form-control-sm"
                                accept="image/jpeg,image/png,image/gif,image/webp,.jpg,.jpeg,.png,.gif,.webp"
                                data-max-bytes="3145728">
                            </div>
                            <p class="small text-danger mt-2 mb-0 d-none" id="drip-image-error" role="alert"></p>
                          </div>
                        </div>

                        <div class="col-12 col-md-4 d-flex">
                          <div class="clinical-image-tile flex-grow-1 w-100 border rounded p-3 bg-light text-center micro-needling-upload">
                            <label for="micro_needling_image" class="form-label d-block">Micro needling</label>
                            <p class="text-muted font-sm mb-3">Optional. Upload a photo from the micro needling session, device, or related documentation (JPEG, PNG, GIF, or WebP — max 3&nbsp;MB).</p>
                            @if (optional($appointmentNote)->hasMicroNeedlingImagePath())
                              @php
                                $mnPreviewUrl = $appointmentNote?->microNeedlingImageUrl();
                              @endphp
                              <div class="mb-3">
                                @if ($mnPreviewUrl)
                                  <img src="{{ $mnPreviewUrl }}" alt="{{ __('Current micro needling image') }}" class="img-thumbnail mx-auto d-block shadow-sm" style="max-width: 100%; max-height: 220px; width: auto; height: auto; object-fit: contain;" id="micro-needling-preview-img">
                                  <p id="micro-needling-img-fallback" class="small text-warning mt-2 mb-0 d-none" role="status"></p>
                                  <p class="small text-secondary mb-0 mt-2">{{ __('Upload a new file to replace this image.') }}</p>
                                @else
                                  <p class="small text-warning mb-2">{{ __('A saved image path exists, but the file was not found on the server. Upload a new file to replace it.') }}</p>
                                @endif
                              </div>
                            @endif
                            <div class="micro-needling-file-wrap mx-auto text-start">
                              <input type="file" id="micro_needling_image" name="micro_needling_image" class="form-control form-control-sm"
                                accept="image/jpeg,image/png,image/gif,image/webp,.jpg,.jpeg,.png,.gif,.webp"
                                data-max-bytes="3145728">
                            </div>
                            <p class="small text-danger mt-2 mb-0 d-none" id="micro-needling-image-error" role="alert"></p>
                          </div>
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
                          <a href="{{ route('clinical_staff.products') }}" class="btn btn-sm btn-outline-primary flex-shrink-0">
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
                                          <strong>{{ number_format($onHand) }}</strong>
                                          @if ($product->unit)
                                            <span class="prescribe-card-unit">{{ $product->unit }}</span>
                                          @endif
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
                          <a href="{{ route('clinical_staff.appointments') }}" class="btn btn-sm btn-outline">Cancel</a>
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
    #notes-create-doc .notes-create-tab-btn {
      border: 1px solid #e5e7eb;
      background: #fff;
      color: #374151;
      border-radius: 8px;
      padding: 0.45rem 0.85rem;
      font-size: 0.8125rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s, border-color 0.2s, color 0.2s;
    }
    #notes-create-doc .notes-create-tab-btn:hover {
      border-color: #d1b8c8;
      color: #111827;
    }
    #notes-create-doc .notes-create-tab-btn.active {
      background: #c7819d;
      border-color: #c7819d;
      color: #fff;
    }
    #notes-create-doc .notes-create-tab-panel {
      display: none;
    }
    #notes-create-doc .notes-create-tab-panel.active {
      display: block;
    }

    .vital-signs-clinical-images .body-analyzer-upload,
    .vital-signs-clinical-images .bottle-citrus-upload,
    .vital-signs-clinical-images .lemon-bottle-upload,
    .vital-signs-clinical-images .aqualyx-upload,
    .vital-signs-clinical-images .drip-upload,
    .vital-signs-clinical-images .micro-needling-upload {
      max-width: none;
      margin-left: 0;
      margin-right: 0;
    }

    .vital-signs-clinical-images .body-analyzer-file-wrap,
    .vital-signs-clinical-images .bottle-citrus-file-wrap,
    .vital-signs-clinical-images .lemon-bottle-file-wrap,
    .vital-signs-clinical-images .aqualyx-file-wrap,
    .vital-signs-clinical-images .drip-file-wrap,
    .vital-signs-clinical-images .micro-needling-file-wrap {
      max-width: 100%;
    }

    .clinical-image-tile .text-muted.font-sm {
      line-height: 1.35;
    }

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
        (function () {
          var root = document.getElementById('notes-create-doc');
          if (!root) {
            return;
          }
          var btns = root.querySelectorAll('[data-notes-tab]');
          var panels = root.querySelectorAll('[data-notes-panel]');
          var descs = root.querySelectorAll('[data-notes-desc]');
          function show(tab) {
            btns.forEach(function (b) {
              var on = b.getAttribute('data-notes-tab') === tab;
              b.classList.toggle('active', on);
              b.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            panels.forEach(function (p) {
              p.classList.toggle('active', p.getAttribute('data-notes-panel') === tab);
            });
            descs.forEach(function (d) {
              d.classList.toggle('d-none', d.getAttribute('data-notes-desc') !== tab);
            });
          }
          btns.forEach(function (b) {
            b.addEventListener('click', function () {
              show(b.getAttribute('data-notes-tab') || 'clinical');
            });
          });
          var init = root.getAttribute('data-initial-notes-tab') || 'clinical';
          var h = (window.location.hash || '').replace(/^#/, '');
          if (h === 'notes-create-assessment') {
            init = 'assessment';
          }
          show(init);
        })();

        var id = (window.location.hash || '').replace(/^#/, '');
        if (id) {
          var el = document.getElementById(id);
          if (el && typeof el.focus === 'function') {
            el.focus({ preventScroll: false });
          }
        }

        function wireOptionalImageUpload(inputId, errId, previewImgId, previewFallbackId) {
          var fileInput = document.getElementById(inputId);
          var fileErr = document.getElementById(errId);
          var maxBytes = fileInput && fileInput.getAttribute('data-max-bytes')
            ? parseInt(fileInput.getAttribute('data-max-bytes'), 10)
            : 3145728;
          var previewImg = previewImgId ? document.getElementById(previewImgId) : null;
          var previewFallback = previewFallbackId ? document.getElementById(previewFallbackId) : null;
          if (previewImg && previewFallback) {
            previewImg.addEventListener('error', function () {
              previewImg.classList.add('d-none');
              previewFallback.textContent = '{{ __("Image could not be displayed. If uploads never show, run php artisan storage:link on the server, then refresh.") }}';
              previewFallback.classList.remove('d-none');
            });
          }
          if (fileInput && fileErr) {
            fileInput.addEventListener('change', function () {
              fileErr.classList.add('d-none');
              fileErr.textContent = '';
              if (!fileInput.files || !fileInput.files.length) return;
              var f = fileInput.files[0];
              if (f.size > maxBytes) {
                fileErr.textContent = '{{ __("Please choose an image under 3 MB.") }}';
                fileErr.classList.remove('d-none');
                fileInput.value = '';
              }
            });
          }
        }

        wireOptionalImageUpload('body_analyzer_image', 'body-analyzer-image-error', 'body-analyzer-preview-img', 'body-analyzer-img-fallback');
        wireOptionalImageUpload('bottle_citrus_image', 'bottle-citrus-image-error', 'bottle-citrus-preview-img', 'bottle-citrus-img-fallback');
        wireOptionalImageUpload('lemon_bottle_image', 'lemon-bottle-image-error', 'lemon-bottle-preview-img', 'lemon-bottle-img-fallback');
        wireOptionalImageUpload('aqualyx_image', 'aqualyx-image-error', 'aqualyx-preview-img', 'aqualyx-img-fallback');
        wireOptionalImageUpload('drip_image', 'drip-image-error', 'drip-preview-img', 'drip-img-fallback');
        wireOptionalImageUpload('micro_needling_image', 'micro-needling-image-error', 'micro-needling-preview-img', 'micro-needling-img-fallback');
      });
    </script>
  @endpush
@endsection
