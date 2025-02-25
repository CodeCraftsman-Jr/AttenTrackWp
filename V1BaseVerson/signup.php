<?php
$host = 'sql210.infinityfree.com';
$db = 'if0_37517154_user_management';
$user = 'if0_37517154';
$pass = '1slLMeZbqe7IyKy';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_type = 'user';

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, user_type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $password, $user_type);

    if ($stmt->execute()) {
        echo "Registration successful!";
        header("Location: login.html"); // Redirect to login page after successful signup
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}
?>
