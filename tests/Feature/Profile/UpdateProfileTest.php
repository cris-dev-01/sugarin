<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Models\User;
use App\Notifications\ProfileUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UpdateProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_notifies_the_user_when_profile_is_updated_successfully(): void
    {
        Notification::fake();

        $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);
        $this->actingAs($user);

        $response = $this->put('/profile', [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        $response->assertRedirect(route('profile.index'));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name', 'email' => 'new@example.com']);

        Notification::assertSentTo($user, ProfileUpdatedNotification::class, function ($notification, $channels) {
            return $channels === ['database'];
        });
    }

    public function test_it_does_not_notify_on_validation_failure(): void
    {
        Notification::fake();

        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create(['email' => 'user@example.com']);
        $this->actingAs($user);

        $response = $this->put('/profile', [
            'name' => 'New Name',
            'email' => 'taken@example.com',
        ]);

        $response->assertSessionHasErrors('email');
        Notification::assertNothingSent();
    }
}
