/* ==========================================================================
   PINNACLE — main.js
   --------------------------------------------------------------------------
   Vanilla JavaScript (no dependencies). Each feature lives in its own
   small, self-contained module-function and is initialised on DOMContentLoaded.

   Modules:
     1. Mobile navigation (hamburger menu)
     2. Sticky header shadow on scroll
     3. Smooth scroll + active link highlighting
     4. Scroll-reveal animations (IntersectionObserver)
     5. Animated stat counters
     6. Portfolio filtering
     7. Testimonials slider
     8. Form validation (contact + newsletter)
     9. Back-to-top button
    10. Footer year
   ========================================================================== */

(function () {
  'use strict';

  /* Small helpers ---------------------------------------------------------- */
  const $  = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

  /* ======================================================================
     1. MOBILE NAVIGATION
  ====================================================================== */
  function initNav() {
    const toggle  = $('#navToggle');
    const nav     = $('#nav');
    const overlay = $('#navOverlay');
    if (!toggle || !nav) return;

    const open = () => {
      nav.classList.add('is-open');
      toggle.classList.add('is-active');
      toggle.setAttribute('aria-expanded', 'true');
      document.body.classList.add('no-scroll');
      overlay.hidden = false;
      // allow the element to render before transitioning opacity
      requestAnimationFrame(() => overlay.classList.add('is-visible'));
    };

    const close = () => {
      nav.classList.remove('is-open');
      toggle.classList.remove('is-active');
      toggle.setAttribute('aria-expanded', 'false');
      document.body.classList.remove('no-scroll');
      overlay.classList.remove('is-visible');
      // wait for the fade-out before hiding from the a11y tree
      setTimeout(() => { overlay.hidden = true; }, 350);
    };

    toggle.addEventListener('click', () => {
      nav.classList.contains('is-open') ? close() : open();
    });

    overlay.addEventListener('click', close);

    // Close after tapping a nav link (mobile)
    $$('.nav__link, .nav__cta', nav).forEach((link) =>
      link.addEventListener('click', close)
    );

    // Close on Escape
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && nav.classList.contains('is-open')) close();
    });

    // Reset state if resized up to desktop while menu is open
    window.addEventListener('resize', () => {
      if (window.innerWidth >= 960 && nav.classList.contains('is-open')) close();
    });
  }

  /* ======================================================================
     2. STICKY HEADER SHADOW
  ====================================================================== */
  function initHeaderScroll() {
    const header = $('#header');
    if (!header) return;
    const onScroll = () =>
      header.classList.toggle('is-scrolled', window.scrollY > 8);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  /* ======================================================================
     3. SMOOTH SCROLL + ACTIVE LINK HIGHLIGHTING
     (smooth scrolling itself is CSS; here we offset for the fixed header
      and keep the current section's nav link highlighted)
  ====================================================================== */
  function initScrollSpy() {
    const links    = $$('.nav__link');
    const sections = links
      .map((l) => document.getElementById(l.getAttribute('href').slice(1)))
      .filter(Boolean);
    if (!sections.length) return;

    const spy = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          const id = entry.target.id;
          links.forEach((l) =>
            l.classList.toggle('is-active', l.getAttribute('href') === `#${id}`)
          );
        });
      },
      { rootMargin: '-45% 0px -50% 0px', threshold: 0 }
    );

    sections.forEach((s) => spy.observe(s));
  }

  /* ======================================================================
     4. SCROLL-REVEAL ANIMATIONS
  ====================================================================== */
  function initReveal() {
    const items = $$('[data-animate]');
    if (!items.length) return;

    // Fallback: if IntersectionObserver is unsupported, just show everything.
    if (!('IntersectionObserver' in window)) {
      items.forEach((el) => el.classList.add('is-visible'));
      return;
    }

    const observer = new IntersectionObserver(
      (entries, obs) => {
        entries.forEach((entry, i) => {
          if (!entry.isIntersecting) return;
          // small stagger for groups of cards
          setTimeout(() => entry.target.classList.add('is-visible'), i * 60);
          obs.unobserve(entry.target);
        });
      },
      { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
    );

    items.forEach((el) => observer.observe(el));
  }

  /* ======================================================================
     5. ANIMATED STAT COUNTERS
  ====================================================================== */
  function initCounters() {
    const counters = $$('.counter');
    if (!counters.length || !('IntersectionObserver' in window)) {
      counters.forEach((c) => (c.textContent = c.dataset.target));
      return;
    }

    const run = (el) => {
      const target = parseInt(el.dataset.target, 10) || 0;
      const duration = 1400;
      const start = performance.now();

      const tick = (now) => {
        const progress = Math.min((now - start) / duration, 1);
        // easeOutCubic for a natural deceleration
        const eased = 1 - Math.pow(1 - progress, 3);
        el.textContent = Math.round(target * eased);
        if (progress < 1) requestAnimationFrame(tick);
      };
      requestAnimationFrame(tick);
    };

    const obs = new IntersectionObserver((entries, o) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        run(entry.target);
        o.unobserve(entry.target);
      });
    }, { threshold: 0.6 });

    counters.forEach((c) => obs.observe(c));
  }

  /* ======================================================================
     6. PORTFOLIO FILTERING
  ====================================================================== */
  function initPortfolioFilter() {
    const buttons = $$('.filter-btn');
    const items   = $$('.portfolio__item');
    if (!buttons.length || !items.length) return;

    buttons.forEach((btn) => {
      btn.addEventListener('click', () => {
        // update active button + aria state
        buttons.forEach((b) => {
          b.classList.remove('is-active');
          b.setAttribute('aria-pressed', 'false');
        });
        btn.classList.add('is-active');
        btn.setAttribute('aria-pressed', 'true');

        const filter = btn.dataset.filter;
        items.forEach((item) => {
          const match = filter === 'all' || item.dataset.category === filter;
          item.classList.toggle('is-hidden', !match);
        });
      });
    });
  }

  /* ======================================================================
     7. TESTIMONIALS SLIDER
  ====================================================================== */
  function initSlider() {
    const track = $('#sliderTrack');
    const slides = $$('.slide', track);
    const prev  = $('#prevSlide');
    const next  = $('#nextSlide');
    const dotsWrap = $('#sliderDots');
    if (!track || slides.length < 2) return;

    let index = 0;
    let timer = null;

    // Build dot indicators
    slides.forEach((_, i) => {
      const dot = document.createElement('button');
      dot.type = 'button';
      dot.setAttribute('aria-label', `Go to testimonial ${i + 1}`);
      if (i === 0) dot.classList.add('is-active');
      dot.addEventListener('click', () => goTo(i));
      dotsWrap.appendChild(dot);
    });
    const dots = $$('button', dotsWrap);

    function goTo(i) {
      index = (i + slides.length) % slides.length;
      track.style.transform = `translateX(-${index * 100}%)`;
      dots.forEach((d, di) => d.classList.toggle('is-active', di === index));
    }

    const nextSlide = () => goTo(index + 1);
    const prevSlide = () => goTo(index - 1);

    next.addEventListener('click', () => { nextSlide(); restart(); });
    prev.addEventListener('click', () => { prevSlide(); restart(); });

    // Auto-play (pauses on hover)
    function start() { timer = setInterval(nextSlide, 6000); }
    function stop()  { clearInterval(timer); }
    function restart() { stop(); start(); }

    track.addEventListener('mouseenter', stop);
    track.addEventListener('mouseleave', start);
    start();
  }

  /* ======================================================================
     8. FORM VALIDATION
  ====================================================================== */
  function initForms() {
    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    /* --- Contact form --- */
    const form = $('#contactForm');
    if (form) {
      const setError = (name, msg) => {
        const field = form.querySelector(`[name="${name}"]`).closest('.field');
        const slot  = form.querySelector(`[data-error-for="${name}"]`);
        field.classList.toggle('has-error', Boolean(msg));
        if (slot) slot.textContent = msg || '';
      };

      form.addEventListener('submit', (e) => {
        e.preventDefault();
        let valid = true;
        const data = new FormData(form);

        if (!data.get('name').trim()) { setError('name', 'Please enter your name.'); valid = false; }
        else setError('name', '');

        const email = data.get('email').trim();
        if (!email) { setError('email', 'Please enter your email.'); valid = false; }
        else if (!emailRe.test(email)) { setError('email', 'Please enter a valid email.'); valid = false; }
        else setError('email', '');

        if (!data.get('message').trim()) { setError('message', 'Please write a short message.'); valid = false; }
        else setError('message', '');

        if (valid) {
          // No backend in this template — show a friendly success message.
          form.reset();
          const ok = $('#formSuccess');
          ok.hidden = false;
          setTimeout(() => { ok.hidden = true; }, 5000);
        }
      });

      // Clear an error as soon as the user starts fixing the field
      $$('input, textarea', form).forEach((el) =>
        el.addEventListener('input', () => {
          el.closest('.field').classList.remove('has-error');
          const slot = form.querySelector(`[data-error-for="${el.name}"]`);
          if (slot) slot.textContent = '';
        })
      );
    }

    /* --- Newsletter form --- */
    const nl = $('#newsletterForm');
    if (nl) {
      nl.addEventListener('submit', (e) => {
        e.preventDefault();
        const input = $('#newsletterEmail');
        const msg   = $('#newsletterMsg');
        if (!emailRe.test(input.value.trim())) {
          input.focus();
          input.style.borderColor = '#ff5c8a';
          return;
        }
        input.style.borderColor = '';
        nl.reset();
        msg.hidden = false;
        setTimeout(() => { msg.hidden = true; }, 5000);
      });
    }
  }

  /* ======================================================================
     9. BACK-TO-TOP
  ====================================================================== */
  function initBackToTop() {
    const btn = $('#backToTop');
    if (!btn) return;

    // Keep the button in the DOM; CSS handles fade via the .is-visible class.
    btn.hidden = false;

    const onScroll = () =>
      btn.classList.toggle('is-visible', window.scrollY > 500);

    window.addEventListener('scroll', onScroll, { passive: true });
    btn.addEventListener('click', () =>
      window.scrollTo({ top: 0, behavior: 'smooth' })
    );
    onScroll();
  }

  /* ======================================================================
     10. FOOTER YEAR
  ====================================================================== */
  function initYear() {
    const el = $('#year');
    if (el) el.textContent = new Date().getFullYear();
  }

  /* ======================================================================
     BOOTSTRAP — run all modules when the DOM is ready
  ====================================================================== */
  document.addEventListener('DOMContentLoaded', () => {
    initNav();
    initHeaderScroll();
    initScrollSpy();
    initReveal();
    initCounters();
    initPortfolioFilter();
    initSlider();
    initForms();
    initBackToTop();
    initYear();
  });
})();
