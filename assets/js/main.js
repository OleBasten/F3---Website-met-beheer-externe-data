'use strict';

document.addEventListener('DOMContentLoaded', () => {

    // ── Navbar scroll effect ─────────────────────────────────
    const navbar = document.querySelector('.gv-navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 30);
        }, { passive: true });
    }

    // ── Auto-dismiss flash alerts ────────────────────────────
    document.querySelectorAll('.gv-flash').forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert?.close();
        }, 5000);
    });

    // ── Delete bevestiging ───────────────────────────────────
    // Verwijder-buttons die een data-confirm attribuut hebben
    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', e => {
            if (!confirm(btn.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    // ── Zoekformulier debounce (live search) ─────────────────
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        let timer;
        searchInput.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                searchInput.closest('form')?.submit();
            }, 500);
        });
    }

    // ── Rating preview in formulier ──────────────────────────
    const ratingInput = document.getElementById('rating');
    const ratingDisplay = document.getElementById('ratingDisplay');
    if (ratingInput && ratingDisplay) {
        const update = () => {
            const val = parseFloat(ratingInput.value);
            ratingDisplay.textContent = isNaN(val) ? '–' : val.toFixed(1);
        };
        ratingInput.addEventListener('input', update);
        update();
    }

    // ── Cover URL preview ─────────────────────────────────────
    const coverUrlInput = document.getElementById('cover_url');
    const coverPreview  = document.getElementById('coverPreview');
    if (coverUrlInput && coverPreview) {
        coverUrlInput.addEventListener('input', () => {
            const url = coverUrlInput.value.trim();
            if (url) {
                coverPreview.src = url;
                coverPreview.style.display = 'block';
                coverPreview.onerror = () => { coverPreview.style.display = 'none'; };
            } else {
                coverPreview.style.display = 'none';
            }
        });
    }

    // ── Fade-up observatie ────────────────────────────────────
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationPlayState = 'running';
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.fade-up').forEach(el => {
            el.style.animationPlayState = 'paused';
            observer.observe(el);
        });
    }
});