Usage
===============

For the most part, torrentwatch-xa is very intuitive and self-explanatory. These usage notes explain some of the advanced features, details, and behaviors that are not immediately obvious.

### Item Colors

The small colored bars on the left edge of some items indicate the states of the items. Click the Legend button to see what each color means.

### Check for Updates

Check for Updates checks once every 7 days for new versions of torrentwatch-xa. If you turn it off, then turn it back on, it will take up to 7 days to check again.

### Seed Ratio Settings

If set to a positive number, each Favorite's seed ratio setting overrides its parent Feed's seed ratio setting, which overrides the global Default Seed Ratio setting. To allow inheritance to occur, leave the setting blank. Any negative number gets overridden by -1. If the global Default Seed Ratio is blank, it is overridden by -1.

Transmission itself has a seed ratio limit that will override any limit set within torrentwatch-xa.

### Configure > Feeds

torrentwatch-xa provides you with several default feeds when starting fresh with no config file. If you've added your own feeds, you should probably disable or remove any of these default feeds that you don't use to improve twxa_cli.php's performance and reduce the load placed on the feed host(s), saving their operators bandwidth. Please be sure to visit your favorite feeds' websites often so that they can earn advertising revenue from your support and help keep the anime fansubbing community alive--thanks!

### Disable an Individual Favorite or Super-Favorite

If you wish to disable an individual Favorite or Super-Favorite without changing its matching or deleting it, simply set its Feed to None.

### Configure > Trigger

torrentwatch-xa can trigger email notifications by SMTP or shell scripts or both. Shell scripts can be used for post-processing including sending email notifications in place of or in addition to the built-in SMTP notifications.

SMTP Notifications trigger on these events:
- Favorite item starts downloading (usually started by cron job, but web UI can do it as well)
- error while downloading
- error in Script

Scripts trigger on these events:
- Favorite item starts downloading
- non-Favorite item starts downloading
- error while downloading

To run a shell script, check Enable Script, provide the full path to a single shell script with no parameters in the Script field. Your shell script must have rwx permissions for www-data, and no parameters may be supplied in the Script field. See /var/lib/torrentwatch-xa/examples for example shell scripts that you can customize to suit your needs.

To use the built-in SMTP notifications, check SMTP Notifications and fill in the From: Email and To: Email fields and all the SMTP fields. SMTP Port defaults to 25 if left blank. From: Name and HELO Override are optional and will use default settings if left blank. If the cron job has trouble sending email notifications, it is probably unable to retrieve the hostname for use as the HELO, and the HELO Override will be necessary.

The Test button tests the SMTP settings currently in the form by sending a test email. You must click the Save button to actually save the settings.

torrentwatch-xa uses PHPMailer 5.2.23 to send emails, so you may need to refer to PHPMailer documentation for help in understanding any SMTP error messages that appear.

### Season and Episode Notation

A quick explanation of the new season and episode notation in the "episode label" shown on each line to the left of the timestamp at the right edge of the feed list:

- SxE = single episode
- SxEv# = single episode with version number
- YYYYMMDD = single date
- S1xE1-S1-E2 = batch of episodes within one season
- YYYYMMD1-YYYYMMD2 = batch of dates
- S1xFULL = one full season
- S1xE1-S2xE2 = batch of episodes starting in one season and ending in a later season
- S1xE1v2-S2xE2v3 = batch of episodes starting in one season and ending in a later season, with version numbers

