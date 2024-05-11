<?php

namespace Tests\Feature;

use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\User;
use Tests\TestCase;
use App\Models\Post;
use App\Models\Order;
use App\Models\Shipping;
use App\Models\PostComment;

class AdminDashboardControllerTest extends TestCase
{
  
    public function testAdminCanGenerateOrderReceiptPdf()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin);

        $response = $this->get(route('admin.orders.pdf'));
    }


    public function testPendingOrderIsUpdatedToDeliveredStatusByAdmin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $order = Order::factory()->create(['status' => 'pending']);
        $response = $this->put(route('admin.orders.update', ['order' => $order->id]), ['status' => 'delivered']);
        $this->assertEquals('delivered', $order->fresh()->status);
    }

    public function testAdminCanDeleteSpecificOrderHistoryEntry()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $order = Order::factory()->create();
        $response = $this->delete(route('admin.orders.delete', ['order' => $order->id]));
        $this->assertDeleted($order);
    }

}
