<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        return Complaint::where('customer_id', $request->user()->id)->latest()->get()->map(fn ($c) => [
            'id' => (string) $c->id,
            'orderId' => $c->order_id ? (string) $c->order_id : null,
            'subject' => $c->subject,
            'description' => $c->description,
            'status' => $c->status,
            'createdAt' => optional($c->created_at)->toISOString(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'orderId' => 'nullable|exists:orders,id',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $complaint = Complaint::create([
            'customer_id' => $request->user()->id,
            'order_id' => $data['orderId'] ?? null,
            'subject' => $data['subject'],
            'description' => $data['description'] ?? null,
            'status' => 'open',
        ]);

        return response()->json([
            'id' => (string) $complaint->id,
            'subject' => $complaint->subject,
            'status' => $complaint->status,
        ], 201);
    }
}
