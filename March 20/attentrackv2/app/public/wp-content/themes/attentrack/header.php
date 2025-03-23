<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="<?php echo esc_url(home_url('/')); ?>">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo.png" alt="AttenTrack" height="40">
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo esc_url(home_url('/')); ?>">Home</a>
                </li>
            </ul>
            
            <div class="d-flex align-items-center">
                <?php if (is_user_logged_in()): 
                    $current_user = wp_get_current_user();
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
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item" href="<?php echo esc_url(home_url('/dashboard')); ?>">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="signOut(); return false;">
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
    function refreshSession() {
        jQuery.post(authData.ajaxUrl, {
            action: 'refresh_session',
            _ajax_nonce: authData.nonce
        });
    }
    setInterval(refreshSession, 5 * 60 * 1000);
    </script>
    <?php endif; ?>