sed -i -e "s/#addhere/# begin blindftp-$1\n#addhere/g" /etc/supervisord.conf
sed -i -e "s/#addhere/[program:blindftp-$1]\n#addhere/g" /etc/supervisord.conf
sed -i -e "s/#addhere/command=sudo python \/var\/www\/data-diode\/BlindFTP_0.37\/bftp.py -s \/var\/www\/data-diode\/src\/storage\/app\/files\/$1 -a 192.168.101.2 -b -P 5 -p 36018 -u pip -x\n#addhere/g" /etc/supervisord.conf
sed -i -e "s/#addhere/autostart=true\n#addhere/g" /etc/supervisord.conf
sed -i -e "s/#addhere/autorestart=true\n#addhere/g" /etc/supervisord.conf
sed -i -e "s/#addhere/user=www-data\n#addhere/g" /etc/supervisord.conf
sed -i -e "s/#addhere/numprocs=1\n#addhere/g" /etc/supervisord.conf
sed -i -e "s/#addhere/redirect_stderr=true\n#addhere/g" /etc/supervisord.conf
sed -i -e "s/#addhere/stdout_logfile=\/var\/www\/data-diode\/src\/storage\/app\/bftp-diodein-$1.log\n#addhere/g" /etc/supervisord.conf
sed -i -e "s/#addhere/# end blindftp-$1\n#addhere/g" /etc/supervisord.conf
