sudo rm "/etc/apache2/sites-available/py-mirror-$1.conf"
sed -i "/Listen $2/d" /etc/apache2/ports.conf # delete lines
sudo service apache2 reload