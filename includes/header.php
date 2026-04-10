<?php
/**
 * PwanDeal - Header & Navigation
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. DATABASE & STATE PREP
$unread_count = 0;
$base_url = '/pwandeal'; // Absolute base path for reliability

if (isset($_SESSION['user_id'])) {
    try {
        if (!isset($conn)) {
            require_once __DIR__ . '/../config/database.php';
        }
        
        $user_id = $_SESSION['user_id'];
        
        // Fetch Unread Messages
        $stmt = $conn->prepare('SELECT COUNT(*) as total FROM messages WHERE receiver_id = ? AND is_read = 0');
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $unread_count = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

        // Security: Verify if user was suspended since last click
        $check_stmt = $conn->prepare('SELECT is_suspended FROM users WHERE user_id = ?');
        $check_stmt->bind_param('i', $user_id);
        $check_stmt->execute();
        $user_data = $check_stmt->get_result()->fetch_assoc();
        
        if ($user_data && $user_data['is_suspended']) {
            header('Location: ' . $base_url . '/auth/logout.php?reason=suspended');
            exit();
        }

    } catch (Exception $e) {
        $unread_count = 0; // Silently handle DB errors to prevent header crash
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - PwanDeal' : 'PwanDeal - Pwani University Marketplace'; ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root { 
            --primary-teal: #028090; 
            --dark-navy: #1e2761; 
            --accent-gold: #f4d03f;
        }
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #f8f9fa; 
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .navbar { 
            background: linear-gradient(135deg, var(--primary-teal) 0%, var(--dark-navy) 100%); 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
            padding: 0.8rem 0;
        }
        .navbar-brand { font-size: 1.5rem; letter-spacing: -0.5px; }
        .nav-link { 
            font-size: 0.95rem; 
            font-weight: 500; 
            color: rgba(255,255,255,0.9) !important;
            padding: 0.5rem 1rem !important;
        }
        .nav-link:hover { color: var(--accent-gold) !important; }
        
        .notification-badge {
            position: absolute;
            top: 4px;
            right: 2px;
            background: #ff4757;
            color: white;
            font-size: 0.65rem;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 2px solid var(--dark-navy);
            font-weight: 800;
        }
        .profile-img {
            width: 32px; height: 32px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.3);
            transition: transform 0.2s;
        }
        .dropdown-item i { margin-right: 10px; opacity: 0.7; }
        .dropdown-menu { border-radius: 12px; padding: 0.5rem; }
        .container-main { flex: 1; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= $base_url; ?>/index.php">
            <span style="color: var(--accent-gold);">⭐</span> PwanDeal
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <i class="bi bi-list fs-2"></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link" href="<?= $base_url; ?>/index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $base_url; ?>/listings/view.php">Browse</a></li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link px-lg-3" href="<?= $base_url; ?>/listings/create.php">
                            <i class="bi bi-plus-circle me-1"></i> Post
                        </a>
                    </li>
                    <li class="nav-item me-lg-2">
                        <a class="nav-link position-relative px-lg-3" href="<?= $base_url; ?>/messages/view.php">
                            <i class="bi bi-chat-dots fs-5"></i>
                            <?php if ($unread_count > 0): ?>
                                <span class="notification-badge"><?= $unread_count > 9 ? '9+' : $unread_count ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <li class="nav-item dropdown ms-lg-2">
                        <a class="nav-link dropdown-toggle d-flex align-items-center bg-white bg-opacity-10 rounded-pill px-3 py-1" 
                           href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <img src="<?= (!empty($_SESSION['profile_photo'])) ? $base_url.'/uploads/profiles/'.$_SESSION['profile_photo'] : $base_url.'/assets/default-avatar.png' ?>" 
                                 class="profile-img me-2" alt="User">
                            <span class="small fw-bold d-none d-sm-inline">
                                <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3 animate slideIn">
                            <li><a class="dropdown-item py-2" href="<?= $base_url; ?>/profile/view.php?id=<?= $_SESSION['user_id']; ?>"><i class="bi bi-person"></i> My Profile</a></li>
                            <li><a class="dropdown-item py-2" href="<?= $base_url; ?>/listings/my-listings.php"><i class="bi bi-grid"></i> My Services</a></li>
                            <li><a class="dropdown-item py-2" href="<?= $base_url; ?>/profile/edit.php"><i class="bi bi-gear"></i> Settings</a></li>
                            
                            <?php if ($_SESSION['user_id'] == 1): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item fw-bold text-primary py-2" href="<?= $base_url; ?>/admin/dashboard.php"><i class="bi bi-shield-lock"></i> Admin Panel</a></li>
                            <?php endif; ?>
                            
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger py-2" href="<?= $base_url; ?>/auth/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>

                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_url; ?>/auth/login.php">Login</a></li>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-light btn-sm fw-bold px-4 rounded-pill shadow-sm" href="<?= $base_url; ?>/auth/register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container container-main my-5"></main>