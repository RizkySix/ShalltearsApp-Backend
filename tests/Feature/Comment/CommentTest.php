<?php

namespace Tests\Feature\Comment;

use App\Models\Comment;
use App\Models\Post;
use App\Models\SubComment;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Trait\Test\TestingTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase , TestingTrait;
    private $user , $secUser;
    private $post;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->secUser = User::factory()->create();
    }

    /**
     * @group comment-test
     */
    public function test_user_can_comment_a_post_and_should_return_json_response(): void
    {
        $this->makePost();

        //payload comment
        $payload = [
            'post_id' => $this->post->id,
            'comment' => 'This is just simple comment oh fuck',
        ];

        //hit endpoint buat comment
        $response = $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . 'comment' , $payload);
        $response->assertStatus(201);
        $this->assertDatabaseCount('comments' , 1);
        $this->assertDatabaseHas('comments' , [
            'user_id' => $this->user->id,
            ...$payload
        ]);

        //pastikan jumlah comments posts bertambah
        $this->assertDatabaseHas('posts' , [
            'id' => $this->post->id,
            'comments' => 1
        ]);

        //pastikan response json dengan valid format
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'comment_id',
                'comment',
                'post_id',
                'created_at',
                'total_comment'
            ]
        ]);

        $response->assertJson([
            'data' => [
                'post_id' => $this->post->id,
                'total_comment' => 1
            ]
        ]);
    }

    /**
     * @group comment-test
     */
    public function test_user_can_add_subcomment_post_and_should_return_json_response() : void
    {
        $this->test_user_can_comment_a_post_and_should_return_json_response();

        //dapatkan id comment
        $comment = Comment::select('id')->first();
        
        //set payload subcomment
        $payload = [
            'comment_id' => $comment->id,
            'sub_comment' => 'This one is sub comment nothing special'
        ];

        //hit end point buat sub comment
        $response = $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . 'sub-comment' , $payload);
        $response->assertStatus(201);
        $this->assertDatabaseCount('sub_comments' , 1);
        $this->assertDatabaseHas('sub_comments' , [
            'user_id' => $this->user->id,
            ...$payload
        ]);

        //pastikan jumlah comments posts bertambah
        $this->assertDatabaseHas('posts' , [
            'id' => $this->post->id,
            'comments' => 2
        ]);

        //pastikan response json dengan valid format
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'sub_comment_id',
                'sub_comment',
                'comment_id',
                'created_at',
                'total_comment'
            ]
        ]);

        $response->assertJson([
            'data' => [
                'comment_id' => $comment->id,
                'total_comment' => 2
            ]
        ]);
    }

      /**
     * @group comment-test
     */
    public function test_delete_sub_comment_only_delete_sub_comment_record() : void
    {
        $this->test_user_can_add_subcomment_post_and_should_return_json_response();

        //dapatkan id sub comment
        $subComment = SubComment::select('id')->first();

        $response = $this->actingAs($this->user)->deleteJson(RouteServiceProvider::DOMAIN . 'sub-comment/' . $subComment->id);
        $response->assertStatus(200);

        //pastikan record sub comment terhapus
        $this->assertDatabaseEmpty('sub_comments');
        $this->assertDatabaseCount('comments' , 1);

        $response->assertJsonStructure([
            'status',
            'message',
            'current_comment' 
        ]);

        //pastikan jumlah total comment sekarang berkurang
        $response->assertJson([
            'current_comment' => 1
        ]);
    }


     /**
     * @group comment-test
     */
    public function test_delete_comment_should_delete_comment_and_all_equivalent_sub_comments() : void
    {
        $this->test_user_can_add_subcomment_post_and_should_return_json_response();

        //dapatkan id comment
        $comment = Comment::select('id')->first();

        $response = $this->actingAs($this->user)->deleteJson(RouteServiceProvider::DOMAIN . 'comment/' . $comment->id);
        $response->assertStatus(200);

        //pastikan record sub comment dan comment terhapus
        $this->assertDatabaseEmpty('sub_comments');
        $this->assertDatabaseEmpty('comments');

        $response->assertJsonStructure([
            'status',
            'message',
            'current_comment' 
        ]);

        //pastikan jumlah total comment sekarang berkurang
        $response->assertJson([
            'current_comment' => 0
        ]);
    } 


     /**
     * @group comment-test
     */
    public function test_should_not_add_notification_when_owner_comment_or_sub_comment_his_own_posts() : void
    {
        $this->test_user_can_add_subcomment_post_and_should_return_json_response();

        //user adalah pemilik posts dan pastikan notification listnya null
        $this->user->refresh;
        $this->assertEquals(null , $this->user->notification_list);
    }

    
     /**
     * @group comment-test
     */
    public function test_notification_should_add_when_other_user_comment_other_user_posts() : void
    {
        //buat posts untuk secUser
        $this->makePost(true);

         //payload comment
         $payload = [
            'post_id' => $this->post->id,
            'comment' => 'This is just simple comment oh fuck',
        ];

        //clear cache dulu
        Cache::clear();

        //hit endpoint buat comment
        $response = $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . 'comment' , $payload);
        $response->assertStatus(201);
       
        $this->secUser->refresh();

        //pastikan notifikasi ditambah
        $this->assertNotEmpty($this->secUser->notification_list);

        $notification = json_decode($this->secUser->notification_list , true);
        $this->assertEquals(1 , count($notification));
      
    }

    /**
     * @group comment-test
     */
    public function test_notification_should_add_when_other_user_add_sub_comment_other_user_posts() : void
    {
        $this->test_notification_should_add_when_other_user_comment_other_user_posts();

        //dapatkan id comment
        $comment = Comment::select('id')->first();
        
         //payload comment
         $payload = [
            'comment_id' => $comment->id,
            'sub_comment' => 'This is just simple comment oh fuck',
        ];

        //clear cache dulu
        Cache::clear();

        //hit endpoint buat sub comment
        $response = $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . 'sub-comment' , $payload);
        $response->assertStatus(201);
       
        $this->secUser->refresh();

        //pastikan notifikasi ditambah
        $this->assertNotEmpty($this->secUser->notification_list);

        $notification = json_decode($this->secUser->notification_list , true);
        $this->assertEquals(2 , count($notification));
      
    }

    /**
     * @group comment-test
     */
    public function test_add_notification_can_not_spamming() : void
    {
        $this->test_notification_should_add_when_other_user_comment_other_user_posts();

        $payload = [
            'post_id' => $this->post->id,
            'comment' => 'This is just simple comment oh fuck',
        ];

        //disini tidak menggunakan Cache clear supaya gate/security handle spam notifnya dijalankan
        //Cache::clear();

        //hit endpoint buat comment
        $response = $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . 'comment' , $payload);
        $response->assertStatus(201);
       
        $this->secUser->refresh();

        //pastikan notifikasi tetap 1 menandakan bahwa gate dijalankan
        $this->assertNotEmpty($this->secUser->notification_list);

        $notification = json_decode($this->secUser->notification_list , true);
        $this->assertEquals(1 , count($notification));
      
    }

     /**
     * @group comment-test
     */
    public function test_notification_message_for_thread_should_valid_format() : void
    {
       //buat posts untuk secUser
       $this->makePost(true);

       //payload comment
       $payload = [
          'post_id' => $this->post->id,
          'comment' => 'This is just simple comment oh fuck',
      ];

      //clear cache dulu
      Cache::clear();

      //hit endpoint buat comment
      $response = $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . 'comment' , $payload);
      $response->assertStatus(201);
     
      $this->secUser->refresh();
     
      //memastikan message yang ditambahkan ke notification sesuai
      $this->assertEquals(true, stripos($this->secUser->notification_list , '"accepter_content": null'));
      $this->assertEquals(true, stripos($this->secUser->notification_list , '"message": "mengomentari thread anda"'));
      $this->assertEquals(true, stripos($this->secUser->notification_list , '"sender_username": "' . $this->user->username .'"'));
     
    }


    /**
     * @group comment-test
     */
    public function test_notification_message_for_album_should_valid_format() : void
    {
       //buat posts untuk secUser
       $this->makePost(true , 'album');

       //payload comment
       $payload = [
          'post_id' => $this->post->id,
          'comment' => 'This is just simple comment oh fuck',
      ];

      //clear cache dulu
      Cache::clear();

      //hit endpoint buat comment
      $response = $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . 'comment' , $payload);
      $response->assertStatus(201);
     
      $this->secUser->refresh();
    
      //memastikan message yang ditambahkan ke notification sesuai
      $this->assertEquals(true, stripos($this->secUser->notification_list , '"accepter_content": "http://shalltears-app.test/storage/'));
      $this->assertEquals(true, stripos($this->secUser->notification_list , '"message": "mengomentari album anda"'));
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
