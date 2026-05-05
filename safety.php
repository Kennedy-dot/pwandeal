<?php
/**
 * PwanDeal - Safety Tips & Best Practices
 * Purpose: Comprehensive guide to safe trading within the Pwani University community.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = "Safety & Trust";
$base_url = '/pwandeal';
include __DIR__ . '/includes/header.php';
?>

<style>
    body { padding-top: 0; } 
    
    .safety-hero {
        background: linear-gradient(135deg, #011627 0%, #028090 100%);
        color: white;
        padding: 100px 0 120px 0;
        border-radius: 0 0 50px 50px;
        margin-bottom: -40px;
        position: relative;
        z-index: 1;
    }

    .safety-card {
        border: none;
        border-radius: 20px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: white;
        z-index: 2;
        overflow: hidden;
    }

    .safety-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 40px rgba(2, 128, 144, 0.15) !important;
    }

    .safety-card.danger {
        border-left: 4px solid #dc3545;
    }

    .safety-card.success {
        border-left: 4px solid #28a745;
    }

    .icon-box {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, rgba(2, 128, 144, 0.1) 0%, rgba(30, 39, 97, 0.05) 100%);
        color: #028090;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 18px;
        font-size: 2rem;
        margin-bottom: 20px;
    }

    .step-number {
        color: #028090;
        font-weight: 800;
        font-size: 2.5rem;
        opacity: 0.08;
        position: absolute;
        top: 5px;
        right: 20px;
        user-select: none;
    }

    .badge-custom {
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.75rem;
        display: inline-block;
        margin-bottom: 15px;
    }

    .badge-critical {
        background: #ffe5e5;
        color: #dc3545;
    }

    .badge-essential {
        background: #e5f3ff;
        color: #0066cc;
    }

    .section-title {
        color: #1e2761;
        font-weight: 800;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 3px solid #028090;
    }

    .checklist-item {
        padding: 15px;
        margin-bottom: 10px;
        background: #f8f9fa;
        border-radius: 12px;
        border-left: 4px solid #28a745;
    }

    .checklist-item.warning {
        border-left-color: #ffc107;
        background: #fffbf0;
    }

    .checklist-item.danger {
        border-left-color: #dc3545;
        background: #fff5f5;
    }

    .trust-score {
        background: linear-gradient(135deg, #028090 0%, #1e2761 100%);
        color: white;
        padding: 40px;
        border-radius: 20px;
        text-align: center;
    }

    .rating-box {
        background: #f0f0f0;
        padding: 20px;
        border-radius: 15px;
        text-align: center;
        margin-bottom: 20px;
    }

    .stars {
        color: #ffc107;
        font-size: 1.5rem;
    }

    .comparison-table {
        border-collapse: collapse;
        width: 100%;
    }

    .comparison-table th,
    .comparison-table td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #dee2e6;
    }

    .comparison-table th {
        background: #f8f9fa;
        font-weight: 700;
        color: #1e2761;
    }

    .comparison-table tr:hover {
        background: #f9f9f9;
    }

    .comparison-table .check {
        color: #28a745;
        font-weight: bold;
    }

    .comparison-table .cross {
        color: #dc3545;
        font-weight: bold;
    }

    .alert-timeline {
        position: relative;
        padding-left: 40px;
    }

    .alert-timeline::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: #028090;
    }

    .timeline-item {
        margin-bottom: 20px;
        position: relative;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -47px;
        top: 5px;
        width: 12px;
        height: 12px;
        background: #028090;
        border-radius: 50%;
        border: 3px solid white;
    }
</style>

<!-- Hero Section -->
<section class="safety-hero text-center">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3">Your Safety is Our <span style="color: #f4d03f;">Priority</span></h1>
        <p class="lead opacity-75 mx-auto" style="max-width: 700px;">
            PwanDeal is a community-driven marketplace exclusively for Pwani University students. Follow these campus-tested safety guidelines to buy and sell with total confidence.
        </p>
    </div>
</section>

<div class="container pb-5">
    
    <!-- ==================== ESSENTIAL SAFETY TIPS ==================== -->
    <section class="mt-5 mb-5">
        <h2 class="section-title">⭐ Essential Safety Principles</h2>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="card safety-card shadow-sm p-4 h-100 position-relative">
                    <span class="badge-custom badge-critical">Critical</span>
                    <span class="step-number">01</span>
                    <div class="icon-box"><i class="bi bi-geo-alt-fill"></i></div>
                    <h5 class="fw-bold mb-2">Meet in Public Places</h5>
                    <p class="text-muted small mb-0">Always meet on campus during daylight. Safe locations include <strong>Graduation Square</strong>, <strong>Library Main Entrance</strong>, <strong>Student Centre</strong>, or <strong>Main Gate Area</strong>. Never agree to meet off-campus or in secluded areas.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card safety-card shadow-sm p-4 h-100 position-relative">
                    <span class="badge-custom badge-critical">Critical</span>
                    <span class="step-number">02</span>
                    <div class="icon-box"><i class="bi bi-eye-fill"></i></div>
                    <h5 class="fw-bold mb-2">Inspect Everything</h5>
                    <p class="text-muted small mb-0">Test electronics, check condition, count items, and verify they match the listing. Don't rush. If something feels wrong, walk away. Legitimate sellers won't pressure you.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card safety-card shadow-sm p-4 h-100 position-relative">
                    <span class="badge-custom badge-critical">Critical</span>
                    <span class="step-number">03</span>
                    <div class="icon-box"><i class="bi bi-shield-lock-fill"></i></div>
                    <h5 class="fw-bold mb-2">Payment Security</h5>
                    <p class="text-muted small mb-0"><strong>M-Pesa only</strong> for digital proof. <strong>Never pay upfront deposits</strong> before inspecting. <strong>Never use bank transfers</strong> for unverified sellers. Only complete payment after verifying the item.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card safety-card shadow-sm p-4 h-100 position-relative">
                    <span class="badge-custom badge-essential">Essential</span>
                    <span class="step-number">04</span>
                    <div class="icon-box"><i class="bi bi-chat-left-dots-fill"></i></div>
                    <h5 class="fw-bold mb-2">Use Official Messaging</h5>
                    <p class="text-muted small mb-0">Keep all negotiations within PwanDeal. This creates a digital record and allows us to support you if disputes arise. Don't move to WhatsApp or personal phone until transaction is complete.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card safety-card shadow-sm p-4 h-100 position-relative">
                    <span class="badge-custom badge-essential">Essential</span>
                    <span class="step-number">05</span>
                    <div class="icon-box"><i class="bi bi-heart-pulse-fill"></i></div>
                    <h5 class="fw-bold mb-2">Trust Your Gut</h5>
                    <p class="text-muted small mb-0">If a price is suspiciously low, if the seller is pushy, or if something feels off—trust your instinct and walk away. Your safety and peace of mind are worth more than any deal.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card safety-card shadow-sm p-4 h-100 position-relative">
                    <span class="badge-custom badge-essential">Essential</span>
                    <span class="step-number">06</span>
                    <div class="icon-box"><i class="bi bi-person-check-fill"></i></div>
                    <h5 class="fw-bold mb-2">Verify Seller Profile</h5>
                    <p class="text-muted small mb-0">Check seller ratings, review count, and how long they've been active. Look for red flags like no profile photo, zero reviews, or new accounts with high-value items.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== RED FLAGS / WARNING SIGNS ==================== -->
    <section class="mb-5">
        <h2 class="section-title">🚨 Red Flags - Stop & Report</h2>
        <p class="text-muted mb-4">Beware of these warning signs. If you encounter any of these, <strong>report immediately</strong>.</p>
        
        <div class="row g-3">
            <div class="col-md-6">
                <div class="checklist-item danger">
                    <div class="d-flex">
                        <div class="me-3 fs-5">🚫</div>
                        <div>
                            <strong>Suspicious Payment Requests</strong>
                            <p class="small text-muted mb-0">Asking for deposit, gift cards, cryptocurrency, or bank transfers before showing item.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="checklist-item danger">
                    <div class="d-flex">
                        <div class="me-3 fs-5">🚫</div>
                        <div>
                            <strong>Pressure to Meet Quickly</strong>
                            <p class="small text-muted mb-0">"Only available right now," "Meet in 10 minutes," or refusing reasonable meeting times.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="checklist-item danger">
                    <div class="d-flex">
                        <div class="me-3 fs-5">🚫</div>
                        <div>
                            <strong>Off-Campus Only Meeting</strong>
                            <p class="small text-muted mb-0">Refuses to meet on campus or insists on remote/isolated locations.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="checklist-item danger">
                    <div class="d-flex">
                        <div class="me-3 fs-5">🚫</div>
                        <div>
                            <strong>Multiple Offers / Urgency</strong>
                            <p class="small text-muted mb-0">"Others are interested," "Price dropping soon," creating artificial urgency.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="checklist-item danger">
                    <div class="d-flex">
                        <div class="me-3 fs-5">🚫</div>
                        <div>
                            <strong>Stolen/Fake Items</strong>
                            <p class="small text-muted mb-0">Brand new expensive items at fraction of market price, or asking price seems unrealistic.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="checklist-item danger">
                    <div class="d-flex">
                        <div class="me-3 fs-5">🚫</div>
                        <div>
                            <strong>No Phone Number in Listing</strong>
                            <p class="small text-muted mb-0">Legitimate sellers provide contact info. Suspicious if they avoid direct communication.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="checklist-item danger">
                    <div class="d-flex">
                        <div class="me-3 fs-5">🚫</div>
                        <div>
                            <strong>Poor Grammar / Suspicious Messages</strong>
                            <p class="small text-muted mb-0">Obvious spelling errors, random capitalization, or messages that don't match local language patterns.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="checklist-item danger">
                    <div class="d-flex">
                        <div class="me-3 fs-5">🚫</div>
                        <div>
                            <strong>Profile Photo That's Stock Image</strong>
                            <p class="small text-muted mb-0">Photo looks professional/professional = often scammers using fake images.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== BEFORE YOU MEET ==================== -->
    <section class="mb-5">
        <h2 class="section-title">📋 Pre-Meeting Checklist</h2>
        
        <div class="row g-3">
            <div class="col-md-6">
                <h5 class="fw-bold mb-3 text-success">✓ DO These Things</h5>
                <div class="checklist-item">
                    <div class="d-flex">
                        <span class="text-success me-3 fw-bold">✓</span>
                        <span>Message seller through PwanDeal first</span>
                    </div>
                </div>
                <div class="checklist-item">
                    <div class="d-flex">
                        <span class="text-success me-3 fw-bold">✓</span>
                        <span>Ask detailed questions about the item</span>
                    </div>
                </div>
                <div class="checklist-item">
                    <div class="d-flex">
                        <span class="text-success me-3 fw-bold">✓</span>
                        <span>Request recent photos/video if needed</span>
                    </div>
                </div>
                <div class="checklist-item">
                    <div class="d-flex">
                        <span class="text-success me-3 fw-bold">✓</span>
                        <span>Confirm a specific time and location</span>
                    </div>
                </div>
                <div class="checklist-item">
                    <div class="d-flex">
                        <span class="text-success me-3 fw-bold">✓</span>
                        <span>Tell a friend where you're going</span>
                    </div>
                </div>
                <div class="checklist-item">
                    <div class="d-flex">
                        <span class="text-success me-3 fw-bold">✓</span>
                        <span>Bring enough cash/M-Pesa balance</span>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <h5 class="fw-bold mb-3 text-danger">✗ DON'T Do These Things</h5>
                <div class="checklist-item danger">
                    <div class="d-flex">
                        <span class="text-danger me-3 fw-bold">✗</span>
                        <span>Meet alone (bring a friend)</span>
                    </div>
                </div>
                <div class="checklist-item danger">
                    <div class="d-flex">
                        <span class="text-danger me-3 fw-bold">✗</span>
                        <span>Carry large amounts of cash</span>
                    </div>
                </div>
                <div class="checklist-item danger">
                    <div class="d-flex">
                        <span class="text-danger me-3 fw-bold">✗</span>
                        <span>Share personal address/dorm details</span>
                    </div>
                </div>
                <div class="checklist-item danger">
                    <div class="d-flex">
                        <span class="text-danger me-3 fw-bold">✗</span>
                        <span>Pay without inspecting item first</span>
                    </div>
                </div>
                <div class="checklist-item danger">
                    <div class="d-flex">
                        <span class="text-danger me-3 fw-bold">✗</span>
                        <span>Send payment before meeting</span>
                    </div>
                </div>
                <div class="checklist-item danger">
                    <div class="d-flex">
                        <span class="text-danger me-3 fw-bold">✗</span>
                        <span>Ignore your instinct if something feels wrong</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== PAYMENT COMPARISON ==================== -->
    <section class="mb-5">
        <h2 class="section-title">💰 Payment Methods - Which Is Safest?</h2>
        
        <div class="table-responsive">
            <table class="comparison-table">
                <thead>
                    <tr>
                        <th>Payment Method</th>
                        <th>Safety</th>
                        <th>Traceability</th>
                        <th>Recommendation</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>M-Pesa</strong></td>
                        <td><span class="check">✓✓✓</span> Safest</td>
                        <td><span class="check">✓✓✓</span> Full record</td>
                        <td class="text-success fw-bold">🥇 RECOMMENDED</td>
                    </tr>
                    <tr>
                        <td><strong>Cash (In-Person)</strong></td>
                        <td><span class="check">✓✓</span> Good</td>
                        <td><span class="cross">✗✗</span> No record</td>
                        <td>Acceptable with precautions</td>
                    </tr>
                    <tr>
                        <td><strong>Bank Transfer</strong></td>
                        <td><span class="check">✓</span> Fair</td>
                        <td><span class="check">✓✓✓</span> Full record</td>
                        <td>Only for trusted sellers</td>
                    </tr>
                    <tr>
                        <td><strong>Cheque</strong></td>
                        <td><span class="cross">✗</span> Not recommended</td>
                        <td><span class="check">✓✓</span> Traceable</td>
                        <td>Avoid - Can bounce</td>
                    </tr>
                    <tr>
                        <td><strong>Cryptocurrency</strong></td>
                        <td><span class="cross">✗✗</span> Unsafe</td>
                        <td><span class="cross">✗✗</span> Irreversible</td>
                        <td><span class="text-danger fw-bold">🚫 NEVER USE</span></td>
                    </tr>
                    <tr>
                        <td><strong>Gift Cards</strong></td>
                        <td><span class="cross">✗✗</span> Very risky</td>
                        <td><span class="cross">✗✗</span> Hard to trace</td>
                        <td><span class="text-danger fw-bold">🚫 NEVER USE</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="alert alert-info border-0 bg-light p-4 rounded-3 mt-4">
            <strong>💡 Pro Tip:</strong> M-Pesa + In-Person Inspection = Maximum Safety. You get the digital record AND you can verify the item before payment. This is the gold standard for campus trading.
        </div>
    </section>

    <!-- ==================== RATING SYSTEM EXPLAINED ==================== -->
    <section class="mb-5">
        <h2 class="section-title">⭐ Understanding Seller Ratings</h2>
        <p class="text-muted mb-4">PwanDeal's rating system helps you identify trustworthy sellers. Here's what to look for:</p>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="rating-box">
                    <div class="stars mb-2">★★★★★</div>
                    <h6 class="fw-bold text-success mb-1">4.5+ Stars (Excellent)</h6>
                    <p class="small text-muted mb-0">Very reliable. Look for 10+ reviews. Safe to transact with.</p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="rating-box">
                    <div class="stars mb-2">★★★★</div>
                    <h6 class="fw-bold text-success mb-1">3.5 - 4.4 Stars (Good)</h6>
                    <p class="small text-muted mb-0">Generally reliable. Some issues but mostly positive. Safe bet.</p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="rating-box">
                    <div class="stars mb-2">★★★</div>
                    <h6 class="fw-bold text-warning mb-1">2.5 - 3.4 Stars (Fair)</h6>
                    <p class="small text-muted mb-0">Mixed reviews. Proceed with caution. Read recent comments.</p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="rating-box">
                    <div class="stars mb-2">★★</div>
                    <h6 class="fw-bold text-danger mb-1">Below 2.5 Stars (Poor)</h6>
                    <p class="small text-muted mb-0">Multiple complaints. Avoid if possible. Approach with extreme caution.</p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="rating-box">
                    <div class="stars mb-2">★ (No Stars)</div>
                    <h6 class="fw-bold text-secondary mb-1">No Reviews (New Seller)</h6>
                    <p class="small text-muted mb-0">Unproven. Extra caution needed. Stick to low-value items if trading.</p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="rating-box">
                    <div class="stars mb-2">🚫 Suspended</div>
                    <h6 class="fw-bold text-danger mb-1">Account Suspended</h6>
                    <p class="small text-muted mb-0">DO NOT TRADE. The user violated our terms. Report any contact.</p>
                </div>
            </div>
        </div>

        <div class="alert alert-warning border-0 bg-light p-4 rounded-3 mt-4">
            <strong>⚠️ Note:</strong> Always read the <strong>recent reviews</strong> (not just the rating). Sometimes a highly-rated seller had one bad experience that affected their score. Look for patterns in complaints.
        </div>
    </section>

    <!-- ==================== IF SOMETHING GOES WRONG ==================== -->
    <section class="mb-5">
        <h2 class="section-title">🆘 What To Do If Something Goes Wrong</h2>
        
        <div class="alert-timeline">
            <div class="timeline-item">
                <h6 class="fw-bold text-danger">Step 1: Document Everything</h6>
                <p class="text-muted small">Take screenshots of messages, photos of the defective item, and note the time/location of the transaction.</p>
            </div>

            <div class="timeline-item">
                <h6 class="fw-bold text-danger">Step 2: Contact the Seller</h6>
                <p class="text-muted small">Send a clear message within PwanDeal explaining the issue. Give them 24-48 hours to respond. Stay calm and professional.</p>
            </div>

            <div class="timeline-item">
                <h6 class="fw-bold text-danger">Step 3: Use PwanDeal Dispute System</h6>
                <p class="text-muted small">If seller doesn't respond, file a formal dispute through PwanDeal's Resolution Center. Include screenshots and evidence.</p>
            </div>

            <div class="timeline-item">
                <h6 class="fw-bold text-danger">Step 4: Report to Admin</h6>
                <p class="text-muted small">Contact our support team with all evidence. We investigate all reports and take action against violators.</p>
            </div>

            <div class="timeline-item">
                <h6 class="fw-bold text-danger">Step 5: File Police Report (If Needed)</h6>
                <p class="text-muted small">For serious crimes (theft, fraud), file a report with campus security or Kilifi police and provide PwanDeal with case details.</p>
            </div>
        </div>
    </section>

    <!-- ==================== REPORT SECTION ==================== -->
    <section class="mb-5">
        <div class="p-5 rounded-4" style="background: linear-gradient(135deg, rgba(2, 128, 144, 0.1) 0%, rgba(244, 208, 63, 0.1) 100%); border: 2px dashed #028090;">
            <div class="text-center">
                <h3 class="fw-bold mb-2 text-dark">🚨 Report Fraudulent Activity</h3>
                <p class="text-muted mb-4 mx-auto" style="max-width: 600px;">
                    Help keep Pwani University safe. Report scams, suspicious behavior, stolen items, or fake listings. All reports are investigated.
                </p>
                
                <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 flex-wrap">
                    <a href="contact.php" class="btn btn-danger rounded-pill px-4 py-2 fw-bold">
                        <i class="bi bi-megaphone me-2"></i> Report via Contact Form
                    </a>
                    <a href="mailto:report@pwandeal.pw" class="btn btn-outline-danger rounded-pill px-4 py-2 fw-bold">
                        <i class="bi bi-envelope me-2"></i> Email: report@pwandeal.pw
                    </a>
                    <a href="https://wa.me/254111602678" target="_blank" class="btn btn-outline-dark rounded-pill px-4 py-2 fw-bold">
                        <i class="bi bi-whatsapp me-2"></i> WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== QUICK REFERENCE ==================== -->
    <section class="mb-5">
        <h2 class="section-title">📌 Quick Reference Card</h2>
        
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card border-0 bg-light p-4 rounded-3">
                    <h6 class="fw-bold text-success mb-3"><i class="bi bi-check-circle me-2"></i>Safe Transactions Look Like:</h6>
                    <ul class="small text-muted ps-3 mb-0">
                        <li class="mb-2">Seller has 4+ rating with multiple reviews</li>
                        <li class="mb-2">Account created 3+ months ago</li>
                        <li class="mb-2">Clear, detailed item photos</li>
                        <li class="mb-2">Responsive to questions</li>
                        <li class="mb-2">Willing to meet on campus</li>
                        <li class="mb-2">Accepts M-Pesa payment</li>
                        <li class="mb-2">Reasonable pricing (not too cheap)</li>
                        <li>Provides phone number & full name</li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 bg-light p-4 rounded-3">
                    <h6 class="fw-bold text-danger mb-3"><i class="bi bi-exclamation-triangle me-2"></i>Suspicious Transactions Look Like:</h6>
                    <ul class="small text-muted ps-3 mb-0">
                        <li class="mb-2">New account (less than 1 month old)</li>
                        <li class="mb-2">No reviews or very poor rating</li>
                        <li class="mb-2">Vague, blurry, or stolen photos</li>
                        <li class="mb-2">Unresponsive to messages</li>
                        <li class="mb-2">Wants to meet off-campus only</li>
                        <li class="mb-2">Demands upfront payment</li>
                        <li class="mb-2">Price is unusually cheap</li>
                        <li>Vague item description</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== FOOTER INFO ==================== -->
    <section class="mb-5 text-center p-5 bg-light rounded-4">
        <h4 class="fw-bold mb-3">Remember: We're Here To Help</h4>
        <p class="text-muted mb-4 mx-auto" style="max-width: 700px;">
            PwanDeal has a complete moderation team reviewing reports 24/7. We take safety seriously and enforce our community standards strictly. If you experience a scam, <strong>report it immediately</strong> and we'll investigate.
        </p>
        <p class="small text-muted">
            <strong>Average report resolution time:</strong> 24 hours<br>
            <strong>Scammer action taken:</strong> Account suspension or ban
        </p>
    </section>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>