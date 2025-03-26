<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckUserType
{
    public function handle(Request $request, Closure $next, string $userType): Response
    {
        if (Auth::check() && Auth::user()->userType->name === $userType) {
            return $next($request);
        }

        abort(403, 'Unauthorized.');
    }
}
