<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Centralized Brand Configuration — ATHAR (أَثَر)
    |--------------------------------------------------------------------------
    */

    'technical_name' => env('BRAND_TECHNICAL_NAME', 'ATHAR'),

    'name' => [
        'ar' => env('BRAND_NAME_AR', 'أَثَر'),
        'en' => env('BRAND_NAME_EN', 'ATHAR'),
    ],

    'wordmark_subtitle' => [
        'ar' => env('BRAND_SUBTITLE_AR', 'مساحة أدبية للاقتباسات'),
        'en' => env('BRAND_SUBTITLE_EN', 'Literary Quote Sanctuary'),
    ],

    'tagline' => [
        'ar' => 'منصة أدبية دافئة لتوثيق ومشاركة أجمل الاقتباسات من الكتب والقراء.',
        'en' => 'A warm editorial sanctuary for curating, reading, and sharing literary quotes.',
    ],

    'default_locale' => 'ar',

    'supported_locales' => ['ar', 'en'],
];