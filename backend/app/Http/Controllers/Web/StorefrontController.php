<?php

namespace App\Http\Controllers\Web;

use App\Enums\ApprovalStatus;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use App\Models\PromotionBanner;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    public function home(): View
    {
        return view('storefront.home', [
            'banners' => PromotionBanner::where('is_active', true)->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))->orderBy('sort_order')->limit(8)->get(),
            'categories' => Category::whereNull('parent_id')->where('is_active', true)->orderBy('sort_order')->limit(12)->get(),
            'businesses' => Business::where('status', ApprovalStatus::Approved)->with('outlets')->latest()->limit(12)->get(),
            'products' => Product::where('is_active', true)->with(['business', 'category'])->latest()->limit(16)->get(),
        ]);
    }

    public function catalog(Request $request): View
    {
        $products = Product::query()->where('is_active', true)->with(['business', 'category'])
            ->when($request->filled('type'), fn ($q) => $q->where('product_type', $request->input('type')))
            ->when($request->filled('category'), fn ($q) => $q->whereHas('category', fn ($category) => $category->where('slug', $request->input('category'))))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($search) => $search->where('name', 'like', '%'.$request->input('q').'%')->orWhere('description', 'like', '%'.$request->input('q').'%')))
            ->latest()->paginate(24)->withQueryString();

        return view('storefront.catalog', ['products' => $products, 'categories' => Category::where('is_active', true)->orderBy('name')->get()]);
    }

    public function product(Product $product): View
    {
        abort_unless($product->is_active, 404);
        return view('storefront.product', ['product' => $product->load(['business.outlets', 'category']), 'related' => Product::where('is_active', true)->where('category_id', $product->category_id)->whereKeyNot($product->id)->limit(8)->get()]);
    }

    public function business(Business $business): View
    {
        abort_unless($business->status === ApprovalStatus::Approved, 404);
        return view('storefront.business', ['business' => $business->load('outlets'), 'products' => $business->products()->where('is_active', true)->paginate(24)]);
    }

    public function cart(): View
    {
        return view('storefront.cart');
    }
}

