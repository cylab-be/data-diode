#!/bin/bash
export DEBIAN_FRONTEND=noninteractive
apt update
apt install -y apache2 php libapache2-mod-php php-pdo php-mbstring php-tokenizer php-xml composer zip unzip iptables-persistent php-sqlite3 python-pip python3-pip
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
# OLD:
# git clone https://github.com/RUCD/data-diode.git
# NEW:
rm -rf data-diode
        # BEGIN: Temporary usage of local files instead of repository
        # OLD: 
        # git clone https://github.com/RUCD/data-diode.git
        # cd data-diode
        # git checkout ftp-plugin
        # git pull
        # cd ..
        # NEW:
        mkdir data-diode
        cp -r /vagrant/* data-diode/
# END
cd data-diode/src
composer install
cp /vagrant/vagrant/env/.env.in .env
cp /vagrant/supervisor/supervisord.conf.in /etc/supervisord.conf
cp /vagrant/supervisor/supervisord /etc/init.d/supervisord
touch storage/app/db.sqlite
php artisan key:generate
php artisan migrate
php artisan config:reset
cp -r /vagrant/BlindFTP_0.37 ..

#update-alternatives --install /usr/bin/pip pip /usr/bin/pip3 1 # to make pip act as pip3 (better than a simple alias)
#pip install --upgrade pip
python3 -m pip install --upgrade pip
python3 -m pip install supervisor #pip install supervisor 

chmod +x /etc/init.d/supervisord
update-rc.d supervisord defaults
service supervisord stop
service supervisord start

cd storage/app/files/
rm -rf py-mirror
mkdir py-mirror
cd py-mirror
mkdir downloads
python3 -m pip install python-pypi-mirror
chmod +x /var/www/data-diode/src/app/Scripts/sendpip.sh

cd /var/www/data-diode/src
chown -R www-data:www-data . ../BlindFTP_0.37 /etc/supervisord.conf

sed -i -e "s/#net.ipv4.ip_forward=1/net.ipv4.ip_forward=1/g" /etc/sysctl.conf
sysctl -p /etc/sysctl.conf
echo "www-data ALL=NOPASSWD: /var/www/data-diode/src/app/Scripts/datadiode.sh, /var/www/data-diode/src/app/Scripts/sendpip.sh, /usr/local/bin/supervisord" | EDITOR="tee -a" visudo

systemctl restart apache2