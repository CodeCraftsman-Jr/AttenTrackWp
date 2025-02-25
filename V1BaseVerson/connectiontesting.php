<?php
$host = 'sql210.infinityfree.com';
$db = 'if0_37517154_user_management';
$user = 'if0_37517154';
$pass = '1slLMeZbqe7IyKy';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Connected successfully!";
}
?>
