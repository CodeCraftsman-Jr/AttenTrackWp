<?php
// Ensure WordPress core is loaded
if (!defined('ABSPATH')) {
    require_once(dirname(__FILE__) . '/../../../../wp-load.php');
}

// Include WordPress upgrade functionality for dbDelta
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

// Load required files - Multi-tier access control system
require_once get_template_directory() . '/inc/database-migration.php';
require_once get_template_directory() . '/inc/multi-tier-roles.php';
require_once get_template_directory() . '/inc/rbac-system.php';
require_once get_template_directory() . '/inc/audit-logging.php';
require_once get_template_directory() . '/inc/staff-client-assignments.php';
require_once get_template_directory() . '/inc/subscription-management.php';
require_once get_template_directory() . '/inc/enhanced-authentication.php';
require_once get_template_directory() . '/inc/terminology-migration.php';
// Dashboard router disabled - using page-dashboard.php instead
// require_once get_template_directory() . '/inc/dashboard-router.php';

// Load existing files
require_once get_template_directory() . '/includes/utilities.php';
require_once get_template_directory() . '/includes/subscription-functions.php';
require_once get_template_directory() . '/inc/institution-functions.php';
require_once get_template_directory() . '/inc/institution-ajax.php';
require_once get_template_directory() . '/includes/subscription-handler.php';
require_once get_template_directory() . '/includes/patient-details-handler.php';
require_once get_template_directory() . '/includes/client-details-handler.php';

// Include essential files
require_once get_template_directory() . '/includes/class-bootstrap-walker-nav-menu.php';
require_once get_template_directory() . '/inc/user-data-consolidation.php';
require_once get_template_directory() . '/inc/role-access-check.php';

// Include dashboard access control
require_once get_template_directory() . '/inc/dashboard-access-control.php';
require_once get_template_directory() . '/inc/consolidated-authentication.php';

// Institution Dashboard AJAX Handlers
require_once get_template_directory() . '/inc/institution-ajax.php';

// Institution User Update Script
require_once get_template_directory() . '/inc/update-institution-users.php';

// Create institution role on theme activation
function create_institution_role() {
    // Check if the role already exists
    if (!get_role('institution')) {
        // Add the institution role with capabilities
        add_role(
            'institution',
            'Institution',
            array(
                'read' => true,
                'edit_posts' => false,
                'delete_posts' => false,
                'publish_posts' => false,
                'upload_files' => true,
                'manage_institution_users' => true,
            )
        );
    }
}
add_action('init', 'create_institution_role');

// Create database tables on theme activation
function attentrack_create_database_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    // Subscriptions table - standardized schema
    $subscriptions_table = $wpdb->prefix . 'attentrack_subscriptions';
    $sql = "CREATE TABLE IF NOT EXISTS $subscriptions_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        profile_id varchar(50) NOT NULL,
        plan_name varchar(50) NOT NULL,
        plan_group enum('small_scale','large_scale') NOT NULL DEFAULT 'small_scale',
        amount decimal(10,2) NOT NULL DEFAULT 0,
        duration_months int(11) NOT NULL DEFAULT 0,
        member_limit int(11) NOT NULL DEFAULT 1,
        days_limit int(11) NOT NULL DEFAULT 0,
        payment_id varchar(100) DEFAULT NULL,
        order_id varchar(100) DEFAULT NULL,
        status varchar(20) NOT NULL DEFAULT 'pending',
        start_date datetime NOT NULL,
        end_date datetime DEFAULT NULL,
        created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY profile_id (profile_id),
        KEY status (status),
        KEY plan_name (plan_name)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Check if table was created
    if ($wpdb->get_var("SHOW TABLES LIKE '$subscriptions_table'") != $subscriptions_table) {
        error_log("Failed to create subscriptions table: $subscriptions_table");
    } else {
        error_log("Successfully created or verified subscriptions table: $subscriptions_table");

        // Check if we need to migrate from plan_type to plan_name
        $columns = $wpdb->get_results("SHOW COLUMNS FROM $subscriptions_table LIKE 'plan_type'");
        if (!empty($columns)) {
            // Migrate plan_type column to plan_name
            error_log("Migrating subscription table from plan_type to plan_name");
            $wpdb->query("ALTER TABLE $subscriptions_table CHANGE COLUMN plan_type plan_name varchar(50) NOT NULL");

            // Update plan_group to use ENUM if it's not already
            $wpdb->query("ALTER TABLE $subscriptions_table MODIFY COLUMN plan_group enum('small_scale','large_scale') NOT NULL DEFAULT 'small_scale'");

            error_log("Subscription table migration completed");
        }
    }
    
    // Institutions table
    $institutions_table = $wpdb->prefix . 'attentrack_institutions';
    $sql = "CREATE TABLE IF NOT EXISTS $institutions_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        institution_name varchar(255) NOT NULL,
        institution_type varchar(50) DEFAULT NULL,
        contact_person varchar(100) DEFAULT NULL,
        contact_email varchar(100) DEFAULT NULL,
        contact_phone varchar(20) DEFAULT NULL,
        address text DEFAULT NULL,
        city varchar(100) DEFAULT NULL,
        state varchar(100) DEFAULT NULL,
        country varchar(100) DEFAULT NULL,
        postal_code varchar(20) DEFAULT NULL,
        website varchar(255) DEFAULT NULL,
        logo_url varchar(255) DEFAULT NULL,
        member_limit int(11) NOT NULL DEFAULT 0,
        members_used int(11) NOT NULL DEFAULT 0,
        status varchar(20) NOT NULL DEFAULT 'active',
        created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY user_id (user_id),
        KEY institution_name (institution_name),
        KEY status (status)
    ) $charset_collate;";
    
    dbDelta($sql);
    
    // Check if table was created
    if ($wpdb->get_var("SHOW TABLES LIKE '$institutions_table'") != $institutions_table) {
        error_log("Failed to create institutions table: $institutions_table");
    } else {
        error_log("Successfully created or verified institutions table: $institutions_table");
    }
    
    // Institution Members table
    $institution_members_table = $wpdb->prefix . 'attentrack_institution_members';
    $sql = "CREATE TABLE IF NOT EXISTS $institution_members_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        institution_id bigint(20) NOT NULL,
        user_id bigint(20) NOT NULL,
        role varchar(50) DEFAULT 'member',
        added_by bigint(20) DEFAULT NULL,
        status varchar(20) NOT NULL DEFAULT 'active',
        created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY institution_user (institution_id, user_id),
        KEY user_id (user_id),
        KEY status (status)
    ) $charset_collate;";
    
    dbDelta($sql);
    
    // Check if table was created
    if ($wpdb->get_var("SHOW TABLES LIKE '$institution_members_table'") != $institution_members_table) {
        error_log("Failed to create institution members table: $institution_members_table");
    } else {
        error_log("Successfully created or verified institution members table: $institution_members_table");
    }
}
add_action('after_switch_theme', 'attentrack_create_database_tables');
add_action('init', 'attentrack_create_database_tables');

// Theme Setup
function attentrack_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));

    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'attentrack'),
        'footer'  => __('Footer Menu', 'attentrack'),
    ));
}
add_action('after_setup_theme', 'attentrack_setup');

