<?php


namespace App\Action\Comment;

use App\Models\Comment;
use App\Trait\NotificationInsertTrait;

class CommentStoreAction
{
    //use NotificationInsertTrait;
    /**
     * Action handling data.
     */
    public static function comment_store_action(array $data) : Comment
    {
        $user = auth()->user();
        $comment = $user->comments()->create([
            'post_id' => $data['post_id'],
            'comment' => $data['comment']
        ]);
        
        $currentComment = $comment->post->comments + 1;
        $comment->post()->update(['comments' => $currentComment]);
        $comment->current_comment = $currentComment;

        //add notification
        $post = $comment->post;
        NotificationInsertTrait::notification($post , 'mengomentari');
        return $comment;
    }
}