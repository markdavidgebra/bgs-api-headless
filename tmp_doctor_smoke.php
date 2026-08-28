<?php

/**
 * Throwaway verification script — DELETE AFTER USE.
 *
 * Boots Laravel, dumps the doctor-portal schema, then drives every /api/doctor/*
 * endpoint through the real HTTP kernel (middleware, session, guards included),
 * carrying cookies forward between requests like a browser would.
 */

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

/** @var array<string, string> */
$cookieJar = [];

function jsonLine(string $label, mixed $value): void
{
    echo $label.': '.json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL;
}

function hit(string $method, string $uri, array $payload = [], array $query = []): array
{
    global $kernel, $cookieJar;

    $request = Request::create(
        $uri,
        $method,
        $method === 'GET' ? $query : $payload,
        $cookieJar,
        [],
        ['HTTP_ACCEPT' => 'application/json', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'],
        $method === 'GET' ? null : json_encode($payload)
    );

    if ($method !== 'GET') {
        $request->headers->set('Content-Type', 'application/json');
    }

    if ($query !== [] && $method === 'GET') {
        $request->server->set('QUERY_STRING', http_build_query($query));
    }

    $response = $kernel->handle($request);

    foreach ($response->headers->getCookies() as $cookie) {
        if ($cookie->getValue() === null || $cookie->getValue() === '') {
            unset($cookieJar[$cookie->getName()]);

            continue;
        }
        $cookieJar[$cookie->getName()] = $cookie->getValue();
    }

    $body = json_decode($response->getContent(), true);

    return ['status' => $response->getStatusCode(), 'body' => $body ?? $response->getContent()];
}

echo str_repeat('=', 70).PHP_EOL;
echo 'SCHEMA'.PHP_EOL;
echo str_repeat('=', 70).PHP_EOL;

foreach (['doctors', 'medications', 'prescriptions', 'prescription_items', 'doctor_notes'] as $table) {
    $exists = Illuminate\Support\Facades\Schema::hasTable($table);
    echo PHP_EOL.'-- '.$table.' (exists: '.($exists ? 'yes' : 'NO').')'.PHP_EOL;
    if (! $exists) {
        continue;
    }
    foreach (Illuminate\Support\Facades\DB::select('SHOW COLUMNS FROM `'.$table.'`') as $col) {
        echo sprintf(
            "   %-24s %-22s null=%-3s default=%s\n",
            $col->Field,
            $col->Type,
            $col->Null,
            $col->Default === null ? 'NULL' : $col->Default
        );
    }
    foreach (Illuminate\Support\Facades\DB::select('SHOW INDEX FROM `'.$table.'`') as $idx) {
        echo '   [index] '.$idx->Key_name.' -> '.$idx->Column_name.' (unique: '.($idx->Non_unique ? 'no' : 'yes').')'.PHP_EOL;
    }
}

echo PHP_EOL.str_repeat('=', 70).PHP_EOL;
echo 'SMOKE TEST'.PHP_EOL;
echo str_repeat('=', 70).PHP_EOL;

// 1. Login
$r = hit('POST', '/api/doctor/login', ['email' => 'doctor@bioglow.test', 'password' => 'password']);
echo PHP_EOL.'1) POST /api/doctor/login -> '.$r['status'].PHP_EOL;
jsonLine('   body', $r['body']);

if ($r['status'] !== 200) {
    echo 'LOGIN FAILED — aborting.'.PHP_EOL;
    exit(1);
}

// 2. me
$r = hit('GET', '/api/doctor/me');
echo PHP_EOL.'2) GET /api/doctor/me -> '.$r['status'].PHP_EOL;
jsonLine('   doctor', $r['body']['doctor'] ?? null);

// 3. dashboard
$r = hit('GET', '/api/doctor/dashboard');
echo PHP_EOL.'3) GET /api/doctor/dashboard -> '.$r['status'].PHP_EOL;
jsonLine('   stats', $r['body']['stats'] ?? null);
jsonLine('   recent_notes_count', is_array($r['body']['recent_notes'] ?? null) ? count($r['body']['recent_notes']) : null);

// 4. patients list
$r = hit('GET', '/api/doctor/patients', [], ['limit' => 3]);
echo PHP_EOL.'4) GET /api/doctor/patients?limit=3 -> '.$r['status'].PHP_EOL;
jsonLine('   meta', $r['body']['meta'] ?? null);
jsonLine('   first_record', $r['body']['records'][0] ?? null);

$patientId = $r['body']['records'][0]['patient']['id'] ?? null;
if ($patientId === null) {
    echo 'No patients in DB — aborting.'.PHP_EOL;
    exit(1);
}

// 4b. search
$r = hit('GET', '/api/doctor/patients', [], ['search' => 'a', 'limit' => 2]);
echo PHP_EOL.'4b) GET /api/doctor/patients?search=a&limit=2 -> '.$r['status'].' total='.($r['body']['meta']['total'] ?? '?').PHP_EOL;

// 5. patient record
$r = hit('GET', '/api/doctor/patients/'.$patientId);
echo PHP_EOL.'5) GET /api/doctor/patients/'.$patientId.' -> '.$r['status'].PHP_EOL;
echo '   top-level keys: '.implode(', ', array_keys($r['body'])).PHP_EOL;
jsonLine('   patient', $r['body']['patient'] ?? null);
jsonLine('   stats', $r['body']['stats'] ?? null);
jsonLine('   appointments.past[0]', $r['body']['appointments']['past'][0] ?? null);
jsonLine('   clinical_notes[0].note (truncated keys)', array_slice($r['body']['clinical_notes'][0]['note'] ?? [], 0, 12, true));
jsonLine('   vital_signs_timeline[0]', $r['body']['vital_signs_timeline'][0] ?? null);
jsonLine('   vital_signs_timeline_count', count($r['body']['vital_signs_timeline'] ?? []));

$appointmentId = $r['body']['appointments']['past'][0]['id'] ?? ($r['body']['appointments']['upcoming'][0]['id'] ?? null);

// 6. vitals only
$r = hit('GET', '/api/doctor/patients/'.$patientId.'/vitals');
echo PHP_EOL.'6) GET /api/doctor/patients/'.$patientId.'/vitals -> '.$r['status'].PHP_EOL;
echo '   keys: '.implode(', ', array_keys($r['body'])).PHP_EOL;
jsonLine('   timeline_count', count($r['body']['vital_signs_timeline'] ?? []));
jsonLine('   field_keys', $r['body']['field_keys'] ?? null);

// 7. create doctor note
$r = hit('POST', '/api/doctor/patients/'.$patientId.'/notes', [
    'note' => 'Reviewed vitals and clinical staff entries. Patient stable, no adverse reaction noted.',
    'diagnosis' => 'Mild post-procedure erythema',
    'plan' => 'Cold compress, review in 1 week. Antihistamine PRN.',
    'appointment_id' => $appointmentId,
]);
echo PHP_EOL.'7) POST /api/doctor/patients/'.$patientId.'/notes -> '.$r['status'].PHP_EOL;
jsonLine('   note', $r['body']['note'] ?? $r['body']);
$noteId = $r['body']['note']['id'] ?? null;

// 8. notes index
$r = hit('GET', '/api/doctor/notes', [], ['patient_id' => $patientId]);
echo PHP_EOL.'8) GET /api/doctor/notes?patient_id='.$patientId.' -> '.$r['status'].PHP_EOL;
jsonLine('   meta', $r['body']['meta'] ?? null);
jsonLine('   notes_count', count($r['body']['notes'] ?? []));

// 9. patch note
if ($noteId) {
    $r = hit('PATCH', '/api/doctor/notes/'.$noteId, [
        'note' => 'EDITED: reviewed vitals; patient stable.',
        'plan' => 'Review in 2 weeks.',
    ]);
    echo PHP_EOL.'9) PATCH /api/doctor/notes/'.$noteId.' -> '.$r['status'].PHP_EOL;
    jsonLine('   note', $r['body']['note'] ?? $r['body']);
}

// 10. medications
$r = hit('GET', '/api/doctor/medications', [], ['search' => 'cetiri']);
echo PHP_EOL.'10) GET /api/doctor/medications?search=cetiri -> '.$r['status'].PHP_EOL;
jsonLine('   medications', $r['body']['medications'] ?? null);

$r = hit('GET', '/api/doctor/medications');
$meds = $r['body']['medications'] ?? [];
echo '10b) GET /api/doctor/medications -> '.$r['status'].' count='.count($meds).PHP_EOL;
jsonLine('   sample', $meds[0] ?? null);

$medA = $meds[0]['id'] ?? null;
$medB = $meds[1]['id'] ?? null;

// 11. create prescription with two items
$r = hit('POST', '/api/doctor/prescriptions', [
    'patient_id' => $patientId,
    'appointment_id' => $appointmentId,
    'diagnosis' => 'Post-procedure inflammation',
    'notes' => 'Take after meals. Return if swelling worsens.',
    'items' => [
        [
            'medication_id' => $medA,
            'dosage' => '1 tablet',
            'frequency' => '3x a day',
            'duration' => '7 days',
            'quantity' => 21,
            'instructions' => 'Take 1 tablet 3x a day after meals for 7 days.',
        ],
        [
            'medication_name' => 'Custom compounded cream',
            'strength' => '0.05%',
            'form' => 'cream',
            'route' => 'topical',
            'dosage' => 'thin layer',
            'frequency' => '2x a day',
            'duration' => '14 days',
            'quantity' => 1,
            'instructions' => 'Apply thin layer to affected area twice daily.',
        ],
    ],
]);
echo PHP_EOL.'11) POST /api/doctor/prescriptions -> '.$r['status'].PHP_EOL;
jsonLine('   prescription', $r['body']['prescription'] ?? $r['body']);
$rxId = $r['body']['prescription']['id'] ?? null;

// 12. read it back
if ($rxId) {
    $r = hit('GET', '/api/doctor/prescriptions/'.$rxId);
    echo PHP_EOL.'12) GET /api/doctor/prescriptions/'.$rxId.' -> '.$r['status'].PHP_EOL;
    jsonLine('   prescription', $r['body']['prescription'] ?? $r['body']);
    jsonLine('   is_mine', $r['body']['is_mine'] ?? null);
}

// 13. prescriptions index
$r = hit('GET', '/api/doctor/prescriptions', [], ['status' => 'active']);
echo PHP_EOL.'13) GET /api/doctor/prescriptions?status=active -> '.$r['status'].PHP_EOL;
jsonLine('   meta', $r['body']['meta'] ?? null);
jsonLine('   filters', $r['body']['filters'] ?? null);
jsonLine('   status_options', $r['body']['status_options'] ?? null);

// 14. patch prescription (replace items)
if ($rxId) {
    $r = hit('PATCH', '/api/doctor/prescriptions/'.$rxId, [
        'diagnosis' => 'Post-procedure inflammation (revised)',
        'items' => [[
            'medication_id' => $medB,
            'dosage' => '1 capsule',
            'frequency' => '2x a day',
            'duration' => '5 days',
            'quantity' => 10,
            'instructions' => 'Take 1 capsule twice a day for 5 days.',
        ]],
    ]);
    echo PHP_EOL.'14) PATCH /api/doctor/prescriptions/'.$rxId.' -> '.$r['status'].PHP_EOL;
    jsonLine('   prescription', $r['body']['prescription'] ?? $r['body']);
}

// 15. cancel
if ($rxId) {
    $r = hit('POST', '/api/doctor/prescriptions/'.$rxId.'/cancel');
    echo PHP_EOL.'15) POST /api/doctor/prescriptions/'.$rxId.'/cancel -> '.$r['status'].PHP_EOL;
    jsonLine('   status', $r['body']['prescription']['status'] ?? $r['body']);
}

// 16. profile
$r = hit('GET', '/api/doctor/profile');
echo PHP_EOL.'16) GET /api/doctor/profile -> '.$r['status'].PHP_EOL;
jsonLine('   doctor', $r['body']['doctor'] ?? null);

// 17. profile update via POST + _method=PATCH
$r = hit('POST', '/api/doctor/profile', [
    '_method' => 'PATCH',
    'name' => 'Dr. Ana Reyes',
    'email' => 'doctor@bioglow.test',
    'phone' => '+63 917 111 2222',
    'specialty' => 'Aesthetic Medicine',
    'license_no' => 'PRC-1234567',
    'bio' => 'Updated via smoke test.',
]);
echo PHP_EOL.'17) POST /api/doctor/profile (_method=PATCH) -> '.$r['status'].PHP_EOL;
jsonLine('   doctor.phone', $r['body']['doctor']['phone'] ?? $r['body']);

// 18. password change (then change back)
$r = hit('PUT', '/api/doctor/profile/password', [
    'current_password' => 'password',
    'password' => 'NewSecret12345',
    'password_confirmation' => 'NewSecret12345',
]);
echo PHP_EOL.'18) PUT /api/doctor/profile/password -> '.$r['status'].PHP_EOL;
jsonLine('   body', $r['body']);

$r = hit('PUT', '/api/doctor/profile/password', [
    'current_password' => 'NewSecret12345',
    'password' => 'password',
    'password_confirmation' => 'password',
]);
echo '18b) revert password -> '.$r['status'].PHP_EOL;

// 19. negative: wrong-guard credentials
$r2 = hit('POST', '/api/doctor/login', ['email' => 'inventory@gmail.com', 'password' => '12345678']);
echo PHP_EOL.'19) POST /api/doctor/login with admin creds -> '.$r2['status'].PHP_EOL;
jsonLine('   body', $r2['body']);

// 20. logout, then confirm protected route is rejected
$r = hit('POST', '/api/doctor/logout');
echo PHP_EOL.'20) POST /api/doctor/logout -> '.$r['status'].PHP_EOL;
jsonLine('   body', $r['body']);

$r = hit('GET', '/api/doctor/dashboard');
echo '20b) GET /api/doctor/dashboard after logout -> '.$r['status'].PHP_EOL;

// 21. named web route resolves
echo PHP_EOL.'21) route(doctor.dashboard) = '.route('doctor.dashboard').PHP_EOL;

echo PHP_EOL.'DONE'.PHP_EOL;
