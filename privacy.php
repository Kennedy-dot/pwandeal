<?php
/**
 * PwanDeal - Updated Privacy Policy
 * Comprehensive Version with Data Protection & User Rights
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';

$page_title = 'Privacy Policy';
$base_url = '.'; // Relative to root

include 'includes/header.php';
?>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none" style="color: #028090;">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Privacy Policy</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4 p-md-5 rounded-4">
                <div class="d-flex align-items-center mb-5">
                    <div class="p-3 bg-light rounded-circle me-3">
                        <span class="fs-2">🛡️</span>
                    </div>
                    <div>
                        <h1 class="fw-bold mb-0" style="color: #1e2761;">Privacy Policy</h1>
                        <p class="text-muted small mb-0">Last updated: April 28, 2026</p>
                    </div>
                </div>

                <!-- 1. Introduction -->
                <section class="mb-5">
                    <h4 class="fw-bold mb-3" style="color: #028090;">1. Introduction</h4>
                    <p class="text-secondary">PwanDeal ("we," "us," "our," or "the Platform") is built exclusively for the Pwani University community. We value your trust and are committed to protecting your personal data and privacy rights. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our marketplace platform.</p>
                    
                    <div class="alert alert-info border-0 bg-light p-3 rounded-3 mt-3">
                        <strong>📌 Key Point:</strong> We are dedicated to ensuring transparency. If you have questions about this policy, please contact us at any time.
                    </div>
                </section>

                <!-- 2. Information We Collect -->
                <section class="mb-5">
                    <h4 class="fw-bold mb-3" style="color: #028090;">2. Information We Collect</h4>
                    <p class="text-secondary mb-3">We collect information in several ways to provide and improve our services:</p>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-4 border rounded-3 h-100" style="background: #f9f9f9;">
                                <h6 class="fw-bold mb-2" style="color: #028090;">📝 Account Information</h6>
                                <ul class="small text-muted mb-0 ps-3">
                                    <li>Full name</li>
                                    <li>University email (@pwani.ac.ke)</li>
                                    <li>Phone number</li>
                                    <li>Profile photo/avatar</li>
                                    <li>Account creation date</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-4 border rounded-3 h-100" style="background: #f9f9f9;">
                                <h6 class="fw-bold mb-2" style="color: #028090;">📸 Content You Create</h6>
                                <ul class="small text-muted mb-0 ps-3">
                                    <li>Service listings & descriptions</li>
                                    <li>Images and attachments</li>
                                    <li>Reviews and ratings</li>
                                    <li>Messages to other users</li>
                                    <li>Profile bio and preferences</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-4 border rounded-3 h-100" style="background: #f9f9f9;">
                                <h6 class="fw-bold mb-2" style="color: #028090;">💻 Technical Data</h6>
                                <ul class="small text-muted mb-0 ps-3">
                                    <li>IP address</li>
                                    <li>Browser type and version</li>
                                    <li>Device information</li>
                                    <li>Pages visited and time spent</li>
                                    <li>Cookies and similar technologies</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-4 border rounded-3 h-100" style="background: #f9f9f9;">
                                <h6 class="fw-bold mb-2" style="color: #028090;">🔐 Transaction Data</h6>
                                <ul class="small text-muted mb-0 ps-3">
                                    <li>Service listings created</li>
                                    <li>Searches performed</li>
                                    <li>Payment information*</li>
                                    <li>Transaction history</li>
                                    <li>Dispute records</li>
                                </ul>
                                <small class="text-muted d-block mt-2">*Handled by secure third-party processors</small>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 3. How We Use Your Data -->
                <section class="mb-5">
                    <h4 class="fw-bold mb-3" style="color: #028090;">3. How We Use Your Information</h4>
                    <p class="text-secondary mb-3">We use the information we collect for the following purposes:</p>
                    
                    <ul class="text-secondary ps-3">
                        <li class="mb-2"><strong>Account Management:</strong> To create and manage your account, verify your identity, and provide customer support.</li>
                        <li class="mb-2"><strong>Service Delivery:</strong> To connect service providers with buyers, facilitate transactions, and enable communication between users.</li>
                        <li class="mb-2"><strong>Trust & Safety:</strong> To prevent fraud, abuse, and misuse of the platform. This includes user verification and dispute resolution.</li>
                        <li class="mb-2"><strong>Quality Improvement:</strong> To analyze platform usage, identify bugs, and develop new features that enhance user experience.</li>
                        <li class="mb-2"><strong>Communication:</strong> To send you service updates, policy changes, security alerts, and support responses.</li>
                        <li class="mb-2"><strong>Legal Compliance:</strong> To comply with applicable laws, regulations, and respond to lawful requests from authorities.</li>
                        <li class="mb-2"><strong>Rating System:</strong> To maintain the integrity of our review and rating system, helping users make informed decisions.</li>
                        <li><strong>Marketing (Optional):</strong> With your consent, to send news, updates, and promotions about new features.</li>
                    </ul>
                </section>

                <!-- 4. Data Sharing & Visibility -->
                <section class="mb-5">
                    <h4 class="fw-bold mb-3" style="color: #028090;">4. Data Sharing & Visibility</h4>
                    
                    <div class="mb-4">
                        <h6 class="fw-bold mb-2">What's Public</h6>
                        <p class="text-secondary small">The following information is visible to other registered users on PwanDeal:</p>
                        <ul class="text-secondary small ps-3">
                            <li>Your profile name and avatar</li>
                            <li>Your service listings and descriptions</li>
                            <li>Your average rating and review count</li>
                            <li>Your account age and active status</li>
                            <li>Messages you send to other users</li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold mb-2">What's Private</h6>
                        <p class="text-secondary small">The following information is NEVER shared without your consent:</p>
                        <ul class="text-secondary small ps-3">
                            <li>Your phone number (shared only with buyers when you post listings)</li>
                            <li>Your email address (shared only with admin support staff)</li>
                            <li>Your password (never stored in plain text, always encrypted)</li>
                            <li>Your IP address and browser information</li>
                            <li>Your personal payment details</li>
                        </ul>
                    </div>

                    <div class="alert alert-success border-0 bg-light p-3 rounded-3">
                        <strong>✓ Data Protection Promise:</strong> We <strong>NEVER</strong> sell, rent, lease, or share your personal data with third-party advertisers, marketers, or external organizations. Period.
                    </div>
                </section>

                <!-- 5. Security Measures -->
                <section class="mb-5">
                    <h4 class="fw-bold mb-3" style="color: #028090;">5. Data Security & Protection</h4>
                    <p class="text-secondary mb-3">We implement comprehensive security measures to protect your data:</p>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <h6 class="fw-bold mb-2">🔐 Technical Security</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li>SSL/TLS encryption for all data in transit</li>
                                    <li>Password hashing with bcrypt</li>
                                    <li>Secure database storage</li>
                                    <li>Regular security audits</li>
                                    <li>Firewall protection</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <h6 class="fw-bold mb-2">👥 Access Controls</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li>Limited staff access</li>
                                    <li>Admin verification required</li>
                                    <li>Session timeouts</li>
                                    <li>Two-factor authentication (available)</li>
                                    <li>Activity logging</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning border-0 bg-light p-3 rounded-3 mt-3">
                        <strong>⚠️ Note:</strong> While we employ industry-standard security measures, no system is 100% secure. We encourage you to use strong passwords and enable two-factor authentication.
                    </div>
                </section>

                <!-- 6. Your Data Rights -->
                <section class="mb-5">
                    <h4 class="fw-bold mb-3" style="color: #028090;">6. Your Rights & Choices</h4>
                    <p class="text-secondary mb-3">You have the following rights regarding your personal data:</p>
                    
                    <ul class="text-secondary ps-3">
                        <li class="mb-2"><strong>Right to Access:</strong> You can request a copy of all personal data we hold about you.</li>
                        <li class="mb-2"><strong>Right to Correction:</strong> You can update or correct inaccurate information in your profile.</li>
                        <li class="mb-2"><strong>Right to Deletion:</strong> You can request deletion of your account and associated data (subject to legal retention requirements).</li>
                        <li class="mb-2"><strong>Right to Data Portability:</strong> You can request your data in a portable format.</li>
                        <li class="mb-2"><strong>Right to Opt-Out:</strong> You can unsubscribe from marketing communications at any time.</li>
                        <li class="mb-2"><strong>Right to Object:</strong> You can object to certain types of data processing.</li>
                    </ul>

                    <p class="text-secondary mt-4"><strong>To exercise these rights, contact us at:</strong> <a href="contact.php" class="fw-bold" style="color: #028090;">Contact Support Form</a></p>
                </section>

                <!-- 7. Data Retention -->
                <section class="mb-5">
                    <h4 class="fw-bold mb-3" style="color: #028090;">7. How Long We Keep Your Data</h4>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless">
                            <thead>
                                <tr style="background-color: #f0f0f0;">
                                    <th class="fw-bold">Data Type</th>
                                    <th class="fw-bold">Retention Period</th>
                                </tr>
                            </thead>
                            <tbody class="text-secondary small">
                                <tr>
                                    <td>Active Account Data</td>
                                    <td>For the duration of your account</td>
                                </tr>
                                <tr>
                                    <td>Inactive Account</td>
                                    <td>2 years after last login</td>
                                </tr>
                                <tr>
                                    <td>Transaction Records</td>
                                    <td>5 years (for legal/tax compliance)</td>
                                </tr>
                                <tr>
                                    <td>Messages & Communication</td>
                                    <td>2 years after conversation ends</td>
                                </tr>
                                <tr>
                                    <td>Reviews & Ratings</td>
                                    <td>For the lifetime of the listing</td>
                                </tr>
                                <tr>
                                    <td>Dispute Records</td>
                                    <td>3 years after resolution</td>
                                </tr>
                                <tr>
                                    <td>Deleted Account</td>
                                    <td>90 days (backup recovery period)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- 8. Cookies & Tracking -->
                <section class="mb-5">
                    <h4 class="fw-bold mb-3" style="color: #028090;">8. Cookies & Similar Technologies</h4>
                    <p class="text-secondary mb-3">We use cookies and similar technologies to:</p>
                    
                    <ul class="text-secondary small ps-3">
                        <li><strong>Essential Cookies:</strong> Keep you logged in and secure your session</li>
                        <li><strong>Analytics Cookies:</strong> Understand how users interact with the platform</li>
                        <li><strong>Preference Cookies:</strong> Remember your settings and preferences</li>
                    </ul>

                    <p class="text-secondary small mt-3">You can control cookies through your browser settings. Disabling cookies may affect platform functionality.</p>
                </section>

                <!-- 9. Third-Party Services -->
                <section class="mb-5">
                    <h4 class="fw-bold mb-3" style="color: #028090;">9. Third-Party Services</h4>
                    <p class="text-secondary mb-3">We may use third-party services for:</p>
                    
                    <ul class="text-secondary small ps-3">
                        <li><strong>Payment Processing:</strong> Secure payment gateways (M-Pesa, etc.) - they follow their own privacy policies</li>
                        <li><strong>Email Delivery:</strong> Transactional email providers</li>
                        <li><strong>Cloud Hosting:</strong> Data stored on secure servers</li>
                        <li><strong>Analytics:</strong> To understand platform usage patterns</li>
                    </ul>

                    <p class="text-secondary small mt-3">These services are bound by confidentiality agreements and are prohibited from using your data for their own marketing.</p>
                </section>

                <!-- 10. Student Privacy & FERPA -->
                <section class="mb-5">
                    <h4 class="fw-bold mb-3" style="color: #028090;">10. University Student Status</h4>
                    <p class="text-secondary">PwanDeal requires a valid @pwani.ac.ke email to register. We verify student status but:</p>
                    
                    <ul class="text-secondary small ps-3">
                        <li>Do not access university academic records</li>
                        <li>Do not share data with the university</li>
                        <li>Only verify email domain for security purposes</li>
                        <li>Operate independently from university systems</li>
                    </ul>

                    <div class="alert alert-info border-0 bg-light p-3 rounded-3 mt-3">
                        <strong>ℹ️ Note:</strong> PwanDeal is not affiliated with Pwani University IT systems and does not fall under the university's data retention policies.
                    </div>
                </section>

                <!-- 11. Contact & Policy Changes -->
                <section class="mb-5">
                    <h4 class="fw-bold mb-3" style="color: #028090;">11. Changes to This Policy</h4>
                    <p class="text-secondary">We may update this policy to reflect changes in our practices or legal requirements. We will:</p>
                    
                    <ul class="text-secondary small ps-3">
                        <li>Post the updated policy on this page</li>
                        <li>Update the "Last Updated" date</li>
                        <li>Send you an email if changes significantly affect your privacy</li>
                    </ul>

                    <p class="text-secondary small mt-3">Your continued use of PwanDeal after changes indicates your acceptance of the updated policy.</p>
                </section>

                <!-- 12. Contact & Support -->
                <section class="border-top pt-4">
                    <h5 class="fw-bold mb-3">❓ Questions or Concerns?</h5>
                    <p class="text-secondary mb-3">If you have questions about this Privacy Policy or how we handle your data, please contact us:</p>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <h6 class="fw-bold mb-2 text-dark">📧 Email</h6>
                                <a href="mailto:support@pwandeal.pw" class="text-decoration-none fw-bold" style="color: #028090;">
                                    support@pwandeal.pw
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <h6 class="fw-bold mb-2 text-dark">📱 WhatsApp</h6>
                                <a href="https://wa.me/254111602678" target="_blank" class="text-decoration-none fw-bold" style="color: #028090;">
                                    +254 111 602 678
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-primary border-0 bg-light p-3 rounded-3">
                        <strong>💬 Preferred:</strong> Use our <a href="contact.php" class="fw-bold" style="color: #028090;">Contact Support Form</a> for fastest response. We reply within 24 hours.
                    </div>
                </section>

                <div class="mt-5 pt-4 border-top">
                    <small class="text-muted">
                        <strong>Last Updated:</strong> April 28, 2026<br>
                        <strong>Effective Date:</strong> April 28, 2026<br>
                        <strong>Version:</strong> 2.0 (Comprehensive)
                    </small>
                </div>
            </div>
        </div>

        <!-- Sidebar Navigation -->
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="sticky-top" style="top: 2rem;">
                <!-- Security Highlight -->
                <div class="card border-0 shadow-sm p-4 rounded-4 text-white mb-4" style="background: linear-gradient(135deg, #1e2761, #028090);">
                    <h5 class="fw-bold mb-3">🔒 Student Safety First</h5>
                    <p class="small opacity-75 mb-0">By limiting access to verified <strong>@pwani.ac.ke</strong> accounts, we significantly reduce the risk of non-student scammers entering the marketplace.</p>
                </div>

                <!-- Quick Navigation -->
                <div class="card border-0 shadow-sm p-4 rounded-4 bg-white mb-4">
                    <h6 class="fw-bold mb-3 text-dark">📚 Quick Navigation</h6>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-3">
                            <a href="terms.php" class="text-decoration-none fw-bold" style="color: #028090;">
                                <i class="bi bi-file-text me-2"></i>Terms of Service
                            </a>
                        </li>
                        <li class="mb-3">
                            <a href="safety.php" class="text-decoration-none fw-bold" style="color: #028090;">
                                <i class="bi bi-shield-check me-2"></i>Safety Tips
                            </a>
                        </li>
                        <li class="mb-3">
                            <a href="contact.php" class="text-decoration-none fw-bold" style="color: #028090;">
                                <i class="bi bi-chat-dots me-2"></i>Contact Support
                            </a>
                        </li>
                        <li>
                            <a href="#" onclick="window.print()" class="text-decoration-none fw-bold text-muted">
                                <i class="bi bi-printer me-2"></i>Print This Policy
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Key Points -->
                <div class="card border-0 shadow-sm p-4 rounded-4 bg-light">
                    <h6 class="fw-bold mb-3 text-dark">✓ Key Privacy Points</h6>
                    <ul class="small text-muted ps-3 mb-0">
                        <li class="mb-2">✓ Your data is never sold</li>
                        <li class="mb-2">✓ Encrypted transmission</li>
                        <li class="mb-2">✓ You control your visibility</li>
                        <li class="mb-2">✓ 24/7 support available</li>
                        <li>✓ GDPR & DPA compliant</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>