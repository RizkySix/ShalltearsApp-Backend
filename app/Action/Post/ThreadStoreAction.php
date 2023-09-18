<?php


namespace App\Action\Post;

use App\Models\Thread;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;

class ThreadStoreAction
{
      /**
     * Action handling data.
     */
    public static function store_thread_action(array $data) : Thread
    {
        //buat post
        $hashedNum = Hashids::encode(mt_rand(111,999));
        $uuid = Str::random(8) . $hashedNum;

        $post = auth()->user()->posts()->create([
            'uuid' => $uuid
        ]);

        //buat threadnya
        $hashedPostId = Hashids::encode($post->id);
        $slug = Str::random(8) . $hashedPostId;

        $thread = $post->thread()->create([
            'slug' => $slug,
            'text' => $data['text']
        ]);

        return $thread;

    }
}