<?php
/**
 * PwanDeal - Refined Footer
 * Enhanced with Social proof, Newsletter, and Smooth micro-interactions.
 */

if (!isset($base_url)) {
    $base_url = '/pwandeal';
}

$is_logged_in = isset($_SESSION['user_id']);
$is_admin = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == 1);
?>

</main> 

<footer class="footer text-white py-5 mt-auto" style="background: #011627; border-top: 5px solid #028090;">
    <div class="container">
        <div class="row g-5 mb-5">
            
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center mb-3">
                    <span class="fs-3 me-2">⭐</span>
                    <h4 class="fw-bold mb-0">Pwan<span style="color: #00BFB2;">Deal</span></h4>
                </div>
                <p class="small opacity-75 pe-lg-4" style="line-height: 1.8;">
                    Empowering Pwani University students to turn skills into income and find affordable campus essentials. Join 100+ verified students today.
                </p>
                <div class="d-flex gap-2 mt-4">
                    <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
                    <a href="https://wa.me/254111602678" class="social-icon"><i class="bi bi-whatsapp"></i></a>
                </div>
            </div>

            <div class="col-lg-2 col-6">
                <h6 class="text-uppercase fw-bold mb-4 small tracking-wider text-white-50">Marketplace</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="<?= $base_url; ?>/listings/view.php">Browse All</a></li>
                    <li><a href="<?= $base_url; ?>/listings/view.php?category=1">Hostels</a></li>
                    <li><a href="<?= $base_url; ?>/listings/view.php?category=2">Electronics</a></li>
                    <li><a href="<?= $base_url; ?>/listings/create.php">Start Selling</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-6">
                <h6 class="text-uppercase fw-bold mb-4 small tracking-wider text-white-50">Student Hub</h6>
                <ul class="list-unstyled footer-links">
                    <?php if (!$is_logged_in): ?>
                        <li><a href="<?= $base_url; ?>/auth/register.php">Create Account</a></li>
                        <li><a href="<?= $base_url; ?>/auth/login.php">Login</a></li>
                    <?php else: ?>
                        <li><a href="<?= $base_url; ?>/profile/view.php?id=<?= $_SESSION['user_id'] ?>">My Profile</a></li>
                        <li><a href="<?= $base_url; ?>/messages/inbox.php">Messages</a></li>
                    <?php endif; ?>
                    <li><a href="<?= $base_url; ?>/safety.php">Safety Tips</a></li>
                </ul>
            </div>

            <div class="col-lg-4">
                <h6 class="text-uppercase fw-bold mb-4 small tracking-wider text-white-50">Never Miss a Deal</h6>
                <p class="small opacity-75 mb-3">Get notified when new hostels or hot deals are posted.</p>
                <form action="#" class="newsletter-form position-relative">
                    <input type="email" class="form-control rounded-pill bg-dark border-0 text-white py-3 ps-4" 
                           placeholder="Enter your PU email" style="font-size: 0.9rem;">
                    <button type="submit" class="btn btn-primary rounded-circle position-absolute end-0 top-0 mt-1 me-1" 
                            style="width: 42px; height: 42px;">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </form>
                <div class="mt-4 small opacity-50">
                    <i class="bi bi-geo-alt-fill me-1"></i> Kilifi, Pwani University Campus
                </div>
            </div>
        </div>

        <hr style="border-color: rgba(255,255,255,0.05);">

        <div class="row align-items-center pt-3 pb-2">
            <div class="col-md-6 text-center text-md-start">
                <p class="x-small mb-0 opacity-50">
                    &copy; <?= date('Y'); ?> <strong>PwanDeal</strong>. Designed for Pwanians.
                </p>
            </div>
            <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                <a href="<?= $base_url; ?>/privacy.php" class="footer-sub-link me-3">Privacy</a>
                <a href="<?= $base_url; ?>/terms.php" class="footer-sub-link">Terms</a>
                <a href="<?= $base_url; ?>/cookies.php" class="footer-sub-link">of service</a>
            </div>
        </div>
    </div>
</footer>

<button type="button" id="backToTopBtn" class="btn shadow-lg d-flex align-items-center justify-content-center">
    <i class="bi bi-arrow-up-short fs-4"></i>
</button>

<style>
    /* Tracking / Spacing */
    .tracking-wider { letter-spacing: 1.5px; }
    .x-small { font-size: 0.75rem; }

    /* Footer Link Styles */
    .footer-links li { margin-bottom: 12px; }
    .footer-links a { 
        color: rgba(255,255,255,0.6); 
        text-decoration: none; 
        font-size: 0.9rem;
        transition: 0.3s ease;
    }
    .footer-links a:hover { 
        color: #00BFB2; 
        padding-left: 8px;
    }

    /* Social Icons */
    .social-icon {
        width: 38px;
        height: 38px;
        background: rgba(255,255,255,0.05);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        text-decoration: none;
        transition: 0.3s;
    }
    .social-icon:hover {
        background: #028090;
        transform: translateY(-3px);
        color: white;
    }

    /* Form Styling */
    .newsletter-form input:focus {
        background: rgba(255,255,255,0.1) !important;
        box-shadow: none;
        color: white;
    }

    /* Footer Sub Links */
    .footer-sub-link { 
        color: rgba(255,255,255,0.4); 
        text-decoration: none; 
        font-size: 0.8rem; 
    }
    .footer-sub-link:hover { color: #028090; }

    /* Back to Top Refined */
    #backToTopBtn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: #028090;
        color: white;
        border-radius: 12px;
        width: 45px;
        height: 45px;
        z-index: 1000;
        border: none;
        transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        opacity: 0;
        visibility: hidden;
    }
    #backToTopBtn.show {
        opacity: 1;
        visibility: visible;
    }
    #backToTopBtn:hover {
        background: #00BFB2;
        transform: scale(1.1);
    }
</style>

<script>
// Back to Top Logic
const backToTopBtn = document.getElementById("backToTopBtn");
window.onscroll = function() {
    if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
        backToTopBtn.classList.add("show");
    } else {
        backToTopBtn.classList.remove("show");
    }
};
backToTopBtn.onclick = function() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>