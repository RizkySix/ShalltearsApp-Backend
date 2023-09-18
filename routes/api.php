<?php

use App\Http\Controllers\Authentication\AuthenticatedController;
use App\Http\Controllers\Authentication\OtpController;
use App\Http\Controllers\Authentication\RegisterController;
use App\Http\Controllers\Authentication\ResetPasswordController;
use App\Http\Controllers\Comment\CommentController;
use App\Http\Controllers\Comment\SubCommentController;
use App\Http\Controllers\Emergency\EmergencyShalltearMailController;
use App\Http\Controllers\Like\LikeController;
use App\Http\Controllers\Post\AlbumController;
use App\Http\Controllers\Post\AlbumPhotoController;
use App\Http\Controllers\Post\PostController;
use App\Http\Controllers\Post\ThreadController;
use App\Http\Controllers\Profile\UserProfileController;
use App\Http\Controllers\Temporary\AddIndexAfterUploadAlbumController;
use App\Http\Controllers\Temporary\DeleteTempAlbumImageController;
use App\Http\Controllers\Temporary\TempAlbumImageUploadController;
use App\Http\Controllers\User\FindUserController;
use App\Http\Controllers\User\NotificationUpdateController;
use App\Http\Controllers\User\PersonalProfileUserController;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


    Route::post('/register' , [RegisterController::class , 'store'])->name('register.store');
    Route::get('/verify-otp/{hash_id}/{otp_code}' , [OtpController::class , 'direct_verify'])->name('otp.direct.verify');
    Route::post('/login' , [AuthenticatedController::class , 'login'])->middleware('throttle:user.login')->name('user.login');
    Route::post('/reset-password' , [ResetPasswordController::class , 'reset_password'])->middleware('throttle:mail.sender')->name('reset.password');
    Route::post('/reset-password/{reset_password_token}/{email}' , [ResetPasswordController::class , 'store_reset_password'])->middleware('reset.password')->name('reset.password.store');

Route::middleware(['auth:sanctum'])->group(function() {
    Route::get('/personal-user' , [PersonalProfileUserController::class , 'authenticated_user'])->name('authenticated.user');
    Route::post('/logout' , [AuthenticatedController::class , 'logout'])->name('user.logout');

    Route::middleware(['un.verified'])->group(function() { 
        Route::post('/resend-otp' , [OtpController::class , 'resend_otp'])->middleware('throttle:mail.sender')->name('resend.otp');
        Route::post('/verify-otp' , [OtpController::class , 'send_otp'])->name('send.otp');
    });


    //middleware verified user
    Route::middleware(['is.verified'])->group(function() {
        Route::put('/reset-password' , [ResetPasswordController::class , 'change_password'])->name('change.password');

        Route::put('/profile' , [UserProfileController::class  , 'update_profile'])->name('update.profile');
        Route::put('/profile/avatar' , [UserProfileController::class  , 'avatar_update'])->name('update.avatar');

        //thread endpoints
        Route::post('/thread' , [ThreadController::class  , 'store_thread'])->name('make.thread');
      
        Route::middleware(['activity.thread'])->group(function() {
            Route::put('/thread/{thread}' , [ThreadController::class  , 'update_thread'])->name('update.thread');
            Route::post('/thread/{post}/force-delete' , [ThreadController::class  , 'force_delete'])->name('force.delete.thread');
        });
    

        //comment and like endpoints
        Route::post('/comment' , [CommentController::class , 'store_comment'])->name('store.comment');
        Route::delete('/comment/{comment}' , [CommentController::class , 'delete_comment'])->name('delete.comment');
    
        Route::put('/like/{post}' , [LikeController::class  , 'like_or_dislike'])->name('like.or.dislike');
    
        Route::post('/sub-comment' , [SubCommentController::class , 'store_sub_comment'])->name('store.sub.comment');
        Route::delete('/sub-comment/{subComment}' , [SubCommentController::class , 'delete_sub_comment'])->name('delete.sub.comment');
    

        //album endpoints
        Route::post('/album' , [AlbumController::class , 'store_album'])->middleware('album.capacity')->name('make.album');

        Route::middleware(['activity.album'])->group(function() {
            Route::get('/album/{album}' , [AlbumController::class  , 'get_contents_album'])->name('get.contents.album');
            Route::put('/album/{album}' , [AlbumController::class , 'update_album'])->name('update.album');
            Route::delete('/album/{post}' , [AlbumController::class , 'archive_album'])->name('archive.album');
            Route::post('/album/{uuid}/restore' , [AlbumController::class , 'restore_album'])->name('restore.album');
            Route::post('/album/{uuid}/force-delete' , [AlbumController::class , 'force_delete_album'])->name('force.delete.album');
        });
    
        Route::delete('/album-content/{content}' , [AlbumPhotoController::class , 'delete_album_photo'])->middleware('minimum.content')->name('delete.album.content');
    

        //post endpoints
        Route::get('/post' , [PostController::class , 'throw_post'])->name('all.posts');
        Route::get('/post/expand' , [PostController::class , 'throw_expand_post'])->name('expand.posts');

        Route::get('/post/{post}' , [PostController::class , 'throw_single_post'])->name('single.post');
        Route::get('/post/archived/{uuid}' , [PostController::class , 'throw_single_archived_post'])->middleware('activity.album')->name('archived.single.post');
        Route::get('/post/comments/{postId}' , [PostController::class , 'throw_post_comments'])->name('all.post.comments');
        Route::get('/post/likes/{postId}' , [PostController::class , 'throw_post_likes'])->name('all.post.likes');
    

        //specifiec user endpoints
        Route::get('/find-user' , [FindUserController::class , 'find_user'])->name('find.user');
    
        Route::get('/user/profile/{user}' , [PersonalProfileUserController::class , 'specifiec_user'])->name('specifiec.user');
        Route::get('/user/account' , [PersonalProfileUserController::class , 'specifiec_email_and_created_at'])->name('specifiec.user.account');
        Route::get('/user/albums/{user}' , [PersonalProfileUserController::class , 'specifiec_user_albums'])->name('specifiec.user.albums');
        Route::get('/user/threads/{user}' , [PersonalProfileUserController::class , 'specifiec_user_threads'])->name('specifiec.user.threads');

        Route::get('/user/albums/archived/{user}' , 
        [PersonalProfileUserController::class , 'specifiec_user_archived_albums'])->middleware('archived.album')->name('specifiec.archived.user.albums');

        /* Temporary image */
        Route::post('/temp/album' , TempAlbumImageUploadController::class)->name('temp.upload');
        Route::put('/temp/album' , AddIndexAfterUploadAlbumController::class)->name('temp.new.order');
        Route::delete('/temp/album' , DeleteTempAlbumImageController::class)->name('delete.temp');

        Route::get('/login-user' , [FindUserController::class , 'user_login'])->name('user.login.data');

        //notification
        Route::put('/notification/readed' , [ NotificationUpdateController::class , 'readed_notif'])->middleware('notification.read')->name('readed.notif');
        Route::put('/notification/readed/clear' , [ NotificationUpdateController::class , 'clear_notif'])->name('clear.notif');

        //emergency mail shalltear
        Route::post('/emergency-mail' , [EmergencyShalltearMailController::class , 'send_mail'])->middleware('emergency.call')->name('emergency.mail');
        Route::get('/emergency-mail/limit' , [EmergencyShalltearMailController::class , 'check_limit'])->name('emergency.limit');
    });
 

});
