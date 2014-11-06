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

As of 2014/11/05, 0.1.0 is working fine with the new episode and season detection engine. There are about 2% false positives or false negatives, typically of the format 1 - 5, where torrentwatch-xa cannot decide whether that is Season 1, Episode 5 or Episodes 1 through 5.
Such challenges are to be expected from simple regular expression matching, and they are nothing that affect torrentwatch-xa's ability to auto-download the episodes you want, so long as your favorites aren't written to specifically target the titles that torrentwatch-xa has trouble with.

There is only one bug that I have found in 0.1.0 that is annoying to me, and it was carried over from TorrentWatch-X: the delete torrent buttons behave the same as the trash torrent buttons. I have fixed this for the 0.1.1 release but will not repair it in 0.1.0.

Otherwise, I have found nothing that warrants more commits to 0.1.0, and so I have turned to developing 0.1.1. The primary focus will be on improving episode and season detection and adding debug/diagnostic features to help me dial in the accuracy over time.

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
