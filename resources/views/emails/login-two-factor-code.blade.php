@php
    $brand = config('aggregpay.brand', 'AggregPay');
    $ttlMinutes = (int) (config('security.email_2fa_ttl_seconds', 900) / 60);
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Code de connexion</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #1e293b;">
    <p>Bonjour {{ $user->name ?? $user->email }},</p>
    <p>Voici votre code à <strong>6 chiffres</strong> pour finaliser votre connexion au portail {{ $brand }} :</p>
    <p style="font-size: 28px; font-weight: 700; letter-spacing: 0.2em; color: #4f46e5;">{{ $code }}</p>
    <p style="font-size: 14px; color: #64748b;">Ce code expire dans {{ $ttlMinutes }} minutes. Si vous n'êtes pas à l'origine de cette demande, ignorez ce message.</p>
    <p>— {{ $brand }}</p>
</body>
</html>
