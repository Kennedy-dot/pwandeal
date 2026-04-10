/**
 * PwanDeal - Main Application Logic
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Optimized Back to Top Button
    const backToTopBtn = document.getElementById('backToTopBtn');
    if (backToTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 400) {
                backToTopBtn.style.display = 'block';
                setTimeout(() => backToTopBtn.classList.add('show'), 10);
            } else {
                backToTopBtn.classList.remove('show');
                setTimeout(() => { if(!backToTopBtn.classList.contains('show')) backToTopBtn.style.display = 'none'; }, 300);
            }
        }, { passive: true });

        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // 2. Intelligent Active Nav Links
    const currentPath = window.location.pathname;
    document.querySelectorAll('.nav-link').forEach(link => {
        const linkPath = link.getAttribute('href');
        if (linkPath && currentPath.includes(linkPath.replace('..', ''))) {
            link.classList.add('active');
        }
    });

    // 3. Auto-Dismiss Alerts (Bootstrap Compatible)
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });

    // 4. Global Character Counter (For Bio/Description textareas)
    const textAreas = document.querySelectorAll('textarea[maxlength]');
    textAreas.forEach(area => {
        const counter = document.getElementById('char-count');
        if (counter) {
            area.addEventListener('input', () => {
                counter.textContent = area.value.length;
            });
        }
    });
});

/**
 * Kenyan Currency Formatter
 */
const formatPrice = (price) => {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
        minimumFractionDigits: 0
    }).format(price);
};

/**
 * Universal Form Validator
 */
const Validator = {
    email: (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email),
    phone: (phone) => /^(?:\+254|0)[17]\d{8}$/.test(phone),
    password: (pass) => pass.length >= 8
};

/**
 * Image Preview Logic
 */
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input && input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Clipboard Utility
 */
async function copyToClipboard(text, element) {
    try {
        await navigator.clipboard.writeText(text);
        const originalHTML = element.innerHTML;
        element.innerHTML = '<span class="text-success">✅ Copied!</span>';
        setTimeout(() => { element.innerHTML = originalHTML; }, 2000);
    } catch (err) {
        console.error('Copy failed', err);
    }
}

/**
 * Time Ago Utility
 */
function timeAgo(date) {
    const seconds = Math.floor((new Date() - new Date(date)) / 1000);
    if (seconds < 60) return "Just now";
    
    const intervals = {
        y: 31536000,
        mo: 2592000,
        d: 86400,
        h: 3600,
        m: 60
    };

    for (let key in intervals) {
        const counter = Math.floor(seconds / intervals[key]);
        if (counter >= 1) return `${counter}${key} ago`;
    }
}