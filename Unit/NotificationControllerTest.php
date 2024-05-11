<?php

namespace Tests\Feature;

use App\User;
use App\Notifications\StatusNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_shows_the_notification_index_page()
    {
        $response = $this->get('/notifications');

        $response->assertViewIs('backend.notification.index');
    }

    /** @test */
    public function it_deletes_the_notification()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user);
    
        $notification = new StatusNotification([
            'title' => 'Test Notification',
            'actionURL' => '/some/action',
            'fas' => 'fa-bell',
        ]);
    
        Notification::send($user, $notification);
    
        $notification = $user->notifications()->first();
    
        $response = $this->delete("/notification/{$notification->id}");
    }

    /** @test */
    public function it_returns_error_when_trying_to_delete_non_existing_notification()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user);

        $response = $this->delete("/notification/999");
    }
}
