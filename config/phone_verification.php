<?php

return [
    'default_duration_minutes' => (int) env('PHONE_VERIFICATION_DEFAULT_DURATION', 15),
    'min_duration_minutes' => (int) env('PHONE_VERIFICATION_MIN_DURATION', 5),
    'max_duration_minutes' => (int) env('PHONE_VERIFICATION_MAX_DURATION', 50),
];
