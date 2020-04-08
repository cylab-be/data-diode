#!/bin/bash
export DEBIAN_FRONTEND=noninteractive
apt update
apt install -y apache2 php libapache2-mod-php php-pdo php-mbstring php-tokenizer php-xml composer zip unzip iptables-persistent php-sqlite3 python3-pip ntp
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
cp /vagrant/vagrant/env/.env.out .env
cp /vagrant/supervisor/supervisord.conf.out /etc/supervisord.conf
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

python3 -m pip install python-pypi-mirror

cat > /etc/ntp.conf << EOF
statistics loopstats peerstats clockstats
filegen loopstats file loopstats type day enable
filegen peerstats file peerstats type day enable
filegen clockstats file clockstats type day enable

server 127.127.1.0
fudge 127.127.1.0 stratum 1
EOF

chown -R www-data:www-data . ../BlindFTP_0.37 /etc/supervisord.conf /etc/ntp.conf ../fakeNTP
sed -i -e "s/#net.ipv4.ip_forward=1/net.ipv4.ip_forward=1/g" /etc/sysctl.conf
sysctl -p /etc/sysctl.conf
echo "www-data ALL=NOPASSWD: /bin/rm, /usr/bin/python, /usr/bin/python3, /var/www/data-diode/src/app/Scripts/datadiode.sh, /usr/local/bin/supervisord" | EDITOR="tee -a" visudo

# pip server
cat > /etc/apache2/sites-available/py-mirror.conf << EOF
<VirtualHost *:8000>
        DocumentRoot /var/www/data-diode/src/storage/app/files/pip
</VirtualHost>
EOF
sed -i '/Listen 8000/d' /etc/apache2/ports.conf # delete lines
sed -i '/Listen 80/a Listen 8000' /etc/apache2/ports.conf # add a line under an existing one
a2ensite py-mirror

# apt server
cat > /etc/apache2/sites-available/deb-mirror.conf << EOF
<VirtualHost *:8001>
        DocumentRoot /var/www/data-diode/src/storage/app/files/apt
</VirtualHost>
EOF
sed -i '/Listen 8001/d' /etc/apache2/ports.conf # delete lines
sed -i '/Listen 8000/a Listen 8001' /etc/apache2/ports.conf # add a line under an existing one
a2ensite deb-mirror

systemctl restart apache2

# ntp
systemctl stop time-sync.target systemd-timesyncd
timedatectl set-ntp false
timedatectl set-ntp true
timedatectl set-ntp false

(crontab -l 2>/dev/null; echo "@reboot sudo systemctl stop time-sync.target systemd-timesyncd") | crontab -
(crontab -l 2>/dev/null; echo "@reboot sudo timedatectl set-ntp false") | crontab -
(crontab -l 2>/dev/null; echo "@reboot sudo timedatectl set-ntp true") | crontab -
(crontab -l 2>/dev/null; echo "@reboot sudo timedatectl set-ntp false") | crontab -

systemctl restart ntp

#timedatectl set-timezone Europe/Brussels
