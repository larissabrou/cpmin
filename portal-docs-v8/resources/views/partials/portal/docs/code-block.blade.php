{{--
    Partial : partials/portal/docs/code-block.blade.php
    Variables :
      $lang      – 'bash' | 'json' | 'php' …
      $label     – libellé affiché (ex : 'cURL', 'JSON')
      $codeId    – id unique HTML pour le copier
      $content   – code brut (string)
      $highlight – bool : activer la coloration JS
--}}
<div class="code-block" data-lang="{{ $lang }}">
    <div class="code-header">
        <span class="code-lang">{{ $label ?? strtoupper($lang) }}</span>
        <button type="button" class="btn-copy-code" data-copy-id="{{ $codeId }}">
            <i class="bx bx-copy" aria-hidden="true"></i>
            <span>Copier</span>
        </button>
    </div>
    <pre id="{{ $codeId }}"
         class="code-pre {{ $highlight ? 'highlight-json' : 'highlight-bash' }}"
         tabindex="0"><code>{{ $content }}</code></pre>
</div>
