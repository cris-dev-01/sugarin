<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Notifications\ProfileUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_the_authenticated_users_notifications_paginated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        for ($i = 0; $i < 12; $i++) {
            $user->notify(new ProfileUpdatedNotification());
        }

        $response = $this->getJson('/notifications');

        $response->assertOk();
        $response->assertJsonCount(10, 'data');
        $response->assertJsonPath('meta.total', 12);
        $response->assertJsonPath('meta.per_page', 10);
        $response->assertJsonPath('data.0.type', 'profile-changed');
        $response->assertJsonStructure([
            'data' => [['id', 'type', 'data' => ['title', 'message', 'url'], 'read_at', 'created_at']],
            'meta',
        ]);
    }

    public function test_it_does_not_list_another_users_notifications(): void
    {
        $owner = User::factory()->create();
        $owner->notify(new ProfileUpdatedNotification());

        $other = User::factory()->create();
        $this->actingAs($other);

        $response = $this->getJson('/notifications');

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_it_marks_a_notification_as_read(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $user->notify(new ProfileUpdatedNotification());

        $notification = $user->notifications()->firstOrFail();

        $response = $this->patch("/notifications/{$notification->id}/read");

        $response->assertRedirect();
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_it_returns_404_when_marking_another_users_notification_as_read(): void
    {
        $owner = User::factory()->create();
        $owner->notify(new ProfileUpdatedNotification());
        $notification = $owner->notifications()->firstOrFail();

        $other = User::factory()->create();
        $this->actingAs($other);

        $response = $this->patch("/notifications/{$notification->id}/read");

        $response->assertNotFound();
    }

    public function test_it_marks_all_notifications_as_read(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $user->notify(new ProfileUpdatedNotification());
        $user->notify(new ProfileUpdatedNotification());

        $response = $this->patch('/notifications/read-all');

        $response->assertRedirect();
        $this->assertSame(0, $user->unreadNotifications()->count());
    }
}
