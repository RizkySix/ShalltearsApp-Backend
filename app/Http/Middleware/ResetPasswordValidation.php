<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResetPasswordValidation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = User::select('reset_password_token')->where('email' , $request->email)->first();

        if($user->reset_password_token != $request->reset_password_token){
            return response()->json([
                'status' => false,
                'message' => 'Token not valid',
            ] , 404);
        }

        return $next($request);
    }
}
