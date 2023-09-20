<?php

namespace App\Http\Controllers\Authentication;

use App\Action\Authentication\OtpDirectVerifyAction;
use App\Action\Authentication\OtpSendAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Authentication\OtpCodeRequest;
use App\Models\User;
use App\Observers\RegisterObserver;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class OtpController extends Controller
{

     /**
    * Direct otp
    */
    public function direct_verify(Request $request)
    {
       
        $actionResponse = OtpDirectVerifyAction::otp_direct_verify_action($request->hash_id , $request->otp_code);

        if($actionResponse == true){
            return redirect()->away('http://localhost:5173/auth/verification/success');
        }else{
            return redirect()->away('http://localhost:5173/auth/verification/success?fail=true');
        }
    }


    /**
    * send otp 
    */
    public function send_otp(OtpCodeRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            $actionResponse = OtpSendAction::send_otp_action($request->user()->id , $validatedData['otp_code']);

            if($actionResponse == true){
                return response()->json([
                    'status' => true,
                    'message' => 'Email succes verified'
                ] , 200);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Email failed to verified'
                ] , 400);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ] , 400);
        }


    }

    /**
    * resend otp 
    */
    public function resend_otp(Request $request): JsonResponse
    {
        $observer = new RegisterObserver;
        $observer->created($request->user());

        return response()->json([
            'status' => true,
            'message' => 'Otp resend success'
        ]);
    }

}
