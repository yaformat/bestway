<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EnsureTokenIsValid
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('sanctum')->check()) {
            if ($user = Auth::user()) {
                $user->preventSavePhotos = true;
                $user->update(['activity_at' => Carbon::now()]);
                $user->preventSavePhotos = false;
            }
            return $next($request);
        }

        return ApiResponse::error('Ошибка авторизации', 401);
    }
}