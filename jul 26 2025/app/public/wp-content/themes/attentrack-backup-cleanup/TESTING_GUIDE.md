# AttenTrack Multi-Tier Access Control Testing Guide

## 🚀 **QUICK START TESTING**

### 1. Access the Test Interface
Navigate to: `your-site.com/wp-content/themes/attentrack/admin-test-page.php`

### 2. Run Automated Tests
Click **"Run All Tests"** to execute the comprehensive test suite that validates:
- User role permissions
- Data isolation
- Security boundaries
- Authentication flows
- Audit logging

## 📋 **MANUAL TESTING CHECKLIST**

### **PHASE 1: Database Migration Testing**

#### ✅ **Step 1: Check Migration Status**
```php
// In WordPress admin or via WP-CLI
$migration = AttenTrack_Terminology_Migration::getInstance();
$status = $migration->get_migration_status();
var_dump($status);
```

**Expected Results:**
- `database_migrated: true`
- `remaining_patient_references: 0`
- Tables renamed from `attentrack_patient_*` to `attentrack_client_*`

#### ✅ **Step 2: Verify New Tables Exist**
Check these tables exist in your database:
- `wp_attentrack_client_details`
- `wp_attentrack_staff_assignments`
- `wp_attentrack_user_role_assignments`
- `wp_attentrack_subscription_details`
- `wp_attentrack_audit_log`

### **PHASE 2: User Role Testing**

#### ✅ **Step 3: Create Test Users**

**Create Institution Admin:**
```php
// WordPress Admin > Users > Add New
Username: test_admin
Email: admin@testinstitution.com
Role: Institution Admin
```

**Create Staff Member:**
```php
Username: test_staff
Email: staff@testinstitution.com
Role: Staff
```

**Create Clients:**
```php
Username: test_client1
Email: client1@testinstitution.com
Role: Client

Username: test_client2
Email: client2@testinstitution.com
Role: Client
```

#### ✅ **Step 4: Test Role Capabilities**

**Test Client Role:**
1. Login as `test_client1`
2. Navigate to `/dashboard?type=client`
3. ✅ Should see client dashboard
4. Try to access `/dashboard?type=institution`
5. ❌ Should be redirected/denied
6. Try to access subscription management
7. ❌ Should be denied

**Test Staff Role:**
1. Login as `test_staff`
2. Navigate to `/dashboard?type=staff`
3. ✅ Should see staff dashboard
4. ✅ Should see "No clients assigned" message
5. Try to access institution management
6. ❌ Should be denied

**Test Institution Admin Role:**
1. Login as `test_admin`
2. Navigate to `/dashboard?type=institution`
3. ✅ Should see full institution dashboard
4. ✅ Should see user management options
5. ✅ Should see subscription management

### **PHASE 3: Permission Boundary Testing**

#### ✅ **Step 5: Test Data Isolation**

**Setup Client Assignment:**
1. Login as Institution Admin
2. Go to Staff Assignments section
3. Assign `test_client1` to `test_staff`
4. Leave `test_client2` unassigned

**Test Staff Access:**
1. Login as `test_staff`
2. ✅ Should see `test_client1` in assigned clients
3. ❌ Should NOT see `test_client2`
4. Try direct URL: `/dashboard?type=client&view_client=CLIENT2_ID`
5. ❌ Should be denied access

**Test Client Data Access:**
1. Login as `test_client1`
2. ✅ Should see own test results
3. Try to access another client's data via URL manipulation
4. ❌ Should be denied

#### ✅ **Step 6: Test AJAX Endpoints**

**Test with Browser Developer Tools:**
```javascript
// Try to access unauthorized client data
fetch('/wp-admin/admin-ajax.php', {
    method: 'POST',
    body: new FormData(Object.assign(document.createElement('form'), {
        action: 'get_client_details',
        user_id: 'UNAUTHORIZED_CLIENT_ID',
        nonce: 'VALID_NONCE'
    }))
})
.then(response => response.json())
.then(data => console.log(data)); // Should return error
```

### **PHASE 4: Security Testing**

#### ✅ **Step 7: Authentication Security**

**Test Failed Login Attempts:**
1. Try to login with wrong password 6 times
2. ✅ Account should be locked after 5 attempts
3. Check audit log for lockout entry

**Test Session Timeouts:**
1. Login as different role types
2. Wait for session timeout periods:
   - Client: 1 hour
   - Staff: 2 hours
   - Institution Admin: 4 hours
3. ✅ Should be automatically logged out

