Troubleshooting
===============

#### Can't add some feeds

I have found that some feeds can't be added for various reasons, including:

- rejection of SSL certificate chain
- feed format is not recognized as Atom or RSS
- can't handle HTTP redirects

Please note that I have not been able to personally test torrentwatch-xa's handling of Atom feeds due to their rarity. It seems that RSS is far more common for torrent feeds.

#### Feed is totally missing; "Feed inaccessible" in log

From the NyaaTorrents shutdown cascading to Feedburner's Anime (Aggregated) feed, I learned the hard way that if the feed URL is not even valid (usually returning a 404 or 403 HTTP status code), and the UI will never get the chance to show the section header that says "Feed is not available" in red. I will have to rewrite certain functions to handle this, but for now, if a feed is totally missing from the list, first, wait for the cron job to force a reload of the feed cache, then refresh the browser, which should then show the missing feed. If not, check the log file, which might say "Feed inaccessible: " instead of "Feed down: ". If it says "Feed inaccessible," the feed URL is likely not valid or experiencing serious server problems or traffic congestion.

#### Can't handle compressed torrent files

Some feeds link to some torrent files on them that are compressed (usually gzipped). I do not plan to fix this because it is usually very easy to find the same media or content via some other torrent file that is not compressed, possibly even on the same feed.

#### Email notifications not actually sending (SMTP errors in the log file)

SMTP sending is done via PHPMailer 5.2.23. You may need to refer to PHPMailer documentation for help in understanding any SMTP error messages that appear. See https://github.com/PHPMailer/PHPMailer

Typically, you should double-check the following:

- From Email is valid and correct (if left blank or it is invalid, it defaults to To Email)
- To Email addresses is valid and correct
- SMTP Server is valid and correct
- SMTP Port number is correct (if left blank, it defaults to port 25). Typically SSL uses port 465 and TLS uses port 587.
- SMTP Authentication is usually PLAIN but might be LOGIN. torrentwatch-xa does not support other authentication methods such as NTLM, etc.
- SMTP Encryption is usually TLS. SSL is obsolete and None (no encryption) is banned on most SMTP servers.

There is one SMTP setting that can affect sending that is not accessible via the web UI: the SMTP HELO field. torrentwatch-xa attempts to automatically generate a valid HELO field based on your From Email, but if the value it provides to the SMTP server is rejected, you may need to modify the source code to set the value.

#### Allowed memory size of ... exhausted

PHP memory_limit may be too low to handle some of the larger feeds. Edit your php.ini file for Apache2 (typically /etc/php5/apache2/php.ini) and increase the size of memory_limit to something reasonable for your system.

#### apache2 process dies with AH00052: child pid ... exit signal Segmentation fault (11)

This bug appears to have gone away on its own with recent PHP 7.0 updates to Ubuntu 16.04.x LTS. At the time of the successful testing, I was running torrentwatch-xa 0.4.0. If you get this error in your Apache2 error log, try updating PHP 7.0 to 7.0.15 or later first. If you still get the error, try upgrading to torrentwatch-xa 0.4.0 or later.

#### "I created a Favorite but it doesn't work, even though I see the item it should match right there. I've tried reloading the page but it just doesn't start the auto-download."

First, please review the section of [INSTALL.md](INSTALL.md) called **Use the Favorites panel to set up your automatic downloads**. You will know whether a Favorite matches properly if it causes items to show up in the Matching filter.

Second, check the Favorite's Quality filter and make sure it's not too restrictive, then make sure there are no typos in any of the Favorite's fields.

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

- Look for the **debugMatch** value for the item in the page source. Usually you will have to look just a few lines below the one(s) that were initially highlighted. You may have to expand some collapsed groups of lines by clicking the arrow buttons next to groups of lines. When you find the **debugMatch** value, it will look like this:

`<span class="hidden" id="debugMatch">1_1_30_1-1</span>`

Copy and paste that into the bug report.
- (optional) Cut and paste the favorite's Filter setting into the bug report.
- Submit the bug report. _Thank you for helping to improve the season and episode detection engine!_

#### "Nothing downloads automatically, even though I see the items marked as matching and they download properly when I manually refresh the browser."

Check that you successfully copied the CRON file /etc/cron.d/torrentwatch-xa-cron, check that it is owned by root:root, and check the permissions (should be 644). 

Watch the syslog to see CRON attempt to run /etc/cron.d/torrentwatch-xa-cron:

`sudo tail -f /var/log/syslog | grep CRON`

You should see entries like these:

`Dec 20 10:00:01 hostname CRON[4493]: (www-data) CMD (/usr/bin/php -q /var/lib/torrentwatch-xa/rss_dl.php -D >/dev/null 2>&1)`

Otherwise you will likely see errors with short instructions on how to fix the problem(s).