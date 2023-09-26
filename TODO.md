TODO List
===============

## Validating files

These files have been completely validated (no functions inside them need improvement):

- twxa_cache.php
- twxa_html.php
- twxa_parse_match*.php
- twxa_test_parser.php

All other files have functions that need improvement or rewrites or validation.

## Throughout all versions

- improve performance
- improve debugging and commenting
- refactor when reasonable
- conform all names to Zend naming convention detailed at: http://framework.zend.com/manual/1.12/en/coding-standard.naming-conventions.html

## Testing tasks

- what is the purpose of getClient()'s `clientId`, `clientId_`, and `client_id` and the difference between them? `clientId_` is how torrentwatch-xa.js keeps track of items in #transmission_list, but what does `client_id` do? client_id seems to be needed in .processSelected()

## Code cleanup tasks

- fix collision between ul#torrentlist and ul.torrentlist in phone.css and twxa_html.php
- move $items assignment from inside process_feed() up to process_all_feeds()
- simplify/performance-tune JQuery code, especially implicit .each loops
- apply JQuery Best Practices from: http://lab.abhinayrathore.com/jquery-standards/

## Bugfixes

- if torrent item is removed from another browser session, this browser doesn't figure it out
- with the episode filter it also ignores all the batches regardless of the setting to ignore batches

- fix debug console in web UI
  - merge errorDialog into twError
  - use error function instead of alert() in torrentwatch-xa.js

- adding a selected line as a favorite should toggle off the Favorites "heart" button in button bar and drop-down menu
- "Error connecting to Transmission" Javascript alert stays open even after successful connection to Transmission and often occurs even if the problem is some unrelated PHP Fatal error
- handle resolution and quality 1080p60
- check setupCacheDir() to see if file_exists() check fails even if download cache dir exists but permissions are wrong
- fix main UI Responsive Design especially for phones in portrait mode

## Improvements

- design class interface for TorrentClient and design child classes FolderClient and TransmissionClient
  - rename references to Transmission to some generic "torrent client" where appropriate and keep references to Transmission where appropriate, in case other torrent clients are added in the future

- modify Configure > Feeds to allow re-ordering of Feeds
- change getCurl() to use PicoFeed's Curl class
- possibly rewrite torrent links to pass around an array of detected links to be tried in order from best to worst until one of them works, but this is difficult for Save Torrent In Folder behavior
- per-feed Filter capability to only show some items
- handle multibyte numerals when detecting season and episode
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
- convert event.keyCode to event.which in torrentwatch-xa.js per https://api.jquery.com/event.which/
- add error handling to the Transmission functions
- add config option "Videos Only" beneath "Require Episode Info" to only show items with at least one video quality
- add auto-refresh of entire list at regular intervals to show new feed items in web UI
- make the Favorites panel's Update button not close the panel after updating (same behavior as the Delete button)

- finish new "Serialization" concept as replacement for Episodes (now that print media can be faved)
  - check to make sure that new decimal PV numbering system works throughout entire app

- reduce use of global variables 
  - $config_values['Global'] appears to be a crappy way of globally passing some data, maybe convert to $GLOBALS
  - $html_out (can't use passing by value because performance suffers badly as $html_out gets very large, so use passing by reference, but definitely do not return $html_out if passing by reference because of poor performance)
  - $twxa_version

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