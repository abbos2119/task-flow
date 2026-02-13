<?php

declare(strict_types=1);

return [
    'expires_minutes' => (int) env('OTP_EXPIRES_MINUTES', 5),
    'rate_limit_minutes' => (int) env('OTP_RATE_LIMIT_MINUTES', 1),
    'code_length' => 6,
];
