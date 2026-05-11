/* ===================================================
   HumKadam - Main JavaScript
   =================================================== */

document.addEventListener('DOMContentLoaded', () => {

  /* -----------------------------------------------
     NAVBAR SCROLL BEHAVIOR
  ----------------------------------------------- */
  const navbar = document.getElementById('navbar');
  const handleNavScroll = () => {
    if (window.scrollY > 60) {
      navbar.classList.add('scrolled');
      navbar.classList.remove('transparent');
    } else {
      navbar.classList.remove('scrolled');
      if (navbar.dataset.transparent === 'true') {
        navbar.classList.add('transparent');
      }
    }
  };
  if (navbar) {
    window.addEventListener('scroll', handleNavScroll, { passive: true });
    handleNavScroll();
  }

  /* -----------------------------------------------
     HAMBURGER MENU
  ----------------------------------------------- */
  const hamburger = document.getElementById('hamburger');
  const mobileMenu = document.getElementById('mobileMenu');
  if (hamburger && mobileMenu) {
    hamburger.addEventListener('click', () => {
      hamburger.classList.toggle('open');
      mobileMenu.classList.toggle('open');
    });
    // Close on link click
    mobileMenu.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        hamburger.classList.remove('open');
        mobileMenu.classList.remove('open');
      });
    });
  }

  /* -----------------------------------------------
     ACTIVE NAV LINK
  ----------------------------------------------- */
  const currentPage = window.location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav-links a, .mobile-menu a').forEach(link => {
    const href = link.getAttribute('href');
    if (href === currentPage || (currentPage === '' && href === 'index.html')) {
      link.classList.add('active');
    }
  });

  /* -----------------------------------------------
     SCROLL ANIMATIONS (IntersectionObserver)
  ----------------------------------------------- */
  const animEls = document.querySelectorAll('.fade-up, .scale-in');
  if (animEls.length) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    animEls.forEach(el => observer.observe(el));
  }

  /* -----------------------------------------------
     BACK TO TOP BUTTON
  ----------------------------------------------- */
  const backTop = document.getElementById('backTop');
  if (backTop) {
    window.addEventListener('scroll', () => {
      backTop.classList.toggle('visible', window.scrollY > 400);
    }, { passive: true });
    backTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }

  /* -----------------------------------------------
     HERO FLOATING PARTICLES
  ----------------------------------------------- */
  const particlesContainer = document.getElementById('heroParticles');
  if (particlesContainer) {
    const count = 18;
    for (let i = 0; i < count; i++) {
      const p = document.createElement('span');
      p.classList.add('hero-particle');
      p.style.left = Math.random() * 100 + '%';
      p.style.width = p.style.height = (Math.random() * 3 + 2) + 'px';
      p.style.animationDuration = (Math.random() * 10 + 8) + 's';
      p.style.animationDelay = (Math.random() * 10) + 's';
      p.style.opacity = Math.random() * 0.4 + 0.1;
      particlesContainer.appendChild(p);
    }
  }

  /* -----------------------------------------------
     TESTIMONIALS SLIDER
  ----------------------------------------------- */
  const track = document.querySelector('.testimonials-track');
  if (track) {
    const cards = track.querySelectorAll('.testimonial-card');
    const dotsContainer = document.querySelector('.slider-dots');
    const prevBtn = document.querySelector('.slider-btn.prev');
    const nextBtn = document.querySelector('.slider-btn.next');
    let current = 0;
    let perView = window.innerWidth < 600 ? 1 : window.innerWidth < 900 ? 2 : 3;

    const maxIndex = () => Math.max(0, cards.length - perView);

    const createDots = () => {
      if (!dotsContainer) return;
      dotsContainer.innerHTML = '';
      const numDots = maxIndex() + 1;
      for (let i = 0; i < numDots; i++) {
        const dot = document.createElement('span');
        dot.classList.add('slider-dot');
        if (i === 0) dot.classList.add('active');
        dot.addEventListener('click', () => goTo(i));
        dotsContainer.appendChild(dot);
      }
    };

    const goTo = (idx) => {
      current = Math.max(0, Math.min(idx, maxIndex()));
      const cardWidth = cards[0].offsetWidth + 28;
      track.style.transform = `translateX(-${current * cardWidth}px)`;
      dotsContainer?.querySelectorAll('.slider-dot').forEach((d, i) => {
        d.classList.toggle('active', i === current);
      });
    };

    if (prevBtn) prevBtn.addEventListener('click', () => goTo(current - 1));
    if (nextBtn) nextBtn.addEventListener('click', () => goTo(current + 1));

    createDots();

    // Auto-play
    let autoplay = setInterval(() => goTo(current >= maxIndex() ? 0 : current + 1), 5000);
    track.addEventListener('mouseenter', () => clearInterval(autoplay));
    track.addEventListener('mouseleave', () => {
      autoplay = setInterval(() => goTo(current >= maxIndex() ? 0 : current + 1), 5000);
    });

    window.addEventListener('resize', () => {
      perView = window.innerWidth < 600 ? 1 : window.innerWidth < 900 ? 2 : 3;
      current = 0;
      createDots();
      goTo(0);
    });
  }

  /* -----------------------------------------------
     COUNTER ANIMATION (stats)
  ----------------------------------------------- */
  const counters = document.querySelectorAll('[data-count]');
  if (counters.length) {
    const countObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const el = entry.target;
        const target = parseInt(el.dataset.count, 10);
        const suffix = el.dataset.suffix || '';
        const duration = 1800;
        const step = target / (duration / 16);
        let current = 0;
        const timer = setInterval(() => {
          current = Math.min(current + step, target);
          el.textContent = Math.round(current) + suffix;
          if (current >= target) clearInterval(timer);
        }, 16);
        countObserver.unobserve(el);
      });
    }, { threshold: 0.5 });
    counters.forEach(c => countObserver.observe(c));
  }

  /* -----------------------------------------------
     CONTACT FORM VALIDATION
  ----------------------------------------------- */
  const contactForm = document.getElementById('contactForm');
  if (contactForm) {
    const rules = {
      name: { required: true, minLength: 3, label: 'Full Name' },
      email: { required: true, pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, label: 'Email' },
      phone: { required: true, pattern: /^[6-9]\d{9}$/, label: 'Phone' },
      service: { required: true, label: 'Service' },
      message: { required: true, minLength: 20, label: 'Message' },
    };

    const showError = (fieldName, msg) => {
      const group = contactForm.querySelector(`[name="${fieldName}"]`)?.closest('.form-group');
      if (!group) return;
      group.classList.add('error');
      const err = group.querySelector('.form-error');
      if (err) err.textContent = msg;
    };
    const clearError = (fieldName) => {
      const group = contactForm.querySelector(`[name="${fieldName}"]`)?.closest('.form-group');
      if (group) {
        group.classList.remove('error');
      }
    };

    // Live validation
    Object.keys(rules).forEach(name => {
      const el = contactForm.querySelector(`[name="${name}"]`);
      if (el) el.addEventListener('input', () => clearError(name));
    });

    // Client-side validation only — actual submission is handled by
    // the fetch() in contact.html which posts to contact-handler.php
    contactForm.addEventListener('submit', (e) => {
      let valid = true;

      Object.entries(rules).forEach(([name, rule]) => {
        const el = contactForm.querySelector(`[name="${name}"]`);
        if (!el) return;
        const val = el.value.trim();
        clearError(name);

        if (rule.required && !val) {
          showError(name, `${rule.label} is required.`);
          valid = false;
        } else if (val && rule.minLength && val.length < rule.minLength) {
          showError(name, `${rule.label} must be at least ${rule.minLength} characters.`);
          valid = false;
        } else if (val && rule.pattern && !rule.pattern.test(val)) {
          showError(name, `Please enter a valid ${rule.label}.`);
          valid = false;
        }
      });

      // If validation fails, stop the fetch in contact.html from running
      if (!valid) e.preventDefault();
      // If valid, do NOT call e.preventDefault() — let contact.html's fetch handler take over
    });
  }

  /* -----------------------------------------------
     SMOOTH SCROLLING FOR ANCHOR LINKS
  ----------------------------------------------- */
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', (e) => {
      const target = document.querySelector(anchor.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  /* -----------------------------------------------
     PLAN TOGGLE (Monthly/Yearly)
  ----------------------------------------------- */
  const planToggle = document.getElementById('planToggle');
  if (planToggle) {
    const prices = {
      monthly: { basic: '4,999', premium: '9,999', elite: '19,999' },
      yearly: { basic: '44,999', premium: '89,999', elite: '1,79,999' },
    };
    planToggle.addEventListener('change', () => {
      const mode = planToggle.checked ? 'yearly' : 'monthly';
      const label = planToggle.checked ? '/year' : '/month';
      document.querySelectorAll('.plan-amount').forEach((el, i) => {
        const key = ['basic', 'premium', 'elite'][i];
        if (key) el.textContent = prices[mode][key];
      });
      document.querySelectorAll('.plan-period').forEach(el => {
        el.textContent = label;
      });
    });
  }

  /* -----------------------------------------------
     GALLERY LIGHTBOX (simple)
  ----------------------------------------------- */
  const galleryItems = document.querySelectorAll('.gallery-item');
  if (galleryItems.length) {
    galleryItems.forEach(item => {
      item.addEventListener('click', () => {
        const img = item.querySelector('img');
        if (!img) return;
        const overlay = document.createElement('div');
        overlay.style.cssText = `
          position:fixed;inset:0;background:rgba(0,0,0,0.9);z-index:9999;
          display:flex;align-items:center;justify-content:center;cursor:zoom-out;
          animation:fadeIn 0.3s ease;
        `;
        const picture = document.createElement('img');
        picture.src = img.src;
        picture.style.cssText = 'max-width:92vw;max-height:88vh;border-radius:12px;box-shadow:0 24px 80px rgba(0,0,0,0.5);';
        overlay.appendChild(picture);
        overlay.addEventListener('click', () => overlay.remove());
        document.body.appendChild(overlay);
      });
    });
  }

  /* -----------------------------------------------
     PAGE LOAD FADE IN
  ----------------------------------------------- */
  document.body.style.opacity = '0';
  document.body.style.transition = 'opacity 0.4s ease';
  requestAnimationFrame(() => {
    requestAnimationFrame(() => { document.body.style.opacity = '1'; });
  });

});