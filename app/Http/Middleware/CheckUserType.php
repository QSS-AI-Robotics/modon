<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckUserType
{
    public function handle(Request $request, Closure $next, ...$types): Response
    {
        if (Auth::check()) {
            $userType = strtolower(Auth::user()->userType->name ?? '');
            $allowedTypes = array_map('strtolower', $types);

            if (in_array($userType, $allowedTypes)) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized.');
    }
}

