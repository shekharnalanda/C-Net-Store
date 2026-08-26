<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\ApprovalStatus;
use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BusinessApprovalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => Business::query()->with(['owner', 'outlets'])->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))->latest()->paginate(30)]);
    }

    public function update(Request $request, Business $business): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['under_review', 'approved', 'rejected', 'suspended'])],
            'commission_rate' => ['nullable', 'numeric', 'between:0,100'],
            'rejection_reason' => ['nullable', 'required_if:status,rejected', 'string', 'max:1000'],
        ]);

        $status = ApprovalStatus::from($data['status']);
        $business->update([
            ...$data,
            'approved_by' => $status === ApprovalStatus::Approved ? $request->user()->id : $business->approved_by,
            'approved_at' => $status === ApprovalStatus::Approved ? now() : $business->approved_at,
        ]);

        return response()->json(['message' => 'Business status updated.', 'data' => $business->fresh()]);
    }
}
