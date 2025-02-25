<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php wp_title('|', true, 'right'); ?><?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
    <!-- Add FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
    
<!-- Bootstrap Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-body-tertiary" style="margin: -5px;">
    <div class="container-fluid">
        <?php if (has_custom_logo()): ?>
            <?php the_custom_logo(); ?>
        <?php else: ?>
            <img src="<?php echo get_theme_file_uri('assets/images/logo.jpg'); ?>" width="85px" alt="<?php bloginfo('name'); ?>">
        <?php endif; ?>
        
        <a class="navbar-brand logo" href="<?php echo esc_url(home_url('/')); ?>" style="font-size: 38px;">
            <?php bloginfo('name'); ?>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <?php
            if (has_nav_menu('primary')) {
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'navbar-nav',
                    'fallback_cb'    => false,
                    'depth'          => 2,
                    'walker'         => new Bootstrap_Walker_Nav_Menu()
                ));
            } else {
                // Fallback menu
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
        </div>
    </div>
</nav>