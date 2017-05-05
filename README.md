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

I've posted 0.4.0 with the changes listed in [CHANGELOG.md](CHANGELOG.md).

The long-awaited switch from the old season and episode detection engine to the new is finally resuming!

Broadly speaking, there are two major changes ongoing in this gradual switch:

1. The method of passing and comparing season and episode data has changed. The old engine passed season and episode data between its functions in human-friendly SxE notation such as 1x3. This meant that it translated in and out of this notation during each comparison of the record of the last-downloaded episode of a Favorite with a matching item in the feed list. The new engine dispenses with the human-friendly notation and passes season and episode data as discrete numbers.

2. The human-friendly season and episode notation is being updated to handle the new numeric-only item version numbering system. The old engine tracked only PROPER/REPACK versions (effectively the final version of a given item) and referred to PROPER episodes with a "p" at the end of the episode number, as in 1x3p. It did not track item versions numerically, such as v2, v3, and so on. The new engine tracks numeric item versions, so the human-friendly notation is being changed to SxEvV. For instance 1x3v4 means Season 1, Episode 3, Version 4 (of Episode 3). PROPER/REPACK versions will be translated into a numeric value, probably version 99. The old "p" notation will never return.

Switching from the old style of passing and comparing season and episode data to the newer style brings in 0.4.0 two awesome new features:

a. The ability to detect and auto-download batches that contain episodes newer than the last downloaded episode. Thus, if the latest you have is episode 1x8, and a batch 1x5-1x10 comes out, it will download the entire batch to get episodes 1x9 and 1x10. If you do not wish to download batches, set the option Configure > Favorites > Ignore Batches. Batch downloading is very new and only partially implemented, so it may be very buggy. It will be rolled out in chunks and tested over the long term.

b. The ability to detect and auto-download print media, especially manga!

PROPER/REPACK handling is currently disabled during the ongoing transition to the new season and episode notation. Essentially, PROPER/REPACK versions are temporarily being ignored while numeric item versions are now being recognized. Configure > Favorites > Download PROPER/REPACK has been renamed to Configure > Favorites > Download Versions >1. PROPER/REPACK handling will be restored in a future version of torrentwatch-xa.

Also in 0.4.0, I dramatically changed the way the titles are processed:

- The detectItem() logic was drastically reorganized to allow code reuse, changing the order of several matchTitle functions. I expect this to introduce many season and episode detection errors, but the alternative was that I'd end up maintaining an ever-growing list of mostly redundant functions.
- In many matchTitle functions, the regex used to detect the season and episode numbering is being reused to remove the season and episode numbering and undetected codecs, leaving behind the generated show_title (aka favTitle or 'favTi'). This should reduce bugs in the show_title.
- Many regexes were improved to include more languages and catch more abbreviations. This will probably result in a net gain of bugs, but we may never know since NyaaTorrents is permanently shut down, and NyaaTorrents had the widest and most challenging range of season and episode numbering styles.

I added a few features to the UI to make it easier to debug the season and episode detection engine. First, every item's episode label now has mouse-hover text that displays the debugMatch value. Each item's debugMatch value is still hidden in the source code as described in the Troubleshooting instructions, but the mouse-hover text makes it easier to see. In addition, there is now the option to display both debugMatch and show_title per line in the feed lists: Configure > Interface > Show Item Debug Info. show_title is generated from the item title by removing all the detected codecs, qualities, and episodic numbering. What is left behind is supposed to be just the show title to be used for various tasks such as checking the download cache and setting the initial Filter value when Add to Favorites is clicked. show_title is important and generating it correctly is key; making it visible will help you debug problems getting Favorites to match items in the feed list that it should be matching and help you craft better Favorite Filters.

Another big new feature is the ability to enable or disable individual feeds in Configure > Feeds. Note that after re-enabling a feed, the browser must be refreshed to see the feed return to the list.

Broken long ago in TorrentWatch-X and carried over into torrentwatch-xa, the 'Require Episode Info' feature is now finally repaired. If it is unchecked, the season and episode number comparisons are completely bypassed so that Favorites can match items with or without episode numbering. This is useful for collectors who want every single item or batch that matches a given set of Favorite Filter, Not and Qualities filters.

Finally, I reduced the number of item states and made sure the ones that are used do show up properly according to the Legend.

In alpha: a Favorite Filter can now match multibyte strings (Japanese/Chinese/Korean) in RegEx matching mode only (not Simple, nor Glob), but multibyte characters must be individually specified in PCRE Unicode hexadecimal notation like 0x{3010} to satisfy PHP's preg_ functions.

### Next Version

I hope to:

- rewrite the episode_filter() function to handle the new season and episode notation style
- add more pattern detectors and fix any bugs introduced by the major reorganization in 0.4.0
- rewrite PROPER/REPACK handling in the new itemVersion method
- improve performance by breaking functions into smaller ones and calling just the necessary functions

Known bugs are tracked primarily in the [TODO.md](TODO.md) and [CHANGELOG.md](CHANGELOG.md) files. Tickets in GitHub Issues will remain separate for accountability reasons.

Tested Platforms
===============

torrentwatch-xa is developed and tested on Ubuntu 14.04.5 LTS with the prerequisite packages listed in the next section. For this testbed transmission-daemon is not installed locally--a separate NAS on the same LAN serves as the transmission server. The UI works on pretty much any modern web browser that has Javascript enabled, including smartphone and tablet browsers.

torrentwatch-xa should work without modifications on an out-of-the-box install of Debian 8.x x86_64 or Ubuntu 14.04.x, although I am only actively testing on Ubuntu 14.04.x with PHP 5.6.

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

Installation
===============

See [INSTALL.md](INSTALL.md) for detailed installation steps.

Usage
===============

For the most part, torrentwatch-xa is very intuitive and self-explanatory. 

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

Troubleshooting
===============

See [TROUBLESHOOTING.md](TROUBLESHOOTING.md) for detailed troubleshooting steps and explanations of design decisions and common issues.


Credits
===============

- Original TorrentWatch-X by Joris Vandalon https://code.google.com/p/torrentwatch-x/
- Original Torrentwatch by Erik Bernhardson https://code.google.com/p/torrentwatch/
- Credits for the PHP and Javascript libraries are inside of their respective files.