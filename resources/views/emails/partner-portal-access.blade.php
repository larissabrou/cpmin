@php
    $brand = config('aggregpay.brand', 'AggregPay');
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Vos accès {{ $brand }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #1e293b; max-width: 560px; margin: 0 auto; padding: 24px;">
    <h1 style="font-size: 20px; margin-bottom: 8px;">Bienvenue sur le portail {{ $brand }}</h1>
    <p>Un accès au dashboard du projet <strong>{{ $company->name }}</strong> vient d'être créé pour vous.</p>

    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin: 20px 0;">
        <p style="margin: 0 0 8px;"><strong>URL de connexion</strong><br>
            <a href="{{ $loginUrl }}" style="color: #4f46e5;">{{ $loginUrl }}</a>
        </p>
        <p style="margin: 0 0 8px;"><strong>Identifiant</strong><br>{{ $user->email }}</p>
        <p style="margin: 0;"><strong>Mot de passe temporaire</strong><br><code style="background: #fff; padding: 2px 6px; border-radius: 4px;">{{ $plainPassword }}</code></p>
        <p style="margin: 12px 0 0; font-size: 14px; color: #64748b;">Changez ce mot de passe dès votre première connexion.</p>
    </div>

    <p>Depuis le portail, vous pourrez consulter vos transactions, gérer votre clé API et accéder à la documentation d'intégration.</p>

    <p style="font-size: 13px; color: #64748b; margin-top: 24px;">— L'équipe {{ $brand }}</p>
</body>
</html>
