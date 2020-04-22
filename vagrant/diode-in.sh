#!/bin/bash
export DEBIAN_FRONTEND=noninteractive
apt update
apt install -y apache2 php libapache2-mod-php php-pdo php-mbstring php-tokenizer php-xml composer zip unzip iptables-persistent php-sqlite3 python-pip python3-pip ntp
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

python3 -m pip install python-pypi-mirror
chmod +x /var/www/data-diode/src/app/Scripts/sendpip.sh

cat > /etc/ntp.conf << EOF
statistics loopstats peerstats clockstats
filegen loopstats file loopstats type day enable
filegen peerstats file peerstats type day enable
filegen clockstats file clockstats type day enable

pool 0.be.pool.ntp.org iburst
pool 1.be.pool.ntp.org iburst
pool 2.be.pool.ntp.org iburst
pool 3.be.pool.ntp.org iburst

# needed for adding pool entries
restrict source notrap nomodify noquery
EOF

cd /var/www/data-diode/src
chown -R www-data:www-data . ../BlindFTP_0.37 /etc/supervisord.conf /etc/ntp.conf ../fakeNTP

sed -i -e "s/#net.ipv4.ip_forward=1/net.ipv4.ip_forward=1/g" /etc/sysctl.conf
sysctl -p /etc/sysctl.conf
echo "www-data ALL=NOPASSWD: /bin/netstat, /bin/rm, /usr/bin/python, /var/www/data-diode/src/app/Scripts/*, /usr/local/bin/supervisord" | EDITOR="tee -a" visudo

systemctl restart apache2

(crontab -l 2>/dev/null; echo "*/10 * * * * $(which python3) /var/www/data-diode/fakeNTP/sntp-clie.py 2>&1") | crontab -

timedatectl set-timezone Europe/Brussels

# ftp, apt and pip added in db (diode in and diode out)
#python /var/www/data-diode/uploadersScripts/db_uploaders_clie.py add ftp 0 36016
python /var/www/data-diode/uploadersScripts/db_uploaders_clie.py add apt 0 36017
#python /var/www/data-diode/uploadersScripts/db_uploaders_clie.py add pip 0 36018

# Opera deb test
sudo -H -u www-data bash -c 'mkdir apt'
cd apt
sudo -H -u www-data bash -c 'wget -r -l 0 http://deb.opera.com/opera/' # as if root were www-data to give it the rights on deb.opera.com
cd ..
mv -v apt /var/www/data-diode/src/storage/app/files/
chown -R www-data:www-data /var/www/data-diode/src/storage/app/files/apt
python /var/www/data-diode/uploadersScripts/db_uploaders_clie.py update apt 1 # Python2.7
