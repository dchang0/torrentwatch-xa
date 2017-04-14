Throughout all versions
==========

- improve performance
- improve debugging and commenting
- refactor when reasonable
- conform all names to Zend naming convention detailed at: http://framework.zend.com/manual/1.12/en/coding-standard.naming-conventions.html

Tasks
==========

- times shown in feed list might not obey 'Time Zone' setting until next rss_dl.php run, but log datestamps take effect immediately; maybe force a feed cache refresh immediately after 'Time Zone' is changed 

- "Error connecting to Transmission" Javascript alert stays open even after successful connection to Transmission and often occurs even if the problem is some unrelated PHP Fatal error

- ON HOLD FOR PERFORMANCE REASONS: continue converting detectItem() if...else control structures to switch...case, breaking out code into separate functions

- sometimes blank favorites may be added to the favorites list; these appear to be when $itemtags data gets inserted into $config_values['Favorites'] directly like so:

73[] = title => HorribleSubs Alice to Zouroku - 01 720p.mkv
73[] = link => http://www.nyaa.se/?page=download&tid=913586
73[] = description => Torrent: http://www.nyaa.se/?page=download&tid=913586Size: 603.16MBAuthorized: YesMagnet Link Comment: #horriblesubs@irc.rizon.net Proudly translated and presented by the HorribleSubs Fansubbing Team. Visit our website for DDL links, schedules, and latest news.
73[] = category => Anime
73[] = guid => http://tokyotosho.info/details.php?id=1084434
73[] = pubDate => Apr 02, 08:49

