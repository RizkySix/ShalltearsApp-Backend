<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class EmergencyCall
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $cache = Cache::get('emergency-call-' . $request->user()->id);
        if($cache){
            return response()->json([
                'status' => false,
                'message' => 'Anda sudah memakai limit hari ini'
            ], 422);
        }

        Cache::add('emergency-call-' . $request->user()->id , now()->addHours(24) , now()->addHours(24));
        
        return $next($request);
    }
}
