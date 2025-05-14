<?php

return [
    'domain' => env('PAYMENT_DOMAIN', 'http://localhost:9092'),
    'api_key' => env('PAYMENT_API_KEY', 'a761ae5f9-f824-4828-a66a-04d3c77e2bcb'),
    'tenant_id' => env('PAYMENT_TENANT_ID', 1004),
    'context' => env('PAYMENT_CONTEXT', 'Invoice'),
    'status' => env('PAYMENT_STATUS', 'CREATED'),
]; 
