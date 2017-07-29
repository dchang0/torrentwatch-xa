TODO List
===============

## Validating files

These files have been completely validated (no functions inside them need improvement):

twxa_atomparser.php
twxa_cache.php
twxa_html.php
twxa_lastRSS.php
twxa_parse_match*.php
twxa_test_parser.php

All other files have functions that need improvement or rewrites or validation.

## Throughout all versions

- improve performance
- improve debugging and commenting
- refactor when reasonable
- conform all names to Zend naming convention detailed at: http://framework.zend.com/manual/1.12/en/coding-standard.naming-conventions.html

## Testing tasks

- make sure auto-del works even when caching is disabled
- test _Save torrent in folder_ client and make sure progress bar works properly in this mode
- validate Deep Directories feature
- what is the purpose of `clientId_` and `client_id` and the difference between them? `clientId_` is how torrentwatch-xa.js keeps track of items in #transmission_list, but what does `client_id` do?
- verify the settings and complete their hints in the config panels
- test the Atom-related functions and conform them to their equivalent RSS functions if necessary
- find out what configure.js, json2.js, and reset.css are for

## Code cleanup tasks

- go through CSS and remove unused styles
- div#linkButtons ID is assigned in three elements--is this correct?
- fix collision between ul#torrentlist and ul.torrentlist in phone.css
- use twxa_parse.php:88 block to make date checker function for reuse elsewhere
- rename references to Transmission to some generic "torrent client" where appropriate and keep references to Transmission where appropriate, in case other torrent clients are added in the future
- move $items assignment from inside process_feed() up to process_all_feeds()
- break client_add_torrent() into smaller functions
- refactor transmission_rpc request code that is used over and over in twxa_tools.php functions
- apply JQuery Best Practices from: http://lab.abhinayrathore.com/jquery-standards/
- remove support for Internet Explorer 6 through 8

## Bugfixes

- fix debug console in web UI
- adding a selected line as a favorite should toggle off the Favorites "heart" button in button bar and drop-down menu
- improve handling of "Feed inaccessible" (usually 403 or 404 errors on the URL)
- PROPER/REPACK notation must be rolled into item version numbering (probably set as version 99 or 999)
- "Error connecting to Transmission" Javascript alert stays open even after successful connection to Transmission and often occurs even if the problem is some unrelated PHP Fatal error

- sometimes blank favorites may be added to the favorites list; these appear to be when $itemtags data gets inserted into $config_values['Favorites'] directly like so:

73[] = title => HorribleSubs Alice to Zouroku - 01 720p.mkv
73[] = link => http://www.nyaa.se/?page=download&tid=913586
73[] = description => Torrent: http://www.nyaa.se/?page=download&tid=913586Size: 603.16MBAuthorized: YesMagnet Link Comment: #horriblesubs@irc.rizon.net Proudly translated and presented by the HorribleSubs Fansubbing Team. Visit our website for DDL links, schedules, and latest news.
73[] = category => Anime
73[] = guid => http://tokyotosho.info/details.php?id=1084434
73[] = pubDate => Apr 02, 08:49

