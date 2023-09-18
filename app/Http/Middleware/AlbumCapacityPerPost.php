<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AlbumCapacityPerPost
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if(count($request->content) > 5){
            return response()->json([
                'status' => false,
                'message' => 'One album only handle 5 content',
            ] , 422);
        }
        return $next($request);
    }
}
