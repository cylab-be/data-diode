#!/bin/bash
export DEBIAN_FRONTEND=noninteractive
apt update
apt install -y zip unzip python-pip python3-pip lynx ntp

python3 -m pip install --upgrade pip
python3 -m pip install python-pypi-mirror

cat > /etc/ntp.conf << EOF
tinker panic 0

statistics loopstats peerstats clockstats
filegen loopstats file loopstats type day enable
filegen peerstats file peerstats type day enable
filegen clockstats file clockstats type day enable

pool 192.168.102.1 iburst
# Needed for adding pool entries
restrict source notrap nomodify noquery
EOF

systemctl stop time-sync.target systemd-timesyncd
timedatectl set-ntp false
timedatectl set-ntp true
timedatectl set-ntp false

(crontab -l 2>/dev/null; echo "@reboot sudo systemctl stop time-sync.target systemd-timesyncd") | crontab -
(crontab -l 2>/dev/null; echo "@reboot sudo timedatectl set-ntp false") | crontab -
(crontab -l 2>/dev/null; echo "@reboot sudo timedatectl set-ntp true") | crontab -
(crontab -l 2>/dev/null; echo "@reboot sudo timedatectl set-ntp false") | crontab -

systemctl restart ntp

# Uniquement à mettre si parfois ce client met plus d'une heure avant la synchro
# (crontab -l 2>/dev/null; echo "@hourly sudo systemctl restart ntp") | crontab -

arp -s 192.168.102.1 00:aa:bb:bb:aa:00 # pour que le client trouve le serveur NTP

#timedatectl set-timezone Europe/Brussels

# Opera deb test
apt install -y libfontconfig1 libgstreamer-plugins-base0.10-0 libgstreamer0.10-0 libice6 libsm6 libxrender1 gstreamer0.10-plugins-good fonts-liberation ttf-liberation ttf-mscorefonts-installer flashplugin-nonfree cups-client # needed to install opera
#cat > /etc/apt/sources.list << EOF
#deb [trusted=yes] http://192.168.101.2:8001/deb.opera.com/opera stable non-free
#EOF
#sudo wget -O - http://deb.opera.com/archive.key | sudo apt-key add -
#apt update
