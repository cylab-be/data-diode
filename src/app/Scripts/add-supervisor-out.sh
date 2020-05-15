# Parameters:
#
# $1 = uploader's name
# $2 = uploader's port
#
# Structure:
#
# add config
# if not failed:
#     add folder
#     if not failed:
#         update supervisorctl
#         if not failed:
#             echo success
#         else:
#             remove config
#             remove folder
#             echo update failed
#     else:
#         remove config
#         echo folder failed
# else:
#     echo config failed

{
sed -i -e "s/#addhere/# begin blindftp-$1\n#addhere/g" /etc/supervisord.conf
sed -i -e "s/#addhere/[program:blindftp-$1]\n#addhere/g" /etc/supervisord.conf
sed -i -e "s/#addhere/command=sudo python \/var\/www\/data-diode\/BlindFTP_0.37\/bftp.py -r \/var\/www\/data-diode\/src\/storage\/app\/files\/$1 -a $3 -p $2\n#addhere/g" /etc/supervisord.conf
sed -i -e "s/#addhere/autostart=true\n#addhere/g" /etc/supervisord.conf
sed -i -e "s/#addhere/autorestart=true\n#addhere/g" /etc/supervisord.conf
sed -i -e "s/#addhere/user=www-data\n#addhere/g" /etc/supervisord.conf
sed -i -e "s/#addhere/numprocs=1\n#addhere/g" /etc/supervisord.conf
sed -i -e "s/#addhere/redirect_stderr=true\n#addhere/g" /etc/supervisord.conf
sed -i -e "s/#addhere/stdout_logfile=\/var\/www\/data-diode\/src\/storage\/app\/bftp-diodeout-$1.log\n#addhere/g" /etc/supervisord.conf
sed -i -e "s/#addhere/# end blindftp-$1\n#addhere/g" /etc/supervisord.conf
} &> /dev/null
if [ $? -eq 0 ]; then
    mkdir "/var/www/data-diode/src/storage/app/files/$1" &> /dev/null
    # the following sudo is necessary otherwise the owner will remain root
    sudo chown -R www-data:www-data "/var/www/data-diode/src/storage/app/files/$1" &> /dev/null
    if [ $? -eq 0 ]; then
	supervisorctl update &> /dev/null
        if [ $? -eq 0 ]; then
	    echo "The $1's channel has successfully been added."
	else
	    sed -i "/^# begin blindftp-$1$/,/^# end blindftp-$1$/d" /etc/supervisord.conf &> /dev/null
            rmdir "/var/www/data-diode/src/storage/app/files/$1" &> /dev/null
            echo "The $1's channel could not have been added. An error occured while updating from the configuration."
	fi
    else
        sed -i "/^# begin blindftp-$1$/,/^# end blindftp-$1$/d" /etc/supervisord.conf &> /dev/null
        echo "The $1's channel could not have been added. An error occured while adding its folder."
    fi
else
    echo "The $1's channel could not have been added. An error occured while adding its configuration."
fi
