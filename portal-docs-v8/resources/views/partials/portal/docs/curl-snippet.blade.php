{{--
    Partial : partials/portal/docs/curl-snippet.blade.php
    Génère la commande cURL pour un endpoint.
    Variables : $endpoint, $apiBaseUrl, $apiKeyForCurl
--}}
@php
    $method  = strtoupper($endpoint['method'] ?? 'GET');
    $path    = $endpoint['path'] ?? '/';
    $url     = rtrim($apiBaseUrl, '/') . $path;
    $hasBody = in_array($method, ['POST', 'PUT', 'PATCH']);
    $key     = $apiKeyForCurl ?: 'sk_live_VOTRE_CLÉ';

    // Exemple de body depuis les paramètres
    $bodyParams = collect($endpoint['parameters'] ?? [])
        ->filter(fn($p) => in_array($p['in'] ?? 'body', ['body', '']) || $hasBody)
        ->mapWithKeys(fn($p) => [$p['name'] => $p['example'] ?? $p['type'] ?? ''])
        ->toArray();
@endphp
curl -X {{ $method }} \
  "{{ $url }}" \
  -H "Authorization: Bearer {{ $key }}" \
@if($hasBody)
  -H "Content-Type: application/json" \
  -d '{
@foreach($bodyParams as $key => $val)
    "{{ $key }}": {{ is_string($val) ? '"'.$val.'"' : $val }}{{ !$loop->last ? ',' : '' }}
@endforeach
  }'
@endif
