<?php

namespace App\Http\Controllers\Api\Seller;

use App\Enums\ApprovalStatus;
use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BusinessController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()->businesses()->with('outlets')->latest()->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'type' => ['required', Rule::in(['retail', 'grocery', 'restaurant', 'multi_category'])],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:190'],
            'tax_number' => ['nullable', 'string', 'max:40'],
            'seller_delivery_enabled' => ['boolean'],
            'cnet_delivery_enabled' => ['boolean'],
        ]);

        $business = $request->user()->businesses()->create([
            ...$data,
            'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(6)),
            'status' => ApprovalStatus::Pending,
        ]);

        return response()->json(['message' => 'Business submitted for approval.', 'data' => $business], 201);
    }

    public function show(Request $request, Business $business): JsonResponse
    {
        abort_unless($business->owner_id === $request->user()->id, 403);

        return response()->json(['data' => $business->load('outlets')]);
    }
}

