<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{


        public function handle($request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
           return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();

        if ($user->status !== 'active') {
              return response()->json(['message' => 'حسابك قيد المراجعة'], 403);
         }

        if (!in_array($user->role, $roles)) {
         return response()->json(['message' => 'غير مصرح لك بالدخول'], 403);

         }

        return $next($request);
    }
}