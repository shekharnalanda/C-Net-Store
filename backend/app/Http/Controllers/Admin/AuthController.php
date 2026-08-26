<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View { return view('admin.auth.login'); }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        if (! Auth::attempt($credentials, $request->boolean('remember'))) return back()->withErrors(['email' => 'Invalid administrator credentials.'])->onlyInput('email');
        $request->session()->regenerate();
        $user = $request->user();
        if (! in_array($user->role, [UserRole::SuperAdmin, UserRole::Staff], true) || $user->status !== ApprovalStatus::Approved) { Auth::logout(); return back()->withErrors(['email' => 'This account cannot access administration.']); }
        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}

