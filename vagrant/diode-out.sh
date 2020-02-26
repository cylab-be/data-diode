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
# BEGIN: Temporary removal and checkout to ftp-plugin until it is merged to master
rm -rf data-diode
git clone https://github.com/RUCD/data-diode.git
cd data-diode
git checkout ftp-plugin
git pull
cd ..
# END
cd data-diode/src
composer install
cp /vagrant/vagrant/env/.env.out .env
touch storage/app/db.sqlite
php artisan key:generate
php artisan migrate
php artisan config:reset
cp -r /vagrant/BlindFTP_0.37 ..
php artisan bftp:restart
php artisan queue:checkup # launch the queue for the ftp server job -> before the chown because it creates /var/www/data-diode/src/storage/app/queue.pid
chown -R www-data:www-data . ../BlindFTP_0.37
sed -i -e "s/#net.ipv4.ip_forward=1/net.ipv4.ip_forward=1/g" /etc/sysctl.conf
sysctl -p /etc/sysctl.conf
echo "www-data ALL=NOPASSWD: /var/www/data-diode/src/app/Scripts/datadiode.sh, /var/www/data-diode/BlindFTP_0.37, /bin/kill, /var/www/data-diode/src/artisan" | EDITOR="tee -a" visudo
systemctl restart apache2