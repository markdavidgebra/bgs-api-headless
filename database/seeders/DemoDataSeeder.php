<?php

namespace Database\Seeders;

use App\Models\About;
use App\Models\Appointment;
use App\Models\AppointmentNote;
use App\Models\AppointmentPayment;
use App\Models\AppointmentTimeline;
use App\Models\Blog;
use App\Models\Doctor;
use App\Models\DoctorBlockedDate;
use App\Models\DoctorNotification;
use App\Models\DoctorWeeklySchedule;
use App\Models\Faq;
use App\Models\MembershipPlan;
use App\Models\Patient;
use App\Models\PatientSubscription;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\Slide;
use App\Models\StockMovement;
use App\Models\Testimonial;
use App\Models\TreatmentPackage;
use App\Models\TreatmentPackageUsageHistory;
use App\Models\TreatmentPatientPackage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * Fills the database with fake demo rows using model factories.
 *
 * Run: php artisan db:seed --class=DemoDataSeeder
 * Full reset: php artisan migrate:fresh --seed
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $services = Service::factory()->count(8)->create();
        $doctors = Doctor::factory()->count(4)->create();
        $patients = Patient::factory()->count(15)->create();

        About::factory()->count(2)->create();
        Faq::factory()->count(5)->create();
        Testimonial::factory()->count(4)->create();
        Blog::factory()->count(3)->published()->create();
        Slide::factory()->count(4)->create();

        $membershipPlans = MembershipPlan::factory()->count(3)->create();
        $this->attachMembershipPlanServices($membershipPlans, $services);

        $treatmentPackages = TreatmentPackage::factory()->count(4)->create();
        $this->attachTreatmentPackageRelations($treatmentPackages, $services, $doctors);

        $categories = ProductCategory::factory()->count(4)->create();
        $products = Collection::times(12, fn () => Product::factory()->create([
            'category_id' => $categories->random()->id,
        ]));

        foreach ($products->take(8) as $product) {
            StockMovement::factory()->count(fake()->numberBetween(1, 3))->create([
                'product_id' => $product->id,
            ]);
        }

        $promotions = Promotion::factory()->count(3)->create();
        $this->attachPromotionRelations($promotions, $services, $treatmentPackages, $membershipPlans, $products);

        $appointments = Collection::times(30, fn () => Appointment::factory()->create([
            'patient_id' => $patients->random()->id,
            'doctor_id' => $doctors->random()->id,
            'service_id' => $services->random()->id,
            'appointment_date' => fake()->dateTimeBetween('+1 day', '+4 months')->format('Y-m-d'),
        ]));

        foreach ($appointments->random(min(12, $appointments->count())) as $appointment) {
            AppointmentNote::factory()->create(['appointment_id' => $appointment->id]);
            AppointmentPayment::factory()->create(['appointment_id' => $appointment->id]);
            AppointmentTimeline::factory()->count(2)->create(['appointment_id' => $appointment->id]);
        }

        foreach (range(1, 8) as $_) {
            PatientSubscription::factory()->create([
                'patient_id' => $patients->random()->id,
                'membership_plan_id' => $membershipPlans->random()->id,
            ]);
        }

        foreach (range(1, 6) as $_) {
            TreatmentPatientPackage::factory()->create([
                'patient_id' => $patients->random()->id,
                'treatment_package_id' => $treatmentPackages->random()->id,
            ]);
        }

        TreatmentPackageUsageHistory::factory()->count(5)->create();

        foreach ($appointments->take(10) as $appointment) {
            Payment::factory()->forAppointment($appointment)->create();
        }

        Payment::factory()->count(4)->create();

        foreach (range(1, 8) as $_) {
            DoctorNotification::factory()->create([
                'doctor_id' => $doctors->random()->id,
            ]);
        }

        foreach ($doctors as $doctor) {
            for ($weekday = 1; $weekday <= 7; $weekday++) {
                DoctorWeeklySchedule::factory()->create([
                    'doctor_id' => $doctor->id,
                    'weekday' => $weekday,
                    'is_active' => $weekday <= 5,
                    'start_time' => $weekday <= 5 ? '09:00:00' : null,
                    'end_time' => $weekday <= 5 ? '17:00:00' : null,
                ]);
            }
        }

        foreach (range(1, 6) as $dayOffset) {
            DoctorBlockedDate::factory()->create([
                'doctor_id' => $doctors->random()->id,
                'blocked_date' => now()->addDays($dayOffset * 4)->format('Y-m-d'),
            ]);
        }
    }

    /**
     * @param  Collection<int, MembershipPlan>  $membershipPlans
     * @param  Collection<int, Service>  $services
     */
    private function attachMembershipPlanServices(Collection $membershipPlans, Collection $services): void
    {
        $take = min(4, $services->count());
        if ($take === 0) {
            return;
        }

        foreach ($membershipPlans as $plan) {
            $sync = [];
            foreach ($services->random($take) as $service) {
                $sync[$service->id] = ['sessions' => fake()->numberBetween(1, 6)];
            }
            $plan->services()->sync($sync);
        }
    }

    /**
     * @param  Collection<int, TreatmentPackage>  $treatmentPackages
     * @param  Collection<int, Service>  $services
     * @param  Collection<int, Doctor>  $doctors
     */
    private function attachTreatmentPackageRelations(
        Collection $treatmentPackages,
        Collection $services,
        Collection $doctors
    ): void {
        $serviceTake = min(3, $services->count());
        $doctorTake = min(2, $doctors->count());
        if ($serviceTake === 0) {
            return;
        }

        foreach ($treatmentPackages as $package) {
            $sync = [];
            foreach ($services->random($serviceTake) as $service) {
                $sync[$service->id] = ['sessions' => fake()->numberBetween(1, 4)];
            }
            $package->services()->sync($sync);

            if ($doctors->isNotEmpty()) {
                $n = max(1, min($doctorTake, $doctors->count()));
                $package->doctors()->sync(
                    $doctors->random($n)->pluck('id')->all()
                );
            }
        }
    }

    /**
     * @param  Collection<int, Promotion>  $promotions
     * @param  Collection<int, Service>  $services
     * @param  Collection<int, TreatmentPackage>  $treatmentPackages
     * @param  Collection<int, MembershipPlan>  $membershipPlans
     * @param  Collection<int, Product>  $products
     */
    private function attachPromotionRelations(
        Collection $promotions,
        Collection $services,
        Collection $treatmentPackages,
        Collection $membershipPlans,
        Collection $products
    ): void {
        foreach ($promotions as $promotion) {
            if ($services->isNotEmpty()) {
                $promotion->services()->sync(
                    $services->random(min(3, $services->count()))->pluck('id')->all()
                );
            }
            if ($treatmentPackages->isNotEmpty()) {
                $promotion->treatmentPackages()->sync(
                    $treatmentPackages->random(min(2, $treatmentPackages->count()))->pluck('id')->all()
                );
            }
            if ($membershipPlans->isNotEmpty()) {
                $promotion->membershipPlans()->sync(
                    $membershipPlans->random(min(2, $membershipPlans->count()))->pluck('id')->all()
                );
            }
            if ($products->isNotEmpty()) {
                $promotion->products()->sync(
                    $products->random(min(4, $products->count()))->pluck('id')->all()
                );
            }
        }
    }
}
