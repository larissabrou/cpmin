@php
    $method = strtoupper($endpoint['method'] ?? 'GET');
    $path = $endpoint['path'] ?? '/';
    $url = rtrim($apiBaseUrl, '/').$path;
    $hasBody = in_array($method, ['POST', 'PUT', 'PATCH']);
    $key = $apiKeyForCurl ?: 'sk_live_VOTRE_CLÉ';
@endphp
curl -X {{ $method }} \
  "{{ $url }}" \
  -H "Authorization: Bearer {{ $key }}" \
@if($hasBody)
  -H "Content-Type: application/json" \
  -d '{}'
@endif
