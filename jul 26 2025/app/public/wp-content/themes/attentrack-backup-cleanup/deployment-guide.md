# AttenTrack Deployment Guide

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Database Setup](#database-setup)
3. [WordPress Installation](#wordpress-installation)
4. [Theme Installation](#theme-installation)
5. [Configuration](#configuration)
6. [Post-Deployment Checklist](#post-deployment-checklist)

## Prerequisites
- Web server (Apache/Nginx)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- SSL certificate (for secure connections)
- Domain name configured with DNS

## Database Setup
1. Create a new MySQL database
```sql
CREATE DATABASE your_database_name;
CREATE USER 'your_username'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON your_database_name.* TO 'your_username'@'localhost';
FLUSH PRIVILEGES;
```

2. Required Database Tables
The following tables will be automatically created during WordPress installation:
- wp_attentrack_patient_details
- wp_attentrack_selective_results
- wp_attentrack_divided_results
- wp_attentrack_alternative_results
- wp_attentrack_extended_results

## WordPress Installation
1. Download WordPress from wordpress.org
2. Upload files to your web server
3. Create/edit wp-config.php:
```php
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_username');
define('DB_PASSWORD', 'your_password');
define('DB_HOST', 'localhost');
define('WP_DEBUG', false);
```

## Theme Installation
1. Zip the attentrack theme folder
2. Upload via WordPress admin or FTP:
   - Location: `/wp-content/themes/attentrack/`
   - Required files:
     - All PHP template files
     - style.css
     - assets/ directory
     - functions.php

## Configuration

### 1. WordPress Settings
- Settings → General
  - Site Title: Your Site Name
  - Timezone: Your local timezone
- Settings → Reading
  - Homepage: Select "Home"
  - Posts page: Select "Blog"
- Settings → Permalinks
  - Select "Post name"

### 2. Theme Settings
1. Create required pages:
   - Home
   - Dashboard
   - Tests
   - Results
   - Subscription

2. Menu Setup:
   - Create main navigation menu
   - Assign to "Primary Menu" location

### 3. File Permissions
```bash
# Set correct permissions
find /path/to/wordpress -type d -exec chmod 755 {} \;
find /path/to/wordpress -type f -exec chmod 644 {} \;
chmod 600 wp-config.php
```

## Post-Deployment Checklist

### 1. Security
- [ ] Install SSL certificate
- [ ] Enable HTTPS
- [ ] Update WordPress salts
- [ ] Configure security headers
- [ ] Set up firewall rules

### 2. Performance
- [ ] Enable caching
- [ ] Configure CDN (if used)
- [ ] Optimize images
- [ ] Enable GZIP compression

### 3. Testing
- [ ] Test user registration
- [ ] Verify all test types work
  - Selective attention test
  - Divided attention test
  - Alternative attention test
  - Extended attention test
- [ ] Check subscription system
- [ ] Verify email notifications
- [ ] Test responsive design
- [ ] Check database connections

### 4. Backup
- [ ] Configure automated backups
- [ ] Test backup restoration
- [ ] Document backup procedures

## Important Notes
1. Update wp-config.php with production values
2. Change all hardcoded URLs from local to production
3. Update email settings for notifications
4. Configure SSL and force HTTPS
5. Set up backup schedule

## Support
For technical support, contact:
- Email: support@attentrack.com
- Documentation: docs.attentrack.com

## Version Information
- WordPress: 6.0+
- PHP: 7.4+
- MySQL: 5.7+
- Theme Version: 1.0
