<img src="http://silverlakecorp.com/torrentwatch-xa/torrentwatch-xa-logo144.png" width="144" height="144"/>

torrentwatch-xa
===============

__torrentwatch-xa is an anime/manga/light novel/TV show broadcatcher__ that regularly monitors multiple subscribed public RSS/Atom feeds for the latest "Favorite" serialized torrents and downloads them automatically. It is an actively-developed, high-quality resurrection of the popular but long-abandoned TorrentWatch-X.

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

I've posted 1.0.0 with the changes listed in [CHANGELOG.md](CHANGELOG.md).

It is now much easier to change torrentwatch-xa's install paths. get_webDir(), get_baseDir(), and all the PHP include path calculations are now located only in config.php. As a result, twxacli.php and config.php were moved from the base directory to the web directory and config.php is no longer optional.

1.0.0 includes several JQuery 3 bug fixes, including a fix for a slow memory leak in listSelector.

Some new detection errors were fixed, especially where a number is part of the title, as with Steins;Gate 0. Bugs in the video and audio codec detection functions were fixed by switching back to the slower PHP preg_ functions. A bug in the version number comparison was fixed by committing to a three-number format.

Still in alpha since 0.4.0: a Favorite Filter can now match multibyte strings (Japanese/Chinese/Korean) in RegEx matching mode only (not Simple, nor Glob), but multibyte characters must be individually specified in PCRE Unicode hexadecimal notation like `0x{3010}` to satisfy PHP's preg_ functions.

New in alpha: Fedora Server 25 and Ubuntu 18.04 are being tested and work fine but will not be officially supported for quite a while.

### Next Version

I'm working on making the default config logic foolproof so that it is possible to write a command-line bulk Favorite importer.

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