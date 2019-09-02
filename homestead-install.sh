#!/bin/bash
######
# Helper script to simplify a clean homestead installation.
######

# xdebug installation
apt-get install php-xdebug
cp ./vagrant/xdebug/xdebug.ini /etc/php/7.3/mods-available/xdebug.ini



