@extends('layouts.docs')

@section('title', 'Documentation API')

@section('content')
<div class="docs-wrap">

    <aside class="docs-sidebar" id="docs-sidebar">
        <div class="sidebar-brand">
            <div class="brand-dot"></div>
            <span class="brand-name">API {{ $brand }}</span>
        </div>

        <nav class="sidebar-nav" id="docs-nav">
            <div class="sidebar-section">
                <div class="sidebar-label">Général</div>
                <a href="#demarrage" class="nav-item active" data-section="demarrage">
                    <span class="nav-label">Démarrage rapide</span>
                </a>
                <a href="#statuts" class="nav-item" data-section="statuts">
                    <span class="nav-label">Statuts transaction</span>
                </a>
            </div>

            @foreach($navigation as $item)
                @if($item['kind'] === 'section')
                    <div class="sidebar-section">
                        <div class="sidebar-label">{{ $item['label'] }}</div>
                @elseif($item['kind'] === 'endpoint')
                        <a href="#{{ $item['id'] }}"
                           class="nav-item"
                           data-section="{{ $item['id'] }}"
                           data-method="{{ $item['method'] ?? '' }}">
                            @if(!empty($item['method']))
                                <span class="method-badge badge-{{ strtolower($item['method']) }}">
                                    {{ $item['method'] }}
                                </span>
                            @endif
                            <span class="nav-label">{{ $item['label'] }}</span>
                        </a>
                @elseif($item['kind'] === 'section_end')
                    </div>
                @endif
            @endforeach
        </nav>

        <div class="sidebar-footer">
            <a href="{{ route('dashboard') }}" class="sidebar-cta sidebar-cta--secondary">
                <i class="bx bx-arrow-back" aria-hidden="true"></i>
                Retour au portail
            </a>
            <a href="{{ route('api.credentials') }}" class="sidebar-cta">
                <i class="bx bx-key" aria-hidden="true"></i>
                Mes identifiants
            </a>
        </div>
    </aside>

    <main class="docs-main" id="docs-main">
        <div class="docs-topbar">
            <button class="docs-mobile-toggle" id="docs-mobile-toggle" aria-label="Menu" type="button">
                <i class="bx bx-menu"></i>
            </button>
            <div class="topbar-search">
                <i class="bx bx-search" aria-hidden="true"></i>
                <input type="text" id="docs-search" placeholder="Rechercher un endpoint…" autocomplete="off">
            </div>
            <nav class="topbar-breadcrumb" aria-label="Fil d'ariane">
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <span>/</span>
                <span id="breadcrumb-current">Documentation</span>
            </nav>
        </div>

        <div class="docs-content">
            <section class="docs-section" id="demarrage">
                <div class="section-header">
                    <h1 class="section-title">Documentation API</h1>
                    <p class="section-sub">
                        Référence des endpoints, paramètres et exemples cURL. Tous les appels utilisent HTTPS,
                        l'authentification Bearer et les montants en XOF (entiers).
                    </p>
                </div>

                <div class="quick-start-grid">
                    <div class="quick-card">
                        <span class="quick-label">URL de base</span>
                        <div class="quick-value">
                            <code id="val-base-url">{{ $apiBaseUrl }}</code>
                            <button type="button" class="btn-copy" data-copy="val-base-url" title="Copier">
                                <i class="bx bx-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div class="quick-card">
                        <span class="quick-label">Authentification</span>
                        <div class="quick-value">
                            <code id="val-auth">Authorization: Bearer {{ $apiKeyDisplay }}</code>
                            @if($company?->gateway_api_key)
                                <button type="button" class="btn-copy"
                                        data-copy-value="{{ $company->gateway_api_key }}"
                                        title="Copier la clé">
                                    <i class="bx bx-copy"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="quick-card quick-card--full">
                    <span class="quick-label">En-têtes requis</span>
                    <div class="headers-block">
                        <code>Authorization: Bearer VOTRE_CLÉ</code>
                        <code>Content-Type: application/json <span class="header-note">— POST / PUT uniquement</span></code>
                    </div>
                </div>
            </section>

            <section class="docs-section" id="statuts">
                <div class="section-header">
                    <h2 class="section-title">Statuts transaction</h2>
                </div>
                <div class="status-grid">
                    <div class="status-chip">
                        <span class="status-badge badge-pending">PENDING</span>
                        <p>En attente — pas de webhook terminal. Interrogez
                            <code class="inline-code">GET /payments/{order_id}/status</code>.
                        </p>
                    </div>
                    <div class="status-chip">
                        <span class="status-badge badge-success">SUCCESS</span>
                        <p>Terminé — webhook
                            <code class="inline-code">payment.succeeded</code> ou
                            <code class="inline-code">payout.succeeded</code> émis.
                        </p>
                    </div>
                    <div class="status-chip">
                        <span class="status-badge badge-failed">FAILED</span>
                        <p>Échec terminal — webhook
                            <code class="inline-code">payment.failed</code> ou
                            <code class="inline-code">payout.failed</code> émis.
                        </p>
                    </div>
                </div>
            </section>

            @foreach($sections as $section)
                <section class="docs-api-section" id="{{ $section['id'] }}">
                    <div class="section-header">
                        <h2 class="section-title">{{ $section['title'] }}</h2>
                        @if(!empty($section['subtitle']))
                            <p class="section-sub">{{ $section['subtitle'] }}</p>
                        @endif
                    </div>

                    @foreach($section['endpoints'] as $endpoint)
                        @include('partials.portal.docs.endpoint-card', [
                            'endpoint' => $endpoint,
                            'apiBaseUrl' => $apiBaseUrl,
                            'apiKeyForCurl' => $apiKeyForCurl,
                            'company' => $company,
                        ])
                    @endforeach
                </section>
            @endforeach

            <footer class="docs-footer">
                <i class="bx bx-help-circle" aria-hidden="true"></i>
                Documentation complète (PDF / Markdown) disponible sur demande auprès de l'équipe
                <strong>{{ $brand }}</strong>.
            </footer>
        </div>
    </main>
</div>
@endsection
