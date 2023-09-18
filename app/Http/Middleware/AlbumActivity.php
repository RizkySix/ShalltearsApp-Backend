<?php

namespace App\Http\Middleware;

use App\Models\Post;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AlbumActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if($realUser = $request->route('album')){
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

        if($uuid = $request->route('uuid')){
            $findPost = Post::withTrashed()->with('user:id')->where('uuid' , $uuid)->first();
            if($findPost->user->id !== $request->user()->id){
                return response()->json([
                    'status' => false,
                    'message' => 'Forbidden action'
                ] , 403);
            }
        }
        return $next($request);
    }
}
