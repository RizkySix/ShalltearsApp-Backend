<?php

namespace App\Http\Controllers\Authentication;

use App\Action\Authentication\ChangePasswordAction;
use App\Action\Authentication\ResetPasswordAction;
use App\Action\Authentication\StoreResetPasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Authentication\ChangePasswordRequest;
use App\Http\Requests\Authentication\ResetPasswordRequest;
use App\Http\Requests\Authentication\StoreResetPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ResetPasswordController extends Controller
{
    /**
     * Handle an incoming reset password request.
     */
    public function reset_password(ResetPasswordRequest $request) : JsonResponse
    {
        $validatedData = $request->validated();

        $actionResponse = ResetPasswordAction::reset_password_action($validatedData['email']);

        if($actionResponse == true){
            return response()->json([
                'status' => true,
                'message' => 'New password send to your mail',
            ] , 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'We cant find that email',
        ] , 400);
    }


    
     /**
     * Handle an incoming store reset password request.
     */
    public function store_reset_password(string $reset_password_token , string $email) : JsonResponse
    {
        $actionResponse = StoreResetPasswordAction::store_reset_password_action($email);

        if($actionResponse == true){
            return response()->json([
                'status' => true,
                'message' => 'Password reset success',
            ] , 200);
        }
        
        return response()->json([
            'status' => false,
            'message' => 'Failed reset password, please try again to make reset request',
        ] , 422);
    }


     /**
     * Handle an incoming change password request.
     */
    public function change_password(ChangePasswordRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $validatedData['user_id'] = $request->user()->id;

        $actionResponse = ChangePasswordAction::change_password($validatedData);

        if($actionResponse == true){
            return response()->json([
                'status' => true,
                'message' => 'New password updated',
            ] , 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Current password not match',
        ] , 422);
    }
}
