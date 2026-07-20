TODO List
===============

Next Version

Functional Changes

IN PROGRESS

- use a single curl_init() for as many curl_exec() calls as possible to improve performance
- strip down CURL options to bare minimum needed for Transmission RPC
- add some kind of error handler for multiple timed-out CURL requests in a row

- merge Javascript-side's #clientError div and showClientError() into #twError div and $.fn.showErrorPanel()
- maybe merge PHP-side's #errorDialog div into #twError div

- Add Favorite and Hide Item in client buttons bar don't go away if the item is already in favorites or already hidden, respectively

- if Transmission list is empty and cookie is older than 1 hour, switch to the All filter

- fix vertical alignment of title line in Transmission filter on iPhone (first line of text sits too low and is too close to the progress bar)

- fix slow timeout on first processClientData update of active torrent items after browser refresh (may be related to window.gotAllData)

- change reload button so that it doesn't clear the Filter textbox OR add Lock checkbox to the filter

- Started downloading item with Download button in web UI, then Trashed it completely, switched to Client = "Save .torrent/magnet: Files In Folder", and item switched from st_inCacheNotActive to st_downloading state; it switches back to st_downloading if Client is changed back to Transmission, even though the item is clearly not being downloaded

- check if st_noURL item state can be used when item is missing any URL

- History file dl_history is deleted when clearing Torrent cache
  (clear_cache('torrents') uses glob "dl_*" which matches dl_history)
  Fix: rename to just "history" in getDownloadHistoryFile()

Code Changes

IN PROGRESS

- combine more pattern detectors
  - word ## -|through|thru|to ##
  - word ## - word ##
  - ## - word ## (could be difficult)
  - word ## (including Month YYYY)

- after switching pattern detectors to word-based patterns, move Volume|, Chapter|, Season|, Episode| word matches to functions

- whatever is highlighted should stay highlighted even when the mouse moves; use lighter highlight for mouseover, darker for selected--same as in torrent list

- refactor old Add Favorites PHP functions to wrap addFavoriteFromParams()

- rewrite check_cache() and check_cache_episode() so that they are inverted; use check_cache() in processFeed()

- getBestTorrentOrMagnetLinks() only needs to be called once, when the feed is parsed and not in show_feed_item(); major rewrite to refer to feed cache unless cache is disabled

- rename $output to $result when appropriate: $result is typically a boolean result returned by a function; $output is typically a string returned by a function
- modify PicoFeed to provide getDescription for each RSS feed item description or each Atom feed item summary
- continue adding filter_input() in some reads (not writes) of $_GET or $_SERVER
- move set_client_passwd() and set_smtp_passwd() calls outside of writejSONConfigFile() so that they are only run when needed
- add function that detects errors in $config_values
- figure out window.gotAllData logic, maybe merge window.gotAllData into window.updatingClientData or remove one
  - setting window.gotAllData = 0 at end of processClientData causes progressBar to disappear from active torrents in #torrentlist_container
- continue cleaning up CSS with csslint.net

- use PicoFeed's Curl class where appropriate

RECENTLY DONE (1.9.6)

- used SEASON_WORDS constants across some pattern matching functions
- consolidated some matching functions in twxa_parse_match4.php through twxa_parse_match6.php
- fixed date validation bug in matchTitle6_2() (MM DD YYYY vs DD MM YYYY end-date not validated)
- gave all Update and Delete buttons in Favorites and Super-Favorites dialogs unique ids
- performance-tuned JQuery .each loops with updateMatchCounts and processTransmissionData
- removed empty CSS rulesets, added missing user-select property


## Validating files

These files have been completely validated (no functions inside them need improvement):

- twxa_html.php
- twxa_parse_match*.php
- twxa_test_parser.php

All other files have functions that need improvement or rewrites or validation.

NOTE: twxa_cache.php was previously listed as validated but has been removed pending review
of the clear_cache() / delete_cache_files() glob collision with dl_history.

## Throughout all versions

- conform all names to Zend naming convention detailed at: http://framework.zend.com/manual/1.12/en/coding-standard.naming-conventions.html

## Code cleanup tasks

- fix collision between ul#torrentlist and ul.torrentlist in phone.css and twxa_html.php
- move $items assignment from inside process_feed() up to process_all_feeds()
- continue simplifying/performance-tuning JQuery .each loops
- apply JQuery Best Practices from: http://lab.abhinayrathore.com/jquery-standards/

## Bugfixes

- with the episode filter it also ignores all the batches regardless of the setting to ignore batches
- adding a selected line as a favorite should toggle off the Favorites "heart" button in button bar and drop-down menu

- handle resolution and quality 1080p60
- check setupCacheDir() to see if file_exists() check fails even if download cache dir exists but permissions are wrong
- fix main UI Responsive Design especially for phones in portrait mode

