<?php


namespace App\Action\Comment;

use App\Models\Comment;

class CommentDeleteAction
{
    /**
     * Action handling data.
     */
    public static function comment_delete_action(Comment $comment) : bool
    {
       $subComment = $comment->sub_comments();
       $newCommentTotal = $subComment->count() + 1;
       $comment->post->comments = $comment->post->comments - $newCommentTotal;
       $comment->post->save();

       $subComment->delete();
       $comment->delete();
       return true;
    }
}