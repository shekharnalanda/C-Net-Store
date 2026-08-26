<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\CmsPage;
use App\Models\DeliveryPartner;
use App\Models\Order;
use App\Models\Product;
use App\Models\SellerSettlement;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperationsController extends Controller
{
    public function sellers(Request $request): View { return $this->page('Sellers & Local Shops', 'sellers', Business::with('owner')->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))->latest()->paginate(30)); }
    public function products(Request $request): View { return $this->page('Products & Catalogue', 'products', Product::with(['business', 'category'])->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))->latest()->paginate(30)); }
    public function orders(Request $request): View { return $this->page('Orders', 'orders', Order::with(['customer', 'business', 'deliveryAssignment.partner.user'])->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))->latest()->paginate(30)); }
    public function customers(): View { return $this->page('Customers', 'customers', User::where('role', 'customer')->latest()->paginate(30)); }
    public function delivery(): View { return $this->page('Delivery Partners', 'delivery', DeliveryPartner::with('user')->latest()->paginate(30)); }
    public function settlements(): View { return $this->page('Seller Settlements', 'settlements', SellerSettlement::with('business')->latest()->paginate(30)); }
    public function support(): View { return $this->page('Customer Support', 'support', SupportTicket::with(['user', 'order'])->latest()->paginate(30)); }
    public function content(): View { return $this->page('CMS Pages', 'content', CmsPage::latest()->paginate(30)); }

    private function page(string $title, string $type, LengthAwarePaginator $rows): View
    {
        return view('admin.operations.index', compact('title', 'type', 'rows'));
    }
}

