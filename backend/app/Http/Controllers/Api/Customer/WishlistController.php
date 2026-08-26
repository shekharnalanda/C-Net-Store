<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\WishlistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request): JsonResponse { return response()->json(['data' => WishlistItem::where('user_id', $request->user()->id)->with('product.business')->latest()->paginate(30)]); }
    public function toggle(Request $request, Product $product): JsonResponse
    {
        $item = WishlistItem::where('user_id', $request->user()->id)->where('product_id', $product->id)->first();
        if ($item) { $item->delete(); return response()->json(['wishlisted' => false]); }
        WishlistItem::create(['user_id' => $request->user()->id, 'product_id' => $product->id]); return response()->json(['wishlisted' => true]);
    }
}

