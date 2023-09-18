<?php


namespace App\Action\Comment;

use App\Models\Post;
use App\Models\SubComment;

class SubCommentDeleteAction
{
    /**
     * Action handling data.
     */
    public static function sub_comment_delete_action(SubComment $subComment) : bool
    {
       $subComment->comment->post->comments =  $subComment->comment->post->comments - 1;
       $subComment->comment->post->save();
    
       $subComment->delete();
       return true;
    }
}