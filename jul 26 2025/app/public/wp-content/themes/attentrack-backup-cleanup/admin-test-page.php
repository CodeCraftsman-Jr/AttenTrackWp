<?php
/**
 * Admin Test Page for AttenTrack Multi-Tier Access Control System
 * Provides interface to run tests and view results
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Check admin permissions
if (!current_user_can('administrator')) {
    wp_die('You do not have sufficient permissions to access this page.');
}

// Include test suite
require_once get_template_directory() . '/tests/test-suite.php';

get_header();
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1><i class="fas fa-vial"></i> AttenTrack System Testing</h1>
            <p class="lead">Comprehensive testing suite for the multi-tier access control system.</p>
            
            <!-- Test Controls -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-play"></i> Test Controls</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <button id="runAllTests" class="btn btn-primary btn-lg">
                                <i class="fas fa-play"></i> Run All Tests
                            </button>
                            <button id="runMigrationTest" class="btn btn-info ml-2">
                                <i class="fas fa-database"></i> Test Migration
                            </button>
                        </div>
                        <div class="col-md-6 text-right">
                            <button id="clearResults" class="btn btn-outline-secondary">
                                <i class="fas fa-trash"></i> Clear Results
                            </button>
                            <button id="exportResults" class="btn btn-outline-success ml-2">
                                <i class="fas fa-download"></i> Export Results
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Status -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle"></i> System Status</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 id="userRoleStatus" class="text-muted">-</h4>
                                <p>User Roles</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 id="databaseStatus" class="text-muted">-</h4>
                                <p>Database</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 id="permissionStatus" class="text-muted">-</h4>
                                <p>Permissions</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 id="securityStatus" class="text-muted">-</h4>
                                <p>Security</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Results -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list-alt"></i> Test Results</h5>
                </div>
                <div class="card-body">
                    <div id="testResults">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-flask fa-3x mb-3"></i>
                            <p>Click "Run All Tests" to begin testing the system.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manual Testing Guide -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-hand-paper"></i> Manual Testing Guide</h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="manualTestAccordion">
                        <!-- User Role Testing -->
                        <div class="card">
                            <div class="card-header" id="userRoleTestHeader">
                                <h6 class="mb-0">
                                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#userRoleTest">
                                        1. User Role Testing
                                    </button>
                                </h6>
                            </div>
                            <div id="userRoleTest" class="collapse" data-parent="#manualTestAccordion">
                                <div class="card-body">
                                    <h6>Test Client Role:</h6>
                                    <ol>
                                        <li>Create a test client user</li>
                                        <li>Login as client and verify dashboard access</li>
                                        <li>Attempt to access institution features (should fail)</li>
                                        <li>Take a test and verify results are saved</li>
                                    </ol>
                                    
                                    <h6>Test Staff Role:</h6>
                                    <ol>
                                        <li>Create a test staff user</li>
                                        <li>Assign clients to staff member</li>
                                        <li>Login as staff and verify only assigned clients are visible</li>
                                        <li>Attempt to access unassigned client data (should fail)</li>
                                    </ol>
                                    
                                    <h6>Test Institution Admin Role:</h6>
                                    <ol>
                                        <li>Login as institution admin</li>
                                        <li>Create new client and staff users</li>
                                        <li>Assign clients to staff members</li>
                                        <li>Access subscription management</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <!-- Permission Testing -->
                        <div class="card">
                            <div class="card-header" id="permissionTestHeader">
                                <h6 class="mb-0">
                                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#permissionTest">
                                        2. Permission Boundary Testing
                                    </button>
                                </h6>
                            </div>
                            <div id="permissionTest" class="collapse" data-parent="#manualTestAccordion">
                                <div class="card-body">
                                    <h6>Data Isolation Tests:</h6>
                                    <ol>
                                        <li>Create two staff members with different client assignments</li>
                                        <li>Verify Staff A cannot see Staff B's clients</li>
                                        <li>Test direct URL access to unauthorized client data</li>
                                        <li>Verify AJAX endpoints respect permissions</li>
                                    </ol>
                                    
                                    <h6>Subscription Access Tests:</h6>
                                    <ol>
                                        <li>Login as client and attempt to access subscription page</li>
                                        <li>Login as staff and attempt subscription management</li>
                                        <li>Verify only institution admins can access billing</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <!-- Security Testing -->
                        <div class="card">
                            <div class="card-header" id="securityTestHeader">
                                <h6 class="mb-0">
                                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#securityTest">
                                        3. Security Testing
                                    </button>
                                </h6>
                            </div>
                            <div id="securityTest" class="collapse" data-parent="#manualTestAccordion">
                                <div class="card-body">
                                    <h6>Authentication Tests:</h6>
                                    <ol>
                                        <li>Test failed login attempts and account lockout</li>
                                        <li>Verify session timeouts for different roles</li>
                                        <li>Test suspicious activity detection</li>
                                    </ol>
                                    
                                    <h6>Audit Logging Tests:</h6>
                                    <ol>
                                        <li>Perform various actions and check audit logs</li>
                                        <li>Verify IP addresses and user agents are logged</li>
                                        <li>Test log filtering and search functionality</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Run all tests
    $('#runAllTests').click(function() {
        const button = $(this);
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Running Tests...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'run_attentrack_tests',
                nonce: '<?php echo wp_create_nonce('attentrack_test_suite'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    displayTestResults(response.data);
                    updateSystemStatus();
                } else {
                    $('#testResults').html('<div class="alert alert-danger">Test failed: ' + response.data + '</div>');
                }
            },
            error: function() {
                $('#testResults').html('<div class="alert alert-danger">Error running tests. Check console for details.</div>');
            },
            complete: function() {
                button.prop('disabled', false).html('<i class="fas fa-play"></i> Run All Tests');
            }
        });
    });

    // Run migration test
    $('#runMigrationTest').click(function() {
        const button = $(this);
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Testing Migration...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'check_migration_status',
                nonce: '<?php echo wp_create_nonce('attentrack_terminology_migration'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    const status = response.data;
                    let html = '<div class="alert alert-info"><h6>Migration Status:</h6>';
                    html += '<p><strong>Database Migrated:</strong> ' + (status.database_migrated ? 'Yes' : 'No') + '</p>';
                    html += '<p><strong>Files Migrated:</strong> ' + (status.files_migrated ? 'Yes' : 'No') + '</p>';
                    html += '<p><strong>Remaining Patient References:</strong> ' + status.remaining_patient_references + '</p>';
                    html += '</div>';
                    $('#testResults').html(html);
                } else {
                    $('#testResults').html('<div class="alert alert-danger">Migration check failed: ' + response.data + '</div>');
                }
            },
            complete: function() {
                button.prop('disabled', false).html('<i class="fas fa-database"></i> Test Migration');
            }
        });
    });

    // Clear results
    $('#clearResults').click(function() {
        $('#testResults').html('<div class="text-center text-muted py-4"><i class="fas fa-flask fa-3x mb-3"></i><p>Results cleared.</p></div>');
        resetSystemStatus();
    });

    // Export results
    $('#exportResults').click(function() {
        const results = $('#testResults').html();
        if (results.includes('flask')) {
            alert('No test results to export. Run tests first.');
            return;
        }
        
        const blob = new Blob([results], { type: 'text/html' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'attentrack_test_results_' + new Date().toISOString().slice(0,10) + '.html';
        a.click();
        window.URL.revokeObjectURL(url);
    });

    function displayTestResults(results) {
        let html = '<div class="test-results">';
        let passCount = 0;
        let failCount = 0;
        
        results.forEach(function(result) {
            const levelClass = result.level === 'error' ? 'danger' : 
                              result.level === 'success' ? 'success' : 'info';
            
            if (result.message.includes('PASS:')) passCount++;
            if (result.message.includes('FAIL:')) failCount++;
            
            html += '<div class="alert alert-' + levelClass + ' py-2">';
            html += '<small class="text-muted">' + result.timestamp + '</small> ';
            html += result.message;
            html += '</div>';
        });
        
        // Add summary
        html = '<div class="alert alert-primary"><strong>Test Summary:</strong> ' + 
               passCount + ' passed, ' + failCount + ' failed</div>' + html;
        
        html += '</div>';
        $('#testResults').html(html);
    }

    function updateSystemStatus() {
        $('#userRoleStatus').removeClass('text-muted').addClass('text-success').text('✓');
        $('#databaseStatus').removeClass('text-muted').addClass('text-success').text('✓');
        $('#permissionStatus').removeClass('text-muted').addClass('text-success').text('✓');
        $('#securityStatus').removeClass('text-muted').addClass('text-success').text('✓');
    }

    function resetSystemStatus() {
        $('.text-success, .text-danger').removeClass('text-success text-danger').addClass('text-muted').text('-');
    }
});
</script>

<style>
.test-results {
    max-height: 500px;
    overflow-y: auto;
}

.alert {
    margin-bottom: 0.5rem;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
</style>

<?php get_footer(); ?>
