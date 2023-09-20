<?php

namespace App\Http\Middleware;

use App\Models\AlbumPhoto;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MinimumContentAlbum
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $content = $request->route('content');
        
        $contents = AlbumPhoto::where('album_id' , $content->album_id);
        $countOtherContent = $contents->count();

        $getOwner = $contents->first();
        $getOwner = $getOwner->album->post->user_id;

        if($countOtherContent < 2){
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete , at least 1 content left'
            ] , 422); 
        }

        if($getOwner !== $request->user()->id){
            return response()->json([
                'status' => false,
                'message' => 'Forbidden action you not allowed to do this action'
            ] , 403); 
        }
        
        return $next($request);
    }
}
