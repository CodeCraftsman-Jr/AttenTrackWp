    <footer class="footer mt-5 py-4 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5>About AttenTrack</h5>
                <p>Helping individuals understand and improve their attention levels through advanced assessment tools.</p>
            </div>
            <div class="col-md-4">
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
            <div class="col-md-4">
                <h5>Contact Us</h5>
                <ul class="list-unstyled">
                    <li><i class="fas fa-envelope me-2"></i> contact@attentrack.com</li>
                    <li><i class="fas fa-phone me-2"></i> +1 (123) 456-7890</li>
                    <li><i class="fas fa-map-marker-alt me-2"></i> 123 Main St, City, Country</li>
                </ul>
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
