# Data Diode

[![Build Status](https://travis-ci.org/RUCD/data-diode.svg?branch=master)](https://travis-ci.org/RUCD/data-diode)

Developement takes place at https://gitlab.cylab.be/cylab/data-diode

## APT-Mirror

Vagrantfile creates two machines: a machine for the apt-mirror and a machine in the "secure" server.

The file mirror.sh install all requirements to obtain a apt-mirror:

- Install apache2 and apt-mirror
- Link the apt dependencies folder to the /var/www/ubuntu folder
- Set a rule to update mirror every day
- Create a new file mirror.lists and add all dependencies for ubuntu 16.04 (+/- 200GB)
- Set the apache server to be the apt-mirror server and restart apache
- Add a line to crontab to compress all dependencies.

The goal is to have an apt-mirror in unsecure side of the data-diode. The dependencies will be transfer through the data diode (compressed archive) and used to create another apt-mirror in the secure side of the data-diode.

It is not possible to directly create a mirror in the secure side of the data diode because of the one-side flow.

The file mirror-secure.sh is for a machine in the secure network. This file configure Apt to use the apt-mirror in the secure side of the data diode.