<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\User;
use Tests\TestCase;
use App\Models\Post;

class ProductControllerTest extends TestCase
{

    public function testProductsAreFilteredCorrectly()
    {
      
        $product1 = Product::factory()->create(['price' => 50]);
        $product2 = Product::factory()->create(['price' => 100]);
        $product3 = Product::factory()->create(['price' => 150]);
        $response = $this->get(route('products.index', ['min_price' => 75, 'max_price' => 125]));
        $response->assertSee($product2->title);
        $response->assertDontSee($product1->title);
        $response->assertDontSee($product3->title);
    }


    public function testProductsAreDisplayedWithMatchingOrSimilarNames()
    {
        $product1 = Product::factory()->create(['title' => 'Apple iPhone 12']);
        $product2 = Product::factory()->create(['title' => 'Samsung Galaxy S20']);
        $product3 = Product::factory()->create(['title' => 'OnePlus 9 Pro']);
        $response = $this->get(route('products.index', ['search' => 'iPhone']));
        $response->assertSee($product1->title);
        $response->assertDontSee($product2->title);
        $response->assertDontSee($product3->title);
    }
     

    public function testNoProductsAreShownForNonExistingPriceRange()
    {
        $product1 = Product::factory()->create(['price' => 50]);
        $product2 = Product::factory()->create(['price' => 100]);
        $product3 = Product::factory()->create(['price' => 150]);
        $response = $this->get(route('products.index', ['min_price' => 200, 'max_price' => 250]));
        $response->assertSee('No products available in this price range.');
    }



}