// Create required pages on theme activation
function attentrack_create_pages() {
    $pages = array(
        'signin' => array(
            'title' => 'Sign In',
            'template' => 'page-signin.php',
            'content' => ''
        ),
        'signup' => array(
            'title' => 'Sign Up',
            'template' => 'page-signup.php',
            'content' => ''
        ),
        'home2' => array(
            'title' => 'Test Selection',
            'template' => 'home2-template.php',
            'content' => ''
        ),
        'dashboard' => array(
            'title' => 'Dashboard',
            'template' => 'page-dashboard.php',
            'content' => ''
        ),
        'selective-attention-test' => array(
            'title' => 'Selective Attention Test',
            'template' => 'selective-attention-test.php',
            'content' => ''
        ),
        'divided-attention-test' => array(
            'title' => 'Divided Attention Test',
            'template' => 'divided-attention-test.php',
            'content' => ''
        ),
        'alternative-attention-test' => array(
            'title' => 'Alternative Attention Test',
            'template' => 'alternative-attention-test.php',
            'content' => ''
        ),
        'extended-attention-test' => array(
            'title' => 'Extended Attention Test',
            'template' => 'extended-attention-test.php',
            'content' => ''
        ),
        'demo-selective-test' => array(
            'title' => 'Demo Selective Test',
            'template' => 'demo-selective-test-template.php',
            'content' => ''
        ),
        'demo-divided-test' => array(
            'title' => 'Demo Divided Test',
            'template' => 'demo-divided-test-template.php',
            'content' => ''
        ),
        'demo-alternative-test' => array(
            'title' => 'Demo Alternative Test',
            'template' => 'demo-alternative-test-template.php',
            'content' => ''
        ),
        'demo-extended-test' => array(
            'title' => 'Demo Extended Test',
            'template' => 'demo-extended-test-template.php',
            'content' => ''
        ),
        'selective-test-instructions' => array(
            'title' => 'Selective Attention Test Instructions',
            'template' => 'selective-test-instructions-template.php',
            'content' => ''
        ),
        'divided-test-instructions' => array(
            'title' => 'Divided Attention Test Instructions',
            'template' => 'divided-test-instructions-template.php',
            'content' => ''
        ),
        'alternative-test-instructions' => array(
            'title' => 'Alternative Attention Test Instructions',
            'template' => 'alternative-test-instructions-template.php',
            'content' => ''
        ),
        'extended-test-instructions' => array(
            'title' => 'Extended Attention Test Instructions',
            'template' => 'extended-test-instructions-template.php',
            'content' => ''
        ),
        'subscription' => array(
            'title' => 'Subscription',
            'template' => 'page-subscription.php',
            'content' => ''
        ),
        'checkout' => array(
            'title' => 'Checkout',
            'template' => 'page-checkout.php',
            'content' => ''
        ),
        'payment-success' => array(
            'title' => 'Payment Success',
            'template' => 'page-payment-success.php',
            'content' => ''
        ),
        'payment-failed' => array(
            'title' => 'Payment Failed',
            'template' => 'page-payment-failed.php',
            'content' => ''
        ),
        'profile' => array(
            'title' => 'Profile',
            'template' => 'page-profile.php',
            'content' => ''
        ),
        'test-history' => array(
            'title' => 'Test History',
            'template' => 'page-test-history.php',
            'content' => ''
        ),
        'reports' => array(
            'title' => 'Reports',
            'template' => 'page-reports.php',
            'content' => ''
        )
    );

    // Create or update pages
    foreach ($pages as $slug => $page_data) {
        $existing_page = get_page_by_path($slug);
        
        if (!$existing_page) {
            $page_id = wp_insert_post(array(
                'post_title' => $page_data['title'],
                'post_name' => $slug,
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_content' => $page_data['content']
            ));

            if ($page_id && !is_wp_error($page_id)) {
                // Set page template
                if (!empty($page_data['template'])) {
                    update_post_meta($page_id, '_wp_page_template', $page_data['template']);
                }

                // Set home page
                if ($slug === 'home') {
                    update_option('show_on_front', 'page');
                    update_option('page_on_front', $page_id);
                }
            }
        }
    }

    // Create navigation menu
    $menu_name = 'AttenTrack Main Menu';
    $menu_exists = wp_get_nav_menu_object($menu_name);
    
    if (!$menu_exists) {
        $menu_id = wp_create_nav_menu($menu_name);
        
        if (!is_wp_error($menu_id)) {
            // Add menu items
            $menu_items = array(
                'home' => 'Home',
                'dashboard' => 'Dashboard',
                'home2' => 'Take Tests',
                'subscription' => 'Subscription'
            );

            foreach ($menu_items as $slug => $title) {
                $page = get_page_by_path($slug);
                if ($page) {
                    wp_update_nav_menu_item($menu_id, 0, array(
                        'menu-item-title' => $title,
                        'menu-item-object' => 'page',
                        'menu-item-object-id' => $page->ID,
                        'menu-item-type' => 'post_type',
                        'menu-item-status' => 'publish'
                    ));
                }
            }

            // Assign menu to theme location
            $locations = get_theme_mod('nav_menu_locations');
            $locations['primary'] = $menu_id;
            set_theme_mod('nav_menu_locations', $locations);
        }
    }

    // Create required database tables
    attentrack_create_tables();
}

// Create required database tables
if (!function_exists('attentrack_create_tables')) {
    function attentrack_create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Patient details table
        $table_name = $wpdb->prefix . 'attentrack_patient_details';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            profile_id varchar(20) NOT NULL,
            test_id varchar(20) NOT NULL,
            user_code varchar(20) NOT NULL,
            first_name varchar(50),
            last_name varchar(50),
            age int(3),
            gender varchar(10),
            email varchar(100),
            phone varchar(20),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY profile_test_user (profile_id, test_id, user_code)
        ) $charset_collate;";

        // Test results tables
        $test_tables = array(
            'selective_results',
            'divided_results',
            'alternative_results',
            'extended_results'
        );

        foreach ($test_tables as $table) {
            $table_name = $wpdb->prefix . 'attentrack_' . $table;
            $sql .= "CREATE TABLE IF NOT EXISTS $table_name (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                profile_id varchar(20) NOT NULL,
                test_id varchar(20) NOT NULL,
                user_code varchar(20) NOT NULL,
                correct_count int(11) DEFAULT 0,
                wrong_count int(11) DEFAULT 0,
                missed_count int(11) DEFAULT 0,
                average_reaction_time float DEFAULT 0,
                test_date datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY  (id),
                KEY profile_test_user (profile_id, test_id, user_code)
            ) $charset_collate;";
        }

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

// Hook into theme activation
add_action('after_switch_theme', 'attentrack_create_pages');

// Firebase configuration
define('FIREBASE_PUBLIC_KEY', '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA4ZwN7HLKvX+ZpP0GHgmR
0Vgl/ZD3K4p9ZLvL4ZwN7HLKvX+ZpP0GHgmR0Vgl/ZD3K4p9ZLvL4ZwN7HLKvX+Z
pP0GHgmR0Vgl/ZD3K4p9ZLvL4ZwN7HLKvX+ZpP0GHgmR0Vgl/ZD3K4p9ZLvL4ZwN
7HLKvX+ZpP0GHgmR0Vgl/ZD3K4p9ZLvL4ZwN7HLKvX+ZpP0GHgmR0Vgl/ZD3K4p9
ZLvL
-----END PUBLIC KEY-----');

// Razorpay configuration
define('RAZORPAY_KEY_ID', 'rzp_test_VRFkhYqRkNQlG3');
define('RAZORPAY_KEY_SECRET', '0awz8jYvyo4ltzTIGojYrRO4');

// Enqueue scripts and styles
function attentrack_enqueue_scripts() {
    // Bootstrap CSS and JS
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js', array('jquery'), null, true);
    
    // Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');

    // Google Fonts - Modern Typography
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap');

    // Theme styles
    wp_enqueue_style('attentrack-style', get_stylesheet_uri(), array(), time());
    
    // Custom styles
    $background_image_url = get_template_directory_uri() . '/assets/images/containerbg.jpg';
    $custom_css = "
        .navbar { padding: 1rem 0; }
        .navbar-brand img { max-height: 40px; width: auto; }
        .dropdown-toggle::after { margin-left: 0.5rem; }
        .dropdown-menu { min-width: 200px; }
        .dropdown-item i { width: 20px; }
        .user-avatar { width: 32px; height: 32px; object-fit: cover; }

        /* Force new hero design */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%) !important;
            min-height: 100vh !important;
            position: relative !important;
            overflow: hidden !important;
        }

        .hero-section .overlay-container,
        section .overlay-container,
        .overlay-container {
            background-image: none !important;
            background: transparent !important;
        }
    ";
    wp_add_inline_style('attentrack-style', $custom_css);
    
    // jQuery (make sure it's loaded)
    wp_enqueue_script('jquery');
    
    // Firebase SDK
    wp_enqueue_script('firebase-app', 'https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js', array(), null, true);
    wp_enqueue_script('firebase-auth', 'https://www.gstatic.com/firebasejs/9.6.1/firebase-auth-compat.js', array('firebase-app'), null, true);
    
    // Auth script (loads Firebase config)
    wp_enqueue_script('auth-js', get_template_directory_uri() . '/js/auth-new.js', array('jquery', 'firebase-app', 'firebase-auth'), null, true);
    
    // Firebase Phone Auth script
    wp_enqueue_script('firebase-phone-auth-js', get_template_directory_uri() . '/js/firebase-phone-auth.js', array('jquery', 'firebase-app', 'firebase-auth'), null, true);
    
    // Firebase configuration script (initializes Firebase) - MUST load first
    wp_enqueue_script('firebase-config-js', get_template_directory_uri() . '/js/firebase-config.js', array('jquery', 'firebase-app', 'firebase-auth', 'auth-js', 'firebase-phone-auth-js'), null, true);

    // Firebase Email Auth script - depends on Firebase being initialized
    wp_enqueue_script('firebase-email-auth', get_template_directory_uri() . '/js/firebase-email-auth.js', array('jquery', 'firebase-app', 'firebase-auth', 'firebase-config-js'), '1.0.0', true);

    // Modern UI enhancements
    wp_enqueue_script('modern-ui', get_template_directory_uri() . '/js/modern-ui.js', array('jquery'), '1.0.0', true);
    
    // Localize script with AJAX URL and nonce
    $auth_data = array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'homeUrl' => home_url(),
        'nonce' => wp_create_nonce('auth-nonce')
    );
    
    // Localize both auth scripts with the same data
    wp_localize_script('auth-js', 'authData', $auth_data);
    wp_localize_script('firebase-email-auth', 'authData', $auth_data);
}
add_action('wp_enqueue_scripts', 'attentrack_enqueue_scripts');

