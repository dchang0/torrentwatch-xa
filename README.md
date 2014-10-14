torrentwatch-xa
===============

torrentwatch-xa is a fork of TorrentWatch-X with focus on anime fansub torrents that do not have season numbers, only episode numbers and on Transmission as the only torrent client.

In the process of customizing torrentwatch-xa to fit my needs and workflow, I'll:

- fix some bugs
- refactor code
- add some features, mostly UI and workflow improvements
- let some features languish or remove them outright, especially buggy/unreliable portions of the code
 
The end goal is for torrentwatch-xa to do only what it's supposed to do and do it well. Over time, this will mean that broken or aging features related to non-anime torrents will probably be removed rather than repaired. While such features still work, they will remain.


Currently it is too soon to post any code. There are some glaring bugs carried over from TorrentWatch-X 0.8.9 that should be fixed immediately that are probably these:

https://code.google.com/p/torrentwatch-x/issues/list

Issues 238 and 244 which are essentially "no torrent found at URL." I am getting this on all ezRSS torrents.
Issue 237 which could be rephrased as "episode filter filtering out favorites that should match." I am getting this on all feeds' torrents. Downloads happen if I explicitly select Download on a torrent.
