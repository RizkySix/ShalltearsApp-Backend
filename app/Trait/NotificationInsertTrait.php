<?php

namespace App\Trait;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

trait NotificationInsertTrait 
{
  
    public static function notification(Post $post , string $msg)
    {
        if(! Gate::allows('handle-spam-notif' , [$post , $msg])){
            return false;
        }

        //add notification
        $user = auth()->user();
        if($user->username !== $post->user->username){
            $notification = $post->user->notification_list;
            $notification = json_decode($notification , true);
           
            if(isset($post->album->album_photos)){
                $accepterContent = $post->album->album_photos()->select('content')->where('index' , 0)->first();
                $accepterContent = asset('storage/' . $accepterContent['content']);
            }else{
                $accepterContent = null;
            }
    
            $newNotification = [
                'sender_username' => $user->username,
                'sender_foto_profile' => $user->foto_profile ? asset('storage/' . $user->foto_profile) : null,
                'accepter_content' => $accepterContent,
                'message' => $accepterContent !== null ? $msg . " album anda" : $msg . " thread anda" 
            ];
    
            $notification[] = $newNotification;
            $notification = json_encode($notification);
            $post->user->notification_list = $notification;
            $post->user->save();

            Cache::remember($user->id . $post->uuid . $msg , now()->addMinutes(5) , function() {
                return true;
            });
        }
    }
}