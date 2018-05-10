Prerequisites
===============

### Ubuntu 14.04 and Debian 8.x (jessie)

From the official repos:

- transmission-daemon
- apache2 (Apache httpd 2.4.x)
- php5 (PHP 5.6.0alpha3 or higher)
- php5-json
- php5-curl

### Ubuntu 16.04 and Debian 9.x (stretch)

From the official repos:

- transmission-daemon
- apache2
- php-mbstring (php7.0-mbstring)
- libapache2-mod-php (libapache2-mod-php7.0)
- php (php7.0)
- php-curl (php7.0-curl)

### Ubuntu 18.04 (Not officially supported at this time, but the instructions for Ubuntu 16.04 do work.)

From the official repos:

- transmission-daemon
- apache2
- php-mbstring (php7.2-mbstring)
- libapache2-mod-php (libapache2-mod-php7.2)
- php (php7.2)
- php-curl (php7.2-curl)

### Fedora Server 25 (Not officially supported at this time, but the instructions below do work.)

From the official repos:

- httpd
- php
- php-mbstring
- php-process

Installation
===============

NOTE: For those of you upgrading to 0.8.0 from a version of torrentwatch-xa prior to 0.7.0, if you wish to carry over your config file you must skip to the section **Upgrading to 0.8.0 While Keeping Your Old Config File** below.

There is a rudimentary installation script included with torrentwatch-xa. You may use the script or follow the directions in this section. Skip to the section **Installation Script** below if you prefer using the script.

### Ubuntu 16.04, Ubuntu 14.04, Debian 8.x or Debian 9.x only:

- For Ubuntu 14.04 or Debian 8.x:
  - Start with a Debian 8.x or Ubuntu 14.04 installation.
  - `sudo apt-get install apache2 php5 php5-json php5-curl transmission-daemon git`
- For Ubuntu 16.04 or Debian 9.x:
  - Start with a Debian 9.x or Ubuntu 16.04 installation.
  - If your OS is not up to date, update it now.
    - `sudo apt-get update; sudo apt-get upgrade`
  - `sudo apt-get install apache2 php php-mbstring php-curl libapache2-mod-php transmission-daemon git`
- Set up the transmission-daemon (instructions not included here) and test it so that you know it works and know what the username and password are. You may alternately use a Transmission instance on another server like a NAS.
- Use git to obtain torrentwatch-xa (or download and unzip the zip file instead)
  - `git clone https://github.com/dchang0/torrentwatch-xa.git`
- Copy/move the folders and their contents to their intended locations:
  - `sudo mv ./torrentwatch-xa/var/www/html/torrentwatch-xa /var/www/html`
  - `sudo mv ./torrentwatch-xa/var/lib/torrentwatch-xa /var/lib`
- Allow apache2 to write to the cache folders.
  - `sudo chown -R www-data:www-data /var/lib/torrentwatch-xa/*_cache`
- Set up the cron job by copying the cron job script torrentwatch-xa-cron to /etc/cron.d with proper permissions for it to run.
  - `sudo cp ./torrentwatch-xa/etc/cron.d/torrentwatch-xa-cron /etc/cron.d`
  - Make sure /etc/cron.d/torrentwatch-xa-cron is owned by root:root, or it will not run.
- Restart apache2 just in case some PHP modules are not yet loaded.
  - `sudo service apache2 restart`
- Skip to the section __Continue below for all distros:__ below.

### Fedora Server 25 only:

__RedHat-derived distros are not officially supported at this time__ though the below instructions do work on Fedora Server 25. See the Prerequisites section above or the Status section on the README.md page for further details and updates on Fedora Server support.

- Start with a Fedora Server installation and log in as root.
- If your OS is not up to date, update it now.
  - `dnf update`
- `dnf install httpd php php-mbstring php-process git`
- Add the firewall rule to allow access to httpd. (For CentOS 7, change the zone FedoraServer to public.)
  - `firewall-cmd --zone=FedoraServer --add-service=http --permanent`
  - `firewall-cmd --reload`
- Configure SELINUX to allow httpd to contact Transmission RPC (instructions not included here, but the following commands may work as-is).
  - `dnf install setroubleshoot setools`
  - `ausearch -c 'httpd' --raw | audit2allow -M my-httpd`
  - `semodule -i my-httpd.pp`
- Configure SELINUX to allow httpd to write files to the local filesystem to save .torrent and magnet link files (instructions not included here).
- Set httpd to start at boot and start it now.
  - `systemctl enable httpd.service`
  - `systemctl start httpd.service`
- Set up the transmission-daemon (instructions not included here) and test it so that you know it works and know what the username and password are. You may alternately use a Transmission instance on another server like a NAS.
- Use git to obtain torrentwatch-xa (or download and unzip the zip file instead)
  - `git clone https://github.com/dchang0/torrentwatch-xa.git`
- Copy/move the folders and their contents to their intended locations:
  - `mv ./torrentwatch-xa/var/www/html/torrentwatch-xa /var/www/html`
  - `mv ./torrentwatch-xa/var/lib/torrentwatch-xa /var/lib`
- Allow httpd to write to the cache folders.
  - `sudo chown -R apache:apache /var/lib/torrentwatch-xa/*_cache`
- Set up the cron job by copying the cron job script torrentwatch-xa-cron to /etc/cron.d with proper permissions for it to run.
  - `cp ./torrentwatch-xa/etc/cron.d/torrentwatch-xa-cron /etc/cron.d`
  - Make sure /etc/cron.d/torrentwatch-xa-cron is owned by root:root, or it will not run.
  - Edit /etc/cron.d/torrentwatch-xa-cron and change `www-data` to `apache`
