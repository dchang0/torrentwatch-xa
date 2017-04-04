![torrentwatch-xa TWXA logo](http://silverlakecorp.com/torrentwatch-xa/torrentwatch-xa-logo144.png)

torrentwatch-xa
===============

torrentwatch-xa is a fork of Joris Vandalon's TorrentWatch-X automatic episodic torrent downloader with the _extra_ capability of handling anime fansub torrents that do not have season numbers, only episode numbers. It will continue to handle live-action TV episodes with nearly all season + episode notations.

To restrict the development and testing scopes in order to improve quality assurance, I am focusing on Ubuntu 16.04.x LINUX as the only OS and on Transmission as the only torrent client. It works on pretty much any modern web browser that has Javascript enabled.

In the process of customizing torrentwatch-xa to fit my needs and workflow, I'll:

- fix some bugs
- refactor some code
- add some features, mostly UI and workflow improvements
- let some features languish or remove them outright, especially buggy/unreliable portions of the code
 
The end goal is for torrentwatch-xa to do only what it's supposed to do and do it well. Over time, this will mean that broken or aging features will probably be removed rather than repaired. While such features still work, they will remain.

Status and Announcements
===============

CURRENT VERSION: I've posted 0.2.5 with the changes listed in CHANGELOG. This version is mostly minor bugfixes, two of which affected me last season: recap episodes with decimal numbering and exclamation points as the last character of a Favorite's Filter.

In the process of diagnosing a reproducible Apache2 + PHP 7.0 segmentation fault on Ubuntu 16.04.2 with PHP 7.0, I have tested 0.2.5 on Ubuntu 14.04.5 with PHP 5.6 and found that the SEGFAULT does not occur, so the code is now verified to work on Ubuntu 14.04.5 with PHP 5.6.

As for the SEGFAULT itself, I will have to solve it soon, as the target is still Ubuntu 16.04 with PHP 7.0. Sadly, I am not gaining much visibility into the cause using XDebug--the SEGFAULT is reproducible via a certain series of interactions with torrentwatch-xa, but the actual process that dies is not the one running torrentwatch-xa's PHP code, so it does not get stack-traced by XDebug. It may take a while to diagnose this problem. By that time, I will probably release 0.2.6 with the fix.

For now, if you don't plan on updating torrentwatch-xa frequently, choose Ubuntu 14.04.x and PHP 5.6 as your platform.

NEXT VERSION: 0.2.6 in progress, focusing on:
- upgrade of JQuery to latest 1.x version
- upgrade of jquery.form.js from 2.43 to 4.2.1
- email notifications don't work
  - upgrade of PHPMailer from 5.2 to 5.2.23
  - addition of Configure options for email notifications sent via PHPMailer SMTP
- refinement of the season and episode detection engine
- cleaning up PHP Warnings and Notices
- improving code quality

The JQuery and JQuery plugin upgrades will require testing over an extended period since there may be unintended breakages. Thus, it will be many months before 0.2.6 is released.

Known bugs are tracked primarily in the TODO and CHANGELOG files. Tickets in GitHub Issues will remain separate for accountability reasons and will also be referenced in the TODO and CHANGELOG.

Tested Platforms
===============

NOTE: read status above about updates on a SEGFAULT bug that affects Ubuntu 16.04.2 with PHP 7.0 but not Ubuntu 14.04.5 with PHP 5.6.

torrentwatch-xa is developed and tested on Ubuntu 16.04.x LTS with the prerequisite packages listed in the next section. For this testbed transmission-daemon is not installed locally--a separate NAS on the same LAN serves as the transmission server.

torrentwatch-xa should work without modifications on an out-of-the-box install of Debian 8.x x86_64 or Ubuntu 14.04, although I am not actively testing on these platforms.

Nearly all the debugging features are turned on and will remain so for the foreseeable future.

Be aware that I rarely test the GitHub copy of the code; I test using my local copy, and I rarely do wipe-and-reinstall torrentwatch-xa testing. So it is possible that permissions and file ownership differences may break the GitHub copy without my knowing it.

Prerequisites
===============

### Ubuntu 16.04

From the official repos:

- transmission-daemon
- apache2
- php-mbstring (defaults to php7.0-mbstring)
- libapache2-mod-php (defaults to libapache2-mod-php7.0)
- php (defaults to php7.0)

### Debian 8.x and Ubuntu 14.04

From the official repos:

- transmission-daemon
- apache2 (currently Apache httpd 2.4.10)
- php5 (currently PHP 5.6)

Installation
===============

- For Debian 8.x or Ubuntu 14.04:
  - Start with a Debian 8.x or Ubuntu 14.04 installation.
  - `sudo apt-get install apache2 php5 transmission-daemon`
- For Ubuntu 16.04:
  - Start with an Ubuntu 16.04 installation.
  - `sudo apt-get install apache2 php php-mbstring libapache2-mod-php transmission-daemon`
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

Troubleshooting
===============

### Can't add some feeds

I have found that some feeds can't be added for various reasons, including:

- rejection of SSL certifcate chain
- feed format is not recognized as Atom or RSS
- can't handle HTTP redirects

The plan is to completely rewrite curl.php so that it uses PHP 5.6 and later's built-in CURL functions, but this will not happen soon due to the difficulty.

Please note that I have not been able to personally test torrentwatch-xa's handling of Atom feeds due to their rarity. It seems that RSS is far more common for torrent feeds.

### Can't handle compressed torrent files

Some feeds link to some torrent files on them that are compressed (usually gzipped). I do not plan to fix this because it is usually very easy to find the same media or content via some other torrent file that is not compressed, possibly even on the same feed.

### Email notifications not actually sending (SMTP errors)

I haven't modernized the SMTP code yet, and it is currently the same as it was in TorrentWatch-X 0.8.9. Most obviously missing is the ability to use SMTP authentication. torrentwatch-xa currently uses a moderately old version of PHPMailer that does support SMTP authentication, but torrentwatch-xa does not yet take full advantage of what PHPMailer has to offer. 

For now, the simplest way for you to get email notifications going is to install sendmail locally on the torrentwatch-xa web server and set up SMTP relaying of emails from localhost through a smarthost with SMTP authentication, then configure torrentwatch-xa to use localhost as the SMTP server.

I plan on upgrading PHPMailer to the latest version and adding SMTP authentication soon.

### Allowed memory size of ... exhausted or AH00052: child pid ... exit signal Segmentation fault (11)

PHP memory_limit may be too low to handle some of the larger feeds. Edit your php.ini file (typically /etc/php/7.0/apache2/php.ini) and increase the size of memory_limit to something reasonable. 256MB seems to be too low for the default RSS feeds.

### Design Decisions Explained

"One man's bug is another man's feature."

It's become obvious that there are situations for which a mutually-exclusive design decision cannot be avoided. The below are design decisions that will never be "fixed."

#### Some Numbering Schemes Only Make Sense to Humans

The title "Holly Stage for 50 - 3" is meant to be interpreted as title = "Holly Stage for 50" and Episode 3, with Season 1 implied.
(Fans know that "Holly Stage for 50 - 3" really should be read as title = "Holly Stage for 49", Season 2, Episode 3, to further complicate matters.)
But the engine currently reads it as title = "Holly Stage for" and Season 50, Episode 3. Why? Because it was determined that the ## - ## pattern much more often means Season ## - Episode ##.

Sadly, because the engine was forced to make the choice, fans of "Holly Stage for 50" must "hack" the favorite to get it to download properly. There is no way to solve this problem without referring to some centralized database of anime titles or relying on some sort of AI, neither of which are going to happen in torrentwatch-xa any time soon.

#### Items Drop Off the Feed Lists

If one starts an item downloading from a feed list, and that item is bumped off the end of the feed list by newer items on the next browser refresh, the item will not appear in the Downloaded or Downloading filtered lists even if the item still shows on the Transmission tab as downloading or downloaded. This is because the item simply is no longer in the list to be filtered and then shown by the Downloading and Downloaded filters. It seems counterintuitive until one understands that the Downloaded and Downloading filters are view filters on the feed list, not historical logs nor connected to Transmission's internal list.

#### Auto-Delete Seeded Torrents Only Works in the Browser

torrentwatch-xa uses browser-based Javascript to auto-delete seeded torrents. As such, it cannot auto-delete seeded torrents if the browser is not open and the cron job is automatically downloading items. Normally this is not an issue since the transmission-daemon will automatically delete seeded torrents. However, this feature is broken in some versions of transmission-daemon, and that results in seeded torrents piling up in Paused state. At first blush, it may seem like this is torrentwatch-xa's fault, but it is transmission-daemon's bug that is to blame.

#### Can't Match Batches

In many cases, the item's title is matched to a favorite by the detection engine, but because the item contains a batch of episodes, chapters, or volumes, the engine doesn't know how to handle it. For instance, let's say we matched a full season of a show. What should torrentwatch-xa do for the next item in the series? Does it match the first episode of the next season as soon as it comes out, going from Season 1, Episodes 1-13 to Season 2, Episode 1, _or_ does it wait until the whole second season finishes, going from Season 1 to Season 2?

However, with manga, it is obvious that the user favors downloading entire volumes (batches of chapters) in sequence, but with episodic videos, the user probably doesn't want to wait a whole season. Basically, the expectation is to get a _weekly_ download of some favorite media.

I decided not to deal with this just yet. You can always download any item you see in a feed list by manually highlighting it and clicking the Download (Play) button.


### Common Issues

##### "I created a favorite but it doesn't work, even though I see the item it should match right there. I've tried reloading the page but it just doesn't match."

See the section of the instructions called **Use the Favorites panel to set up your automatic downloads** above.

_However_ there are situations where a correctly-set-up favorite does not match items.

For instance, some items have numbering that cannot be understood by the detection engine. You can tell that these are not recognized by the lack of any sequential-item-numbering directly to the left of the datestamp on the right side.

Also, as mentioned under the **Design Decisions Explained** section, currently, the detection engine does not know how to download batches like Full Seasons or Volumes (batches of Chapters) in sequence. While the detection engine does match the title, it will not show the sequential-item-numbering for that torrent and will not download it automatically.

Remember, you can always manually download any item you see in the feed list by highlighting it and clicking the Download (Play) button.

##### Some items have obviously-incorrect sequential-item-numbering (wrong Season/Episode or Volume/Chapter)

The detection engine is good but not perfect. There are some cases where it misreads an item's sequential-item-numbering. There are some steps you can take to help me quickly fix this kind of bug:

- Look in the footer at the very bottom of the page and click "report bugs here" to go to the torrentwatch-xa Github Issues page.
- Right-click on the item that won't match correctly and choose "Inspect Element." Every modern web browser has this feature or something similar to it. It will open up the source code underlying the item.
- Look for the item title in the source code. Usually the line(s) of code that are highlighted initially are the ones that contain the title. It should look something like this:

`<span class="torrent_title" title="Torrent: https://server/path/torrentname.torrentSize: 29.5MBAuthorized: N/AMagnet Link ">Mori Komori-san wa Kotowarenai! - 12.­mkv</span>`

Copy and paste that into the bug report.

- Look for the **debugMatch** value for the item in the page source. Usually you will have to look just a few lines below the one(s) that were initially highlighted. You may have to expand some collapsed groups of lines by clicking the arrow buttons next to groups of lines. When you find the **debugMatch** value, it will look like this:

`<span class="hidden" id="debugMatch">1_1_30_1-1</span>`

Copy and paste that into the bug report.
- (optional) Cut and paste the favorite's Filter setting into the bug report.
- **Submit the bug report. _Thank you for helping to improve the season and episode detection engine._**

##### "Nothing downloads automatically, even though I see the items marked as matching and they download properly when I manually refresh the browser."

Check that you successfully copied the CRON file /etc/cron.d/torrentwatch-xa-cron, check that it is owned by root:root, and check the permissions (should be 644). 

Watch the syslog to see CRON attempt to run /etc/cron.d/torrentwatch-xa-cron:

`sudo tail -f /var/log/syslog | grep CRON`

You should see entries like these:

`Dec 20 10:00:01 hostname CRON[4493]: (www-data) CMD (/usr/bin/php -q /var/lib/torrentwatch-xa/rss_dl.php -D >/dev/null 2>&1)`

Otherwise you will likely see errors with short instructions on how to fix the problem(s).

Credits
===============

The credits may change as features and assets are removed.

- Original TorrentWatch-X by Joris Vandalon https://code.google.com/p/torrentwatch-x/
- Original Torrentwatch by Erik Bernhardson https://code.google.com/p/torrentwatch/
- I have stumbled upon some credits embedded in various files that were put there by prior coders and that will not be re-listed here.