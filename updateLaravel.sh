vagrant ssh diodein -c 'sudo chown -R vagrant:vagrant /var/www/data-diode/ && exit; /bin/bash'
vagrant scp ~/data-diode/src/app       diodein:/var/www/data-diode/src
vagrant scp ~/data-diode/src/routes    diodein:/var/www/data-diode/src
vagrant scp ~/data-diode/src/resources diodein:/var/www/data-diode/src
vagrant scp ~/data-diode/src/public    diodein:/var/www/data-diode/src
vagrant ssh diodein -c 'sudo chown -R www-data:www-data /var/www/data-diode/ && exit; /bin/bash'

vagrant ssh diodeout -c 'sudo chown -R vagrant:vagrant /var/www/data-diode/ && exit; /bin/bash'
vagrant scp ~/data-diode/src/app       diodeout:/var/www/data-diode/src
vagrant scp ~/data-diode/src/routes    diodeout:/var/www/data-diode/src
vagrant scp ~/data-diode/src/resources diodeout:/var/www/data-diode/src
vagrant scp ~/data-diode/src/public    diodeout:/var/www/data-diode/src
vagrant ssh diodeout -c 'sudo chown -R www-data:www-data /var/www/data-diode/ && exit; /bin/bash'
