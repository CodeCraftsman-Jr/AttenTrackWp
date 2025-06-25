<?php
/**
 * Multi-Tier Dashboard Router for AttenTrack
 * Routes users to appropriate dashboards based on their roles and permissions
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dashboard Router Class
 */
class AttenTrack_Dashboard_Router {
    
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        add_action('template_redirect', array($this, 'handle_dashboard_routing'));
    }
    
    /**
     * Handle dashboard routing based on user role and permissions
     */
    public function handle_dashboard_routing() {
        // Only handle dashboard pages
        if (!$this->is_dashboard_page()) {
            return;
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_redirect(wp_login_url(get_permalink()));
            exit;
        }
        
        $current_user = wp_get_current_user();
        $requested_type = $_GET['type'] ?? '';
        
        // Determine user's primary role and appropriate dashboard
        $user_dashboard = $this->determine_user_dashboard($current_user);
        
        // If no specific type requested, redirect to user's default dashboard
        if (empty($requested_type)) {
            wp_redirect(add_query_arg('type', $user_dashboard, get_permalink()));
            exit;
        }
        
        // Validate user can access requested dashboard type
        if (!$this->can_access_dashboard_type($current_user, $requested_type)) {
            // Redirect to their appropriate dashboard with error message
            wp_redirect(add_query_arg(array(
                'type' => $user_dashboard,
                'error' => 'access_denied'
            ), get_permalink()));
            exit;
        }
        
        // Load appropriate dashboard template
        $this->load_dashboard_template($requested_type, $current_user);
    }
    
    /**
     * Check if current page is a dashboard page
     */
    private function is_dashboard_page() {
        global $post;

        if (!$post) {
            return false;
        }

        // Check if it's specifically the dashboard page
        return ($post->post_name === 'dashboard' && $post->post_type === 'page');
    }
    
    /**
     * Determine user's primary dashboard type
     */
    private function determine_user_dashboard($user) {
        $roles = $user->roles;
        
        // Priority order: admin > institution_admin > staff > client
        if (in_array('administrator', $roles)) {
            return 'admin';
        } elseif (in_array('institution_admin', $roles)) {
            return 'institution';
        } elseif (in_array('staff', $roles)) {
            return 'staff';
        } elseif (in_array('client', $roles)) {
            return 'client';
        }
        
        // Default to client for unknown roles
        return 'client';
    }
    
    /**
     * Check if user can access specific dashboard type
     */
    private function can_access_dashboard_type($user, $dashboard_type) {
        $roles = $user->roles;
        
        switch ($dashboard_type) {
            case 'admin':
                return in_array('administrator', $roles);
                
            case 'institution':
                return in_array('administrator', $roles) || in_array('institution_admin', $roles);
                
            case 'staff':
                return in_array('administrator', $roles) || 
                       in_array('institution_admin', $roles) || 
                       in_array('staff', $roles);
                
            case 'client':
                return in_array('administrator', $roles) || 
                       in_array('institution_admin', $roles) || 
                       in_array('client', $roles);
                
            default:
                return false;
        }
    }
    
    /**
     * Load appropriate dashboard template
     */
    private function load_dashboard_template($dashboard_type, $user) {
        // Set global variables for templates
        global $attentrack_dashboard_type, $attentrack_current_user;
        $attentrack_dashboard_type = $dashboard_type;
        $attentrack_current_user = $user;
        
        // Log dashboard access
        attentrack_log_audit_action($user->ID, 'dashboard_access', 'dashboard', null, 
            attentrack_get_user_institution_id($user->ID), array(
                'dashboard_type' => $dashboard_type
            ));
        
        // Include appropriate template
        switch ($dashboard_type) {
            case 'admin':
                $this->load_admin_dashboard($user);
                break;
                
            case 'institution':
                $this->load_institution_dashboard($user);
                break;
                
            case 'staff':
                $this->load_staff_dashboard($user);
                break;
                
            case 'client':
                $this->load_client_dashboard($user);
                break;
                
            default:
                wp_die('Invalid dashboard type');
        }
    }
    
    /**
     * Load admin dashboard
     */
    private function load_admin_dashboard($user) {
        // Admin gets full system overview
        $template = locate_template('admin-dashboard-template.php');
        if ($template) {
            include $template;
        } else {
            // Fallback to WordPress admin
            wp_redirect(admin_url());
            exit;
        }
    }
    
    /**
     * Load institution admin dashboard
     */
    private function load_institution_dashboard($user) {
        // Check if user has institution
        $institution_id = attentrack_get_user_institution_id($user->ID);
        
        if (!$institution_id) {
            wp_die('You are not associated with any institution. Please contact support.');
        }
        
        // Check subscription status
        $subscription_manager = AttenTrack_Subscription_Manager::getInstance();
        $subscription = $subscription_manager->get_institution_subscription($institution_id);
        
        if (!$subscription || $subscription->status !== 'active') {
            $template = locate_template('subscription-expired-template.php');
            if ($template) {
                include $template;
                return;
            }
        }
        
        $template = locate_template('institution-dashboard-template.php');
        if ($template) {
            include $template;
        } else {
            wp_die('Institution dashboard template not found');
        }
    }
    
    /**
     * Load staff dashboard
     */
    private function load_staff_dashboard($user) {
        // Check if staff member has any assigned clients
        $assigned_clients = attentrack_get_staff_assigned_clients($user->ID);
        
        // Set global variable for template
        global $attentrack_assigned_clients;
        $attentrack_assigned_clients = $assigned_clients;
        
        $template = locate_template('staff-dashboard-template.php');
        if ($template) {
            include $template;
        } else {
            // Fallback to basic dashboard
            $this->load_basic_dashboard($user, 'staff');
        }
    }
    
    /**
     * Load client dashboard
     */
    private function load_client_dashboard($user) {
        // Check if viewing specific client (for institution admins)
        $viewing_user_id = 0;
        if (isset($_GET['view_client']) && current_user_can('manage_institution_users')) {
            $viewing_user_id = intval($_GET['view_client']);
            
            // Verify client belongs to institution
            if (!attentrack_can_access_resource('client_data', $viewing_user_id, 'view')) {
                wp_die('You do not have permission to view this client.');
            }
        }
        
        // Set global variable for template
        global $attentrack_viewing_user_id;
        $attentrack_viewing_user_id = $viewing_user_id;
        
        $template = locate_template('client-dashboard-template.php');
        if (!$template) {
            // Try legacy template name
            $template = locate_template('patient-dashboard-template.php');
        }
        
        if ($template) {
            include $template;
        } else {
            $this->load_basic_dashboard($user, 'client');
        }
    }
    
    /**
     * Load basic dashboard fallback
     */
    private function load_basic_dashboard($user, $role) {
        get_header();
        ?>
        <div class="container mt-4">
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info">
                        <h4>Welcome, <?php echo esc_html($user->display_name); ?>!</h4>
                        <p>Your <?php echo esc_html($role); ?> dashboard is being prepared. Please check back soon.</p>
                        <p><strong>Role:</strong> <?php echo esc_html(ucfirst($role)); ?></p>
                        <p><strong>Last Login:</strong> <?php echo esc_html(get_user_meta($user->ID, 'last_login', true) ?: 'First time'); ?></p>
                    </div>
                    
                    <?php if ($role === 'client'): ?>
                        <div class="card">
                            <div class="card-header">
                                <h5>Available Tests</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <a href="<?php echo home_url('/selective-attention-test'); ?>" class="btn btn-primary btn-block">
                                            Selective Attention Test
                                        </a>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <a href="<?php echo home_url('/divided-attention-test'); ?>" class="btn btn-primary btn-block">
                                            Divided Attention Test
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mt-3">
                        <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-secondary">Logout</a>
                    </div>
                </div>
            </div>
        </div>
        <?php
        get_footer();
    }
    
    /**
     * Get dashboard navigation items based on user role
     */
    public function get_dashboard_navigation($user, $dashboard_type) {
        $nav_items = array();
        
        switch ($dashboard_type) {
            case 'admin':
                $nav_items = array(
                    'overview' => array('title' => 'System Overview', 'url' => admin_url()),
                    'institutions' => array('title' => 'Institutions', 'url' => admin_url('admin.php?page=institutions')),
                    'users' => array('title' => 'Users', 'url' => admin_url('users.php')),
                    'reports' => array('title' => 'Reports', 'url' => admin_url('admin.php?page=reports'))
                );
                break;
                
            case 'institution':
                $nav_items = array(
                    'overview' => array('title' => 'Dashboard', 'url' => home_url('/dashboard?type=institution')),
                    'clients' => array('title' => 'Clients', 'url' => home_url('/dashboard?type=institution&section=clients')),
                    'staff' => array('title' => 'Staff', 'url' => home_url('/dashboard?type=institution&section=staff')),
                    'assignments' => array('title' => 'Assignments', 'url' => home_url('/dashboard?type=institution&section=assignments')),
                    'reports' => array('title' => 'Reports', 'url' => home_url('/dashboard?type=institution&section=reports')),
                    'subscription' => array('title' => 'Subscription', 'url' => home_url('/dashboard?type=institution&section=subscription'))
                );
                break;
                
            case 'staff':
                $nav_items = array(
                    'overview' => array('title' => 'Dashboard', 'url' => home_url('/dashboard?type=staff')),
                    'clients' => array('title' => 'My Clients', 'url' => home_url('/dashboard?type=staff&section=clients')),
                    'reports' => array('title' => 'Reports', 'url' => home_url('/dashboard?type=staff&section=reports'))
                );
                break;
                
            case 'client':
                $nav_items = array(
                    'overview' => array('title' => 'Dashboard', 'url' => home_url('/dashboard?type=client')),
                    'tests' => array('title' => 'Take Tests', 'url' => home_url('/dashboard?type=client&section=tests')),
                    'results' => array('title' => 'My Results', 'url' => home_url('/dashboard?type=client&section=results')),
                    'profile' => array('title' => 'Profile', 'url' => home_url('/dashboard?type=client&section=profile'))
                );
                break;
        }
        
        return $nav_items;
    }
}

// Initialize dashboard router
AttenTrack_Dashboard_Router::getInstance();

/**
 * Helper function to get current dashboard type
 */
function attentrack_get_current_dashboard_type() {
    global $attentrack_dashboard_type;
    return $attentrack_dashboard_type ?? 'client';
}

/**
 * Helper function to check if user is viewing their own dashboard
 */
function attentrack_is_own_dashboard() {
    global $attentrack_viewing_user_id;
    return empty($attentrack_viewing_user_id) || $attentrack_viewing_user_id == get_current_user_id();
}
