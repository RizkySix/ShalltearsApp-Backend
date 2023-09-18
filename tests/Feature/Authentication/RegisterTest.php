<?php

namespace Tests\Feature\Authentication;

use App\Mail\RegisterOtpMail;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'email' => 'test@gmail.com'
        ]);

    } 

    /**
     *@group register-test
     */
    public function test_validation_register_invalid(): void
    {
       $payload =  $this->set_payload($this->user->email , $this->user->username);
       $response = $this->postJson(RouteServiceProvider::DOMAIN . 'register' , $payload);
       $response->assertStatus(400);
       $response->assertJsonStructure([
        'validation_errors' => [
            'email',
            'username'
        ]
       ]);

       $response->assertJson([
            "validation_errors" => [
                "email" => [
                    "The email has already been taken."
                ]
            ]
        ]);

        $this->assertDatabaseMissing('users' , [
            'first_name' => $payload['first_name'],
            'last_name' => $payload['last_name'],
        ]);

    }

     /**
     *@group register-test
     */
    public function test_token_should_return_succed_registration_and_email_verified_at_must_be_null() : void
    {
        $payload =  $this->set_payload();
        $response = $this->postJson(RouteServiceProvider::DOMAIN . 'register' , $payload);
        $response->assertStatus(201);

        $this->assertDatabaseCount('users' , 2);
        $this->assertDatabaseHas('users' , [
            'first_name' => $payload['first_name'],
            'last_name' => $payload['last_name'],
            'email_verified_at' => null
        ]);

        $this->assertNotNull($response['token']);
        $this->assertDatabaseCount('personal_access_tokens' , 1);
    }

     /**
     *@group register-test
     */
    public function test_email_otp_should_queued() : void
    {
        Mail::fake();

        Mail::assertNothingQueued();
        $payload =  $this->set_payload();
        $response = $this->postJson(RouteServiceProvider::DOMAIN . 'register' , $payload);
        $response->assertStatus(201);

       
        Mail::assertQueued(RegisterOtpMail::class , function($email) use($payload) {
            return $email->hasTo($payload['email']);
        });
        
        Mail::assertNothingSent();
    }

    /**
     *payload
     */
    private function set_payload(string $email = 'rizkyjanu2001@gmail.com' , string $username = 'nikkisix666') : array
    {
        $data = [
            'first_name' => 'TestName',
            'last_name' => 'SecondName',
            'username' => $username,
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'alamat' => 'Jalan pulau kawe 15'
        ];

        return $data;
    }
}
