<?php

namespace Tests\Feature\User;

use App\Models\Post;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Trait\Test\TestingTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Str;

class UserFetchingTest extends TestCase
{
    use RefreshDatabase , TestingTrait;
    private $user, $secUser;
    private $post;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->secUser = User::factory()->create();
    }


    /**
     * @group user-test
     */
    public function test_readed_notif_added_when_user_read_notification(): void
    {
        $this->hit_like();

        $this->secUser->refresh();

        $this->assertNotEmpty($this->secUser->notification_list);

        $notification = json_decode($this->secUser->notification_list , true);
        $totalNotif = count($notification);

        //pastikan jumlah readed notif saat ini masih 0 dan jumlah notifikasinya 1
        $this->assertEquals(1 , count($notification));
        $this->assertDatabaseHas('users' , [
            'username' => $this->secUser->username,
            'readed_notification' => 0,
        ]);

        //hit endpoint read notification
        $response =  $this->actingAs($this->secUser)->putJson(RouteServiceProvider::DOMAIN . 'notification/readed' , [
            'readed_notif' => $totalNotif
        ]);
        $response->assertStatus(200);

        //pastikan sekarang jumlah readed_notif nya bertambah
        $this->assertDatabaseHas('users' , [
            'username' => $this->secUser->username,
            'readed_notification' => 1,
        ]);

    }

    /**
     * @group user-test
     */
    public function test_clear_notification_should_remove_all_user_notification() : void
    {
        $this->test_readed_notif_added_when_user_read_notification();

        //pastikan notification user list masih ada
        $this->assertNotEmpty($this->secUser->notification_list);

        //hit endpoint clear notification
        $response =  $this->actingAs($this->secUser)->putJson(RouteServiceProvider::DOMAIN . 'notification/readed/clear');
        $response->assertStatus(200);

        $this->secUser->refresh();

        //pastikan sekarang notification list user sudah terhapus
        $this->assertEmpty($this->secUser->notification_list);
        
    }

     /**
     * @group user-test
     */
    public function test_searching_user_can_with_username_firstName_or_lastName_and_should_return_six_data() : void
    {
        $this->makeRandomUser('aldi' , 'reka' , 'adegan' , 10);
        $this->makeRandomUser('buk' , 'agus' , 'hot' , 10);

        //test pertama coba fetch dengan username
        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'find-user?user_keyword=aldi');
        $response->assertStatus(200);

        //pastikan total datanya adalah 6 dan hanya username valid saja
        $response->assertJsonCount(6 , 'match_users');
        $response->assertSee([
            'aldi',
            'reka',
            'adegan'
        ]);
        $response->assertDontSee([
            'buk',
            'agus',
            'hot'
        ]);

        //test kedua coba fetch dengan first_name
        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'find-user?user_keyword=reka');
        $response->assertStatus(200);

        //pastikan total datanya adalah 6 dan hanya username valid saja
        $response->assertJsonCount(6 , 'match_users');
        $response->assertSee([
            'aldi',
            'reka',
            'adegan'
        ]);
        $response->assertDontSee([
            'buk',
            'agus',
            'hot'
        ]);


        //test ketiga coba fetch dengan last_name
        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'find-user?user_keyword=adegan');
        $response->assertStatus(200);

        //pastikan total datanya adalah 6 dan hanya username valid saja
        $response->assertJsonCount(6 , 'match_users');
        $response->assertSee([
            'aldi',
            'reka',
            'adegan'
        ]);
        $response->assertDontSee([
            'buk',
            'agus',
            'hot'
        ]);

    }

    /**
     * @group user-test
     */
    public function test_should_return_all_match_user_when_adding_fetch_all_query_params() : void
    {
        $this->makeRandomUser('aldi' , 'reka' , 'adegan' , 10);
        $this->makeRandomUser('buk' , 'agus' , 'hot' , 10);

        //test pertama coba fetch dengan username
        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'find-user?user_keyword=aldi&fetch_all=true');
        $response->assertStatus(200);

        //pastikan total datanya adalah 10 dan hanya username valid saja
        $response->assertJsonCount(10 , 'match_users');
        $response->assertSee([
            'aldi',
            'reka',
            'adegan'
        ]);
        $response->assertDontSee([
            'buk',
            'agus',
            'hot'
        ]);

        //test kedua coba fetch dengan first_name
        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'find-user?user_keyword=reka&fetch_all=true');
        $response->assertStatus(200);

        //pastikan total datanya adalah 10 dan hanya username valid saja
        $response->assertJsonCount(10 , 'match_users');
        $response->assertSee([
            'aldi',
            'reka',
            'adegan'
        ]);
        $response->assertDontSee([
            'buk',
            'agus',
            'hot'
        ]);


        //test ketiga coba fetch dengan last_name
        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'find-user?user_keyword=adegan&fetch_all=true');
        $response->assertStatus(200);

        //pastikan total datanya adalah 10 dan hanya username valid saja
        $response->assertJsonCount(10 , 'match_users');
        $response->assertSee([
            'aldi',
            'reka',
            'adegan'
        ]);
        $response->assertDontSee([
            'buk',
            'agus',
            'hot'
        ]);
    }


    /**
     * @group user-test
     */
    public function test_should_return_zero_match_users_when_query_keyword_not_found() : void
    {
        $this->makeRandomUser('buk' , 'agus' , 'hot' , 10);

        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'find-user?user_keyword=adegan');
        $response->assertStatus(200);

        $response->assertJsonCount(0 , 'match_users');
        $response->assertDontSee([
            'buk',
            'agus',
            'hot'
        ]);
    }


     /**
     * @group user-test
     */
    public function test_searching_should_return_valid_json_response() : void
    {
        $this->makeRandomUser('buk' , 'agus' , 'hot' , 10);

        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'find-user?user_keyword=agus');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'match_users' => [
                '*' => [
                    'first_name',
                    'last_name',
                    'username',
                    'foto_profile',
                    'bio',
                    'address',
                    'phone_number',
                    'notification_list',
                    'readed_notif'
                ]
            ]
        ]);
    }


     /**
     * @group user-test
     */
    public function test_user_can_get_specifiec_user_profile_and_should_return_valid_json_response() : void
    {
        //buat 10 post menggunakan method dari TestingTrait
        $this->makePost(now() , 10 , $this->user , $this->secUser , 'both');

        //hit endpoint
        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'user/profile/' . $this->user->username);
        $response->assertStatus(200);

        //response json harus valid
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'first_name',
                'last_name',
                'username',
                'foto_profile',
                'bio',
                'address',
                'phone_number',
                'notification_list',
                'readed_notif'
            ],
            'total_albums',
            'total_threads'
        ]);

        $response->assertJson([
            'data' => [
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'username' => $this->user->username,
            ],
            'total_albums' => 6,
            'total_threads' => 4
        ]);
    }


     /**
     * @group user-test
     */
    public function test_specifiec_user_should_not_return_any_user_profile_when_username_not_found() : void
    {
        $this->test_user_can_get_specifiec_user_profile_and_should_return_valid_json_response();

        //hit endpoint dengan username sembarangan yang tidak terdaftar
        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'user/profile/nyencaine');
        $response->assertStatus(404);
    }


     /**
     * @group user-test
     */
    public function test_get_personal_user_should_return_valid_response() : void
    {
        //hit endpoint get personal user
        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'personal-user');
        $response->assertStatus(200);

        //pastikan response dengan valid format
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'first_name',
                'last_name',
                'username',
                'foto_profile',
                'bio',
                'address',
                'phone_number',
                'email_verified_at',
                'notification_list',
                'readed_notif'
            ]
        ]);

        $response->assertJson([
            'data' => [
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'username' => $this->user->username,
            ],
        ]);

    }

     /**
     * @group user-test
     */
    public function test_get_personal_user_should_not_return_data_when_request_token_or_user_not_found() : void
    {
        //buat random token invalid/sembarangan
        $invalidToken = fake()->uuid() . 'invalid';

        //hit endpoint dengan invalid token
        $response = $this->getJson(RouteServiceProvider::DOMAIN . 'personal-user' , [
            'Authorization' => 'Bearer ' . $invalidToken
        ]);
        $response->assertStatus(401);
    }



    private function hit_like() : void
    {
        //buat post thread untuk secUser
        $this->makeSinglePost(true);

        //hit endpoint like post
        $this->actingAs($this->user)->putJson(RouteServiceProvider::DOMAIN . 'like/' . $this->post->uuid);
    }

    /**
     * type dapat berupa 'thread' default atau 'album'
     */
    private function makeSinglePost(bool $secUser = false , string $type = 'thread') : void
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

     /**
     * make random user for testing
     */
    private function makeRandomUser(string $username , string $firstName, string $lastName, int $qty) : void
    {
        if($qty > 0){
            $username = $username . rand(10,100);
            User::factory()->create([
                'username' => $username,
                'first_name' => $firstName . rand(10,100),
                'last_name' => $lastName . rand(10,100),
            ]);

            $this->makeRandomUser($username , $firstName, $lastName , $qty - 1);
        }
    }
}