// Create database tables on theme activation
function attentrack_create_tables() {
    require_once get_template_directory() . '/inc/user-management.php';
    create_custom_user_tables();
}
add_action('after_switch_theme', 'attentrack_create_tables');

// Create pages and menu
function setup_attentrack_pages_and_menu() {
    $pages = [
        'about-app' => ['title' => 'About App', 'template' => 'page-templates/about-app.php'],
        'contact-us' => ['title' => 'Contact Us', 'template' => 'page-templates/contact-us.php'],
        'signin' => ['title' => 'Sign In', 'template' => 'page-signin.php'],
        'signup' => ['title' => 'Sign Up', 'template' => 'page-signup.php'],
        'patient-details-form' => ['title' => 'Patient Details Form', 'template' => 'patientdetailsform-template.php'],
        'selection-page' => ['title' => 'Selection Page', 'template' => 'selectionpage2-template.php'],
        'alternative-attention-test' => ['title' => 'Alternative Attention Test', 'template' => 'alternative-attention-test.php'],
        'divided-attention-test' => ['title' => 'Divided Attention Test', 'template' => 'divided-attention-test.php'],
        'selective-attention-test' => ['title' => 'Selective Test', 'template' => 'selective-attention-test.php']
    ];
    
    foreach ($pages as $slug => $data) {
        if (!get_page_by_path($slug)) {
            $page_id = wp_insert_post([
                'post_title' => $data['title'],
                'post_name' => $slug,
                'post_status' => 'publish',
                'post_type' => 'page'
            ]);
            if ($page_id) update_post_meta($page_id, '_wp_page_template', $data['template']);
        }
    }
    
    // Create menu
    $menu_name = 'Primary Menu';
    $menu_exists = wp_get_nav_menu_object($menu_name);
    if (!$menu_exists) {
        $menu_id = wp_create_nav_menu($menu_name);
        
        // Only add About App and Contact Us to the menu
        $menu_items = [
            'about-app' => 'About App',
            'contact-us' => 'Contact Us'
        ];
        
        foreach ($menu_items as $slug => $title) {
            $page = get_page_by_path($slug);
            if ($page) {
                wp_update_nav_menu_item($menu_id, 0, [
                    'menu-item-title' => $title,
                    'menu-item-object' => 'page',
                    'menu-item-object-id' => $page->ID,
                    'menu-item-type' => 'post_type',
                    'menu-item-status' => 'publish'
                ]);
            }
        }
        
        set_theme_mod('nav_menu_locations', ['primary' => $menu_id]);
    }
}

// Run setup on theme activation and manual trigger
add_action('after_switch_theme', 'setup_attentrack_pages_and_menu');
if (isset($_GET['setup_pages']) && current_user_can('manage_options')) {
    setup_attentrack_pages_and_menu();
}

// Add menu page for subscription plans
function attentrack_add_subscription_menu() {
    add_menu_page(
        'Subscription Plans',
        'Subscription',
        'read',
        'subscription-plans',
        '',
        'dashicons-cart',
        30
    );
}
add_action('admin_menu', 'attentrack_add_subscription_menu');

// Handle auth page access
function handle_auth_page_access() {
    global $post;
    
    if (!$post) return;
    
    // If user is logged in and trying to access auth pages, redirect to home
    if (is_user_logged_in() && in_array($post->post_name, ['signin', 'signup'])) {
        wp_redirect(home_url());
        exit;
    }
    
    // If user is not logged in and trying to access protected pages, redirect to signin
    if (!is_user_logged_in() && in_array($post->post_name, ['selection-page', 'dashboard'])) {
        wp_redirect(home_url('/signin'));
        exit;
    }
}
add_action('template_redirect', 'handle_auth_page_access', 1);

// Add AJAX endpoint for getting home URL
function get_home_url_ajax() {
    wp_send_json_success(home_url());
}
add_action('wp_ajax_get_home_url', 'get_home_url_ajax');
add_action('wp_ajax_nopriv_get_home_url', 'get_home_url_ajax');

// Function to generate a unique 4-digit code
function generate_unique_code($prefix, $existing_codes = array()) {
    do {
        $code = $prefix . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
    } while (in_array($code, $existing_codes));
    return $code;
}

// Function to ensure user has all required IDs
function ensure_user_ids($user_id) {
    global $wpdb;
    
    // Get existing codes to ensure uniqueness
    $existing_profiles = $wpdb->get_col("SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key = 'profile_id'");
    $existing_tests = $wpdb->get_col("SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key = 'test_id'");
    $existing_user_codes = $wpdb->get_col("SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key = 'user_code'");
    
    // Generate IDs
    $profile_id = generate_unique_code('P', $existing_profiles);
    $test_id = generate_unique_code('T', $existing_tests);
    $user_code = generate_unique_code('U', $existing_user_codes);
    
    // Save IDs
    update_user_meta($user_id, 'profile_id', $profile_id);
    update_user_meta($user_id, 'test_id', $test_id);
    update_user_meta($user_id, 'user_code', $user_code);
    
    return array(
        'profile_id' => $profile_id,
        'test_id' => $test_id,
        'user_code' => $user_code
    );
}

// Hook to assign IDs on user registration
add_action('user_register', 'ensure_user_ids');

// Function to get user IDs
function get_user_ids($user_id) {
    return array(
        'profile_id' => get_user_meta($user_id, 'profile_id', true),
        'test_id' => get_user_meta($user_id, 'test_id', true),
        'user_code' => get_user_meta($user_id, 'user_code', true)
    );
}

// Function to save divided attention test results
function save_divided_attention_results_handler() {
    global $wpdb;

    // Verify nonce for security
    check_ajax_referer('divided_attention_nonce', 'nonce');

    // Get the test data
    $test_id = sanitize_text_field($_POST['test_id']);
    $profile_id = sanitize_text_field($_POST['profile_id']);
    $user_code = sanitize_text_field($_POST['user_code']); 
    $correct_responses = intval($_POST['correct_responses']);
    $incorrect_responses = intval($_POST['incorrect_responses']);
    $missed_responses = intval($_POST['missed_responses']);
    $reaction_time = floatval($_POST['reaction_time']);

    // Insert into database
    $result = $wpdb->insert(
        'wp_attentrack_divided_results',
        array(
            'test_id' => $test_id,
            'profile_id' => $profile_id,
            'user_code' => $user_code, 
            'correct_responses' => $correct_responses,
            'incorrect_responses' => $incorrect_responses,
            'missed_responses' => $missed_responses,
            'total_colors_shown' => intval($_POST['totalColorsShown']),
            'reaction_time' => $reaction_time,
            'test_date' => current_time('mysql')
        ),
        array('%s', '%s', '%s', '%d', '%d', '%d', '%d', '%f', '%s')
    );

    if ($result === false) {
        wp_send_json_error('Failed to save test results');
    } else {
        wp_send_json_success('Test results saved successfully');
    }
}

