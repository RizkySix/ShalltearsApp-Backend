<?php

namespace App\Http\Controllers\Authentication;

use App\Action\Authentication\RegisterAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Authentication\RegisterRequest;
use Exception;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{

     /**
     * Handle an incoming registration request.
     */
    public function store(RegisterRequest $request) : JsonResponse
    {
        $validatedData = $request->validated();
        $actionResponse = RegisterAction::registration_action($validatedData);
        
        if(!$actionResponse){
            return response()->json([
                'status' => false,
                'message' => 'Failed Registration',
            ] , 400);
        }

        return response()->json([
            'status' => true,
            'message' => 'Success Registration',
            'token' => $actionResponse
        ] , 201);

    }
}
