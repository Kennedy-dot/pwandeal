<?php
/**
 * PwanDeal - Contact Admin / Support Page
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$page_title = 'Contact Support';
$base_url = '.';

$success = false;
$error = '';
$form_data = [
    'name' => '',
    'email' => '',
    'subject' => '',
    'message' => ''
];

// Pre-fill email if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_stmt = $conn->prepare("SELECT name, email FROM users WHERE user_id = ?");
    $user_stmt->bind_param('i', $user_id);
    $user_stmt->execute();
    $user = $user_stmt->get_result()->fetch_assoc();
    
    if ($user) {
        $form_data['name'] = $user['name'];
        $form_data['email'] = $user['email'];
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Security token expired. Please try again.';
    } else {
        // Collect & Sanitize Form Data
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        // Validation
        $form_data['name'] = $name;
        $form_data['email'] = $email;
        $form_data['subject'] = $subject;
        $form_data['message'] = $message;

        if (empty($name)) {
            $error = 'Please enter your name.';
        } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (empty($subject) || strlen($subject) < 5) {
            $error = 'Subject must be at least 5 characters.';
        } elseif (empty($message) || strlen($message) < 10) {
            $error = 'Message must be at least 10 characters.';
        } else {
            // Prepare email content
            $admin_email = 'admin@pwandeal.pw'; // CHANGE THIS TO YOUR ACTUAL ADMIN EMAIL
            $sender_name = htmlspecialchars($name);
            $sender_email = htmlspecialchars($email);
            $email_subject = htmlspecialchars($subject);
            $email_message = htmlspecialchars($message);

            // Email Headers
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
            $headers .= "From: " . $sender_email . "\r\n";
            $headers .= "Reply-To: " . $sender_email . "\r\n";

            // Email Body - Admin receives this
            $admin_body = "
            <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; background: #f9f9f9; padding: 20px; border-radius: 8px; }
                        .header { background: linear-gradient(135deg, #028090 0%, #1e2761 100%); color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; }
                        .content { background: white; padding: 20px; }
                        .field { margin-bottom: 20px; }
                        .label { font-weight: bold; color: #028090; margin-bottom: 5px; }
                        .value { background: #f5f5f5; padding: 10px; border-radius: 5px; }
                        .footer { font-size: 12px; color: #999; text-align: center; padding-top: 10px; border-top: 1px solid #ddd; margin-top: 20px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>📩 New Support Message</h2>
                        </div>
                        <div class='content'>
                            <div class='field'>
                                <div class='label'>From:</div>
                                <div class='value'>{$sender_name} ({$sender_email})</div>
                            </div>
                            <div class='field'>
                                <div class='label'>Subject:</div>
                                <div class='value'>{$email_subject}</div>
                            </div>
                            <div class='field'>
                                <div class='label'>Message:</div>
                                <div class='value' style='white-space: pre-wrap; word-wrap: break-word;'>{$email_message}</div>
                            </div>
                            <div class='footer'>
                                <p>Received at: " . date('Y-m-d H:i:s') . "</p>
                            </div>
                        </div>
                    </div>
                </body>
            </html>";

            // Email Body - Confirmation email to user
            $user_body = "
            <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; background: #f9f9f9; padding: 20px; border-radius: 8px; }
                        .header { background: linear-gradient(135deg, #028090 0%, #1e2761 100%); color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; }
                        .content { background: white; padding: 20px; }
                        .footer { font-size: 12px; color: #999; text-align: center; padding-top: 10px; border-top: 1px solid #ddd; margin-top: 20px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>✅ We Received Your Message</h2>
                        </div>
                        <div class='content'>
                            <p>Hi {$sender_name},</p>
                            <p>Thank you for contacting PwanDeal Support. We have received your message and our team will review it shortly.</p>
                            <p><strong>Your Message Subject:</strong> {$email_subject}</p>
                            <p>We typically respond within 24 hours. If your issue is urgent, you can also reach us at:</p>
                            <ul>
                                <li><strong>Email:</strong> support@pwandeal.pw</li>
                                <li><strong>WhatsApp:</strong> +254 111 602 678</li>
                            </ul>
                            <p>Best regards,<br><strong>PwanDeal Support Team</strong></p>
                            <div class='footer'>
                                <p>This is an automated response. Please do not reply to this email.</p>
                            </div>
                        </div>
                    </div>
                </body>
            </html>";

            // Send Email to Admin
            $admin_subject = "[PwanDeal Support] " . $email_subject;
            $admin_sent = mail($admin_email, $admin_subject, $admin_body, $headers);

            // Send Confirmation Email to User
            $user_subject = "PwanDeal Support - We Received Your Message";
            $user_headers = "MIME-Version: 1.0" . "\r\n";
            $user_headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
            $user_headers .= "From: support@pwandeal.pw" . "\r\n";
            $user_sent = mail($sender_email, $user_subject, $user_body, $user_headers);

            if ($admin_sent && $user_sent) {
                $success = true;
                // Clear form data
                $form_data = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
                // Log the contact submission
                error_log("Contact Form Submitted - From: {$sender_name} ({$sender_email}), Subject: {$email_subject}");
            } else {
                $error = 'Failed to send message. Please try again or contact us directly.';
                error_log("Email sending failed - Admin sent: " . ($admin_sent ? 'Yes' : 'No') . ", User sent: " . ($user_sent ? 'Yes' : 'No'));
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none" style="color: #028090;">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Contact Support</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Contact Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4 p-md-5 rounded-4">
                <div class="d-flex align-items-center mb-4">
                    <div class="p-3 bg-light rounded-circle me-3">
                        <span class="fs-2">💬</span>
                    </div>
                    <div>
                        <h1 class="fw-bold mb-0" style="color: #1e2761;">Contact Support</h1>
                        <p class="text-muted small mb-0">We're here to help! Send us a message.</p>
                    </div>
                </div>

                <!-- Success Message -->
                <?php if ($success): ?>
                    <div class="alert alert-success border-0 shadow-sm mb-4" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill me-3 fs-5"></i>
                            <div>
                                <h5 class="mb-1 fw-bold">Message Sent Successfully! ✨</h5>
                                <p class="mb-0 small">Thank you for contacting us. We'll get back to you within 24 hours. A confirmation email has been sent to your inbox.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Error Message -->
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger border-0 shadow-sm mb-4" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill me-3 fs-5"></i>
                            <div>
                                <h5 class="mb-1 fw-bold">Error</h5>
                                <p class="mb-0 small"><?= htmlspecialchars($error) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Contact Form -->
                <form method="POST" class="mt-4">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Your Name</label>
                        <input type="text" 
                               name="name" 
                               class="form-control form-control-lg bg-light border-0" 
                               placeholder="John Doe"
                               value="<?= htmlspecialchars($form_data['name']) ?>"
                               required>
                        <small class="text-muted">Help us know who you are</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Address</label>
                        <input type="email" 
                               name="email" 
                               class="form-control form-control-lg bg-light border-0" 
                               placeholder="your.email@pwani.ac.ke"
                               value="<?= htmlspecialchars($form_data['email']) ?>"
                               required>
                        <small class="text-muted">We'll use this to respond to you</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject</label>
                        <input type="text" 
                               name="subject" 
                               class="form-control form-control-lg bg-light border-0" 
                               placeholder="e.g., Account Issue, Payment Problem, Feature Request"
                               value="<?= htmlspecialchars($form_data['subject']) ?>"
                               required>
                        <small class="text-muted">Brief description of your issue</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Message</label>
                        <textarea name="message" 
                                  class="form-control bg-light border-0" 
                                  rows="5" 
                                  placeholder="Please provide details about your inquiry..."
                                  required><?= htmlspecialchars($form_data['message']) ?></textarea>
                        <small class="text-muted d-block mt-2">
                            <span id="char-count">0</span>/1000 characters
                        </small>
                    </div>

                    <div class="d-grid gap-2 pt-3 border-top">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold rounded-pill shadow-sm" style="background-color: #028090; border: none;">
                            <i class="bi bi-send-fill me-2"></i> Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar - Contact Info & FAQ -->
        <div class="col-lg-4">
            <!-- Quick Contact Info -->
            <div class="card border-0 shadow-sm p-4 rounded-4 mb-4" style="background: linear-gradient(135deg, #028090 0%, #1e2761 100%); color: white;">
                <h5 class="fw-bold mb-3">📞 Other Ways to Reach Us</h5>
                
                <div class="mb-3">
                    <p class="small opacity-75 mb-2">Email Support</p>
                    <a href="mailto:support@pwandeal.pw" class="text-white fw-bold text-decoration-none">
                        <i class="bi bi-envelope-fill me-2"></i> support@pwandeal.pw
                    </a>
                </div>

                <div class="mb-3">
                    <p class="small opacity-75 mb-2">WhatsApp (Quick Response)</p>
                    <a href="https://wa.me/254111602678" target="_blank" class="text-white fw-bold text-decoration-none">
                        <i class="bi bi-whatsapp me-2"></i> +254 111 602 678
                    </a>
                </div>

                <div>
                    <p class="small opacity-75 mb-2">Response Time</p>
                    <p class="small mb-0">🕐 Usually within 24 hours</p>
                </div>
            </div>

            <!-- FAQ -->
            <div class="card border-0 shadow-sm p-4 rounded-4 mb-4">
                <h6 class="fw-bold mb-3 text-dark">❓ Quick FAQ</h6>
                <div class="accordion accordion-flush" id="faqAccordion">
                    <div class="accordion-item border-0 mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold p-0 text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                How do I reset my password?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body p-0 pt-2 text-small text-muted">
                                Click "Forgot Password" on the login page. We'll send you a reset link to your email.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold p-0 text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Is my data safe?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body p-0 pt-2 text-small text-muted">
                                Yes! We use encryption and never sell your data. See our <a href="privacy.php" class="text-decoration-none" style="color: #028090;">Privacy Policy</a>.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold p-0 text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                How do I report a scam?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body p-0 pt-2 text-small text-muted">
                                Contact us immediately with details. We investigate all reports and take action against violators.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Support Center Link -->
            <div class="card border-0 shadow-sm p-4 rounded-4 bg-light">
                <h6 class="fw-bold mb-2 text-dark">📚 Need More Help?</h6>
                <p class="small text-muted mb-3">Check our complete documentation and guides.</p>
                <a href="safety.php" class="btn btn-sm btn-outline-primary rounded-pill fw-bold">
                    <i class="bi bi-shield-check me-1"></i> Safety Tips
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Character counter for message field
document.querySelector('textarea[name="message"]').addEventListener('input', function() {
    document.getElementById('char-count').textContent = this.value.length;
});
</script>

<?php include 'includes/footer.php'; ?>