// Add AJAX endpoint for saving test results
function save_test_results_ajax() {
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
        return;
    }
    
    // Get current user ID
    $user_id = get_current_user_id();
    
    // Ensure user has test ID and profile ID
    $ids = ensure_user_ids($user_id);
    
    // Get test type and results from POST data
    $test_type = isset($_POST['test_type']) ? sanitize_text_field($_POST['test_type']) : '';
    $results = isset($_POST['results']) ? $_POST['results'] : array();
    
    // Validate test type
    if (empty($test_type)) {
        wp_send_json_error('Test type is required');
        return;
    }
    
    // Validate results
    if (empty($results)) {
        wp_send_json_error('Results are required');
        return;
    }
    
    // Log the received data for debugging
    error_log('Received test results for ' . $test_type . ': ' . print_r($results, true));
    error_log('User IDs: ' . print_r($ids, true));
    
    // Process results based on test type
    switch ($test_type) {
        case 'selective_attention_extended':
            // Save overall results
            $total_correct = 0;
            $total_incorrect = 0;
            $total_reaction_time = 0;
            
            // Get test ID and profile ID
            $test_id = $ids['test_id'];
            $profile_id = $ids['profile_id'];
            
            // Save individual phase results
            for ($i = 1; $i <= 4; $i++) {
                if (isset($results['phases'][$i-1])) {
                    $phase = $results['phases'][$i-1];
                    
                    // Log the phase data for debugging
                    error_log("Processing phase {$i} data: " . print_r($phase, true));
                    
                    // Ensure phase data is correct
                    $phase_num = isset($phase['phase']) ? intval($phase['phase']) : $i;
                    
                    update_user_meta($user_id, "test_selective_attention_extended_phase{$phase_num}_total_letters", sanitize_text_field($phase['totalLetters']));
                    update_user_meta($user_id, "test_selective_attention_extended_phase{$phase_num}_p_letters", sanitize_text_field($phase['pLetters']));
                    update_user_meta($user_id, "test_selective_attention_extended_phase{$phase_num}_correct", sanitize_text_field($phase['correctResponses']));
                    update_user_meta($user_id, "test_selective_attention_extended_phase{$phase_num}_incorrect", sanitize_text_field($phase['incorrectResponses']));
                    update_user_meta($user_id, "test_selective_attention_extended_phase{$phase_num}_time", sanitize_text_field($phase['reactionTime']));
                    update_user_meta($user_id, "test_selective_attention_extended_phase{$phase_num}_score", sanitize_text_field($phase['score']));
                    
                    $total_correct += intval($phase['correctResponses']);
                    $total_incorrect += intval($phase['incorrectResponses']);
                    $total_reaction_time += floatval($phase['reactionTime']);
                    
                    // Save to custom database table
                    if (function_exists('save_extended_results')) {
                        $extended_data = array(
                            'test_id' => $test_id,
                            'profile_id' => $profile_id,
                            'phase' => $phase_num,
                            'total_letters' => $phase['totalLetters'],
                            'p_letters' => $phase['pLetters'],
                            'correct_responses' => $phase['correctResponses'],
                            'incorrect_responses' => $phase['incorrectResponses'],
                            'reaction_time' => $phase['reactionTime']
                        );
                        
                        // Debug log
                        error_log('Saving extended results for phase ' . $phase_num . ': ' . print_r($extended_data, true));
                        
                        $result = save_extended_results($extended_data);
                        
                        if ($result === false) {
                            global $wpdb;
                            error_log('Database error: ' . $wpdb->last_error);
                        } else {
                            error_log('Successfully saved data to extended results table for phase ' . $phase_num . '. Result: ' . $result);
                        }
                    } else {
                        error_log('save_extended_results function not found!');
                    }
                } else {
                    error_log("Phase {$i} data not found in results array");
                }
            }
            
            // Save combined results
            update_user_meta($user_id, 'test_selective_attention_extended_total_correct', $total_correct);
            update_user_meta($user_id, 'test_selective_attention_extended_total_incorrect', $total_incorrect);
            update_user_meta($user_id, 'test_selective_attention_extended_avg_time', $total_reaction_time / 4);
            update_user_meta($user_id, 'test_selective_attention_extended_total_score', sanitize_text_field($results['totalScore']));
            break;
            
        case 'divided_attention':
            // Extract test ID and profile ID from results or use the generated ones
            $test_id = isset($results['testId']) ? sanitize_text_field($results['testId']) : $ids['test_id'];
            $profile_id = isset($results['profileId']) ? sanitize_text_field($results['profileId']) : $ids['profile_id'];
            
            // Ensure IDs are set
            if (empty($test_id)) $test_id = $ids['test_id'];
            if (empty($profile_id)) $profile_id = $ids['profile_id'];
            
            // Log the IDs
            error_log("Using test ID: {$test_id} and profile ID: {$profile_id} for divided attention test");
            
            // Save test results
            update_user_meta($user_id, 'test_divided_attention_correct', sanitize_text_field($results['correctResponses']));
            update_user_meta($user_id, 'test_divided_attention_incorrect', sanitize_text_field($results['incorrectResponses']));
            update_user_meta($user_id, 'test_divided_attention_reaction_time', sanitize_text_field($results['reactionTime']));
            update_user_meta($user_id, 'test_divided_attention_score', sanitize_text_field($results['score']));
            
            // Save to custom database table
            global $wpdb;
            $divided_data = array(
                'test_id' => sanitize_text_field($test_id),
                'profile_id' => sanitize_text_field($profile_id),
                'correct_responses' => intval($results['correctResponses']),
                'incorrect_responses' => intval($results['incorrectResponses']),
                'missed_responses' => intval($results['missedResponses']),
                'total_colors_shown' => intval($results['totalColorsShown']),
                'reaction_time' => floatval($results['reactionTime']),
                'test_date' => current_time('mysql')
            );
            
            $result = $wpdb->insert(
                $wpdb->prefix . 'attentrack_divided_results',
                $divided_data,
                array('%s', '%s', '%d', '%d', '%d', '%d', '%f', '%s')
            );

            if ($result === false) {
                error_log('Database error: ' . $wpdb->last_error);
            } else {
                error_log('Successfully saved divided attention results to database. Result: ' . $result);
            }
            break;
            
        // Add other test types here
        default:
            wp_send_json_error('Invalid test type');
            return;
    }
    
    wp_send_json_success('Test results saved successfully');
}
add_action('wp_ajax_save_test_results', 'save_test_results_ajax');

// AJAX endpoint to get user IDs
function get_user_ids_ajax() {
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
        return;
    }
    
    // Get current user ID
    $user_id = get_current_user_id();
    
    // Ensure user has test ID and profile ID
    $ids = ensure_user_ids($user_id);
    
    // Return the IDs
    wp_send_json_success($ids);
}
add_action('wp_ajax_get_user_ids', 'get_user_ids_ajax');
add_action('wp_ajax_nopriv_get_user_ids', 'get_user_ids_ajax');

// Handle divided attention test results
add_action('wp_ajax_divided_attention_save_results', 'save_divided_attention_results_handler');
add_action('wp_ajax_nopriv_divided_attention_save_results', 'save_divided_attention_results_handler');

// AJAX handler for selective attention test results
add_action('wp_ajax_save_selective_attention_results', 'save_selective_attention_results');
add_action('wp_ajax_nopriv_save_selective_attention_results', 'save_selective_attention_results');

