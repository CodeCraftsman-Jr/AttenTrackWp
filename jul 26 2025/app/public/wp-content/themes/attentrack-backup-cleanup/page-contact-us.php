<?php
/**
 * Template Name: Contact Us
 */

get_header();
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="text-primary mb-4">Contact Us</h1>
                    
                    <div class="row mb-5">
                        <div class="col-md-6 mb-4 mb-md-0">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-envelope text-primary fa-3x mb-3"></i>
                                    <h3 class="h5">Email Support</h3>
                                    <p class="mb-2">For general inquiries:</p>
                                    <a href="mailto:support@attentrack.com" class="text-decoration-none">
                                        support@attentrack.com
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-phone-alt text-primary fa-3x mb-3"></i>
                                    <h3 class="h5">Phone Support</h3>
                                    <p class="mb-2">Call us at:</p>
                                    <a href="tel:+1234567890" class="text-decoration-none">
                                        +1 (234) 567-890
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h2 class="h4 mb-4">Send us a Message</h2>
                            <form id="contactForm" action="" method="post">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Your Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                </div>
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-5">
                        <h2 class="h4 mb-4">Office Location</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h3 class="h5 mb-3">Main Office</h3>
                                        <address class="mb-0">
                                            123 Attention Street<br>
                                            Focus City, FC 12345<br>
                                            United States
                                        </address>
                                    </div>
                                    <div class="col-md-6">
                                        <h3 class="h5 mb-3">Business Hours</h3>
                                        <p class="mb-1">Monday - Friday: 9:00 AM - 6:00 PM</p>
                                        <p class="mb-0">Saturday - Sunday: Closed</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $subject = sanitize_text_field($_POST['subject']);
    $message = sanitize_textarea_field($_POST['message']);
    
    $to = 'support@attentrack.com';
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $headers[] = 'From: ' . $name . ' <' . $email . '>';
    
    $email_content = sprintf(
        'Name: %s<br>Email: %s<br>Subject: %s<br><br>Message:<br>%s',
        esc_html($name),
        esc_html($email),
        esc_html($subject),
        nl2br(esc_html($message))
    );
    
    $mail_sent = wp_mail($to, $subject, $email_content, $headers);
    
    if ($mail_sent) {
        echo '<script>alert("Thank you for your message. We will get back to you soon!");</script>';
    } else {
        echo '<script>alert("Sorry, there was an error sending your message. Please try again later.");</script>';
    }
}
?>

<?php get_footer(); ?>
