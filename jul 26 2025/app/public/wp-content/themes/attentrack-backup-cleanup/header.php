<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <style>
    .navbar {
        padding: 10px 0;
    }
    .navbar-brand img {
        height: 40px;
        width: auto;
        transition: transform 0.3s ease;
    }
    .navbar-brand:hover img {
        transform: scale(1.05);
    }
    .site-content {
        min-height: calc(100vh - 70px);
    }
    </style>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="<?php echo esc_url(home_url('/')); ?>">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo.svg" alt="AttenTrack" height="40">
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo esc_url(home_url('/')); ?>">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo esc_url(home_url('/about')); ?>">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo esc_url(home_url('/contact')); ?>">Contact</a>
                </li>
            </ul>
            
            <div class="d-flex align-items-center">
                <?php if (is_user_logged_in()):
                    $current_user = wp_get_current_user();
                    $user_roles = $current_user->roles;
                    $user_role = !empty($user_roles) ? $user_roles[0] : 'subscriber';

                    // Determine dashboard type and display name based on role
                    $dashboard_info = array();
                    if (in_array('administrator', $user_roles)) {
                        $dashboard_info = array('type' => 'admin', 'name' => 'Admin Dashboard', 'icon' => 'fas fa-cog');
                    } elseif (in_array('institution_admin', $user_roles)) {
                        $dashboard_info = array('type' => 'institution', 'name' => 'Institution Dashboard', 'icon' => 'fas fa-building');
                    } elseif (in_array('staff', $user_roles)) {
                        $dashboard_info = array('type' => 'staff', 'name' => 'Staff Dashboard', 'icon' => 'fas fa-user-tie');
                    } elseif (in_array('client', $user_roles)) {
                        $dashboard_info = array('type' => 'client', 'name' => 'Client Dashboard', 'icon' => 'fas fa-user-circle');
                    } elseif (in_array('institution', $user_roles)) {
                        // Legacy support for old institution role
                        $dashboard_info = array('type' => 'institution', 'name' => 'Institution Dashboard', 'icon' => 'fas fa-building');
                    } elseif (in_array('patient', $user_roles)) {
                        // Legacy support for old patient role
                        $dashboard_info = array('type' => 'client', 'name' => 'Client Dashboard', 'icon' => 'fas fa-user-circle');
                    } else {
                        $dashboard_info = array('type' => 'client', 'name' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt');
                    }

                    // Format role display name
                    $role_display = ucfirst(str_replace('_', ' ', $user_role));
                ?>
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle text-dark text-decoration-none"
                                type="button"
                                id="userDropdown"
                                data-bs-toggle="dropdown"
                                aria-expanded="false">
                            <img src="<?php echo get_avatar_url($current_user->ID, array('size' => 32)); ?>"
                                 alt="<?php echo esc_attr($current_user->display_name); ?>"
                                 class="rounded-circle me-2"
                                 width="32"
                                 height="32">
                            <?php echo esc_html($current_user->display_name); ?>
                            <small class="text-muted">(<?php echo esc_html($role_display); ?>)</small>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <?php if ($dashboard_info['type'] === 'admin'): ?>
                                    <a class="dropdown-item" href="<?php echo esc_url(admin_url()); ?>">
                                        <i class="<?php echo esc_attr($dashboard_info['icon']); ?> me-2"></i><?php echo esc_html($dashboard_info['name']); ?>
                                    </a>
                                <?php else: ?>
                                    <a class="dropdown-item" href="<?php echo esc_url(home_url('/dashboard')); ?>">
                                        <i class="<?php echo esc_attr($dashboard_info['icon']); ?> me-2"></i><?php echo esc_html($dashboard_info['name']); ?>
                                    </a>
                                <?php endif; ?>
                            </li>
                            <?php if (current_user_can('manage_options')): ?>
                            <li>
                                <a class="dropdown-item" href="<?php echo esc_url(admin_url()); ?>">
                                    <i class="fas fa-tools me-2"></i>WordPress Admin
                                </a>
                            </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="<?php echo wp_logout_url(home_url()); ?>">
                                    <i class="fas fa-sign-out-alt me-2"></i>Sign Out
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?php echo esc_url(home_url('/signin')); ?>" class="btn btn-primary">Sign In</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div id="content" class="site-content">
    <?php if (is_user_logged_in()): ?>
    <script>
    // Global auth data for JavaScript functions
    var authData = {
        ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
        nonce: '<?php echo wp_create_nonce('auth-nonce'); ?>',
        logoutUrl: '<?php echo wp_logout_url(home_url()); ?>'
    };
    
    function refreshSession() {
        jQuery.post(ajaxurl, {
            action: 'refresh_session'
        });
    }
    
    // Refresh session function
    // Note: We've removed the JavaScript signOut function and are using direct WordPress logout URLs instead
    
    // Refresh session every 5 minutes
    setInterval(refreshSession, 300000);
    </script>
    <?php endif; ?>