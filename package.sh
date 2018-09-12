#!/bin/bash

cwd=$(pwd)

rm -Rf /tmp/data-diode
rm -f /tmp/data-diode.zip

mkdir /tmp/data-diode
cp -Rf ./src /tmp/data-diode/src
cp setup-in.sh /tmp/data-diode
cp README.md /tmp/data-diode
cd /tmp/data-diode/src

## Remove unnecessary or generated content...
rm -Rf node_modules
rm storage/app/input
rm storage/app/output
rm storage/app/db.sqlite
rm storage/logs/laravel.log

composer install

cd /tmp
zip -r data-diode data-diode

cd $cwd
mv /tmp/data-diode.zip ./
