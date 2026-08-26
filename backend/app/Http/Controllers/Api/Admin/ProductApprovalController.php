<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductApprovalController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => Product::query()->with(['business', 'category'])->latest()->paginate(40)]);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $product->update($data);

        return response()->json(['message' => 'Product visibility updated.', 'data' => $product->fresh()]);
    }
}

