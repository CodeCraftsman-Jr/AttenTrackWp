<?php
session_start();

$host = 'sql210.infinityfree.com';
$db = 'if0_37517154_user_management';
$user = 'if0_37517154';
$pass = '1slLMeZbqe7IyKy';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['user'];
    $password = $_POST['password'];
    
    // Check if input is email or username
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $user, $user);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_type'] = $user['user_type'];
            
            if ($user['user_type'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: Home2.html");
            }
            exit();
        } else {
            
            header("Location: signin.html?error=Invalid password!");
        }
    } else {
        
        header("Location: signinerror.html?error=No user found with this username or email!");
    }
    $stmt->close();
    $conn->close();
}
?>
