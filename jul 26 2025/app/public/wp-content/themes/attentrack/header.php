<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <style>
    /* Override CSS Variables */
    :root {
        --primary-color: #667eea !important;
        --primary-hover: #5a67d8 !important;
        --secondary-color: #764ba2 !important;
        --accent-color: #f093fb !important;
        --hero-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%) !important;
    }

    /* Force new design styles */
    .hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%) !important;
        position: relative !important;
        overflow: hidden !important;
        min-height: 100vh !important;
        display: flex !important;
        align-items: center !important;
    }

    .hero-section::before {
        content: '' !important;
        position: absolute !important;
        top: -50% !important;
        right: -20% !important;
        width: 80% !important;
        height: 150% !important;
        background: linear-gradient(45deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%) !important;
        border-radius: 50% !important;
        transform: rotate(-15deg) !important;
        z-index: 1 !important;
    }

    .hero-section::after {
        content: '' !important;
        position: absolute !important;
        bottom: -30% !important;
        left: -10% !important;
        width: 60% !important;
        height: 100% !important;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.03) 100%) !important;
        border-radius: 50% !important;
        transform: rotate(25deg) !important;
        z-index: 1 !important;
    }

    .hero-section .container {
        position: relative !important;
        z-index: 10 !important;
    }

    body {
        background: linear-gradient(180deg, #f0f4f8 0%, #e2e8f0 100%) !important;
    }

    /* Assessment Section Styling */
    .assessment-section {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(240, 147, 251, 0.05) 100%) !important;
        position: relative !important;
        overflow: hidden !important;
    }

    .assessment-section::before {
        content: '' !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        background: radial-gradient(circle at 20% 80%, rgba(102, 126, 234, 0.1) 0%, transparent 50%),
                    radial-gradient(circle at 80% 20%, rgba(240, 147, 251, 0.1) 0%, transparent 50%) !important;
        z-index: 1 !important;
    }

    .assessment-section .container {
        position: relative !important;
        z-index: 2 !important;
    }

    .assessment-content {
        background: rgba(255, 255, 255, 0.9) !important;
        backdrop-filter: blur(10px) !important;
        border-radius: 20px !important;
        padding: 2.5rem !important;
        box-shadow: 0 20px 40px rgba(102, 126, 234, 0.1) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
    }

    .cognitive-assessment-card {
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(15px) !important;
        border-radius: 20px !important;
        padding: 2rem !important;
        box-shadow: 0 25px 50px rgba(118, 75, 162, 0.15) !important;
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
        transform: translateY(0) !important;
        transition: transform 0.3s ease !important;
    }

    .cognitive-assessment-card:hover {
        transform: translateY(-5px) !important;
    }

    /* Feature Items Styling */
    .feature-item {
        background: rgba(255, 255, 255, 0.6) !important;
        border-radius: 15px !important;
        padding: 1.5rem !important;
        margin-bottom: 1rem !important;
        border: 1px solid rgba(102, 126, 234, 0.1) !important;
        transition: all 0.3s ease !important;
    }

    .feature-item:hover {
        background: rgba(255, 255, 255, 0.8) !important;
        transform: translateX(10px) !important;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.1) !important;
    }

    .feature-icon {
        background: linear-gradient(135deg, #667eea, #764ba2) !important;
        color: white !important;
        width: 50px !important;
        height: 50px !important;
        border-radius: 15px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 1.2rem !important;
    }

    /* Button Styling */
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        border: none !important;
        border-radius: 15px !important;
        padding: 1rem 2rem !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        transition: all 0.3s ease !important;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3) !important;
    }

    .btn-primary:hover {
        transform: translateY(-3px) !important;
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4) !important;
    }

    .btn-outline-primary {
        border: 2px solid #667eea !important;
        color: #667eea !important;
        background: rgba(255, 255, 255, 0.9) !important;
        border-radius: 15px !important;
        padding: 1rem 2rem !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
    }

    .btn-outline-primary:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        color: white !important;
        transform: translateY(-3px) !important;
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.3) !important;
    }

    /* Progress Bar Styling */
    .progress {
        background: rgba(102, 126, 234, 0.1) !important;
        border-radius: 10px !important;
        overflow: hidden !important;
    }

    .progress-bar {
        background: linear-gradient(90deg, #667eea 0%, #764ba2 50%, #f093fb 100%) !important;
        border-radius: 10px !important;
        transition: width 0.6s ease !important;
    }

    /* Assessment Icon Styling */
    .assessment-icon i {
        background: linear-gradient(135deg, #667eea, #f093fb) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        background-clip: text !important;
        filter: drop-shadow(0 4px 8px rgba(102, 126, 234, 0.3)) !important;
    }

    /* Section Badge Styling */
    .section-badge .badge {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(240, 147, 251, 0.1)) !important;
        color: #667eea !important;
        border: 1px solid rgba(102, 126, 234, 0.2) !important;
        font-weight: 600 !important;
        padding: 0.75rem 1.5rem !important;
    }

    /* Ensure hero section is visible */
    section.hero-section,
    .hero-section,
    #hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%) !important;
        min-height: 100vh !important;
        display: flex !important;
        align-items: center !important;
        position: relative !important;
        overflow: hidden !important;
    }

    .navbar {
        background: rgba(248, 250, 252, 0.95) !important;
        padding: 1rem 0 !important;
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
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/attentrack-logo.svg" alt="AttenTrack" height="40">
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
                    <a href="<?php echo esc_url(home_url('/signin')); ?>" class="btn btn-primary rounded-pill px-4">Sign In</a>
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