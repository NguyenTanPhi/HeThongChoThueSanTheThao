<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Role
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth('sanctum')->check()) {
            return response()->json(['message' => 'Chưa đăng nhập'], 401);
        }

        $user = auth('sanctum')->user();

        if (!in_array($user->role, $roles)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập'], 403);
        }

        return $next($request);
    }
}