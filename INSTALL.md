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

Security
===============

torrentwatch-xa handles two passwords: one for Transmission and one for SMTP. If security is a concern, be sure to configure your web server with SSL and harden the OS.

Installation Script
===============

There is a rudimentary install/upgrade script called `install_twxa.sh` compatible with Debian 8.x/9.x (but not Debian 7.x), Ubuntu 14.04/16.04/18.04, and Fedora Server 25.

The install/upgrade script will remove an existing installation of torrentwatch-xa and only performs the copy and chown steps to put a fresh install in place. It does not install any prerequisite packages for you, nor does it configure or start/restart the Apache2 webserver. See Manual Installation below for those steps.

Be aware that the script contains `rm -fr` commands, which are potentially dangerous. **Use install_twxa.sh at your own risk!** I will gradually improve the script over time until it essentially does every installation step, at which point it would probably be best to provide a .deb installation package.

To use the script, make sure you have sudo privileges or are running as root, then:

- `git clone https://github.com/dchang0/torrentwatch-xa.git`
- `cd torrentwatch-xa`

Then, if you are upgrading and want to keep your previous config:

- `./install_twxa.sh --keep-config`

For fresh installs or when you want to discard your config:

- `./install_twxa.sh`

The script will back up the config file if it sees one even if `--keep-config` is not specified. It will put the backup in your home folder as `~/torrentwatch-xa.config.bak`. Your config file might be too old to use if you are upgrading from a very old version.

Manual Installation
===============

### Ubuntu 18.04, Ubuntu 16.04, Ubuntu 14.04, Debian 8.x or Debian 9.x only:

- For Ubuntu 14.04 or Debian 8.x:
  - Start with a Debian 8.x or Ubuntu 14.04 installation.
  - `sudo apt-get install apache2 php5 php5-json php5-curl transmission-daemon git`
- For Ubuntu 18.04, Ubuntu 16.04, or Debian 9.x:
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
- Create the log file:
  - `sudo touch /var/log/twxalog`
  - `sudo chown www-data:www-data /var/log/twxalog`
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
- Create the log file:
  - `sudo touch /var/log/twxalog`
  - `sudo chown apache:apache /var/log/twxalog`
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
- You may see error messages if apache2 is unable to write to the two cache folders. Correct any such errors.
- Use the Configure > Client panel to set up the Transmission connection.
  - The Configure > Client > Download Dir setting needs to be a path that Transmission can reach and write to. If your Transmission daemon is running on a remote host, be aware that the Download Dir setting refers to a path on the remote host, not on the local host where torrentwatch-xa is running.
  - It may be necessary to restart Transmission to get torrentwatch-xa to connect.
    - For Ubuntu 18.04, Ubuntu 16.04, Ubuntu 14.04, or Debian 8.x:
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

