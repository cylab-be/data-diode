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

# Ne fonctionne que si fait à la main sur la machine...
systemctl stop time-sync.target systemd-timesyncd
timedatectl set-ntp false
timedatectl set-ntp true
timedatectl set-ntp false

systemctl restart ntp

# Uniquement à mettre si parfois ce client met plus d'une heure avant la synchro
(crontab -l 2>/dev/null; echo "@hourly sudo systemctl restart ntp") | crontab -
