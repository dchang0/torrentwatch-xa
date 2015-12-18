![torrentwatch-xa TWXA logo](http://silverlakecorp.com/torrentwatch-xa/torrentwatch-xa-logo144.png)

torrentwatch-xa
===============

torrentwatch-xa is a fork of Joris Vandalon's TorrentWatch-X automatic episodic torrent downloader with the _extra_ capability of handling anime fansub torrents that do not have season numbers, only episode numbers. It will continue to handle live-action TV episodes with nearly all season + episode notations.

To restrict the development and testing scopes in order to improve quality assurance, I am focusing on Debian 8.x LINUX as the only OS and on Transmission as the only torrent client.

In the process of customizing torrentwatch-xa to fit my needs and workflow, I'll:

- fix some bugs
- refactor some code
- add some features, mostly UI and workflow improvements
- let some features languish or remove them outright, especially buggy/unreliable portions of the code
 
The end goal is for torrentwatch-xa to do only what it's supposed to do and do it well. Over time, this will mean that broken or aging features will probably be removed rather than repaired. While such features still work, they will remain.

Status and Announcements
===============

CURRENT VERSION: I've posted 0.2.2 with the changes listed in CHANGELOG. This version is almost entirely devoted to the upgrade from Debian 7.x and PHP 5.4 to Debian 8.x and PHP 5.6.

NEXT VERSION: 0.2.3 in progress, focusing on refinement of the season and episode detection engine along with small bug fixes.

I MAY tackle the following large change: Carried over in the clone from TorrentWatch-X, the torInfo() function was only half-completed. This MUST be fixed to reduce confusion in the torrent download mechanism, but it could take a while to unravel. I can see why it was abandoned half-finished. The new version should be properly interfaced, but it may take many releases before it is fully rewritten.

Known bugs are tracked primarily in the TODO and CHANGELOG files. Tickets in GitHub Issues will remain separate for accountability reasons and will also be referenced in the TODO and CHANGELOG.

Design Decisions Explained
===============

"One man's bug is another man's feature."

1) It's become obvious that there are situations for which a mutually-exclusive design decision cannot be avoided. For example, the title "Holly Stage for 50 - 3" is meant to be interpreted as title = "Holly Stage for 50" and episode number 3, with season 1 implied.
(Fans know that "Holly Stage for 50 - 3" really should be read as title = "Holly Stage for 49", season 2, episode 3, to further complicate matters.)
But the engine currently reads it as title = "Holly Stage for" and season 50, episode 3. Why? Because it was determined that the ## - ## pattern much more often means SS - EE.

Sadly, because the engine was forced to make the choice, fans of "Holly Stage for 50" must "hack" the Favorite to get it to download properly. There is no way to solve this problem without referring to some centralized database of anime titles or relying on some sort of AI, neither of which are going to happen in torrentwatch-xa any time soon.

2) If one starts an item downloading from a feed list and that item is bumped off the end of the feed list by newer items on the next browser refresh, the item will not appear in the Downloaded or Downloading filtered lists even if the item still shows on the Transmission tab as downloading or downloaded. This is because the item simply is no longer in the list to be filtered and then shown by the Downloading and Downloaded filters. It seems counterintuitive until one understands that the Downloaded and Downloading filters are view filters on the feed list, not historical logs nor connected to Transmission's internal list.

Tested Platforms
===============

torrentwatch-xa is developed and tested on an out-of-the-box install of Debian 8.2 x86_64 with its out-of-the-box transmission-daemon, Apache2.4, and PHP5.6 packages. I have tested it using the local transmission-daemon as well as a remote transmission-daemon running on a separate NAS on the same LAN.

Up until torrentwatch-xa 0.2.1, development was targeted at Debian 7.x wheezy with PHP 5.4. The code seems to work flawlessly on either Debian 7.x or 8.x without any modifications except that the web UI portion of torrentwatch-xa is installed in /var/www/torrentwatch-xa on Debian 7.x and in /var/www/html/torrentwatch-xa in Debian 8.x. The file /var/lib/torrentwatch-xa/config.php has to be changed to reflect this; the value of get_webDir() must be set to the correct path. Since torrentwatch-xa 0.2.2 targets Debian 8.x, the default settings will be for Debian 8.x.

Nearly all the debugging features are turned on and will remain so for the foreseeable future.

Be aware that I rarely test the GitHub copy of the code; I test using my local copy, and I rarely do wipe-and-reinstall torrentwatch-xa testing. So it is possible that permissions and file ownership differences may break the GitHub copy without my knowing it.

The last wipe-and-reinstall test of the GitHub copy occurred with torrentwatch-xa 0.2.2 on Debian 8.2 x86_64 on 2015-12-17 and was a success.

Prerequisites
===============

The following packages are provided by the official Debian 8.x jessie repos:

- transmission-daemon
- apache2 (currently Apache httpd 2.4.x)
- php5 (currently PHP 5.6)

Installation
===============

Installation is fairly straightforward.

- Start with a Debian 8.x installation. (It can run with none of the tasksel bundles selected, but I typically choose only "SSH Server" and "Standard System Utilities".)
- `sudo apt-get install apache2 php5 transmission-daemon`
- Set up the transmission-daemon (instructions not included here) and test it so that you know it works and know what the username and password are. You may alternately use a Transmission instance on another server like a NAS.
- Use git to obtain torrentwatch-xa (or download and unzip the zip file instead)
  - `sudo apt-get install git`
  - `git clone https://github.com/dchang0/torrentwatch-xa.git`
- Copy/move the folders and their contents to their intended locations:
  - `sudo mv ./torrentwatch-xa/var/www/html/torrentwatch-xa /var/www/html`
  - `sudo mv ./torrentwatch-xa/var/lib/torrentwatch-xa /var/lib`
- Allow apache2 to write to the three cache folders.
  - `sudo chown -R www-data:www-data /var/lib/torrentwatch-xa/*_cache`
- Set up the cron job by copying the cron job script torrentwatch-xa-cron to /etc/cron.d with proper permissions for it to run.
  - `sudo cp ./torrentwatch-xa/etc/cron.d/torrentwatch-xa-cron /etc/cron.d`
  - (optional) `sudo chmod 755 /etc/cron.d/torrentwatch-xa-cron`
- Restart apache2
  - `sudo service apache2 restart`
- Open a web browser and visit `http://[hostname or IP of your Debian instance]/torrentwatch-xa`
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

Credits
===============

The credits may change as features and assets are removed.

- Original TorrentWatch-X by Joris Vandalon
- Original Torrentwatch by Erik Bernhardson
- Original Torrentwatch CSS styling, images and general html tweaking by Keith Solomon http://reciprocity.be/
- Some of the icons were made by David Vignoni and are from Nuvola-1.0 available under the LGPL http://icon-king.com/
- Backgrounds and CSS Layout are borrowed from Clutch http://www.clutchbt.com/
- I have stumbled upon some credits embedded in various files that were put there by prior coders and that will not be re-listed here.