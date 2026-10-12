<?php

return [

    'email_2fa_enabled' => env('AUTH_EMAIL_2FA', true),

    'email_2fa_ttl_seconds' => (int) env('AUTH_EMAIL_2FA_TTL', 900),

];
