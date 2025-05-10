# AttenTrack Deployment Preparation Script
param(
    [Parameter(Mandatory=$true)]
    [string]$ProductionDomain,
    
    [Parameter(Mandatory=$true)]
    [string]$DbName,
    
    [Parameter(Mandatory=$true)]
    [string]$DbUser,
    
    [Parameter(Mandatory=$true)]
    [System.Security.SecureString]$DbPassword
)

# Create deployment directory
$deployDir = ".\deploy-package"
New-Item -ItemType Directory -Force -Path $deployDir

# Copy theme files
$themeDir = "$deployDir\attentrack"
New-Item -ItemType Directory -Force -Path $themeDir
Copy-Item "..\*.php" -Destination $themeDir
Copy-Item "..\style.css" -Destination $themeDir
Copy-Item "..\assets" -Destination "$themeDir\" -Recurse

# Convert secure password to plain text for config file
$BSTR = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($DbPassword)
$plainPassword = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($BSTR)

# Generate wp-config.php from template
$configContent = Get-Content ".\production-config-sample.php" -Raw
$configContent = $configContent.Replace("your_database_name", $DbName)
$configContent = $configContent.Replace("your_database_username", $DbUser)
$configContent = $configContent.Replace("your_strong_password", $plainPassword)
$configContent = $configContent.Replace("yourdomain.com", $ProductionDomain)

# Generate new salts from WordPress.org API
$salts = (Invoke-WebRequest -Uri 'https://api.wordpress.org/secret-key/1.1/salt/').Content
$configContent = $configContent -replace "(?ms)define\('AUTH_KEY'.*?NONCE_SALT.*?\);", $salts

Set-Content -Path "$deployDir\wp-config.php" -Value $configContent

# Create .htaccess with security rules
@'
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress

# Security Headers
<IfModule mod_headers.c>
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
Header set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
Header set Content-Security-Policy "default-src 'self' 'unsafe-inline' 'unsafe-eval' *.googleapis.com *.gstatic.com"
</IfModule>

# Protect wp-config.php
<Files wp-config.php>
Order deny,allow
Deny from all
</Files>

# Disable directory browsing
Options -Indexes

# Protect .htaccess
<Files .htaccess>
Order allow,deny
Deny from all
</Files>
'@ | Set-Content "$deployDir\.htaccess"

# Create deployment script
@'
#!/bin/bash

# Backup existing files
timestamp=$(date +%Y%m%d_%H%M%S)
mkdir -p backups/${timestamp}
cp -r /var/www/html/* backups/${timestamp}/

# Deploy new files
cp -r * /var/www/html/
chown -R www-data:www-data /var/www/html
find /var/www/html -type d -exec chmod 755 {} \;
find /var/www/html -type f -exec chmod 644 {} \;
chmod 600 /var/www/html/wp-config.php

# Clear cache
wp cache flush
wp rewrite flush

# Run database updates
wp core update-db

echo "Deployment completed successfully!"
'@ | Set-Content "$deployDir\deploy.sh"

# Create verification script
@'
<?php
// Verify deployment
$checks = array(
    "PHP Version" => version_compare(PHP_VERSION, "7.4.0", ">="),
    "MySQL Version" => version_compare(mysqli_get_client_info(), "5.7.0", ">="),
    "WordPress Version" => get_bloginfo("version") >= "5.0",
    "Theme Active" => get_template() === "attentrack",
    "SSL Active" => is_ssl(),
    "Database Tables" => array_reduce(
        array(
            "attentrack_patient_details",
            "attentrack_selective_results",
            "attentrack_divided_results",
            "attentrack_alternative_results",
            "attentrack_extended_results"
        ),
        function($carry, $table) {
            global $wpdb;
            $carry[$table] = $wpdb->get_var("SHOW TABLES LIKE '${wpdb->prefix}$table'") !== null;
            return $carry;
        },
        array()
    )
);

foreach ($checks as $check => $result) {
    if (is_array($result)) {
        echo "$check:\n";
        foreach ($result as $subcheck => $subresult) {
            echo "  $subcheck: " . ($subresult ? "✓" : "✗") . "\n";
        }
    } else {
        echo "$check: " . ($result ? "✓" : "✗") . "\n";
    }
}
'@ | Set-Content "$deployDir\verify.php"

# Create deployment package
Compress-Archive -Path "$deployDir\*" -DestinationPath "attentrack-deploy.zip" -Force

# Cleanup
Remove-Item -Path $deployDir -Recurse -Force

Write-Host "Deployment package created successfully: attentrack-deploy.zip"
Write-Host "Upload this package to your production server and run deploy.sh"
