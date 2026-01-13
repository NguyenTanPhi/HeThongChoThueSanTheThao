<?php

return [
    'app_id'    => env('ZALO_APP_ID', ''),
    'key1'=> env('ZALO_KEY1', ''),
    'key2'=> env('ZALO_KEY2', ''),
    'endpoint' => env('ZALO_ENDPOINT', 'https://openapi.zalo.me'),
    'redirect_url' => env('ZALO_REDIRECT_URL', 'http://localhost:5173/ket-qua-thanh-toan'),
];
