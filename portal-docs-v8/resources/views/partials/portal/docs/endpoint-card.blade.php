{{--
    Partial : partials/portal/docs/endpoint-card.blade.php
    Variables attendues :
      $endpoint      – array (method, path, id, description, parameters, responses, curl)
      $apiBaseUrl    – string
      $apiKeyForCurl – string
      $company       – model|null
--}}

@php
    $method   = strtolower($endpoint['method'] ?? 'get');
    $epId     = $endpoint['id'] ?? \Str::slug($endpoint['path'] ?? '');
    $hasBody  = in_array($method, ['post', 'put', 'patch']);
@endphp

<article class="endpoint-card endpoint-card--{{ $method }}" id="{{ $epId }}">

    {{-- HEADER --}}
    <div class="endpoint-header" role="button" tabindex="0"
         data-ep="{{ $epId }}"
         aria-expanded="false"
         aria-controls="ep-body-{{ $epId }}">

        <span class="method-pill pill-{{ $method }}">{{ strtoupper($method) }}</span>
        <span class="endpoint-path">{{ $endpoint['path'] ?? '' }}</span>

        @if(!empty($endpoint['description']))
            <span class="endpoint-desc">{{ $endpoint['description'] }}</span>
        @endif

        <span class="endpoint-chevron" aria-hidden="true">
            <i class="bx bx-chevron-down"></i>
        </span>
    </div>

    {{-- BODY --}}
    <div class="endpoint-body" id="ep-body-{{ $epId }}" hidden>

        {{-- ONGLETS --}}
        <div class="endpoint-tabs" role="tablist" data-ep="{{ $epId }}">
            <button class="tab-btn active"
                    role="tab"
                    aria-selected="true"
                    aria-controls="ep-{{ $epId }}-params"
                    data-tab="params">
                {{ $hasBody ? 'Corps de requête' : 'Paramètres' }}
            </button>
            <button class="tab-btn"
                    role="tab"
                    aria-selected="false"
                    aria-controls="ep-{{ $epId }}-curl"
                    data-tab="curl">
                cURL
            </button>
            <button class="tab-btn"
                    role="tab"
                    aria-selected="false"
                    aria-controls="ep-{{ $epId }}-response"
                    data-tab="response">
                Réponse
            </button>
        </div>

        {{-- ONGLET : PARAMÈTRES --}}
        <div class="tab-pane active"
             id="ep-{{ $epId }}-params"
             role="tabpanel">

            @if(!empty($endpoint['parameters']))
                <table class="params-table">
                    <thead>
                        <tr>
                            <th>Paramètre</th>
                            <th>Type</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($endpoint['parameters'] as $param)
                            <tr>
                                <td>
                                    <div class="param-name">{{ $param['name'] }}</div>
                                    @if(!empty($param['in']))
                                        <span class="param-in">{{ $param['in'] }}</span>
                                    @endif
                                    @if(($param['required'] ?? false))
                                        <span class="param-badge param-required">requis</span>
                                    @else
                                        <span class="param-badge param-optional">optionnel</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="param-type">{{ $param['type'] ?? 'string' }}</span>
                                    @if(!empty($param['format']))
                                        <span class="param-format">{{ $param['format'] }}</span>
                                    @endif
                                </td>
                                <td class="param-desc">
                                    {!! $param['description'] ?? '' !!}
                                    @if(!empty($param['enum']))
                                        <div class="param-enum">
                                            @foreach($param['enum'] as $val)
                                                <code class="inline-code">{{ $val }}</code>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if(!empty($param['example']))
                                        <div class="param-example">
                                            Ex. : <code class="inline-code">{{ $param['example'] }}</code>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="empty-params">Aucun paramètre requis.</p>
            @endif
        </div>

        {{-- ONGLET : cURL --}}
        <div class="tab-pane"
             id="ep-{{ $epId }}-curl"
             role="tabpanel"
             hidden>

            @include('partials.portal.docs.code-block', [
                'lang'    => 'bash',
                'label'   => 'cURL',
                'codeId'  => 'curl-' . $epId,
                'content' => view('partials.portal.docs.curl-snippet', [
                    'endpoint'      => $endpoint,
                    'apiBaseUrl'    => $apiBaseUrl,
                    'apiKeyForCurl' => $apiKeyForCurl,
                ])->render(),
                'highlight' => false,
            ])
        </div>

        {{-- ONGLET : RÉPONSE --}}
        <div class="tab-pane"
             id="ep-{{ $epId }}-response"
             role="tabpanel"
             hidden>

            @if(!empty($endpoint['responses']))
                <div class="responses-grid">
                    @foreach($endpoint['responses'] as $resp)
                        @php
                            $httpCode = $resp['code'] ?? 200;
                            $isSuccess = $httpCode >= 200 && $httpCode < 300;
                        @endphp
                        <div class="response-block">
                            <div class="response-status-row">
                                <span class="status-http {{ $isSuccess ? 'status-2xx' : 'status-4xx' }}">
                                    {{ $httpCode }}
                                </span>
                                <span class="response-label">{{ $resp['label'] ?? '' }}</span>
                            </div>
                            @include('partials.portal.docs.code-block', [
                                'lang'      => 'json',
                                'label'     => 'JSON',
                                'codeId'    => 'res-' . $epId . '-' . $httpCode,
                                'content'   => $resp['body'] ?? '{}',
                                'highlight' => true,
                            ])
                        </div>
                    @endforeach
                </div>
            @else
                <p class="empty-params">Aucun exemple de réponse disponible.</p>
            @endif
        </div>

    </div>{{-- /.endpoint-body --}}
</article>
