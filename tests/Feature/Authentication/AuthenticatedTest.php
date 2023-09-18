<?php

namespace Tests\Feature\Authentication;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthenticatedTest extends TestCase
{
    use RefreshDatabase;
    private $user;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'email' => 'test@gmail.com',
        ]);

        $this->token = $this->user->createToken('shalltears-app')->plainTextToken;

    } 

    /**
     * @group auth-test
     */
    public function test_invalid_crendential_login_should_fail(): void
    {
        $response = $this->postJson(RouteServiceProvider::DOMAIN . 'login' , [
            'user_mail' => 'datasalah',
            'password' => 'password'
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'status' => false,
            'message' => 'Credentials not match'
        ]);
        $this->assertArrayNotHasKey('token' , $response);
        $this->assertDatabaseCount('personal_access_tokens' , 1);
    }


     /**
     * @group auth-test
     */
    public function test_valid_crendential_login_should_success(): void
    {

        //percobaan petama dengan email
        $response = $this->postJson(RouteServiceProvider::DOMAIN . 'login' , [
            'user_mail' => $this->user->email,
            'password' => 'password'
        ]);

        $response->assertStatus(200);
        $this->assertNotNull($response['token']);

        $response->assertJson([
            'status' => true,
            'message' => 'Login Success',
            'token' => $response['token'],
        ]);
        $this->assertDatabaseCount('personal_access_tokens' , 2);


        //percobaan kedua dengan username
        $response = $this->postJson(RouteServiceProvider::DOMAIN . 'login' , [
            'user_mail' => $this->user->username,
            'password' => 'password'
        ]);

        $response->assertStatus(200);
        $this->assertNotNull($response['token']);

        $response->assertJson([
            'status' => true,
            'message' => 'Login Success',
            'token' => $response['token'],
        ]);
        $this->assertDatabaseCount('personal_access_tokens' , 3);
    }

    /**
     * @group auth-test
     */
    public function test_all_tokens_should_revoked_after_logout() : void
    {
        $response = $this->postJson(RouteServiceProvider::DOMAIN . 'logout' , [] , [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseEmpty('personal_access_tokens');
    }
}
