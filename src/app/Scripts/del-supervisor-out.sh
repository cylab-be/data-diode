sed -i "/^# begin blindftp-$1$/,/^# end blindftp-$1$/d" /etc/supervisord.conf
rm -rf "/var/www/data-diode/src/storage/app/files/$1"
supervisorctl update
sudo rm -rf "/var/www/data-diode/src/storage/app/bftp-diodeout-$1.log"
