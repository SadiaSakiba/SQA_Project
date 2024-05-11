<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\User;
use Illuminate\Support\Facades\DB;


class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_a_login_form()
    {
        $response = $this->get('user/login');

        $response->assertSuccessful();
        $response->assertViewIs('frontend.pages.login');
    }

    public function test_user_cannot_view_a_login_form_when_authenticated()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)->get('user/login');

        $response->assertRedirect('/');
    }

    public function test_user_can_login_with_correct_credentials()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt($password = '123456'),
        ]);


        $response = $this->post('user/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }


    public function test_user_cannot_login_with_incorrect_password()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt('123456'),
        ]);

        $response = $this->from('user/login')->post('user/login', [
            'email' => $user->email,
            'password' => 'invalid-password',
        ]);

        $response->assertRedirect('user/login');
        // $response->assertSessionHasErrors('email');
        // $this->assertTrue(session()->hasOldInput('email'));
        // $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

}