@extends('admin.layout')
@section('title', $title)
@section('content')
<style>
.ops-filters,.ops-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}.ops-filters input,.ops-filters select,.ops-field{border:1px solid #d7dce2;border-radius:8px;padding:8px;background:#fff;max-width:180px}.ops-field.wide{max-width:280px}.ops-save{border:0;border-radius:8px;padding:8px 11px;background:#172b4d;color:#fff;font-weight:700;cursor:pointer}.ops-green{background:#168b48}.ops-card{display:grid;gap:5px;min-width:190px}.ops-card small{color:#68707c}.ops-error{background:#fff1f1;color:#9c1c1c;padding:12px;border-radius:8px;margin-bottom:14px}.ops-text{width:280px;min-height:80px}.ops-check{width:18px;height:18px}
</style>

<div class="page-title">
    <div><span>Marketplace management</span><h1>{{ $title }}</h1></div>
    <form method="get" class="ops-filters">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search">
        <select name="status">
            <option value="">All statuses</option>
            @foreach(['pending','under_review','approved','rejected','suspended','payment_pending','confirmed','accepted','preparing','ready_for_pickup','out_for_delivery','delivered','cancelled','refunded','open','in_progress','waiting_customer','resolved','closed','draft','published','paid'] as $status)
                <option value="{{ $status }}" @selected(request('status')===$status)>{{ ucwords(str_replace('_',' ',$status)) }}</option>
            @endforeach
        </select>
        <button class="small-btn" type="submit">Filter</button>
        <a class="small-btn" href="{{ url()->current() }}">Reset</a>
    </form>
</div>

@if($errors->any())<div class="ops-error">{{ $errors->first() }}</div>@endif

<section class="panel">
    <div class="panel-head"><div><h2>{{ $rows->total() }} records</h2><p>Verified administrative controls with protected status changes.</p></div></div>
    <div class="table-wrap"><table>
        <thead><tr><th>ID / Name</th><th>Details</th><th>Status</th><th>Created</th><th>Manage</th></tr></thead>
        <tbody>
        @forelse($rows as $row)
            <tr>
                <td><div class="ops-card"><strong>
                    @switch($type)
                        @case('sellers') {{ $row->name }} @break
                        @case('orders') {{ $row->order_number }} @break
                        @case('customers') {{ $row->name }} @break
                        @case('delivery') {{ $row->user->name ?? 'Partner' }} @break
                        @case('settlements') {{ $row->settlement_number }} @break
                        @case('support') {{ $row->ticket_number }} @break
                        @case('content') {{ $row->title }} @break
                    @endswitch
                </strong><small>#{{ $row->id }}</small></div></td>
                <td>
                    @switch($type)
                        @case('sellers') {{ $row->owner->phone ?? '' }} · {{ $row->type->value ?? $row->type }} · {{ $row->outlets->count() }} outlet(s) @break
                        @case('orders') {{ $row->customer->name ?? '' }} · {{ $row->business->name ?? '' }} · ₹{{ number_format($row->grand_total,2) }} · Payment: {{ $row->payments->last()?->status?->value ?? $row->payments->last()?->status ?? 'pending' }} @break
                        @case('customers') {{ $row->phone }} · {{ $row->email }} @break
                        @case('delivery') {{ $row->user->phone ?? '' }} · {{ $row->vehicle_number ?: 'Vehicle pending' }} · {{ $row->is_online ? 'Online' : 'Offline' }} @break
                        @case('settlements') {{ $row->business->name ?? '' }} · ₹{{ number_format($row->net_payable,2) }} · {{ $row->period_start?->format('d M') }}–{{ $row->period_end?->format('d M Y') }} @break
                        @case('support') {{ $row->subject }} · {{ $row->user->phone ?? '' }} @break
                        @case('content') /{{ $row->slug }} @break
                    @endswitch
                </td>
                <td><span class="status">{{ str_replace('_',' ',($row->status->value ?? $row->status ?? ($row->is_published ? 'published':'draft'))) }}</span></td>
                <td>{{ $row->created_at?->format('d M Y') }}</td>
                <td>
                    @switch($type)
                        @case('sellers')
                            <form method="post" action="{{ route('admin.sellers.update',$row) }}" class="ops-actions">@csrf @method('patch')
                                <select class="ops-field" name="status">@foreach(['pending','under_review','approved','rejected','suspended'] as $s)<option value="{{ $s }}" @selected(($row->status->value ?? $row->status)===$s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>@endforeach</select>
                                <input class="ops-field" type="number" name="commission_rate" min="0" max="100" step=".01" value="{{ $row->commission_rate }}" required title="Commission %">
                                <input type="hidden" name="seller_delivery_enabled" value="0"><label>Seller delivery <input class="ops-check" type="checkbox" name="seller_delivery_enabled" value="1" @checked($row->seller_delivery_enabled)></label>
                                <input type="hidden" name="cnet_delivery_enabled" value="0"><label>C-Net delivery <input class="ops-check" type="checkbox" name="cnet_delivery_enabled" value="1" @checked($row->cnet_delivery_enabled)></label>
                                <button class="ops-save">Save</button>
                            </form>
                            @break
                        @case('orders')
                            <form method="post" action="{{ route('admin.orders.update',$row) }}" class="ops-actions">@csrf @method('patch')
                                <select class="ops-field" name="status">@foreach(['accepted','preparing','ready_for_pickup','out_for_delivery','delivered','cancelled'] as $s)<option value="{{ $s }}">{{ ucwords(str_replace('_',' ',$s)) }}</option>@endforeach</select>
                                <input class="ops-field wide" name="note" maxlength="500" placeholder="Status note">
                                <button class="ops-save">Update</button>
                            </form>
                            @if($row->fulfilment_type==='cnet_delivery' && !$row->deliveryAssignment)
                                <form method="post" action="{{ route('admin.orders.delivery',$row) }}" class="ops-actions" style="margin-top:7px">@csrf
                                    <select class="ops-field" name="delivery_partner_id" required><option value="">Assign partner</option>@foreach($partners as $partner)<option value="{{ $partner->id }}">{{ $partner->user->name ?? 'Partner #'.$partner->id }}</option>@endforeach</select>
                                    <button class="ops-save ops-green">Assign</button>
                                </form>
                            @elseif($row->deliveryAssignment)
                                <small>Assigned: {{ $row->deliveryAssignment->partner->user->name ?? 'Partner' }}</small>
                            @endif
                            @break
                        @case('customers')
                            <form method="post" action="{{ route('admin.customers.update',$row) }}" class="ops-actions">@csrf @method('patch')
                                <select class="ops-field" name="status">@foreach(['approved','suspended'] as $s)<option value="{{ $s }}" @selected(($row->status->value ?? $row->status)===$s)>{{ ucfirst($s) }}</option>@endforeach</select><button class="ops-save">Save</button>
                            </form>
                            @break
                        @case('delivery')
                            <form method="post" action="{{ route('admin.delivery.update',$row) }}" class="ops-actions">@csrf @method('patch')
                                <select class="ops-field" name="status">@foreach(['pending','under_review','approved','rejected','suspended'] as $s)<option value="{{ $s }}" @selected(($row->status->value ?? $row->status)===$s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>@endforeach</select>
                                <input class="ops-field" name="vehicle_type" value="{{ $row->vehicle_type }}" placeholder="Vehicle type">
                                <input class="ops-field" name="vehicle_number" value="{{ $row->vehicle_number }}" placeholder="Vehicle number">
                                <button class="ops-save">Save</button>
                            </form>
                            @break
                        @case('support')
                            <form method="post" action="{{ route('admin.support.update',$row) }}" class="ops-actions">@csrf @method('patch')
                                <select class="ops-field" name="priority">@foreach(['low','normal','high','urgent'] as $s)<option value="{{ $s }}" @selected($row->priority===$s)>{{ ucfirst($s) }}</option>@endforeach</select>
                                <select class="ops-field" name="status">@foreach(['open','in_progress','waiting_customer','resolved','closed'] as $s)<option value="{{ $s }}" @selected($row->status===$s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>@endforeach</select>
                                <button class="ops-save">Save</button>
                            </form>
                            @break
                        @case('content')
                            <form method="post" action="{{ route('admin.content.update',$row) }}" class="ops-card">@csrf @method('patch')
                                <input class="ops-field wide" name="title" value="{{ $row->title }}" required>
                                <textarea class="ops-field ops-text" name="content" required>{{ $row->content }}</textarea>
                                <input class="ops-field wide" name="meta_title" value="{{ $row->meta_title }}" placeholder="Meta title">
                                <input class="ops-field wide" name="meta_description" value="{{ $row->meta_description }}" placeholder="Meta description">
                                <input type="hidden" name="is_published" value="0"><label>Published <input class="ops-check" type="checkbox" name="is_published" value="1" @checked($row->is_published)></label>
                                <button class="ops-save">Save page</button>
                            </form>
                            @break
                        @default
                            <span class="ops-card"><small>Managed through verified settlement workflow.</small></span>
                    @endswitch
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="empty">No records found.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    <div class="pager">{{ $rows->links() }}</div>
</section>
@endsection
