<?php
session_start();

// Define the correct password
$correctPassword = "mypassword";

// Check if the password is already in session
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    // User is authenticated, show the content
    showContent();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission
    $userPassword = $_POST['password'] ?? '';
    if ($userPassword === $correctPassword) {
        $_SESSION['authenticated'] = true;
        header("Location: protected-page.php");
        exit();
    } else {
        $error = "Incorrect password. Try again.";
    }
} else {
    // Show password input form
    showPasswordForm(isset($error) ? $error : '');
}

// Function to display password input form
function showPasswordForm($error = '') {
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Password Protected Page</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-dark text-white d-flex justify-content-center align-items-center" style="height: 100vh;">
        <div class="text-center">
            <h3 class="mb-4">Enter Password</h3>
            <form action="" method="POST">
                <div class="mb-3">
                    <input type="password" name="password" class="form-control text-center" placeholder="Password" required>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
            <p class="text-danger mt-3">$error</p>
        </div>
    </body>
    </html>
    HTML;
    exit();
}

// Function to display the protected content
function showContent() {
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Sign Up</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background: url('https://static.vecteezy.com/system/resources/previews/006/712/955/non_2x/abstract-health-medical-science-consist-doctor-digital-wireframe-concept-modern-medical-technology-treatment-medicine-on-gray-background-for-template-web-design-or-presentation-vector.jpg') center/cover no-repeat;
                height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                color: white;
            }
            .signup-container {
                background: rgba(0, 0, 0, 0.25);
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
                color: #ffffff;
            }
            h3 {
                color: #ffcc00;
            }
            .form-label, .text-center a {
                color: #ffcc00;
            }
            .btn-primary {
                background-color: #ffcc00;
                border: none;
                color: #6f2c91;
            }
            .btn-primary:hover {
                background-color: #e6b800;
            }
        </style>
    </head>
    <body>
        <div class="signup-container">
            <h3 class="text-center mb-4">Sign Up</h3>
            <form action="signup.php" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Sign Up</button>
            </form>
            <div class="text-center mt-3">
                <p>Already have an account? <a href="signin.html">Sign In</a></p>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    HTML;
    exit();
}
?>
