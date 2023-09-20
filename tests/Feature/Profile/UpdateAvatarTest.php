<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpdateAvatarTest extends TestCase
{
    use RefreshDatabase;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }


    /**
     * @group user-profile
     */
    public function test_avatar_should_image(): void
    {
        Storage::fake();
        //success test
        $data = [
            'foto_profile' => UploadedFile::fake()->create('test.jpg' , 1000)
        ];

        $this->actingAs($this->user)->put(RouteServiceProvider::DOMAIN . 'profile/avatar' , $data)->assertStatus(200);

        //fail test
        $data = [
            'foto_profile' => UploadedFile::fake()->create('test.doc' , 1000)
        ];
        $this->actingAs($this->user)->put(RouteServiceProvider::DOMAIN . 'profile/avatar' , $data)->assertStatus(400);

    }

     /**
     * @group user-profile
     */
    public function test_avatar_should_under_5mb() :void
    {
        Storage::fake();
        //success test
        $data = [
            'foto_profile' => UploadedFile::fake()->create('test.jpg' , 4000)
        ];

        $this->actingAs($this->user)->put(RouteServiceProvider::DOMAIN . 'profile/avatar' , $data)->assertStatus(200);

        //fail test
        $data = [
            'foto_profile' => UploadedFile::fake()->create('test.jpg' , 5100)
        ];
        $this->actingAs($this->user)->put(RouteServiceProvider::DOMAIN . 'profile/avatar' , $data)->assertStatus(400);
    }
}
