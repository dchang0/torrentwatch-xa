![torrentwatch-xa twxa logo](http://silverlakecorp.com/torrentwatch-xa/torrentwatch-xa-logo144.png)

torrentwatch-xa
===============

torrentwatch-xa is an actively-developed fork of Joris Vandalon's abandoned TorrentWatch-X automatic episodic torrent downloader with the _extra_ capability of handling anime fansub torrents that do not have season numbers, only episode numbers. As of 0.4.0, it can also detect and auto-download print media like manga numbered by date or volume x chapter. It will continue to handle live-action TV episodes with nearly all season and episode numbering styles.

![torrentwatch-xa twxa ScreenShot 1](http://silverlakecorp.com/torrentwatch-xa/twxaScreenShot1.png)

I resurrected TorrentWatch-X because I could not make Sick Beard-Anime PVR handle anime episode numbering styles well enough for certain titles, and the TorrentWatch-X UI is far easier to use and understand for both automated and manual torrent downloads. When I forked TorrentWatch-X at version 0.8.9, it was a buggy mess, but over years of testing and development, torrentwatch-xa has proven to be the excellent set-it-and-forget-it PVR that TorrentWatch-X was always meant to be.

Without getting caught up in the feature race that other torrent downloaders seem to be stuck in, the goal is for torrentwatch-xa to do only what it's supposed to do and do it well. In the end, what we all really want is to come home to a folder full of automatically-downloaded live-action shows, anime, manga, and light novels, ready to be viewed immediately.

Status
===============

### Current Version

I've posted 0.4.1 with the changes listed in [CHANGELOG.md](CHANGELOG.md).

0.4.0 saw major changes to the season and episode detection engine, specifically big changes to the way that episodes are passed between functions and compared with Favorites. The benefit is the ability to detect and download batches.

0.4.1 is primarily about fixing bugs introduced in 0.4.0 (as well as an annoying bug introduced in 0.3.1 where highlighted items in the Transmission filter will lose the highlight after a few seconds) and moving/renaming/refactoring directories, files, functions, and variables for clarity, especially to delineate which parts belong to torrentwatch-xa and which are 3rd-party libraries. There are no major changes in 0.4.1 in terms of functionality. 

However, the directory and file renames certainly mean that **it is best to wipe out an older version of torrentwatch-xa and do a fresh install of 0.4.1 rather than do an overwrite upgrade.** For instance, the cron job file requires a change from "rss_dl.php" to "twxacli.php" or it won't work, and even the torrentwatch-xa.config file saw some changes that require either editing the file to replace all occurrences of "rss_cache" with "dl_cache" or starting over with a fresh default config and adding back feeds and Favorites via the web UI.

My apologies for the trouble--it was finally time to do away with these vestiges of TorrentWatch or TorrentWatch-X from when they could only handle RSS and not Atom feeds. I do not expect any major directory renames in the future. 

To help with this upgrade, I added a very simple install script called install_twxa.sh. **Use this at your own risk--it has `rm -fr` commands inside, which can be quite dangerous if misused.** I'll improve the install script over time.

I did finally run a PHP 7.0 compatibility checker on torrentwatch-xa and found that all the code is and has been compatible excepting the deprecated constructor function name in the 3rd-party library atomparser.php. That has been corrected in 0.4.1.

Still in alpha: a Favorite Filter can now match multibyte strings (Japanese/Chinese/Korean) in RegEx matching mode only (not Simple, nor Glob), but multibyte characters must be individually specified in PCRE Unicode hexadecimal notation like 0x{3010} to satisfy PHP's preg_ functions.

Please note that if you keep the default RSS feeds provided by a fresh default config, some of them are not 100% reliable (TokyoTosho.info goes down quite often due to high traffic). This is sadly the new normal after NyaaTorrents shut down and all its fans were forced to find new homes, at least until the rumored replacement is built and comes online.

### Next Version

I hope to:

- rewrite the episode_filter() function to handle the new season and episode notation style
- rewrite PROPER/REPACK handling in the new itemVersion method
- validate the functions in tor_client.php and feeds.php and rename these files with twxa_ prefixes


Known bugs are tracked primarily in the [TODO.md](TODO.md) and [CHANGELOG.md](CHANGELOG.md) files. Tickets in GitHub Issues will remain separate for accountability reasons.

Tested Platforms
===============

torrentwatch-xa is developed and tested on Ubuntu 14.04.5 LTS with the prerequisite packages listed in the next section. For this testbed transmission-daemon is not installed locally--a separate NAS on the same LAN serves as the transmission server. The UI works on pretty much any modern web browser that has Javascript enabled, including smartphone and tablet browsers.

torrentwatch-xa should work without modifications on an out-of-the-box install of Debian 8.x x86_64 or Ubuntu 14.04.x, although I am only actively testing on Ubuntu 14.04.x with PHP 5.6.

As of 0.4.0, it has also been confirmed to run correctly on Ubuntu 16.04.2 with PHP 7.0.15, but this is not a development target at this time.

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

Ubuntu 16.04 has PHP 7.0 and is not officially supported yet, but I have confirmed that it works as of torrentwatch-xa 0.4.1 on Ubuntu 16.04.2 with PHP 7.0.15.

From the official repos:

- transmission-daemon
- apache2
- php-mbstring (defaults to php7.0-mbstring)
- libapache2-mod-php (defaults to libapache2-mod-php7.0)
- php (defaults to php7.0)

Also of 0.4.1, torrentwatch-xa has been confirmed to be fully PHP 7.0 compliant with php7cc compatibility checker. The sole problem was deprecation of the constructor function name inside of the customized atomparser.php.

Installation
===============

See [INSTALL.md](INSTALL.md) for detailed installation steps.

Usage
===============

For the most part, torrentwatch-xa is very intuitive and self-explanatory.

### Season and Episode Notation

A quick explanation of the new season and episode notation in the "episode label" shown on each line to the left of the timestamp at the right edge of the feed list:

- SxE = single episode
- SxEv# = single episode with version number
- YYYYMMDD = single date
- S1xE1-S1-E2 = batch of episodes within one season
- YYYYMMD1-YYYYMMD2 = batch of dates
- S1xFULL = one full season
- S1xE1-S2xE2 = batch of episodes starting in one season and ending in a later season
- S1xE1v2-S2xE2v3 = batch of episodes starting in one season and ending in a later season, with version numbers

For items not recognized as having an episodic numbering, Glummy ("_ ) is displayed.

Internally, the new Favorite matching engine uses direct comparisons of the separate season and episode as discrete numeric values and does not deal with this notation at all.

Later, when the Favorite Episodes filter functionality is implemented, it will also use this notation (except for Glummy, who is for display only).

The ideal notation for videos is actually SxVxEv# (Season x Volume x Episode version #); if downloading anime BluRay Disc sets becomes super-popular, I may implement this notation style throughout torrentwatch-xa in a future version.

### Authentication for private RSS Feeds

See the **Design Decisions Explained** section of [TROUBLESHOOTING.md](TROUBLESHOOTING.md) for more details.

Troubleshooting
===============

See [TROUBLESHOOTING.md](TROUBLESHOOTING.md) for detailed troubleshooting steps and explanations of design decisions and common issues.


Credits
===============

- Original TorrentWatch-X by Joris Vandalon https://code.google.com/p/torrentwatch-x/
- Original Torrentwatch by Erik Bernhardson https://code.google.com/p/torrentwatch/
- Credits for the PHP and Javascript libraries are inside of their respective files.