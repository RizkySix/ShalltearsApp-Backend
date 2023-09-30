<?php

namespace Tests\Feature\Post;

use App\Models\Album;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Trait\Test\TestingTrait;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostFetchingTest extends TestCase
{
    use RefreshDatabase , TestingTrait;
    private $user , $secUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->secUser = User::factory()->create();
    }

    /**
     * @group post-fetch
     */
    public function test_get_posts_should_throwing_json_with_valid_format(): void
    {
       //gunakan 10 data untuk test
       $this->makePost(now() , 10 , $this->user , $this->secUser , 'both' );
       $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'post')->assertStatus(200);
       $response->assertJsonStructure($this->valid_response_format());
    }


   /**
     * @group post-fetch
     */
    public function test_post_without_thread_or_album_with_album_photos_should_not_throw_on_response() :void
    {
       $this->makePost(now() , 10 , $this->user , $this->secUser , 'albumNoImg' );
       $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'post')->assertStatus(200);
       $response->assertJsonStructure([
                    'status',
                    'message',
                    'posts' => [], //posts nya harus null
                ]);
    }


    
     /**
     * @group post-fetch
     */
    public function test_fetch_post_only_throw_10_or_less_post() : void
    {
        $this->makePost(now(), 15 , $this->user , $this->secUser , 'both' );
        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'post');
        $response->assertStatus(200);
        $response->assertJsonCount(10, 'posts');

    }


      /**
     * @group post-fetch
     */
    public function test_retrieve_posts_for_the_last_7_days_when_there_are_10_post() : void
    {
        //15 data yang dibuat 7 hari terakhir
        $this->makePost(now(), 15 , $this->user , $this->secUser , 'both' );

        //15 data yang dibuat lebih dari 7 hari terakhir
        $this->makePost(now()->subDays(8), 15 , $this->user , $this->secUser , 'both' );
        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'post');
        $response->assertStatus(200);
        $response->assertSee(now()->format('Y M d'));
        $response->assertDontSee(now()->subDays(8)->format('Y M d'));
        $response->assertJsonCount(10 , 'posts');
    }


      /**
     * @group post-fetch
     */
    public function test_retrieve_posts_for_the_last_30_days_when_there_are_less_then_10_post_on_last_7_days() : void
    {
        //3 data yang dibuat 7 hari terakhir
        $this->makePost(now(), 3 , $this->user , $this->secUser , 'both' );

        //2 data yang dibuat lebih dari 7 hari terakhir
        $this->makePost(now()->subDays(30), 2 , $this->user , $this->secUser , 'both' );

        //20 data yang dibuat 2 bulan lalu
        $this->makePost(now()->subDays(60), 20 , $this->user , $this->secUser , 'both' );
        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'post');
        $response->assertStatus(200);
        $response->assertSee(now()->format('Y M d'));
        $response->assertSee(now()->subDays(30)->format('Y M d'));
        $response->assertSee(now()->subDays(60)->format('Y M d'));
        $response->assertJsonCount(10 , 'posts');
    }


     /**
     * @group post-fetch
     */
    public function test_retrieve_all_posts_when_there_are_less_than_10_post_on_last_10_or_30_days() : void
    {
        //3 data yang dibuat 7 hari terakhir
        $this->makePost(now(), 3 , $this->user , $this->secUser , 'both' );

        //15 data yang dibuat lebih dari 7 hari terakhir
        $this->makePost(now()->subDays(30), 15 , $this->user , $this->secUser , 'both' );
        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'post');
        $response->assertStatus(200);
        $response->assertSee(now()->format('Y M d'));
        $response->assertSee(now()->subDays(30)->format('Y M d'));
        $response->assertJsonCount(10 , 'posts');
    }


    /**
     * @group post-fetch
     */
    public function test_expand_fetch_posts_should_not_re_fetch_previously_posts() : void
    {
        //asumsikan 5 post dibuat kemarin
        $this->makePost(now()->subDays(1) , 5 , $this->user , $this->secUser , 'both' );

        //lalu 10 post dibuat hari ini
        $this->makePost(now() , 10 , $this->user , $this->secUser , 'both' );
        
        //fetch posts awal
        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'post');
        $response->assertStatus(200);

        //mendapatkan 10 data terbaru
        $response->assertSee(now()->format('Y M d'));
        $response->assertDontSee(now()->subDays(1)->format('Y M d'));
        $response->assertJsonCount(10 , 'posts');

        //fetch posts expand
        //ambil id post urutan terakhir dari fetch posts sebelumnya
        $getIdPost = Post::select('id')->where('created_at' , now())->orderBy('id' , 'ASC')->first();
      
        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'post/expand?post_filter=' . $getIdPost->id);
        $response->assertStatus(200);

        //hanya mengambil 5 post yang belum diambil pada fetch sebelumnya
        $response->assertDontSee(now()->format('Y M d'));
        $response->assertSee(now()->subDays(1)->format('Y M d'));
        $response->assertJsonCount(5 , 'posts');

    }


    /**
     * @group post-fetch
     */
    public function test_archived_album_should_not_throw_when_post_fetched() : void
    {
        //buat 8 posts
        $this->makePost(now(), 8 , $this->user , $this->secUser , 'both' );
        
        //buat 2 post lagi tetapi terarchive
        $this->makePost(now() , 2  , $this->user , $this->secUser , 'archiveAlbum' );

        //fetch posts awal
        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'post');
        $response->assertStatus(200);

        //pastikan hanya 8 post yang terambil karena 2 sisanya terarchive
        $response->assertJsonCount(8 , 'posts');
    }


     /**
     * @group post-fetch
     */
    public function test_fetch_specifiec_album_user_should_not_throw_other_user_album() : void
    {
        //buat posts untuk user1
        $this->makePost(now() , 10 , $this->user , $this->secUser , 'both' );

        //buat posts untuk user2
        $this->makePost(now() , 10 , $this->user , $this->secUser , 'secondUser' );

        //fetch album awal dengan user1 username
        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'user/albums/' . $this->user->username);
        $response->assertStatus(200);

        //pastikan hanya 6 post yang terambil yang berisi album2 dari user1
        $response->assertJsonCount(6 , 'posts');
        $response->assertDontSee($this->secUser->first_name);
        $response->assertSee($this->user->first_name);


        //fetch album awal dengan user2 username
        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'user/albums/' . $this->secUser->username);
        $response->assertStatus(200);

        //pastikan hanya 6 post yang terambil yang berisi album2 dari user2
        $response->assertJsonCount(6 , 'posts');
        $response->assertDontSee($this->user->first_name);
        $response->assertSee($this->secUser->first_name);

    }


     /**
     * @group post-fetch
     */
    public function test_fetch_specifiec_thread_user_should_not_throw_other_user_thread() : void
    {
        //buat posts untuk user1
        $this->makePost(now() , 10 , $this->user , $this->secUser , 'both' );

        //buat posts untuk user2
        $this->makePost(now() , 10 , $this->user , $this->secUser , 'secondUser' );

        //fetch thread awal dengan user1 username
        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'user/threads/' . $this->user->username);
        $response->assertStatus(200);

        //pastikan hanya 6 post yang terambil yang berisi thread2 dari user1
        $response->assertJsonCount(4 , 'posts');
        $response->assertDontSee($this->secUser->first_name);
        $response->assertSee($this->user->first_name);


        //fetch thread awal dengan user2 username
        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'user/threads/' . $this->secUser->username);
        $response->assertStatus(200);

        //pastikan hanya 6 post yang terambil yang berisi thread2 dari user2
        $response->assertJsonCount(4 , 'posts');
        $response->assertDontSee($this->user->first_name);
        $response->assertSee($this->secUser->first_name);

    }

     /**
     * @group post-fetch
     */
    public function test_fetch_specifiec_user_archived_album_only_owner_can_access() : void
    {
        //buat albums yang tidak diarchive sebanyak 10
        $this->makePost(now() , 10 , $this->user , $this->secUser , 'both' );
        
        //buat lagi albums yang diarchive sebanyak 5
        $this->makePost(now() , 10  , $this->user , $this->secUser , 'archiveAlbum' );

        //fetch archive albums awal dengan user1 username
        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'user/albums/archived/' . $this->user->username);
        $response->assertStatus(200);

        //pastikan hanya 6 post yang terambil yang berisi albums2 archive dari user1
        $response->assertJsonCount(6 , 'posts');
        $response->assertSee($this->user->first_name);
        $response->assertSee('archived_at');


         //fetch archive dengan user yang bukan owner case fail
         $response = $this->actingAs($this->secUser)->getJson(RouteServiceProvider::DOMAIN . 'user/albums/archived/' . $this->user->username);
         $response->assertStatus(422);

    }


      /**
     * @group post-fetch
     */
    public function test_single_post_should_throw_valid_response_format() : void
    {
        //buat posts
        $this->makePost(now() , 10 , $this->user , $this->secUser , 'both' );
       
        $post = Post::select('uuid')->first();
        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'post/' . $post->uuid);
        $response->assertStatus(200);
        $response->assertJsonStructure(
            [ 
                "status" , 
                "message" ,
                "like" , 
                "single_post" => [
                  "uuid" ,
                  "total_like" ,
                  "total_comment" , 
                  "created_at" , 
                  "album"  => [
                    "slug" , 
                    "caption" , 
                    "post_id" ,
                    "contents" => [
                        '*' =>  [
                            "id",
                            "content" ,
                          ]
                    ]
                  ]
                ]
              ]
        );
    }


      /**
     * @group post-fetch
     */
    public function test_single_archived_post_should_throw_valid_response_format() : void
    {
        //buat posts
        $this->makePost(now() , 10 , $this->user , $this->secUser , 'archiveAlbum' );
        
        $post = Post::onlyTrashed()->select('uuid')->first();
        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'post/archived/' . $post->uuid);
        $response->assertStatus(200);
        $response->assertJsonStructure(
            [ 
                "status" , 
                "message" ,
                "like" , 
                "single_post" => [
                  "uuid" ,
                  "total_like" ,
                  "total_comment" , 
                  "created_at" ,
                  "archived_at", 
                  "album"  => [
                    "slug" , 
                    "caption" , 
                    "post_id" ,
                    "contents" => [
                        '*' =>  [
                            "id",
                            "content" ,
                          ]
                    ]
                  ]
                ]
              ]
        );
    }

     /**
     * @group post-fetch
     */
    public function test_fetch_posts_likes_should_throw_all_participants() : void
    {   
        //buat posts
        $this->makePost(now() , 10 , $this->user , $this->secUser , 'both' );
        $post = Post::select('id')->first();

        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'post/likes/' . $post->id);
        $response->assertStatus(200);
        $response->assertSee($this->user->username);
        $response->assertSee($this->secUser->username);
        $response->assertJsonCount(2 , 'people_likes');
        $response->assertJson(['total_like' => 2]);
    }


     /**
     * @group post-fetch
     */
    public function test_fetch_posts_comments_should_throw_all_participants() : void
    {   
        //buat posts
        $this->makePost(now() , 10 , $this->user , $this->secUser , 'both' );
        $post = Post::select('id')->first();

        $response = $this->actingAs($this->user)->getJson(RouteServiceProvider::DOMAIN . 'post/comments/' . $post->id);
        $response->assertStatus(200);
        $response->assertSee($this->user->username);
        //$response->assertSee($this->secUser->username);
        $response->assertJsonCount(2 , 'comments_list');
        $response->assertJson(['comments_list' => [
            [
                'total_sub_comments' => 1
            ]
        ]]);
      
    }


     /**
     * custom makePost
     * value status yang dapat dikirim :
     * both,
     * like,
     * albumNoImg,
     * archiveAlbum,
     * secondUser =>
     * status harus berupa string
     */
    /* private function makePost(DateTime $date , int $data , string $status = 'both') :void
    {
       $data > 2 ?: $data += 1;
    
       $posts = Post::factory($data)->create([
            'created_at' => $date,
            'user_id' => $status === 'secondUser' ? $this->secUser->id : $this->user->id,
            'deleted_at' => $status === 'archiveAlbum' ? now() : null
        ]);
    
        
        //both
       if($status !== 'albumNoImg'){
            $mod = count($posts) / 2;
           
            for($i = 0; $i < count($posts); $i++){
                if($i <= intval(floor($mod))){
                   $this->makeAlbum($posts[$i] , true);

                }else{
                    $this->makeThread($posts[$i]);
                }

            }
        //albumNoImg
        }elseif($status === 'albumNoImg'){
            foreach($posts as $post){
                $this->makeAlbum($post);
            }
        }
    }


    private function makeAlbum(object $post, bool $withImg = false) : void
    {
        $album = Album::factory()->create([
            'post_id' => $post->id,
        ]);
        
        if($withImg){
            $album->album_photos()->create([
                'content' => 'this should be image path, but this is just a test',
                'index' => rand(0,4)
            ]);
        }

        $this->makeLike($post);
        $this->makeComment($post);
        
    }


    private function makeThread(object $post) : void
    {
        Thread::factory()->create([
            'post_id' => $post->id
        ]);

        $this->makeLike($post);
        $this->makeComment($post);
    }


    private function makeLike(Post $post) : void
    {
        $post->likes()->create(['user_id' => $this->user->id]);
        $post->likes()->create(['user_id' => $this->secUser->id]);
    }

    private function makeComment(Post $post , int $counter = 1) : void
    {
        if($counter <= 2){
            
            //buat comment
            $comment = Comment::create([
                'post_id' => $post->id,
                'user_id' => $counter < 2 ? $this->user->id : $this->secUser->id,
                'comment' => 'This just comment'
            ]);

            //buat sub comment
            $comment->sub_comments()->create([
                'user_id' => $counter < 2 ? $this->user->id : $this->secUser->id,
                'sub_comment' => 'Nothing to lose'
            ]);

            $counter += 1;
            
            //rekursiv
            $this->makeComment($post , $counter);

        }
    } */


    /**
     * response format
     */
    private function valid_response_format() : array
    {
       return  [
            "status",
            "message",
            "posts" => [
            [
                "uuid",
                "total_like",
                "liker",
                "total_comment",
                "created_at",
                "album" => [
                    "slug",
                    "caption",
                    "post_id",
                    "contents" => [
                        [
                            "id",
                            "content"
                        ]
                    ]
                ],
                "user" => [
                    "first_name",
                    "last_name",
                    "username",
                    "foto_profile"
                ]
            ],
            [
                "uuid",
                "total_like",
                "liker",
                "total_comment",
                "created_at",
                "album" => [
                    "slug",
                    "caption",
                    "post_id",
                    "contents" => [
                        [
                            "id",
                            "content"
                        ]
                    ]
                ],
                "user" => [
                    "first_name",
                    "last_name",
                    "username",
                    "foto_profile"
                ]
            ],
            [
                "uuid",
                "total_like",
                "liker",
                "total_comment",
                "created_at",
                "album" => [
                    "slug",
                    "caption",
                    "post_id",
                    "contents" => [
                        [
                            "id",
                            "content"
                        ]
                    ]
                ],
                "user" => [
                    "first_name",
                    "last_name",
                    "username",
                    "foto_profile"
                ]
            ],
            [
                "uuid",
                "total_like",
                "liker",
                "total_comment",
                "created_at",
                "album" => [
                    "slug",
                    "caption",
                    "post_id",
                    "contents" => [
                        [
                            "id",
                            "content"
                        ]
                    ]
                ],
                "user" => [
                    "first_name",
                    "last_name",
                    "username",
                    "foto_profile"
                ]
            ],
            [
                "uuid",
                "total_like",
                "liker",
                "total_comment",
                "created_at",
                "album" => [
                    "slug",
                    "caption",
                    "post_id",
                    "contents" => [
                        [
                            "id",
                            "content"
                        ]
                    ]
                ],
                "user" => [
                    "first_name",
                    "last_name",
                    "username",
                    "foto_profile"
                ]
            ],
            [
                "uuid",
                "total_like",
                "liker",
                "total_comment",
                "created_at",
                "album" => [
                    "slug",
                    "caption",
                    "post_id",
                    "contents" => [
                        [
                            "id",
                            "content"
                        ]
                    ]
                ],
                "user" => [
                    "first_name",
                    "last_name",
                    "username",
                    "foto_profile"
                ]
            ],
            [
                "uuid",
                "total_like",
                "liker",
                "total_comment",
                "created_at",
                "thread" => [
                    "slug",
                    "text",
                    "post_id"
                ],
                "user" => [
                    "first_name",
                    "last_name",
                    "username",
                    "foto_profile"
                ]
            ],
            [
                "uuid",
                "total_like",
                "liker",
                "total_comment",
                "created_at",
                "thread" => [
                    "slug",
                    "text",
                    "post_id"
                ],
                "user" => [
                    "first_name",
                    "last_name",
                    "username",
                    "foto_profile"
                ]
            ],
            [
                "uuid",
                "total_like",
                "liker",
                "total_comment",
                "created_at",
                "thread" => [
                    "slug",
                    "text",
                    "post_id"
                ],
                "user" => [
                    "first_name",
                    "last_name",
                    "username",
                    "foto_profile"
                ]
            ],
            [
                "uuid",
                "total_like",
                "liker",
                "total_comment",
                "created_at",
                "thread" => [
                    "slug",
                    "text",
                    "post_id"
                ],
                "user" => [
                    "first_name",
                    "last_name",
                    "username",
                    "foto_profile"
                ]
            ]
        ]
       ];
    }


}
