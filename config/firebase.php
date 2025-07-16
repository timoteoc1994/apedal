<?php

return [
    'credentials' => [
        'file' => storage_path('app/appedal-ffe02-firebase-adminsdk-fbsvc-98fe6577e7.json'),
    ],
    'database_url' => env('FIREBASE_DATABASE_URL', null),
    'project_id' => env('FIREBASE_PROJECT_ID', null),
];
