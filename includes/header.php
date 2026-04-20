<?php
/**
 * PwanDeal - Global Header (Optimized)
 * Combined with Security Checks, Unread Messages, and Modern UI
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base_url = '/pwandeal';
$unread_count = 0;

// 1. DATABASE & SECURITY PREP
if (isset($_SESSION['user_id'])) {
    try {
        if (!isset($conn)) {
            require_once __DIR__ . '/../config/database.php';
        }
        
        $user_id = $_SESSION['user_id'];
        
        // Fetch Unread Messages
        $msg_stmt = $conn->prepare('SELECT COUNT(*) as total FROM messages WHERE receiver_id = ? AND is_read = 0');
        $msg_stmt->bind_param('i', $user_id);
        $msg_stmt->execute();
        $unread_count = $msg_stmt->get_result()->fetch_assoc()['total'] ?? 0;

        // Security: Verify if user was suspended since last click
        $status_stmt = $conn->prepare('SELECT is_suspended FROM users WHERE user_id = ?');
        $status_stmt->bind_param('i', $user_id);
        $status_stmt->execute();
        $user_status = $status_stmt->get_result()->fetch_assoc();
        
        if ($user_status && $user_status['is_suspended']) {
            session_destroy();
            header('Location: ' . $base_url . '/auth/login.php?error=suspended');
            exit();
        }
    } catch (Exception $e) {
        error_log("Header Error: " . $e->getMessage());
        $unread_count = 0; 
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' | PwanDeal' : 'PwanDeal - PU Marketplace'; ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-teal: #028090;
            --deep-navy: #011627;
            --accent-yellow: #f4d03f;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #f8f9fa; 
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-teal) 0%, var(--deep-navy) 100%) !important;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 0.75rem 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .notification-badge {
            position: absolute;
            top: 2px;
            right: 0px;
            background: #ff4757;
            color: white;
            font-size: 0.6rem;
            min-width: 18px; height: 18px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%;
            border: 2px solid var(--primary-teal);
            font-weight: 800;
        }

        .nav-link { font-weight: 500; color: rgba(255,255,255,0.85) !important; }
        .nav-link:hover { color: var(--accent-yellow) !important; }

        .nav-search-btn {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: rgba(255,255,255,0.8);
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.85rem;
            margin-right: 15px;
            text-decoration: none;
            transition: 0.3s;
        }
        .nav-search-btn:hover {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .profile-img-nav {
            width: 32px; height: 32px;
            object-fit: cover;
            border-radius: 50%;
            border: 1.5px solid rgba(255,255,255,0.4);
        }

        @media (max-width: 991px) {
            .navbar-collapse {
                background: var(--deep-navy);
                margin-top: 15px;
                padding: 20px;
                border-radius: 20px;
            }
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="<?= $base_url; ?>/index.php">
            <span class="me-2" style="font-size: 1.4rem;">⭐</span>
            <span>PwanDeal</span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <i class="bi bi-list-nested fs-1"></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item d-none d-lg-block">
                    <a href="<?= $base_url; ?>/listings/view.php" class="nav-search-btn">
                        <i class="bi bi-search me-2"></i> Search marketplace...
                    </a>
                </li>

                <li class="nav-item"><a class="nav-link" href="<?= $base_url; ?>/index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $base_url; ?>/listings/view.php">Browse</a></li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link px-lg-3" href="<?= $base_url; ?>/listings/create.php">
                            <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                                <i class="bi bi-plus-lg me-1"></i> Post Deal
                            </span>
                        </a>
                    </li>
                    
                    <li class="nav-item me-lg-2">
                        <a class="nav-link position-relative px-lg-3" href="<?= $base_url; ?>/messages/inbox.php">
                            <i class="bi bi-chat-dots fs-5"></i>
                            <?php if ($unread_count > 0): ?>
                                <span class="notification-badge"><?= $unread_count > 9 ? '9+' : $unread_count ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <li class="nav-item dropdown ms-lg-2">
                        <a class="nav-link dropdown-toggle d-flex align-items-center bg-white bg-opacity-10 rounded-pill px-3 py-1" 
                           href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <?php 
                                $nav_avatar = (!empty($_SESSION['profile_photo'])) ? $base_url.'/uploads/profiles/'.$_SESSION['profile_photo'] : $base_url.'/assets/img/default-avatar.png';
                                $first_name = isset($_SESSION['user_name']) ? explode(' ', $_SESSION['user_name'])[0] : 'Student';
                            ?>
                            <img src="<?= $nav_avatar ?>" class="profile-img-nav me-2">
                            <span class="small fw-bold d-none d-sm-inline"><?= htmlspecialchars($first_name) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3">
                            <li><a class="dropdown-item py-2" href="<?= $base_url; ?>/profile/view.php?id=<?= $_SESSION['user_id']; ?>"><i class="bi bi-person me-2"></i> Profile</a></li>
                            <li><a class="dropdown-item py-2" href="<?= $base_url; ?>/listings/my-listings.php"><i class="bi bi-grid me-2"></i> My Services</a></li>
                            
                            <?php if ($_SESSION['user_id'] == 1): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item fw-bold text-primary py-2" href="<?= $base_url; ?>/admin/dashboard.php"><i class="bi bi-shield-lock me-2"></i> Admin Panel</a></li>
                            <?php endif; ?>
                            
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger py-2" href="<?= $base_url; ?>/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_url; ?>/auth/login.php">Login</a></li>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-warning btn-sm fw-bold px-4 rounded-pill shadow-sm" href="<?= $base_url; ?>/auth/register.php">Join PwanDeal</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container my-5 flex-grow-1"></main>