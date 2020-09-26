<?php

namespace App\Http\Middleware;

use Closure;
use \Tymon\JWTAuth\Facades\JWTAuth;

class JWTVerify
{
    public function handle($request, Closure $next)
    {        
        $token= str_replace('Bearer ', "" , $request->header('Authorization'));

        try { 
           JWTAuth::setToken($token); //<-- set token and check
            if (!JWTAuth::getPayload()) {
                return response()->json(array('message'=>'user_not_found'), 404);
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(array('message'=>'token_expired'), 404);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(array('message'=>'token_invalid'), 404);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(array('message'=>'token_absent'), 404);
        } 

        return $next($request);
    }
}