## Improvements

- handle multibyte numerals when detecting season and episode
- handle when the date or number is at the very beginning of the item title as with some Japanese multibyte titles by checking if all the numbers (after codecs are removed) are at the front, then move them to the back and process normally. Either that, or in all cases, if there are lots of text behind the season x episode, keep the text as part of the title

- design class interface for TorrentClient and design child classes FolderClient and TransmissionClient
  - rename references to Transmission to some generic "torrent client" where appropriate and keep references to Transmission where appropriate, in case other torrent clients are added in the future

- design class interface for Favorite and rework all Add/Update/Delete Favorite functions to use it

- modify Configure > Feeds to allow re-ordering of Feeds
- change getCurl() to use PicoFeed's Curl class
- possibly rewrite torrent links to pass around an array of detected links to be tried in order from best to worst until one of them works, but this is difficult for Save Torrent In Folder behavior
- per-feed Filter capability to only show some items
- enable PicoFeed HTTP basic authentication functionality
- move checks for DownloadCacheDir and ConfigCacheDir in torrentwatch-xa till after attempt to create them if they are missing so that the error does not show in the web UI
- use 'use strict'; to clean up blocks of code in torrentwatch-xa.js starting from smaller blocks to larger
- consolidate/simplify code in displayFilter's switch-case block
- possibly allow user to un-Favorite or un-Hide items via contextual menu
- implement Ignore Batches feature per Favorite as well as globally
- store item version numbers in Favorite Last Downloaded field
- sometimes the History looks like it downloaded the same episode twice, but this is due to different numbering systems for the same episode, such as 1x26 = 2x1 for Attack on Titan; the ultimate way to fix it is to compare torrent hashes with all the cached hashes before downloading again, but this is not possible, as the torrent hash is not known until after a torrent is added
  - fix problem of different season and episode numbering by one or all of the below:
    - check feed item's notes for torrent hash, then compare this to the cache files
    - rewrite the Favorite Episodes filter functionality so that users can manually filter out other numbering styles via regex
    - adding a "stay in this season" checkbox to each Favorite
    - do not match Favorites in this feed

- add itemVersion handling to batches such as 1x03v2-1x05v2 (requires changing many match functions to handle version numbers)
- make list items double-tall for smartphone displays and wrap the title text properly
- possibly change Hide List from using favTitle to a list of regexes so the user can block anything they like

- times shown in feed list might not obey 'Time Zone' setting until next twxa_cli.php run, but log datestamps take effect immediately; maybe force a feed cache refresh immediately after 'Time Zone' is changed 
- if deleting active torrent manually before it completes, perhaps it should not be labeled as match_inCacheNotActive if it isn't actually in the download cache; in other words, this would require adding the ability to check the cache to the Javascript side
- add toggle to config for local/remote Transmission and disable features like Deep Directories for remote Transmission

- allow user to create Favorites from items in the History list
- convert event.keyCode to vanilla Javascript equivalent
- add error handling to the Transmission functions
- add config option "Videos Only" beneath "Require Episode Info" to only show items with at least one video quality
- add auto-refresh of entire list at regular intervals to show new feed items in web UI
- make the Favorites panel's Update button not close the panel after updating (same behavior as the Delete button)

- finish new "Serialization" concept as replacement for Episodes (now that print media can be faved)
  - check to make sure that new decimal PV numbering system works throughout entire app

- reduce use of global variables
  - $config_values['Global'] appears to be a crappy way of globally passing some data, maybe convert to $GLOBALS or replace with singleton Config object
  - $html_out (can't use passing by value because performance suffers badly as $html_out gets very large, so use passing by reference, but definitely do not return $html_out if passing by reference because of poor performance)

- rework History panel (and probably all other panels) so that it resizes according to Responsive Design
- allow user to clear individual items from the cache
- allow user to easily mark a torrent as the most recent episode downloaded in that season or in every season
- auto-delete old episodes that are replaced by REPACK or PROPER
- browser vertical scroll-bar leaves white region if it pops-out over the feed list; must refresh browser to resize and move feed list's right edge to the left
- harden the filter input against exploits
- write test suite and automate tests if possible
- POSSIBLY combine Downloading and Downloaded filters into one, using color-coding to differentiate between states
- add ability to select a torrent and report just that item as having a detection bug (requires move away from GitHub Issues)
- implement five-star rating system with separate subfolders for each to make watching the best shows first easier
- implement "probation" system for shows that haven't been liked enough to keep (perhaps zero stars out of five)
- sort torrents into resolutions by folder and allow for download of low-res version first, then high-res later, with toggle-able auto-delete of low-res version
- switch from JQuery to ES5 per youmightnotneedjquery.com
