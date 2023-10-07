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

I've posted 1.8.0 with the changes listed in [CHANGELOG.md](CHANGELOG.md).

1.8.0 has a major new feature called Super-Favorites.

A Super-Favorite is a type of favorite that creates a Favorite from the item title when it matches an item.

I created the Super-Favorite because I wanted some way to add Favorites for all the shows from a specific fansubbing crew at the start of an anime season--right before the season starts so as to capture the first episode. It has been tedious to have to research and then type in the titles of ten or more anime series before the first episode aired, and I often missed the first episodes if I waited for the anime series to show up in the feeds so that I could add them from the feed. The bulk Favorite importer twxa_fav_import.php didn't bypass the tedium of researching and inputing anime titles either.

Enter the Super-Favorite!

Every item in the feed is matched against all the configured Super-Favorites, and if any item matches a Super-Favorite, the item's title is turned into a new Favorite's Name and Filter fields with the same Feed and Quality from the matched Super-Favorite. (All other fields of the Favorite will be blank.)

After a few weeks, the Super-Favorite will have Favorited every item that matched during that time, the Favorites it created will be downloading the specific items as it should, and Super-Favorites can be globally-disabled to improve performance. Then, when the next season rolls around, you can globally-enable Super-Favorites again, maybe make slight modifications to individual Super-Favorites carried over from the prior season, and create the new season's set of Favorites.

Obviously, Super-Favorites can create a lot of unwanted Favorites if the Filter and Not fields are poorly designed. Deleting erroneous Favorites is slow since they can only be deleted one by one, so Super-Favorites are disabled by default. New users should not enable them until after learning how the pattern matching style of their choice works.

It is not necessary to convert your 1.7.0 config file to accept Super-Favorites after upgrading to 1.8.0, though you may see PHP warnings in your web server log until you enable Super-Favorites and create one Super-Favorite for the first time. Starting with a fresh 1.8.0 config file will not generate any PHP warnings.

I may make Super-Favorites more powerful by adding some of the other fields such as the Episodes filter or Download Dir/Also Save Dir, if there is enough demand. It's probably more important to come up with a way to delete multiple Favorites at once, though.

For more details on how to use Super-Favorites, see [USAGE.md](USAGE.md).

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