function save_selective_attention_results() {
    global $wpdb;

    // Verify nonce for security
    check_ajax_referer('selective_attention_nonce', 'nonce');

    // Get the test data
    $test_id = sanitize_text_field($_POST['test_id']);
    $profile_id = sanitize_text_field($_POST['profile_id']);
    $user_code = sanitize_text_field($_POST['user_code']); 
    $total_letters = intval($_POST['total_letters']);
    $p_letters = intval($_POST['p_letters']);
    $correct_responses = intval($_POST['correct_responses']);
    $incorrect_responses = intval($_POST['incorrect_responses']);
    $reaction_time = floatval($_POST['reaction_time']);

    // Insert into database
    $result = $wpdb->insert(
        'wp_attentrack_selective_results',
        array(
            'test_id' => $test_id,
            'profile_id' => $profile_id,
            'user_code' => $user_code, 
            'total_letters' => $total_letters,
            'p_letters' => $p_letters,
            'correct_responses' => $correct_responses,
            'incorrect_responses' => $incorrect_responses,
            'reaction_time' => $reaction_time,
            'test_date' => current_time('mysql')
        ),
        array('%s', '%s', '%s', '%d', '%d', '%d', '%d', '%f', '%s') 
    );

    if ($result === false) {
        wp_send_json_error('Failed to save test results');
    } else {
        wp_send_json_success('Test results saved successfully');
    }
}

// Add menu page to view selective attention test results
add_action('admin_menu', 'add_selective_results_page');

function add_selective_results_page() {
    add_menu_page(
        'Selective Attention Results',
        'Selective Results',
        'manage_options',
        'selective-results',
        'display_selective_results'
    );
}

function display_selective_results() {
    global $wpdb;
    
    // Get all results
    $results = $wpdb->get_results("
        SELECT * FROM wp_attentrack_selective_results 
        ORDER BY test_date DESC
    ");
    
    echo '<div class="wrap">';
    echo '<h1>Selective Attention Test Results</h1>';
    
    if ($results) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>
                <tr>
                    <th>Test ID</th>
                    <th>Profile ID</th>
                    <th>Total Letters</th>
                    <th>P Letters</th>
                    <th>Correct Responses</th>
                    <th>Incorrect Responses</th>
                    <th>Reaction Time (s)</th>
                    <th>Test Date</th>
                </tr>
            </thead>';
        
        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row->test_id) . '</td>';
            echo '<td>' . esc_html($row->profile_id) . '</td>';
            echo '<td>' . esc_html($row->total_letters) . '</td>';
            echo '<td>' . esc_html($row->p_letters) . '</td>';
            echo '<td>' . esc_html($row->correct_responses) . '</td>';
            echo '<td>' . esc_html($row->incorrect_responses) . '</td>';
            echo '<td>' . esc_html($row->reaction_time) . '</td>';
            echo '<td>' . esc_html($row->test_date) . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
    } else {
        echo '<p>No test results found.</p>';
    }
    
    echo '</div>';
}

// AJAX handler for selective attention extended test results
add_action('wp_ajax_save_selective_attention_extended_results', 'save_selective_attention_extended_results');
add_action('wp_ajax_nopriv_save_selective_attention_extended_results', 'save_selective_attention_extended_results');

function save_selective_attention_extended_results() {
    global $wpdb;

    // Verify nonce for security
    check_ajax_referer('selective_attention_extended_nonce', 'nonce');

    // Get the test data
    $test_id = sanitize_text_field($_POST['test_id']);
    $profile_id = sanitize_text_field($_POST['profile_id']);
    $user_code = sanitize_text_field($_POST['user_code']); 
    $total_letters = intval($_POST['total_letters']);
    $p_letters = intval($_POST['p_letters']);
    $correct_responses = intval($_POST['correct_responses']);
    $incorrect_responses = intval($_POST['incorrect_responses']);
    $reaction_time = floatval($_POST['reaction_time']);
    $phase = intval($_POST['phase']);

    // Insert into database
    $result = $wpdb->insert(
        'wp_attentrack_extended_results',
        array(
            'test_id' => $test_id,
            'profile_id' => $profile_id,
            'user_code' => $user_code, 
            'total_letters' => $total_letters,
            'p_letters' => $p_letters,
            'correct_responses' => $correct_responses,
            'incorrect_responses' => $incorrect_responses,
            'reaction_time' => $reaction_time,
            'phase' => $phase,
            'test_date' => current_time('mysql')
        ),
        array('%s', '%s', '%s', '%d', '%d', '%d', '%d', '%f', '%d', '%s') 
    );

    if ($result === false) {
        wp_send_json_error('Failed to save test results');
    } else {
        wp_send_json_success('Test results saved successfully');
    }
}

// Remove old handlers that are no longer used
remove_action('wp_ajax_save_divided_attention_test_results', 'save_divided_attention_test_results');
remove_action('wp_ajax_nopriv_save_divided_attention_test_results', 'save_divided_attention_test_results');

// Register Test Results post type
add_action('init', 'register_test_results_post_type');

function register_test_results_post_type() {
    register_post_type('test_result', array(
        'labels' => array(
            'name' => 'Test Results',
            'singular_name' => 'Test Result'
        ),
        'public' => false,
        'show_ui' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        'rewrite' => array('slug' => 'test-result'),
        'supports' => array('title', 'author')
    ));
}

function create_divided_attention_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'attentrack_divided_results';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        test_id varchar(50) NOT NULL,
        profile_id varchar(50) NOT NULL,
        user_code varchar(50) NOT NULL,
        correct_responses int NOT NULL,
        incorrect_responses int NOT NULL,
        missed_responses int NULL DEFAULT 0,
        total_colors_shown int NOT NULL,
        reaction_time decimal(10,2) NOT NULL,
        test_date datetime NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY profile_id (profile_id),
        KEY test_id (test_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Log table creation result
    error_log('Divided Attention Test - Table Creation:');
    error_log($sql);
}

// Run table creation on theme activation
add_action('after_switch_theme', 'create_divided_attention_table');

// Create subscription table on theme activation
function attentrack_create_subscription_table() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}attentrack_subscriptions (
        id bigint NOT NULL AUTO_INCREMENT,
        user_id bigint NOT NULL,
        plan_name varchar(50) NOT NULL,
        amount decimal(10,2) NOT NULL,
        duration_months int NOT NULL,
        payment_id varchar(100) NOT NULL,
        order_id varchar(100) NOT NULL,
        status varchar(20) NOT NULL,
        start_date datetime NOT NULL,
        end_date datetime NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY payment_id (payment_id),
        KEY order_id (order_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_switch_theme', 'attentrack_create_subscription_table');

// Add admin function to repair subscription data
function attentrack_repair_subscription_data() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    global $wpdb;

    // Check for orphaned subscription records with wrong column names
    $table_name = $wpdb->prefix . 'attentrack_subscriptions';

    // Get all columns in the table
    $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");
    $column_names = array_column($columns, 'Field');

    echo '<div class="notice notice-info"><p>Subscription Table Columns: ' . implode(', ', $column_names) . '</p></div>';

    // Check for data integrity issues
    $subscription_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $active_subscriptions = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'active'");

    echo '<div class="notice notice-info"><p>Total Subscriptions: ' . $subscription_count . '</p></div>';
    echo '<div class="notice notice-info"><p>Active Subscriptions: ' . $active_subscriptions . '</p></div>';

    // Show recent subscription records
    $recent_subs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 5");
    if ($recent_subs) {
        echo '<div class="notice notice-info"><p>Recent Subscriptions:</p><pre>' . print_r($recent_subs, true) . '</pre></div>';
    }

    // Show current user's subscriptions specifically
    $current_user = wp_get_current_user();
    if ($current_user->ID) {
        $user_subs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d ORDER BY created_at DESC",
            $current_user->ID
        ));
        echo '<div class="notice notice-info"><p>Current User\'s Subscriptions (User ID: ' . $current_user->ID . '):</p><pre>' . print_r($user_subs, true) . '</pre></div>';

        // Show which subscription is being returned by the function
        if (function_exists('attentrack_get_subscription_status')) {
            $active_sub_query = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d AND status = 'active' ORDER BY end_date DESC LIMIT 1",
                $current_user->ID
            ));
            echo '<div class="notice notice-info"><p>Active Subscription Query Result:</p><pre>' . print_r($active_sub_query, true) . '</pre></div>';
        }
    }

    wp_redirect(admin_url('admin.php?page=attentrack-subscription-debug'));
    exit;
}



