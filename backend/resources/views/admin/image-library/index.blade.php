@extends('admin.layout')
@section('title', 'Smart Product Image Library')
@section('content')
<div class="page-title"><div><span>Seller catalogue tools</span><h1>Smart Product Image Library</h1></div></div>
@if($errors->any())<div class="error">{{ $errors->first() }}</div>@endif
<section class="panel image-upload-panel">
<div class="panel-head"><div><h2>Add copyright-safe images</h2><p>Upload up to 20 related images together. New images remain hidden until approved.</p></div></div>
<form method="post" action="{{ route('admin.image-library.store') }}" enctype="multipart/form-data" class="library-form">@csrf
<label>Group<input name="group_name" required placeholder="Food, Grocery, Beauty…" value="{{ old('group_name') }}"></label>
<label>Item name<input name="name" required placeholder="Chicken Biryani" value="{{ old('name') }}"></label>
<label>Category<select name="category_id"><option value="">General</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></label>
<label>Keywords<input name="keywords" placeholder="biryani, chicken, rice, food"></label>
<label>Image description<input name="alt_text" placeholder="Fresh chicken biryani"></label>
<label>Rights<select name="license_type" required><option value="cnet_original">C-Net original</option><option value="licensed_stock">Licensed stock</option><option value="manufacturer_authorized">Manufacturer authorized</option><option value="seller_owned">Seller owned</option><option value="public_domain">Public domain</option></select></label>
<label class="wide">License/source URL<input type="url" name="license_source" placeholder="https://…"></label>
<label class="wide">Images (JPG, PNG or WebP; max 5 MB each)<input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple required></label>
<button type="submit" class="primary-btn">Upload for review</button></form></section>
<form class="library-filter" method="get"><input name="search" value="{{ request('search') }}" placeholder="Search item"><select name="group"><option value="">All groups</option>@foreach($groups as $group)<option @selected(request('group')===$group)>{{ $group }}</option>@endforeach</select><button>Filter</button></form>
<section class="image-library-grid">@forelse($assets as $asset)
<article class="image-asset-card"><img src="{{ $asset->image_url }}" alt="{{ $asset->alt_text ?: $asset->name }}" loading="lazy"><div><small>{{ $asset->group_name }} · {{ $asset->category->name ?? 'General' }}</small><h3>{{ $asset->name }}</h3><p>{{ str_replace('_', ' ', $asset->license_type) }} · Used {{ $asset->usage_count }} times</p></div><div class="asset-actions"><form method="post" action="{{ route('admin.image-library.update', $asset) }}">@csrf @method('PATCH')<input type="hidden" name="is_active" value="{{ $asset->is_active ? 0 : 1 }}"><button class="{{ $asset->is_active ? 'danger-btn' : 'success-btn' }}">{{ $asset->is_active ? 'Disable' : 'Approve' }}</button></form>@if(!$asset->products()->exists())<form method="post" action="{{ route('admin.image-library.destroy', $asset) }}" onsubmit="return confirm('Delete this unused image?')">@csrf @method('DELETE')<button class="small-btn">Delete</button></form>@endif</div></article>
@empty<div class="empty-card">No image assets found. Upload the first approved collection above.</div>@endforelse</section><div class="pager">{{ $assets->links() }}</div>
@endsection
