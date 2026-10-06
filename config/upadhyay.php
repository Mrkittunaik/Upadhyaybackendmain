<?php

// Project specific settings.
return [
    'branch_code_length' => 5,
    'phone_unique' => false, // set true if phone numbers must be unique
    'pagination' => ['default' => 20, 'max' => 100],
    'uploads' => [
        // Max size in KB and allowed extensions per file type.
        'profile_photo' => ['max_kb' => 2048, 'ext' => ['jpg', 'jpeg', 'png', 'webp']],
        'company_logo' => ['max_kb' => 2048, 'ext' => ['jpg', 'jpeg', 'png', 'webp']],
        'job_image' => ['max_kb' => 4096, 'ext' => ['jpg', 'jpeg', 'png', 'webp']],
        'resume' => ['max_kb' => 5120, 'ext' => ['pdf', 'doc', 'docx']],
        'certificate' => ['max_kb' => 5120, 'ext' => ['pdf', 'jpg', 'jpeg', 'png']],
        'document' => ['max_kb' => 10240, 'ext' => ['pdf', 'doc', 'docx', 'xls', 'xlsx']],
        'video' => ['max_kb' => 51200, 'ext' => ['mp4', 'webm']],
    ],
];
