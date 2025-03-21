Troubleshooting
===============

#### Error messages and logs

There are four places to check for error messages:

- a pop-up in the web browser (typically configuration errors such as permissions problems or unreachable paths)
- web server error log: /var/log/apache2/error.log (all PHP errors, warnings, and notifications)
- torrentwatch-xa error log: /var/log/torrentwatch-xa.log (all errors in torrentwatch-xa logic such as failures to process feeds or auto-download items)
- web browser Javascript console (Javascript errors only, typically minor errors in torrentwatch-xa's web UI)

#### If you change the installation path(s)

If you change the base or web directories' paths, you must also:

- Change the paths in get_webDir() and get_baseDir() in config.php (default location is /var/www/html/torrentwatch-xa/config.php as of 1.0.0)
- Change the path to twxa_cli.php in the cron file torrentwatch-xa (default location is /etc/cron.d/torrentwatch-xa)

#### Browser shows entirely or mostly blank page

This is almost always due to missing PHP packages or functions OR problems with the config file or config cache directory. Check the web server error log for more details.
With php-curl 8.3 or newer, if the page is mostly blank and can't connect to Transmission, make sure you upgrade torrentwatch-xa to version 1.9.4 or later.

#### Can't add some feeds

Some feeds can't be added for various reasons including:

- rejection of SSL certificate chain
- feed format is not recognized as Atom or RSS
- can't handle HTTP redirects or DDoS blockers like CloudFlare or DDoS-GUARD

#### Can't handle compressed torrent files

Some feeds link to some torrent files on them that are compressed (usually gzipped). I do not plan to fix this because it is usually very easy to find the same content via some other torrent file that is not compressed, possibly even on the same feed.

#### Email notifications not sending

1.4.0 added a Test button to test the SMTP settings in the Configure > Trigger tab. Any errors will be shown to the right of the Test button.

The error messages for these fields are obvious:

- From: Email
- To: Email
- SMTP Port is either blank (defaults to 25) or must be an integer from 0 to 65535

If you get "SMTP connect() failed," you should double-check all of these:

- SMTP Server is valid and correct.
- SMTP Port number is correct. Typically SSL uses port 465, and TLS uses port 587.
- SMTP Authentication is usually PLAIN but might be LOGIN. torrentwatch-xa does not support other authentication methods such as NTLM.
- SMTP Encryption is usually TLS. SSL is obsolete, and None (no encryption) is banned on most SMTP servers.
- SMTP User and Password are correct.

If you get more cryptic error messages than that, you may need to refer to the PHPMailer documentation here: https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting

If your SMTP server rejects the default HELO calculated by PHPMailer, use the optional HELO Override to satisfy the SMTP server. If provided, HELO Override must be a valid FQDN.

Remember that the Test button does not save the current SMTP settings; click Save to write the settings to the config file.

IMPORTANT: The Test button and the cron job both use the same SMTP settings saved in the config file, _but_ because the Test button runs in the web server and the cron job runs at the LINUX command line, they can and do behave differently. For instance, PHPMailer uses $_SERVER['SERVER_NAME'] to calculate the HELO, but this variable is not available to the cron job.

Thus, even if your settings work with the Test button, they might not work with the cron job. The cron job's SMTP errors are written to the log file, so if the automatic downloads are not sending you email notifications, you will have to check there.

#### Allowed memory size of ... exhausted

PHP memory_limit may be too low to handle some of the larger feeds or if you have many feeds. Edit your php.ini file for Apache2 (typically /etc/php/7.0/apache2/php.ini) and increase the size of memory_limit to something reasonable for your system.


#### "I created a Favorite but it doesn't work, even though I see the item it should match in the feed list. I've tried reloading the page but it just doesn't start the auto-download."

First, please review the section of [INSTALL.md](INSTALL.md) called **Use the Favorites panel to set up your automatic downloads**. You will know whether a Favorite matches properly if it causes items to show up in the Matching filter.

Second, check the Favorite's Quality filter and make sure it's not too restrictive, then make sure there are no typos in any of the Favorite's fields.

Third, check torrentwatch-xa's log (typically /var/log/torrentwatch-xa.log) for errors.

If you have followed the instructions correctly and are still having trouble, turn on Configure > Interface > Show Item Debug Info and refresh the browser so that you can see the show_title that must be matched by your Favorite Filter. You will likely find a typo in your Favorite's Filter that needs correcting.

_However_ there _are_ situations where a correctly-set-up favorite does not match items.

For instance, some items have numbering that cannot be understood by the detection engine. You can tell that these are not recognized by the lack of any season and episode notation directly to the left of the datestamp on the right side. Instead, Glummy ("_ ) is displayed.

Also, see **Item Says It's an Old Favorite but is Actually New and Should Be Downloaded** in the **Design Decisions Explained** section.

Remember, you can always manually download any item you see in the feed list by highlighting it and clicking the Download (Play) button.

#### Some items have obviously-incorrect detected sequential-item-numbering (wrong Season/Episode or Volume/Chapter)

The detection engine is good but not perfect. There are some cases where it misreads an item's sequential-item-numbering. There are some steps you can take to help me quickly fix this kind of bug:

- Look in the footer at the very bottom of the page and click "report bugs here" to go to the torrentwatch-xa Github Issues page.
- Right-click on the item that won't match correctly and choose "Inspect Element." Every modern web browser has this feature or something similar to it. It will open up the source code underlying the item.
- Look for the item title in the source code. Usually the line(s) of code that are highlighted initially are the ones that contain the title. It should look something like this:

`<span class="torrent_title" title="Torrent: https://server/path/torrentname.torrentSize: 29.5MBAuthorized: N/AMagnet Link ">Mori Komori-san wa Kotowarenai! - 12.­mkv</span>`

Copy and paste that into the bug report.

- Look for the **debugMatch** value for the item in the source code. Usually you will have to look just a few lines below the one(s) that were initially highlighted. You may have to expand some collapsed groups of lines by clicking the arrow buttons next to groups of lines. When you find the **debugMatch** value, it will look like this:

`<span class="debugMatch hidden" id="debugMatch">1_1_30_1-1</span>`

Copy and paste that into the bug report.
- (optional) Cut and paste the favorite's Filter setting into the bug report.
- Submit the bug report. _Thank you for helping to improve the season and episode detection engine!_

#### "Nothing downloads automatically, even though I see the items marked as matching and they download properly when I manually refresh the browser."

Make sure you have installed the cron daemon and that it is running. Some minimal installations do not include cron.

Check that you successfully copied the CRON file /etc/cron.d/torrentwatch-xa, check that it is owned by root:root, and check the permissions (should be 644).

Watch the syslog to see CRON attempt to run /etc/cron.d/torrentwatch-xa:

`sudo tail -f /var/log/syslog | grep CRON`

You should see entries like these:

`Dec 20 10:00:01 hostname CRON[4493]: (www-data) CMD (/usr/bin/php -q /var/www/html/torrentwatch-xa/twxa_cli.php -D >/dev/null 2>&1)`

Otherwise you will likely see errors with short instructions on how to fix the problem(s).

It may also be necessary to check torrentwatch-xa's log file (typically /var/log/torrentwatch-xa.log) for other errors preventing the auto-download.

#### "Invalid or corrupt torrent file"

Very rarely, a zero-length or corrupt torrent file will be downloaded from the RSS feed, producing this error. If the item is still in the feed and the Favorite does not think it has already downloaded the item, the easiest way to solve this is to clear all caches, which should trigger the auto-download of the torrent file from the RSS feed and the the auto-download of the torrent in Transmission.

If you don't want to clear your caches, you can manually delete the individual torrent file from the download cache.

If the Favorite already thinks it has downloaded the item, you will need to change the season and episode in the Favorite to trigger, then clear the cache.

If the RSS feed is still serving a corrupt torrent file, there is not much you can do about it except find a different source for the same item.

#### Time zone setting is not taking effect

After setting the time zone in Configure > Interface > Time Zone, it may be necessary to do any or all of the following to get the time zone to take effect:

- Clear feed caches
- Restart web server
- Double-check that the time zone setting is valid--invalid time zones will generate errors in the web server error log

It still might not be possible to change the timestamps in the RSS/Atom feeds themselves if they are hardcoded into the feed.

#### transmission-daemon won't start on Ubuntu 24.04

While upgrading from Ubuntu 22.04 to 24.04, I ran into this breaking bug:

transmission-daemon was being blocked from starting by AppArmor. To fix it, you need to edit /etc/apparmor.d/transmission and change this line:

`profile transmission-daemon /usr/bin/transmission-daemon flags=(complain) {`

to this:

`profile transmission-daemon /usr/bin/transmission-daemon flags=(complain,attach_disconnected) {`

Then reboot for it to take effect. You can also force AppArmor to parse its files to avoid a restart.