# Data Diode

## Installation (Ubuntu Server 16.04)

Install all the required packages.
```bash
apt install -y apache2 php libapache2-mod-php php-pdo php-mbstring php-tokenizer php-xml composer zip unzip iptables-persistent php-sqlite3
```
Once everything is installed, you need to configure apache2.
First, disable the default site.
```bash
sudo a2dissite 000-default.conf
```
Then create a new configuration in `/etc/apache2/sites-available/data-diode.conf`.
```xml
<Directory /var/www/data-diode/src/public>
        AllowOverride All
</Directory>
<VirtualHost *:80>
        DocumentRoot /var/www/data-diode/src/public
</VirtualHost>
```
And enable it. You also need to have apache rewrite module enabled.
```bash
sudo a2ensite data-diode
sudo a2enmod rewrite
```

Now you need to clone the project from github.
```bash
cd /var/www
sudo git clone https://github.com/RUCD/data-diode.git
```

You now need to install required php libraries. To do so, simple use.
```bash
cd data-diode/src
sudo composer install
```
You need to create the file for the sqlite database.
```bash
sudo touch storage/app/db.sqlite
```
The configuration of the network interfaces is possible within the data-diode webUI or the configuration file. To make this work, you need to create links.
```bash
sudo ln -s /var/www/data-diode/src/storage/app/input /etc/network/interfaces.d/diode-input.cfg
sudo ln -s /var/www/data-diode/src/storage/app/output /etc/network/interfaces.d/diode-output.cfg
```

Generate a private key for the cookie encryption and initialize the database.
```bash
sudo php artisan key:generate
sudo php artisan migrate
```

As the user that runs apache is not root (by default), you know need to change the ownership of the files.
```bash
sudo chown -R www-data:www-data .
```

For the NAT functionalities to work properly, you need to enable ip forwarding.
```bash
sudo sed -i -e "s/#net.ipv4.ip_forward=1/net.ipv4.ip_forward=1/g" /etc/sysctl.conf
sudo sysctl -p /etc/sysctl.conf
```

You need to make www-data to be able to execute a script as root by adding the following line in sudoers file : `www-data ALL=NOPASSWD: /var/www/data-diode/src/app/Scripts/datadiode.sh`.
```bash
sudo visudo
```
Finally restart apache server.
```bash
systemctl restart apache2
```

## Configuration

You can start by copying the default configuration file `.env`.
```bash
cp .env.example .env
```
Then you simply need to edit values in that file. Once you finished editing the configuration you need to apply the configuration.
Be aware that this will override network configuration made in the webUI. This is useful in case the IP configuration is wrong and make the webUI unavailable.
```bash
php artisan config:reset
```
