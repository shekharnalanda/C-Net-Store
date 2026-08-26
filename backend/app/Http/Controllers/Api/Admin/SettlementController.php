<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\SellerSettlement;
use App\Services\SettlementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    public function index(): JsonResponse { return response()->json(['data' => SellerSettlement::with('business')->latest()->paginate(30)]); }
    public function store(Request $request, SettlementService $service): JsonResponse
    {
        $data = $request->validate(['business_id' => ['required', 'exists:businesses,id'], 'period_start' => ['required', 'date'], 'period_end' => ['required', 'date', 'after_or_equal:period_start']]);
        return response()->json(['data' => $service->generate(Business::findOrFail($data['business_id']), $data['period_start'], $data['period_end'])], 201);
    }
    public function pay(Request $request, SellerSettlement $settlement): JsonResponse
    {
        $data = $request->validate(['payment_reference' => ['required', 'string', 'max:190', 'unique:seller_settlements,payment_reference']]);
        abort_unless(in_array($settlement->status, ['draft', 'approved'], true), 422);
        $settlement->update(['status' => 'paid', 'approved_by' => $request->user()->id, 'approved_at' => $settlement->approved_at ?? now(), 'paid_at' => now(), 'payment_reference' => $data['payment_reference']]);
        return response()->json(['message' => 'Settlement marked paid.', 'data' => $settlement->fresh()]);
    }
}

