<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckUserType
{
    public function handle($request, Closure $next, $type)
    {
        $user = Auth::user();

        if (!$user || $user->userType?->name !== $type) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
