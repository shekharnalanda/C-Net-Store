<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\CmsPage;
use App\Models\DeliveryPartner;
use App\Models\Order;
use App\Models\Product;
use App\Models\SellerSettlement;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\DeliveryAssignmentService;
use App\Services\OrderStatusService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OperationsController extends Controller
{
    public function sellers(Request $request): View
    {
        $rows = Business::with(['owner', 'outlets'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', '%'.$request->q.'%')->orWhere('phone', 'like', '%'.$request->q.'%')))
            ->latest()->paginate(30)->withQueryString();

        return $this->page('Sellers & Local Shops', 'sellers', $rows);
    }

    public function products(Request $request): View
    {
        return $this->page('Products & Catalogue', 'products', Product::with(['business', 'category'])->latest()->paginate(30));
    }

    public function orders(Request $request): View
    {
        $rows = Order::with(['customer', 'business', 'payments', 'deliveryAssignment.partner.user'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($q) => $q->where('order_number', 'like', '%'.$request->q.'%')->orWhereHas('customer', fn ($u) => $u->where('phone', 'like', '%'.$request->q.'%'))))
            ->latest()->paginate(30)->withQueryString();

        $partners = DeliveryPartner::with('user')->where('status', 'approved')->orderBy('id')->get();

        return $this->page('Orders', 'orders', $rows, compact('partners'));
    }

    public function customers(Request $request): View
    {
        $rows = User::where('role', 'customer')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', '%'.$request->q.'%')->orWhere('phone', 'like', '%'.$request->q.'%')->orWhere('email', 'like', '%'.$request->q.'%')))
            ->latest()->paginate(30)->withQueryString();

        return $this->page('Customers', 'customers', $rows);
    }

    public function delivery(Request $request): View
    {
        $rows = DeliveryPartner::with('user')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), fn ($q) => $q->whereHas('user', fn ($u) => $u->where('name', 'like', '%'.$request->q.'%')->orWhere('phone', 'like', '%'.$request->q.'%')))
            ->latest()->paginate(30)->withQueryString();

        return $this->page('Delivery Partners', 'delivery', $rows);
    }

    public function settlements(Request $request): View
    {
        return $this->page('Seller Settlements', 'settlements', SellerSettlement::with('business')->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))->latest()->paginate(30)->withQueryString());
    }

    public function support(Request $request): View
    {
        $rows = SupportTicket::with(['user', 'order'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($q) => $q->where('ticket_number', 'like', '%'.$request->q.'%')->orWhere('subject', 'like', '%'.$request->q.'%')))
            ->latest()->paginate(30)->withQueryString();

        return $this->page('Customer Support', 'support', $rows);
    }

    public function content(Request $request): View
    {
        $rows = CmsPage::when($request->filled('status'), fn ($q) => $q->where('is_published', $request->status === 'published'))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($q) => $q->where('title', 'like', '%'.$request->q.'%')->orWhere('slug', 'like', '%'.$request->q.'%')))
            ->latest()->paginate(30)->withQueryString();

        return $this->page('CMS Pages', 'content', $rows);
    }

    public function updateSeller(Request $request, Business $business): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'under_review', 'approved', 'rejected', 'suspended'])],
            'commission_rate' => ['required', 'numeric', 'between:0,100'],
            'seller_delivery_enabled' => ['required', 'boolean'],
            'cnet_delivery_enabled' => ['required', 'boolean'],
        ]);
        $business->update($data);

        return back()->with('success', $business->name.' updated.');
    }

    public function updateOrder(Request $request, Order $order, OrderStatusService $service): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['accepted', 'preparing', 'ready_for_pickup', 'out_for_delivery', 'delivered', 'cancelled'])],
            'note' => ['nullable', 'string', 'max:500'],
        ]);
        $service->transition($order, OrderStatus::from($data['status']), $request->user(), $data['note'] ?? null);

        return back()->with('success', $order->order_number.' status updated.');
    }

    public function assignDelivery(Request $request, Order $order, DeliveryAssignmentService $service): RedirectResponse
    {
        $data = $request->validate(['delivery_partner_id' => ['required', 'exists:delivery_partners,id']]);
        $partner = DeliveryPartner::findOrFail($data['delivery_partner_id']);
        $service->assign($order, $partner, $request->user());

        return back()->with('success', 'Delivery partner assigned to '.$order->order_number.'.');
    }

    public function updateCustomer(Request $request, User $customer): RedirectResponse
    {
        abort_unless(($customer->role->value ?? $customer->role) === 'customer', 404);
        $data = $request->validate(['status' => ['required', Rule::in(['approved', 'suspended'])]]);
        $customer->update($data);

        return back()->with('success', $customer->name.' status updated.');
    }

    public function updateDelivery(Request $request, DeliveryPartner $partner): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'under_review', 'approved', 'rejected', 'suspended'])],
            'vehicle_type' => ['nullable', 'string', 'max:50'],
            'vehicle_number' => ['nullable', 'string', 'max:50'],
        ]);
        $partner->update($data);

        return back()->with('success', 'Delivery partner updated.');
    }

    public function updateSupport(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'in_progress', 'waiting_customer', 'resolved', 'closed'])],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
        ]);
        $data['closed_at'] = in_array($data['status'], ['resolved', 'closed'], true) ? now() : null;
        $ticket->update($data);

        return back()->with('success', $ticket->ticket_number.' updated.');
    }

    public function updateContent(Request $request, CmsPage $page): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'content' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:190'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'is_published' => ['required', 'boolean'],
        ]);
        $data['published_at'] = $data['is_published'] ? ($page->published_at ?? now()) : null;
        $page->update($data);

        return back()->with('success', $page->title.' updated.');
    }

    private function page(string $title, string $type, LengthAwarePaginator $rows, array $extra = []): View
    {
        return view('admin.operations.index', [...compact('title', 'type', 'rows'), ...$extra]);
    }
}
