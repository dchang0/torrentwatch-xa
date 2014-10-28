![torrentwatch-xa TWXA logo](http://silverlakecorp.com/torrentwatch-xa/torrentwatch-xa-logo144.png)

torrentwatch-xa
===============

torrentwatch-xa is a fork of Joris Vandalon's TorrentWatch-X with special focus on anime fansub torrents that do not have season numbers, only episode numbers.

To restrict the development and testing scopes in order to improve quality assurance, I am focusing on Debian 7.x LINUX as the only OS and on Transmission as the only torrent client.

In the process of customizing torrentwatch-xa to fit my needs and workflow, I'll:

- fix some bugs
- refactor code
- add some features, mostly UI and workflow improvements
- let some features languish or remove them outright, especially buggy/unreliable portions of the code
 
The end goal is for torrentwatch-xa to do only what it's supposed to do and do it well. Over time, this will mean that broken or aging features related to non-anime torrents will probably be removed rather than repaired. While such features still work, they will remain.

Status and Announcements
===============

I've posted 0.1.0 with the changes listed in CHANGELOG. There are many changes, and I fully expect a bunch of bugs to have been introduced, even as I fixed many bugs carried over from the original TorrentWatch-X 0.8.9.

So far, 0.1.0 works on my out-of-the-box Debian 7.7 with its OOB Apache2 and PHP5.4 packages.

Prerequisites
===============


Credits
===============

The credits may change as features and assets are removed.

- Original TorrentWatch-X by Joris Vandalon
- Original Torrentwatch by Erik Bernhardson
- Original Torrentwatch CSS styling, images and general html tweaking by Keith Solomon http://reciprocity.be/
- Icons, by David Vignoni, are from Nuvola-1.0 available under the LGPL http://icon-king.com/
- Backgrounds and CSS Layout are borrowed from Clutch http://www.clutchbt.com/
