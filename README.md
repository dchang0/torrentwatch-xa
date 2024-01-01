<img src="http://silverlakecorp.com/torrentwatch-xa/torrentwatch-xa-logo144.png" width="144" height="144"/>

torrentwatch-xa
===============

__torrentwatch-xa is an anime/manga/light novel/TV show broadcatcher or PVR__ that regularly monitors multiple subscribed public RSS/Atom feeds for the latest "Favorite" serialized torrents and downloads them automatically. It is an actively-developed, high-quality resurrection of the popular but long-abandoned TorrentWatch-X.

As a fork of TorrentWatch-X, torrentwatch-xa handles Western live-action show titles containing commonly-used season x episode or date-based numbering styles. It is specially designed to __also__ handle the widely-varying numbering styles used by anime, manga, and light novel fansubbing crews and also features all the bugfixes and code cleanup that TorrentWatch-X so badly needed.

![torrentwatch-xa twxa ScreenShot 1](http://silverlakecorp.com/torrentwatch-xa/twxaScreenShot1.png)

To auto-download Favorite torrents, torrentwatch-xa controls a local __or remote__ Transmission BitTorrent client via Transmission RPC __and/or__ saves .torrent files or magnet links as files locally. The latter allows the use of __any__ BitTorrent client (not just Transmission) that can watch directories for .torrent files or magnet links to automatically start those torrents.

torrentwatch-xa is a single-page web app designed to run on Apache httpd 2.4 and up with PHP 5.7 and up and certain PHP modules. See [INSTALL.md](INSTALL.md) for the list of prerequisite software. While official support is only for specific LINUX distributions, you should be able to run torrentwatch-xa on any OS, any architecture, and any web server so long as the PHP installation has all the functions needed.

torrentwatch-xa is extremely lightweight and can run decently on even a $5 Raspberry Pi Zero. The web UI works on any modern web browser that has Javascript enabled, including smartphone and tablet browsers.

Common setups:

- __Local__: torrentwatch-xa and Transmission run together on the same LINUX desktop, server, or NAS; downloaded content is stored on this one device. The ODROID HC1, HC2, or HC4 with a large capacity SATA drive is perfect for this use case--quiet, fast, and easy to directly install torrentwatch-xa and transmission-daemon on.
- __Remote__: torrentwatch-xa runs on a low-power computer (usually a home-theater single-board computer running Kodi) or virtual machine and remotely controls Transmission running on a separate NAS that stores the downloaded content.

Status
===============

I've posted 1.9.1 with the changes listed in [CHANGELOG.md](CHANGELOG.md).

Super-Favorites now have an Episode filter that can be used to restrict the creation of Favorites to just the first episodes of the coming season.

For instance, let's say you want to capture all the shows fansubbed by Subsplease, and you know that you will be watching the first episodes and deleting about half of the Favorites before the second episodes come out. You don't want the Super-Favorite to re-add the deleted Favorites when it sees the second episodes. So, the Super-Favorite would be set up like this:

Name: Subsplease

Filter: Subsplease

Not:

Episodes: 1x1,2x1,3x1,4x1

Feed: All

Quality: 480p

This Super-Favorite will create a Favorite whenever it sees a Subsplease show that only has episode 1x1, 2x1, 3x1, or 4x1. This will capture the first episode of any season from 1 to 4 in 480p from any feed. 

Now, there is a problem with shows that don't use season numbers and just continue sequentially where the last season left off. For example, let's say Edens Zero is going to start the next season at episode 51. You could change the Episodes filter to:

1x1,2x1,3x1,4x1,1x51

and it would also match Edens Zero episode 51 but not 52 and later. Having to plan ahead for Edens Zero's sequential numbering misses the point of Super-Favorites, though (it's easier to simply add a Favorite for Edens Zero like normal), so I'm trying to come up with a novel way to address this issue.

Still, the Episodes filter is extremely useful, so I am releasing it now on its own in 1.9.1.


Please report any bugs using Github Issues.

If you like, buy me a coffee for those late-night torrentwatch-xa programming stints at [Ko-Fi](https://ko-fi.com/dchang0) or [CoinDrop](https://coindrop.to/dchang0/).

Documentation
===============

See:

- [INSTALL.md](INSTALL.md) for detailed installation steps or important notes if you are upgrading from a prior version.

- [USAGE.md](USAGE.md) for usage notes and an explanation of some design decisions.

- [TROUBLESHOOTING.md](TROUBLESHOOTING.md) for detailed troubleshooting steps and explanations of design decisions and common issues.

- Known bugs are tracked primarily in the [TODO.md](TODO.md) and [CHANGELOG.md](CHANGELOG.md) files. Tickets in GitHub Issues will remain separate for accountability.

Credits
===============

- Original TorrentWatch-X by Joris Vandalon https://code.google.com/p/torrentwatch-x/
- Original torrentwatch by Erik Bernhardson https://code.google.com/p/torrentwatch/
- Credits for the few third-party PHP and Javascript libraries are inside of their respective files.
