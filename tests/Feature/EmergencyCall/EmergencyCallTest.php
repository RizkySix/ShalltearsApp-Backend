<?php

namespace Tests\Feature\EmergencyCall;

use App\Mail\EmergencyShalltearMail;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmergencyCallTest extends TestCase
{
    use RefreshDatabase;
    private $user, $secUser, $thirdUser;


    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->secUser = User::factory()->create();
        $this->thirdUser = User::factory()->create([
            'email_verified_at' => null
        ]);

    }

    /**
     * @group emergency-test
     */
    public function test_user_can_make_an_emergency_call_mail(): void
    {
        Mail::fake();

        //pastikan belum ada Mail yang di queue
        Mail::assertNothingQueued();

        $response = $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . 'emergency-mail' , $this->payload());
        $response->assertStatus(202);

        //pastikan hanya user dan secUser saja yang akan menerima email, karena thirdUser emailnya belum diverifikasi
        Mail::assertQueued(EmergencyShalltearMail::class , function($email) {
            return $email->hasTo($this->user->email);
        });
        Mail::assertQueued(EmergencyShalltearMail::class , function($email) {
            return $email->hasTo($this->secUser->email);
        });
        Mail::assertQueued(EmergencyShalltearMail::class , function($email) {
            return !$email->hasTo($this->thirdUser->email);
        });

        Mail::assertQueuedCount(2);
      
        Mail::assertNothingSent();
        
        //clear cache agar test ini dapat dijalankan terus (supaya tidak terkena limit)
        Cache::clear();
    }


     /**
     * @group emergency-test
     */
    public function test_user_will_get_limit_for_24_hours_after_sending_emergency_call_mail() : void
    {
        Mail::fake();

        //pastikan belum ada Mail yang di queue
        Mail::assertNothingQueued();

        //hit pertama
        $response = $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . 'emergency-mail' , $this->payload());
        $response->assertStatus(202);
        
        //pastikan mail queued ke valid user
        Mail::assertQueued(EmergencyShalltearMail::class , function($email) {
            return $email->hasTo($this->user->email);
        });
        Mail::assertQueued(EmergencyShalltearMail::class , function($email) {
            return $email->hasTo($this->secUser->email);
        });
        Mail::assertQueued(EmergencyShalltearMail::class , function($email) {
            return !$email->hasTo($this->thirdUser->email);
        });

        Mail::assertQueuedCount(2);
      
        Mail::assertNothingSent();


        //hit kedua
        $response = $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . 'emergency-mail' , $this->payload());
        $response->assertStatus(422);

        //pastikan tidak ada email yang terqueued karena user saat ini sudah mencapai limit harian
        Mail::assertQueued(EmergencyShalltearMail::class , function($email) {
            return !$email->hasTo($this->user->email);
        });
        Mail::assertQueued(EmergencyShalltearMail::class , function($email) {
            return !$email->hasTo($this->secUser->email);
        });
        Mail::assertQueued(EmergencyShalltearMail::class , function($email) {
            return !$email->hasTo($this->thirdUser->email);
        });

        Mail::assertQueuedCount(2); //pastikan queued email masih 2 yang didapat dari hit pertama tadi
      
        Mail::assertNothingSent();
        
    }


     /**
     * @group emergency-test
     */
    public function test_check_limit_user_should_return_valid_json_response_available_case() : void
    {
        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'emergency-mail/limit');
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'status',
            'message'
        ]);

        $response->assertJson([
            'status' => true,
            'message' => 'You have access'
        ]);
    }


    /**
     * @group emergency-test
     */
    public function test_check_limit_user_should_return_valid_json_response_unavailable_case() : void
    {
        $this->test_user_will_get_limit_for_24_hours_after_sending_emergency_call_mail();

        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'emergency-mail/limit');
        $response->assertStatus(422);

        $response->assertJsonStructure([
            'status',
            'message'
        ]);

        $response->assertJson([
            'status' => false,
            'message' => 'You have access'
        ]);
    }


    private function payload() : array
    {
        return [
            'message' => 'ini adalah emergency call bey',
            'place' => 'Rumah jand*',
            'map' => 'https://laravel.com/docs/10.x/queries#select-statements' //this must be an place url from google maps
        ];
    }
}
