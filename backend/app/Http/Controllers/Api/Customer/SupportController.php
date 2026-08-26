<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupportController extends Controller
{
    public function index(Request $request): JsonResponse { return response()->json(['data' => SupportTicket::where('user_id', $request->user()->id)->with('messages.sender')->latest()->paginate(20)]); }
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['order_id' => ['nullable', 'exists:orders,id'], 'subject' => ['required', 'string', 'max:190'], 'category' => ['required', 'in:order,payment,refund,delivery,product,account,other'], 'message' => ['required', 'string', 'max:5000']]);
        if (! empty($data['order_id'])) abort_unless($request->user()->orders()->whereKey($data['order_id'])->exists(), 403);
        $ticket = SupportTicket::create(['ticket_number' => 'TKT-'.now()->format('ymd').'-'.Str::upper(Str::random(8)), 'user_id' => $request->user()->id, 'order_id' => $data['order_id'] ?? null, 'subject' => $data['subject'], 'category' => $data['category']]);
        $ticket->messages()->create(['sender_id' => $request->user()->id, 'message' => $data['message']]);
        return response()->json(['data' => $ticket->load('messages')], 201);
    }
    public function reply(Request $request, SupportTicket $ticket): JsonResponse
    {
        abort_unless($ticket->user_id === $request->user()->id && $ticket->status !== 'closed', 403);
        $data = $request->validate(['message' => ['required', 'string', 'max:5000']]);
        return response()->json(['data' => $ticket->messages()->create(['sender_id' => $request->user()->id, 'message' => $data['message']])], 201);
    }
}

