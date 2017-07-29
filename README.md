![torrentwatch-xa twxa logo](http://silverlakecorp.com/torrentwatch-xa/torrentwatch-xa-logo144.png)

torrentwatch-xa
===============

__torrentwatch-xa is an anime/manga/light novel broadcatcher__ that regularly monitors multiple subscribed public RSS/Atom feeds for the latest "Favorite" serialized torrents and downloads them automatically. It is an actively-developed, high-quality resurrection of the popular but long-abandoned TorrentWatch-X.

As a fork of TorrentWatch-X, torrentwatch-xa handles Western live-action show titles containing commonly-used season x episode or date-based numbering styles. It is specially designed to __also__ handle the widely-varying numbering styles used by anime, manga, and light novel fansubbing crews and also features all the bugfixes and code cleanup that TorrentWatch-X so badly needed.

![torrentwatch-xa twxa ScreenShot 1](http://silverlakecorp.com/torrentwatch-xa/twxaScreenShot1.png)

To auto-download Favorite torrents, torrentwatch-xa controls a local __or remote__ Transmission BitTorrent client via Transmission RPC __and/or__ saves .torrent files or magnet links as files locally. The latter allows the use of __any__ BitTorrent client (not just Transmission) that can watch directories for .torrent files or magnet links to automatically start those torrents.

torrentwatch-xa runs on an Apache 2.4.x webserver with PHP 5.6.0alpha3&sup1; or higher and the prerequisite PHP packages listed in the installation instructions. It works out-of-the-box on any up-to-date instance of Debian 8.x, Ubuntu 14.04.x, or Ubuntu 16.04.x on any architecture, and it can be made to work on current versions of RedHat, Fedora, or CentOS LINUX by installing the RPM package equivalents of the prerequisite PHP .deb packages and adjusting the firewall and SELINUX restrictions. (RedHat distros are not officially supported at this time.)

torrentwatch-xa is extremely lightweight and can run decently on even a $5 Raspberry Pi Zero (around 18 seconds for the web UI to process all six default feeds with 32 favorites, as compared to around 5 seconds on an ODROID C1+). The web UI works on any modern web browser that has Javascript enabled, including smartphone and tablet browsers.

Common setups:

- torrentwatch-xa and Transmission run on the same LINUX desktop or server or NAS together; downloaded content is stored on this device
- torrentwatch-xa runs on a low-power computer (usually a home-theater single-board computer running Kodi) or virtual machine and remotely controls Transmission running on a separate NAS that stores the downloaded content

&sup1; PHP 5.6.0alpha3 is really only required by PHPMailer's SMTP 5.2.23 library to support TLS 1.1 and 1.2. torrentwatch-xa itself only requires PHP 5.4.0. If you are not using email triggers with TLS 1.1 or 1.2, you should be able to avoid this version requirement by downgrading PHPMailer's SMTP library.

Status
===============

### Current Version

I've posted 0.7.0 with the changes listed in [CHANGELOG.md](CHANGELOG.md).

__If upgrading to torrentwatch-xa 0.7.0 from any prior version and keeping the Favorites in torrentwatch-xa.config, you must run `upgradeConfigTo0_7_0.php` to upgrade your Favorites. This script will only be available in the 0.7.0 release; it is always possible to "upgrade" a Favorite by deleting it and re-creating it.__

I've completed all of the currently-outstanding season and episode detection patterns. What remains in the detection engine are bugfixes; just about every numbering style I've seen so far has been added to the engine.

PROPER/REPACK/RERIP keywords are treated as item version 99 so that it preempts all lower versions.

0.7.0 now saves magnet links to files for either the _Client: Save Torrent in Folder_ or _Also Save Torrent Files_ features.

Individual Favorites can now override the global default _Download Dir_ and the _Also Save Torrent Files Dir_ settings. This is the reason for the upgrade script mentioned above.

Still in alpha since 0.4.0: a Favorite Filter can now match multibyte strings (Japanese/Chinese/Korean) in RegEx matching mode only (not Simple, nor Glob), but multibyte characters must be individually specified in PCRE Unicode hexadecimal notation like `0x{3010}` to satisfy PHP's preg_ functions.

New in alpha: Fedora Server 25 is being tested but will not be officially supported for quite a while.

### Next Version

I hope to:

- continue cleaning up or improving old code (still about half of torrentwatch-xa.js and several functions in twxa_feed.php and twxa_torrent.php need improvement).
- shorten the time to the first firing of getClientData after a browser refresh
- start comprehensive testing of the _Client: Save torrent in folder_ feature, which may require readjustment of the list item states
- rewrite the episode_filter() function to handle the new season and episode notation style
- finish twxaDebug() and $verbosity


Documentation
===============

See:

- [INSTALL.md](INSTALL.md) for detailed installation steps or important notes if you are upgrading from a prior version.

- [USAGE.md](USAGE.md) for usage notes and an explanation of some design decisions.

- [TROUBLESHOOTING.md](TROUBLESHOOTING.md) for detailed troubleshooting steps and explanations of design decisions and common issues.

- Known bugs are tracked primarily in the [TODO.md](TODO.md) and [CHANGELOG.md](CHANGELOG.md) files. Tickets in GitHub Issues will remain separate for accountability reasons.

Credits
===============

- Original TorrentWatch-X by Joris Vandalon https://code.google.com/p/torrentwatch-x/
- Original Torrentwatch by Erik Bernhardson https://code.google.com/p/torrentwatch/
- Credits for the few third-party PHP and Javascript libraries are inside of their respective files.