<?php

namespace Tests\Feature\Post;

use App\Models\AlbumPhoto;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AlbumTest extends TestCase
{
    use RefreshDatabase;
    private $user , $secUser;

    private $firstImg , $secImg , $thirdImg;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->secUser = User::factory()->create();
    }

    /**
     * @group post-album
     */
    public function test_store_temp_album_content_should_contain_image(): void
    {
        Storage::fake();
       
        for($i = 0; $i <= 2; $i++){
            //success case
            $image = [
                'album_content' =>  UploadedFile::fake()->create('test.jpg' , 1000)
            ];
            $response = $this->actingAs($this->user)->post(RouteServiceProvider::DOMAIN . 'temp/album' , $image)
                ->assertStatus(200);
            
            $this->assertDatabaseHas('temp_images' , [
                'path' => $response->getContent(), //lokasi content/path nya
            ]);

            //store to properties
            if($i === 0){
                $this->firstImg = $response->getContent();
            }elseif($i === 1){
                $this->secImg = $response->getContent();
            }elseif($i === 2){
                $this->thirdImg = $response->getContent();
            }

            //fail case
            $image['album_content'] = UploadedFile::fake()->create('test.mp4' , 1000);
            $this->actingAs($this->user)->post(RouteServiceProvider::DOMAIN . 'temp/album' , $image)
            ->assertStatus(400);
        }
    }


     /**
     * @group post-album
     */
    public function test_reorder_position_album_content_should_adding_index_image(): void
    {
        $this->test_store_temp_album_content_should_contain_image();
        
        //pastikan dulu bahwa sebelumnya index dari path masih null
        $this->assertDatabaseHas('temp_images' , [
            'index' => null,
        ]);

        $reorderContent = [
            'album_contents' => [
                $this->secImg, //index 0,
                $this->thirdImg, //index 1,
                $this->firstImg, //index 2
            ]
        ];

       $this->actingAs($this->user)->put(RouteServiceProvider::DOMAIN . 'temp/album' , $reorderContent)
            ->assertStatus(200);

        //pastikan data distore ke database
        $this->assertDatabaseHas('temp_images' , [
            'path' => $this->firstImg,
            'index' => 2,
        ]);
        $this->assertDatabaseHas('temp_images' , [
            'path' => $this->secImg,
            'index' => 0,
        ]);
        $this->assertDatabaseHas('temp_images' , [
            'path' => $this->thirdImg,
            'index' => 1,
        ]);

        //pastikan gambar terstore ke storage
        $this->assertEquals(true , Storage::exists($this->firstImg));
        $this->assertEquals(true , Storage::exists($this->secImg));
        $this->assertEquals(true , Storage::exists($this->thirdImg));
    }

     /**
     * @group post-album
     */
    public function test_delete_temp_album_content() : void
    {
        Storage::fake();
        $this->test_store_temp_album_content_should_contain_image();
        
        //pastikan dulu path firstImg ada
        $this->assertDatabaseHas('temp_images' , [
            'path' => $this->firstImg
        ]);

         //pastikan data pada storage masih ada
         $this->assertEquals(false , Storage::missing($this->firstImg));

        $path = [
            'path' => $this->firstImg
        ];

        $this->actingAs($this->user)->delete(RouteServiceProvider::DOMAIN . 'temp/album' , $path)
        ->assertStatus(200);

        //pastikan data firstImg sudah terhapus
        $this->assertDatabaseMissing('temp_images' , [
            'path' => $this->firstImg
        ]);
        
        //pastikan data pada storage kosong
        $this->assertEquals(true , Storage::missing($this->firstImg));

    }

    /**
     * @group post-album
     */
    public function test_store_album_should_move_all_images_from_temp_images_to_album_and_ordered_when_was_reordered() : void
    {
        Storage::fake();
        $this->test_reorder_position_album_content_should_adding_index_image(); //ordered
        
        $content = $this->payload();

        $response = $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . 'album' , $content)
        ->assertStatus(201);
       
        //path ke album
        $firstImgAlbum = str_replace('temp' , $this->user->email , $this->firstImg);
        $secImgAlbum = str_replace('temp' , $this->user->email , $this->secImg);
        $thirdImgAlbum = str_replace('temp' , $this->user->email , $this->thirdImg);
  
        //pastikan database temp_images empty dan dipindah ke database album_photos
        $this->assertDatabaseEmpty('temp_images');
        $this->assertDatabaseHas('album_photos' , [
            'content' => $firstImgAlbum,
            'index' => 2,
            'album_id' => $response['data']['post_id']
        ]);
        $this->assertDatabaseHas('album_photos' , [
            'content' => $secImgAlbum,
            'index' => 0,
            'album_id' => $response['data']['post_id']
        ]);
        $this->assertDatabaseHas('album_photos' , [
            'content' => $thirdImgAlbum,
            'index' => 1,
            'album_id' => $response['data']['post_id']
        ]);

        //pastikan storage temporary kosong dan pindah ke album storage
        $this->assertEquals(true , Storage::missing($this->firstImg));
        $this->assertEquals(true , Storage::missing($this->secImg));
        $this->assertEquals(true , Storage::missing($this->thirdImg));

        $this->assertEquals(true , Storage::exists($firstImgAlbum));
        $this->assertEquals(true , Storage::exists($secImgAlbum));
        $this->assertEquals(true , Storage::exists($thirdImgAlbum)); 
    }


    /**
     * @group post-album
     */
    public function test_store_album_should_move_all_images_from_temp_images_to_album_and_not_ordered_when_without_ordered() : void
    {
        Storage::fake();
        $this->test_store_temp_album_content_should_contain_image(); //no ordered
        
        //pastikan dulu temp_images index nya null
        $this->assertDatabaseHas('temp_images' , [
            'index' => null,
        ]);

        $content = $this->payload();

        $response = $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . 'album' , $content)
        ->assertStatus(201);
       
        //path ke album
        $firstImgAlbum = str_replace('temp' , $this->user->email , $this->firstImg);
        $secImgAlbum = str_replace('temp' , $this->user->email , $this->secImg);
        $thirdImgAlbum = str_replace('temp' , $this->user->email , $this->thirdImg);
  
        //pastikan database temp_images empty dan dipindah ke database album_photos
        $this->assertDatabaseEmpty('temp_images');

        //pastikan urutan gambarnya tidak sama dengan gambar yang direorder dulu sebelum di pindahkan ke album
        $this->assertDatabaseMissing('album_photos' , [
            'content' => $firstImgAlbum,
            'index' => 2,
            'album_id' => $response['data']['post_id']
        ]);
        $this->assertDatabaseMissing('album_photos' , [
            'content' => $secImgAlbum,
            'index' => 0,
            'album_id' => $response['data']['post_id']
        ]);
        $this->assertDatabaseMissing('album_photos' , [
            'content' => $thirdImgAlbum,
            'index' => 1,
            'album_id' => $response['data']['post_id']
        ]);

        //pastikan storage temporary kosong dan pindah ke album storage
        $this->assertEquals(true , Storage::missing($this->firstImg));
        $this->assertEquals(true , Storage::missing($this->secImg));
        $this->assertEquals(true , Storage::missing($this->thirdImg));

        $this->assertEquals(true , Storage::exists($firstImgAlbum));
        $this->assertEquals(true , Storage::exists($secImgAlbum));
        $this->assertEquals(true , Storage::exists($thirdImgAlbum));
        
    }

    /**
     * @group post-album
     */
    public function test_store_album_response_must_be_json() : void
    {
        $this->test_reorder_position_album_content_should_adding_index_image();

        $content = $this->payload();

        $response = $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . 'album' , $content)
        ->assertStatus(201);

        $response->assertJsonStructure([
            "data" => [
                "slug",
                "caption",
                "post_id",
                "created_at",
                "contents" => [
                    "*" => [
                        "id",
                        "content"
                    ]
                ]
            ]
        ]);
    
    }

      /**
     * @group post-album
     */
    public function test_only_can_delete_album_image_when_there_is_one_image_left() : void
    {
        
        Storage::fake();

        $this->test_reorder_position_album_content_should_adding_index_image();

        $content = $this->payload();

        $response = $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . 'album' , $content)
        ->assertStatus(201);
       
        $contents = $response['data']['contents'];
      
        $count = count($contents);
       
        foreach($contents as $content){
            $deleteResponse = $this->actingAs($this->user)
                            ->delete(RouteServiceProvider::DOMAIN . 'album-content/' . $content['id']);

            //buat path untuk cek gambar di storage
            $path = str_replace('http://shalltears-app.test/storage/' , "" , $content['content']);
           
            if($count === 1){
                $deleteResponse->assertStatus(422);
                $this->assertEquals(true , Storage::exists($path));
                $this->assertDatabaseCount('album_photos' , 1);
            }else{
                $deleteResponse->assertStatus(200);
                $this->assertEquals(true , Storage::missing($path));
            }

            $count--;
        }

    }

    /**
     * @group post-album
     */
    public function test_maximal_image_only_5_to_store_on_album() : void
    {
        $this->test_reorder_position_album_content_should_adding_index_image(); 
        
        $content = $this->payload();
        $content['content'][] = 'image4';
        $content['content'][] = 'image5';
        $content['content'][] = 'image6'; //gakboleh sampe 6

        $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . 'album' , $content)
        ->assertStatus(422); //lebih dari 5 error code 422
    }


    
    /**
     * @group post-album
     */
    public function test_update_caption_or_delete_image_only_for_album_owner() : void
    {
        $this->test_store_album_should_move_all_images_from_temp_images_to_album_and_ordered_when_was_reordered();

        //ambil id album_content kedatabase
        $content = AlbumPhoto::select('id' , 'content')->first(); // id album ini milik user pertama
       
        $deleteResponse = $this->actingAs($this->secUser)
                            ->delete(RouteServiceProvider::DOMAIN . 'album-content/' . $content->id);
        $deleteResponse->assertStatus(403);

        //pastikan data masih ada dalam database dan masih tersimpan dalam storage
        $this->assertDatabaseHas('album_photos' , [
            'content' => $content->content,
            'id' => $content->id
        ]);
        $this->assertEquals(true , Storage::exists($content->content));

    }


    private function payload() : array
    {
        return [
            'caption' => 'Caption hereeeee',
            'content' => [
                $this->firstImg,
                $this->secImg,
                $this->thirdImg
            ]
        ];
    }
}
