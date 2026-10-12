@extends('layouts.portal')

@section('title', 'Clé API')

@section('content')
<div class="row portal-page-head mb-3">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1 sub-title"><i class="bx bx-key me-1"></i> Intégration API</h4>
                <p class="text-muted mb-0 font-size-13">Consultez vos identifiants et configurez la réception des webhooks.</p>
            </div>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Clé API</li>
            </ol>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(!$company->hasGatewayCredentials())
    <div class="alert alert-warning d-flex align-items-center">
        <i class="bx bx-error-circle font-size-18 me-2"></i>
        <span>Aucune clé API n'est associée à votre compte. Contactez l'équipe {{ $brand }}.</span>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header bg-transparent border-bottom">
                <h5 class="card-title mb-0">Identifiants (lecture seule)</h5>
                <p class="text-muted font-size-12 mb-0 mt-1">Fournis par {{ $brand }} — copiez-les pour vos appels API.</p>
            </div>
            <div class="card-body">
                <div class="portal-api-field mb-4">
                    <label class="form-label text-muted small text-uppercase">URL de base API</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-link"></i></span>
                        <input type="text" class="form-control font-monospace" id="api-gateway-url" value="{{ $apiBaseUrl }}" readonly>
                        <button type="button" class="btn btn-soft-primary" data-copy="#api-gateway-url" title="Copier">
                            <i class="bx bx-copy"></i>
                        </button>
                    </div>
                </div>

                <div class="portal-api-field mb-4">
                    <label class="form-label text-muted small text-uppercase">Clé API</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-key"></i></span>
                        <input type="text" class="form-control font-monospace" value="{{ $company->maskedApiKey() ?? 'Non configurée' }}" readonly>
                        @if($company->gateway_api_key)
                            <button type="button" class="btn btn-soft-primary" data-copy-value="{{ $company->gateway_api_key }}" title="Copier la clé complète">
                                <i class="bx bx-copy"></i> Copier
                            </button>
                        @endif
                    </div>
                    <div class="form-text">En-tête HTTP : <code>Authorization: Bearer VOTRE_CLE</code></div>
                </div>

                <div class="portal-api-field">
                    <label class="form-label text-muted small text-uppercase">Secret webhook (HMAC)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-shield-quarter"></i></span>
                        <input type="text" class="form-control font-monospace" value="{{ $company->maskedWebhookSecret() ?? 'Non configuré' }}" readonly>
                        @if($company->gateway_webhook_secret)
                            <button type="button" class="btn btn-soft-primary" data-copy-value="{{ $company->gateway_webhook_secret }}" title="Copier le secret">
                                <i class="bx bx-copy"></i> Copier
                            </button>
                        @endif
                    </div>
                    <div class="form-text">Utilisé pour vérifier la signature des notifications reçues sur votre URL webhook.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header bg-transparent border-bottom">
                <h5 class="card-title mb-0">Webhook entrant</h5>
                <p class="text-muted font-size-12 mb-0 mt-1">Seule cette URL est modifiable depuis le portail.</p>
            </div>
            <div class="card-body d-flex flex-column">
                <form method="POST" action="{{ route('api.credentials.update') }}" class="flex-grow-1 d-flex flex-column">
                    @csrf
                    @method('PUT')
                    <div class="mb-3 flex-grow-1">
                        <label class="form-label" for="gateway_webhook_url">URL de réception</label>
                        <input type="url" name="gateway_webhook_url" id="gateway_webhook_url" class="form-control"
                               value="{{ old('gateway_webhook_url', $company->gateway_webhook_url) }}"
                               placeholder="https://votre-app.com/webhooks/aggregpay">
                        @error('gateway_webhook_url')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            {{ $brand }} enverra les événements <code>payment.*</code> et <code>payout.*</code> à cette adresse HTTPS.
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bx-save me-1"></i> Enregistrer l'URL webhook
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<p class="text-muted font-size-13 mt-3 mb-0">
    Besoin d'aide pour intégrer l'API ?
    <a href="{{ route('docs') }}" class="text-primary">Consultez la documentation</a>.
</p>
@endsection

@push('styles')
<link href="{{ asset('theme/portal-api.css') }}?v=2" rel="stylesheet" type="text/css" />
@endpush

@push('scripts')
<script src="{{ asset('theme/portal-api.js') }}?v=2"></script>
@endpush
