<?php
$servername = "sql210.infinityfree.com"; // Can change based on your hosting settings
$username = "if0_37517154"; // your database username
$password = "1slLMeZbqe7IyKy"; // your database password
$dbname = "if0_37517154_user_database"; // the database created above

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);
    
     if ($result->num_rows > 0) {
        // User found, retrieve the user data, including the test_id
        $user = $result->fetch_assoc();
        $testID = $user['test_id']; // Retrieve the test ID
        // Optionally, send this ID in a password reset email or show it for validation
        echo "Password recovery initiated for user with Test ID: " . $testID;
    } else {
        echo "No user found with this email!";
    }
}
$conn->close();
?>
