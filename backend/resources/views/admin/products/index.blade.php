@extends('admin.layout')
@section('title', 'Products & Catalogue')
@section('content')
<style>
.catalog-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:18px}.catalog-stat{background:#fff;border:1px solid #e7e7e7;border-radius:12px;padding:16px}.catalog-stat strong{display:block;font-size:24px}.catalog-filters{display:flex;gap:10px;flex-wrap:wrap;align-items:end}.catalog-filters label{display:grid;gap:5px;font-size:12px;font-weight:700}.catalog-filters input,.catalog-filters select,.catalog-input{border:1px solid #d9d9d9;border-radius:8px;padding:9px;background:#fff}.catalog-input{width:100%;min-width:85px}.catalog-actions{display:flex;gap:8px;align-items:center}.catalog-image{width:52px;height:52px;object-fit:cover;border-radius:9px;background:#f3f3f3}.catalog-name{display:flex;gap:10px;align-items:center;min-width:220px}.catalog-name small{display:block;color:#777}.catalog-check{width:18px;height:18px}.catalog-save,.catalog-activate{border:0;border-radius:8px;padding:9px 12px;cursor:pointer;font-weight:700}.catalog-save{background:#172b4d;color:#fff}.catalog-activate{background:#159447;color:#fff}.catalog-muted{color:#777;font-size:12px}.catalog-error{background:#fff1f1;color:#9c1c1c;padding:12px;border-radius:8px;margin-bottom:14px}@media(max-width:800px){.catalog-stats{grid-template-columns:1fr}.catalog-filters{display:grid}.catalog-actions{align-items:stretch}.catalog-actions button{width:100%}}
</style>

<div class="page-title">
    <div><span>Marketplace management</span><h1>Products & Catalogue</h1></div>
</div>

@if($errors->any())
    <div class="catalog-error">{{ $errors->first() }}</div>
@endif

<div class="catalog-stats">
    <div class="catalog-stat"><span>All products</span><strong>{{ $counts['all'] }}</strong></div>
    <div class="catalog-stat"><span>Active</span><strong>{{ $counts['active'] }}</strong></div>
    <div class="catalog-stat"><span>Drafts</span><strong>{{ $counts['draft'] }}</strong></div>
</div>

<section class="panel">
    <div class="panel-head">
        <form method="get" class="catalog-filters">
            <label>Search<input type="search" name="q" value="{{ request('q') }}" placeholder="Name or SKU"></label>
            <label>Status<select name="status"><option value="">All statuses</option><option value="draft" @selected(request('status')==='draft')>Draft</option><option value="active" @selected(request('status')==='active')>Active</option></select></label>
            <label>Category<select name="category"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string)request('category')===(string)$category->id)>{{ $category->name }}</option>@endforeach</select></label>
            <button class="small-btn" type="submit">Apply filters</button>
            <a class="small-btn" href="{{ route('admin.products') }}">Reset</a>
        </form>
    </div>

    <form id="bulk-form" method="post" action="{{ route('admin.products.bulk-activate') }}">
        @csrf
    </form>

    <div class="panel-head">
        <div><h2>{{ $products->total() }} records</h2><p>Set genuine price and stock before activation.</p></div>
        <button class="catalog-activate" type="submit" form="bulk-form">Activate selected valid products</button>
    </div>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Select</th><th>Product</th><th>Category</th><th>Price / Sale</th><th>Stock / Unit</th><th>Tax</th><th>Active</th><th>Action</th></tr></thead>
            <tbody>
            @forelse($products as $product)
                <tr>
                    <td><input class="catalog-check" type="checkbox" name="product_ids[]" value="{{ $product->id }}" form="bulk-form" aria-label="Select {{ $product->name }}"></td>
                    <td><div class="catalog-name">@if($product->image_url)<img class="catalog-image" src="{{ $product->image_url }}" alt="">@endif<div><strong>{{ $product->name }}</strong><small>{{ $product->sku ?: '#'.$product->id }}</small></div></div></td>
                    <td>{{ $product->category->name ?? 'Uncategorised' }}</td>
                    <td>
                        <input class="catalog-input" type="number" name="price" value="{{ $product->price }}" min="0" step="0.01" required form="edit-{{ $product->id }}" aria-label="Price">
                        <input class="catalog-input" type="number" name="sale_price" value="{{ $product->sale_price }}" min="0.01" step="0.01" form="edit-{{ $product->id }}" placeholder="Sale price" aria-label="Sale price">
                    </td>
                    <td>
                        <input class="catalog-input" type="number" name="stock_quantity" value="{{ $product->stock_quantity }}" min="0" step="1" required form="edit-{{ $product->id }}" aria-label="Stock">
                        <input class="catalog-input" type="text" name="unit" value="{{ $product->unit }}" maxlength="30" form="edit-{{ $product->id }}" placeholder="Unit" aria-label="Unit">
                    </td>
                    <td><input class="catalog-input" type="number" name="tax_rate" value="{{ $product->tax_rate }}" min="0" max="100" step="0.01" required form="edit-{{ $product->id }}" aria-label="Tax rate"></td>
                    <td>
                        <input type="hidden" name="is_active" value="0" form="edit-{{ $product->id }}">
                        <input class="catalog-check" type="checkbox" name="is_active" value="1" @checked($product->is_active) form="edit-{{ $product->id }}" aria-label="Active">
                        <div class="catalog-muted">{{ $product->is_active ? 'Live' : 'Draft' }}</div>
                    </td>
                    <td>
                        <form id="edit-{{ $product->id }}" method="post" action="{{ route('admin.products.update', $product) }}">@csrf @method('patch')</form>
                        <button class="catalog-save" type="submit" form="edit-{{ $product->id }}">Save</button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="empty">No products found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pager">{{ $products->links() }}</div>
</section>
@endsection
