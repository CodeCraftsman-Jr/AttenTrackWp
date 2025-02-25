    <footer class="bg-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><?php echo esc_html(get_bloginfo('name')); ?></h5>
                    <p><?php echo esc_html(get_bloginfo('description')); ?></p>
                </div>
                <div class="col-md-4">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'footer',
                        'container' => false,
                        'menu_class' => 'list-unstyled',
                        'fallback_cb' => false
                    ));
                    ?>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-envelope me-2"></i> contact@attentrack.com</li>
                        <li><i class="fas fa-phone me-2"></i> (555) 123-4567</li>
                    </ul>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 text-center">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo esc_html(get_bloginfo('name')); ?>. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
    <?php wp_footer(); ?>
</body>
</html>
