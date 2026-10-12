<div class="code-block" data-lang="{{ $lang }}">
    <div class="code-header">
        <span class="code-lang">{{ $label ?? strtoupper($lang) }}</span>
        <button type="button" class="btn-copy-code" data-copy-id="{{ $codeId }}">
            <i class="bx bx-copy" aria-hidden="true"></i>
            <span>Copier</span>
        </button>
    </div>
    <pre id="{{ $codeId }}"
         class="code-pre {{ ($highlight ?? true) ? 'highlight-json' : 'highlight-bash' }}"
         tabindex="0"><code>{{ $content }}</code></pre>
</div>
