{{-- All patient edit offcanvases - each Edit button targets a different one --}}
<x-admin.off-canvas id="offcanvas-edit-patient-details" title="Edit Patient Details">
    @include('components.admin.off-canvas.patient_details')
</x-admin.off-canvas>

<x-admin.off-canvas id="offcanvas-edit-address" title="Edit Address">
    @include('components.admin.off-canvas.address')
</x-admin.off-canvas>

<x-admin.off-canvas id="offcanvas-edit-medical-history" title="Edit Medical History">
    @include('components.admin.off-canvas.medical_history')
</x-admin.off-canvas>

<x-admin.off-canvas id="offcanvas-edit-contact-details" title="Edit Contact Details">
    @include('components.admin.off-canvas.contact_details')
</x-admin.off-canvas>

<x-admin.off-canvas id="offcanvas-edit-emergency-contact" title="Edit Emergency Contact">
    @include('components.admin.off-canvas.emergency_contact')
</x-admin.off-canvas>

<x-admin.off-canvas id="offcanvas-edit-patient-history-summary" title="Edit Patient History Summary">
    @include('components.admin.off-canvas.patient_history_summary')
</x-admin.off-canvas>

<x-admin.off-canvas id="offcanvas-edit-skin-type" title="Edit Skin Type">
    @include('components.admin.off-canvas.skin_type')
</x-admin.off-canvas>

<x-admin.off-canvas id="offcanvas-edit-skin-concerns" title="Edit Skin Concerns">
    @include('components.admin.off-canvas.skin_concerns')
</x-admin.off-canvas>

<x-admin.off-canvas id="offcanvas-edit-plan-overview" title="Edit Plan Overview">
    @include('components.admin.off-canvas.plan_overview')
</x-admin.off-canvas>

<x-admin.off-canvas id="offcanvas-edit-schedule-sessions" title="Edit Schedule & Sessions">
    @include('components.admin.off-canvas.schedule_sessions')
</x-admin.off-canvas>

<x-admin.off-canvas id="offcanvas-edit-benefits" title="Edit Benefits">
    @include('components.admin.off-canvas.benefits')
</x-admin.off-canvas>
