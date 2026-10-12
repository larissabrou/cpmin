<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projet Laravel - Migration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 2rem;
            background: #f4f6f8;
            color: #1f2937;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 2rem;
        }
        h1 { margin-top: 0; }
        code {
            background: #f3f4f6;
            border-radius: 6px;
            padding: 0.15rem 0.4rem;
        }
        .actions {
            margin-top: 1.5rem;
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            text-decoration: none;
            padding: 0.65rem 1rem;
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-primary {
            background: #2563eb;
            color: #fff;
        }
        .btn-secondary {
            background: #e5e7eb;
            color: #1f2937;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Plateforme SaaS Laravel prête</h1>
        <p>
            Cette application est préparée comme un SaaS multi-entreprises : chaque entreprise
            dispose de son propre contexte de données et de son tableau de bord isolé.
        </p>
        <p>
            L'authentification à double facteur est aussi prévue dans le schéma de base de données
            (activation, secret, codes de récupération et challenges à usage unique).
        </p>
        <p>
            Les taux sont définis par <strong>opérateur</strong> (Orange Money, MTN, etc.) et par
            <strong>sens de flux</strong> : <code>payin</code> (encaissement) et
            <code>payout</code> (décaissement). Chaque partenaire peut avoir des grilles différentes
            selon l'opérateur et le flux (<code>operators</code>,
            <code>partner_payment_rates</code>).
        </p>
        <p>
            L'ancien template front a été conservé dans <code>legacy-template</code> et copié dans
            <code>public/legacy-src</code> pour référence.
        </p>
        <div class="actions">
            <a class="btn btn-primary" href="/legacy-src/index.html">Ouvrir l'ancien index</a>
            <a class="btn btn-secondary" href="https://laravel.com/docs" target="_blank" rel="noreferrer">Documentation Laravel</a>
        </div>
    </div>
</body>
</html>
