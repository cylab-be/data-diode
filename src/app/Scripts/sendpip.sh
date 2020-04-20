#!/bin/sh
mkdir $2
cd $2
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

cd ..
sudo chown -R www-data:www-data $2
sudo cp -r $2 /var/www/data-diode/src/storage/app/files
sudo chown -R "www-data:www-data /var/www/data-diode/src/storage/app/files/$2"
sudo rm -rf $2
sudo python /var/www/data-diode/uploadersScripts/db_uploaders_clie.py update $2 1
