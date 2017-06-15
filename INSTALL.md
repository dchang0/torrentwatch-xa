Installation
===============

NOTE: For those of you upgrading from a version of torrentwatch-xa prior to 0.5.0, you MUST either replace `/etc/cron.d/torrentwatch-xa-cron` OR edit it and remove the `-D` flag (for Process Watch Dir). Otherwise, the cron job will generate an error and fail. The `install_twxa.sh` script can do this for you; note the `--keep-config` option that backs up your config file to your home directory and then puts it back after installing the new torrentwatch-xa files and folders.

- For Ubuntu 14.04 or Debian 8.x:
  - Start with a Debian 8.x or Ubuntu 14.04 installation.
  - `sudo apt-get install apache2 php5 php5-json php5-curl transmission-daemon`
- For Ubuntu 16.04:
  - Start with an Ubuntu 16.04 installation.
  - If your OS is not up to date, it's a good idea to do so.
    - `sudo apt-get update; sudo apt-get upgrade`
  - `sudo apt-get install apache2 php php-mbstring php-curl libapache2-mod-php transmission-daemon`
- Set up the transmission-daemon (instructions not included here) and test it so that you know it works and know what the username and password are. You may alternately use a Transmission instance on another server like a NAS.
- Use git to obtain torrentwatch-xa (or download and unzip the zip file instead)
  - `sudo apt-get install git`
  - `git clone https://github.com/dchang0/torrentwatch-xa.git`
- Copy/move the folders and their contents to their intended locations:
  - `sudo mv ./torrentwatch-xa/var/www/html/torrentwatch-xa /var/www/html`
  - `sudo mv ./torrentwatch-xa/var/lib/torrentwatch-xa /var/lib`
- Allow apache2 to write to the cache folders.
  - `sudo chown -R www-data:www-data /var/lib/torrentwatch-xa/*_cache`
- Set up the cron job by copying the cron job script torrentwatch-xa-cron to /etc/cron.d with proper permissions for it to run.
  - `sudo cp ./torrentwatch-xa/etc/cron.d/torrentwatch-xa-cron /etc/cron.d`
  - Make sure /etc/cron.d/torrentwatch-xa-cron is owned by root:root, or it will not run.
  - (optional) `sudo chmod 644 /etc/cron.d/torrentwatch-xa-cron`
- Restart apache2
  - `sudo service apache2 restart`
- Open a web browser and visit `http://[hostname or IP of torrentwatch-xa webserver]/torrentwatch-xa`
- You may see error messages if apache2 is unable to write to the three cache folders. Correct any such errors.
- Use the Configure panel to set up the Transmission connection.
  - It may be necessary to restart Transmission to get torrentwatch-xa to connect.
    - `sudo service transmission-daemon restart`
  - It may also be necessary to reconfigure Transmission (not described here) to get it to work.
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

As of 0.4.1, there is a simple install/upgrade script called `install_twxa.sh` meant for Debian 8.x (but not Debian 7.x) and Ubuntu 14.04/16.04. It will remove an existing installation of torrentwatch-xa and only performs the copy and chown steps to put a fresh install in place. Be aware that the script contains `rm -fr` commands, which are potentially dangerous. **Use install_twxa.sh at your own risk!** I will gradually improve the script over time until it essentially does every installation step, at which point it would probably be easiest to provide a .deb installation package.

As of 0.5.0, the `install_twxa.sh` script has an option `--keep-config` that will copy your current config file to your home directory, then copy it back after performing an upgrade.
