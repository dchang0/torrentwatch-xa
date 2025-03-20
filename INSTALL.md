Officially-supported OSes
===============

- Debian 9.x and up
- Ubuntu 16.04 and up
- Fedora Server 34 and up with SELINUX in Permissive mode (see below for more details on SELINUX)

Other LINUX distributions and other operating systems will work as long as they provide a web server with PHP 5.6.0alpha3 and up with mbstring, xml, curl, and posix_getuid() support. PHP 5.6.0alpha3 is really only required by PHPMailer's SMTP 5.2.23 library to support TLS 1.1 and 1.2. torrentwatch-xa itself only requires PHP 5.4.0. If you are not using email triggers with TLS 1.1 or 1.2, you should be able to avoid this version requirement by downgrading PHPMailer's SMTP library.

Prerequisites
===============

### Debian 9.x and up, Ubuntu 16.04 and up

From the official repositories:

- cron
- transmission-daemon
- apache2
- php-mbstring
- libapache2-mod-php
- php
- php-curl
- php-xml

Suggested (optional):

- logrotate

### Fedora Server

cron is required but should be part of Fedora's base installation.

From the official repositories:

- transmission-daemon
- httpd
- php
- php-mbstring
- php-process (provides posix_getuid() function)
- php-xml

Suggested (optional):

- logrotate

Password Security
===============

torrentwatch-xa stores and uses two passwords: one for Transmission and one for SMTP. If security is a concern, be sure to configure your web server with SSL and harden the OS.


.deb Package Installation
===============

The easiest way to install torrentwatch-xa is via the .deb package for Debian-based LINUX distributions. The .deb package file is in the Releases section of this repository. Install it with this command:

First, install the prerequisites:

`sudo apt install transmission-daemon apache2 php libapache2-mod-php php-curl php-mbstring php-xml`

