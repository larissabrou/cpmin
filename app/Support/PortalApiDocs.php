<?php

namespace App\Support;

class PortalApiDocs
{
    /**
     * @return list<array{id: string, title: string, endpoints: list<array<string, mixed>>}>
     */
    public static function sections(): array
    {
        return [
            [
                'id' => 'payins',
                'title' => 'Encaissements (Payin)',
                'subtitle' => 'Créer, consulter et suivre les encaissements Mobile Money.',
                'endpoints' => [
                    self::payinCreate(),
                    self::payinStatus(),
                    self::payinList(),
                ],
            ],
            [
                'id' => 'payouts',
                'title' => 'Décaissements (Payout)',
                'subtitle' => 'Envoyer des fonds vers un numéro Mobile Money (virement sortant).',
                'endpoints' => [
                    self::payoutCreate(),
                    self::payoutList(),
                ],
            ],
            [
                'id' => 'webhooks',
                'title' => 'Webhooks',
                'subtitle' => 'Recevoir les événements terminaux signés HMAC sur votre URL.',
                'endpoints' => [
                    self::webhooks(),
                ],
            ],
        ];
    }

    /**
     * Navigation sidebar (format portal-docs-v8).
     *
     * @return list<array<string, mixed>>
     */
    public static function navigation(): array
    {
        $nav = [];

        foreach (self::sections() as $section) {
            $nav[] = ['kind' => 'section', 'id' => $section['id'], 'label' => $section['title']];
            foreach ($section['endpoints'] as $endpoint) {
                $nav[] = [
                    'kind' => 'endpoint',
                    'id' => $endpoint['id'],
                    'label' => $endpoint['title'],
                    'method' => $endpoint['method'],
                    'section' => $section['id'],
                ];
            }
            $nav[] = ['kind' => 'section_end', 'id' => $section['id']];
        }

        return $nav;
    }

