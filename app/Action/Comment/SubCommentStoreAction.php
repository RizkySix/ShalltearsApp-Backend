<?php


namespace App\Action\Comment;

use App\Models\SubComment;
use App\Trait\NotificationInsertTrait;

class SubCommentStoreAction
{   
    //use NotificationInsertTrait;

    /**
     * Action handling data.
     */
    public static function sub_comment_store_action(array $data) : SubComment
    {
        $user = auth()->user();
        $subComment = $user->sub_comments()->create([
            'comment_id' => $data['comment_id'],
            'sub_comment' => $data['sub_comment']
        ]);

        $subComment->load(['comment.post:id,comments,user_id' , 'comment.post.user']);

        if(isset($subComment->comment->post)){
           $currentComment = $subComment->comment->post->comments + 1;
           $subComment->comment->post()->update(['comments' => $currentComment]);
           $subComment->current_comment = $currentComment; //tambahkan dinamic propertis
        }

         //add notification
         $post = $subComment->comment->post;
         NotificationInsertTrait::notification($post , 'mengomentari');
         
        return $subComment;
    }
}