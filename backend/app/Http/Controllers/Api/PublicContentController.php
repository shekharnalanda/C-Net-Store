<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Models\PromotionBanner;
use Illuminate\Http\JsonResponse;

class PublicContentController extends Controller
{
    public function banners(): JsonResponse { return response()->json(['data' => PromotionBanner::where('is_active', true)->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))->orderBy('sort_order')->get()]); }
    public function page(string $slug): JsonResponse { return response()->json(['data' => CmsPage::where('slug', $slug)->where('is_published', true)->firstOrFail()]); }
}

