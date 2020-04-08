#!/bin/sh
mkdir pip
cd pip
mkdir pip
cd pip
mkdir downloads
pypi-mirror download -d downloads/ -p /usr/local/bin/pip3 -b $1
pypi-mirror create -d downloads -m simple

# Replace all symlinks by originals
# https://stackoverflow.com/questions/7167424/replace-all-symlinks-with-original
for link in $(find simple -type l)
do
  loc="$(dirname "$link")"
  dir="$(readlink -e "$link")"	# readlink "$link" gives the next link... readlink -e "$link" gives the final target of a chain of links.
  mv "$dir" "$loc"
  # rm "$link"			# not used because removes the original file that has just been moved.
done

cd ../..
sudo chown -R www-data:www-data pip
sudo cp -r pip /var/www/data-diode/src/storage/app/files
sudo chown -R www-data:www-data /var/www/data-diode/src/storage/app/files/pip
sudo rm -rf pip
sudo python /var/www/data-diode/uploadersScripts/db_uploaders_clie.py pip 1
