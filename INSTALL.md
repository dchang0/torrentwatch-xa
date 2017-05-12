Installation
===============

NOTE: As of 0.4.1, there is a simple install/upgrade script meant for Debian 8 (but not Debian 7) and Ubuntu 14.04/16.04 called install_twxa.sh that has potentially dangerous `rm -fr` commands inside. **Use this script at your own risk!** It is far safer to perform the following installation manually, without the script. The script does not install prerequisites.

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

Configure > Trigger
===============

torrentwatch-xa can trigger email notifications by SMTP or shell scripts or both. Shell scripts can be used for post-processing including sending email notifications in place of or in addition to the built-in SMTP notifications.

SMTP Notifications trigger on these events:
- Favorite item starts downloading (usually started by cron job, but web UI can do it as well)
- error while downloading
- error in Script

Scripts trigger on these events:
- Favorite item starts downloading
- non-Favorite item starts downloading
- error while downloading

To run a shell script, check Enable Script, provide the full path to a single shell script with no parameters in the Script field. Your shell script must have rwx permissions for www-data, and no parameters may be supplied in the Script field. See /var/lib/torrentwatch-xa/examples for example shell scripts that you can customize to suit your needs.

To use the built-in SMTP notifications, check SMTP Notifications and fill in the From Email and To Email fields and all the SMTP fields. SMTP Port defaults to 25 if left blank. From Email defaults to To Email if left blank or it is invalid. 

For various in-depth reasons I am not providing any means of testing the SMTP settings at this time. You can trigger a real email notification by initiating a Favorite download (by cron job) and checking the log file at /tmp/twxalog for errors.

torrentwatch-xa uses PHPMailer 5.2.23 to send emails, so you may need to refer to PHPMailer documentation for help in understanding any SMTP error messages that appear.