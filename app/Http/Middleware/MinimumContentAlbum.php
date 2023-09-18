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
        
        $countOtherContent = AlbumPhoto::where('album_id' , $content->album_id)->count();

        if($countOtherContent < 2){
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete , at least 1 content left'
            ] , 422); 
        }
        return $next($request);
    }
}
