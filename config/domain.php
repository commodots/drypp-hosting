<?php

return [
    // Manual display conversion only. Does not set the payment/checkout currency.
    'ngn_per_usd' => (float) env('DOMAIN_NGN_PER_USD', 1500),
    'force_usd_display' => (bool) env('DOMAIN_FORCE_USD_DISPLAY', true),
];