    /**
     * @return array<string, mixed>
     */
    private static function payinCreate(): array
    {
        return [
            'id' => 'post-payments',
            'title' => 'Créer un payin',
            'method' => 'POST',
            'path' => '/payments',
            'description' => 'Initie un encaissement Mobile Money. Réponse à la racine JSON (sans clé data). Idempotent sur order_id en SUCCESS/PENDING. Orange, Moov et MTN partagent la même structure de requête ; le client valide sur son téléphone via le code USSD de son opérateur.',
            'fields' => [
                ['name' => 'order_id', 'type' => 'string', 'required' => true, 'description' => 'Référence unique côté partenaire (max 64 car.)'],
                ['name' => 'amount', 'type' => 'integer', 'required' => true, 'description' => 'Montant en XOF (minimum 200)'],
                ['name' => 'operator', 'type' => 'string', 'required' => true, 'description' => 'orange, moov, mtn ou wave. Orange, Moov et MTN utilisent la même structure de requête.'],
                ['name' => 'phone_number', 'type' => 'string', 'required' => true, 'description' => 'Numéro du payeur (format local, ex. CI)'],
                ['name' => 'country', 'type' => 'string', 'required' => true, 'description' => 'Code ISO pays (ex. CI)'],
                ['name' => 'currency', 'type' => 'string', 'required' => false, 'description' => 'Défaut XOF'],
                ['name' => 'success_url', 'type' => 'url', 'required' => 'conditional', 'condition' => 'Wave', 'description' => 'URL de retour après paiement réussi'],
                ['name' => 'cancel_url', 'type' => 'url', 'required' => 'conditional', 'condition' => 'Wave', 'description' => 'URL de retour si annulation'],
                ['name' => 'metadata', 'type' => 'object', 'required' => false, 'description' => 'Données libres optionnelles (ex. invoice_id). Applicable à tous les payins ; renvoyées au webhook.'],
            ],
            'operator_flows' => [
                [
                    'operator' => 'Orange',
                    'ussd' => '#120#',
                    'instruction' => 'Le client compose #120# et suit les instructions pour valider sa transaction.',
                ],
                [
                    'operator' => 'Moov',
                    'ussd' => '*155*15#',
                    'instruction' => 'Le client compose *155*15# pour valider sa transaction.',
                ],
                [
                    'operator' => 'MTN',
                    'ussd' => '*133#',
                    'instruction' => 'Le client compose *133# puis suit les instructions pour valider sa transaction.',
                ],
            ],
            'curl_examples' => [
                [
                    'label' => 'Orange · Moov · MTN',
                    'curl' => <<<'CURL'
# orange: 0701208509 | moov: 0171940341 | mtn: 0506169325 — adapter operator et phone_number.
curl -X POST {{BASE_URL}}/payments \
  -H "Authorization: Bearer {{API_KEY}}" \
  -H "Content-Type: application/json" \
  -d '{
    "order_id": "PAY-10001",
    "amount": 5000,
    "operator": "orange",
    "phone_number": "0701208509",
    "country": "CI",
    "metadata": { "invoice_id": 42 }
  }'
CURL,
                ],
                [
                    'label' => 'Wave (redirect)',
                    'curl' => <<<'CURL'
curl -X POST {{BASE_URL}}/payments \
  -H "Authorization: Bearer {{API_KEY}}" \
  -H "Content-Type: application/json" \
  -d '{
    "order_id": "PAY-10003",
    "amount": 5000,
    "operator": "wave",
    "phone_number": "0701208509",
    "country": "CI",
    "success_url": "https://votre-app.com/paiement/succes",
    "cancel_url": "https://votre-app.com/paiement/annule",
    "metadata": { "invoice_id": 42 }
  }'
CURL,
                ],
            ],
            'responses' => [
                [
                    'label' => '202 — En attente',
                    'code' => 202,
                    'badge' => 'pending',
                    'body' => <<<'JSON'
{
  "success": true,
  "order_id": "PAY-10001",
  "aggregator": "aggregpay",
  "status": "PENDING",
  "amount": 5000,
  "fee_rate_percent": 2,
  "fee_amount": 100,
  "net_amount": 4900,
  "currency": "XOF",
  "country": "CI",
  "operator": "orange",
  "phone_number": "0701208509",
  "metadata": { "invoice_id": 42 },
  "created_at": "2026-06-01T18:53:32+00:00"
}
JSON,
                ],
                [
                    'label' => '200 — Succès immédiat',
                    'code' => 200,
                    'badge' => 'success',
                    'body' => <<<'JSON'
{
  "success": true,
  "order_id": "PAY-10001",
  "aggregator": "aggregpay",
  "status": "SUCCESS",
  "amount": 5000,
  "fee_rate_percent": 2,
  "fee_amount": 100,
  "net_amount": 4900,
  "currency": "XOF",
  "country": "CI",
  "operator": "orange",
  "phone_number": "0701208509",
  "metadata": { "invoice_id": 42 },
  "created_at": "2026-06-01T18:53:32+00:00"
}
JSON,
                ],
                [
                    'label' => '200 — Wave (lien redirect)',
                    'code' => 200,
                    'badge' => 'pending',
                    'body' => <<<'JSON'
{
  "success": true,
  "order_id": "PAY-10003",
  "aggregator": "aggregpay",
  "status": "PENDING",
  "amount": 5000,
  "fee_rate_percent": 2,
  "fee_amount": 100,
  "net_amount": 4900,
  "currency": "XOF",
  "country": "CI",
  "operator": "wave",
  "phone_number": "0701208509",
  "metadata": {},
  "created_at": "2026-06-01T19:05:35+00:00",
  "provider_link": "https://pay.wave.com/c/…"
}
JSON,
                ],
                [
                    'label' => '422 — Échec',
                    'code' => 422,
                    'badge' => 'failed',
                    'body' => <<<'JSON'
{
  "success": false,
  "order_id": "PAY-10001",
  "aggregator": "aggregpay",
  "status": "FAILED",
  "amount": 5000,
  "fee_rate_percent": 2,
  "fee_amount": 100,
  "net_amount": 4900,
  "currency": "XOF",
  "country": "CI",
  "operator": "orange",
  "phone_number": "0701208509",
  "metadata": { "invoice_id": 42 },
  "created_at": "2026-06-01T18:53:32+00:00",
  "message": "Paiement refusé par le prestataire."
}
JSON,
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function payinStatus(): array
    {
        return [
            'id' => 'get-payment-status',
            'title' => 'Statut payin / payout',
            'method' => 'GET',
            'path' => '/payments/{order_id}/status',
            'description' => 'Interroge le prestataire en temps réel et met à jour le statut en base. Même route pour payins et payouts (champ type, is_terminal). Préférez cette route au polling. Optionnel : GET /payouts/{order_id} ou GET /payments/{order_id} renvoient un snapshot base sans re-interroger le prestataire.',
            'fields' => [
                ['name' => 'order_id', 'type' => 'path', 'required' => true, 'description' => 'Référence payin (PAY-…) ou payout (PO-…)'],
            ],
            'curl_examples' => [
                [
                    'label' => 'Payin',
                    'curl' => <<<'CURL'
curl -X GET {{BASE_URL}}/payments/PAY-10001/status \
  -H "Authorization: Bearer {{API_KEY}}"
CURL,
                ],
                [
                    'label' => 'Payout',
                    'curl' => <<<'CURL'
curl -X GET {{BASE_URL}}/payments/PO-17803/status \
  -H "Authorization: Bearer {{API_KEY}}"
CURL,
                ],
            ],
            'responses' => [
                [
                    'label' => '200 — Payin réussi',
                    'code' => 200,
                    'badge' => 'success',
                    'body' => <<<'JSON'
{
  "success": true,
  "order_id": "PAY-10001",
  "aggregator": "aggregpay",
  "status": "SUCCESS",
  "amount": 5000,
  "fee_rate_percent": 2,
  "fee_amount": 100,
  "net_amount": 4900,
  "currency": "XOF",
  "country": "CI",
  "operator": "orange",
  "phone_number": "0701208509",
  "metadata": { "invoice_id": 42 },
  "created_at": "2026-06-01T18:53:32+00:00",
  "type": "payin",
  "is_terminal": true
}
JSON,
                ],
                [
                    'label' => '200 — En attente (Wave)',
                    'code' => 200,
                    'badge' => 'pending',
                    'body' => <<<'JSON'
{
  "success": true,
  "order_id": "PAY-10003",
  "aggregator": "aggregpay",
  "status": "PENDING",
  "amount": 5000,
  "fee_rate_percent": 2,
  "fee_amount": 100,
  "net_amount": 4900,
  "currency": "XOF",
  "country": "CI",
  "operator": "wave",
  "phone_number": "0701208509",
  "metadata": {},
  "created_at": "2026-06-01T19:05:35+00:00",
  "provider_link": "https://pay.wave.com/c/…",
  "type": "payin",
  "is_terminal": false
}
JSON,
                ],
                [
                    'label' => '200 — Payout réussi',
                    'code' => 200,
                    'badge' => 'success',
                    'body' => <<<'JSON'
{
  "success": true,
  "order_id": "PO-10001",
  "aggregator": "aggregpay",
  "status": "SUCCESS",
  "amount": 5000,
  "fee_rate_percent": 1.5,
  "fee_amount": 75,
  "total_debited": 5075,
  "currency": "XOF",
  "country": "CI",
  "operator": "mtn",
  "phone_number": "0506169325",
  "metadata": {},
  "created_at": "2026-06-01T18:58:00+00:00",
  "type": "payout",
  "is_terminal": true
}
JSON,
                ],
                [
                    'label' => '422 — Échec terminal',
                    'code' => 422,
                    'badge' => 'failed',
                    'body' => <<<'JSON'
{
  "success": false,
  "order_id": "PAY-10001",
  "aggregator": "aggregpay",
  "status": "FAILED",
  "amount": 5000,
  "fee_rate_percent": 2,
  "fee_amount": 100,
  "net_amount": 4900,
  "currency": "XOF",
  "country": "CI",
  "operator": "orange",
  "phone_number": "0701208509",
  "metadata": {},
  "created_at": "2026-06-01T18:53:32+00:00",
  "message": "Paiement refusé par le prestataire.",
  "type": "payin",
  "is_terminal": true
}
JSON,
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function payinList(): array
    {
        return [
            'id' => 'get-payments',
            'title' => 'Lister les payins',
            'method' => 'GET',
            'path' => '/payments',
            'description' => 'Liste paginée. Filtres : status, country, operator, date_from, date_to, q (référence), page, per_page (max 100).',
            'fields' => [
                ['name' => 'status', 'type' => 'query', 'required' => false, 'description' => 'PENDING, SUCCESS, FAILED, CANCELLED'],
                ['name' => 'country', 'type' => 'query', 'required' => false, 'description' => 'Code pays ISO'],
                ['name' => 'date_from', 'type' => 'query', 'required' => false, 'description' => 'Date début (YYYY-MM-DD)'],
                ['name' => 'date_to', 'type' => 'query', 'required' => false, 'description' => 'Date fin (YYYY-MM-DD)'],
                ['name' => 'q', 'type' => 'query', 'required' => false, 'description' => 'Recherche par référence (order_id)'],
                ['name' => 'page', 'type' => 'query', 'required' => false, 'description' => 'Page (défaut 1)'],
                ['name' => 'per_page', 'type' => 'query', 'required' => false, 'description' => 'Éléments par page (max 100)'],
            ],
            'curl_examples' => [
                [
                    'label' => 'Requête',
                    'curl' => <<<'CURL'
curl -X GET "{{BASE_URL}}/payments?status=SUCCESS&date_from=2026-06-01&date_to=2026-06-30&per_page=20" \
  -H "Authorization: Bearer {{API_KEY}}"
CURL,
                ],
            ],
            'responses' => [
                [
                    'label' => '200 — Liste paginée',
                    'code' => 200,
                    'badge' => 'success',
                    'body' => <<<'JSON'
{
  "success": true,
  "data": {
    "current_page": 1,
    "per_page": 20,
    "total": 42,
    "data": [
      {
        "order_id": "PAY-10001",
        "aggregator": "aggregpay",
        "status": "SUCCESS",
        "amount": 5000,
        "fee_rate_percent": 2,
        "fee_amount": 100,
        "net_amount": 4900,
        "currency": "XOF",
        "country": "CI",
        "operator": "orange",
        "phone_number": "0701208509",
        "metadata": { "invoice_id": 42 },
        "created_at": "2026-06-01T18:53:32+00:00"
      }
    ]
  }
}
JSON,
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function payoutCreate(): array
    {
        return [
            'id' => 'post-payouts',
            'title' => 'Créer un payout',
            'method' => 'POST',
            'path' => '/payouts',
            'description' => 'Virement sortant vers un wallet Mobile Money. Réponse dans la clé data. total_debited = amount + fee_amount.',
            'fields' => [
                ['name' => 'order_id', 'type' => 'string', 'required' => true, 'description' => 'Référence unique (ex. PO-17803)'],
                ['name' => 'amount', 'type' => 'integer', 'required' => true, 'description' => 'Montant crédité au bénéficiaire (min. 200 XOF)'],
                ['name' => 'operator', 'type' => 'string', 'required' => true, 'description' => 'orange, moov, mtn ou wave'],
                ['name' => 'phone_number', 'type' => 'string', 'required' => true, 'description' => 'Numéro du bénéficiaire'],
                ['name' => 'country', 'type' => 'string', 'required' => true, 'description' => 'Code ISO (ex. CI)'],
                ['name' => 'currency', 'type' => 'string', 'required' => false, 'description' => 'Défaut XOF'],
                ['name' => 'metadata', 'type' => 'object', 'required' => false, 'description' => 'Données libres'],
            ],
            'curl_examples' => [
                [
                    'label' => 'Exemple Wave',
                    'curl' => <<<'CURL'
curl -X POST {{BASE_URL}}/payouts \
  -H "Authorization: Bearer {{API_KEY}}" \
  -H "Content-Type: application/json" \
  -d '{
    "order_id": "PO-17803",
    "amount": 5000,
    "operator": "wave",
    "phone_number": "0701208509",
    "country": "CI",
    "metadata": { "beneficiary": "Jean Dupont" }
  }'
CURL,
                ],
            ],
            'responses' => [
                [
                    'label' => '202 — En attente',
                    'code' => 202,
                    'badge' => 'pending',
                    'body' => <<<'JSON'
{
  "success": true,
  "data": {
    "order_id": "PO-17803",
    "aggregator": "aggregpay",
    "status": "PENDING",
    "amount": 5000,
    "fee_rate_percent": 1.5,
    "fee_amount": 75,
    "total_debited": 5075,
    "currency": "XOF",
    "country": "CI",
    "operator": "wave",
    "phone_number": "0701208509",
    "metadata": { "beneficiary": "Jean Dupont" },
    "created_at": "2026-06-02T21:07:44+00:00"
  }
}
JSON,
                ],
                [
                    'label' => '200 — Succès immédiat',
                    'code' => 200,
                    'badge' => 'success',
                    'body' => <<<'JSON'
{
  "success": true,
  "data": {
    "order_id": "PO-17803",
    "aggregator": "aggregpay",
    "status": "SUCCESS",
    "amount": 5000,
    "fee_rate_percent": 1.5,
    "fee_amount": 75,
    "total_debited": 5075,
    "currency": "XOF",
    "country": "CI",
    "operator": "wave",
    "phone_number": "0701208509",
    "metadata": { "beneficiary": "Jean Dupont" },
    "created_at": "2026-06-02T21:07:44+00:00"
  }
}
JSON,
                ],
                [
                    'label' => '422 — Échec prestataire',
                    'code' => 422,
                    'badge' => 'failed',
                    'body' => <<<'JSON'
{
  "success": false,
  "data": {
    "order_id": "PO-17803",
    "aggregator": "aggregpay",
    "status": "FAILED",
    "amount": 5000,
    "fee_rate_percent": 1.5,
    "fee_amount": 75,
    "total_debited": 5075,
    "currency": "XOF",
    "country": "CI",
    "operator": "wave",
    "phone_number": "0701208509",
    "metadata": { "beneficiary": "Jean Dupont" },
    "created_at": "2026-06-02T21:07:44+00:00",
    "message": "Solde insuffisant."
  }
}
JSON,
                ],
                [
                    'label' => '422 — Validation',
                    'code' => 422,
                    'badge' => 'failed',
                    'body' => <<<'JSON'
{
  "success": false,
  "message": "validation.unique",
  "errors": {
    "order_id": ["validation.unique"]
  }
}
JSON,
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function payoutList(): array
    {
        return [
            'id' => 'get-payouts',
            'title' => 'Lister les payouts',
            'method' => 'GET',
            'path' => '/payouts',
            'description' => 'Liste paginée des décaissements avec les mêmes filtres que les payins.',
            'fields' => [
                ['name' => 'status', 'type' => 'query', 'required' => false, 'description' => 'PENDING, SUCCESS, FAILED'],
                ['name' => 'country', 'type' => 'query', 'required' => false, 'description' => 'Code pays ISO'],
                ['name' => 'date_from', 'type' => 'query', 'required' => false, 'description' => 'Date début'],
                ['name' => 'date_to', 'type' => 'query', 'required' => false, 'description' => 'Date fin'],
                ['name' => 'q', 'type' => 'query', 'required' => false, 'description' => 'Recherche par référence'],
                ['name' => 'page', 'type' => 'query', 'required' => false, 'description' => 'Page'],
                ['name' => 'per_page', 'type' => 'query', 'required' => false, 'description' => 'Max 100'],
            ],
            'curl_examples' => [
                [
                    'label' => 'Requête',
                    'curl' => <<<'CURL'
curl -X GET "{{BASE_URL}}/payouts?status=PENDING&per_page=20" \
  -H "Authorization: Bearer {{API_KEY}}"
CURL,
                ],
            ],
            'responses' => [
                [
                    'label' => '200 — Liste',
                    'code' => 200,
                    'badge' => 'success',
                    'body' => <<<'JSON'
{
  "success": true,
  "data": {
    "current_page": 1,
    "total": 5,
    "data": [ {
      "order_id": "PO-17803",
      "aggregator": "aggregpay",
      "status": "PENDING",
      "amount": 5000,
      "fee_rate_percent": 1.5,
      "fee_amount": 75,
      "total_debited": 5075,
      "currency": "XOF",
      "country": "CI",
      "operator": "wave",
      "phone_number": "0701208509",
      "metadata": {},
      "created_at": "2026-06-02T21:07:44+00:00"
    } ]
  }
}
JSON,
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function webhooks(): array
    {
        return [
            'id' => 'webhooks',
            'title' => 'Notifications webhook',
            'method' => 'POST',
            'path' => '(votre URL configurée)',
            'hide_curl' => true,
            'default_tab' => 'response',
            'description' => 'AggregPay envoie un POST sur votre Webhook URL (configurable dans Clé API). Header `X-Gateway-Signature` = HMAC-SHA256 du corps JSON (secret webhook). Répondez HTTP 200 rapidement (< 5 s).',
            'fields' => [
                ['name' => 'X-Gateway-Signature', 'type' => 'header', 'required' => true, 'description' => 'HMAC-SHA256 du corps JSON brut'],
                ['name' => 'event', 'type' => 'string', 'required' => true, 'description' => 'payment.succeeded, payment.failed, payout.succeeded ou payout.failed'],
                ['name' => 'order_id', 'type' => 'string', 'required' => true, 'description' => 'Référence transaction'],
                ['name' => 'status', 'type' => 'string', 'required' => true, 'description' => 'SUCCESS ou FAILED'],
                ['name' => 'type', 'type' => 'string', 'required' => true, 'description' => 'payin ou payout'],
                ['name' => 'amount', 'type' => 'integer', 'required' => true, 'description' => 'Montant transaction'],
                ['name' => 'operator', 'type' => 'string', 'required' => true, 'description' => 'orange, moov, mtn ou wave'],
                ['name' => 'phone_number', 'type' => 'string', 'required' => true, 'description' => 'Numéro Mobile Money'],
                ['name' => 'fee_rate_percent', 'type' => 'number', 'required' => false, 'description' => 'Taux de commission (%)'],
                ['name' => 'fee_amount', 'type' => 'integer', 'required' => false, 'description' => 'Montant des frais (XOF)'],
                ['name' => 'net_amount', 'type' => 'integer', 'required' => false, 'description' => 'Montant net crédité (payin)'],
                ['name' => 'total_debited', 'type' => 'integer', 'required' => false, 'description' => 'Montant total débité (payout)'],
                ['name' => 'metadata', 'type' => 'object', 'required' => false, 'description' => 'Métadonnées transmises à la création'],
                ['name' => 'message', 'type' => 'string', 'required' => false, 'description' => 'Raison d\'échec (si FAILED)'],
            ],
            'responses' => [
                [
                    'label' => 'payment.succeeded',
                    'code' => 200,
                    'badge' => 'success',
                    'body' => <<<'JSON'
{
  "event": "payment.succeeded",
  "aggregator": "aggregpay",
  "order_id": "PAY-10001",
  "status": "SUCCESS",
  "type": "payin",
  "amount": 5000,
  "operator": "orange",
  "phone_number": "0701208509",
  "fee_rate_percent": 2,
  "fee_amount": 100,
  "net_amount": 4900,
  "metadata": { "invoice_id": 42 }
}
JSON,
                ],
                [
                    'label' => 'payment.failed',
                    'code' => 200,
                    'badge' => 'failed',
                    'body' => <<<'JSON'
{
  "event": "payment.failed",
  "aggregator": "aggregpay",
  "order_id": "PAY-10001",
  "status": "FAILED",
  "type": "payin",
  "amount": 5000,
  "operator": "orange",
  "phone_number": "0701208509",
  "fee_rate_percent": 2,
  "fee_amount": 100,
  "net_amount": 4900,
  "metadata": { "invoice_id": 42 },
  "message": "Paiement refusé par le prestataire."
}
JSON,
                ],
                [
                    'label' => 'payout.succeeded',
                    'code' => 200,
                    'badge' => 'success',
                    'body' => <<<'JSON'
{
  "event": "payout.succeeded",
  "aggregator": "aggregpay",
  "order_id": "PO-17803",
  "status": "SUCCESS",
  "type": "payout",
  "amount": 5000,
  "operator": "wave",
  "phone_number": "0701208509",
  "fee_rate_percent": 1.5,
  "fee_amount": 75,
  "total_debited": 5075,
  "metadata": { "beneficiary": "Jean Dupont" }
}
JSON,
                ],
                [
                    'label' => 'payout.failed',
                    'code' => 200,
                    'badge' => 'failed',
                    'body' => <<<'JSON'
{
  "event": "payout.failed",
  "aggregator": "aggregpay",
  "order_id": "PO-17803",
  "status": "FAILED",
  "type": "payout",
  "amount": 5000,
  "operator": "wave",
  "phone_number": "0701208509",
  "fee_rate_percent": 1.5,
  "fee_amount": 75,
  "total_debited": 5075,
  "metadata": { "beneficiary": "Jean Dupont" },
  "message": "Solde insuffisant."
}
JSON,
                ],
            ],
        ];
    }

    public static function interpolate(string $text, string $baseUrl, string $apiKey): string
    {
        return str_replace(
            ['{{BASE_URL}}', '{{API_KEY}}'],
            [$baseUrl, $apiKey],
            $text,
        );
    }
}
