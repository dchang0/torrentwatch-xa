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

Currently it is too soon to post any code. There are some glaring bugs carried over from TorrentWatch-X 0.8.9 that should be fixed immediately.

I am rewriting the Season and Episode and Date detection engine; it is a whole barrel full of worms causing the rewrite of Quality detection, among others. The new engine will have slightly more intelligence resulting in more, more accurate matches.

UPDATE: Project is on hold due to an error in PHP 5.4.4-14+deb7u14's preg_match() function. The specific example is this:

preg_match("/S(\d+)E(\d+)/i", $title, $matches);

When encountering a partial match before the real match, preg_match() gives up and stops looking. So, this string will fail to match:

$title = "SAF3 - S02E205";

because preg_match() partially matches the first "S" and then runs into the "A" and gives up, rather than moving on to the correct match of "S02E205".

I will have to wait until this gets fixed by PHP.net OR write a workaround.


Credits
===============

The credits may change as features and assets are removed.

- Original TorrentWatch-X by Joris Vandalon
- Original Torrentwatch by Erik Bernhardson
- Original Torrentwatch CSS styling, images and general html tweaking by Keith Solomon http://reciprocity.be/
- Icons, by David Vignoni, are from Nuvola-1.0 available under the LGPL http://icon-king.com/
- Backgrounds and CSS Layout are borrowed from Clutch http://www.clutchbt.com/
