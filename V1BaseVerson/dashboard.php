<?php
session_start();

$servername = "sql210.infinityfree.com"; 
$username = "if0_37517154"; 
$password = "1slLMeZbqe7IyKy"; 
$dbname = "if0_37517154_user_database"; 

// Check if user is logged in (session exists)
if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');  // Redirect to sign-in page if not logged in
    exit();
}

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the user's details using the user_id from session
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = '$user_id'";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Fetch user data from the database
    $user = $result->fetch_assoc();
} else {
    echo "No user found!";
    exit();
}

// Get the test_id of the logged-in user from session or from DB
$test_id = isset($_SESSION['test_id']) ? $_SESSION['test_id'] : $user['test_id'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .dashboard-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .dashboard-header {
            text-align: center;
        }
        .dashboard-header h1 {
            font-size: 32px;
        }
        .user-info {
            text-align: center;
            margin-bottom: 30px;
        }
        .user-info img {
            border-radius: 50%;
            max-width: 150px;
            margin-bottom: 20px;
        }
        .user-info h3 {
            font-size: 24px;
            margin: 5px 0;
        }
        .user-info p {
            font-size: 18px;
            color: #555;
        }
        .test-id {
            background: #0f4b67;
            color: white;
            padding: 10px 20px;
            display: inline-block;
            margin-top: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .nav-links {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }
        .nav-links a {
            background: #3498db;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
        }
        .nav-links a:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Welcome to Your Dashboard</h1>
        </div>

        <div class="user-info">
            <!-- You can add user image -->
            <img src="https://via.placeholder.com/150" alt="User Profile Picture" />
            <h3>Hello, <?php echo htmlspecialchars($user['username']); ?>!</h3>
            <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
            <p>Test ID: <span class="test-id"><?php echo $test_id; ?></span></p>
        </div>

        <div class="nav-links">
            <a href="profile.php">View Profile</a>
            <a href="settings.php">Account Settings</a>
            <a href="logout.php">Log Out</a>
        </div>

    </div>

</body>
</html>

<?php
// Close connection
$conn->close();
?>
