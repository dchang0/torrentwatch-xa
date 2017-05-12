#!/bin/bash
# simple install script for torrentwatch-xa
# Most of this script requires sudo power.
# WARNING: This wipes out the old install, copying old config file to your home directory.
# It uses rm -fr, which can be quite dangerous. USE THIS SCRIPT AT YOUR OWN RISK!

# DOES NOT INSTALL PREREQUISITES

# back up the old config file
# requires sudo power
if [ -f /var/lib/torrentwatch-xa/config_cache/torrentwatch-xa.config ]
then
    sudo cp /var/lib/torrentwatch-xa/config_cache/torrentwatch-xa.config ~/torrentwatch-xa.config.bak
fi

# DOES NOT BACK UP THE DOWNLOAD CACHE!

# destroy the old installation
if [ -f /etc/cron.d/torrentwatch-xa-cron ]
then
    sudo rm /etc/cron.d/torrentwatch-xa-cron
fi
if [ -e /var/lib/torrentwatch-xa ]
then
    sudo rm -fr /var/lib/torrentwatch-xa
fi
if [ -e /var/www/html/torrentwatch-xa ]
then
    sudo rm -fr /var/www/html/torrentwatch-xa
fi

# copy in the new installation
sudo cp -R var/lib/torrentwatch-xa /var/lib
sudo chown -R www-data:www-data /var/lib/torrentwatch-xa/*_cache
sudo cp -R var/www/html/torrentwatch-xa /var/www/html
sudo cp etc/cron.d/torrentwatch-xa-cron /etc/cron.d
sudo chown root:root /etc/cron.d/torrentwatch-xa-cron

# DEFAULT CONFIG IS AUTOMATICALLY GENERATED ON FIRST RUN OF NEW INSTALL
