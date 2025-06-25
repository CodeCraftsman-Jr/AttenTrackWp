# AttenTrack Multi-Tier Access Control System

## Overview

This document provides comprehensive documentation for the AttenTrack Multi-Tier Access Control System, which implements three distinct user roles with hierarchical permissions and robust security features.

## User Roles and Permissions

### 1. CLIENT ROLE (End Users - formerly "patients")

**Authentication:**
- Login via institution-provided credentials (ID + password)
- Session timeout: 1 hour
- Enhanced security validation

**Core Permissions:**
- ✅ Access personal dashboard with profile management
- ✅ Complete assigned psychological/assessment tests
- ✅ View personal test history and results with timestamps
- ✅ Update own profile information (contact details, preferences)

**Strict Restrictions:**
- ❌ Zero access to subscription/billing interfaces
- ❌ Cannot view other users' data or test results
- ❌ No administrative capabilities or user management functions
- ❌ Cannot modify test assignments or institutional settings

### 2. INSTITUTION ADMIN ROLE (Paying Customers)

**Authentication:**
- Full administrative access to their organizational instance
- Session timeout: 4 hours
- Advanced security headers and validation

**Core Permissions:**
- ✅ Complete user lifecycle management (create/edit/deactivate client and staff accounts)
- ✅ Subscription management (assign trial periods, manage billing, set access duration)
- ✅ Staff assignment system (assign specific clients to staff members)
- ✅ Organization-wide data access (view all clients, staff, and test results within their institution)
- ✅ Generate comprehensive reports and analytics for their organization
- ✅ Configure institutional settings and test parameters

**Billing Integration:**
- ✅ Manage subscription tiers based on member count and duration
- ✅ View usage statistics and billing history
- ✅ Extend subscriptions and modify member limits

### 3. STAFF ROLE (Institution Employees)

**Authentication:**
- Institution-scoped access with limited permissions
- Session timeout: 2 hours
- Standard security validation

**Core Permissions:**
- ✅ View test results and profiles ONLY for specifically assigned clients
- ✅ Generate reports for assigned clients only
- ✅ Access staff dashboard with assigned client overview

**Strict Restrictions:**
- ❌ Zero visibility into clients assigned to other staff members
- ❌ Cannot create, modify, or delete any user accounts
- ❌ No access to subscription management or institutional billing
- ❌ Cannot reassign clients or modify staff assignments
- ❌ No access to organization-wide analytics or settings

## Technical Implementation

### Database Schema

#### New Tables Created:

1. **attentrack_client_details** (renamed from attentrack_patient_details)
   - Stores client profile information
   - Updated column names (patient_id → client_id)

2. **attentrack_staff_assignments**
   - Links staff members to specific clients
   - Tracks assignment history and status

3. **attentrack_user_role_assignments**
   - Complex role management with institution context
   - Supports role-based permissions with expiration

4. **attentrack_subscription_details**
   - Enhanced subscription management
   - Tracks member limits, billing cycles, and features

5. **attentrack_audit_log**
   - Comprehensive security and action logging
   - Tracks all permission-sensitive operations

### WordPress Integration

#### Custom User Roles:
- `client` (replaces `patient`)
- `staff` (new role)
- `institution_admin` (enhanced from `institution`)

#### Custom Capabilities:
- Role-specific capabilities for fine-grained permission control
- Hierarchical permission structure
- Backward compatibility with existing capabilities

### Security Features

#### Role-Based Access Control (RBAC):
- Comprehensive permission checking system
- Data isolation middleware
- Resource-level access validation

#### Enhanced Authentication:
- Role-specific session timeouts
- Suspicious activity detection
- Account lockout after failed attempts
- Security headers based on user role

#### Audit Logging:
- All permission-sensitive actions logged
- IP address and user agent tracking
- Automatic log cleanup (configurable retention)

## File Structure

```
/inc/
├── database-migration.php          # Database schema migration
├── multi-tier-roles.php           # User role definitions
├── rbac-system.php                # Role-based access control
├── audit-logging.php              # Security audit logging
├── staff-client-assignments.php   # Staff-client assignment system
├── subscription-management.php    # Enhanced subscription management
├── enhanced-authentication.php    # Advanced authentication features
├── terminology-migration.php      # Patient→Client terminology migration
└── dashboard-router.php           # Multi-tier dashboard routing

/includes/
├── client-details-handler.php     # Client data management (new)
└── patient-details-handler.php    # Legacy support (maintained)

/templates/
├── client-dashboard-template.php  # Client dashboard
├── staff-dashboard-template.php   # Staff dashboard
└── institution-dashboard-template.php # Institution admin dashboard

/tests/
└── test-suite.php                 # Comprehensive test suite
```

