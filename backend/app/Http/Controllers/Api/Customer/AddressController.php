<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse { return response()->json(['data' => $request->user()->addresses()->latest()->get()]); }

    public function store(Request $request): JsonResponse
    {
        $address = $request->user()->addresses()->create($this->validated($request));
        return response()->json(['data' => $address], 201);
    }

    public function update(Request $request, Address $address): JsonResponse
    {
        abort_unless($address->user_id === $request->user()->id, 403);
        $address->update($this->validated($request));
        return response()->json(['data' => $address->fresh()]);
    }

    private function validated(Request $request): array
    {
        return $request->validate(['label' => ['required', 'string', 'max:50'], 'contact_name' => ['required', 'string', 'max:120'], 'contact_phone' => ['required', 'string', 'max:20'], 'address_line' => ['required', 'string', 'max:255'], 'landmark' => ['nullable', 'string', 'max:160'], 'city' => ['required', 'in:Bihar Sharif'], 'postal_code' => ['required', 'string', 'max:10'], 'latitude' => ['required', 'numeric', 'between:-90,90'], 'longitude' => ['required', 'numeric', 'between:-180,180'], 'is_default' => ['boolean']]);
    }
}