Seems to be reproducible by: Add a favorite with item tags (HorribleSubs torrents) with Add a favorite Javascript contextual menu; check Favorite in web UI and config file and note index number (77 for a real example; clear all caches; refresh page; check Favorite in web UI and config file and see corruption of the Favorite (also 77, the last index). Looks like a HorribleSubs added-by-JS Favorite that is the last index gets overwritten but not just any last index... Also appears to be accompanied by or caused by a SEGFAULT that may occur in transmission_add_torrent()

Check to see if any HorribleSubs added-by-JS Favorite gets overwritten no matter what index it has. Yes, it appears that the corruption finds the HorribleSubs added-by-JS Favorite, even if its index number is changed to a lower number AND it is not the last Favorite in the list in the config file. Strangely, another added-by-JS Favorite that is not a HorribleSubs torrent has not been affected in all this testing. It may be related to the torrent that downloads immediately after emptying the cache (the unaffected added-by-JS Favorite might have no episodes in the list)

- handle resolution and quality 1080p60 (1080p gets recognized and removed, leaving behind 60)
- fix mkdir(): Permission denied in /var/lib/torrentwatch-xa/lib/twxa_torrent.php around line 280
- Safari browser seems to semi-randomly fire torrentwatch-xa.php call in JQuery twice, second time about 1.2ms after the first, in the middle of rss_perform_matching()'s main foreach loop processing the first feed

## Improvements

- decide how and when global Default Seed Ratio should override the per-feed Seed Ratio when creating new Favorites or starting manual downloads
- sometimes the History looks like it downloaded the same episode twice, but this is due to different numbering systems for the same episode, such as 1x26 = 2x1 for Attack on Titan; the ultimate way to fix it is to compare torrent hashes with all the cached hashes before downloading again, but this is not possible, as the torrent hash is not known until after a torrent is added
  - fix problem of different season and episode numbering by one or all of the below:
    - check feed item's notes for torrent hash, then compare this to the cache files
    - rewrite the Favorite Episodes filter functionality so that users can manually filter out other numbering styles via regex
    - adding a "stay in this season" checkbox to each Favorite
    - do not match Favorites in this feed

- use error function instead of alert() in torrentwatch-xa.js
- add itemVersion handling to batches such as 1x03v2-1x05v2 (requires changing many match functions to handle version numbers)
- make list items double-tall for smartphone displays and wrap the title text properly
- $config_values['Global'] appears to be a crappy way of globally passing some data--maybe improve it
- times shown in feed list might not obey 'Time Zone' setting until next twxacli.php run, but log datestamps take effect immediately; maybe force a feed cache refresh immediately after 'Time Zone' is changed 
- if deleting active torrent manually before it completes, perhaps it should not be labeled as match_inCacheNotActive if it isn't actually in the download cache; in other words, this would require adding the ability to check the cache to the Javascript side
- add toggle to config for local/remote Transmission and disable features like Deep Directories for remote Transmission
- if keeping st_downloaded and st_downloading in the PHP side, change st_favReady to st_downloaded for folder client after checking to make sure the .torrent file was successfully downloaded
- convert Configure > Feeds one-form-per-feed into one form for all the feeds
  - add Feed Title input above each Feed URL
  - add extra input for website of feed operator, to which the feed title in headers will link
- add a bulk import form for Favorites (big textarea, one line per title, all of them receive the same defaults and become individual Favorites)
- allow user to create Favorites from items in the History list
- convert event.keyCode to event.which in torrentwatch-xa.js per https://api.jquery.com/event.which/
- add error handling to the Transmission functions
- migrate jquery.tinysort.js to tinysort (no longer dependent on jquery and faster) http://tinysort.sjeiti.com
- upgrade jquery.cookie.js to js-cookie at https://github.com/js-cookie/js-cookie/tree/v1.5.1
- add config option "Videos Only" beneath "Require Episode Info" to only show items with at least one video quality
- add auto-refresh of list (might already auto-refresh when favorite is matched and starts download)
- make the Favorites panel's Update button not close the panel after updating (same behavior as the Delete button)
- make the Feeds panel's Update buttons not close the panel after updating (same behavior as the Delete buttons)
- finish new "Serialization" concept as replacement for Episodes (now that print media can be faved)
  - check to make sure that new decimal PV numbering system works throughout entire app
- Disable Hide List does disable Hide Show from contextual menus but doesn't hide the Configure > Hide List tab or at least mark it disabled
- convert Hide List from hiding individual titles to hiding by pattern matching (just like the Favorites Filter)

- replace global variables EXCEPT $html_out with proper parameter passing
  - $config_values (not likely--will probably increase CPU util too much)
  - $hit
  - $itemState  <--- IMPORTANT, as the use of global $itemState makes most of twxa_feed.php's functions hard to maintain
  - $config_out <--- IMPORTANT, as the use of global $config_out makes most of twxa_config_lib.php's functions hard to maintain

- add Test SMTP button to Configure > Notify or automatically test on Save and use Javascript alert to show failure
- rework History panel (and probably all other panels) so that it resizes according to Responsive Design
- add ability to gunzip torrents coming from some feeds (such as ezRSS.it)
- allow user to clear individual items from the cache
- allow user to easily mark a torrent as the most recent episode downloaded in that season or in every season
- auto-delete old episodes that are replaced by REPACK or PROPER
- browser vertical scroll-bar leaves white region if it pops-out over the feed list; must refresh browser to resize and move feed list's right edge to the left
- convert all "template" files into real templates like Smarty, etc. Current .tpl files are really just .php files.
- harden the filter input against exploits
- write test suite and automate tests if possible
- POSSIBLY combine Downloading and Downloaded filters into one, using color-coding to differentiate between states
- add ability to select a torrent and report just that item as having a detection bug (requires move away from GitHub Issues)
- REVAMP EPISODE DETECTION TO USE TORRENT CONTENTS (IF TORRENT CONTAINS ONLY ONE FILE >1MB & <1GB, MUST BE ONE EPISODE), BUT THIS REQUIRES DOWNLOADING EVERY TORRENT FILE IN EVERY FEED, WHICH WOULD BE VERY SLOW
- implement five-star rating system with separate subfolders for each to make watching the best shows first easier
- implement "probation" system for shows that haven't been liked enough to keep (perhaps zero stars out of five)
- sort torrents into resolutions by folder and allow for download of low-res version first, then high-res later, with toggle-able auto-delete of low-res version

