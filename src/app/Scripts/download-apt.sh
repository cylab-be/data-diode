#!/bin/bash
cd "/var/www/data-diode/src/storage/app/files/$1"
sudo -H -u www-data bash -c "wget -q -r -l 0 $2" & # as if root were www-data to give it the rights on $2

while [ true ]; do
    if [[ $(ps -ef | grep wget | grep $2) ]]; then
        python /var/www/data-diode/uploadersScripts/db_uploaders_clie.py update $1 1 # Python2.7
        sleep 5s # time that must be smaller than the front-end channelUpdate interval time in main.blade.php
    else
         break
    fi
done