- Restart httpd just in case some PHP modules are not yet loaded.
  - `systemctl restart httpd.service`
- Continue with the section __Continue below for all distros:__ below.

### Continue below for all distros:

- Open a web browser and visit `http://[hostname or IP of torrentwatch-xa webserver]/torrentwatch-xa`
- You may see error messages if apache2 is unable to write to the three cache folders. Correct any such errors.
- Use the Configure panel to set up the Transmission connection.
  - It may be necessary to restart Transmission to get torrentwatch-xa to connect.
    - For Ubuntu 16.04, Ubuntu 14.04, or Debian 8.x:
      - `sudo service transmission-daemon restart`
  - It may also be necessary to reconfigure Transmission (not described here) to get it to work.
  - Fedora may require additional SELINUX configuration to allow httpd to contact Transmission.
- You should already see some items from the default RSS feeds. Use the Configure panel to set up the RSS or Atom torrent feeds to your liking.
- Use the Favorites panel to set up your automatic downloads.
  - Be aware that your favorites may appear to not work if they are configured to be too stringent a match.
  - For instance, when using the "heart" button in the button bar to add a favorite, it MAY not get the title exactly correct in the newly-created favorite's Filter field, making it fail to match the very item used to create the favorite! Edit the favorite to cast a wider net:
    - Change the Qualities field to `All`
    - Remove the season and episode number from the title in the Filter field if present.
    - Remove any extraneous characters like trailing spaces, dashes, and symbols from the Filter field if present.
    - Remove the Last Downloaded Episode values if present.
    - Click the Update button to save the changes to the favorite.
    - Then, empty all caches and refresh the browser to trigger the match and start the download.
- Wait for some downloads to happen automatically or start some manually.
- Enjoy your downloaded torrents!


Installation Script
===============

There is a rudimentary install/upgrade script called `install_twxa.sh` meant for Debian 8.x/9.x (but not Debian 7.x) and Ubuntu 14.04/16.04. It does work with the as-yet unsupported Fedora Server 25.

The install/upgrade script will remove an existing installation of torrentwatch-xa and only performs the copy and chown steps to put a fresh install in place. Be aware that the script contains `rm -fr` commands, which are potentially dangerous. **Use install_twxa.sh at your own risk!** I will gradually improve the script over time until it essentially does every installation step, at which point it would probably be easiest to provide a .deb installation package.

If upgrading, the `install_twxa.sh` script has an option `--keep-config` that will copy your current config file to your home directory, then copy it back after performing the upgrade.


Upgrading to 0.8.0 While Keeping Your Old Config File
===============

torrentwatch-xa 0.8.0 stores its config file in PHP's built-in JSON objClass format.

If you are upgrading from any prior version of torrentwatch-xa, please plan ahead carefully. It is best and easiest to start fresh with a default 0.8.0 config file, transferring any configuration by manually entering Configuration and Favorite settings via the web UI. To minimize data entry, it is probably best to time the upgrade to 0.8.0 to coincide with the start of a new show season, so that your list of Favorites starts fresh.

If you have a huge collection of Favorites that you wish to carry over, then it may be worth your while to try the conversion tool provided with 0.8.0:

/var/lib/torrentwatch-xa/lib/upgrade0_7_0ConfigTo0_8_0.php

This converter will back up your current 0.7.0 config file and then replace it with an 0.8.0 equivalent, BUT you must be careful to run this converter quickly, as the window of opportunity to use it is small. (Either that, or disable the cron job for the duration of the conversion.)

The steps to follow are:

1) If you are running any version of torrentwatch-xa earlier than 0.7.0, please upgrade to 0.7.0 first and use the conversion tool that came with 0.7.0 to update your config file:

/var/lib/torrentwatch-xa/lib/upgradeConfigTo0_7_0.php

Make sure no backup config file exists at this path:

/var/lib/torrentwatch-xa/config_cache/torrentwatch-xa.config.bak

If this file exists, the converter will not work, as it creates a new backup file.

2) Download torrentwatch-xa 0.8.0 to your home directory or any other temporary location:

git clone https://github.com/dchang0/torrentwatch-xa.git

3) Then, pay special attention to the clock. Because the cron job runs automatically every 15 minutes and the config file will be overwritten if it cannot be read, you have at most a 15 minute window to upgrade from 0.7.0 to 0.8.0 and finish the rest of this procedure. Close any browser window that has torrentwatch-xa in it, as the Javascript may run torrentwatch-xa. It is best to start right after the cron job has finished (such as at the start of each hour). When ready, proceed to step 4.

4) Quickly install 0.8.0 using the included install_twxa.sh script:

sudo torrentwatch-xa/install_twxa.sh --keep-config

This will backup the old 0.7.0 config file to your home directory and then copy it back into place at the proper location:

/var/lib/torrentwatch-xa/config_cache/torrentwatch-xa.config

5) Quickly run the converter:

sudo /usr/bin/php /var/lib/torrentwatch-xa/lib/upgrade0_7_0ConfigTo0_8_0.php

It will back up the 0.7.0 config file and then replace it with an 0.8.0 JSON config file.

You can only run the converter once. If you must run it again, rename the 0.7.0 backup config file back to the original config file name, then run the converter again.

All this must be done before torrentwatch-xa's next run. Like I said, it's easier to start over fresh with a default 0.8.0 config file.

The converter provided with 0.8.0 will only be available in 0.8.0. By the next release, any code related to the older Windows INI format will be deleted.