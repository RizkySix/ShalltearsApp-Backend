<?php

namespace Tests\Feature\Like;

use App\Models\Post;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Trait\Test\TestingTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase, TestingTrait;
    private $user, $secUser;
    private $post;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->secUser = User::factory()->create();
    }

    /**
     * @group like-test
     */
    public function test_user_can_like_post_and_return_json_response(): void
    {
        $this->makePost();

        //hit endpoint like post
        $response = $this->actingAs($this->user)->putJson(RouteServiceProvider::DOMAIN . 'like/' . $this->post->uuid);
        $response->assertStatus(200);
        
        $this->assertDatabaseCount('likes' , 1);
        $this->assertDatabaseHas('likes' , [
            'user_id' => $this->user->id,
            'post_id' => $this->post->id
        ]);

        //pastikan jumlah likes pada posts bertambah
        $this->assertDatabaseHas('posts' , [
            'id' => $this->post->id,
            'like' => 1 
        ]);

        //pastikan return jsonnya berbentuk json dengan format yang valid
        $response->assertJsonStructure([
            'status',
            'message',
            'like',
            'total_like'
        ]);

        //pastikan status like dan total like sesuai
        $response->assertJson([
            'like' => 'Liked',
            'total_like' => 1
        ]);
        
    }



     /**
     * @group like-test
     */
    public function test_user_can_unlike_post_and_return_json_response(): void
    {
        $this->test_user_can_like_post_and_return_json_response();

        //hit endpoint like lagi untuk post yang sama untuk unlike post
        $response = $this->actingAs($this->user)->putJson(RouteServiceProvider::DOMAIN . 'like/' . $this->post->uuid);
        $response->assertStatus(200);
        
        //harusnya record likes sudah terhapus
        $this->assertDatabaseEmpty('likes');
        $this->assertDatabaseMissing('likes' , [
            'user_id' => $this->user->id,
            'post_id' => $this->post->id
        ]);

        //pastikan jumlah likes pada posts berkurang
        $this->assertDatabaseHas('posts' , [
            'id' => $this->post->id,
            'like' => 0 
        ]);

        //pastikan return jsonnya berbentuk json dengan format yang valid
        $response->assertJsonStructure([
            'status',
            'message',
            'like',
            'total_like'
        ]);

        //pastikan status like dan total like sesuai
        $response->assertJson([
            'like' => 'Unliked',
            'total_like' => 0
        ]);
        
    }


     /**
     * @group like-test
     */
    public function test_notification_should_not_added_when_owner_like_his_own_post() : void
    {
        $this->test_user_can_like_post_and_return_json_response();
        
        //user adalah pemilik posts dan pastikan notification listnya null
        $this->user->refresh;
        $this->assertEquals(null , $this->user->notification_list);
    }

    /**
     * @group like-test
     */
    public function test_notification_added_when_other_user_like_other_user_post() : void
    {
        $this->makePost(true);

        //clear cache dulu
        Cache::clear();

        //hit endpoint like post
        $response = $this->actingAs($this->user)->putJson(RouteServiceProvider::DOMAIN . 'like/' . $this->post->uuid);
        $response->assertStatus(200);

        $this->secUser->refresh();

        //pastikan notifikasi user 2 ditambah
        $this->assertNotEmpty($this->secUser->notification_list);

        $notification = json_decode($this->secUser->notification_list , true);
        $this->assertEquals(1 , count($notification));

    }


    /**
     * @group like-test
     */
    public function test_add_notification_can_not_spamming() : void
    {
        $this->test_notification_added_when_other_user_like_other_user_post();

        //disini tidak menggunakan Cache clear supaya gate/security handle spam notifnya dijalankan
        //Cache::clear();

        //hit endpoint like post 2x hit pertama akan unlike, dan hit ke 2 untuk like kembali
        $response = $this->actingAs($this->user)->putJson(RouteServiceProvider::DOMAIN . 'like/' . $this->post->uuid);
        $response->assertStatus(200);
        $response = $this->actingAs($this->user)->putJson(RouteServiceProvider::DOMAIN . 'like/' . $this->post->uuid);
        $response->assertStatus(200);

        $this->secUser->refresh();

        //pastikan notifikasi tetap 1 menandakan bahwa gate dijalankan
        $this->assertNotEmpty($this->secUser->notification_list);

        $notification = json_decode($this->secUser->notification_list , true);
        $this->assertEquals(1 , count($notification));

        
    }


    /**
     * @group like-test
     */
    public function test_notification_message_for_thread_should_valid_format() : void
    {
        //buat post thread untuk secUser
        $this->makePost(true);

        //clear cache dulu
        Cache::clear();

        //hit endpoint like post
        $response = $this->actingAs($this->user)->putJson(RouteServiceProvider::DOMAIN . 'like/' . $this->post->uuid);
        $response->assertStatus(200);

        $this->secUser->refresh();
     
        //memastikan message yang ditambahkan ke notification sesuai
        $this->assertEquals(true, stripos($this->secUser->notification_list , '"accepter_content": null'));
        $this->assertEquals(true, stripos($this->secUser->notification_list , '"message": "menyukai thread anda"'));
        $this->assertEquals(true, stripos($this->secUser->notification_list , '"sender_username": "' . $this->user->username .'"'));


    }


    /**
     * @group like-test
     */
    public function test_notification_message_for_album_should_valid_format() : void
    {
        //buat post album untuk secUser
        $this->makePost(true , 'album');

        //clear cache dulu
        Cache::clear();

        //hit endpoint like post
        $response = $this->actingAs($this->user)->putJson(RouteServiceProvider::DOMAIN . 'like/' . $this->post->uuid);
        $response->assertStatus(200);

        $this->secUser->refresh();
     
        //memastikan message yang ditambahkan ke notification sesuai
        $this->assertEquals(true, stripos($this->secUser->notification_list , '"accepter_content": "http://shalltears-app.test/storage/'));
        $this->assertEquals(true, stripos($this->secUser->notification_list , '"message": "menyukai album anda"'));
        $this->assertEquals(true, stripos($this->secUser->notification_list , '"sender_username": "' . $this->user->username .'"'));

    }


     /**
     * type dapat berupa 'thread' default atau 'album'
     */
    private function makePost(bool $secUser = false , string $type = 'thread') : void
    {
       $this->post = Post::factory()->create([
            'user_id' => $secUser ? $this->secUser->id : $this->user->id
        ]);

        if($type === 'thread'){
            $this->makeThread($this->post , false);
        }elseif($type === 'album'){
            $this->makeAlbum($this->post , true , false);
        }
    }
}
