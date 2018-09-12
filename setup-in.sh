#!/bin/bash

if [ "$#" -ne 2 ]; then
  echo "Setup datadiode IN server"
  echo "Usage: setup-in.sh <INPUT interface> <OUTPUT interface>"
  echo "where <INPUT interface> is the interface visible to the outside world"
  echo "Example: setup-in.sh eno1 eno2"
  exit
fi

IF_INPUT=$1
IF_OUTPUT=$2

echo "Install datadiode IN"

export DEBIAN_FRONTEND=noninteractive
apt update

echo "> Install dependencies: Apache, PHP, iptables-persistent..."
apt install -y -q apache2 php libapache2-mod-php php-pdo php-mbstring php-tokenizer php-xml php-sqlite3 zip unzip iptables-persistent

echo "> Configure Apache..."
a2dissite 000-default.conf
cat > /etc/apache2/sites-available/data-diode.conf << EOF
<VirtualHost *:80>
    DocumentRoot /var/www/data-diode/public
    <Directory /var/www/data-diode/public>
        AllowOverride All
    </Directory>
</VirtualHost>
EOF
a2enmod rewrite
a2ensite data-diode

echo "> Install datadiode web interface in /var/www/data-diode ..."
rm -Rf /var/www/data-diode
cp -Rf ./src /var/www/data-diode
cp ./src/env.in /var/www/data-diode/.env

echo "INPUT_INTERFACE=$IF_INPUT" >> /var/www/data-diode/.env
echo "OUTPUT_INTERFACE=$IF_OUTPUT" >> /var/www/data-diode/.env

cd /var/www/data-diode
touch storage/app/db.sqlite
php artisan key:generate
php artisan migrate
chown -R www-data:www-data .

echo "www-data ALL=NOPASSWD: /var/www/data-diode/app/Scripts/datadiode.sh" | EDITOR="tee -a" visudo
systemctl restart apache2

echo "> Clear iptables configuration"
iptables -F
iptables -X
iptables -t nat -F
iptables -t nat -X
iptables -t mangle -F
iptables -t mangle -X
iptables -P INPUT ACCEPT
iptables -P FORWARD ACCEPT
iptables -P OUTPUT ACCEPT

echo "> Configure networking..."
cp /etc/network/interfaces /etc/network/interfaces.back
cat > /etc/network/interfaces << EOF
# interfaces(5) file used by ifup(8) and ifdown(8)
auto lo
iface lo inet loopback
EOF

rm /etc/network/interfaces.d/diode-input.cfg
rm /etc/network/interfaces.d/diode-output.cfg
ln -s /var/www/data-diode/storage/app/input /etc/network/interfaces.d/diode-input.cfg
ln -s /var/www/data-diode/storage/app/output /etc/network/interfaces.d/diode-output.cfg

php artisan config:reset

sed -i -e "s/#net.ipv4.ip_forward=1/net.ipv4.ip_forward=1/g" /etc/sysctl.conf
sysctl -p /etc/sysctl.conf

echo ""t
echo " =========================== "
echo " > Datadiode is READY!"
echo " > http://192.168.100.11"
echo " > login: admin@admin"
echo " > password: admin"
