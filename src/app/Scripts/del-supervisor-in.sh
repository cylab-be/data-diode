sed -i "/^# begin blindftp-$1$/,/^# end blindftp-$1$/d" /etc/supervisord.conf
rm -rf "/var/www/data-diode/src/storage/app/files/$1"
supervisorctl update
python /var/www/data-diode/uploadersScripts/db_uploaders_clie.py remove $1
sudo rm -rf "/var/www/data-diode/src/storage/app/bftp-diodein-$1.log"
