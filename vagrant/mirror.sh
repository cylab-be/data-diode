#!/bin/bash
sudo apt-get update -y
sudo apt-get install apache2 apt-mirror -y
sudo ln -s /var/spool/apt-mirror/mirror/archive.ubuntu.com/ubuntu/ /var/www/ubuntu

sudo echo "@daily /usr/bin/apt-mirror" >> /etc/cron.d/apt-mirror

sudo mv /etc/apt/mirror.list /etc/apt/mirror.list.bak
sudo echo "############# config ##################
#
# set base_path    /var/spool/apt-mirror
#
# set mirror_path  $base_path/mirror
# set skel_path    $base_path/skel
# set var_path     $base_path/var
# set cleanscript $var_path/clean.sh
# set defaultarch  <running host architecture>
# set postmirror_script $var_path/postmirror.sh
# set run_postmirror 0
set nthreads     20
set _tilde 0
set limit_rate 1250k
#
############# end config ##############

# 16.04 mirroring
deb-amd64 http://archive.ubuntu.com/ubuntu xenial main main/debian-installer restricted restricted/debian-installer universe multiverse
deb-amd64 http://archive.ubuntu.com/ubuntu xenial-security main restricted universe multiverse
deb-amd64 http://archive.ubuntu.com/ubuntu xenial-updates main restricted universe multiverse
deb-amd64 http://archive.ubuntu.com/ubuntu xenial-proposed main restricted universe multiverse
deb-amd64 http://archive.ubuntu.com/ubuntu xenial-backports main restricted universe multiverse

deb-i386 http://archive.ubuntu.com/ubuntu xenial main main/debian-installer restricted restricted/debian-installer universe multiverse
deb-i386 http://archive.ubuntu.com/ubuntu xenial-security main restricted universe multiverse
deb-i386 http://archive.ubuntu.com/ubuntu xenial-updates main restricted universe multiverse
deb-i386 http://archive.ubuntu.com/ubuntu xenial-proposed main restricted universe multiverse
deb-i386 http://archive.ubuntu.com/ubuntu xenial-backports main restricted universe multiverse

deb-src http://archive.ubuntu.com/ubuntu xenial-security main restricted universe multiverse
deb-src http://archive.ubuntu.com/ubuntu xenial-updates main restricted universe multiverse
deb-src http://archive.ubuntu.com/ubuntu xenial-proposed main restricted universe multiverse
deb-src http://archive.ubuntu.com/ubuntu xenial-backports main restricted universe multiverse" >> /etc/apt/mirror.list

sudo echo '<VirtualHost *:80>
    ServerAdmin webmaster@hostname.com

    DocumentRoot /var/www
        <Directory /var/www/>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log

    # Possible values include: debug, info, notice, warn, error, crit,
    # alert, emerg.
    LogLevel warn

    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>' | sudo tee /etc/apache2/sites-enabled/000-default.conf

sudo rm -rf /var/www/html
sudo service apache2 restart

sudo echo "01 01 * * * tar -zcvf ~/mirror-dependencies.tar.gz /var/spool/apt-mirror/mirror/archive.ubuntu.com/ubuntu" >> /etc/crontab
