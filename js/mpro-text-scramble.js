/**
 * MPRO Text Scramble — frontend engine
 * Config injected via wp_localize_script as window.mproScrambleConfig
 */
(function () {
  'use strict';

  var cfg = window.mproScrambleConfig || {
    charset:    '!@#$%^&*()_+-=[]{}|',
    duration:   350,
    iterations: 5,
    stagger:    18,
    trigger:    'load',
    cssClass:   '.mpro-scramble',
  };

  /* ── core scramble function ─────────────────────────── */
  function scrambleElement(el) {
    var original = el.dataset.mproOriginal;
    if (!original) {
      original = el.textContent;
      el.dataset.mproOriginal = original;
    }

    var chars   = original.split('');
    var charset = cfg.charset;
    var dur     = parseInt(cfg.duration, 10);
    var iters   = parseInt(cfg.iterations, 10);
    var stag    = parseInt(cfg.stagger, 10);

    // Rebuild innerHTML with individual char spans
    el.innerHTML = chars.map(function (c) {
      var isSpace = (c === ' ' || c === '\u00a0');
      return '<span class="mpro-char' + (isSpace ? ' mpro-space' : '') + '" data-final="' +
        escapeAttr(c) + '">' + (isSpace ? '\u00a0' : randChar(charset)) + '</span>';
    }).join('');

    var spans = el.querySelectorAll('.mpro-char:not(.mpro-space)');

    spans.forEach(function (span, i) {
      var delay    = i * stag;
      var interval = Math.max(16, Math.floor(dur / iters));
      var count    = 0;

      setTimeout(function () {
        span.classList.add('mpro-glitch');
        var tick = setInterval(function () {
          count++;
          if (count >= iters) {
            clearInterval(tick);
            span.textContent = span.dataset.final;
            span.classList.remove('mpro-glitch');
          } else {
            span.textContent = randChar(charset);
          }
        }, interval);
      }, delay);
    });
  }

  function randChar(charset) {
    return charset.charAt(Math.floor(Math.random() * charset.length));
  }

  function escapeAttr(str) {
    return str.replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }

  /* ── IntersectionObserver for load trigger ──────────── */
  function initLoad(els) {
    if (!els.length) return;

    if ('IntersectionObserver' in window) {
      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            scrambleElement(entry.target);
            observer.unobserve(entry.target);
          }
        });
      }, { threshold: 0.15 });

      els.forEach(function (el) { observer.observe(el); });
    } else {
      // Fallback: fire immediately
      els.forEach(scrambleElement);
    }
  }

  /* ── Hover trigger ──────────────────────────────────── */
  function initHover(els) {
    els.forEach(function (el) {
      el.addEventListener('mouseenter', function () {
        scrambleElement(el);
      });
      // Touch support
      el.addEventListener('touchstart', function () {
        scrambleElement(el);
      }, { passive: true });
    });
  }

  /* ── Data attribute override per element ────────────── */
  // data-scramble-trigger="hover|load|both" overrides global setting

  function getTrigger(el) {
    return el.dataset.scrambleTrigger || cfg.trigger;
  }

  /* ── Boot ────────────────────────────────────────────── */
  function boot() {
    var selector = cfg.cssClass;
    var all = Array.prototype.slice.call(document.querySelectorAll(selector));

    var loadEls  = all.filter(function(el){ var t=getTrigger(el); return t==='load'||t==='both'; });
    var hoverEls = all.filter(function(el){ var t=getTrigger(el); return t==='hover'||t==='both'; });

    initLoad(loadEls);
    initHover(hoverEls);

    // Elementor editor live preview support
    if (window.elementorFrontend) {
      window.elementorFrontend.hooks.addAction('frontend/element_ready/global', function ($scope) {
        var newEls = Array.prototype.slice.call($scope[0].querySelectorAll(selector));
        var newLoad  = newEls.filter(function(el){ var t=getTrigger(el); return t==='load'||t==='both'; });
        var newHover = newEls.filter(function(el){ var t=getTrigger(el); return t==='hover'||t==='both'; });
        initLoad(newLoad);
        initHover(newHover);
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }

})();