function attentrack_subscription_debug_page() {
    if (isset($_POST['repair_subscriptions'])) {
        attentrack_repair_subscription_data();
    }

    if (isset($_POST['create_free_subscription'])) {
        attentrack_create_free_subscription_for_user();
    }

    if (isset($_POST['fix_subscription_names'])) {
        attentrack_fix_subscription_plan_names();
    }

    if (isset($_POST['delete_free_subscriptions'])) {
        attentrack_delete_conflicting_free_subscriptions();
    }

    if (isset($_POST['create_manual_subscription'])) {
        attentrack_create_manual_subscription();
    }

    if (isset($_POST['test_plan_change'])) {
        attentrack_test_plan_change();
    }

    echo '<div class="wrap">';
    echo '<h1>AttenTrack Subscription Debug</h1>';

    // Show current user info
    $current_user = wp_get_current_user();
    echo '<h2>Current User Info</h2>';
    echo '<p>User ID: ' . $current_user->ID . '</p>';
    echo '<p>User Role: ' . implode(', ', $current_user->roles) . '</p>';
    echo '<p>Is Institution: ' . (user_can($current_user->ID, 'institution') ? 'Yes' : 'No') . '</p>';

    // Show subscription status
    if (function_exists('attentrack_get_subscription_status')) {
        $subscription = attentrack_get_subscription_status($current_user->ID);
        echo '<h2>Current Subscription Status</h2>';
        echo '<pre>' . print_r($subscription, true) . '</pre>';
    }

    echo '<form method="post" style="margin: 20px 0;">';
    echo '<input type="submit" name="repair_subscriptions" class="button button-primary" value="Check Subscription Data" />';
    echo '</form>';

    echo '<form method="post" style="margin: 20px 0;">';
    echo '<input type="submit" name="create_free_subscription" class="button button-secondary" value="Create Free Subscription for Current User" />';
    echo '</form>';

    echo '<form method="post" style="margin: 20px 0;">';
    echo '<input type="submit" name="fix_subscription_names" class="button button-secondary" value="Fix Subscription Plan Names" />';
    echo '</form>';

    echo '<form method="post" style="margin: 20px 0;">';
    echo '<input type="submit" name="delete_free_subscriptions" class="button button-secondary" value="Delete Conflicting Free Subscriptions" />';
    echo '</form>';

    echo '<h3>Manual Subscription Creation</h3>';
    echo '<form method="post" style="margin: 20px 0;">';
    echo '<p>User ID: <input type="number" name="manual_user_id" value="24" style="width: 80px;" /></p>';
    echo '<p>Plan: <select name="manual_plan">';
    echo '<option value="small_free">Free Tier</option>';
    echo '<option value="small_30">30 Members Plan (₹1999)</option>';
    echo '<option value="small_60">60 Members Plan (₹3499)</option>';
    echo '<option value="large_120">120 Members Plan (₹5999)</option>';
    echo '<option value="large_160">160 Members Plan (₹7999)</option>';
    echo '</select></p>';
    echo '<input type="submit" name="create_manual_subscription" class="button button-primary" value="Create Manual Subscription" />';
    echo '</form>';

    echo '<h3>Test Plan Change</h3>';
    echo '<form method="post" style="margin: 20px 0;">';
    echo '<p>This simulates a successful payment and plan change:</p>';
    echo '<p>User ID: <input type="number" name="test_user_id" value="24" style="width: 80px;" /></p>';
    echo '<p>New Plan: <select name="test_plan">';
    echo '<option value="small_60">60 Members Plan (₹3499)</option>';
    echo '<option value="large_120">120 Members Plan (₹5999)</option>';
    echo '<option value="large_160">160 Members Plan (₹7999)</option>';
    echo '</select></p>';
    echo '<input type="submit" name="test_plan_change" class="button button-secondary" value="Test Plan Change" />';
    echo '</form>';

    echo '</div>';
}

// Function to create a free subscription for testing
function attentrack_create_free_subscription_for_user() {
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    if (!$user_id) {
        echo '<div class="notice notice-error"><p>No user logged in</p></div>';
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'attentrack_subscriptions';

    // Check if user already has an active subscription
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE user_id = %d AND status = 'active'",
        $user_id
    ));

    if ($existing) {
        echo '<div class="notice notice-warning"><p>User already has an active subscription</p></div>';
        return;
    }

    // Get or create profile_id
    $profile_id = get_user_meta($user_id, 'profile_id', true);
    if (!$profile_id) {
        $profile_id = 'P' . sprintf('%04d', $user_id);
        update_user_meta($user_id, 'profile_id', $profile_id);
    }

    // Create free subscription
    $subscription_data = array(
        'user_id' => $user_id,
        'profile_id' => $profile_id,
        'plan_name' => 'small_free',
        'plan_group' => 'small_scale',
        'amount' => 0.00,
        'duration_months' => 0,
        'member_limit' => 1,
        'days_limit' => 0,
        'payment_id' => 'FREE_' . time(),
        'order_id' => 'FREE_ORDER_' . time(),
        'status' => 'active',
        'start_date' => current_time('mysql'),
        'end_date' => date('Y-m-d H:i:s', strtotime('+100 years')),
        'created_at' => current_time('mysql')
    );

    $result = $wpdb->insert($table_name, $subscription_data);

    if ($result) {
        echo '<div class="notice notice-success"><p>Free subscription created successfully! Subscription ID: ' . $wpdb->insert_id . '</p></div>';

        // Update user meta for compatibility
        update_user_meta($user_id, 'subscription_status', 'active');
        update_user_meta($user_id, 'subscription_plan_type', 'small_free');
        update_user_meta($user_id, 'subscription_plan_group', 'small_scale');

        // If user is institution, create/update institution record
        if (user_can($user_id, 'institution')) {
            if (function_exists('attentrack_create_or_update_institution')) {
                attentrack_create_or_update_institution($user_id, array(
                    'member_limit' => 1,
                    'members_used' => 0
                ));
                echo '<div class="notice notice-success"><p>Institution record updated</p></div>';
            }
        }
    } else {
        echo '<div class="notice notice-error"><p>Failed to create subscription: ' . $wpdb->last_error . '</p></div>';
    }
}

// Function to delete conflicting free subscriptions
function attentrack_delete_conflicting_free_subscriptions() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'attentrack_subscriptions';
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    if (!$user_id) {
        echo '<div class="notice notice-error"><p>No user logged in</p></div>';
        return;
    }

    // Get all subscriptions for this user
    $all_subs = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE user_id = %d ORDER BY amount DESC, created_at DESC",
        $user_id
    ));

    if (empty($all_subs)) {
        echo '<div class="notice notice-info"><p>No subscriptions found for user. Creating a free subscription now...</p></div>';

        // Create a free subscription since none exist
        attentrack_create_free_subscription_for_user();
        return;
    }

    $paid_subs = array_filter($all_subs, function($sub) { return $sub->amount > 0; });
    $free_subs = array_filter($all_subs, function($sub) { return $sub->amount == 0; });

    echo '<div class="notice notice-info"><p>Found ' . count($paid_subs) . ' paid subscriptions and ' . count($free_subs) . ' free subscriptions.</p></div>';

    // If there are paid subscriptions, deactivate/delete conflicting free ones
    if (!empty($paid_subs)) {
        $deleted_count = 0;

        foreach ($free_subs as $free_sub) {
            if ($free_sub->status === 'active') {
                // Deactivate free subscription
                $result = $wpdb->update(
                    $table_name,
                    ['status' => 'inactive'],
                    ['id' => $free_sub->id]
                );

                if ($result !== false) {
                    $deleted_count++;
                    echo '<div class="notice notice-success"><p>Deactivated free subscription ID ' . $free_sub->id . '</p></div>';
                }
            }
        }

        // Activate the most recent paid subscription
        $latest_paid = reset($paid_subs);
        $wpdb->update(
            $table_name,
            ['status' => 'active'],
            ['id' => $latest_paid->id]
        );

        echo '<div class="notice notice-success"><p>Activated paid subscription ID ' . $latest_paid->id . ' (' . $latest_paid->plan_name . ')</p></div>';

        if ($deleted_count === 0) {
            echo '<div class="notice notice-info"><p>No conflicting free subscriptions to deactivate.</p></div>';
        }
    } else {
        echo '<div class="notice notice-info"><p>No paid subscriptions found. Keeping free subscription active.</p></div>';
    }
}

