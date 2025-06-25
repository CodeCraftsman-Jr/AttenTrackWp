<?php
// Quick Role Fix for Institution Owner
// This script directly updates the database to fix your role

// Database connection details (update these to match your setup)
$host = 'localhost';
$dbname = 'local';  // Your database name
$username = 'root'; // Your database username  
$password = 'root'; // Your database password

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Quick Role Fix for Institution Owner</h1>";
    
    // Your user ID (from the diagnostic)
    $user_id = 24;
    
    echo "<h2>Current Status</h2>";
    
    // Check current role
    $stmt = $pdo->prepare("SELECT meta_value FROM wp_usermeta WHERE user_id = ? AND meta_key = 'wp_capabilities'");
    $stmt->execute([$user_id]);
    $current_caps = $stmt->fetchColumn();
    
    echo "Current capabilities: " . htmlspecialchars($current_caps) . "<br>";
    
    // Fix the role
    echo "<h2>Fixing Role</h2>";
    
    // New capabilities for institution_admin role
    $new_capabilities = 'a:1:{s:16:"institution_admin";b:1;}';
    
    // Update the user's capabilities
    $stmt = $pdo->prepare("UPDATE wp_usermeta SET meta_value = ? WHERE user_id = ? AND meta_key = 'wp_capabilities'");
    $result = $stmt->execute([$new_capabilities, $user_id]);
    
    if ($result) {
        echo "✅ <strong>SUCCESS!</strong> Your role has been updated to 'institution_admin'<br>";
        
        // Verify the change
        $stmt = $pdo->prepare("SELECT meta_value FROM wp_usermeta WHERE user_id = ? AND meta_key = 'wp_capabilities'");
        $stmt->execute([$user_id]);
        $updated_caps = $stmt->fetchColumn();
        
        echo "Updated capabilities: " . htmlspecialchars($updated_caps) . "<br>";
        
        echo "<div style='background: #d4edda; padding: 20px; margin: 20px 0; border: 1px solid #c3e6cb; border-radius: 5px;'>";
        echo "<h3>✅ Role Fixed Successfully!</h3>";
        echo "<p><strong>Next steps:</strong></p>";
        echo "<ol>";
        echo "<li><strong>Log out completely</strong> from your WordPress account</li>";
        echo "<li><strong>Clear browser cache</strong> (Ctrl+F5 or Cmd+Shift+R)</li>";
        echo "<li><strong>Log back in</strong> to refresh your session</li>";
        echo "<li><strong>Navigate to:</strong> <a href='/dashboard?type=institution'>/dashboard?type=institution</a></li>";
        echo "<li><strong>Verify:</strong> You should now have full access to the institution dashboard</li>";
        echo "</ol>";
        echo "</div>";
        
    } else {
        echo "❌ <strong>ERROR:</strong> Failed to update role<br>";
    }
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 20px; margin: 20px 0; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<h3>❌ Database Connection Error</h3>";
    echo "<p>Could not connect to database. Please check your database credentials.</p>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Alternative solution:</strong> Contact your system administrator to manually change your role from 'subscriber' to 'institution_admin' in the WordPress admin panel.</p>";
    echo "</div>";
}

echo "<h2>Manual Alternative</h2>";
echo "<p>If this script doesn't work, you can ask an administrator to:</p>";
echo "<ol>";
echo "<li>Go to WordPress Admin → Users → All Users</li>";
echo "<li>Find user: <strong>vasanthan22td0728</strong> (ID: 24)</li>";
echo "<li>Edit the user</li>";
echo "<li>Change role from 'Subscriber' to 'Institution Admin'</li>";
echo "<li>Save changes</li>";
echo "</ol>";
?>
