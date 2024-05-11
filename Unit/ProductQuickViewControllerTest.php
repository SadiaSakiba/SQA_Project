<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\User;
use Tests\TestCase;
use App\Models\Cart;

class ProductQuickViewController extends TestCase
{

    /** @test */
    public function testProductQuickView()
    {
        // Create a sample product
        $product = Product::create([
            'title' => 'Test Product',
            'summary' => 'Test Summary',
            'description' => 'Test Description',
            'photo' => 'test_photo.jpg',
            'stock' => 10,
            'cat_id' => 1, 
            'status' => 'active',
            'condition' => 'default',
            'price' => 100,
        ]);
        $response = $this->get(route('product.index'));

        $response->assertSee($product->title);

        $response = $this->post(route('product.quickview', ['id' => $product->id]));

        $response->assertSuccessful();

        $response->assertSee('Quick View');
    }


    public function testUserCanAddToCartFromQuickView()
    {

        $product = Product::factory()->create();

        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('product.addtocart.quickview', ['slug' => $product->slug]));

        $response->assertSessionHas('success', 'Product has been added to cart');

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function testUserCanAddToWishlistFromQuickView()
    {
        $product = Product::factory()->create();

        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('product.addtowishlist.quickview', ['slug' => $product->slug]));

        $response->assertSessionHas('success', 'Product has been added to wishlist');

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

}