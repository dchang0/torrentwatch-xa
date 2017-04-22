Troubleshooting
===============

#### Can't add some feeds

I have found that some feeds can't be added for various reasons, including:

- rejection of SSL certificate chain
- feed format is not recognized as Atom or RSS
- can't handle HTTP redirects

Please note that I have not been able to personally test torrentwatch-xa's handling of Atom feeds due to their rarity. It seems that RSS is far more common for torrent feeds.

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

PHP memory_limit may be too low to handle some of the larger feeds. Edit your php.ini file (typically /etc/php5/apache2/php.ini) and increase the size of memory_limit to something reasonable for your system.

#### apache2 process dies with AH00052: child pid ... exit signal Segmentation fault (11)

This reproducible bug seems to only happen with PHP 7.0, not PHP 5.6.

As of 0.2.5, there is a reproducible bug with torrentwatch-xa and PHP 7.0 on Ubuntu 16.04.x. If all the caches are emptied and then the browser is refreshed, there will certainly be a SEGFAULT in the Apache2 error log, and there may be a blank white page in place of the feeds list. Another browser refresh will bring the feeds list back. 

However, it appears that a bug, perhaps the same one or a different one, may prevent the recording of the latest downloaded episode in each Favorite in the config file. That causes torrentwatch-xa to re-attempt downloading torrents that were successfully downloaded once.

I've looked at Apache2 core dumps using php-src/.gdbinit dump_bt but did not get any helpful PHP function traces. Right now, I cannot tell whether the bug(s) is wholly within PHP 7.0 on Ubuntu 16.04.x and merely consistently triggered by torrentwatch-xa or a bug wholly in torrentwatch-xa. I have checked the migration guide from PHP 5.6 to PHP 7.0, and torrentwatch-xa does not appear to be using any deprecated PHP code that is incompatible with PHP 7.0. It is supposed to work, and yet it doesn't.

The good news is that I have definitely verified 0.2.5 and up work perfectly on Ubuntu 14.04.5 with PHP 5.6. Avoid any OS with PHP 7.0 until I figure this SEGFAULT out. It may end up that skipping over PHP 7.0 to PHP 7.1 solves the problem.

#### "I created a favorite but it doesn't work, even though I see the item it should match right there. I've tried reloading the page but it just doesn't match."

See the section of the instructions called **Use the Favorites panel to set up your automatic downloads**.

_However_ there are situations where a correctly-set-up favorite does not match items.

For instance, some items have numbering that cannot be understood by the detection engine. You can tell that these are not recognized by the lack of any sequential-item-numbering directly to the left of the datestamp on the right side. Also, see **Item Says It's an Old Favorite but is Actually New and Should Be Downloaded** in the **Design Decisions Explained** section.

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
- **Submit the bug report. _Thank you for helping to improve the season and episode detection engine._**

#### "Nothing downloads automatically, even though I see the items marked as matching and they download properly when I manually refresh the browser."

Check that you successfully copied the CRON file /etc/cron.d/torrentwatch-xa-cron, check that it is owned by root:root, and check the permissions (should be 644). 

Watch the syslog to see CRON attempt to run /etc/cron.d/torrentwatch-xa-cron:

`sudo tail -f /var/log/syslog | grep CRON`

You should see entries like these:

`Dec 20 10:00:01 hostname CRON[4493]: (www-data) CMD (/usr/bin/php -q /var/lib/torrentwatch-xa/rss_dl.php -D >/dev/null 2>&1)`

Otherwise you will likely see errors with short instructions on how to fix the problem(s).

Design Decisions Explained
===============

"One man's bug is another man's feature."

It's become obvious that there are situations for which a mutually-exclusive design decision cannot be avoided. The below are design decisions that will never be "fixed."

#### Only Public Torrent RSS or Atom Feeds Are Supported

I have found that due to the highly fluid nature of the torrent scene, it's better to stick with public torrent RSS or Atom feeds than deal with the many different authentication systems of private torrent feeds. Just about everything you could want is going to be available via multiple public torrent feeds anyway.

#### Some Numbering Schemes Only Make Sense to Humans

The title "Holly Stage for 50 - 3" is meant to be interpreted as title = "Holly Stage for 50" and Episode 3, with Season 1 implied.
(Fans know that "Holly Stage for 50 - 3" really should be read as title = "Holly Stage for 49", Season 2, Episode 3, to further complicate matters.)
But the engine currently reads it as title = "Holly Stage for" and Season 50, Episode 3. Why? Because it was determined that the ## - ## pattern much more often means Season ## - Episode ##.

Sadly, because the engine was forced to make the choice, fans of "Holly Stage for 50" must "hack" the favorite to get it to download properly. There is no way to solve this problem without referring to some centralized database of anime titles or relying on some sort of AI, neither of which are going to happen in torrentwatch-xa any time soon.

#### Item Says It's an Old Favorite but is Actually New and Should Be Downloaded

This can happen if there are parallel numbering styles for the same torrent. For instance, with HorribleSubs Boku no Hero Academia 17 (Season 1, Episode 17), some crew on the Feedburner Anime (Aggregated) feed was re-releasing it later as Season 2, Episode 4. What happened then was that once torrentwatch-xa saw the Season 2 track, it jumped onto it and began ignoring the Season 1 numbering. The Season 1-numbered episodes would come out earlier each week and not be downloaded.

I am not sure I should fix this. Technically, the season and episode detection engine is working properly; it's the crew that was renumbering episodes that was causing problems. The episode would download once the Season 2 renumbering was released. The only way I can think of fixing this is to have a setting that locks the Favorites into a season, but this means that there won't be smooth transitions from one season to the next. More people are going to want the latter than experience the renumbering, so I'll leave this problem unsolved.

#### Items Drop Off the Feed Lists

If one starts an item downloading from a feed list, and that item is bumped off the end of the feed list by newer items on the next browser refresh, the item will not appear in the Downloaded or Downloading filtered lists even if the item still shows on the Transmission tab as downloading or downloaded. This is because the item simply is no longer in the list to be filtered and then shown by the Downloading and Downloaded filters. It seems counterintuitive until one understands that the Downloaded and Downloading filters are view filters on the feed list, not historical logs nor connected to Transmission's internal list.

#### Auto-Delete Seeded Torrents Only Works in the Browser

torrentwatch-xa uses browser-based Javascript to auto-delete seeded torrents. As such, it cannot auto-delete seeded torrents if the browser is not open and the cron job is automatically downloading items. Normally this is not an issue since the transmission-daemon will automatically delete seeded torrents. However, this feature is broken or missing in some versions of transmission-daemon, and that results in seeded torrents piling up in Paused state. At first blush, it may seem like this is torrentwatch-xa's fault, but it is transmission-daemon's bug that is to blame.

