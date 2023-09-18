<?php

namespace App\Http\Controllers\Emergency;

use App\Http\Controllers\Controller;
use App\Http\Requests\Emergency\EmergecyCallRequest;
use App\Mail\EmergencyShalltearMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class EmergencyShalltearMailController extends Controller
{
      /**
     * Handle an send emergency call message.
     */
    public function send_mail(EmergecyCallRequest $request) : JsonResponse
    {   
        $validatedData = $request->validated();
        $validatedData['sender'] = $request->user()->first_name;
        $users = User::select('email' , 'first_name')->where('email_verified_at' , '!=' , null)->get();

        foreach($users as $user){
            $validatedData['first_name'] = $user->first_name;
            Mail::to($user->email)->send(new EmergencyShalltearMail($validatedData));
        }

        return response()->json([
            'status' => true,
            'message' => 'Emergency call sent'
        ] , 202); //onprocess
    }


     /**
     * Handle an send emergency call check limit a day.
     */
    public function check_limit() : JsonResponse
    {
        $cache = Cache::get('emergency-call-' . auth()->user()->id);
        $responseMsg = 'You have access';
        $statusCode = 200;
        if($cache){
            $responseMsg = Carbon::parse($cache)->diffInHours();
            $statusCode = 422;
        }

        return response()->json([
            'status' => $statusCode == 200 ? true : false,
            'message' => $responseMsg
        ], $statusCode); 
    }
}
