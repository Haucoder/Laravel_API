<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class checkAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    { 
        if(!$request->user()){
            return response()->json([
                'message'=>"ban chua dang nhap"
            ],401);
        }
        if($request->user()->role !=='admin'){
            return response()->json([
            "status"=>false,
            "message"=>"ban khong co quyen truy cap"
            ],403);
        }
        return $next($request);
    }
}
