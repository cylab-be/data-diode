sudo sh -c "cat > /etc/apache2/sites-available/py-mirror-$1.conf << EOF
<VirtualHost *:$2>
        DocumentRoot /var/www/data-diode/src/storage/app/files/$1
</VirtualHost>
EOF"
sed -i "/Listen $2/d" /etc/apache2/ports.conf # delete lines
sed -i "/Listen 80/a Listen $2" /etc/apache2/ports.conf # add a line under an existing one
sudo a2ensite "py-mirror-$1"
sudo service apache2 reload

