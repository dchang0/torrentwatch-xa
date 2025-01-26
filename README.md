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

I've posted 1.9.4 with the changes listed in [CHANGELOG.md](CHANGELOG.md).

While upgrading from Ubuntu 22.04 to 24.04, I ran into two breaking bugs.

1) transmission-daemon was being blocked from starting by AppArmor. To fix it, you need to edit /etc/apparmor.d/transmission and change this line:

`profile transmission-daemon /usr/bin/transmission-daemon flags=(complain) {`

to this:

`profile transmission-daemon /usr/bin/transmission-daemon flags=(complain,attach_disconnected) {`

Then reboot for it to take effect. You can also force AppArmor to parse its files to avoid a restart.

2) torrentwatch-xa was unable to connect to Transmission RPC because php-curl 8.3 needed an additional CURL option, CURLOPT_RETURNTRANSFER, when getting the X-Transmission-Session-Id.

Ubuntu 20.04 and 22.04 do not seem to mind this bug fix for Ubuntu 24.04. Given that CURLOPT_RETURNTRANSFER was being used for a very long time on the second and subsequent requests, I do not expect this fix to affect any other php-curl versions prior to 8.3.

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

Ubuntu 24.04 (PHP 8.3)
Ubuntu 20.04 (PHP 7.4)

I was testing on Ubuntu 22.04 (PHP 8.1) for quite a while but upgraded that server to Ubuntu 24.04.

Really, what torrentwatch-xa is affected most by is the PHP version and whether or not a PHP module/library is installed or not, not the OS. All other LINUX distros should work fine so long as they don't modify PHP too much.


Credits
===============

- Original TorrentWatch-X by Joris Vandalon https://code.google.com/p/torrentwatch-x/
- Original torrentwatch by Erik Bernhardson https://code.google.com/p/torrentwatch/
- Credits for the few third-party PHP and Javascript libraries are inside of their respective files.
