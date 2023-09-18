<?php

namespace App\Http\Controllers\Authentication;

use App\Action\Authentication\LoginAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Authentication\LoginRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class AuthenticatedController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('user_mail' , 'password');
      
       $actionResponse = LoginAction::login_action($credentials);
        
       if($actionResponse != false){
            return response()->json([
                'status' => true,
                'message' => 'Login Success',
                'token' => $actionResponse,
                'verified' => $request->user()->email_verified_at == null ? false : true
            ] , 200);
       }

        return response()->json([
            'status' => false,
            'message' => 'Credentials not match'
        ] , 400);
    }


    /**
     * Handle an incoming logout request.
     */
    public function logout(Request $request)
    {
        //hapus data login
        $loginUsers = Cache::get('isLoginUser');
        if($loginUsers){
            unset($loginUsers[$request->user()->id]);
            Cache::put('isLoginUser' , $loginUsers);
        }

        //revoking all tokens
        $request->user()->tokens()->delete();
        
        return response()->json([
            'status' => true,
            'message' => 'Logout success'
        ] , 200);
    }
}
