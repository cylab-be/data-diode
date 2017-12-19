# Data Diode

## Installation

### Install a LAMP server

```bash
sudo apt install lamp-server^
sudo apt install php-pdo php-mbstring php-tokenizer php-xml
```
And depending on the DBMS you are planning to use 
```bash
sudo apt install php-mysql
#or
sudo apt install php-sqlite
```

If you are planning to use sqlite you need to create the database file
```bash
sudo touch /var/www/data-diode/laravel-test/database/database.sqlite
```

### Install composer

```bash
sudo apt install composer zip unzip
```

### Configure apache

Assuming you will install the data diode in the `/var/www/data-diode` directory, create a new file `/etc/apache2/sites-available/data-diode.conf` Which will contain :
```xml
<Directory /var/www/data-diode/laravel-test/public>
        AllowOverride All
</Directory>
<VirtualHost *:80>
        DocumentRoot /var/www/data-diode/laravel-test/public
</VirtualHost>
```
We can now enable the new configuration file
```bash
sudo a2ensite data-diode
```
And if you are not planning to use the default apache website
```bash
sudo a2dissite 000-default
```
You also need to have apache rewrite module enabled
```bash
sudo a2enmod rewrite
```

### Install the data diode

Go to `/var/www/` and clone the project.
```bash
sudo git clone https://github.com/RUCD/data-diode.git
```
Then install dependencies and configure it as you want.
```bash
cd data-diode/laravel-test
sudo composer install
sudo php artisan key:generate
sudo cp .env.example .env
vi .env
```
Once this is done change the ownership to apache
```bash
sudo chown -R www-data:www-data /var/www/data-diode
```
Finally restart apache
```bash
sudo systemctl restart apache2
```

### Authorize apache to use iptables

This project uses a script which is using iptables and iptables-persistent. So they need to be installed and apache needs to be able to run them as root.

```bash
sudo apt install iptables-persistent
```

Give apache the ability to use sudo
```bash
sudo visudo
```
and add

```
www-data ALL=NOPASSWD: /var/www/data-diode/laravel-test/app/Scripts/datadiode.sh
```

### Enable IP forwarding

To properly transfer packets, you need to enable IP forwarding.
```bash
vi /etc/sysctl.conf
```
Uncomment and/or set `ip_forwarding = 1`

Then enable NATing on iptables and save its state (as root)
```bash
iptables -t nat -A POSTROUTING -o eth0 -j MASQUERADE
iptables-save > /etc/iptables/rules.v4
ip6tables-save > /etc/iptables/rules.v6
```
Where eth0 is the output NIC

### Done

At this point the data diode should be working