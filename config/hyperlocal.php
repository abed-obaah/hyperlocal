<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Platform economics
    |--------------------------------------------------------------------------
    |
    | commission_rate: platform's cut of each order's food subtotal (0.15 = 15%).
    | rider_fee: flat payout (₦) a rider earns per completed delivery.
    |
    */

    'commission_rate' => (float) env('HYPERLOCAL_COMMISSION_RATE', 0.15),

    'rider_fee' => (float) env('HYPERLOCAL_RIDER_FEE', 800),
];
