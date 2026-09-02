<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\ApprovalStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductApprovalController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Product::query()
                ->with([
                    'business',
                    'category',
                    'libraryImage',
                ])
                ->latest()
                ->paginate(40),
        ]);
    }

    public function update(
        Request $request,
        Product $product
    ): JsonResponse {
        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        if ($data['is_active']) {
            $product->load([
                'business',
                'category',
                'libraryImage',
            ]);

            abort_unless(
                $product->business
                    && $product->business->status
                        === ApprovalStatus::Approved,
                422,
                'Product business must be approved.'
            );

            abort_unless(
                $product->category
                    && $product->category->is_active,
                422,
                'Product category must be active.'
            );

            abort_unless(
                $product->category->marketplace
                    === $product->product_type,
                422,
                'Product category and type do not match.'
            );

            $hasLibraryImage = $product->libraryImage
                && $product->libraryImage->is_active
                && $product->libraryImage->category_id
                    === $product->category_id;

            $hasCustomImage = $product->image_path
                && Storage::disk('public')
                    ->exists($product->image_path);

            abort_unless(
                $hasLibraryImage || $hasCustomImage,
                422,
                'A valid product image is required.'
            );

            abort_unless(
                (float) $product->price > 0,
                422,
                'Product price must be greater than zero.'
            );
        }

        $product->update($data);

        return response()->json([
            'message' => $data['is_active']
                ? 'Product approved and published.'
                : 'Product unpublished.',
            'data' => $product->fresh()->load([
                'business',
                'category',
                'libraryImage',
            ]),
        ]);
    }
}
