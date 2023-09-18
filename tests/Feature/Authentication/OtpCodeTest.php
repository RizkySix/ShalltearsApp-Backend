<?php

namespace Tests\Feature\Authentication;

use App\Mail\RegisterOtpMail;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OtpCodeTest extends TestCase
{
    use RefreshDatabase;
    private $user;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'email' => 'test@gmail.com',
            'email_verified_at' => null
        ]);

        $this->token = $this->user->createToken('shalltears-app')->plainTextToken;

    } 

    /**
     * @group otp-test
     */
    public function test_otp_code_should_exist(): void
    {
       $this->assertDatabaseCount('otp_codes' , 1);
       $this->assertDatabaseHas('otp_codes' , [
        'user_id' => $this->user->id
       ]);
    }

    /**
     * @group otp-test
     */
    public function test_send_invalid_otp_code_should_fail(): void
    {
        //error validation
       $response = $this->postJson(RouteServiceProvider::DOMAIN . 'verify-otp' , [
            'otp_code' => 1234567
       ], [
            'Authorization' => 'Bearer ' . $this->token
       ]);
       $response->assertStatus(400);
       $response->assertJson([
            "validation_errors" => [
                "otp_code" => [
                    "The otp code field must be 6 digits."
                ]
            ]
          ]);
    
        $response->assertJsonStructure([
            "validation_errors" => [
                'otp_code'
            ]
        ]);

        //fail otp code tidak sesuai
        $response = $this->postJson(RouteServiceProvider::DOMAIN . 'verify-otp' , [
            'otp_code' => 123456
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            "status" => false,
            "message" => "Email failed to verified"
        ]);

        $this->assertDatabaseHas('users' , [
            'email' => $this->user->email,
            'email_verified_at' => null
        ]);
    }
    

      /**
     * @group otp-test
     */
    public function test_send_expired_otp_code_should_fail() : void
    {
        Carbon::setTestNow(Carbon::now()->addMinutes(120));

        $getOtp = DB::table('otp_codes')->select('otp_code')->where('user_id' , $this->user->id)->first();

        $response = $this->postJson(RouteServiceProvider::DOMAIN . 'verify-otp' , [
            'otp_code' => $getOtp->otp_code
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'status' => false,
            'message' => 'Email failed to verified'
        ]);

        $this->assertDatabaseHas('users' , [
            'email' => $this->user->email,
            'email_verified_at' => null
        ]);
    }


    /**
     * @group otp-test
     */
    public function test_valid_otp_code_should_success() : void
    {
        $getOtp = DB::table('otp_codes')->select('otp_code')->where('user_id' , $this->user->id)->first();

        $response = $this->postJson(RouteServiceProvider::DOMAIN . 'verify-otp' , [
            'otp_code' => $getOtp->otp_code
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Email succes verified'
        ]);

        $this->assertDatabaseHas('users' , [
            'email' => $this->user->email,
            'email_verified_at' => now()
        ]);

        $this->assertDatabaseEmpty('otp_codes');

    }

    /**
     * @group otp-test
     */
    public function test_resend_otp_should_delete_old_otp_code() :void
    {
        Mail::fake();

        Mail::assertNothingQueued();

        $response = $this->postJson(RouteServiceProvider::DOMAIN . 'resend-otp'  , [] , [
            'Authorization' => 'Bearer ' . $this->token
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseCount('otp_codes' , 1);

        Mail::assertQueued(RegisterOtpMail::class , function($email) {
            return $email->hasTo($this->user->email);
        });

        Mail::assertNothingSent();
    }
}
