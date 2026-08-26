<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'email' => ['nullable', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in([UserRole::Customer->value, UserRole::Seller->value, UserRole::DeliveryPartner->value])],
            'preferred_language' => ['nullable', Rule::in(['en', 'hi'])],
        ]);

        $role = UserRole::from($data['role']);
        $user = User::create([
            ...$data,
            'status' => $role === UserRole::Customer ? ApprovalStatus::Approved : ApprovalStatus::Pending,
        ]);

        return response()->json([
            'message' => $role === UserRole::Customer ? 'Registration successful.' : 'Registration submitted for approval.',
            'user' => $user,
            'token' => $user->createToken($request->userAgent() ?: 'mobile-app')->plainTextToken,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $user = User::query()
            ->where('phone', $data['login'])
            ->orWhere('email', $data['login'])
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['login' => ['The supplied credentials are incorrect.']]);
        }

        if ($user->status === ApprovalStatus::Suspended || $user->status === ApprovalStatus::Rejected) {
            return response()->json(['message' => 'This account is not permitted to sign in.'], 403);
        }

        return response()->json([
            'user' => $user,
            'token' => $user->createToken($data['device_name'] ?? 'mobile-app')->plainTextToken,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }
}

