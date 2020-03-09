#!/bin/bash
apt update
apt install -y zip unzip python-pip python3-pip lynx

python3 -m pip install --upgrade pip
python3 -m pip install python-pypi-mirror