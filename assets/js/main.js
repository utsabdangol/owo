/**
 * OWO E-Commerce — Main JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {

  // ── Navbar Scroll Effect ──────────────────────────────────
  const navbar = document.getElementById('navbar');
  window.addEventListener('scroll', () => {
    if (navbar) {
      navbar.style.boxShadow = window.scrollY > 20
        ? '0 2px 20px rgba(0,0,0,.08)' : 'none';
    }
  }, { passive: true });

  // ── Mobile Hamburger ──────────────────────────────────────
  const hamburger = document.getElementById('hamburger');
  const navLinks  = document.getElementById('navLinks');
  if (hamburger && navLinks) {
    hamburger.addEventListener('click', () => {
      const open = navLinks.classList.toggle('open');
      hamburger.setAttribute('aria-expanded', open);
      hamburger.classList.toggle('is-open', open);
      document.body.style.overflow = open ? 'hidden' : '';
    });
    // Close on outside click
    document.addEventListener('click', (e) => {
      if (!navbar.contains(e.target) && navLinks.classList.contains('open')) {
        navLinks.classList.remove('open');
        hamburger.setAttribute('aria-expanded', 'false');
        hamburger.classList.remove('is-open');
        document.body.style.overflow = '';
      }
    });
  }

  // ── Auto-dismiss Flash ────────────────────────────────────
  const flash = document.getElementById('flashMsg');
  if (flash) {
    setTimeout(() => {
      flash.style.transition = 'opacity .4s ease';
      flash.style.opacity = '0';
      setTimeout(() => flash.remove(), 400);
    }, 4000);
  }

  // ── Size Selector ─────────────────────────────────────────
  document.querySelectorAll('.size-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      btn.closest('.size-options').querySelectorAll('.size-btn')
         .forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const inp = document.getElementById('selectedSize');
      if (inp) inp.value = btn.dataset.size;
    });
  });

  // ── Quantity Controls ─────────────────────────────────────
  document.querySelectorAll('.qty-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = btn.closest('.qty-control, .cart-qty')
                       ?.querySelector('input[type="number"]');
      if (!input) return;
      let val = parseInt(input.value) || 1;
      if (btn.dataset.action === 'inc') val = Math.min(val + 1, 99);
      if (btn.dataset.action === 'dec') val = Math.max(val - 1, 1);
      input.value = val;
      input.dispatchEvent(new Event('change', { bubbles: true }));
    });
  });

  // ── Cart Quantity AJAX Update ─────────────────────────────
  document.querySelectorAll('.cart-qty-input').forEach(input => {
    let timer;
    input.addEventListener('change', () => {
      clearTimeout(timer);
      timer = setTimeout(() => {
        const form = input.closest('form');
        if (form) form.submit();
      }, 600);
    });
  });

  // ── Image Gallery Switcher (product detail) ───────────────
  document.querySelectorAll('.gallery-thumb').forEach(thumb => {
    thumb.addEventListener('click', () => {
      const mainImg = document.querySelector('.gallery-main img');
      if (mainImg && thumb.dataset.src) {
        mainImg.src = thumb.dataset.src;
        document.querySelectorAll('.gallery-thumb')
                .forEach(t => t.classList.remove('active'));
        thumb.classList.add('active');
      }
    });
  });

  // ── Filter Chips (shop page) ──────────────────────────────
  document.querySelectorAll('.filter-chip[data-cat]').forEach(chip => {
    chip.addEventListener('click', () => {
      const url = new URL(window.location.href);
      const cat = chip.dataset.cat;
      if (cat === 'all') {
        url.searchParams.delete('category');
      } else {
        url.searchParams.set('category', cat);
      }
      url.searchParams.delete('page');
      window.location.href = url.toString();
    });
  });

  // ── Khalti Payment Button Loading State ───────────────────
  const khaltiBtn = document.querySelector('.khalti-btn');
  if (khaltiBtn) {
    khaltiBtn.closest('form')?.addEventListener('submit', () => {
      khaltiBtn.disabled = true;
      khaltiBtn.innerHTML = '<span class="spinner"></span> Redirecting to Khalti…';
    });
  }

  // ── Admin: Confirm Delete ─────────────────────────────────
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', (e) => {
      if (!confirm(el.dataset.confirm || 'Are you sure?')) {
        e.preventDefault();
      }
    });
  });

  // ── Hamburger animation styles ────────────────────────────
  const style = document.createElement('style');
  style.textContent = `
    .hamburger.is-open span:nth-child(1){transform:translateY(7px) rotate(45deg)}
    .hamburger.is-open span:nth-child(2){opacity:0}
    .hamburger.is-open span:nth-child(3){transform:translateY(-7px) rotate(-45deg)}
    .spinner{display:inline-block;width:16px;height:16px;border:2px solid rgba(255,255,255,.4);
      border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite}
    @keyframes spin{to{transform:rotate(360deg)}}
  `;
  document.head.appendChild(style);

});
