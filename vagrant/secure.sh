#!/bin/bash
apt update
apt install -y zip unzip python-pip python3-pip lynx

pip3 install --upgrade pip
pip3 install python-pypi-mirror
# pip3 install --trusted-host 192.168.101.2 -i http://192.168.101.2:8000/simple package