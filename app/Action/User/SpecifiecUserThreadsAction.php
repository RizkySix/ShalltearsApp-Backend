<?php

namespace App\Action\User;

use App\Models\Post;
use App\Models\User;

class SpecifiecUserThreadsAction
{
     /**
     * Action handling data.
     */
    public static function specifiec_user_threads_action(User $user)
    {
        $postsThreads = Post::with(['thread:slug,text,created_at,post_id' , 'likes' => function($likes) {
            $likes->with(['user:username,id'])->select('id' , 'post_id' , 'user_id');
        }])
        ->select('id', 'uuid' , 'user_id' , 'like' , 'comments' , 'created_at' , 'deleted_at')
        ->where('user_id' , $user->id)
        ->has('thread')
        ->latest()
        ->get();

        return $postsThreads;
    }
}