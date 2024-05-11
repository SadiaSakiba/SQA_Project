<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Dusk\Browser;
use App\User;
use Tests\TestCase;
use App\Models\Post;
use App\Models\Order;
use App\Models\Shipping;
use App\Models\PostComment;
use App\Models\Notification;

class UserDashboardControllerTest extends TestCase
{

    public function testOrderDetailsAndShippingInfoDisplayed()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $order = Order::factory()->create(['user_id' => $user->id]);
        $shipping = Shipping::factory()->create(['order_id' => $order->id]);
        $response = $this->get(route('user.orders'));
        $response->assertSee($order->order_number);
        $response->assertSee($order->sub_total); 
    
        $response->assertSee(route('user.order.view', ['id' => $order->id]));
        $response->assertDontSee('order number of another user'); 
    }

    public function testUserCanEditComments()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $comment = PostComment::factory()->create(['user_id' => $user->id]);
        $response = $this->get(route('user.comments'));
        $response->assertSee($comment->content);

        $response = $this->get(route('comment.edit', ['id' => $comment->id]));

        $response->assertSee($comment->content);
        $updatedContent = 'Updated comment content';
        $response = $this->post(route('comment.update', ['id' => $comment->id]), ['content' => $updatedContent]);
        $this->assertDatabaseHas('post_comments', ['id' => $comment->id, 'content' => $updatedContent]);
    }

    public function testUserCanGeneratePdfOrderReceipt()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                    ->visit(route('user.orders'));

            $browser->clickLink('View');

            $browser->click('#generate-pdf-button')
                    ->assertPathIs('/generate-pdf'); 
        });
    }


    public function testAdminCanDeleteNotificationsInBulk()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $notifications = Notification::factory(5)->create();
        $response = $this->get(route('admin.notifications.index'));
        foreach ($notifications as $notification) {
            $response->assertSee($notification->title);
        }
        $notificationIdsToDelete = $notifications->pluck('id')->take(2);
        $response = $this->post(route('admin.notifications.delete'), ['notification_ids' => $notificationIdsToDelete]);
        $this->assertDatabaseMissing('notifications', ['id' => $notificationIdsToDelete->first()]);
        $this->assertDatabaseMissing('notifications', ['id' => $notificationIdsToDelete->last()]);
    }

    public function testUserCanEditOrderDetails()
    {
        $product = Product::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user);
        $order = Order::factory()->create(['user_id' => $user->id]);
        $response = $this->get(route('user.dashboard'));
        $response->assertSee('Orders');
        $response = $this->get(route('user.orders.show', $order->id));
        $newOrderDetails = [
            'product_id' => $product->id,
            'quantity' => 1,
        ];
        $response = $this->post(route('user.orders.update', $order->id), $newOrderDetails);

        $this->assertDatabaseHas('orders', $newOrderDetails);
    }



    public function testUserCanDeleteOrderHistory()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $orders = Order::factory(3)->create(['user_id' => $user->id]);
        $response = $this->get(route('user.dashboard'));
        $response->assertSee('Orders');
        $response = $this->delete(route('user.orders.destroyAll'));
        $this->assertDatabaseMissing('orders', ['user_id' => $user->id]);
    }


    public function testUserCanDeleteComments()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $comments = Comment::factory(3)->create(['user_id' => $user->id]);

        $response = $this->get(route('user.dashboard.comments.index'));

        // Assert that the response contains the comments
        foreach ($comments as $comment) {
            $response->assertSee($comment->content);
        }

        // Select some comments to delete
        $commentIdsToDelete = $comments->pluck('id')->take(2);

        // Send a DELETE request to delete the selected comments
        $response = $this->delete(route('user.dashboard.comments.destroy', $commentIdsToDelete));

        // Assert that the selected comments are deleted from the database
        $this->assertDatabaseMissing('comments', ['id' => $commentIdsToDelete->first()]);
        $this->assertDatabaseMissing('comments', ['id' => $commentIdsToDelete->last()]);
    }
}
