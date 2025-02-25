# AttenTrack - Attention Assessment Platform
## Technical Documentation

### Table of Contents
1. [Project Overview](#project-overview)
2. [System Architecture](#system-architecture)
3. [Features Implementation](#features-implementation)
4. [Database Schema](#database-schema)
5. [Authentication System](#authentication-system)
6. [Subscription System](#subscription-system)
7. [Test Management](#test-management)
8. [Setup Instructions](#setup-instructions)

## 1. Project Overview
AttenTrack is a comprehensive attention assessment platform built on WordPress. It provides various attention tests, user management, subscription plans, and detailed analytics.

### Core Functionalities
- User Authentication (Google, Facebook, Phone)
- Subscription Management
- Attention Tests
- Progress Tracking
- Analytics Dashboard

## 2. System Architecture
### Technology Stack
- WordPress Core
- PHP 7.4+
- MySQL Database
- Bootstrap 5
- Firebase Authentication
- Razorpay Payment Gateway

### Directory Structure
```
wp-content/themes/attentrack/
├── includes/
│   ├── razorpay-config.php
│   └── social-login-config.php
├── templates/
│   ├── tests/
│   │   ├── test-phase-0-template.php
│   │   └── trialtest3.php
│   └── subscription.php
├── functions.php
└── index.php
```

## 3. Features Implementation

### 3.1 Attention Tests
- Multiple test types
- Randomized questions
- Time tracking
- Score calculation
- Progress saving

### 3.2 User Dashboard
- Test history
- Performance analytics
- Subscription status
- Profile management

### 3.3 Administration
- User management
- Test management
- Subscription oversight
- Payment tracking

## 4. Database Schema

### WordPress Core Tables
- wp_users
- wp_usermeta

### Custom Tables
```sql
-- Subscription Plans
CREATE TABLE wp_subscription_plans (
    id int(11) NOT NULL AUTO_INCREMENT,
    plan_name varchar(100) NOT NULL,
    access_limit int(11) NOT NULL,
    price decimal(10,2) NOT NULL,
    description text,
    PRIMARY KEY (id)
);

-- User Subscriptions
CREATE TABLE wp_user_subscriptions (
    id int(11) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    plan_id int(11) NOT NULL,
    start_date datetime NOT NULL,
    end_date datetime,
    access_count int(11) DEFAULT 0,
    status varchar(20) NOT NULL,
    PRIMARY KEY (id)
);

-- Payment Logs
CREATE TABLE wp_subscription_payment_logs (
    id int(11) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    plan_id int(11) NOT NULL,
    razorpay_order_id varchar(100),
    razorpay_payment_id varchar(100),
    amount decimal(10,2) NOT NULL,
    status varchar(20) NOT NULL,
    error_message text,
    created_at datetime NOT NULL,
    PRIMARY KEY (id)
);
```

## 5. Authentication System

### 5.1 Social Login Integration
- Google OAuth 2.0
- Facebook OAuth
- Phone Number (Firebase)

### 5.2 Configuration Requirements
```php
// Google OAuth
define('GOOGLE_CLIENT_ID', 'YOUR_CLIENT_ID');
define('GOOGLE_CLIENT_SECRET', 'YOUR_CLIENT_SECRET');

// Facebook OAuth
define('FACEBOOK_APP_ID', 'YOUR_APP_ID');
define('FACEBOOK_APP_SECRET', 'YOUR_APP_SECRET');

// Firebase
define('FIREBASE_API_KEY', 'YOUR_API_KEY');
define('FIREBASE_PROJECT_ID', 'YOUR_PROJECT_ID');
```

## 6. Subscription System

### 6.1 Subscription Plans
1. **Stage 1: One-time Access**
   - Price: ₹99
   - Single test access
   
2. **Stage 2: Two-person Access**
   - Price: ₹179
   - Access for two users
   
3. **Stage 3: Three-person Access**
   - Price: ₹249
   - Access for three users
   
4. **Stage 4: Unlimited Access**
   - Price: ₹499
   - Unlimited user access

### 6.2 Payment Integration
- Razorpay payment gateway
- Secure payment processing
- Payment verification
- Transaction logging

### 6.3 Subscription Management
- Access control
- Usage tracking
- Renewal notifications
- Expiry handling

## 7. Test Management

### 7.1 Test Types
- Attention span assessment
- Cognitive performance
- Response time measurement
- Pattern recognition

### 7.2 Test Flow
1. User authentication
2. Subscription verification
3. Test selection
4. Test execution
5. Result calculation
6. Progress saving

## 8. Setup Instructions

### 8.1 Prerequisites
- WordPress 5.8+
- PHP 7.4+
- MySQL 5.7+
- Composer

### 8.2 Installation Steps
1. Clone repository
2. Install dependencies:
   ```bash
   composer require razorpay/razorpay
   composer require google/apiclient
   composer require facebook/graph-sdk
   ```

3. Configure authentication:
   - Set up Google OAuth credentials
   - Configure Facebook App
   - Set up Firebase project
   - Configure Razorpay account

4. Database setup:
   - Run WordPress installation
   - Execute custom table creation scripts
   - Import initial subscription plans

5. Theme activation:
   - Activate AttenTrack theme
   - Configure payment settings
   - Set up email notifications

### 8.3 Configuration Files
Update the following configuration files with your credentials:
- `includes/razorpay-config.php`
- `includes/social-login-config.php`

### 8.4 Testing
1. Test social login functionality
2. Verify payment processing
3. Check subscription management
4. Validate test execution
5. Confirm email notifications

## Support and Maintenance
- Regular updates
- Security patches
- Bug fixes
- Feature enhancements

---
*Documentation generated on February 25, 2025*
