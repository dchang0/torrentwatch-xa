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

CURRENT VERSION: I've posted 0.1.1 with the changes listed in CHANGELOG. This version has a brand new season and episode detection engine that first counts the number of numbers in the title and uses that to improve pattern matching. This should improve accuracy and performance. One design decision this allows me to make is to give up on titles containing large numbers of numbers because they are too confusing to the parser. This behavior is preferable to getting a false-positive match.

I am sure the new detection engine will introduce its own share of bugs, but it has largely worked well over the past several months in testing.

NEXT VERSION: 0.1.2 in progress. Will require re-prioritization of TODOs because there are so many. The following "Known Bugs" will be addressed, finally.

Known Bugs
===============

Bugs carried over from TorrentWatch-X:

- "Episodes Only" checkbox in the configuration panel doesn't seem to do anything.
- Downloading/Downloaded state is incorrect so that Downloaded items end up in the Downloading filter.
- Javascript can crash page in browser if system coming out of standby/sleep. It's not serious--just reload the page. This is NOT a browser crash, just the page within the browser.

And one feature that must be added because the lack of it is very annoying is the automatic removal of Downloaded and seeded torrents.

"One man's bug is another man's feature."
---

It's become obvious that there are situations for which a mutually-exclusive design decision cannot be avoided. For example, the title "Holly Stage for 50 - 3" is meant to be interpreted as title = "Holly Stage for 50" and episode number 3, with season 1 implied.
(Fans know that "Holly Stage for 50 - 3" really should be read as title = "Holly Stage for 49", season 2, episode 3, to further complicate matters.)
But the engine currently reads it as title = "Holly Stage for" and season 50, episode 3. Why? Because it was determined that the ## - ## pattern much more often means SS - EE.

Sadly, because the engine was forced to make the choice, fans of "Holly Stage for 50" must "hack" the Favorite to get it to download properly. There is no way to solve this problem without referring to some centralized database of anime titles or relying on some sort of AI, neither of which are going to happen in torrentwatch-xa any time soon.

Tested Platforms
===============

0.1.1 works on my out-of-the-box install of Debian 7.8 x86_64 with its OOB transmission-daemon, Apache2, and PHP5.4 packages. I have tested it using the local transmission-daemon as well as a remote transmission-daemon running on a separate NAS on the same LAN.

I do not plan on testing on Debian 8 yet. It will probably work fine without any changes to torrentwatch-xa.

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
