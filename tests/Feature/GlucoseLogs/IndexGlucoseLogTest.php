<?php

declare(strict_types=1);

namespace Tests\Feature\GlucoseLogs;

use App\Models\GlucoseRange;
use App\Models\User;
use App\Models\UserPatient;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class IndexGlucoseLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionsSeeder::class, RolesSeeder::class, RolesPermissionsSeeder::class]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/glucose-logs');

        $response->assertRedirect(route('login'));
    }

    public function test_user_without_permission_cannot_view_the_page(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/glucose-logs');

        $response->assertForbidden();
    }

    public function test_it_renders_the_index_page_with_available_patients(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrator');
        $this->actingAs($admin);

        $range = GlucoseRange::factory()->create();
        $patientUser = User::factory()->create(['name' => 'Paciente de Prueba']);
        $patientUser->assignRole('Patient');
        UserPatient::factory()->create([
            'user_id' => $patientUser->id,
            'glucose_range_id' => $range->id,
        ]);

        $response = $this->get('/glucose-logs');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('GlucoseLogs/Index')
            ->has('patients', 1)
            ->where('patients.0.name', 'Paciente de Prueba')
        );
    }
}
