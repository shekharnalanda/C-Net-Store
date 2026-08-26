<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\DeliveryPartner;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'metrics' => [
                'orders_today' => Order::whereDate('created_at', today())->count(),
                'sales_today' => PaymentTransaction::where('status', 'captured')->whereDate('paid_at', today())->sum('amount'),
                'active_sellers' => Business::where('status', 'approved')->count(),
                'active_products' => Product::where('is_active', true)->count(),
                'customers' => User::where('role', 'customer')->count(),
                'delivery_partners' => DeliveryPartner::where('status', 'approved')->count(),
                'pending_sellers' => Business::where('status', 'pending')->count(),
                'open_tickets' => SupportTicket::where('status', '!=', 'closed')->count(),
            ],
            'recentOrders' => Order::with(['customer', 'business'])->latest()->limit(10)->get(),
            'statusCounts' => Order::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }
}

