<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <title><?php wp_title('|', true, 'right'); ?><?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
    
<!-- Bootstrap Navbar -->
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand" href="<?php echo esc_url(home_url('/')); ?>">
            <?php if (has_custom_logo()): ?>
                <?php the_custom_logo(); ?>
            <?php else: ?>
                <img src="<?php echo site_url(); ?>/wp-content/includes/images/logo.jpg" alt="<?php bloginfo('name'); ?>">
            <?php endif; ?>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <?php
            if (has_nav_menu('primary')) {
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'navbar-nav me-auto mb-2 mb-lg-0',
                    'fallback_cb'    => '__return_false',
                    'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                    'walker'         => new Bootstrap_Walker_Nav_Menu()
                ));
            } else {
                ?>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link <?php echo is_front_page() ? 'active' : ''; ?>" href="<?php echo esc_url(home_url('/')); ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo is_page('about-app') ? 'active' : ''; ?>" href="<?php echo esc_url(get_permalink(get_page_by_path('about-app'))); ?>">About App</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://svcet.ac.in/" target="_blank">About us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo is_page('contact-us') ? 'active' : ''; ?>" href="<?php echo esc_url(get_permalink(get_page_by_path('contact-us'))); ?>">Contact us</a>
                    </li>
                </ul>
                <?php
            }
            ?>
            <div class="d-flex align-items-center">
                <?php if (is_user_logged_in()): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo wp_get_current_user()->display_name; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?php echo esc_url(home_url('/dashboard')); ?>">Dashboard</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo wp_logout_url(home_url()); ?>">Sign Out</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?php echo esc_url(home_url('/sign-in')); ?>" class="btn btn-primary">Sign In</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>