<?php

namespace App\Http\Controllers\Api\Delivery;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\DeliveryAssignment;
use App\Models\DeliveryEarning;
use App\Services\OrderStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AssignmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => DeliveryAssignment::query()->where('delivery_partner_id', $request->user()->deliveryPartner->id)->with(['order.items', 'order.outlet', 'order.address'])->latest()->paginate(30)]);
    }

    public function update(Request $request, DeliveryAssignment $assignment, OrderStatusService $orders): JsonResponse
    {
        abort_unless($assignment->delivery_partner_id === $request->user()->deliveryPartner->id, 403);
        $data = $request->validate(['status' => ['required', Rule::in(['accepted', 'at_pickup', 'picked_up', 'in_transit', 'delivered', 'failed'])], 'pickup_otp' => ['nullable', 'string', 'size:6'], 'delivery_otp' => ['nullable', 'string', 'size:6'], 'failure_reason' => ['nullable', 'required_if:status,failed', 'string', 'max:500']]);

        DB::transaction(function () use ($assignment, $data, $request, $orders): void {
            $status = DeliveryStatus::from($data['status']);
            if ($status === DeliveryStatus::PickedUp) abort_unless(hash_equals($assignment->pickup_otp, (string) ($data['pickup_otp'] ?? '')), 422, 'Invalid pickup OTP.');
            if ($status === DeliveryStatus::Delivered) abort_unless(hash_equals($assignment->delivery_otp, (string) ($data['delivery_otp'] ?? '')), 422, 'Invalid delivery OTP.');
            $updates = ['status' => $status, 'failure_reason' => $data['failure_reason'] ?? null];
            if ($status === DeliveryStatus::Accepted) $updates['accepted_at'] = now();
            if ($status === DeliveryStatus::PickedUp) { $updates['picked_up_at'] = now(); $orders->transition($assignment->order, OrderStatus::OutForDelivery, $request->user()); }
            if ($status === DeliveryStatus::Delivered) { $updates['delivered_at'] = now(); $orders->transition($assignment->order, OrderStatus::Delivered, $request->user()); DeliveryEarning::firstOrCreate(['delivery_assignment_id' => $assignment->id], ['delivery_partner_id' => $assignment->delivery_partner_id, 'base_amount' => 0, 'net_amount' => 0]); }
            $assignment->update($updates);
        });
        return response()->json(['message' => 'Delivery status updated.', 'data' => $assignment->fresh()]);
    }

    public function location(Request $request, DeliveryAssignment $assignment): JsonResponse
    {
        abort_unless($assignment->delivery_partner_id === $request->user()->deliveryPartner->id, 403);
        $data = $request->validate(['latitude' => ['required', 'numeric', 'between:-90,90'], 'longitude' => ['required', 'numeric', 'between:-180,180'], 'accuracy_meters' => ['nullable', 'numeric', 'min:0']]);
        $location = $assignment->locations()->create([...$data, 'recorded_at' => now()]);
        return response()->json(['data' => $location], 201);
    }
}

