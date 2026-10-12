@php
    use App\Support\PortalApiDocs;

    $method = strtolower($endpoint['method'] ?? 'get');
    $epId = $endpoint['id'];
    $hasBody = in_array($method, ['post', 'put', 'patch']);
    $params = $endpoint['parameters'] ?? $endpoint['fields'] ?? [];
    $curlExamples = $endpoint['curl_examples'] ?? [];
    $hideCurl = !empty($endpoint['hide_curl']);
    $defaultTab = $endpoint['default_tab'] ?? 'params';
    $responseTabLabel = $hideCurl ? 'Payload' : 'Réponse';
@endphp

<article class="endpoint-card endpoint-card--{{ $method }}" id="{{ $epId }}">

    <div class="endpoint-header" role="button" tabindex="0"
         data-ep="{{ $epId }}"
         aria-expanded="false"
         aria-controls="ep-body-{{ $epId }}">

        <span class="method-pill pill-{{ $method }}">{{ strtoupper($method) }}</span>
        <span class="endpoint-path">{{ $endpoint['path'] ?? '' }}</span>
        <span class="endpoint-title">{{ $endpoint['title'] ?? '' }}</span>

        @if(!empty($endpoint['description']))
            <span class="endpoint-desc">{{ $endpoint['description'] }}</span>
        @endif

        <span class="endpoint-chevron" aria-hidden="true">
            <i class="bx bx-chevron-down"></i>
        </span>
    </div>

    <div class="endpoint-body" id="ep-body-{{ $epId }}" hidden>

        <div class="endpoint-tabs" role="tablist" data-ep="{{ $epId }}">
            <button class="tab-btn {{ $defaultTab === 'params' ? 'active' : '' }}" role="tab" aria-selected="{{ $defaultTab === 'params' ? 'true' : 'false' }}"
                    aria-controls="ep-{{ $epId }}-params" data-tab="params" type="button">
                {{ $hasBody ? 'Requête' : 'Paramètres' }}
            </button>
            @unless($hideCurl)
            <button class="tab-btn {{ $defaultTab === 'curl' ? 'active' : '' }}" role="tab" aria-selected="{{ $defaultTab === 'curl' ? 'true' : 'false' }}"
                    aria-controls="ep-{{ $epId }}-curl" data-tab="curl" type="button">
                cURL
            </button>
            @endunless
            <button class="tab-btn {{ $defaultTab === 'response' ? 'active' : '' }}" role="tab" aria-selected="{{ $defaultTab === 'response' ? 'true' : 'false' }}"
                    aria-controls="ep-{{ $epId }}-response" data-tab="response" type="button">
                {{ $responseTabLabel }}
            </button>
        </div>

        <div class="tab-pane {{ $defaultTab === 'params' ? 'active' : '' }}" id="ep-{{ $epId }}-params" role="tabpanel" @if($defaultTab !== 'params') hidden @endif>
            @if(!empty($params))
                <table class="params-table">
                    <thead>
                        <tr>
                            <th>Paramètre</th>
                            <th>Type</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($params as $param)
                            @php
                                $in = $param['in'] ?? match ($param['type'] ?? 'string') {
                                    'path', 'query', 'header' => $param['type'],
                                    default => 'body',
                                };
                                $required = $param['required'] ?? false;
                            @endphp
                            <tr>
                                <td>
                                    <div class="param-name">{{ $param['name'] }}</div>
                                    <span class="param-in">{{ $in }}</span>
                                    @if($required === true)
                                        <span class="param-badge param-required">requis</span>
                                    @elseif($required === 'conditional')
                                        <span class="param-badge param-conditional">conditionnel{{ !empty($param['condition']) ? ' · '.$param['condition'] : '' }}</span>
                                    @else
                                        <span class="param-badge param-optional">optionnel</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="param-type">{{ $param['type'] ?? 'string' }}</span>
                                </td>
                                <td class="param-desc">{{ $param['description'] ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="empty-params">Aucun paramètre requis.</p>
            @endif

            @if(!empty($endpoint['operator_flows']))
                <div class="operator-flows">
                    <p class="operator-flows-title">Validation côté client — Orange · Moov · MTN</p>
                    <div class="operator-flows-grid">
                        @foreach($endpoint['operator_flows'] as $flow)
                            <div class="operator-flow-chip">
                                <span class="operator-flow-name">{{ $flow['operator'] }}</span>
                                <code class="inline-code">{{ $flow['ussd'] }}</code>
                                <p>{{ $flow['instruction'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        @unless($hideCurl)
        <div class="tab-pane {{ $defaultTab === 'curl' ? 'active' : '' }}" id="ep-{{ $epId }}-curl" role="tabpanel" @if($defaultTab !== 'curl') hidden @endif>
            @if(count($curlExamples) > 1)
                <div class="curl-examples-tabs" data-curl-tabs="{{ $epId }}">
                    <div class="curl-tab-list">
                        @foreach($curlExamples as $i => $example)
                            <button type="button"
                                    class="curl-tab-btn {{ $i === 0 ? 'active' : '' }}"
                                    data-curl-tab="curl-{{ $epId }}-{{ $i }}">
                                {{ $example['label'] }}
                            </button>
                        @endforeach
                    </div>
                    @foreach($curlExamples as $i => $example)
                        @php
                            $curl = PortalApiDocs::interpolate($example['curl'], $apiBaseUrl, $apiKeyForCurl);
                        @endphp
                        <div class="curl-tab-panel {{ $i === 0 ? 'active' : '' }}"
                             id="curl-{{ $epId }}-{{ $i }}"
                             @if($i !== 0) hidden @endif>
                            @include('partials.portal.docs.code-block', [
                                'lang' => 'bash',
                                'label' => 'cURL',
                                'codeId' => 'code-curl-' . $epId . '-' . $i,
                                'content' => $curl,
                                'highlight' => false,
                            ])
                        </div>
                    @endforeach
                </div>
            @elseif(count($curlExamples) === 1)
                @php
                    $curl = PortalApiDocs::interpolate($curlExamples[0]['curl'], $apiBaseUrl, $apiKeyForCurl);
                @endphp
                @include('partials.portal.docs.code-block', [
                    'lang' => 'bash',
                    'label' => 'cURL',
                    'codeId' => 'code-curl-' . $epId,
                    'content' => $curl,
                    'highlight' => false,
                ])
            @else
                @php
                    $curlFallback = trim(view('partials.portal.docs.curl-snippet', [
                        'endpoint' => $endpoint,
                        'apiBaseUrl' => $apiBaseUrl,
                        'apiKeyForCurl' => $apiKeyForCurl,
                    ])->render());
                @endphp
                @include('partials.portal.docs.code-block', [
                    'lang' => 'bash',
                    'label' => 'cURL',
                    'codeId' => 'code-curl-' . $epId,
                    'content' => $curlFallback,
                    'highlight' => false,
                ])
            @endif

            @if($company?->gateway_api_key)
                <p class="curl-hint"><i class="bx bx-info-circle"></i> Le cURL inclut votre clé API réelle.</p>
            @endif
        </div>
        @endunless

        <div class="tab-pane {{ $defaultTab === 'response' ? 'active' : '' }}" id="ep-{{ $epId }}-response" role="tabpanel" @if($defaultTab !== 'response') hidden @endif>
            @if(!empty($endpoint['responses']))
                <div class="responses-grid">
                    @foreach($endpoint['responses'] as $resp)
                        @php
                            $httpCode = $resp['code'] ?? 200;
                            $isSuccess = $httpCode >= 200 && $httpCode < 300;
                            $body = trim($resp['body'] ?? '{}');
                            $decoded = json_decode($body);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $body = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                            }
                        @endphp
                        <div class="response-block">
                            <div class="response-status-row">
                                <span class="status-http {{ $isSuccess ? 'status-2xx' : 'status-4xx' }}">
                                    {{ $httpCode }}
                                </span>
                                <span class="response-label">{{ $resp['label'] ?? '' }}</span>
                            </div>
                            @include('partials.portal.docs.code-block', [
                                'lang' => 'json',
                                'label' => 'JSON',
                                'codeId' => 'code-res-' . $epId . '-' . $httpCode,
                                'content' => $body,
                                'highlight' => true,
                            ])
                        </div>
                    @endforeach
                </div>
            @else
                <p class="empty-params">Aucun exemple de réponse disponible.</p>
            @endif
        </div>

    </div>
</article>
