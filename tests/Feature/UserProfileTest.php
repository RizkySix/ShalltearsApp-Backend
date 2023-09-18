<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class UserProfileTest extends TestCase
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
    public function test_update_profile(): void
    {
        $data = [
            'first_name' => 'Tukul',
            'last_name' => 'Arwana',
            'username' => $this->user->username,
            'foto_profile' => new UploadedFile(public_path('assets/logo.png') , 'logo.png' , null , null , true)
        ];

        $response = $this->actingAs($this->user)->put('http://shalltears-app.test/api/v1/profile' , $data)->assertStatus(200);
    }
}
