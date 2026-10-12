/**
 * portal-docs.js — Documentation API
 * TOUTransfert / 10nastie Groupe & Services
 */

(function () {
  'use strict';

  /* ── Utilitaires ──────────────────────────────────────────── */

  /** Copie du texte dans le presse-papier et retour visuel */
  function copyToClipboard(text, btn) {
    if (!navigator.clipboard) {
      // Fallback legacy
      const ta = document.createElement('textarea');
      ta.value = text;
      ta.style.cssText = 'position:fixed;opacity:0;pointer-events:none';
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      document.body.removeChild(ta);
      flashCopied(btn);
      return;
    }
    navigator.clipboard.writeText(text).then(() => flashCopied(btn));
  }

  function flashCopied(btn) {
    if (!btn) return;
    btn.classList.add('copied');
    const orig = btn.querySelector('span') ? btn.querySelector('span').textContent : btn.innerHTML;
    if (btn.querySelector('span')) btn.querySelector('span').textContent = 'Copié !';
    else btn.innerHTML = '<i class="bx bx-check"></i>';
    setTimeout(() => {
      btn.classList.remove('copied');
      if (btn.querySelector('span')) btn.querySelector('span').textContent = orig;
      else btn.innerHTML = '<i class="bx bx-copy"></i>';
    }, 1800);
  }

  /* ── Copie depuis data-copy (id élément) ───────────────────── */
  document.querySelectorAll('[data-copy]').forEach(btn => {
    btn.addEventListener('click', () => {
      const el = document.getElementById(btn.dataset.copy);
      if (el) copyToClipboard(el.textContent.trim(), btn);
    });
  });

  /* ── Copie depuis data-copy-value (valeur directe) ──────────── */
  document.querySelectorAll('[data-copy-value]').forEach(btn => {
    btn.addEventListener('click', () => {
      copyToClipboard(btn.dataset.copyValue, btn);
    });
  });

  /* ── Copie bloc de code ─────────────────────────────────────── */
  document.querySelectorAll('.btn-copy-code').forEach(btn => {
    btn.addEventListener('click', () => {
      const pre = document.getElementById(btn.dataset.copyId);
      if (!pre) return;
      copyToClipboard(pre.innerText || pre.textContent, btn);
    });
  });

  /* ── Accordéon endpoint ─────────────────────────────────────── */
  document.querySelectorAll('.endpoint-header').forEach(header => {
    header.addEventListener('click', () => toggleEndpoint(header));
    header.addEventListener('keydown', e => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        toggleEndpoint(header);
      }
    });
  });

  function toggleEndpoint(header) {
    const epId   = header.dataset.ep;
    const body   = document.getElementById('ep-body-' + epId);
    if (!body) return;

    const isOpen = header.getAttribute('aria-expanded') === 'true';
    header.setAttribute('aria-expanded', String(!isOpen));

    if (isOpen) {
      body.hidden = true;
    } else {
      body.hidden = false;
      body.removeAttribute('hidden');
    }
  }

  /* ── Onglets ────────────────────────────────────────────────── */
  document.querySelectorAll('.endpoint-tabs').forEach(tabsEl => {
    tabsEl.querySelectorAll('.tab-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const epId = tabsEl.dataset.ep;
        const tab  = btn.dataset.tab;

        // Activer le bon bouton
        tabsEl.querySelectorAll('.tab-btn').forEach(b => {
          b.classList.remove('active');
          b.setAttribute('aria-selected', 'false');
        });
        btn.classList.add('active');
        btn.setAttribute('aria-selected', 'true');

        // Activer le bon pane
        const paneIds = {
          params: 'ep-' + epId + '-params',
          curl:   'ep-' + epId + '-curl',
          response: 'ep-' + epId + '-response',
        };
        Object.values(paneIds).forEach(id => {
          const pane = document.getElementById(id);
          if (!pane) return;
          if (id === paneIds[tab]) {
            pane.classList.add('active');
            pane.removeAttribute('hidden');
          } else {
            pane.classList.remove('active');
            pane.hidden = true;
          }
        });
      });
    });
  });

  /* ── Sidebar navigation active ─────────────────────────────── */
  const navItems   = document.querySelectorAll('.nav-item[data-section]');
  const sections   = document.querySelectorAll('[id].docs-section, [id].docs-api-section, .endpoint-card[id]');
  const docsMain   = document.getElementById('docs-main') || document.querySelector('.docs-main');

  function getScrollParent(el) {
    return el ? (el.scrollHeight > el.clientHeight && getComputedStyle(el).overflowY !== 'visible' ? el : getScrollParent(el.parentElement)) : null;
  }

  function setActive(id) {
    navItems.forEach(item => {
      item.classList.toggle('active', item.dataset.section === id);
    });

    // breadcrumb
    const bc = document.getElementById('breadcrumb-current');
    if (bc) {
      const active = document.querySelector('.nav-item.active .nav-label');
      if (active) bc.textContent = active.textContent.trim();
    }
  }

  // Scroll spy
  const scrollEl = docsMain ? getScrollParent(document.querySelector('.docs-content')) || docsMain : null;

  if (scrollEl && sections.length) {
    scrollEl.addEventListener('scroll', () => {
      let current = sections[0].id;
      sections.forEach(sec => {
        const rect = sec.getBoundingClientRect();
        const parentRect = scrollEl.getBoundingClientRect();
        if (rect.top - parentRect.top <= 80) current = sec.id;
      });
      setActive(current);
    }, { passive: true });
  }

  // Clic sidebar → scroll
  navItems.forEach(item => {
    item.addEventListener('click', e => {
      const targetId = item.getAttribute('href')?.replace('#', '') || item.dataset.section;
      const target   = document.getElementById(targetId);
      if (!target) return;

      // Ouvrir l'accordéon si c'est un endpoint
      const card = target.closest('.endpoint-card') || target;
      const header = card.querySelector('.endpoint-header');
      if (header && header.getAttribute('aria-expanded') === 'false') {
        toggleEndpoint(header);
      }

      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      setActive(targetId);
    });
  });

  /* ── Mobile sidebar toggle ──────────────────────────────────── */
  const mobileToggle = document.getElementById('docs-mobile-toggle');
  const sidebar      = document.getElementById('docs-sidebar');

  if (mobileToggle && sidebar) {
    mobileToggle.addEventListener('click', () => {
      sidebar.classList.toggle('open');
    });

    // Fermer en cliquant en dehors
    document.addEventListener('click', e => {
      if (sidebar.classList.contains('open') &&
          !sidebar.contains(e.target) &&
          !mobileToggle.contains(e.target)) {
        sidebar.classList.remove('open');
      }
    });
  }

  /* ── Recherche live ─────────────────────────────────────────── */
  const searchInput = document.getElementById('docs-search');

  if (searchInput) {
    searchInput.addEventListener('input', debounce(() => {
      const q = searchInput.value.trim().toLowerCase();

      document.querySelectorAll('.endpoint-card').forEach(card => {
        if (!q) {
          card.style.display = '';
          return;
        }
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(q) ? '' : 'none';
      });

      // Masquer les section-headers vides
      document.querySelectorAll('.docs-api-section').forEach(section => {
        if (!q) { section.style.display = ''; return; }
        const visible = [...section.querySelectorAll('.endpoint-card')]
          .some(c => c.style.display !== 'none');
        section.style.display = visible ? '' : 'none';
      });
    }, 200));
  }

  function debounce(fn, delay) {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
  }

  /* ── Coloration syntaxique JSON (rendu serveur ou brut) ─────── */
  document.querySelectorAll('.highlight-json code').forEach(el => {
    el.innerHTML = highlightJson(el.textContent);
  });

  document.querySelectorAll('.highlight-bash code').forEach(el => {
    el.innerHTML = highlightBash(el.textContent);
  });

  function highlightJson(code) {
    return code
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+-]?\d+)?)/g, match => {
        if (/^".*":$/.test(match) || (match.endsWith('":') && match.startsWith('"'))) {
          return '<span class="t-key">' + match + '</span>';
        }
        if (/^"/.test(match)) return '<span class="t-str">' + match + '</span>';
        if (/true|false/.test(match)) return '<span class="t-bool">' + match + '</span>';
        if (/null/.test(match)) return '<span class="t-null">' + match + '</span>';
        return '<span class="t-num">' + match + '</span>';
      });
  }

  function highlightBash(code) {
    return code
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/^(curl)/m, '<span class="t-method">$1</span>')
      .replace(/(-X \w+)/g, '<span class="t-flag">$1</span>')
      .replace(/(-H|-d|--data)/g, '<span class="t-flag">$1</span>')
      .replace(/(https?:\/\/[^\s\\"']+)/g, '<span class="t-url">$1</span>')
      .replace(/"(Authorization|Content-Type)[^"]*"/g, m => '<span class="t-header">' + m + '</span>');
  }

})();
