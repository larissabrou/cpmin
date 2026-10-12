<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DocumentationController extends Controller
{
    public function index()
    {
        $company        = Auth::user()?->company;
        $apiBaseUrl     = config('app.api_url', 'https://api.toutransfert.com/v1');
        $apiKeyDisplay  = $company?->gateway_api_key
            ? 'sk_live_' . str_repeat('*', 12) . substr($company->gateway_api_key, -4)
            : 'sk_live_VOTRE_CLÉ';
        $apiKeyForCurl  = $company?->gateway_api_key ?? 'sk_live_VOTRE_CLÉ';
        $brand          = config('app.name', 'TOUTransfert');

        /*
         * Structure navigation :
         *   kind = 'section'      → ouvre un groupe
         *   kind = 'endpoint'     → lien vers endpoint
         *   kind = 'section_end'  → ferme le groupe
         */
        $navigation = [
            // Paiements
            ['kind' => 'section',     'id' => 'section-payments', 'label' => 'Paiements'],
            ['kind' => 'endpoint',    'id' => 'payment-initiate', 'label' => 'Initier',   'method' => 'POST', 'section' => 'section-payments'],
            ['kind' => 'endpoint',    'id' => 'payment-status',   'label' => 'Statut',    'method' => 'GET',  'section' => 'section-payments'],
            ['kind' => 'endpoint',    'id' => 'payment-list',     'label' => 'Historique','method' => 'GET',  'section' => 'section-payments'],
            ['kind' => 'section_end', 'id' => 'section-payments'],

            // Reversements
            ['kind' => 'section',     'id' => 'section-payouts',  'label' => 'Reversements'],
            ['kind' => 'endpoint',    'id' => 'payout-create',    'label' => 'Créer',     'method' => 'POST', 'section' => 'section-payouts'],
            ['kind' => 'endpoint',    'id' => 'payout-status',    'label' => 'Statut',    'method' => 'GET',  'section' => 'section-payouts'],
            ['kind' => 'section_end', 'id' => 'section-payouts'],

            // Compte
            ['kind' => 'section',     'id' => 'section-account',  'label' => 'Compte'],
            ['kind' => 'endpoint',    'id' => 'account-balance',  'label' => 'Solde',     'method' => 'GET',  'section' => 'section-account'],
            ['kind' => 'section_end', 'id' => 'section-account'],
        ];

        /*
         * Structure sections → endpoints
         *
         * Chaque endpoint peut avoir :
         *   method      : GET | POST | PUT | PATCH | DELETE
         *   path        : chemin relatif (ex: /payments/initiate)
         *   id          : anchor HTML unique
         *   description : courte description affichée dans l'en-tête
         *   parameters  : tableau de paramètres (voir ci-dessous)
         *   responses   : tableau de réponses HTTP
         *   curl        : commande cURL brute (optionnel, sinon générée automatiquement)
         *
         * Paramètre :
         *   name        : string
         *   in          : 'body' | 'path' | 'query'
         *   type        : 'string' | 'integer' | 'boolean' | ...
         *   required    : bool
         *   description : string (HTML autorisé)
         *   example     : string|number|null
         *   enum        : array (valeurs autorisées)
         *
         * Réponse :
         *   code        : int (ex: 200, 422)
         *   label       : string
         *   body        : string JSON
         */
        $sections = [

            /* ── PAIEMENTS ─────────────────────────────── */
            [
                'id'       => 'section-payments',
                'title'    => 'Paiements',
                'subtitle' => 'Initiez et suivez les paiements Mobile Money.',
                'endpoints' => [

                    /* POST /payments/initiate */
                    [
                        'method'      => 'POST',
                        'path'        => '/payments/initiate',
                        'id'          => 'payment-initiate',
                        'description' => 'Initie un paiement mobile money',
                        'parameters'  => [
                            ['name' => 'amount',       'in' => 'body', 'type' => 'integer', 'required' => true,  'description' => 'Montant en XOF (entier, min. 100).', 'example' => '5000'],
                            ['name' => 'phone',        'in' => 'body', 'type' => 'string',  'required' => true,  'description' => 'Numéro au format international.', 'example' => '+2250700000000'],
                            ['name' => 'operator',     'in' => 'body', 'type' => 'string',  'required' => true,  'description' => "Opérateur mobile money.", 'enum' => ['orange', 'mtn', 'moov', 'wave']],
                            ['name' => 'order_id',     'in' => 'body', 'type' => 'string',  'required' => true,  'description' => 'Identifiant unique côté marchand (max 64 car.).', 'example' => 'CMD-20250608-001'],
                            ['name' => 'callback_url', 'in' => 'body', 'type' => 'string',  'required' => false, 'description' => 'URL HTTPS pour recevoir les webhooks.', 'example' => 'https://monsite.ci/webhook'],
                            ['name' => 'description',  'in' => 'body', 'type' => 'string',  'required' => false, 'description' => 'Libellé affiché au payeur (max 255 car.).'],
                        ],
                        'responses' => [
                            [
                                'code'  => 200,
                                'label' => 'Paiement initié',
                                'body'  => json_encode([
                                    'status' => 'success',
                                    'data'   => [
                                        'transaction_id' => 'TXN_a8f3k2...',
                                        'order_id'       => 'CMD-20250608-001',
                                        'status'         => 'PENDING',
                                        'amount'         => 5000,
                                        'currency'       => 'XOF',
                                        'created_at'     => '2025-06-08T10:00:00Z',
                                    ],
                                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                            ],
                            [
                                'code'  => 422,
                                'label' => 'Erreur de validation',
                                'body'  => json_encode([
                                    'status'  => 'error',
                                    'message' => 'Validation failed',
                                    'errors'  => [
                                        'amount'   => ['The amount must be at least 100.'],
                                        'operator' => ['Invalid operator.'],
                                    ],
                                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                            ],
                        ],
                    ],

                    /* GET /payments/{order_id}/status */
                    [
                        'method'      => 'GET',
                        'path'        => '/payments/{order_id}/status',
                        'id'          => 'payment-status',
                        'description' => 'Consulte le statut d\'un paiement',
                        'parameters'  => [
                            ['name' => 'order_id', 'in' => 'path', 'type' => 'string', 'required' => true, 'description' => 'Identifiant de commande passé à l\'initiation.', 'example' => 'CMD-20250608-001'],
                        ],
                        'responses' => [
                            [
                                'code'  => 200,
                                'label' => 'Statut récupéré',
                                'body'  => json_encode([
                                    'status' => 'success',
                                    'data'   => [
                                        'transaction_id' => 'TXN_a8f3k2...',
                                        'order_id'       => 'CMD-20250608-001',
                                        'status'         => 'SUCCESS',
                                        'amount'         => 5000,
                                        'phone'          => '+2250700000000',
                                        'operator'       => 'orange',
                                        'paid_at'        => '2025-06-08T10:02:34Z',
                                    ],
                                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                            ],
                            [
                                'code'  => 404,
                                'label' => 'Paiement introuvable',
                                'body'  => json_encode([
                                    'status'  => 'error',
                                    'message' => 'Payment not found.',
                                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                            ],
                        ],
                    ],

                    /* GET /payments */
                    [
                        'method'      => 'GET',
                        'path'        => '/payments',
                        'id'          => 'payment-list',
                        'description' => 'Liste l\'historique des paiements',
                        'parameters'  => [
                            ['name' => 'page',     'in' => 'query', 'type' => 'integer', 'required' => false, 'description' => 'Numéro de page (défaut : 1).', 'example' => '1'],
                            ['name' => 'per_page', 'in' => 'query', 'type' => 'integer', 'required' => false, 'description' => 'Résultats par page (max 100, défaut : 20).'],
                            ['name' => 'status',   'in' => 'query', 'type' => 'string',  'required' => false, 'description' => 'Filtrer par statut.', 'enum' => ['PENDING', 'SUCCESS', 'FAILED']],
                            ['name' => 'from',     'in' => 'query', 'type' => 'string',  'required' => false, 'description' => 'Date début ISO 8601.', 'example' => '2025-06-01'],
                            ['name' => 'to',       'in' => 'query', 'type' => 'string',  'required' => false, 'description' => 'Date fin ISO 8601.', 'example' => '2025-06-30'],
                        ],
                        'responses' => [
                            [
                                'code'  => 200,
                                'label' => 'Liste paginée',
                                'body'  => json_encode([
                                    'status' => 'success',
                                    'data'   => [
                                        'current_page' => 1,
                                        'total'        => 42,
                                        'per_page'     => 20,
                                        'items'        => [
                                            ['transaction_id' => 'TXN_a8f3k2...', 'order_id' => 'CMD-001', 'status' => 'SUCCESS', 'amount' => 5000],
                                        ],
                                    ],
                                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                            ],
                        ],
                    ],
                ],
            ],

            /* ── REVERSEMENTS ──────────────────────────── */
            [
                'id'       => 'section-payouts',
                'title'    => 'Reversements',
                'subtitle' => 'Envoyez des fonds vers un compte Mobile Money.',
                'endpoints' => [

                    /* POST /payouts */
                    [
                        'method'      => 'POST',
                        'path'        => '/payouts',
                        'id'          => 'payout-create',
                        'description' => 'Crée un reversement (payout)',
                        'parameters'  => [
                            ['name' => 'amount',   'in' => 'body', 'type' => 'integer', 'required' => true,  'description' => 'Montant en XOF.', 'example' => '10000'],
                            ['name' => 'phone',    'in' => 'body', 'type' => 'string',  'required' => true,  'description' => 'Numéro destinataire international.', 'example' => '+2250100000000'],
                            ['name' => 'operator', 'in' => 'body', 'type' => 'string',  'required' => true,  'description' => 'Opérateur du destinataire.', 'enum' => ['orange', 'mtn', 'moov', 'wave']],
                            ['name' => 'ref',      'in' => 'body', 'type' => 'string',  'required' => true,  'description' => 'Référence unique de payout.', 'example' => 'PYT-20250608-001'],
                        ],
                        'responses' => [
                            [
                                'code'  => 200,
                                'label' => 'Payout initié',
                                'body'  => json_encode([
                                    'status' => 'success',
                                    'data'   => [
                                        'payout_id'  => 'PYT_b7c2m...',
                                        'ref'        => 'PYT-20250608-001',
                                        'status'     => 'PENDING',
                                        'amount'     => 10000,
                                        'currency'   => 'XOF',
                                    ],
                                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                            ],
                        ],
                    ],

                    /* GET /payouts/{ref}/status */
                    [
                        'method'      => 'GET',
                        'path'        => '/payouts/{ref}/status',
                        'id'          => 'payout-status',
                        'description' => 'Statut d\'un reversement',
                        'parameters'  => [
                            ['name' => 'ref', 'in' => 'path', 'type' => 'string', 'required' => true, 'description' => 'Référence du payout.', 'example' => 'PYT-20250608-001'],
                        ],
                        'responses' => [
                            [
                                'code'  => 200,
                                'label' => 'Statut récupéré',
                                'body'  => json_encode([
                                    'status' => 'success',
                                    'data'   => ['payout_id' => 'PYT_b7c2m...', 'status' => 'SUCCESS', 'processed_at' => '2025-06-08T10:05:00Z'],
                                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                            ],
                        ],
                    ],
                ],
            ],

            /* ── COMPTE ────────────────────────────────── */
            [
                'id'       => 'section-account',
                'title'    => 'Compte',
                'subtitle' => 'Informations sur votre compte marchand.',
                'endpoints' => [

                    /* GET /account/balance */
                    [
                        'method'      => 'GET',
                        'path'        => '/account/balance',
                        'id'          => 'account-balance',
                        'description' => 'Consulte le solde disponible',
                        'parameters'  => [],
                        'responses'   => [
                            [
                                'code'  => 200,
                                'label' => 'Solde récupéré',
                                'body'  => json_encode([
                                    'status' => 'success',
                                    'data'   => ['balance' => 250000, 'currency' => 'XOF', 'updated_at' => '2025-06-08T09:59:00Z'],
                                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return view('documentation', compact(
            'navigation',
            'sections',
            'apiBaseUrl',
            'apiKeyDisplay',
            'apiKeyForCurl',
            'company',
            'brand',
        ));
    }
}
