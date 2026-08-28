<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ProductImageAsset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductImageLibraryController extends Controller
{
    public function index(Request $request): View
    {
        $assets = ProductImageAsset::with('category')
            ->when($request->filled('group'), fn ($query) => $query->where('group_name', $request->string('group')))
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.addcslashes($request->string('search'), '%_').'%'))
            ->latest()->paginate(24)->withQueryString();

        return view('admin.image-library.index', [
            'assets' => $assets,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'groups' => ProductImageAsset::select('group_name')->distinct()->orderBy('group_name')->pluck('group_name'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'group_name' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:190'],
            'keywords' => ['nullable', 'string', 'max:1000'],
            'alt_text' => ['nullable', 'string', 'max:190'],
            'license_type' => ['required', 'in:cnet_original,licensed_stock,manufacturer_authorized,seller_owned,public_domain'],
            'license_source' => ['nullable', 'url', 'max:1000'],
            'images' => ['required', 'array', 'between:1,20'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        foreach ($request->file('images') as $index => $image) {
            ProductImageAsset::create([
                ...collect($data)->except(['images', 'keywords'])->all(),
                'name' => count($request->file('images')) > 1 ? $data['name'].' '.($index + 1) : $data['name'],
                'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(8)),
                'keywords' => collect(explode(',', $data['keywords'] ?? ''))->map->trim()->filter()->values()->all(),
                'image_path' => $image->store('product-image-library/'.Str::slug($data['group_name']), 'public'),
                'is_active' => false,
            ]);
        }

        return back()->with('success', 'Images uploaded safely. Review and activate them before sellers can use them.');
    }

    public function update(Request $request, ProductImageAsset $asset): RedirectResponse
    {
        $asset->update($request->validate(['is_active' => ['required', 'boolean']]));
        return back()->with('success', 'Image status updated.');
    }

    public function destroy(ProductImageAsset $asset): RedirectResponse
    {
        abort_if($asset->products()->exists(), 422, 'This image is currently used by products. Disable it instead.');
        Storage::disk('public')->delete($asset->image_path);
        $asset->delete();
        return back()->with('success', 'Unused image deleted.');
    }
}
