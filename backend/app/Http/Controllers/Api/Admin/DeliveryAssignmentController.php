<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryPartner;
use App\Models\Order;
use App\Services\DeliveryAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryAssignmentController extends Controller
{
    public function store(Request $request, Order $order, DeliveryAssignmentService $service): JsonResponse
    {
        $data = $request->validate(['delivery_partner_id' => ['required', 'exists:delivery_partners,id']]);
        $assignment = $service->assign($order, DeliveryPartner::findOrFail($data['delivery_partner_id']), $request->user());
        return response()->json(['message' => 'Delivery partner assigned.', 'data' => $assignment], 201);
    }
}