// Function to create manual subscription
function attentrack_create_manual_subscription() {
    if (!current_user_can('manage_options')) {
        echo '<div class="notice notice-error"><p>Unauthorized</p></div>';
        return;
    }

    $user_id = intval($_POST['manual_user_id']);
    $plan_name = sanitize_text_field($_POST['manual_plan']);

    if (!$user_id || !$plan_name) {
        echo '<div class="notice notice-error"><p>Invalid user ID or plan</p></div>';
        return;
    }

    // Plan configurations
    $plan_configs = array(
        'small_free' => array('amount' => 0, 'member_limit' => 1, 'plan_group' => 'small_scale'),
        'small_30' => array('amount' => 1999, 'member_limit' => 30, 'plan_group' => 'small_scale'),
        'small_60' => array('amount' => 3499, 'member_limit' => 60, 'plan_group' => 'small_scale'),
        'large_120' => array('amount' => 5999, 'member_limit' => 120, 'plan_group' => 'large_scale'),
        'large_160' => array('amount' => 7999, 'member_limit' => 160, 'plan_group' => 'large_scale')
    );

    if (!isset($plan_configs[$plan_name])) {
        echo '<div class="notice notice-error"><p>Invalid plan selected</p></div>';
        return;
    }

    $config = $plan_configs[$plan_name];

    global $wpdb;
    $table_name = $wpdb->prefix . 'attentrack_subscriptions';

    // Deactivate existing subscriptions
    $wpdb->update(
        $table_name,
        array('status' => 'inactive'),
        array('user_id' => $user_id)
    );

    // Get or create profile_id
    $profile_id = get_user_meta($user_id, 'profile_id', true);
    if (!$profile_id) {
        $profile_id = 'P' . sprintf('%04d', $user_id);
        update_user_meta($user_id, 'profile_id', $profile_id);
    }

    // Create subscription
    $subscription_data = array(
        'user_id' => $user_id,
        'profile_id' => $profile_id,
        'plan_name' => $plan_name,
        'plan_group' => $config['plan_group'],
        'amount' => $config['amount'],
        'duration_months' => $config['amount'] > 0 ? 12 : 0,
        'member_limit' => $config['member_limit'],
        'days_limit' => 30,
        'payment_id' => 'MANUAL_' . time(),
        'order_id' => 'MANUAL_ORDER_' . time(),
        'status' => 'active',
        'start_date' => current_time('mysql'),
        'end_date' => $config['amount'] > 0 ? date('Y-m-d H:i:s', strtotime('+1 year')) : date('Y-m-d H:i:s', strtotime('+100 years')),
        'created_at' => current_time('mysql')
    );

    $result = $wpdb->insert($table_name, $subscription_data);

    if ($result) {
        echo '<div class="notice notice-success"><p>Manual subscription created successfully!</p>';
        echo '<p>User ID: ' . $user_id . '</p>';
        echo '<p>Plan: ' . $plan_name . '</p>';
        echo '<p>Amount: ₹' . $config['amount'] . '</p>';
        echo '<p>Member Limit: ' . $config['member_limit'] . '</p>';
        echo '<p>Subscription ID: ' . $wpdb->insert_id . '</p></div>';

        // Update user meta
        update_user_meta($user_id, 'subscription_status', 'active');
        update_user_meta($user_id, 'subscription_plan_type', $plan_name);
        update_user_meta($user_id, 'subscription_plan_group', $config['plan_group']);

    } else {
        echo '<div class="notice notice-error"><p>Failed to create subscription: ' . $wpdb->last_error . '</p></div>';
    }
}

// Function to test plan change
function attentrack_test_plan_change() {
    if (!current_user_can('manage_options')) {
        echo '<div class="notice notice-error"><p>Unauthorized</p></div>';
        return;
    }

    $user_id = intval($_POST['test_user_id']);
    $plan_name = sanitize_text_field($_POST['test_plan']);

    if (!$user_id || !$plan_name) {
        echo '<div class="notice notice-error"><p>Invalid user ID or plan</p></div>';
        return;
    }

    // Plan configurations
    $plan_configs = array(
        'small_60' => array('amount' => 3499, 'member_limit' => 60, 'plan_group' => 'small_scale'),
        'large_120' => array('amount' => 5999, 'member_limit' => 120, 'plan_group' => 'large_scale'),
        'large_160' => array('amount' => 7999, 'member_limit' => 160, 'plan_group' => 'large_scale')
    );

    if (!isset($plan_configs[$plan_name])) {
        echo '<div class="notice notice-error"><p>Invalid plan selected</p></div>';
        return;
    }

    $config = $plan_configs[$plan_name];

    global $wpdb;
    $table_name = $wpdb->prefix . 'attentrack_subscriptions';

    // Deactivate existing subscriptions
    $deactivated = $wpdb->update(
        $table_name,
        array('status' => 'inactive'),
        array('user_id' => $user_id, 'status' => 'active')
    );

    echo '<div class="notice notice-info"><p>Deactivated ' . $deactivated . ' existing subscriptions</p></div>';

    // Get or create profile_id
    $profile_id = get_user_meta($user_id, 'profile_id', true);
    if (!$profile_id) {
        $profile_id = 'P' . sprintf('%04d', $user_id);
        update_user_meta($user_id, 'profile_id', $profile_id);
    }

    // Create new subscription
    $subscription_data = array(
        'user_id' => $user_id,
        'profile_id' => $profile_id,
        'plan_name' => $plan_name,
        'plan_group' => $config['plan_group'],
        'amount' => $config['amount'],
        'duration_months' => 12,
        'member_limit' => $config['member_limit'],
        'days_limit' => 30,
        'payment_id' => 'TEST_' . time(),
        'order_id' => 'TEST_ORDER_' . time(),
        'status' => 'active',
        'start_date' => current_time('mysql'),
        'end_date' => date('Y-m-d H:i:s', strtotime('+1 year')),
        'created_at' => current_time('mysql')
    );

    $result = $wpdb->insert($table_name, $subscription_data);

    if ($result) {
        $subscription_id = $wpdb->insert_id;

        // Update institution member limit
        $wpdb->update(
            $wpdb->prefix . 'attentrack_institutions',
            array('member_limit' => $config['member_limit']),
            array('user_id' => $user_id)
        );

        echo '<div class="notice notice-success"><p>Plan change test completed successfully!</p>';
        echo '<p>User ID: ' . $user_id . '</p>';
        echo '<p>New Plan: ' . $plan_name . '</p>';
        echo '<p>Amount: ₹' . $config['amount'] . '</p>';
        echo '<p>Member Limit: ' . $config['member_limit'] . '</p>';
        echo '<p>Subscription ID: ' . $subscription_id . '</p>';
        echo '<p><strong>Go to your dashboard and refresh to see the changes!</strong></p></div>';

        // Update user meta
        update_user_meta($user_id, 'subscription_status', 'active');
        update_user_meta($user_id, 'subscription_plan_type', $plan_name);
        update_user_meta($user_id, 'subscription_plan_group', $config['plan_group']);

    } else {
        echo '<div class="notice notice-error"><p>Failed to create new subscription: ' . $wpdb->last_error . '</p></div>';
    }
}

// Function to fix subscription plan names
function attentrack_fix_subscription_plan_names() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'attentrack_subscriptions';

    // Get all subscriptions with potentially wrong plan names
    $subscriptions = $wpdb->get_results("SELECT * FROM $table_name");

    $fixed_count = 0;

    foreach ($subscriptions as $sub) {
        $new_plan_name = null;

        // Check if plan name needs fixing based on amount or other criteria
        if ($sub->amount == 0 && $sub->plan_name !== 'small_free') {
            $new_plan_name = 'small_free';
        } elseif ($sub->amount == 1999 && $sub->plan_name !== 'small_30') {
            $new_plan_name = 'small_30';
        } elseif ($sub->amount == 3499 && $sub->plan_name !== 'small_60') {
            $new_plan_name = 'small_60';
        } elseif ($sub->amount == 5999 && $sub->plan_name !== 'large_120') {
            $new_plan_name = 'large_120';
        } elseif ($sub->amount == 7999 && $sub->plan_name !== 'large_160') {
            $new_plan_name = 'large_160';
        }

        if ($new_plan_name) {
            $result = $wpdb->update(
                $table_name,
                ['plan_name' => $new_plan_name],
                ['id' => $sub->id]
            );

            if ($result !== false) {
                $fixed_count++;
                echo '<div class="notice notice-success"><p>Fixed subscription ID ' . $sub->id . ': ' . $sub->plan_name . ' → ' . $new_plan_name . '</p></div>';
            }
        }
    }

    if ($fixed_count === 0) {
        echo '<div class="notice notice-info"><p>No subscription plan names needed fixing.</p></div>';
    } else {
        echo '<div class="notice notice-success"><p>Fixed ' . $fixed_count . ' subscription plan names.</p></div>';
    }
}

