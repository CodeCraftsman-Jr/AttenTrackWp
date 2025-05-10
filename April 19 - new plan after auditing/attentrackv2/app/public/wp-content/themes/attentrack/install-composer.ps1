# Download Composer installer
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
Invoke-WebRequest -Uri https://getcomposer.org/installer -OutFile composer-setup.php

# Install Composer
php composer-setup.php

# Remove installer
Remove-Item composer-setup.php

# Install dependencies
php composer.phar install
