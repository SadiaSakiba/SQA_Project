<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\User;
use Tests\TestCase;
use App\Models\Cart;

class CartControllerTest extends TestCase
{
    public function testUserCanAddToCart()
    {
        $product = Product::factory()->create();

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('product.addtocart'), [
            'slug' => $product->slug,
            'quantity' => 1, 
        ]);

        $response->assertSessionHas('success', 'Product has been added to cart');

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
    }

    public function testGuestUserCanAddToCart()
    {
        $product = Product::factory()->create();

        $response = $this->post(route('product.addtocart'), [
            'slug' => $product->slug,
            'quantity' => 1,
        ]);


        $response->assertRedirect(route('login'));

        $this->assertDatabaseMissing('carts', [
            'product_id' => $product->id,
        ]);
    }


    public function testCartSubtotalCalculation()
    {
        $product1 = Product::factory()->create(['price' => 10]); 
        $product2 = Product::factory()->create(['price' => 20]); 


        $user = User::factory()->create();

        $this->actingAs($user);

        $response1 = $this->post(route('product.addtocart'), [
            'slug' => $product1->slug,
            'quantity' => 2, 
        ]);
        $response2 = $this->post(route('product.addtocart'), [
            'slug' => $product2->slug,
            'quantity' => 5,
        ]);

        $response1->assertSessionHas('success', 'Product has been added to cart');
        $response2->assertSessionHas('success', 'Product has been added to cart');

        $subtotal = ($product1->price * 2) + ($product2->price * 3);

        $cartItems = Cart::where('user_id', $user->id)->get();

        $actualSubtotal = $cartItems->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });

        $this->assertEquals($subtotal, $actualSubtotal);
    }

    public function testErrorMessageForWrongCoupon()
    {
        $response = $this->post(route('checkout.apply_coupon'), ['coupon_code' => 'INVALIDCODE']);

        $response->assertSessionHas('error', 'Invalid coupon code. Please try again.');
    }


    public function testValidCouponAppliesCorrectDiscount()
    {
        $response = $this->post(route('checkout.apply_coupon'), ['coupon_code' => 'VALIDCODE']);

        $response->assertSessionHas('success', 'Coupon applied successfully.');

        $response->assertSee('Discounted total');
    }


    public function testCorrectDeliveryChargesAreAdded()
    {
        $response = $this->post(route('checkout.add_delivery_charge'), ['area' => 'SelectedArea']);

        $response->assertSessionHas('success', 'Delivery charge added successfully.');

        $response->assertSee('Total amount with delivery charge');
    }


    public function testOutOfStockProductCannotBeAddedToCart()
    {
        $product = Product::factory()->create(['stock' => 0]);
        $response = $this->post(route('cart.add', ['slug' => $product->slug]));
        $response->assertSessionHas('error', 'Product is out of stock');
    }

    public function testOrderCanBeConfirmedWithCashOnDeliveryOption()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $product = Product::factory()->create();
        $response = $this->post(route('cart.add', ['slug' => $product->slug]));

        $response = $this->get(route('checkout'));
        $response = $this->post(route('checkout.payment'), ['payment_method' => 'cash_on_delivery']);
        $response = $this->post(route('checkout.confirm'));
        $response->assertSee('Order confirmed successfully.');
    }

    public function testOrderCanBeConfirmedWithOtherPaymentOptions()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $product = Product::factory()->create();
        $response = $this->post(route('cart.add', ['slug' => $product->slug]));
        $response = $this->get(route('checkout'));
        $response = $this->post(route('checkout.payment'), ['payment_method' => 'card']);
        $response = $this->post(route('checkout.confirm'));
        $response->assertSee('Order confirmed successfully.');
    }
}
