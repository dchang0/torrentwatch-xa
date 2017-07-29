#!/bin/bash
# simple install script for torrentwatch-xa
# Most of this script requires sudo power.
# WARNING: This wipes out the old install, copying old config file to your home directory.
# It uses rm -fr, which can be quite dangerous. USE THIS SCRIPT AT YOUR OWN RISK!

# DOES NOT INSTALL PREREQUISITES
# REMEMBER TO RESTART APACHE2 AFTER INSTALLING PRE-REQUISITES

while [[ $# -gt 0 ]]; do
    key="$1"
    case "$key" in
        -k|--keep-config)
        KEEPCONFIG=1
        ;;
        -h|--help)
        HELP=1
        ;;
        *)
        # extra, unknown options
        echo "Unknown option '$key'"
        ;;
    esac
    # Shift after checking all the cases to get the next option
    shift
done

if [[ $HELP == 1 ]]
then
    echo "installtwxa.sh {--keep-config}"
    exit
fi

# make sure new torrentwatch-xa package is accessible
if [ ! -e var/lib/torrentwatch-xa/twxacli.php ]
then
    echo "Cannot find new torrentwatch-xa lib tree to install; exiting."
    exit
fi
if [ ! -e var/www/html/torrentwatch-xa/torrentwatch-xa.php ]
then
    echo "Cannot find new torrentwatch-xa www tree to install; exiting."
    exit
fi
if [ ! -e etc/cron.d/torrentwatch-xa-cron ]
then
    echo "Cannot find new torrentwatch-xa cron job to install; exiting."
    exit
fi

# back up the old config file
# requires sudo power
if [ -f /var/lib/torrentwatch-xa/config_cache/torrentwatch-xa.config ]
then
    sudo cp /var/lib/torrentwatch-xa/config_cache/torrentwatch-xa.config ~/torrentwatch-xa.config.bak
    if [ ! -e ~/torrentwatch-xa.config.bak ]
    then
        echo "Backup of config file to ~/torrentwatch-xa.config.bak failed; exiting."
        exit
    fi
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
# try to chown the cache directories using Debian/Ubuntu default Apache user and group www-data
sudo chown -R www-data:www-data /var/lib/torrentwatch-xa/*_cache
if [ $? -ne 0 ]
then
    # try to chown the cache directories using Fedora default Apache user and group apache
  sudo chown -R apache:apache /var/lib/torrentwatch-xa/*_cache
fi
sudo cp -R var/www/html/torrentwatch-xa /var/www/html
sudo cp etc/cron.d/torrentwatch-xa-cron /etc/cron.d
sudo chown root:root /etc/cron.d/torrentwatch-xa-cron

# copy in the old config file
if [[ $KEEPCONFIG == 1 ]]
then
    sudo cp ~/torrentwatch-xa.config.bak /var/lib/torrentwatch-xa/config_cache/torrentwatch-xa.config
    sudo chown www-data:www-data /var/lib/torrentwatch-xa/config_cache/torrentwatch-xa.config
    if [ $? -ne 0 ]
    then
        # try to chown the config file using Fedora default Apache user and group apache
      sudo chown apache:apache /var/lib/torrentwatch-xa/config_cache/torrentwatch-xa.config
    fi
    sudo chmod 644 /var/lib/torrentwatch-xa/config_cache/torrentwatch-xa.config
fi

# RedHat/Fedora/CentOS warning
if [ -e /etc/redhat-release ]
then
    echo "SELINUX and firewalld changes must be made manually as RedHat-derived distros are not officially supported yet."
fi

# DEFAULT CONFIG IS AUTOMATICALLY GENERATED ON FIRST RUN OF NEW INSTALL IF CONFIG FILE NOT FOUND
