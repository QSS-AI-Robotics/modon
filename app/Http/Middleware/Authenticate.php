<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Redirect unauthorized users to the signin page (/).
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            return route('signin.form'); // ✅ Redirect to "/" instead of "login"
        }
    }
}
