<?php

namespace App\Http\Controllers\Api\Delivery;

use App\Enums\ApprovalStatus;
use App\Http\Controllers\Controller;
use App\Models\DeliveryEarning;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $partner = $request->user()->deliveryPartner;
        abort_unless($partner, 404, 'Delivery partner profile not found.');
        return response()->json(['data' => $partner->load('user'), 'earnings' => ['pending' => DeliveryEarning::where('delivery_partner_id', $partner->id)->where('status', 'pending')->sum('net_amount'), 'settled' => DeliveryEarning::where('delivery_partner_id', $partner->id)->where('status', 'settled')->sum('net_amount')]]);
    }

    public function availability(Request $request): JsonResponse
    {
        $data = $request->validate(['is_online' => ['required', 'boolean'], 'latitude' => ['nullable', 'numeric', 'between:-90,90'], 'longitude' => ['nullable', 'numeric', 'between:-180,180']]);
        $partner = $request->user()->deliveryPartner;
        abort_unless($partner && $partner->status === ApprovalStatus::Approved, 422, 'Partner approval is required before going online.');
        $partner->update(['is_online' => $data['is_online'], 'current_latitude' => $data['latitude'] ?? $partner->current_latitude, 'current_longitude' => $data['longitude'] ?? $partner->current_longitude]);
        return response()->json(['message' => $data['is_online'] ? 'You are online.' : 'You are offline.', 'data' => $partner->fresh()]);
    }

    public function earnings(Request $request): JsonResponse
    {
        return response()->json(['data' => DeliveryEarning::where('delivery_partner_id', $request->user()->deliveryPartner->id)->with('assignment.order')->latest()->paginate(30)]);
    }
}