Seems to be reproducible by: Add a favorite with item tags (HorribleSubs torrents) with Add a favorite Javascript contextual menu; check Favorite in web UI and config file and note index number (77 for a real example; clear all caches; refresh page; check Favorite in web UI and config file and see corruption of the Favorite (also 77, the last index). Looks like a HorribleSubs added-by-JS Favorite that is the last index gets overwritten but not just any last index... Also appears to be accompanied by or caused by a SEGFAULT that may occur in transmission_add_torrent()

Check to see if any HorribleSubs added-by-JS Favorite gets overwritten no matter what index it has. Yes, it appears that the corruption finds the HorribleSubs added-by-JS Favorite, even if its index number is changed to a lower number AND it is not the last Favorite in the list in the config file. Strangely, another added-by-JS Favorite that is not a HorribleSubs torrent has not been affected in all this testing. It may be related to the torrent that downloads immediately after emptying the cache (the unaffected added-by-JS Favorite might have no episodes in the list)

- sometimes the History looks like it downloaded the same episode twice, but this is due to different numbering systems for the same episode, such as 1x26 = 2x1 for Attack on Titan; the way to fix it is to compare torrent hashes with all the cached hashes before downloading again
- $config_values['Global'] appears to be a crappy way of globally passing some data--maybe improve it
- Disable Hide List does disable Hide Show from contextual menus but doesn't hide the Configure > Hide List tab or at least mark it disabled
- handle resolution and quality 1080p60 (1080p gets recognized and removed, leaving behind 60)
- decide what to about folder client, then remove all: $client = $config_values['Settings']['Client'];
- after clearing all caches, re-downloaded torrents appear to have seed ratios of Infinity due to item.downloadedEver being 0 at torrentwatch-xa.js on line 480
- verify the settings and complete their hints in the config panels
- fix mkdir(): Permission denied in /var/lib/torrentwatch-xa/lib/tor_client.php on line 280

- PHP Deprecated:  Methods with the same name as their class will not be constructors in a future version of PHP; myAtomParser has a deprecated constructor in /var/lib/torrentwatch-xa/lib/atomparser.php on line 5
  - original source code here: http://www.the-art-of-web.com/php/atom/?hilite=atom+parser
  - upgrade atomparser.php and transfer customized lastRSS code across while doing so

- convert event.keyCode to event.which in torrentwatch-xa.js per https://api.jquery.com/event.which/
- break client_add_torrent() into smaller functions
- add error handling to the Transmission functions
- set session-wide default seed ratio using Transmission RPC session-set on seedRatioLimit
- refactor transmission_rpc request code that is used over and over in tools.php functions
- migrate jquery.tinysort.js to tinysort (no longer dependent on jquery and faster) http://tinysort.sjeiti.com
- upgrade jquery.cookie.js to js-cookie at https://github.com/js-cookie/js-cookie/tree/v1.5.1
- add Feed Title input above each Feed URL
- add config option "Videos Only" beneath "Episodes Only" to only show items with at least one video quality
- add toggle to config for local/remote Transmission and disable features like Deep Directories and Watch Directory for remote Transmission
- fix the filterBar so that it wraps properly when screen is narrow (responsive design, may not be necessary with removal of filters)
- fix the showFilter() redefinition from line 89 (torrentwatch-xa.js:106) and out-of-scope (:127)  problems found by JSLint 
- add auto-refresh of list (might already auto-refresh when favorite is matched and starts download)
- if torrent is deleted using contextMenu, the clientButtons delete and trash buttons sometimes don't know about it until the page is refreshed
- adding a selected line as a favorite should toggle off the Favorites "heart" button in button bar and drop-down menu
- repair phone.css button bar location so it floats just above bottom edge of screen even when scrolling
- how does <li id="id_###"> get its class overridden for downloading/downloaded items and why does clientID not match id when it happens?
- fix debug console in web UI
- on page reload, Javascript doesn't redraw infoDiv on items that are still downloading or seeding--figure out how to make that function call
- make the Favorites panel's Update button not close the panel after updating (same behavior as the Delete button)
- make the Feeds panel's Update buttons not close the panel after updating (same behavior as the Delete buttons)

- finish new "Serialization" concept as replacement for Episodes (now that print media can be faved)
  - check to make sure that new decimal PV numbering system works throughout entire app
  - remove "range" detection logic from detection engine (assume all preg_match_all() calls find just one match)
  - finish counting batches (Seasons, Volumes, ranges of Episodes, ranges of Chapters, etc.) as "Serialized Items"
  - fix Episodes Only config toggle

- finish or remove tvDB support (commented out as of initial cloning of TorrentWatch-X 0.8.9)

- replace global variables EXCEPT $html_out with proper parameter passing
  - $twxa_version
  - $config_values (not likely--will probably increase CPU util too much)
  - $hit
  - $test_run
  - $matched
  - $config_out
  - $verbosity

- add Test SMTP button to Configure > Notify or automatically test on Save and use Javascript alert to show failure
- Safari browser seems to semi-randomly fire torrentwatch-xa.php call in JQuery twice, second time about 1.2ms after the first, in the middle of rss_perform_matching()'s main foreach loop processing the first feed
- remove support for Internet Explorer
- rework History panel (and probably all other panels) so that it resizes according to Responsive Design
- add ability to gunzip torrents coming from some feeds (such as ezRSS.it)
- make the Interface>Font Size actually affect the entire web UI instead of just the Configuration dialog
- refresh button in toolbar can trigger Test Hit downloads just like browser refresh
- separate out the torrents that were direct-downloaded and don't match any favorites from the ones that do match favorites
- improve history system so that flushing the feed cache and deleting torrents does not easily fool torrentwatch-xa into thinking matching torrents must be downloaded again
  - if it does download again, honor the seed ratio from the favorite that it matched
- allow user to easily mark a torrent as the most recent episode downloaded in that season or in every season
- delete old episodes that are replaced by REPACK or PROPER
- browser vertical scroll-bar leaves white region if it pops-out over the feed list; must refresh browser to resize and move feed list's right edge to the left
- convert all "template" files into real templates like Smarty, etc. Current .tpl files are really just .php files.
- harden the filter input against exploits
- sqlite for caching and history
- write test suite and automate tests if possible
- POSSIBLY combine Downloading and Downloaded filters into one, using color-coding to differentiate between states
- add ability to select a torrent and report just that item as having a detection bug (requires move away from GitHub Issues)
- REVAMP EPISODE DETECTION TO USE TORRENT CONTENTS (IF TORRENT CONTAINS ONLY ONE FILE >1MB & <1GB, MUST BE ONE EPISODE), BUT THIS REQUIRES DOWNLOADING EVERY TORRENT FILE IN EVERY FEED, WHICH WOULD BE VERY SLOW
- implement five-star rating system with separate subfolders for each to make watching the best shows first easier
- implement "probation" system for shows that haven't been liked enough to keep (perhaps zero stars out of five)
- sort torrents into resolutions by folder and allow for download of low-res version first, then high-res later, with toggle-able auto-delete of low-res version
- move season and episode pattern matching (regexp) from hardcode to Configuration tab so users can configure them
- make Favorites list cut off by letter, not whole words, and/or make list horizontal scrolling (hard to do)