**Test Suspicious Activity:**
1. Login from different IP addresses rapidly
2. ✅ Should trigger suspicious activity detection

#### ✅ **Step 8: Audit Logging**

**Verify Audit Logs:**
```php
// Check recent audit logs
$logs = attentrack_get_audit_logs(array(), 10);
foreach ($logs as $log) {
    echo $log->action . ' - ' . $log->created_at . "\n";
}
```

**Expected Log Entries:**
- User login/logout events
- Permission denied attempts
- Data access attempts
- User role changes
- Client assignments

### **PHASE 5: Subscription Management Testing**

#### ✅ **Step 9: Subscription Workflows**

**Test Member Limits:**
1. Login as Institution Admin
2. Set member limit to 5
3. Try to add 6th member
4. ❌ Should be prevented

**Test Usage Statistics:**
1. Have clients take tests
2. Check usage reports
3. ✅ Should show accurate test counts

### **PHASE 6: Dashboard Functionality Testing**

#### ✅ **Step 10: Dashboard Features**

**Client Dashboard:**
1. Login as client
2. ✅ Complete profile information
3. ✅ Take available tests
4. ✅ View test results
5. ✅ See test history

**Staff Dashboard:**
1. Login as staff
2. ✅ View assigned clients
3. ✅ Access client details
4. ✅ Generate reports (if implemented)

**Institution Dashboard:**
1. Login as institution admin
2. ✅ Create new users
3. ✅ Assign clients to staff
4. ✅ View institution analytics
5. ✅ Manage subscription

## 🔍 **AUTOMATED TESTING COMMANDS**

### Run Full Test Suite
```bash
# Via WP-CLI (if available)
wp eval "
require_once get_template_directory() . '/tests/test-suite.php';
\$suite = new AttenTrack_Test_Suite();
\$results = \$suite->run_all_tests();
print_r(\$results);
"
```

### Check Migration Status
```bash
wp eval "
\$migration = AttenTrack_Terminology_Migration::getInstance();
\$status = \$migration->get_migration_status();
print_r(\$status);
"
```

### Verify User Roles
```bash
wp eval "
\$roles = wp_roles();
print_r(\$roles->roles);
"
```

## 🐛 **TROUBLESHOOTING COMMON ISSUES**

### Issue: "Permission Denied" Errors
**Solution:**
1. Check user has correct role assigned
2. Verify institution membership
3. Check capability mappings

### Issue: Migration Not Working
**Solution:**
1. Check database permissions
2. Verify table existence
3. Run migration manually:
```php
$migration = AttenTrack_Terminology_Migration::getInstance();
$result = $migration->run_migration();
```

### Issue: Session Timeouts Not Working
**Solution:**
1. Check if sessions are enabled
2. Verify timeout constants
3. Check for JavaScript errors

### Issue: Audit Logs Not Recording
**Solution:**
1. Check database table exists
2. Verify write permissions
3. Check error logs

## ✅ **TESTING CHECKLIST SUMMARY**

- [ ] Database migration completed successfully
- [ ] All new tables created
- [ ] User roles working correctly
- [ ] Permission boundaries enforced
- [ ] Data isolation working
- [ ] AJAX endpoints secured
- [ ] Authentication security active
- [ ] Session timeouts working
- [ ] Audit logging functional
- [ ] Subscription management working
- [ ] All dashboards accessible
- [ ] Staff-client assignments working
- [ ] Terminology migration complete

## 📊 **EXPECTED TEST RESULTS**

**Automated Test Suite Should Show:**
- ✅ All role capability tests passing
- ✅ Permission boundary tests passing
- ✅ Data isolation tests passing
- ✅ Authentication flow tests passing
- ✅ Audit logging tests passing

**Manual Testing Should Confirm:**
- ✅ Users can only access appropriate dashboards
- ✅ Staff can only see assigned clients
- ✅ Clients can only see their own data
- ✅ Institution admins have full control
- ✅ Security measures are active
- ✅ All terminology updated to "client"

## 🚨 **CRITICAL SECURITY TESTS**

### Must Pass Before Production:
1. **Data Isolation**: Staff cannot access unassigned clients
2. **Permission Boundaries**: Roles cannot escalate privileges
3. **Session Security**: Timeouts and lockouts working
4. **Audit Trail**: All sensitive actions logged
5. **Input Validation**: All forms properly sanitized

---

**Testing Complete?** ✅ System is ready for production deployment!
**Issues Found?** 🔧 Review logs and fix before proceeding!
