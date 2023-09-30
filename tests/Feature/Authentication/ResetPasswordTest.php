<?php

namespace Tests\Feature\Authentication;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;
    private $user;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'email' => 'test@gmail.com'
        ]);

        $this->token = $this->user->createToken('shalltears-app')->plainTextToken;
    } 

    /**
     * @group resetpass-test
     */
    public function test_email_queued_and_password_not_changed_yet_and_user_have_reset_token(): void
    {
        Mail::fake();
       
        Mail::assertNothingQueued();
       
        $response = $this->postJson(RouteServiceProvider::DOMAIN . 'reset-password' , [
            'email' => $this->user->email
        ]);
        $response->assertStatus(200);
        $response->json([
            'status' => true,
            'message' => 'New password send to your mail',
        ]);

        $this->user->refresh();

        $this->assertEquals(true , Hash::check('password' , $this->user->password)); //password belum berubah berubah
        $this->assertNotEquals(null , $this->user->reset_password_token); //pastikan token reset password user tidak kosong

        Mail::assertQueued(ResetPasswordMail::class , function($email) {
            return $email->hasTo($this->user->email);
        });
        
        Mail::assertNothingSent();
    }

    
      /**
     * @group resetpass-test
     */
    public function test_new_password_changing_after_user_verifying() : void
    {
        $this->test_email_queued_and_password_not_changed_yet_and_user_have_reset_token();

        $this->postJson(RouteServiceProvider::DOMAIN . 'reset-password/' . $this->user->reset_password_token . '/' . $this->user->email)->assertStatus(200);

        $this->user->refresh();

        //pastikan sekarang password user sudah berganti
        $this->assertNotEquals(true , Hash::check('password' , $this->user->password));

        //pastikan token sudah terhapus
        $this->assertEquals(null , $this->user->reset_password_token);
        $this->assertEquals(false , Cache::get('reset-password-' . $this->user->email));

    }

    /**
     * @group resetpass-test
     */
    public function test_reset_password_token_expired_in_60_minutes() : void
    {
        $this->test_email_queued_and_password_not_changed_yet_and_user_have_reset_token();

        //travel time 61 menit kemasa depan
        Carbon::setTestNow(now()->addHours(1));
        Cache::clear();

        //route verifikasi harus susdah expired
        $this->postJson(RouteServiceProvider::DOMAIN . 'reset-password/' . $this->user->reset_password_token . '/' . $this->user->email)->assertStatus(422);

    }

      /**
     * @group resetpass-test
     */
    public function test_invalid_current_password_should_fail_canging_password() : void
    {
        $response = $this->putJson(RouteServiceProvider::DOMAIN . 'reset-password' , [
            'current_password' => 'bukanPassword',
            'password' => 'newPass',
            'password_confirmation' => 'newPass',
        ] , [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(422);
        $response->json([
            'status' => false,
            'message' => 'Current password not match',
        ]);

        $this->user->refresh();

        $this->assertEquals(true , Hash::check('password' , $this->user->password)); //password belum berubah


    }


     /**
     * @group resetpass-test
     */
    public function test_valid_current_password_should_success() : void
    {
        $response = $this->putJson(RouteServiceProvider::DOMAIN . 'reset-password' , [
            'current_password' => 'password',
            'password' => 'newPass',
            'password_confirmation' => 'newPass',
        ] , [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200);
        $response->json([
            'status' => true,
            'message' => 'New password updated',
        ]);

        $this->user->refresh();
        
        $this->assertEquals(true , Hash::check('newPass' , $this->user->password)); //password belum berubah


    }
}
