<?php

namespace Tests\Feature\Auth;

use App\Models\Admin;
use App\Models\ClinicalStaff;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = Patient::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated('web');
        $response->assertRedirect(route('patient.dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = Patient::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_staff_credentials_cannot_sign_in_through_patient_login(): void
    {
        $admin = Admin::factory()->create([
            'email' => 'staff-only@example.com',
            'password' => 'password',
            'status' => 'approved',
        ]);

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertGuest('admin');
        $this->assertGuest('web');
        $this->assertGuest('clinical_staff');
    }

    public function test_patient_credentials_cannot_sign_in_through_staff_login(): void
    {
        $patient = Patient::factory()->create([
            'email' => 'patient-only@example.com',
            'password' => 'password',
        ]);

        $this->post('/admin/login', [
            'email' => $patient->email,
            'password' => 'password',
        ]);

        $this->assertGuest('admin');
        $this->assertGuest('web');
        $this->assertGuest('clinical_staff');
    }

    public function test_doctor_credentials_cannot_sign_in_through_patient_login(): void
    {
        $doctor = ClinicalStaff::factory()->create([
            'email' => 'doctor-only@example.com',
            'password' => 'password',
            'status' => 'active',
        ]);

        $this->post('/login', [
            'email' => $doctor->email,
            'password' => 'password',
        ]);

        $this->assertGuest('admin');
        $this->assertGuest('web');
        $this->assertGuest('clinical_staff');
    }

    public function test_doctor_can_authenticate_using_staff_login(): void
    {
        $doctor = ClinicalStaff::factory()->create([
            'email' => 'doctor-portal@example.com',
            'password' => 'password',
            'status' => 'active',
        ]);

        $response = $this->post('/admin/login', [
            'email' => $doctor->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated('clinical_staff');
        $this->assertGuest('admin');
        $response->assertRedirect(route('clinical_staff.dashboard', absolute: false));
    }

    public function test_users_can_logout(): void
    {
        $user = Patient::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_users_can_logout_via_get(): void
    {
        $user = Patient::factory()->create();

        $response = $this->actingAs($user)->get('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
