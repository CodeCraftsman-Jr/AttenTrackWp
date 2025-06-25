#!/bin/bash

# AttenTrack Deployment Script
# Usage: ./deploy.sh <domain> <db_name> <db_user> <db_password>

if [ "$#" -ne 4 ]; then
    echo "Usage: $0 <domain> <db_name> <db_user> <db_password>"
    exit 1
fi

DOMAIN=$1
DB_NAME=$2
DB_USER=$3
DB_PASSWORD=$4
DEPLOY_DIR="/var/www/html"
BACKUP_DIR="/var/www/backups"

# Function to check command success
check_success() {
    if [ $? -ne 0 ]; then
        echo "Error: $1"
        exit 1
    fi
}

echo "Starting AttenTrack deployment..."

# 1. Create backup
echo "Creating backup..."
BACKUP_TIME=$(date +%Y%m%d_%H%M%S)
mkdir -p "$BACKUP_DIR/$BACKUP_TIME"
cp -r $DEPLOY_DIR/* "$BACKUP_DIR/$BACKUP_TIME/"
check_success "Backup failed"

# 2. Install system dependencies
echo "Installing dependencies..."
apt-get update
apt-get install -y php-curl php-gd php-mbstring php-xml php-zip
check_success "Dependencies installation failed"

# 3. Configure PHP
echo "Configuring PHP..."
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 64M/' /etc/php/7.4/apache2/php.ini
sed -i 's/post_max_size = .*/post_max_size = 64M/' /etc/php/7.4/apache2/php.ini
sed -i 's/memory_limit = .*/memory_limit = 256M/' /etc/php/7.4/apache2/php.ini
check_success "PHP configuration failed"

# 4. Deploy files
echo "Deploying files..."
unzip attentrack-deploy.zip -d $DEPLOY_DIR
check_success "File deployment failed"

# 5. Set permissions
echo "Setting permissions..."
chown -R www-data:www-data $DEPLOY_DIR
find $DEPLOY_DIR -type d -exec chmod 755 {} \;
find $DEPLOY_DIR -type f -exec chmod 644 {} \;
chmod 600 $DEPLOY_DIR/wp-config.php
check_success "Permission setting failed"

# 6. Configure Apache
echo "Configuring Apache..."
a2enmod rewrite headers ssl
check_success "Apache module enabling failed"

# 7. Install SSL certificate (using Let's Encrypt)
echo "Installing SSL certificate..."
certbot --apache -d $DOMAIN --non-interactive --agree-tos --email admin@$DOMAIN
check_success "SSL installation failed"

# 8. Configure database
echo "Configuring database..."
mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME"
mysql -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD'"
mysql -e "FLUSH PRIVILEGES"
check_success "Database configuration failed"

# 9. Install WP-CLI
echo "Installing WP-CLI..."
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
mv wp-cli.phar /usr/local/bin/wp
check_success "WP-CLI installation failed"

# 10. Configure WordPress
echo "Configuring WordPress..."
cd $DEPLOY_DIR
wp core update-db --allow-root
wp rewrite flush --allow-root
wp cache flush --allow-root
check_success "WordPress configuration failed"

# 11. Verify deployment
echo "Verifying deployment..."
php verify.php
check_success "Deployment verification failed"

# 12. Setup cron jobs for backup
echo "Setting up backup cron job..."
(crontab -l 2>/dev/null; echo "0 3 * * * mysqldump $DB_NAME > $BACKUP_DIR/db_backup_\$(date +\%Y\%m\%d).sql") | crontab -
check_success "Cron job setup failed"

echo "Deployment completed successfully!"
echo "Please check verify.php output for any warnings"
