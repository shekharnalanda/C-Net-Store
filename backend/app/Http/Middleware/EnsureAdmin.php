<?php

namespace App\Http\Middleware;

use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) return redirect()->route('admin.login');
        abort_unless(in_array($request->user()->role, [UserRole::SuperAdmin, UserRole::Staff], true) && $request->user()->status === ApprovalStatus::Approved, 403);
        return $next($request);
    }
}

