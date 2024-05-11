<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\User;
use Tests\TestCase;
use App\Models\Post;

class BlogControllerTest extends TestCase
{

 public function testUserCannotCommentWithoutLogin()
 {
     $post = Post::factory()->create();

     $response = $this->post(route('comment.store'), [
         'slug' => $post->slug,
         'comment' => 'Test Comment',
     ]);

     $response->assertRedirect(route('login'));

     $this->assertDatabaseMissing('post_comments', [
         'comment' => 'Test Comment',
     ]);
 }

 public function testUserCanCommentWithLogin()
 {
     $post = Post::factory()->create();

     $user = User::factory()->create();

     $this->actingAs($user);

     $response = $this->post(route('comment.store'), [
         'slug' => $post->slug,
         'comment' => 'Test Comment',
     ]);

     $response->assertSessionHas('success', 'Thank you for your comment');

     $this->assertDatabaseHas('post_comments', [
         'comment' => 'Test Comment',
     ]);
 }


 public function testReactionWithoutLoggingIn()
    {
        $response = $this->post(route('blog.reaction', ['blog_id' => 1]));

        $response->assertRedirect(route('login'));

        $this->assertDatabaseMissing('blog_reactions', ['blog_id' => 1, 'user_id' => null]);
    }


    public function testReactionAfterLoggingIn()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('blog.reaction', ['blog_id' => 1]));

        $response->assertSessionHas('success', 'Reaction added successfully.');
        $response->assertStatus(200);

        $this->assertDatabaseHas('blog_reactions', ['blog_id' => 1, 'user_id' => $user->id]);
    }

}
