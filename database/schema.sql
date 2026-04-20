-- ============================================================================
-- PwanDeal Database Schema
-- Digital Marketplace for Pwani University Students
-- ============================================================================

-- Create Database
CREATE DATABASE IF NOT EXISTS pwandeal_db;
USE pwandeal_db;

-- ============================================================================
-- TABLE 1: users
-- ============================================================================
CREATE TABLE users (
  user_id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  student_number VARCHAR(50) UNIQUE NOT NULL,
  school VARCHAR(100),
  year_of_study INT,
  phone VARCHAR(20),
  bio TEXT,
  profile_photo VARCHAR(255),
  average_rating DECIMAL(3,2) DEFAULT 0,
  total_reviews INT DEFAULT 0,
  total_listings INT DEFAULT 0,
  is_verified BOOLEAN DEFAULT FALSE,
  verification_code VARCHAR(6),
  verified_at TIMESTAMP NULL,
  is_active BOOLEAN DEFAULT TRUE,
  is_suspended BOOLEAN DEFAULT FALSE,
  suspension_reason VARCHAR(255),
  suspended_until TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_login TIMESTAMP NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_student_number (student_number),
  INDEX idx_is_verified (is_verified)
);

-- ============================================================================
-- TABLE 2: categories
-- ============================================================================
CREATE TABLE categories (
  category_id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL UNIQUE,
  description TEXT,
  icon VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO categories (name, description, icon) VALUES
('Academic Tutoring', 'Help with studies, assignments, exam prep', 'book'),
('Creative Design', 'Graphic design, UI/UX, art, logos', 'palette'),
('Technical Help', 'Programming, web development, IT support', 'code'),
('General Services', 'Other student services and tasks', 'star'),
('Products', 'Second-hand items, books, equipment', 'package');

-- ============================================================================
-- TABLE 3: listings
-- ============================================================================
CREATE TABLE listings (
  listing_id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT NOT NULL,
  category_id INT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  currency VARCHAR(3) DEFAULT 'KSh',
  status ENUM('active', 'archived', 'sold', 'inactive') DEFAULT 'active',
  views INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  expires_at TIMESTAMP NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(category_id),
  INDEX idx_user_id (user_id),
  INDEX idx_status (status)
);

-- ============================================================================
-- TABLE 4: listing_images
-- ============================================================================
CREATE TABLE listing_images (
  image_id INT PRIMARY KEY AUTO_INCREMENT,
  listing_id INT NOT NULL,
  image_url VARCHAR(255) NOT NULL,
  is_primary BOOLEAN DEFAULT FALSE,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE CASCADE,
  INDEX idx_listing_id (listing_id)
);

-- ============================================================================
-- TABLE 5: messages
-- ============================================================================
CREATE TABLE messages (
  message_id INT PRIMARY KEY AUTO_INCREMENT,
  sender_id INT NOT NULL,
  receiver_id INT NOT NULL,
  listing_id INT,
  message_text TEXT NOT NULL,
  is_read BOOLEAN DEFAULT FALSE,
  read_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (receiver_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE SET NULL,
  INDEX idx_sender_id (sender_id),
  INDEX idx_receiver_id (receiver_id),
  INDEX idx_is_read (is_read)
);

-- ============================================================================
-- TABLE 6: reviews
-- ============================================================================
CREATE TABLE reviews (
  review_id INT PRIMARY KEY AUTO_INCREMENT,
  from_user_id INT NOT NULL,
  to_user_id INT NOT NULL,
  listing_id INT,
  rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  title VARCHAR(100),
  comment TEXT,
  is_verified BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (from_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (to_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE SET NULL,
  UNIQUE KEY unique_review (from_user_id, to_user_id, listing_id),
  INDEX idx_to_user_id (to_user_id),
  INDEX idx_rating (rating)
);

-- ============================================================================
-- TABLE 7: reports
-- ============================================================================
CREATE TABLE reports (
  report_id INT PRIMARY KEY AUTO_INCREMENT,
  reporter_id INT NOT NULL,
  target_type ENUM('user', 'listing', 'message', 'review') NOT NULL,
  target_id INT NOT NULL,
  reason ENUM('Scam', 'Harassment', 'Inappropriate', 'Fake', 'Other') NOT NULL,
  description TEXT,
  status ENUM('pending', 'reviewing', 'resolved', 'rejected') DEFAULT 'pending',
  admin_notes TEXT,
  resolution VARCHAR(255),
  resolved_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  resolved_at TIMESTAMP NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (reporter_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (resolved_by) REFERENCES users(user_id) ON DELETE SET NULL,
  INDEX idx_reporter_id (reporter_id),
  INDEX idx_status (status)
);

-- ============================================================================
-- TABLE 8: blocked_users
-- ============================================================================
CREATE TABLE blocked_users (
  block_id INT PRIMARY KEY AUTO_INCREMENT,
  blocker_id INT NOT NULL,
  blocked_id INT NOT NULL,
  reason VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (blocker_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (blocked_id) REFERENCES users(user_id) ON DELETE CASCADE,
  UNIQUE KEY unique_block (blocker_id, blocked_id),
  INDEX idx_blocker_id (blocker_id),
  INDEX idx_blocked_id (blocked_id)
);

-- ============================================================================
-- TABLE 9: admin_logs
-- ============================================================================
CREATE TABLE admin_logs (
  log_id INT PRIMARY KEY AUTO_INCREMENT,
  admin_id INT NOT NULL,
  action VARCHAR(100) NOT NULL,
  target_type VARCHAR(50),
  target_id INT,
  description TEXT,
  ip_address VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES users(user_id) ON DELETE CASCADE,
  INDEX idx_admin_id (admin_id),
  INDEX idx_created_at (created_at)
);

-- ============================================================================
-- Database setup complete!
-- ============================================================================
```

✅ **Save it** (Ctrl+S)

---

## ✅ **ALL 12 FILES CREATED!** 🎉

Now you have all files in your project:
```
pwandeal/
├── config/database.php              ✅
├── includes/header.php              ✅
├── includes/footer.php              ✅
├── auth/register.php                ✅
├── auth/verify.php                  ✅
├── auth/login.php                   ✅
├── auth/logout.php                  ✅
├── assets/css/style.css             ✅
├── assets/js/script.js              ✅
├── database/schema.sql              ✅
├── index.php                        ✅
└── test_db.php                      ✅