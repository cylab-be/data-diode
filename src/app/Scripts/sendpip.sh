#!/bin/sh
cd
mkdir py-mirror
cd py-mirror
mkdir downloads
pypi-mirror download -d downloads/ -p /usr/local/bin/pip3 -b $1
pypi-mirror create -d downloads -m simple
cd ..
sudo chown -R www-data:www-data py-mirror
sudo cp -r py-mirror /var/www/data-diode/src/storage/app/files
sudo chown -R www-data:www-data /var/www/data-diode/src/storage/app/files/py-mirror
sudo rm -rf py-mirror