<?php
/*
Template Name: Contact Us Template
*/

get_header();
?>

<style>
    /* Basic Styling */
    body {
        font-family: 'Roboto', sans-serif;
        background: linear-gradient(135deg, #ff9a8b, #ff6a88, #ff517b);
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
        padding: 20px;
        animation: fadeIn 1.5s ease-in-out;
    }

    h2 {
        text-align: center;
        color: #fff;
        font-size: 32px;
        margin-bottom: 20px;
        animation: slideIn 1s ease-out;
    }

    .contact-form {
        max-width: 600px;
        width: 100%;
        padding: 40px;
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0px 12px 30px rgba(0, 0, 0, 0.1);
        transition: box-shadow 0.3s ease-in-out;
        animation: bounceInUp 1s ease-out;
    }

    .contact-form:hover {
        box-shadow: 0px 15px 40px rgba(0, 0, 0, 0.2);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        font-size: 16px;
        color: #333;
        margin-bottom: 5px;
        display: block;
        animation: fadeInLabel 1s ease-out;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 12px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 8px;
        box-sizing: border-box;
        outline: none;
        transition: border 0.3s ease-in-out;
        animation: fadeInInput 1s ease-out;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        border-color: #ff517b;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 150px;
    }

    .form-group button {
        background-color: #ff517b;
        color: white;
        border: none;
        padding: 12px 25px;
        font-size: 16px;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease-in-out;
        animation: fadeInButton 1s ease-out;
    }

    .form-group button:hover {
        background-color: #ff6a88;
    }

    .form-group button:active {
        background-color: #ff9a8b;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .contact-form {
            padding: 20px;
        }

        .form-group button {
            width: 100%;
        }
    }

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideIn {
        from {
            transform: translateY(-30px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @keyframes bounceInUp {
        from {
            transform: translateY(50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @keyframes fadeInLabel {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes fadeInInput {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes fadeInButton {
        from { opacity: 0; }
        to { opacity: 1; }
    }
</style>

<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">

<div class="content">
    <div>
        <h2>Contact Us</h2>

        <div class="contact-form">
            <form id="contactForm">
                <div class="form-group">
                    <label for="name">Your Name:</label>
                    <input type="text" id="name" name="name" placeholder="Your Name" required>
                </div>
                <div class="form-group">
                    <label for="email">Your Email:</label>
                    <input type="email" id="email" name="email" placeholder="Your Email" required>
                </div>
                <div class="form-group">
                    <label for="message">Your Message:</label>
                    <textarea id="message" name="message" placeholder="Your Message" required></textarea>
                </div>
                <div class="form-group">
                    <button type="submit">Send Message</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    // Initialize EmailJS with your Public Key
    (function(){
        emailjs.init("bDpL6FxFx3SRUNLzm"); // Replace with your Public Key
    })();

    // Handle form submission
    document.getElementById('contactForm').addEventListener('submit', function(event) {
        event.preventDefault();
        
        // Get form data
        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        const message = document.getElementById('message').value;

        // Send email using EmailJS
        emailjs.send("service_id", "template_id", {
            from_name: name,
            from_email: email,
            message: message,
        }).then(
            function(response) {
                alert("Message sent successfully!");
                document.getElementById('contactForm').reset();
            },
            function(error) {
                alert("Failed to send message. Please try again later.");
            }
        );
    });
</script>

<?php
get_footer();
?>
