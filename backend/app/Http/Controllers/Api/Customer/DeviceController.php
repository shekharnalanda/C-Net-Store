<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeviceController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['app_type' => ['required', Rule::in(['customer', 'seller', 'delivery'])], 'platform' => ['required', Rule::in(['android', 'ios', 'web'])], 'token' => ['required', 'string', 'max:512'], 'device_name' => ['nullable', 'string', 'max:120']]);
        $token = DeviceToken::updateOrCreate(['token' => $data['token']], [...$data, 'user_id' => $request->user()->id, 'last_used_at' => now(), 'is_active' => true]);
        return response()->json(['data' => $token], 201);
    }
}

