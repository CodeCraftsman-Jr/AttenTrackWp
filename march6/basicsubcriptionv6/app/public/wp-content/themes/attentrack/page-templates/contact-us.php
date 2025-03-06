<?php
/**
 * Template Name: Contact Us Page
 * 
 * This is the template that displays the contact us page.
 */

get_header(); ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="mb-4">Contact Us</h1>
            
            <div class="card">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h3>Get in Touch</h3>
                            <p>We'd love to hear from you. Please use the contact information below or fill out the form.</p>
                            
                            <div class="contact-info mt-4">
                                <p><i class="fas fa-envelope me-2"></i> Email: contact@attentrack.com</p>
                                <p><i class="fas fa-phone me-2"></i> Phone: +1 (123) 456-7890</p>
                                <p><i class="fas fa-map-marker-alt me-2"></i> Address: 123 Main St, City, Country</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <form id="contact-form" method="post">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Send Message</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