## Installation and Deployment

### Prerequisites
- WordPress 5.0+
- PHP 7.4+
- MySQL 5.7+
- Active AttenTrack theme

### Deployment Steps

1. **Backup Current System**
   ```bash
   # Backup database
   mysqldump -u username -p database_name > attentrack_backup.sql
   
   # Backup files
   tar -czf attentrack_files_backup.tar.gz wp-content/themes/attentrack/
   ```

2. **Deploy New Files**
   - Upload all new files to the theme directory
   - Ensure proper file permissions (644 for files, 755 for directories)

3. **Run Database Migration**
   ```php
   // Access WordPress admin and run:
   // wp-admin/admin.php?page=attentrack-migration
   
   // Or programmatically:
   $migration = AttenTrack_Terminology_Migration::getInstance();
   $result = $migration->run_migration();
   ```

4. **Verify Installation**
   ```php
   // Run test suite to validate installation
   $test_suite = new AttenTrack_Test_Suite();
   $results = $test_suite->run_all_tests();
   ```

### Configuration

#### Required WordPress Options:
```php
// Set default user role for new registrations
update_option('default_role', 'client');

// Configure session settings
update_option('attentrack_session_timeout_client', 3600);
update_option('attentrack_session_timeout_staff', 7200);
update_option('attentrack_session_timeout_institution_admin', 14400);
```

#### Institution Setup:
1. Create institution record in database
2. Assign institution admin user
3. Configure subscription details
4. Set member limits and billing information

## Usage Guide

### For Institution Admins

#### Managing Users:
1. Access Institution Dashboard
2. Navigate to "Users" section
3. Create new client/staff accounts
4. Assign roles and permissions

#### Staff-Client Assignments:
1. Go to "Assignments" section
2. Select staff member
3. Choose clients to assign
4. Add assignment notes
5. Save assignments

#### Subscription Management:
1. Access "Subscription" section
2. View current usage and limits
3. Extend subscription periods
4. Modify member limits
5. View billing history

### For Staff Members

#### Viewing Assigned Clients:
1. Access Staff Dashboard
2. View "My Clients" section
3. Click on client to view details
4. Generate individual reports

#### Generating Reports:
1. Select client from assigned list
2. Choose report type and date range
3. Generate and download report

### For Clients

#### Taking Tests:
1. Access Client Dashboard
2. Navigate to "Take Tests" section
3. Select available test type
4. Complete test following instructions

#### Viewing Results:
1. Go to "Test Results" section
2. View historical test data
3. Compare performance over time

## Security Considerations

### Data Protection:
- All sensitive data encrypted in transit
- Role-based data isolation enforced
- Audit logging for compliance

### Access Control:
- Principle of least privilege applied
- Regular permission validation
- Automatic session management

### Monitoring:
- Failed login attempt tracking
- Suspicious activity detection
- Comprehensive audit trails

## Troubleshooting

### Common Issues:

1. **Migration Errors**
   - Check database permissions
   - Verify table existence
   - Review error logs

2. **Permission Denied Errors**
   - Verify user roles are correctly assigned
   - Check capability mappings
   - Validate institution membership

3. **Session Timeout Issues**
   - Verify session timeout settings
   - Check for JavaScript errors
   - Validate authentication flow

### Support Contacts:
- Technical Support: [support@attentrack.com]
- Documentation: [docs.attentrack.com]
- Emergency Contact: [emergency@attentrack.com]

## Changelog

### Version 2.0.0 (Current)
- ✅ Implemented multi-tier access control system
- ✅ Added staff role and client assignment system
- ✅ Enhanced subscription management
- ✅ Migrated from "patient" to "client" terminology
- ✅ Added comprehensive audit logging
- ✅ Implemented role-specific dashboards
- ✅ Enhanced authentication and security

### Version 1.x (Legacy)
- Basic institution and patient roles
- Simple subscription system
- Limited access controls

## License and Support

This system is proprietary to AttenTrack and includes advanced security features. For support, licensing, or customization requests, please contact the development team.

---

**Last Updated:** December 2024  
**Version:** 2.0.0  
**Compatibility:** WordPress 5.0+, PHP 7.4+
