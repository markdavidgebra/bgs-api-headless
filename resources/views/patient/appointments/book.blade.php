@extends('patient.layouts.master')

@section('title', 'Book appointment')

@section('content')
  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Patient <span></span>
          <a href="{{ route('patient.appointments') }}">Appointments</a>
          <span></span> Book
        </div>
      </div>
    </div>

    <div class="page-content pt-70 pb-60">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="row">
              @include('patient.layouts.sidebar')
              <div class="col-12 col-md-9">
                <div class="account dashboard-content pl-50">
                  <div class="card mb-4">
                    <div class="card-header p-0 pb-10">
                      <h3 class="mb-0">Book appointment</h3>
                    </div>
                    <div class="card-body p-0">
                      <p class="mb-0 text-muted font-sm">
                        Choose service/treatment, doctor, preferred date/time, then confirm booking.
                      </p>
                      <p class="mb-0 text-muted font-sm mt-1">
                        After you confirm booking, you will receive a booking confirmation email.
                      </p>
                    </div>
                  </div>

                  <div class="card">
                    <div class="card-body">
                      <form method="POST" action="{{ route('patient.appointments.store') }}">
                        @csrf

                        @php
                          $hasDateTime = old('appointment_date') && old('appointment_time');
                          $hasService = old('service_id');
                        @endphp
                        <div class="row">
                          <div class="col-md-6 mb-15" id="service-field-wrap" @if (! $hasDateTime) style="display:none;" @endif>
                            <label class="font-sm mb-5" for="service_id">Select service / treatment</label>
                            <select id="service_id" name="service_id" class="form-control @error('service_id') is-invalid @enderror" required>
                              <option value="">Choose a service</option>
                              @foreach ($services as $service)
                                <option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>
                                  {{ $service->name }}
                                  @if ($service->duration_minutes)
                                    ({{ $service->duration_minutes }} mins)
                                  @endif
                                </option>
                              @endforeach
                            </select>
                            @error('service_id')
                              <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                          </div>

                          <div class="col-md-6 mb-15" id="doctor-field-wrap" @if (! $hasDateTime || ! $hasService) style="display:none;" @endif>
                            <label class="font-sm mb-5" for="clinical_staff_id">Select doctor</label>
                            <select id="clinical_staff_id" name="clinical_staff_id" class="form-control @error('clinical_staff_id') is-invalid @enderror" required>
                              <option value="">Choose a doctor</option>
                              @foreach ($clinicalStaff as $doctor)
                                <option value="{{ $doctor->id }}" @selected(old('clinical_staff_id') == $doctor->id)>
                                  {{ $doctor->name }}@if ($doctor->specialty) — {{ $doctor->specialty }} @endif
                                </option>
                              @endforeach
                            </select>
                            @error('clinical_staff_id')
                              <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                          </div>

                          <div class="col-md-6 mb-15">
                            <label class="font-sm mb-5" for="appointment_date">Select date</label>
                            <input
                              id="appointment_date"
                              type="date"
                              name="appointment_date"
                              value="{{ old('appointment_date') }}"
                              min="{{ now()->toDateString() }}"
                              class="form-control @error('appointment_date') is-invalid @enderror"
                              required
                            />
                            <p class="text-muted font-sm mt-5 mb-0">Sundays are not available for booking.</p>
                            @error('appointment_date')
                              <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                          </div>

                          <div class="col-md-6 mb-15">
                            <label class="font-sm mb-5" for="appointment_time">Select time</label>
                            <input
                              id="appointment_time"
                              type="time"
                              name="appointment_time"
                              value="{{ old('appointment_time') }}"
                              class="form-control @error('appointment_time') is-invalid @enderror"
                              required
                            />
                            @error('appointment_time')
                              <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                          </div>

                          <div class="col-12 mb-20">
                            <label class="font-sm mb-5" for="patient_concern">Notes or concern (optional)</label>
                            <textarea
                              id="patient_concern"
                              name="patient_concern"
                              rows="4"
                              class="form-control @error('patient_concern') is-invalid @enderror"
                              placeholder="Describe your concern or anything the doctor should know."
                            >{{ old('patient_concern') }}</textarea>
                            @error('patient_concern')
                              <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                          </div>
                        </div>

                        <div class="d-flex flex-wrap mt-10">
                          <button type="submit" class="btn btn-sm mr-10 mb-10">
                            <i class="fi-rs-check mr-5"></i>Confirm booking
                          </button>
                          <a href="{{ route('patient.appointments') }}" class="btn btn-sm btn-outline-primary mb-10">
                            Cancel
                          </a>
                        </div>
                      </form>
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  @include('partials.block-sunday-date-input')
  <script>
    (function () {
      var dateInput = document.getElementById('appointment_date');
      if (window.blockSundayDateInput) window.blockSundayDateInput(dateInput);
      var serviceWrap = document.getElementById('service-field-wrap');
      var doctorWrap = document.getElementById('doctor-field-wrap');
      var serviceSelect = document.getElementById('service_id');
      var doctorSelect = document.getElementById('clinical_staff_id');
      var timeInput = document.getElementById('appointment_time');
      var doctorsUrl = @json(route('patient.appointments.book.doctors'));
      if (!dateInput || !timeInput || !serviceWrap || !doctorWrap || !serviceSelect || !doctorSelect) return;

      function fillDoctors(doctors, selectedId) {
        var opt0 = document.createElement('option');
        opt0.value = '';
        opt0.textContent = 'Choose a doctor';
        doctorSelect.innerHTML = '';
        doctorSelect.appendChild(opt0);
        doctors.forEach(function (d) {
          var opt = document.createElement('option');
          opt.value = d.id;
          opt.textContent = d.name + (d.specialty ? ' — ' + d.specialty : '');
          if (selectedId != null && String(d.id) === String(selectedId)) opt.selected = true;
          doctorSelect.appendChild(opt);
        });
        if (!Array.from(doctorSelect.options).some(function (o) { return o.selected && o.value; })) {
          doctorSelect.selectedIndex = 0;
        }
      }

      function updateFieldVisibility() {
        var hasDate = Boolean(dateInput.value);
        var hasTime = Boolean(timeInput.value);
        var hasDateTime = hasDate && hasTime;
        serviceWrap.style.display = hasDateTime ? '' : 'none';
        if (!hasDateTime) {
          serviceSelect.value = '';
          doctorWrap.style.display = 'none';
          fillDoctors([], null);
          return;
        }

        doctorWrap.style.display = serviceSelect.value ? '' : 'none';
        if (!serviceSelect.value) {
          fillDoctors([], null);
        }
      }

      function refreshDoctors() {
        var date = dateInput.value;
        var serviceId = serviceSelect.value;
        var time = timeInput.value;
        if (!date || !time || !serviceId) return;
        var keepId = doctorSelect.value;
        fetch(doctorsUrl + '?date=' + encodeURIComponent(date) + '&service_id=' + encodeURIComponent(serviceId), {
          headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
          .then(function (r) { return r.json(); })
          .then(function (data) { fillDoctors(data.clinical_staff || [], keepId); })
          .catch(function () {});
      }

      dateInput.addEventListener('change', function () {
        updateFieldVisibility();
        refreshDoctors();
      });

      timeInput.addEventListener('change', function () {
        updateFieldVisibility();
        refreshDoctors();
      });

      serviceSelect.addEventListener('change', function () {
        updateFieldVisibility();
        refreshDoctors();
      });

      updateFieldVisibility();
      refreshDoctors();
    })();
  </script>
@endsection