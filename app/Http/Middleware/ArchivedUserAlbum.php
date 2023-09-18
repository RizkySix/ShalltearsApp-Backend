<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ArchivedUserAlbum
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $incomingUserRequest = $request->route('user');
        if($incomingUserRequest->id != $request->user()->id){
            return response()->json([
                'status' => false,
                'message' => 'You cant access that album user',
            ] , 422);
        } 
        return $next($request);
    }
}
