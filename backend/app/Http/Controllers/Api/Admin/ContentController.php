<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Models\PromotionBanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContentController extends Controller
{
    public function pages(): JsonResponse { return response()->json(['data' => CmsPage::latest()->paginate(30)]); }
    public function savePage(Request $request, ?CmsPage $page = null): JsonResponse
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:190'], 'slug' => ['nullable', 'string', 'max:190'], 'content' => ['required', 'string'], 'meta_title' => ['nullable', 'string', 'max:190'], 'meta_description' => ['nullable', 'string', 'max:255'], 'is_published' => ['boolean']]);
        $data['slug'] = Str::slug($data['slug'] ?? $data['title']); $data['published_at'] = ! empty($data['is_published']) ? now() : null;
        $page ? $page->update($data) : $page = CmsPage::create($data);
        return response()->json(['data' => $page->fresh()], $page->wasRecentlyCreated ? 201 : 200);
    }
    public function banners(): JsonResponse { return response()->json(['data' => PromotionBanner::latest()->paginate(30)]); }
    public function saveBanner(Request $request, ?PromotionBanner $banner = null): JsonResponse
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:190'], 'subtitle' => ['nullable', 'string', 'max:255'], 'image_path' => ['required', 'string', 'max:255'], 'target_type' => ['required', 'in:none,product,category,business,url'], 'target_value' => ['nullable', 'string', 'max:500'], 'audience' => ['required', 'in:customer,seller,delivery,all'], 'sort_order' => ['integer', 'min:0'], 'starts_at' => ['nullable', 'date'], 'ends_at' => ['nullable', 'date', 'after:starts_at'], 'is_active' => ['boolean']]);
        $banner ? $banner->update($data) : $banner = PromotionBanner::create($data);
        return response()->json(['data' => $banner->fresh()], $banner->wasRecentlyCreated ? 201 : 200);
    }
}

