<?php
namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use JWTAuthException;
use App\Company_customer;
class jwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    { 
        try {
            $user = JWTAuth::setToken($request->header('X-Auth-Token'))->toUser();
            if(!$user){
                return response()->json(['status'=>'TokenError','msg'=>'Token does not exist']);
            }
            $request->user = $user;
           
        } catch (Exception $e) {
            return response()->json(['status'=>'TokenError','msg'=>'Token is Invalid']);
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                //return $next($request);
                return response()->json(['status'=>'TokenError','msg'=>'Token is Invalid'], 401);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                 //return $next($request);
                return response()->json(['status'=>'TokenError','msg'=>'Token is Expired'], 401);
            }else{
                 //return $next($request);
                return response()->json(['status'=>'TokenError','msg'=>'Something is wrong'], 401);
            }
        }
        return $next($request);
    }
}