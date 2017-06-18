![torrentwatch-xa twxa logo](http://silverlakecorp.com/torrentwatch-xa/torrentwatch-xa-logo144.png)

torrentwatch-xa
===============

torrentwatch-xa is an actively-developed fork of Joris Vandalon's abandoned TorrentWatch-X automatic episodic torrent downloader with the _extra_ capability of handling anime fansub torrents that do not have season numbers, only episode numbers. As of 0.4.0, it can also detect and auto-download some print media like manga numbered by date or volume x chapter. It will continue to handle live-action TV episodes with nearly all season and episode numbering styles.

![torrentwatch-xa twxa ScreenShot 1](http://silverlakecorp.com/torrentwatch-xa/twxaScreenShot1.png)

I resurrected TorrentWatch-X because I could not make Sick Beard-Anime PVR handle anime episode numbering styles well enough for certain titles, and the TorrentWatch-X UI is far easier to use and understand for both automated and manual torrent downloads. When I forked TorrentWatch-X at version 0.8.9, it was a buggy mess, but over years of testing and development, torrentwatch-xa has proven to be the excellent set-it-and-forget-it PVR that TorrentWatch-X was always meant to be.

Without getting caught up in the feature race that other torrent downloaders seem to be stuck in, the goal is for torrentwatch-xa to do only what it's supposed to do and do it well. In the end, what we all really want is to come home to a folder full of automatically-downloaded live-action shows, anime, manga, and light novels, ready to be viewed immediately.

Status
===============

### Current Version

I've posted 0.6.0 with the changes listed in [CHANGELOG.md](CHANGELOG.md).

0.6.0 features a major rewrite of the getClientData and processClientData Javascript functions in the web UI so that the Transmission filter is two-way synchronized with Transmission's torrent list. This change was made to address a rare problem introduced in 0.5.0 by the improvement to _Auto-Del Seeded Torrents_ like so:

- The web UI is open in a browser, and an active torrent is completely downloaded and has just finished fully seeding.
- With _Auto-Del Seeded Torrents_ enabled, twxacli.php (run by the cron job) deletes the fully-seeded torrent before the web UI can do so.
- The web UI is unable to auto-delete the torrent because it no longer exists in Transmission, so its item is left on display in the web UI until the browser is refreshed.

With the new two-way sync, if you have the web UI open all the time, you will see torrents auto-downloaded by twxacli.php show up in real-time in the Transmission filter and then disappear when auto-deleted by either the web UI or twxacli.php, whichever gets there first.

Also, torrents added to Transmission by means other than torrentwatch-xa also show up in the Transmission filter and can be managed there. These will never be auto-deleted (a behavior added in 0.5.0's Auto-Del Seeded Torrents improvement) and must be manually deleted.

As part of the processClientData rewrite, all list item states were examined and then cleaned up:

- All states' prefixes were changed for clarity.
- Some redundant states were completely or partially merged into other states, resulting in their removal from the legend.
- State change logic was improved in several places.
- The state tc_seeding was added for consistency, though it isn't visible by legend color.

I finally fixed a bug carried over from TorrentWatch-X 0.8.9 where the context menu's slideUp("fast") behavior did not have time to completely put away the menu after clicking the Download context menu item, leaving a randomly shorter height for that context menu that would endure for the rest of the browser session and would be reset only by a browser refresh. This bug would effectively cut off all but the first context menu item.

Another long-overdue and sorely-needed fix for a bug from TorrentWatch-X: the button bar is now correctly positioned on smartphone screens just above the bottom edge of the screen.

To the *Also Save Torrent Files* feature, I added the *Configure > Torrent > Save Torrent Files Dir* setting to solve the problem of connecting to a remote Transmission client and being unable to save .torrent files to the *Download Dir* on the remote system. With *Save Torrent Files Dir*, the .torrent files are written to any accessible local system path, which may be an NFS mount to a remote path. This is not the same functionality as *Client: Save Torrent In Folder* that does not use Transmission at all; *Also Save Torrent Files* uses Transmission to download the torrent _and also_ saves the .torrent file in the *Save Torrent Files Dir*.

And finally, the overall look and feel has been flattened and modernized.

Still in alpha since 0.4.0: a Favorite Filter can now match multibyte strings (Japanese/Chinese/Korean) in RegEx matching mode only (not Simple, nor Glob), but multibyte characters must be individually specified in PCRE Unicode hexadecimal notation like `0x{3010}` to satisfy PHP's preg_ functions.

### Next Version

I hope to:

- fix web UI behavior for *Client: Save Torrent In Folder*
- continue cleaning up or improving old code (still about half of torrentwatch-xa.js and several functions in twxa_feed.php and twxa_torrent.php need improvement).
- shorten the time to the first firing of getClientData after a browser refresh
- start comprehensive testing of the _Client: Save torrent in folder_ feature, which may require readjustment of the list item states
- rewrite the episode_filter() function to handle the new season and episode notation style
- rewrite PROPER/REPACK handling in the new itemVersion method
- finish twxaDebug() and $verbosity

Known bugs are tracked primarily in the [TODO.md](TODO.md) and [CHANGELOG.md](CHANGELOG.md) files. Tickets in GitHub Issues will remain separate for accountability reasons.

Tested Platforms
===============

torrentwatch-xa is developed and tested on Ubuntu 14.04.5 LTS with the prerequisite packages listed in the next section. For this testbed transmission-daemon is not installed locally--a separate NAS on the same LAN serves as the transmission server. The UI works on pretty much any modern web browser that has Javascript enabled, including smartphone and tablet browsers.

torrentwatch-xa should work without modifications on an out-of-the-box, up-to-date install of Debian 8.x x86_64, Ubuntu 14.04.x, or Ubuntu 16.04.2, although I am only actively testing on Ubuntu 14.04.x with PHP 5.6.

As of 0.4.0, it has also been confirmed to run correctly on Ubuntu 16.04.2 with PHP 7.0.15, but this is not a development target at this time.

For those of you considering installing torrentwatch-xa on an ARM-based single-board computer like the Raspberry Pi, the primary development and testing system is actually an ODROID C1+ (32-bit armv7l).

Nearly all the debugging features are turned on and will remain so for the foreseeable future.

Be aware that I rarely test the GitHub copy of the code; I test using my local copy, and I rarely do wipe-and-reinstall torrentwatch-xa testing. So it is possible that permissions and file ownership differences may break the GitHub copy without my knowing it.

Prerequisites
===============

### Ubuntu 14.04 and Debian 8.x

From the official repos:

- transmission-daemon
- apache2 (currently Apache httpd 2.4.10)
- php5 (currently PHP 5.6)
- php5-json
- php5-curl

### Ubuntu 16.04

From the official repos:

- transmission-daemon
- apache2
- php-mbstring (defaults to php7.0-mbstring)
- libapache2-mod-php (defaults to libapache2-mod-php7.0)
- php (defaults to php7.0)
- php-curl (defaults to php7.0-curl)

Installation
===============

See [INSTALL.md](INSTALL.md) for detailed installation steps or important notes if you are upgrading from a prior version.

Usage
===============

See [USAGE.md](USAGE.md) for usage notes and an explanation of some design decisions.

Troubleshooting
===============

See [TROUBLESHOOTING.md](TROUBLESHOOTING.md) for detailed troubleshooting steps and explanations of design decisions and common issues.


Credits
===============

- Original TorrentWatch-X by Joris Vandalon https://code.google.com/p/torrentwatch-x/
- Original Torrentwatch by Erik Bernhardson https://code.google.com/p/torrentwatch/
- Credits for the PHP and Javascript libraries are inside of their respective files.