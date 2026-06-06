<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Patient;
use App\Support\PatientLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

function assertTrue(bool $value, string $label): void
{
    if (! $value) {
        fwrite(STDERR, "FAIL: {$label}\n");
        exit(1);
    }
    echo "ok: {$label}\n";
}

$email = 'flowtest_'.time().'@example.com';
$pass = 'AnyPassword123!';

$patient = Patient::create([
    'name' => 'Flow Test',
    'email' => $email,
    'birthdate' => '2000-01-01',
    'gender' => 'male',
    'address' => 'Test',
    'password' => $pass,
    'pending_password_plain' => Crypt::encryptString($pass),
    'status' => 'pending',
]);

echo "email={$email}\n";
assertTrue(Hash::check($pass, (string) $patient->password), 'registration hash');

Auth::guard('web')->logout();
assertTrue(
    PatientLogin::attempt(Auth::guard('web'), ['email' => $email, 'password' => $pass]),
    'login while pending (hash valid)'
);
Auth::guard('web')->logout();

// Approve via pending plain (registrations flow).
$plain = PatientLogin::plainPasswordFromPending($patient->fresh());
$patient->update([
    'status' => 'active',
    'password' => $plain,
    'pending_password_plain' => null,
]);

Auth::guard('web')->logout();
assertTrue(
    PatientLogin::attempt(Auth::guard('web'), ['email' => $email, 'password' => $pass]),
    'login after approval'
);
Auth::guard('web')->logout();

// Activate with no pending — password hash must be preserved (patients status flow).
$email2 = 'flowtest2_'.time().'@example.com';
$pass2 = 'AnotherPass456!';

$patient2 = Patient::create([
    'name' => 'Flow Test 2',
    'email' => $email2,
    'birthdate' => '2000-01-01',
    'gender' => 'female',
    'address' => 'Test',
    'password' => $pass2,
    'pending_password_plain' => null,
    'status' => 'pending',
]);

$hashBefore = (string) $patient2->fresh()->password;
$patient2->update(['status' => 'active']);
$hashAfter = (string) $patient2->fresh()->password;

assertTrue($hashBefore === $hashAfter, 'activation without pending keeps hash');
assertTrue(
    PatientLogin::attempt(Auth::guard('web'), ['email' => $email2, 'password' => $pass2]),
    'login after activation without pending'
);
Auth::guard('web')->logout();

// Auto-repair from pending when hash was overwritten.
$email3 = 'flowtest3_'.time().'@example.com';
$pass3 = 'RepairMe789!';

$patient3 = Patient::create([
    'name' => 'Flow Test 3',
    'email' => $email3,
    'birthdate' => '2000-01-01',
    'gender' => 'male',
    'address' => 'Test',
    'password' => $pass3,
    'pending_password_plain' => Crypt::encryptString($pass3),
    'status' => 'active',
]);

$patient3->forceFill(['password' => 'wrong-hash-value'])->save();
assertTrue(
    PatientLogin::attempt(Auth::guard('web'), ['email' => $email3, 'password' => $pass3]),
    'login auto-repair from pending'
);
Auth::guard('web')->logout();

$patient->delete();
$patient2->delete();
$patient3->delete();

echo "done\n";