Then, install the .deb package (replace #.#.# with the correct version number):

`sudo dpkg -i torrentwatch-xa-#.#.#-0-noarch.deb`

Then, open a web browser and visit `http://[IP of torrentwatch-xa webserver]/torrentwatch-xa`

NOTE: When uninstalling the .deb package to start over, be sure to run `dpkg --purge torrentwatch-xa` to remove the configuration files and clear the package details from the dpkg database.

Installation Script
===============

If the .deb package won't work for you, this repository provides an install/upgrade script called `install_twxa.sh`

`install_twxa.sh` will remove an existing installation of torrentwatch-xa and only performs the copy and chown steps to put a fresh install in place. It does not install any prerequisite packages for you, nor does it configure or start/restart the Apache2 webserver. It also does not configure firewalld nor SELINUX for Fedora Server and other RedHat-derived distributions. See Manual Installation below for those steps.

Be aware that the script contains potentially-dangerous `rm -fr` commands. **Use install_twxa.sh at your own risk!**

To use the script, make sure you have sudo privileges or are logged in as root, then:

- `git clone https://github.com/dchang0/torrentwatch-xa.git`
- `cd torrentwatch-xa`

If you are upgrading and want to keep your previous config:

- `./install_twxa.sh --keep-config`

For fresh installs or if you want to discard your config:

- `./install_twxa.sh`

The script will back up the config file if it sees one even if `--keep-config` is not specified. It will put the backup in your home folder as `~/torrentwatch-xa.config.bak`. Your config file might be too old to use if you are upgrading from a very old version.

Manual Installation
===============

### Ubuntu 16.04 and up or Debian 9.x and up:

- Make sure you have sudo privileges or are logged in as root. Do not skip using `sudo`, or the resulting file permissions will probably be incorrect.
- If your OS is not up to date, update it now.
  - `sudo apt-get update; sudo apt-get upgrade`
- `sudo apt-get install apache2 php php-mbstring php-curl php-xml libapache2-mod-php git`
- Install transmission-daemon
  - `sudo apt-get install transmission-daemon`
- IMPORTANT: For Ubuntu 24.04 (and probably all newer Ubuntu versions), AppArmor prevents transmission-daemon from starting. To fix it, you need to edit /etc/apparmor.d/transmission and change this line:

  `profile transmission-daemon /usr/bin/transmission-daemon flags=(complain) {`

  to this:

  `profile transmission-daemon /usr/bin/transmission-daemon flags=(complain,attach_disconnected) {`

  Then reboot for it to take effect.
- Optional: configure transmission-daemon
  - Note that transmission-daemon must be stopped while you make changes to the configuration file.
  - `sudo vi /etc/transmission-daemon/settings.json`
- Use git to obtain torrentwatch-xa (or download and unzip the zip file instead)
  - `git clone https://github.com/dchang0/torrentwatch-xa.git`
- Copy/move the folders and their contents to their intended locations:
  - `sudo mv ./torrentwatch-xa/var/www/html/torrentwatch-xa /var/www/html`
  - `sudo mv ./torrentwatch-xa/var/lib/torrentwatch-xa /var/lib`
- Create the log file:
  - `sudo touch /var/log/torrentwatch-xa.log`
  - `sudo chown www-data:www-data /var/log/torrentwatch-xa.log`
  - Make sure it is owned by `www-data:www-data` and has permissions `rw-r--r--` (644)
    - `ls -l /var/log/torrentwatch-xa.log`
- If you want to use logrotate, copy the config file to `/etc/logrotate.d`.
  - `sudo cp ./torrentwatch-xa/etc/logrotate.d/torrentwatch-xa /etc/logrotate.d`
  - Make sure `/etc/logrotate.d/torrentwatch-xa` is owned by `root:root` and has permissions `rw-r--r`.
    - `ls -l /etc/logrotate.d`
- Allow apache2 to write to the cache folders.
  - `sudo chown -R www-data:www-data /var/lib/torrentwatch-xa/*_cache`
- Make sure that `config_cache` and `dl_cache` are both owned by `www-data:www-data` and have permissions `drwxr-xr-x` (755)
    - `ls -l /var/lib/torrentwatch-xa`
- Set up the cron job by copying the cron job script `torrentwatch-xa` to `/etc/cron.d`.
  - `sudo cp ./torrentwatch-xa/etc/cron.d/torrentwatch-xa /etc/cron.d`
  - Make sure `/etc/cron.d/torrentwatch-xa` is owned by `root:root` and has permissions `rw-r--r--` (644), or it will not run.
    - `ls -l /etc/cron.d`
- Skip to the section __Continue below for all distros:__ below.

### Fedora Server 34 and up:

Note that these instructions will work for versions of Fedora Server going pretty far back--at least as far back as Fedora Server 25, but probably further back than that.

The RedHat-derived distributions have extra security features that have to be dealt with: a built-in firewall and SELINUX. The firewall isn't too difficult to configure properly, but SELINUX is very complex and difficult. My recommendation, even though it is frowned upon by security experts, is to switch SELINUX to `Permissive` mode. If you wish to configure SELINUX properly in `Enforcing` mode, see the notes at the bottom of this file.

- Start with a Fedora Server installation and log in as root. You must have root privileges to run these commands and install the files with the proper permissions.
- If your OS is not up to date, update it now.
  - `sudo dnf update`
- `sudo dnf install httpd php php-mbstring php-process git`
- Add the firewall rule to allow access to httpd. (In CentOS 7, the zone is named `public` instead of `FedoraServer`.)
  - `sudo firewall-cmd --zone=FedoraServer --add-service=http --permanent`
  - `sudo firewall-cmd --reload`
  - `sudo firewall-cmd --list-all-zones | more`
- Set SELINUX from `Enforcing` mode to `Permissive` mode
  - `sudo sestatus`
  - `sudo setenforce permissive`
  - Change the line `SELINUX=enforcing` to `SELINUX=permissive` and save the file with `:wq`
    - `sudo vi /etc/selinux/config`
- Set httpd to start at boot and start it now.
  - `sudo systemctl enable httpd.service`
  - `sudo systemctl start httpd.service`
- Install transmission-daemon
  - `sudo dnf install transmission-daemon`
  - `sudo systemctl enable transmission-daemon.service`
- Optional: configure transmission-daemon
  - Note that transmission-daemon must be stopped while you make changes to the configuration file.
  - `sudo vi /var/lib/transmission/.config/transmission-daemon/settings.json`
- Start transmission-daemon
  - `sudo systemctl start transmission-daemon.service`
- Use git to obtain torrentwatch-xa (or download and unzip the zip file instead)
  - `cd ~`
  - `git clone https://github.com/dchang0/torrentwatch-xa.git`
- Copy/move the folders and their contents to their intended locations:
  - `sudo mv ./torrentwatch-xa/var/www/html/torrentwatch-xa /var/www/html`
  - `sudo mv ./torrentwatch-xa/var/lib/torrentwatch-xa /var/lib`
- Create the log file:
  - `sudo touch /var/log/torrentwatch-xa.log`
  - `sudo chown apache:apache /var/log/torrentwatch-xa.log`
  - Make sure it is owned by `apache:apache` and has permissions `rw-r--r--` (644)
    - `ls -l /var/log/torrentwatch-xa.log`
- Allow httpd to write to the cache folders.
  - `sudo chown -R apache:apache /var/lib/torrentwatch-xa/*_cache`
  - Make sure that `config_cache` and `dl_cache` are both owned by `apache:apache` and have permissions `drwxr-xr-x` (755)
    - `ls -l /var/lib/torrentwatch-xa`
- Set up the cron job by copying the cron job script `torrentwatch-xa` to `/etc/cron.d`.
  - `vi ./torrentwatch-xa/etc/cron.d/torrentwatch-xa`
    - IMPORTANT: change `www-data` to `apache`
    - Save the file with `:wq`
  - `sudo cp ./torrentwatch-xa/etc/cron.d/torrentwatch-xa /etc/cron.d`
  - Make sure `/etc/cron.d/torrentwatch-xa` is owned by `root:root` and the permissions are `rw-r--r--` (644), or it will not run.
    - `ls -l /etc/cron.d`
- Continue with the section __Continue below for all distros:__ below.

### Continue below for all distros:

- Get the IP address of the web server
  - `ip addr`
  - Look for the IP address of `eth0` (your primary NIC might be named something else)
- Open a web browser and visit `http://[IP of torrentwatch-xa webserver]/torrentwatch-xa`
- You should at least see the torrentwatch-xa web UI without any errors at this point. If not, go back and make sure you didn't miss any steps. The most common mistakes are:
  - Permissions problems--for instance, you may see error messages if the web server is unable to write to the two cache folders.
  - For systems with SELINUX installed, Forbidden 403 is probably SELINUX operating in `Enforcing` mode.
  - A blank page is almost certainly a missing PHP module. Be sure to install all the prerequisites and restart the web server for them to take effect.
- Use the Configure > Client panel to set up the Transmission connection.
  - If you're using a remote instance of Transmission, provide the appropriate settings.
    - The Configure > Client > Download Dir setting must be a path that Transmission can read from and write to. Be aware that the Download Dir setting refers to a path on the remote host, not on the local host where torrentwatch-xa is running.
  - If you're using the local instance of transmission-daemon:
    - torrentwatch-xa should be configured with the default username and password set by the transmission-daemon package installation.
    - Change Configure > Client > Download Dir to the appropriate path:
      - For Debian/Ubuntu use `/var/lib/transmission-daemon/downloads` This is the default setting for torrentwatch-xa.
      - IMPORTANT: for Fedora Server use `/var/lib/transmission`. You will have to change Download Dir to match this before you start downloading any torrents.
- You should already see some items from the default feeds. Use the Configure > Feeds panel to set up the RSS or Atom torrent feeds to your liking.
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

Configuring SELINUX In Enforcing Mode
===============

If you wish to keep SELINUX in the default `Enforcing` mode and configure it to allow torrentwatch-xa and transmission-daemon to work properly, generally, these are the permissions you need to grant:

- httpd must be able to read from these folders and all their contents:
  - `/var/www/html/torrentwatch-xa`
  - `/var/lib/torrentwatch-xa/`
- httpd must be able to write to these files and folders:
  - `/var/lib/torrentwatch-xa/dl_cache` and all its contents
  - `/var/lib/torrentwatch-xa/config_cache` and all its contents
  - `/var/log/torrentwatch-xa.log`
  - the watch directory where torrentwatch-xa will drop .torrent and .magnet files for transmission-daemon or some other BitTorrent client to pick up
- httpd must be able to contact either the local or remote transmission-daemon via RPC if you have Configure > Client > Client = Transmission

Since SELINUX is so complex and evolves quickly, I cannot include instructions here. Check out various forums and videos for help configuring SELINUX to allow the above.