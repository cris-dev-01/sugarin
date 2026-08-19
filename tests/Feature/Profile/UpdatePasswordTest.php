<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Models\User;
use App\Notifications\PasswordUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UpdatePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_notifies_the_user_when_password_is_updated_successfully(): void
    {
        Notification::fake();

        $user = User::factory()->create(['password' => Hash::make('old-password')]);
        $this->actingAs($user);

        $response = $this->put('/profile/password', [
            'current_password' => 'old-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertRedirect(route('profile.index'));

        Notification::assertSentTo($user, PasswordUpdatedNotification::class, function ($notification, $channels) {
            return $channels === ['database'];
        });
    }

    public function test_it_does_not_notify_when_current_password_is_incorrect(): void
    {
        Notification::fake();

        $user = User::factory()->create(['password' => Hash::make('old-password')]);
        $this->actingAs($user);

        $response = $this->put('/profile/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertSessionHasErrors('current_password');
        Notification::assertNothingSent();
    }
}
