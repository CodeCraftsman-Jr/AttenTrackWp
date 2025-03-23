    <footer class="footer mt-5 py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5>About AttenTrack</h5>
                <p>Helping individuals understand and improve their attention levels through advanced assessment tools.</p>
            </div>
            <div class="col-md-6">
                <h5>Quick Links</h5>
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer',
                    'container'      => false,
                    'menu_class'     => 'list-unstyled',
                    'fallback_cb'    => false
                ));
                ?>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12 text-center">
                <hr>
                <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
