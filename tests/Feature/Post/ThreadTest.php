<?php

namespace Tests\Feature\Post;

use App\Models\Thread;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ThreadTest extends TestCase
{
    use RefreshDatabase;
    private $user, $secUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->secUser = User::factory()->create();
    }

    /**
     * @group post-thread
     */
    public function test_user_can_make_thread(): void
    {
        $payload = [
            'text' => 'This is thread dont you know?'
        ];

         $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN  . 'thread' , $payload)->assertStatus(201);
         $this->assertDatabaseCount('threads' , 1);
    }

     /**
     * @group post-thread
     */
    public function test_user_update_thread_under_1_hour_should_success(): void
    {
        
        //buat thread
        $this->test_user_can_make_thread();

        //buat time palsu kemasa depan sejauh 50menit
        Carbon::setTestNow(now()->addMinutes(50));

        //dapatkan slug thread ke database
        $thread = Thread::select('slug' , 'text')->first();

        $payload = [
            'text' => 'This is thread dont you know? but this is new one OKEY'
        ];

         $this->actingAs($this->user)->putJson(RouteServiceProvider::DOMAIN  . 'thread/' . $thread->slug , $payload)->assertStatus(200);

         //pastikan data terbaru ada dan data lama sudah tergantikan
         $this->assertDatabaseHas('threads' , [
            'text' => $payload['text']
         ]);

         $this->assertDatabaseMissing('threads' , [
            'text' => $thread->text
         ]);
    }


      /**
     * @group post-thread
     */
    public function test_user_update_thread_over_1_hour_should_fail(): void
    {
        
        //buat thread
        $this->test_user_can_make_thread();

        //buat time palsu kemasa depan sejauh 61menit
        Carbon::setTestNow(now()->addMinutes(61));

        //dapatkan slug thread ke database
        $thread = Thread::select('slug' , 'text')->first();

        $payload = [
            'text' => 'This is thread dont you know? but this is new one OKEY'
        ];

         $this->actingAs($this->user)->putJson(RouteServiceProvider::DOMAIN  . 'thread/' . $thread->slug , $payload)->assertStatus(403);

         //pastikan data terbaru tidak tersimpan dan data lama tidak tergantikan
         $this->assertDatabaseMissing('threads' , [
            'text' => $payload['text']
         ]);

         $this->assertDatabaseHas('threads' , [
            'text' => $thread->text
         ]);
    }

    /**
     * @group post-thread
     */
    public function test_update_thread_only_allowed_for_owner() : void
    {
        //buat thread
        $this->test_user_can_make_thread();

        //dapatkan slug thread ke database
        $thread = Thread::select('slug' , 'text')->first();
        
        $payload = [
            'text' => 'Stupid you cant access this thread'
        ];

         $this->actingAs($this->secUser)->putJson(RouteServiceProvider::DOMAIN  . 'thread/' . $thread->slug , $payload)->assertStatus(403);
    }

}
