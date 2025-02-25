<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<nav class="navbar navbar-expand-lg navbar-light bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo esc_url(home_url('/')); ?>">
            <?php
            if (has_custom_logo()) {
                the_custom_logo();
            } else {
                echo esc_html(get_bloginfo('name'));
            }
            ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'container' => false,
                'menu_class' => 'navbar-nav ms-auto',
                'fallback_cb' => false,
                'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                'depth' => 2,
                'walker' => new Bootstrap_Walker_Nav_Menu()
            ));
            ?>
            <?php if (!is_user_logged_in()) : ?>
                <div class="ms-auto">
                    <a href="<?php echo esc_url(wp_login_url()); ?>" class="btn log-but">Login</a>
                </div>
            <?php else : ?>
                <div class="ms-auto">
                    <a href="<?php echo esc_url(wp_logout_url()); ?>" class="btn log-but">Logout</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>
