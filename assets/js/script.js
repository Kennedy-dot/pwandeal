/**
 * PwanDeal - Main Application Logic
 * Refined for Pwani University Marketplace
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Optimized Back to Top Button
    const backToTopBtn = document.getElementById('backToTopBtn');
    if (backToTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 400) {
                backToTopBtn.style.display = 'block';
                // Small delay to trigger CSS transition
                setTimeout(() => backToTopBtn.classList.add('show'), 10);
            } else {
                backToTopBtn.classList.remove('show');
                setTimeout(() => { 
                    if(!backToTopBtn.classList.contains('show')) backToTopBtn.style.display = 'none'; 
                }, 300);
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
        if (linkPath && linkPath !== '#' && linkPath !== '/') {
            // Remove relative dots and check if path matches
            const cleanLink = linkPath.replace(/\.\.\//g, '').replace('./', '');
            if (currentPath.includes(cleanLink)) {
                link.classList.add('active');
            }
        }
    });

    // 3. Auto-Dismiss Alerts (Bootstrap 5 Compatible)
    const alerts = document.querySelectorAll('.alert-dismissible:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (typeof bootstrap !== 'undefined') {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                if (bsAlert) bsAlert.close();
            } else {
                alert.remove(); // Fallback if bootstrap JS isn't loaded
            }
        }, 5000);
    });

    // 4. Global Character Counter
    // Usage: <textarea maxlength="200" data-counter="bio-count"></textarea>
    //        <span id="bio-count">0</span>/200
    document.querySelectorAll('textarea[maxlength]').forEach(area => {
        const counterId = area.getAttribute('data-counter') || 'char-count';
        const counter = document.getElementById(counterId);
        if (counter) {
            area.addEventListener('input', () => {
                counter.textContent = area.value.length;
                // Add warning color if near limit (90% capacity)
                if (area.value.length >= (area.maxLength * 0.9)) {
                    counter.classList.add('text-danger');
                } else {
                    counter.classList.remove('text-danger');
                }
            });
        }
    });
});

/**
 * Kenyan Currency Formatter
 * Usage: formatPrice(2500) -> KSh 2,500
 */
const formatPrice = (price) => {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
        minimumFractionDigits: 0
    }).format(price).replace('KES', 'KSh');
};

/**
 * Kenyan Phone & Email Validator
 */
const Validator = {
    email: (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email),
    // Validates +254..., 07..., or 01... (Kenyan standards)
    phone: (phone) => /^(?:\+254|0)[17]\d{8}$/.test(phone),
    password: (pass) => pass.length >= 8
};

/**
 * Image Preview Logic
 */
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (preview && input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Clipboard Utility with visual feedback
 */
async function copyToClipboard(text, element) {
    try {
        await navigator.clipboard.writeText(text);
        const originalHTML = element.innerHTML;
        element.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> <small class="text-success">Copied!</small>';
        element.classList.add('active');
        
        setTimeout(() => { 
            element.innerHTML = originalHTML; 
            element.classList.remove('active');
        }, 2000);
    } catch (err) {
        console.error('Copy failed', err);
    }
}

/**
 * Time Ago Utility (Human Readable)
 */
function timeAgo(date) {
    const seconds = Math.floor((new Date() - new Date(date)) / 1000);
    if (seconds < 60) return "Just now";
    
    const intervals = {
        'year': 31536000,
        'month': 2592000,
        'day': 86400,
        'hour': 3600,
        'minute': 60
    };

    for (let key in intervals) {
        const counter = Math.floor(seconds / intervals[key]);
        if (counter >= 1) {
            return counter === 1 ? `1 ${key} ago` : `${counter} ${key}s ago`;
        }
    }
}