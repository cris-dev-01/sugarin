<?php

declare(strict_types=1);

namespace Tests\Feature\GlucoseDashboard;

use App\Models\GlucoseRange;
use App\Models\User;
use App\Models\UserPatient;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Database\Seeders\StatusesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PatientOwnSummaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionsSeeder::class, RolesSeeder::class, RolesPermissionsSeeder::class, StatusesSeeder::class]);
    }

    public function test_user_without_permission_cannot_view_the_page(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/summary');

        $response->assertForbidden();
    }

    public function test_patient_sees_its_own_summary(): void
    {
        $range = GlucoseRange::factory()->create();
        $patientUser = User::factory()->create();
        $patientUser->assignRole('Patient');
        $patient = UserPatient::factory()->create([
            'user_id' => $patientUser->id,
            'glucose_range_id' => $range->id,
        ]);

        $this->actingAs($patientUser);

        $response = $this->get('/summary');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('PatientDashboard/Index')
            ->where('summary.patient.id', $patient->id)
        );
    }

    public function test_user_without_an_associated_patient_gets_a_null_summary(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Patient');
        $this->actingAs($user);

        $response = $this->get('/summary');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('PatientDashboard/Index')
            ->where('summary', null)
        );
    }
}
