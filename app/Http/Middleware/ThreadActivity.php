<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ThreadActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if($realUser = $request->route('thread')){
            if($realUser->post->user->id !== $request->user()->id){
                return response()->json([
                    'status' => false,
                    'message' => 'Forbidden action'
                ] , 403);
            }
        }


        if($realUser = $request->route('post')){
            if($realUser->user->id !== $request->user()->id){
                return response()->json([
                    'status' => false,
                    'message' => 'Forbidden action'
                ] , 403);
            }
        }
        
        return $next($request);
    }
}
