# Role Display/Functionality Fix Guide

## Problem Summary

The user management system had role display and persistence issues where:
1. Role changes were not being reflected in the UI/database
2. Old "patient" role was still showing instead of updated roles
3. Role data was stored in multiple locations causing synchronization issues
4. Caching prevented immediate display of role changes

## Root Causes Identified

### 1. Role Definition Conflicts
- **Old system**: Used `patient` and `institution` roles
- **New system**: Uses `client`, `staff`, and `institution_admin` roles
- **Issue**: Forms and code still referenced old roles

### 2. Multiple Role Storage Locations
- WordPress `wp_usermeta` table (`wp_capabilities`)
- Custom `wp_attentrack_institution_members` table (`role` field)
- Custom `wp_attentrack_user_data` table (`account_type` field)
- **Issue**: Updates only modified some tables, not all

### 3. Inconsistent Role Update Logic
- Different functions used different update methods
- Some bypassed WordPress role system
- **Issue**: Role synchronization failures

### 4. Caching Issues
- WordPress user caches not cleared after role updates
- **Issue**: Changes not visible until cache expiration

## Fixes Implemented

### 1. Updated Role Definitions
**New role structure:**
- `client` - Test takers (formerly `patient`)
- `staff` - Institution employees
- `institution_admin` - Institution owners (formerly `institution`)
- `subscriber` - Regular users
- `administrator` - System administrators

### 2. Comprehensive Role Update Function
**File**: `inc/role-access-check.php`
**Function**: `update_user_role_and_account_type()`

**Features:**
- Updates WordPress roles properly using `$user->set_role()`
- Synchronizes custom institution member roles
- Updates account_type in all relevant tables
- Clears all user caches immediately
- Validates role inputs
- Logs changes for audit

### 3. Updated Frontend Components
**Files updated:**
- `institution-dashboard-template.php` - Role dropdown options
- `fix-user-role-page.php` - Role selection and update logic
- `fix-user-role.php` - Role options and update function
- `check-user-role.php` - Role options and update logic

### 4. AJAX Handler Improvements
**File**: `inc/institution-ajax.php`
**Improvements:**
- Maps institution roles to WordPress roles correctly
- Clears caches after user creation/updates
- Uses proper role assignment logic

### 5. Cache Clearing Implementation
**Added to all role update functions:**
- `wp_cache_delete($user_id, 'users')`
- `wp_cache_delete($user_id, 'user_meta')`
- `clean_user_cache($user_id)`

## Scripts Created

### 1. Role Synchronization Fix
**File**: `fix-role-synchronization.php`
**Purpose**: Comprehensive migration and synchronization script
**Features:**
- Migrates old `patient` roles to `client`
- Migrates old `institution` roles to `institution_admin`
- Synchronizes WordPress roles with institution member roles
- Cleans up old role definitions
- Clears all caches

### 2. Role Fixes Test
**File**: `test-role-fixes.php`
**Purpose**: Verify that all fixes work correctly
**Tests:**
- Role definitions exist
- Role update function works
- Role synchronization between tables
- Cache clearing functionality

## How to Use

### For Immediate Fix
1. **Run the synchronization script:**
   ```
   https://yoursite.com/wp-content/themes/attentrack/fix-role-synchronization.php
   ```

2. **Test the fixes:**
   ```
   https://yoursite.com/wp-content/themes/attentrack/test-role-fixes.php
   ```

### For Individual User Updates
1. **Use the updated role management pages:**
   - `fix-user-role-page.php` - General user role updates
   - `check-user-role.php` - Current user role checking and fixing

2. **Available role options:**
   - **Client**: For users who take attention tests
   - **Staff**: For institution employees who manage clients
   - **Institution Admin**: For institution owners who manage everything
   - **Subscriber**: For regular users
   - **Administrator**: For system administrators

### For Institution Dashboard
The institution dashboard now properly displays and updates user roles:
- Role dropdown shows: Client, Staff, Admin
- Role changes are immediately reflected in the user list
- All role data is synchronized across tables

## Role Clarification

Based on your mention of changing from "patient" to "staff", "client", or "member":

- **Client** = Test takers (best replacement for "patient")
- **Staff** = Institution employees who manage clients
- **Member** = Maps to "subscriber" role (basic users)

**Recommendation**: Use "Client" for users who take tests, as this is the most appropriate replacement for the old "patient" role.

## Verification Steps

1. **Check role display**: Roles should now show correctly in institution dashboard
2. **Test role updates**: Changes should be immediate (no refresh needed)
3. **Verify synchronization**: WordPress roles should match institution member roles
4. **Test caching**: Role changes should be visible immediately

## Troubleshooting

If issues persist:
1. Run `fix-role-synchronization.php` again
2. Check error logs for any PHP errors
3. Verify database tables exist and have correct structure
4. Clear all WordPress caches manually if needed

## Files Modified

- `inc/role-access-check.php` - Core role update function
- `inc/institution-ajax.php` - AJAX handlers
- `institution-dashboard-template.php` - Frontend role options
- `fix-user-role-page.php` - Role management interface
- `fix-user-role.php` - Role update script
- `check-user-role.php` - Role checking interface

All changes maintain backward compatibility while fixing the synchronization issues.