// AJAX handler for alternative attention test results
add_action('wp_ajax_save_alternative_attention_results', 'save_alternative_attention_results');
add_action('wp_ajax_nopriv_save_alternative_attention_results', 'save_alternative_attention_results');

function save_alternative_attention_results() {
    check_ajax_referer('alternative_attention_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . 'attentrack_alternative_results';

    $test_id = sanitize_text_field($_POST['test_id']);
    $profile_id = sanitize_text_field($_POST['profile_id']);
    $user_code = sanitize_text_field($_POST['user_code']);
    $correct_responses = intval($_POST['correct_responses']);
    $incorrect_responses = intval($_POST['incorrect_responses']);
    $total_items_shown = intval($_POST['total_items_shown']);
    $reaction_time = floatval($_POST['reaction_time']);

    $result = $wpdb->insert(
        $table_name,
        array(
            'test_id' => $test_id,
            'profile_id' => $profile_id,
            'user_code' => $user_code,
            'correct_responses' => $correct_responses,
            'incorrect_responses' => $incorrect_responses,
            'total_items_shown' => $total_items_shown,
            'reaction_time' => $reaction_time
        ),
        array('%s', '%s', '%s', '%d', '%d', '%d', '%f')
    );

    if ($result === false) {
        wp_send_json_error('Failed to save test results: ' . $wpdb->last_error);
    } else {
        wp_send_json_success('Test results saved successfully');
    }

    wp_die();
}

// Create alternative attention test results table
function create_alternative_attention_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'attentrack_alternative_results';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        test_id varchar(10) NOT NULL,
        profile_id varchar(10) NOT NULL,
        user_code varchar(10) NOT NULL,
        correct_responses int(11) NOT NULL,
        incorrect_responses int(11) NOT NULL,
        total_items_shown int(11) NOT NULL,
        reaction_time float NOT NULL,
        test_date datetime NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Log table creation result
    error_log('Alternative Attention Test - Table Creation:');
    error_log($sql);
}

// Run table creation immediately and on theme activation
create_alternative_attention_table();
add_action('after_switch_theme', 'create_alternative_attention_table');

// Add custom roles and migrate existing users
function add_custom_roles() {
    // Add Institution role
    add_role('institution', 'Institution', array(
        'read' => true,
        'manage_institution' => true,
        'view_institution_dashboard' => true,
        'manage_institution_users' => true
    ));
    
    // Add Patient role
    add_role('patient', 'Patient', array(
        'read' => true,
        'access_patient_dashboard' => true,
        'take_attention_tests' => true,
        'view_test_results' => true
    ));
}
add_action('init', 'add_custom_roles');

// Migrate existing users to their proper roles
function migrate_users_to_roles() {
    global $wpdb;
    
    // Get all users with institution type
    $institution_users = $wpdb->get_results("
        SELECT u.ID 
        FROM {$wpdb->users} u 
        INNER JOIN {$wpdb->prefix}attentrack_institutions i 
        ON u.ID = i.admin_user_id
    ");

    // Assign institution role
    foreach ($institution_users as $user) {
        $user_obj = new WP_User($user->ID);
        $user_obj->remove_role('subscriber'); // Remove default role if exists
        $user_obj->add_role('institution');
        update_user_meta($user->ID, 'user_type', 'institution');
    }

    // Get all users with patient details
    $patient_users = $wpdb->get_results("
        SELECT DISTINCT CAST(SUBSTRING(patient_id, 2) AS UNSIGNED) as user_id 
        FROM {$wpdb->prefix}attentrack_patient_details
    ");

    // Assign patient role
    foreach ($patient_users as $user) {
        $user_obj = new WP_User($user->user_id);
        $user_obj->remove_role('subscriber'); // Remove default role if exists
        $user_obj->add_role('patient');
        update_user_meta($user->user_id, 'user_type', 'patient');
    }
}

// Run migration on theme activation and when manually triggered
add_action('after_switch_theme', 'migrate_users_to_roles');

// Add migration trigger for admin
function add_migration_button() {
    if (current_user_can('manage_options')) {
        ?>
        <div class="wrap">
            <h2>User Role Migration</h2>
            <p>Click the button below to migrate existing users to their proper roles (Institution/Patient).</p>
            <form method="post" action="">
                <?php wp_nonce_field('migrate_users_nonce', 'migrate_users_nonce'); ?>
                <input type="submit" name="migrate_users" class="button button-primary" value="Migrate Users">
            </form>
        </div>
        <?php
    }
}


// Handle manual migration trigger
function handle_manual_migration() {
    if (
        isset($_POST['migrate_users']) && 
        current_user_can('manage_options') && 
        isset($_POST['migrate_users_nonce']) && 
        wp_verify_nonce($_POST['migrate_users_nonce'], 'migrate_users_nonce')
    ) {
        migrate_users_to_roles();
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success"><p>Users have been migrated to their proper roles.</p></div>';
        });
    }
}
add_action('admin_init', 'handle_manual_migration');

// Helper function to get user type
function get_attentrack_user_type($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    $user_type = get_user_meta($user_id, 'user_type', true);
    
    if (!$user_type) {
        $user = new WP_User($user_id);
        if (in_array('institution', (array) $user->roles)) {
            $user_type = 'institution';
        } elseif (in_array('patient', (array) $user->roles)) {
            $user_type = 'patient';
        }
    }
    
    return $user_type;
}

// Redirect wp-login.php to custom login page
function attentrack_redirect_login_page() {
    // Current page
    $page_viewed = basename($_SERVER['REQUEST_URI']);
    
    // Check if accessing admin-direct.php - don't redirect in this case
    if (strpos($_SERVER['REQUEST_URI'], 'admin-direct.php') !== false) {
        return;
    }
    
    // Check if it's the login page
    if($page_viewed == "wp-login.php" && $_SERVER['REQUEST_METHOD'] == 'GET') {
        // Get the redirect URL if it exists
        $redirect_to = isset($_GET['redirect_to']) ? $_GET['redirect_to'] : '';
        $redirect_url = home_url('/signin');
        
        // Add the redirect parameter if it exists
        if (!empty($redirect_to)) {
            $redirect_url = add_query_arg('redirect_to', urlencode($redirect_to), $redirect_url);
        }
        
        wp_redirect($redirect_url);
        exit;
    }
}
add_action('init', 'attentrack_redirect_login_page');

// Redirect wp-admin to custom login page if not logged in
function attentrack_redirect_admin_page() {
    // Check if accessing admin-direct.php - don't redirect in this case
    if (strpos($_SERVER['REQUEST_URI'], 'admin-direct.php') !== false) {
        return;
    }
    
    // Check if trying to access admin page
    if (is_admin() && !wp_doing_ajax() && !is_user_logged_in()) {
        wp_redirect(home_url('/signin?redirect_to=' . urlencode(admin_url())));
        exit;
    }
}
add_action('admin_init', 'attentrack_redirect_admin_page');

// Add exception for admin users to bypass dashboard redirection
function attentrack_admin_bypass_dashboard_redirect($redirect_url, $requested_url) {
    // If user is an administrator, allow direct access to wp-admin
    if (current_user_can('administrator') && strpos($requested_url, '/wp-admin') !== false) {
        return admin_url();
    }
    return $redirect_url;
}
add_filter('login_redirect', 'attentrack_admin_bypass_dashboard_redirect', 10, 2);
