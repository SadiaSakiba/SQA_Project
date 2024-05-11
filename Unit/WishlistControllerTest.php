<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\User;
use Tests\TestCase;
use App\Models\WishlistProduct;

class WishlistControllerTest extends TestCase
{
    use RefreshDatabase;

 /** @test */
 public function user_adds_product_to_wishlist()
 {
     $product = new Product();
     $product->title = 'Test Product';
     $product->summary = 'This is a test summary';
     $product->slug = 'test-product';
     $product->photo = 'test-photo.jpg';
     $product->price = 10.99;
     $product->discount = 0;
     $product->is_featured = false;
     $product->save();

     $user = factory(\App\User::class)->make();
     $this->actingAs($user);

     $response = $this->get('/wishlist', ['slug' => $product->slug]);

    //  $response->assertSessionHas('success', 'Product added to wishlist');

     $this->assertDatabaseHas('wishlists', [
         'user_id' => $user->id,
         'product_id' => $product->id,
         'price'=> $product->price,
         'quantity'=> $product->quantity,
         'amount'=>$product->amount,
     ]);
 }

 public function testGuestUserCanAddToWishlist()
 {
     $product = Product::factory()->create();

     $response = $this->post(route('product.addtowishlist'), [
         'slug' => $product->slug,
     ]);
     $response->assertRedirect(route('login'));

     $this->assertDatabaseMissing('wishlists', [
         'product_id' => $product->id,
     ]);
 }


 public function testDeleteWishlistProductAfterLoggingIn()
 {
     
     $user = User::factory()->create();

     $this->actingAs($user);
     $wishlistProduct = WishlistProduct::factory()->create(['user_id' => $user->id]);
     $response = $this->delete(route('wishlist.delete', ['id' => $wishlistProduct->id]));
     $response->assertSessionHas('success', 'Product removed from wishlist successfully.');
     $response->assertStatus(200); 
     $this->assertDatabaseMissing('wishlist_products', ['id' => $wishlistProduct->id]);
 }

 public function testDeleteWishlistProductBeforeLoggingIn()
 {

     $wishlistProduct = WishlistProduct::factory()->create();
     $response = $this->delete(route('wishlist.delete', ['id' => $wishlistProduct->id]));
     $response->assertRedirect(route('login'));
     $this->assertDatabaseHas('wishlist_products', ['id' => $wishlistProduct->id]);
 }



    /** @test */
    public function it_returns_error_for_invalid_product_slug()
    {
        $user = factory(User::class)->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $response = $this->post('/wishlist', ['slug' => 'invalid-slug']);

        $response->assertSessionHas('error', 'Invalid Products');
    }

    /** @test */
    public function it_returns_error_for_existing_product_in_wishlist()
    {
        $product = Product::create([
            'title' => 'Test Product',
            'summary' => 'This is a test summary',
            'slug' => 'test-product',
            'photo' => 'test-photo.jpg', 
            'price' => 10.99,
            'discount' => 0,
            'is_featured' => false,
        ]);

        $user = factory(User::class)->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,

        ]);

    
        $this->actingAs($user);

        
        $response = $this->post('/wishlist', ['slug' => $product->slug]);


        $response->assertSessionHas('error', 'You already placed in wishlist');
    }
}
