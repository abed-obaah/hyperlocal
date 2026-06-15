<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        return Coupon::all()->map(fn ($c) => $this->payload($c));
    }

    public function validateCode(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric',
        ]);

        $coupon = Coupon::whereRaw('UPPER(code) = ?', [strtoupper($data['code'])])->first();
        if (! $coupon) {
            return response()->json(['valid' => false, 'message' => 'Invalid coupon code'], 422);
        }
        if ($data['subtotal'] < $coupon->min_subtotal) {
            return response()->json([
                'valid' => false,
                'message' => "Spend at least {$coupon->min_subtotal} to use this coupon",
            ], 422);
        }

        return response()->json(['valid' => true, 'coupon' => $this->payload($coupon)]);
    }

    private function payload(Coupon $c): array
    {
        return [
            'code' => $c->code,
            'label' => $c->label,
            'type' => $c->type,
            'value' => (float) $c->value,
            'minSubtotal' => (float) $c->min_subtotal,
        ];
    }
}
