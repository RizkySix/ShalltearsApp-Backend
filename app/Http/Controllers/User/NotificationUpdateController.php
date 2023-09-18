<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\NotificationRequest;
use Illuminate\Http\Request;

class NotificationUpdateController extends Controller
{
     /**
     * Handle an incoming notification readed update user request.
     */
    public function readed_notif(NotificationRequest $request) : void
    {
        $validatedData = $request->validated();
        $request->user()->update(['readed_notification' => $validatedData['readed_notif']]);

    }


     /**
     * Handle an incoming notification readed update user request.
     */
    public function clear_notif() : void
    {
       $user = auth()->user();
        $user->update(['notification_list' => null , 'readed_notification' => 0]);

    }
}
