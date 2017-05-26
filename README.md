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

I've posted 0.5.0 with the changes listed in [CHANGELOG.md](CHANGELOG.md).

Good news! The **Auto-Del Seeded Torrents** functionality of the web UI has been implemented in twxacli.php so that the cron job can delete fully-seeded, auto-downloaded torrents from Transmission. Users of non-MacOS versions of Transmission will really appreciate this big improvement, because paused, fully-seeded torrents will no longer pile up in Transmission's torrent list if the torrentwatch-xa web UI was not left running all the time to allow the Javascript Auto-Del Seeded Torrents to perform cleanup. (The makers of Transmission purposefully left auto-delete out of the non-MacOS version per enhancement issue #2353 [here](https://trac.transmissionbt.com/ticket/2353)).

Both the web UI's and twxacli.php's auto-delete feature will not delete any torrents until they have been fully downloaded and fully seeded and will not delete any torrents that were not auto-downloaded by torrentwatch-xa (i.e., that are still recorded in the download cache). In other words, you can add other torrents to Transmission outside of torrentwatch-xa, and those torrents will not be touched. This is an improvement over the web UI's past auto-delete behavior.

feeds.php was cleaned up and renamed to twxa_feed.php, and tor_client.php was cleaned up and renamed to twxa_torrent.php. The same goes for lastRSS.php and atomparser.php. With that, every single file has finally been examined and integrated into torrentwatch-xa, and there will probably be no further renames of paths or files.

Broken since TorrentWatch-X 0.8.9, the Watch Dir functionality has been completely commented out to be fully removed in a future version. transmission-daemon already has had watch directory capability built in for quite a while, so this feature is redundant. To enable the watch directory in transmission-daemon, use `watch-dir` and `watch-dir-enabled` in `settings.json`.

**For those of you upgrading from a prior version of torrentwatch-xa, you MUST either replace /etc/cron.d/torrentwatch-xa-cron OR edit it and remove the -D flag (for Process Watch Dir). Otherwise, the cron job will generate an error and fail.** The install_twxa.sh script can do this for you; note the new --keep-config option that backs up your config file to your home directory and then puts it back after installing the new torrentwatch-xa files and folders.

Also, when Add to Favorites is used to create a new Favorite, the Qualities filter is now populated intelligently according to the selected Match Style. **If upgrading, if you are keeping your old config file, you may have to correct the Qualities filters for your Favorites before they will work with 0.5.0.**

I have also started commenting out the isBatch and batch logic in the torrent functions like startTorrent(). The logic is redundant and will be fully removed in a future version.

Still in alpha since 0.4.0: a Favorite Filter can now match multibyte strings (Japanese/Chinese/Korean) in RegEx matching mode only (not Simple, nor Glob), but multibyte characters must be individually specified in PCRE Unicode hexadecimal notation like `0x{3010}` to satisfy PHP's preg_ functions.

A new, better NyaaTorrents is up at nyaa.si, but I have not added them to the default feeds until they have had more time to establish themselves and scale for capacity. *NyaaTorrents is dead, long live NyaaTorrents!*

Finally, the TODO.md list has been cleaned up of obsolete entries.

### Next Version

I hope to:

- diagnose problem with Transmission list items not matching actual transmission-daemon list
- rewrite the episode_filter() function to handle the new season and episode notation style
- rewrite PROPER/REPACK handling in the new itemVersion method
- finish twxaDebug() and $verbosity

Known bugs are tracked primarily in the [TODO.md](TODO.md) and [CHANGELOG.md](CHANGELOG.md) files. Tickets in GitHub Issues will remain separate for accountability reasons.

Tested Platforms
===============

torrentwatch-xa is developed and tested on Ubuntu 14.04.5 LTS with the prerequisite packages listed in the next section. For this testbed transmission-daemon is not installed locally--a separate NAS on the same LAN serves as the transmission server. The UI works on pretty much any modern web browser that has Javascript enabled, including smartphone and tablet browsers.

torrentwatch-xa should work without modifications on an out-of-the-box, up-to-date install of Debian 8.x x86_64, Ubuntu 14.04.x, or Ubuntu 16.04.2, although I am only actively testing on Ubuntu 14.04.x with PHP 5.6.

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

From the official repos:

- transmission-daemon
- apache2
- php-mbstring (defaults to php7.0-mbstring)
- libapache2-mod-php (defaults to libapache2-mod-php7.0)
- php (defaults to php7.0)
- php-curl (defaults to php7.0-curl)

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

### Current Episodes Filter Notation

The Episodes filter currently in each Favorite is still the old TorrentWatch-X filter. The notation style is the old style, like so:

- SxE = single episode
- SxEp = single episode, PROPER or Repack
- S1xE1-S2xE2 = batch of episodes starting in one season and ending in a later season

### RegEx Matching Style vs. Simple vs. Glob

The Favorites fields behave differently in RegEx Matching Style than in Simple or Glob in that you can use PCRE Unicode regular expressions in RegEx mode. I need to better document the finer differences between the styles here, or perhaps it is time to remove the Simple and Glob matching styles and just stick with RegEx all the time. 

### Authentication for private RSS Feeds

See the section "Only Public Torrent RSS or Atom Feeds Are Supported" in the **Design Decisions Explained** section of [TROUBLESHOOTING.md](TROUBLESHOOTING.md) for more details.

Troubleshooting
===============

See [TROUBLESHOOTING.md](TROUBLESHOOTING.md) for detailed troubleshooting steps and explanations of design decisions and common issues.


Credits
===============

- Original TorrentWatch-X by Joris Vandalon https://code.google.com/p/torrentwatch-x/
- Original Torrentwatch by Erik Bernhardson https://code.google.com/p/torrentwatch/
- Credits for the PHP and Javascript libraries are inside of their respective files.