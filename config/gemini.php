<?php

return [
    'project_id' => env('GOOGLE_CLOUD_PROJECT', ''),
    'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
    'location' => env('GEMINI_LOCATION', 'us-central1'),
    'timeout' => (int) env('GEMINI_TIMEOUT', 30),
];
