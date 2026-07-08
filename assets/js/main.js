(() => {
  const root = document.documentElement;
  const savedTheme = localStorage.getItem('ff-theme');
  if (savedTheme) root.setAttribute('data-theme', savedTheme);

  const header = document.querySelector('[data-header]');
  const nav = document.querySelector('[data-main-nav]');
  const navToggle = document.querySelector('[data-nav-toggle]');
  const themeToggle = document.querySelector('[data-theme-toggle]');
  const toTop = document.querySelector('[data-to-top]');

  const onScroll = () => {
    header?.classList.toggle('is-scrolled', window.scrollY > 8);
    toTop?.classList.toggle('show', window.scrollY > 500);
  };
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });

  const setNavOpen = (open) => {
    nav?.classList.toggle('open', open);
    document.body.classList.toggle('nav-open', open);
    navToggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
    navToggle?.setAttribute('aria-label', open ? 'Menü schließen' : 'Menü öffnen');
  };

  navToggle?.addEventListener('click', () => {
    setNavOpen(!nav?.classList.contains('open'));
  });

  nav?.addEventListener('click', event => {
    if (event.target.matches('a')) {
      setNavOpen(false);
    }
  });


  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && nav?.classList.contains('open')) {
      setNavOpen(false);
    }
  });

  themeToggle?.addEventListener('click', () => {
    const next = root.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
    root.setAttribute('data-theme', next);
    localStorage.setItem('ff-theme', next);
  });

  toTop?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('in-view');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });
  document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

  document.querySelectorAll('[data-filter]').forEach(button => {
    button.addEventListener('click', () => {
      const group = button.closest('[data-filter-group]');
      const filter = button.dataset.filter;
      group?.querySelectorAll('[data-filter]').forEach(btn => btn.classList.remove('active'));
      button.classList.add('active');
      document.querySelectorAll('[data-category]').forEach(item => {
        item.hidden = filter !== 'all' && item.dataset.category !== filter;
      });
    });
  });

  document.querySelectorAll('[data-counter]').forEach(el => {
    const target = Number(el.dataset.counter);
    if (!Number.isFinite(target)) return;
    let current = 0;
    const step = Math.max(1, Math.ceil(target / 45));
    const tick = () => {
      current = Math.min(target, current + step);
      el.textContent = current.toLocaleString('de-DE');
      if (current < target) requestAnimationFrame(tick);
    };
    tick();
  });



  const heroVisual = document.querySelector('.hero-visual');
  heroVisual?.addEventListener('pointermove', event => {
    const rect = heroVisual.getBoundingClientRect();
    const px = (event.clientX - rect.left) / rect.width;
    const py = (event.clientY - rect.top) / rect.height;
    const rotateY = (px - 0.5) * 8;
    const rotateX = (0.5 - py) * 8;
    heroVisual.classList.add('is-tilting');
    heroVisual.style.transform = `perspective(1100px) rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg) translateY(-2px)`;
  });
  heroVisual?.addEventListener('pointerleave', () => {
    heroVisual.classList.remove('is-tilting');
    heroVisual.style.transform = '';
  });

  const contactForm = document.querySelector('[data-contact-form]');
  contactForm?.addEventListener('submit', event => {
    if (contactForm.getAttribute('action')) return;
    event.preventDefault();
    const status = contactForm.querySelector('[data-form-status]');
    const data = new FormData(contactForm);
    const required = ['name', 'email', 'message'];
    const missing = required.filter(key => !String(data.get(key) || '').trim());
    if (missing.length) {
      status.textContent = 'Bitte fülle Name, E-Mail und Nachricht aus.';
      status.className = 'form-note warning';
      return;
    }
    status.textContent = 'Das Formular benötigt auf statischem Hosting PHP. Auf PHP-Hosting wird es sicher in SQLite gespeichert.';
    status.className = 'form-note warning';
  });
})();
