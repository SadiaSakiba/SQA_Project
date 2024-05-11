<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\User;
use Tests\TestCase;
use App\Models\Media;

class MediaManagerControllerTest extends TestCase
{

    public function testAdminCanUploadPictureInMediaManager()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $response = $this->post(route('admin.media.upload'), [
            'file' => UploadedFile::fake()->image('test-image.jpg')
        ]);
        $response->assertStatus(200);
    }

    public function testAdminCannotUploadDocumentInsteadOfPicture()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin);
        $response = $this->post(route('admin.media.upload'), [
            'file' => UploadedFile::fake()->create('test-document.pdf')
        ]);
        $response->assertStatus(422);
    }

    

}
