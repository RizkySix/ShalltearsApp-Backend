<?php

namespace App\Trait\Test;

use App\Models\Album;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;
use DateTime;

trait TestingTrait
{
    private $user , $secUser;

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
    protected function makePost(DateTime $date , int $data , User $user1 , User $user2 , string $status = 'both' ) :void
    {
       $this->user = $user1;
       $this->secUser = $user2;

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


    protected function makeAlbum(object $post, bool $withImg = false , bool $withLikeAndComment = true) : void
    {
        $album = Album::factory()->create([
            'post_id' => $post->id,
        ]);
        
        if($withImg){
            $album->album_photos()->create([
                'content' => 'this should be image path, but this is just a test',
                'index' => 0
            ]);
        }

        if($withLikeAndComment){
            $this->makeLike($post);
            $this->makeComment($post);
        }
        
    }


    protected function makeThread(object $post, bool $withLikeAndComment = true) : void
    {
        Thread::factory()->create([
            'post_id' => $post->id
        ]);

        if($withLikeAndComment){
            $this->makeLike($post);
            $this->makeComment($post);
        }
    }


    protected function makeLike(Post $post) : void
    {
        $post->likes()->create(['user_id' => $this->user->id]);
        $post->likes()->create(['user_id' => $this->secUser->id]);
    }

    protected function makeComment(Post $post , int $counter = 1) : void
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
    }
}