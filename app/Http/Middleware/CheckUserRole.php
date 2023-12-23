<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // this line set the header to application/json to force the response to be json, to propperly handle the response and errors in the frontend
        if($request->user()->hasRole('admin')) return $next($request);
        else return \response(['message'=>'no permission to access this route'],400);
    }
}