For items not recognized as having an episodic numbering, Glummy ("_ ) is displayed.

Internally, the new Favorite matching engine uses direct comparisons of the separate season and episode as discrete numeric values and does not deal with this notation at all.

Later, when the Favorite Episodes filter functionality is implemented, it will also use this notation (except for Glummy, who is for display only).

The ideal notation for videos is actually SxVxEv# (Season x Volume x Episode version #); if downloading anime BluRay Disc sets becomes super-popular, I may implement this notation style throughout torrentwatch-xa in a future version.

### Current Episodes Filter Notation

The Episodes filter currently in each Favorite is still the old TorrentWatch-X filter. The notation style is the old style, like so:

- SxE = single episode
- SxEp = single episode, PROPER or Repack
- S1xE1-S2xE2 = batch of episodes starting in one season and ending in a later season

### RegExp Matching Style vs. Simple vs. Glob

The Filter, Not, and Qualities fields in each Favorite behave differently depending on the matching style: Simple, Glob, or RegExp.

Note that for all string comparisons in any matching style, the strings are converted to all-lowercase using the PHP strtolower() function to make the matches case-insensitive.

#### Simple

Uses the PHP strpos() function to compare strings. strpos() finds a substring (the Filter) in a another string (the title). Matches must be exact alphanumeric matches with no wildcards.

Example 1: The Simple Filter "__zombie__" will match all of these titles:

__Zombie__ Land Saga

Kore Wa __Zombie__ Desu Ka?

Kore Wa __Zombie__ Desu Ka? Of the Dead

__Zombie__-Loan

Note that it is not case-sensitive and that the position of the substring "zombie" in the title does not matter. You will match all four of these titles.

Example 2: The Simple Filter "__zombieland__" will match:

__Zombieland__ Saga

but will not match:

Zombie Land Saga

Why? The space in the middle of "Zombie Land" doesn't match.

#### Glob

Named after the PHP glob() function. Uses the PHP fnmatch() function to compare strings. Allows simple wildcards allowed in filename comparisons in a LINUX shell such as * and ? and square brackets.

Example 1: The Glob Filter "__zombie*land__" will match:

__Zombieland__ Saga

__Zombie Land__ Saga

Example 2: The Glob Filter "__zombie*dead__" will match:

Kore Wa __Zombie Desu Ka? Of The Dead__

but will not match:

Kore Wa Zombie Desu Ka?

Example 3: The Glob Filter "__gr[ae]yman__" will match:

D.__Grayman__

D.__Greyman__

but will not match:

D.Gray-man

D.Gray Man

Example 4 (contributed by JohnDarkhorse): The Glob filter "__Grayman*[Erai-raws||SSA]__" will match:

__Grayman Erai-raws__

__Grayman SSA__

but will not match:

D.Grayman Erai-raws (because the "D." at the beginning is not in the pattern)

Grayman SSAM (because the "M" at the end is not in the pattern)

This Glob pattern "__\*Grayman\*[Erai-raws||SSA]__" will match:

__D.Grayman Dual Audio Erai-raws__

because the first asterisk picks up the "D." at the beginning and the second asterisk picks up the " Dual Audio " in the middle.

Note: The Simple pattern "word" is the equivalent of the Glob pattern "\*word\*"

#### RegExp

Uses the PHP preg_match() function to compare strings. Allows PCRE Unicode regular expressions for the most powerful matching and wildcards. RegExp is the default matching style. Only RegExp matching style supports multibyte strings (Japanese/Chinese/Korean). Multibyte characters must be individually specified in PCRE Unicode hexadecimal notation like `0x{3010}` to satisfy PHP's preg_ functions.

PCRE is such a powerful and complex expression language that I won't cover how it works here. Suffice it to say that you can do some very complex matching and simple to moderate logic.

Example 1: Let's say you want to match the title "Oregairu" but only want to match one of three fansubbing crews: Erai-raws, Subsplease, or SSA.

The RegExp filter:

__(Erai-raws|Subsplease|SSA) Oregairu__

would match all of:

__Erai-raws Oregairu__

__Erai-raws Oregairu__ Zoku

__Erai-raws Oregairu__ Kan

__Subsplease Oregairu__

__Subsplease Oregairu__ Zoku

__Subsplease Oregairu__ Kan

__SSA Oregairu__

__SSA Oregairu__ Zoku

__SSA Oregairu__ Kan

Example 2: Let's say you only want Oregairu from one of those three fansubbing crews but not Oregairu Zoku nor Oregairu Kan. Use this filter:

__(Erai-raws|Subsplease|SSA) Oregairu [^ZK]__

The "__Oregairu [^ZK]__" essentially means: "I don't want the next letter after the space after Oregairu to be a Z or a K."

Of course, if you just use simple literal strings, RegExp essentially acts like Simple. For instance, the RegExp Filter "__girls und panzer__" will match:

Erai-raws __Girls Und Panzer__

Subsplease __Girls Und Panzer__ Das Finale

Example 3: Let's say you like 720p and 480p but don't like 1080p. In the Qualities input, you can put this RegExp: "__(720p|480p)__"

This will match 720p or 480p but not 1080p nor 540p nor any other resolution.
Note that for a single Favorite offered in multiple resolutions, with the above filter, whichever of 720p or 480p comes first in the feed matches and downloads. The other would be considered a duplicate and would match but not download.

### Super-Favorites

A Super-Favorite is a type of favorite that creates a Favorite from the item title when it matches a feed item.

I created the Super-Favorite because I wanted some way to add Favorites for all the shows from a specific fansubbing crew at the start of an anime season--right before the season starts so as to capture the first episodes of all the crew's series.

When enabled, every item in the feed is matched against all the configured Super-Favorites, and if any item matches a Super-Favorite, the item's title is turned into a new Favorite's Name and Filter fields with the same Feed and Quality from the matched Super-Favorite. (All other fields of the Favorite will be blank.)

For the following example, under Configure > Favorites, Matching Style is set to RegExp and Enable Super-Favorites is checked.

Under Super-Favorites, with a Super-Favorite set like so:

Name: Erai-raws
Filter: Erai-raws
Not: Bleach
Feed: All
Quality: 480p

when this item is encountered in the feed:

Erai-raws Ayakashi Triangle - 12 480p Multiple Subtitle ENG POR-BR SPA-LA SPA FRE GER ITA RUS

the Super-Favorite will create this Favorite:

Name: Erai-raws Ayakashi Triangle (matched by the Super-Favorite's Filter and Not fields)
Filter: Erai-raws Ayakashi Triangle (always the same as tne Name above)
Not: (always blank)
Download Dir: (always blank)
Episodes: (always blank)
Feed: All (carried over from the Super-Favorite)
Quality: 480p (carried over from the Super-Favorite)
Seed Ratio: (always blank)
Last Download: (always blank)

Due to the Not field being set to:

Bleach

This show will not be turned into a Favorite:

Erai-raws Bleach - Sennen Kessen Hen - Ketsubetsu Tan - 01 480p Multiple Subtitle

Note that the Not field of the Favorite is not carried over from the Super-Favorite because the Super-Favorite's Not is used in conjunction with the Super-Favorite's Filter to produce the Favorite's Name (and thus its Filter) in full.

After a few weeks, the Super-Favorite will have Favorited every item that matched during that time, the Favorites it created will be downloading the specific items as they should, and Super-Favorites can be globally-disabled to improve performance. Then, when the next season rolls around, you can globally-enable Super-Favorites again, maybe make slight modifications to individual Super-Favorites carried over from the prior season, and create the new season's set of Favorites.

Obviously, Super-Favorites can create a lot of unwanted Favorites if the Filter and Not fields are poorly designed. Deleting erroneous Favorites is slow since they can only be deleted one by one, so Super-Favorites are disabled by default. New users should not enable them until after learning how the pattern matching style of their choice works.

Keep in mind that while a Super-Favorite is active, it doesn't make sense to delete any Favorites created by it while the episodic item is still ongoing. It will simply be re-added back the next time an episode of that item shows up in the feed. So, if you wish to stop a Favorite that was created by a Super-Favorite, change its Feed to None so that it will never match anything. As long as it exists with the same Name, the Super-Favorite can't re-add it. You can delete the Favorite after the season is done.

Using the same trick to disable an individual Favorite, you can also disable an individual Super-Favorite by setting its Feed to None. That will cause it to never match any item, thus preventing it from creating any Favorites.

### Authentication for Private RSS Feeds

See the section "Only Public Torrent RSS or Atom Feeds Are Supported" in the **Design Decisions Explained** section below for more details.

### Auto-Del Seeded Torrents

When enabled, either the web UI or twxa_cli.php (run by the cron job) will automatically delete completely-downloaded, fully-seeded torrents from Transmission without trashing the torrent's contents. Auto-Del Seeded Torrents is also smart enough not to delete any torrents that are not found in the download cache, preventing it from deleting torrents that were added to Transmission via other means.

The web UI is fully synchronized with Transmission so that items auto-deleted by twxa_cli.php will be removed from the web UI correctly without requiring a browser refresh.

### Magnet Links Saved as Files

If Also Save Torrent Files is enabled and torrentwatch-xa retrieves a magnet link instead of a .torrent file, it will save the magnet link in a file. There are third-party tools that easily convert magnet links stored in text files to torrent files, if you prefer the torrent file over the magnet link.

The ability to save magnet links was added to deal with the increasingly-common feeds that have only magnet links and no links to torrent files.

### How the Feed Caches and the Cron Interval Setting Work

If caching is enabled, the feeds' caches expire after a certain number of seconds, forcing a refresh of the cache from the feeds' sources. While the feed cache has not expired, reloading the web UI will not trigger a refresh of the feeds. This allows the web UI to be more responsive and reduces the burden on the feed sources.

When the cron job runs, it sets the feed cache to expire at just under the Cron Interval value to effectively force the feeds to be refreshed once each time the cron job runs. If you wish to change the interval between cron job runs by editing torrentwatch-xa-cron, you should change the Cron Interval setting to the same interval in minutes.

The Cron Interval setting is not exposed through the web UI; to change it, you must edit the config file directly.

### Bulk Favorites Importer twxa_fav_import.php

_WARNING!! The bulk importer is experimental; use it at your own risk! Be sure to back up your config file before any bulk import!_

1.1.0 includes twxa_fav_import.php, a command-line tool that can import a tab-separated-values (TSV) file containing a list of favorites. 1.8.0 adds the Not column.

A good way to use the bulk Favorites importer is to go to anichart.net, look up next season's anime titles, and then create the TSV and import it. This will catch the first episode of each show right when it starts. Be aware that importing a massive number of Favorites will slow down torrentwatch-xa if you are running a Raspberry Pi or other low-powered CPU.

Create a plain text TSV file with these columns in order from left to right:

1. Name (required)
2. Filter (required)
3. Not (optional)
4. Quality (optional)

Filter, Not, and Quality can be regular expressions. (Set Configure > Favorites > Matching Style to RegExp in order to use regular expressions.)

Close the browser if you have torrentwatch-xa's web UI open.

Then, at the command line, run:

`sudo cp /var/lib/torrentwatch-xa/config_cache/torrentwatch-xa.config <path to put the backup>`

`sudo /usr/bin/php /var/www/html/torrentwatch-xa/twxa_fav_import.php <path to TSV>`

You can watch the web server's syslog file (default: /var/log/syslog) for PHP errors and torrentwatch-xa's log (default: /var/log/twxalog) for import errors.
If there are no errors, go ahead and open torrentwatch-xa in the browser and make sure the new Favorites are imported.

If the TSV file confuses PHP's fgetcsv() function, there is a good possibility you will corrupt your config file. If you have to put the backup file back, do this:

Close the browser if you have torrentwatch-xa's web UI open.

`sudo cp <path to put the backup> /var/lib/torrentwatch-xa/config_cache/torrentwatch-xa.config`

### Clear All Caches if Switching Configure > Client > Client

If changing the setting Configure > Client > Client, be sure to clear all the caches and reload the page. Otherwise benign errors could show up in the log due to leftover download cache files.

Design Decisions Explained
===============

There are situations for which a mutually-exclusive design decision cannot be avoided. The below are design decisions that will never be "fixed."

### Only Public Torrent RSS or Atom Feeds Are Supported

I have found that due to the highly fluid nature of the torrent scene, it's better to stick with public torrent RSS or Atom feeds than deal with the many different authentication systems of private torrent feeds. Just about everything you could want is going to be available via multiple public torrent feeds anyway.
But, if you absolutely must use a private RSS feed with authentication, there is an easy way to hook torrentwatch-xa up to it. There are many third-party RSS feed tools that can connect to RSS feeds that have authentication and then re-publish the feeds without authentication. I have not tried these apps listed here myself, but most of them should be able to do this: [http://www.makeuseof.com/tag/12-best-yahoo-pipes-alternatives-look/](http://www.makeuseof.com/tag/12-best-yahoo-pipes-alternatives-look/)

### Some Numbering Schemes Only Make Sense to Humans

The title "Holly Stage for 50 - 3" is meant to be interpreted as title = "Holly Stage for 50" and Episode 3, with Season 1 implied.
(Fans know that "Holly Stage for 50 - 3" really should be read as title = "Holly Stage for 49", Season 2, Episode 3, to further complicate matters.)
But the engine currently reads it as title = "Holly Stage for" and Season 50, Episode 3. Why? Because it was determined that the ## - ## pattern much more often means Season ## - Episode ##.

Sadly, because the engine was forced to make the choice, fans of "Holly Stage for 50" must "hack" the favorite to get it to download properly. There is no way to solve this problem without referring to some centralized database of anime titles or relying on some sort of AI, neither of which are going to happen in torrentwatch-xa any time soon.

### 720 and 1080 Without i or p

Titles such as Gamers! - 05 720.mkv are now recognized as Season 1, Episode 5 with resolution of 720p. For a long while, I allowed torrentwatch-xa to continue mismatching it as Season 5, Episode 720 on the off chance that the series is popular enough to have episodes that go that high, but such long-running series are just too outnumbered by the crews who release titles without i or p after 720 and 1080.

### Item Says It's an Old Favorite but is Actually New and Should Be Downloaded

This can happen if there are parallel numbering styles for the same torrent. For instance, with HorribleSubs Boku no Hero Academia 17 (Season 1, Episode 17), some crew on the Feedburner Anime (Aggregated) feed was re-releasing it later as Season 2, Episode 4. What happened then was that once torrentwatch-xa saw the Season 2 track, it jumped onto it and began ignoring the Season 1 numbering. The Season 1-numbered episodes would come out a few hours earlier than the re-release each week and not be auto-downloaded, making it seem like a detection failure.

This is not a bug. Technically, the season and episode detection engine is working properly; it's the crew that was renumbering episodes that was causing problems. The episode would auto-download once the Season 2 renumbering was released.

One easy workaround is to use the Favorite Episodes filter to restrict the downloads to just the Season 1 numbering: 1x1-1x99 would "trap" the series into Season 1 numbering.

### Items Drop Off the Feed Lists

If one starts an item downloading from a feed list, and that item is bumped off the end of the feed list by newer items on the next browser refresh, the item will not appear in the Downloaded or Downloading filtered lists even if the item still shows on the Transmission tab as downloading or downloaded. This is because the item simply is no longer in the list to be filtered and then shown by the Downloading and Downloaded filters. It seems counterintuitive until one understands that the Downloaded and Downloading filters are view filters on the feed list, not historical logs nor connected to Transmission's internal list.

### Watch Dir

transmission-daemon provides a watch directory feature. To enable it, use `watch-dir` and `watch-dir-enabled` in `settings.json`. In Debian/Ubuntu LINUX, `settings.json` is located at `/etc/transmission-daemon/settings.json`.