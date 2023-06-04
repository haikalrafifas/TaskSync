<?php

return [
    "title" => "TaskSync",
    "uri" => [
        "root" => "/",
        "auth" => "auth/login",
        "app" => "app"
    ],
    "controller" => "app",
    "method" => "index",
    "cookiename" => "TS",
    "api" => [
        "domain" => "<DOMAIN>",
        "username" => "<USERNAME>",
        "password" => "<PASSWORD>",
        "login" => "login/index.php",
        "logout" => "login/logout.php",
        "service" => "lib/ajax/service.php",
        "action" => "core_course_get_enrolled_courses_by_timeline_classification"
    ]
];
