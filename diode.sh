#!/bin/bash
export DEBIAN_FRONTEND=noninteractive
apt update
apt install -y apache2 php libapache2-mod-php php-pdo php-mbstring php-tokenizer php-xml composer zip unzip iptables-persistent php-sqlite3
a2dissite 000-default.conf
cat > /etc/apache2/sites-available/data-diode.conf << EOF
<Directory /var/www/data-diode/src/public>
        AllowOverride All
</Directory>
<VirtualHost *:80>
        DocumentRoot /var/www/data-diode/src/public
</VirtualHost>
EOF
a2enmod rewrite
a2ensite data-diode
cd /var/www
git clone https://github.com/RUCD/data-diode.git
cd data-diode/src
composer install
cp .env.example .env
touch storage/app/db.sqlite
php artisan key:generate
php artisan migrate
php artisan config:refresh
chown -R www-data:www-data .
sed -i -e "s/#net.ipv4.ip_forward=1/net.ipv4.ip_forward=1/g" /etc/sysctl.conf
sysctl -p /etc/sysctl.conf
echo "www-data ALL=NOPASSWD: /var/www/data-diode/src/app/Scripts/datadiode.sh" | EDITOR="tee -a" visudo
systemctl restart apache2
