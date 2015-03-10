![torrentwatch-xa TWXA logo](http://silverlakecorp.com/torrentwatch-xa/torrentwatch-xa-logo144.png)

torrentwatch-xa
===============

torrentwatch-xa is a fork of Joris Vandalon's TorrentWatch-X automatic episodic torrent downloader with the extra capability of handling anime fansub torrents that do not have season numbers, only episode numbers.

To restrict the development and testing scopes in order to improve quality assurance, I am focusing on Debian 7.x LINUX as the only OS and on Transmission as the only torrent client.

In the process of customizing torrentwatch-xa to fit my needs and workflow, I'll:

- fix some bugs
- refactor some code
- add some features, mostly UI and workflow improvements
- let some features languish or remove them outright, especially buggy/unreliable portions of the code
 
The end goal is for torrentwatch-xa to do only what it's supposed to do and do it well. Over time, this will mean that broken or aging features related to non-anime torrents will probably be removed rather than repaired. While such features still work, they will remain.

Status and Announcements
===============

CURRENT VERSION: I've posted 0.1.0 with the changes listed in CHANGELOG.

NEXT VERSION: As of 2015/03/08, for 0.1.1, I have finished most of the "only one number detected" portion of the season and episode detection engine and put it through extensive testing.
It is this portion of the engine that catches the most anime episodes, so it is the most valuable part of the engine.

As of 2015/03/09, for 0.1.1, I largely finished the "two numbers detected" portion that is responsible for detecting SSxEE notation and so on and have turned to the third portion for "three numbers detected" that handles dates like YYYY.MM.DD. The "three numbers detected" portion will take quite a while to improve and test, as it deals with the most unruly titles.

Known Bugs
===============

There is only one bug that I have found in 0.1.0 that is annoying to me, and it was carried over from TorrentWatch-X: the delete torrent buttons behave the same as the trash torrent buttons. I have fixed this for the 0.1.1 release but will not repair it in 0.1.0.

I have found a second bug in 0.1.0 that is not quite so annoying: the "Episodes Only" checkbox in the configuration panel doesn't seem to do anything. This too appears to be carried over from TorrentWatch-X, and it may be fixed in 0.1.1 or postponed till 0.1.2.

One other small bug carried over from TorrentWatch-X is that the PHP-based Downloading/Downloaded state is incorrect so that Downloaded items end up in the Downloading filter.

And one feature that must be added because the lack of it is quite annoying is the automatic removal of Downloaded and seeded torrents.

"One man's bug is another man's feature."
---

It's become obvious that there are situations that for a mutually-exclusive design decision that cannot be avoided. For example, the title "Holly Stage for 50 - 3" is meant to be interpreted as title = "Holly Stage for 50" and episode number 3, with season 1 implied.
(Fans know that "Holly Stage for 50 - 3" really should be read as title = "Holly Stage for 49", season 2, episode 3, to further complicate matters.)
But the engine currently reads it as title = "Holly Stage for" and season 50, episode 3. Why? Because it was determined that the ## - ## pattern much more often means SS - EE.

Sadly, because the engine was forced to make the choice, fans of "Holly Stage for 50" must "hack" the Favorite to get it to download properly. There is no way to solve this problem without referring to some centralized database of anime titles or relying on some sort of AI, neither of which are going to happen in torrentwatch-xa any time soon.

Tested Platforms
===============

0.1.0 works on my out-of-the-box install of Debian 7.8 x86_64 with its OOB transmission-daemon, Apache2, and PHP5.4 packages. I have tested it using the local transmission-daemon as well as a remote transmission-daemon running on a separate NAS on the same LAN.

Nearly all the debugging features are turned on and will remain so for the foreseeable future.

Be aware that I am not currently testing the GitHub copy of the code--I test using my local copy. So it is possible that permissions and file ownership differences may break the GitHub copy without my knowing it.

Prerequisites
===============

The following packages are provided by the official Debian 7 wheezy repos:

transmission-daemon
apache2
php5

They were installed just as they are, out-of-the-box.

Credits
===============

The credits may change as features and assets are removed.

- Original TorrentWatch-X by Joris Vandalon
- Original Torrentwatch by Erik Bernhardson
- Original Torrentwatch CSS styling, images and general html tweaking by Keith Solomon http://reciprocity.be/
- Icons, by David Vignoni, are from Nuvola-1.0 available under the LGPL http://icon-king.com/
- Backgrounds and CSS Layout are borrowed from Clutch http://www.clutchbt.com/
