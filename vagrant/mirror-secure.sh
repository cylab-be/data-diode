#!/bin/bash
sudo mv /etc/apt/sources.list /etc/apt/sources.list.bak
sudo echo "deb http://192.168.100.10/ubuntu/ xenial main restricted universe multiverse

#deb http://security.ubuntu.com/ubuntu xenial-security main restricted universe multiverse
deb http://192.168.100.10/ubuntu/ xenial-security main restricted universe multiverse

deb http://192.168.100.10/ubuntu/ xenial-updates main restricted universe multiverse
deb http://192.168.100.10/ubuntu/ xenial-backports main restricted universe multiverse
deb http://192.168.100.10/ubuntu/ xenial-proposed main restricted universe multiverse" >> /etc/apt/sources.list

#sudo apt-get update && sudo apt-get upgrade -y
