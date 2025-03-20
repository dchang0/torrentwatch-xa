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

I've posted 1.9.5 with the changes listed in [CHANGELOG.md](CHANGELOG.md).

Finally, torrentwatch-xa is provided as a .deb installation package for Debian-based LINUX distributions!

I had to make a few changes to the installation and upgrade processes and scripts as well as rename some files to conform to Debian/Ubuntu naming conventions.

- /etc/cron.d/torrentwatch-xa-cron was renamed to /etc/cron.d/torrentwatch-xa. The existing /etc/cron.d/torrentwatch-xa-cron file will be deleted by dpkg during the upgrade.
- /var/log/twxalog was renamed to /var/log/torrentwatch-xa.log. Existing /var/log/twxalog files will not be deleted by dpkg.
- /etc/logrotate.d/twxalog was renamed to /etc/logrotate.d/torrentwatch-xa and it now manages the new log file name above. An existing /etc/logrotate.d/twxalog file will be deleted by dpkg during the ugprade.

This repository also includes a default configuration file so that dpkg can manage it during upgrades. Most notably, this means that when you are doing a dpkg upgrade, dpkg will compare the package's included config file to your existing one and offer you the choice of keeping your old one or accepting the new one, as well as merging the two manually.

Debian/Ubuntu would probably want me to move the config file into /etc/ but since the config file is not meant for manual editing, keeping it where it is may make more sense.

Please report any bugs using Github Issues.

If you like, buy me a coffee for those late-night torrentwatch-xa programming stints at [Ko-Fi](https://ko-fi.com/dchang0) or [CoinDrop](https://coindrop.to/dchang0/).

Documentation
===============

See:

- [INSTALL.md](INSTALL.md) for detailed installation steps or important notes if you are upgrading from a prior version.

- [USAGE.md](USAGE.md) for usage notes and an explanation of some design decisions.

- [TROUBLESHOOTING.md](TROUBLESHOOTING.md) for detailed troubleshooting steps and explanations of design decisions and common issues.

- Known bugs are tracked primarily in the [TODO.md](TODO.md) and [CHANGELOG.md](CHANGELOG.md) files. Tickets in GitHub Issues will remain separate for accountability.

Testing Targets
===============

The main testing targets that I use for development are:

- Ubuntu 24.04 (PHP 8.3)
- Ubuntu 20.04 (PHP 7.4)

I was testing on Ubuntu 22.04 (PHP 8.1) for quite a while but upgraded that server to Ubuntu 24.04.

Really, what torrentwatch-xa is affected most by is the PHP version and whether or not a PHP module/library is installed or not, not the OS. All other LINUX distros should work fine so long as they don't modify PHP too much.


Credits
===============

- Original TorrentWatch-X by Joris Vandalon https://code.google.com/p/torrentwatch-x/
- Original torrentwatch by Erik Bernhardson https://code.google.com/p/torrentwatch/
- Credits for the few third-party PHP and Javascript libraries are inside of their respective files.
