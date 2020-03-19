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

arp -s 192.168.102.1 08:00:27:23:9b:6f