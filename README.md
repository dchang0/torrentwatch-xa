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

I've posted 0.1.0 with the changes listed in CHANGELOG.

As of 2014/11/20, for 0.1.1, I am in the midst of a second rewrite of the season and episode detection engine because the first engine rewrite released in 0.1.0 was forcing the most popular episode notations to go through a long and growing list of conditionals.
The new engine first counts the number of numbers found in the title and short-circuits the conditional tree if only one or two numbers are found. This results in improved performance but also avoids several false positives, ultimately allowing me to expand the number of notations it recognizes to include rarer or more unusual ones, including the addition of more Japanese notations.

This growth does mean that the 0.1.1 release won't be for a long while due to testing, but when it is finally dropped, it will be worth the wait.
 
There is only one bug that I have found in 0.1.0 that is annoying to me, and it was carried over from TorrentWatch-X: the delete torrent buttons behave the same as the trash torrent buttons. I have fixed this for the 0.1.1 release but will not repair it in 0.1.0.

I have found a second bug in 0.1.0 that is not quite so annoying: the "Episodes Only" checkbox in the configuration panel doesn't seem to do anything. This too appears to be carried over from TorrentWatch-X, and it may be fixed in 0.1.1 or postponed till 0.1.2.

---

0.1.0 works on my out-of-the-box install of Debian 7.7 with its OOB transmission-daemon, Apache2, and PHP5.4 packages. I have tested it using the local transmission-daemon as well as a remote transmission-daemon running on a separate NAS on the same LAN.

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
