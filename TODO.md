TODO List
===============

Next Up
-------

- `twxa_parse.php:200` — [Bug] handle BD1280x720p: `detectResolution()`'s `$bDHRegEx` matches the `BD1280` part (1280 is not in the allowed list), and because the standalone-BD / `###p|i` / `WxH` checks form an `if/else if` chain, the `720p` inside `BD1280x720p` is never detected. Convert the chain into sequential `if ($resolution === "")` blocks so a non-matching BD number falls through to `$hRegEx`, which catches `720p`; verify BD720p/BD1080p/BD### still work and that no resolution is double-detected

Source TODO Comments
--------------------

### `javascript/torrentwatch-xa.js`

- `torrentwatch-xa.js:106` — [Performance] replace hide() with style.display = 'none'
- `torrentwatch-xa.js:142` — [Performance] maybe move this block outside the .each loop
- `torrentwatch-xa.js:280` — [Cleanup] simplify JSON error handling (start)
- `torrentwatch-xa.js:314` — [Cleanup] simplify JSON error handling (end)
- `torrentwatch-xa.js:391` — [Feature] detect if item is in download cache (is managed by torrentwatch-xa) and change appearance somehow
- `torrentwatch-xa.js:530` — [Feature] possibly update button bar for items in filters other than Transmission
- `torrentwatch-xa.js:587` — [Bug] test .tc_downloading; might need to separate from .st_waitTorCheck
- `torrentwatch-xa.js:612` — [Bug] we might need to do this only for .tc_downloading
- `torrentwatch-xa.js:614` — [Bug] might need to set .torInfo text to nothing
- `torrentwatch-xa.js:650` — [Bug] didn't work when querySelector returns null (commented-out code)
- `torrentwatch-xa.js:654` — [Cleanup] test the switch from .val() to .value (commented-out code)
- `torrentwatch-xa.js:678` — [Bug] maybe block other interim states (do not show start button on st_waitTorCheck, etc.)
- `torrentwatch-xa.js:882` — [Info] lowering this value causes getClientData to update faster
- `torrentwatch-xa.js:958` — [Feature] maybe scroll to the newly-added name and highlight it
- `torrentwatch-xa.js:1033` — [Feature] maybe scroll to the newly-added name and highlight it
- `torrentwatch-xa.js:1083` — [Bug] also needs to handle episode with length > 8 chars
- `torrentwatch-xa.js:1104` — [Feature] finish code to "pin open" Feeds panel when Save button is pressed
- `torrentwatch-xa.js:1137` — [Cleanup] does this really need #show_legend and #clear_cache?
- `torrentwatch-xa.js:1300` — [Cleanup] may not need window.favving any more after changing addFavoriteFromgET()
- `torrentwatch-xa.js:1432` — [Cleanup] this block might be unnecessary since the same cleanup is done in processClientData
- `torrentwatch-xa.js:1448` — [Bug] double-check to make sure the list length is reduced by 1

### `torrentwatch-xa.php`

- `torrentwatch-xa.php:16` — [Cleanup] `array_keys($_GET);` is a no-op statement
- `torrentwatch-xa.php:195` — [Cleanup] guard access to `$config_values['Settings']['Check for Updates']` instead of relying on raw array access
- `torrentwatch-xa.php:141` — [Cleanup] `display_history()`, `display_global_config()`, `display_legend()`, and `display_clearCache()` discard their return values; the output survives only because the abandoned `ob_start()` buffer flushes at `exit()`. Use `ob_get_clean()` and echo the return value consistently, like `display_superfavorites()` (see call sites at lines 146, 208, 211, 214, 217)
- `torrentwatch-xa.php:226` — [Bug] unescaped `$phpSelf`/`$requestuRI` interpolated into a JS alert string; a crafted URL containing a quote breaks the JavaScript or enables self-XSS
- `torrentwatch-xa.php:727` — [Cleanup] `writeToLog()` logs a raw float `$main_timer` as the start time; use a formatted `date()` timestamp instead
- `torrentwatch-xa.php:641` — [Bug] check all Also Save Dir paths in all Favorites
- `torrentwatch-xa.php:667` — [Cleanup] replace with filter_input(INPUT_COOKIE, 'VERSION-CHECK')

### `twxa_cli.php`

- `twxa_cli.php:6` — [Cleanup] error_reporting excludes E_NOTICE, so the `$config_values['Settings']['Log Level']` write in parse_args (line 27) can silently auto-vivify an undefined `$config_values` if readjSONConfigFile() failed to populate it (corrupt config JSON)

### `templates/feed_item.php`

- `feed_item.php:13` — [Cleanup] improve passing of $guess[], $id, $ulink, and $feed into this file

### `lib/class.phpmailer.php`

- `class.phpmailer.php:1382` — [Cleanup] If possible, this should be changed to escapeshellarg. Needs thorough testing.

### `lib/PicoFeed/Parser/Atom.php`

- `Atom.php:257` — [Feature] add magnet: link validation

### `lib/PicoFeed/Parser/Rss20.php`

- `Rss20.php:265` — [Feature] add magnet: link validation

### `lib/twxa_cache.php`

- `twxa_cache.php:147` — [Feature] does Ignore Batches need to be implemented here?
- `twxa_cache.php` — [Feature] implement the periodic cache-sync pass that populates the client-metadata fields currently reserved (as `null`) in the `dl_*.json` schema introduced in 1.11.0, so the download cache tracks progress and seeding over time (the "seeded amounts" extensibility goal). Today the cache is write-only at download time: `add_cache()` (`twxa_cache.php:104`) and `transmission_add_torrent()` (`twxa_torrent.php:383`) store only what is available then (`torHash`, `torId`, `downloadDir`, `seedRatio`, plus the `parsed` block and provenance), leaving `addedDate`, `totalSize`, `doneDate`, `status`, `name`, `uploadRatio`, `percentDone`, and `leftUntilDone` permanently `null`
  - proposed approach: add a new function, e.g. `syncCacheWithClientData($torrents)`, to `twxa_cache.php` and call it from `auto_del_seeded_torrents()` (`twxa_torrent.php:62`), which already fetches the complete torrent list every run via `getClientData(false)` (`twxa_torrent.php:5`); build a `hashString → torrent` map, then for each `dl_*.json` entry that has a `torHash`, merge the live fields into the cache file with `updateCacheData()`
  - fields to persist (stable/progress data drawn from the `getClientData()` field list): `addedDate`, `doneDate`, `uploadRatio`, `percentDone`, `leftUntilDone`, `totalSize`, `status`, `name`, and optionally `seedRatioLimit` and `errorString`; do not persist transient rates (`rateDownload`, `rateUpload`, `eta`, peer counts)
  - matching: Transmission `hashString` equals the cache entry's `torHash`; skip cache entries without a `torHash` (folder-client items) and skip torrents not present in the cache
  - failure handling: if `getClientData()` returns an RPC error (non-`success` result), skip the sync for that run entirely so an unreachable or failing Transmission never wipes or stales the cached values; mirror the guard style already used in `auto_del_seeded_torrents()`
  - concurrency: reuse `updateCacheData()` (read-merge-write under `LOCK_EX`) so the sync writer and the add-time writers cannot clobber each other; skip the rewrite when no field value actually changed so `dateAdded`/file mtimes are not touched on every run
  - after implementing, update the 1.11.0 CHANGELOG Functional Changes entry that currently describes these fields as "reserved" to say they are now populated by the sync pass

### `lib/twxa_config_lib.php`

- `twxa_config_lib.php:321` — [Cleanup] make sure $configCacheDir looks like a path
- `twxa_config_lib.php:611` — [Feature] should we check if name is unique in list of Favorites?

### `lib/twxa_feed.php`

- `twxa_feed.php:117` — [Cleanup] validate URLs in array using filter_var($url, FILTER_VALIDATE_URL)
- `twxa_feed.php:563` — [Bug] also tie $startedDownload to successful write to cache

### `lib/twxa_feed_parser_wrapper.php`

- `twxa_feed_parser_wrapper.php:119` — [Bug] figure out why magnet: links are filtered out by the ContentFilter even though they are supposed to be whitelisted, then re-enable content filtering (workaround: disableContentFiltering() called on line 120)
- `twxa_feed_parser_wrapper.php:134` — [Cleanup] get rid of ['feed']
- `twxa_feed_parser_wrapper.php:158` — [Feature] where is the item description?

### `lib/twxa_html.php`

- `twxa_html.php:28` — [Cleanup] remove this if PHP side is capable of verifying completed downloads

### `lib/twxa_parse.php`

- `twxa_parse.php:414` — [Bug] cascade down through, removing immediately-surrouding dashes
- `twxa_parse.php:431` — [Question] why do these HTML entities make it into our $ti in the first place?
- `twxa_parse.php:441` — [Cleanup] maybe switch to (C\d\d) regex
- `twxa_parse.php:508` — [Feature] detect video-related words like Sub and Dub
- `twxa_parse.php:523,577-580` — [Bug] FIXED in 1.11.0: `detectMatch()`'s `favTitle` is not guaranteed to be a literal prefix of the raw title (it is post-processed by `removeEmptyParens()` line 523, `collapseExtraSeparators()`, and crew-name reattachment lines 577-580). `check_cache_episode()` previously relied on `favTitle` being a prefix of the cache filename (`dl_` + `sanitizeFilename($title)`) and extracted that prefix with `substr($file, 3, strlen($guess['favTitle']))`; when `favTitle` was transformed relative to the raw title the match failed, causing duplicate downloads. The download cache now stores the parsed metadata in `dl_*.json` and `check_cache_episode()` compares the stored `favTitle` directly, so the comparison no longer depends on `favTitle` being a filename prefix
- `twxa_parse.php:592` — [Cleanup] replace this with mediaType

### `lib/twxa_parse_match.php`

- `twxa_parse_match.php:8` — [Cleanup] ####-#### as v####-#### + ####-#### (show as chapters first #### to last ####, even though it may skip some chapters)
- `twxa_parse_match.php:37` — [Cleanup] ####-#### as v####-#### + #### (show as chapters first #### to last ####, even though it may skip some chapters)
- `twxa_parse_match.php:266` — [Performance] maybe short-circuit S### - EEE for performance
- `twxa_parse_match.php:269` — [Cleanup] could become part of word##word##
- `twxa_parse_match.php:276` — [Cleanup] could become part of word##word##
- `twxa_parse_match.php:277` — [Feature] add S01 PART2 as part of word##word##
- `twxa_parse_match.php:284` — [Cleanup] could become part of ### - word ###
- `twxa_parse_match.php:297` — [Cleanup] could become part of "word ### - word ###"
- `twxa_parse_match.php:310` — [Cleanup] could become part of ##word word##
- `twxa_parse_match.php:317` — [Cleanup] could become part of "### - ### word"
- `twxa_parse_match.php:324` — [Cleanup] could become part of word##word##
- `twxa_parse_match.php:331` — [Cleanup] maybe combine with isolated ##x##
- `twxa_parse_match.php:383` — [Cleanup] could become part of ### - word ###
- `twxa_parse_match.php:402` — [Cleanup] could become part of ##word ##
- `twxa_parse_match.php:433` — [Feature] handle v### (YYYY) (this becomes word### (YYYY), v in this case is print volume, but handle video volume too
- `twxa_parse_match.php:438` — [Feature] 2_27 (YYYY) (Season 1)
- `twxa_parse_match.php:441` — [Cleanup] could become part of "word ### - word ###"
- `twxa_parse_match.php:448` — [Cleanup] could become part of word## word##
- `twxa_parse_match.php:455` — [Cleanup] could become part of isolated ### - ###
- `twxa_parse_match.php:462` — [Cleanup] could become part of word ## - ##
- `twxa_parse_match.php:469` — [Cleanup] could become part of isolated ### - ###
- `twxa_parse_match.php:504` — [Feature] 2_38 ## several words Volume|Vol.##
- `twxa_parse_match.php:507` — [Cleanup] could become part of ##word word##
- `twxa_parse_match.php:518` — [Feature] 2_41 ## words EE (be careful about catching too many false positives)
- `twxa_parse_match.php:630` — [Bug] SP# (Special #) was removed at some point in the past and just put back--figure out better way

### `lib/twxa_parse_match2.php`

- `twxa_parse_match2.php:71` — [Feature] switch to handle these patterns
- `twxa_parse_match2.php:78` — [Feature] add S01 PART2 as part of word##word##
- `twxa_parse_match2.php:1073` — [Cleanup] add $detVid since we have it already
- `twxa_parse_match2.php:1168` — [Bug] use smaller threshold for videos, larger one for print media

### `lib/twxa_parse_match3.php`

- `twxa_parse_match3.php:36` — [Bug] make sure MM and DD have leading zeros if needed
- `twxa_parse_match3.php:692` — [Bug] v### - ### - (YYYY) but it currently gets handled by v###-###, ignoring (YYYY)

### `lib/twxa_torrent.php`

- `twxa_torrent.php:45` — [Bug] maybe test || $checkCache === false too
- `twxa_torrent.php:112` — [Cleanup] does this errorDialog work? Replace it with outputErrorDialog() (also, this echo pollutes stdout when reached from twxa_cli.php/cron)
- `twxa_torrent.php:154` — [Cleanup] break this out into a small function
- `twxa_torrent.php:156` — [Cleanup] does this errorDialog work? Replace it with outputErrorDialog()
- `twxa_torrent.php:413` — [Feature] if $fav is null, then loop through the Favorites to see if the title matches a Favorite and get the Favorite's Download Dir
- `twxa_torrent.php:500` — [Feature] search through the retrieved content itself using detectahrefsInString()

### `lib/twxa_tools.php`

- `twxa_tools.php:135` — [Bug] failed to write, send error to HTML
- `twxa_tools.php:152` — [Bug] writeToLog called with 3 args (message, rc."\n", 2) but it only takes 2; the "\n" and level are discarded, so the message is logged at the wrong level

Bugfixes
--------

- change reload button so that it doesn't clear the Filter textbox OR add Lock checkbox to the filter
- harden the filter input text field against exploits
- if deleting active torrent manually before it completes, perhaps it should not be labeled as match_inCacheNotActive if it isn't actually in the download cache; in other words, this would require adding the ability to check the cache to the Javascript side
- fix main UI semi-Responsive Design especially for phones in portrait mode
  - in iPhone view: fix width of green section bars, confirmation pop-up for X button is too wide for screen, add "burger" button for filter buttons  
  - make list items double-tall for smartphone displays and wrap the title text properly
- sometimes the History looks like it downloaded the same episode twice, but this is due to different numbering systems for the same episode, such as 1x26 = 2x1 for Attack on Titan; the ultimate way to fix it is to compare torrent hashes with all the cached hashes before downloading again, but this is not possible, as the torrent hash is not known until after a torrent is added
  - fix problem of different season and episode numbering by one or all of the below:
    - check feed item's notes for torrent hash, then compare this to the cache files
    - rewrite the Favorite Episodes filter functionality so that users can manually filter out other numbering styles via regex
    - adding a "stay in this season" checkbox to each Favorite
    - do not match Favorites in this feed

Improvements
------------

- if the episode looks like 720 or 1080, check if there is already a resolution/quality detected; if so, then it is more likely that the 720 or 1080 is an episode number than a resolution
- rename $output to $result when appropriate: $result is typically a boolean result returned by a function; $output is typically a string returned by a function
- continue adding filter_input() in some reads (not writes) of $_GET or $_SERVER
- convert event.keyCode to vanilla Javascript equivalent
- CSS: establish a systematic `z-index` scale — current values are 2, 3, 4, 5, 99, 100 with no logical layering
- continue simplifying/performance-tuning JQuery .each loops
- add function that detects errors in $config_values
- figure out window.gotAllData logic, maybe merge window.gotAllData into window.updatingClientData or remove one
  - premature gotAllData=true removed from updateMatchCounts (was causing slow first update after browser refresh), but the merge/remove idea is still open
- combine more pattern detectors
  - word ## -|through|thru|to ##
  - word ## - word ##
  - ## - word ## (could be difficult)
  - word ## (including Month YYYY)
- after switching pattern detectors to word-based patterns, move Volume|, Chapter|, Season|, Episode| word matches to functions
- change SuperFavorite so that it doesn't re-add an intentionally-deleted Favorite
- handle multibyte numerals when detecting season and episode
- handle when the date or number is at the very beginning of the item title as with some Japanese multibyte titles by checking if all the numbers (after codecs are removed) are at the front, then move them to the back and process normally. Either that, or in all cases, if there are lots of text behind the season x episode, keep the text as part of the title
- add itemVersion handling to batches such as 1x03v2-1x05v2 (requires changing many match functions to handle version numbers)
- modify PicoFeed to provide getDescription for each RSS feed item description or each Atom feed item summary
- modify Configure > Feeds to allow re-ordering of Feeds
- allow user to create Favorites from items in the History list
- allow user to clear individual items from the cache
- allow user to un-Favorite items via contextual menu
- use PicoFeed's Curl class where appropriate
- enable PicoFeed HTTP basic authentication functionality
- per-feed Filter capability to only show some items
- implement Ignore Batches feature per Favorite as well as globally
- store item version numbers in Favorite Last Downloaded field
- add config option "Videos Only" beneath "Require Episode Info" to only show items with at least one video quality
- add auto-refresh of entire list at regular intervals to show new feed items in web UI
- add toggle to config for local/remote Transmission and disable features like Deep Directories for remote Transmission
- add error handling to the Transmission functions
- possibly change Hide List from using favTitle to a list of regexes so the user can block anything they like
- allow user to easily mark a torrent as the most recent episode downloaded in that season or in every season
- auto-delete old episodes that are replaced by REPACK or PROPER
- finish new "Serialization" concept as replacement for Episodes (now that print media can be faved)
  - check to make sure that new decimal PV numbering system works throughout entire app
- rework History panel (and probably all other panels) so that it resizes according to Responsive Design (use separate prototype to perfect all dialogs)
- POSSIBLY combine Downloading and Downloaded filters into one, using color-coding to differentiate between states
- TODO: change global box model from content-box to border-box
  - all CSS was authored for content-box; adding `*, *:before, *:after { box-sizing: border-box; }`
    to reset.css requires auditing every width/height/min-width/min-height/max-width/max-height
    declaration and adding back padding+border to keep the same visual size
  - example: `#filterbar_container li.tab { width: 91px; }` with 7px padding + 1px border
    each side would need `width: 107px` under border-box
- conform all names to Zend naming convention detailed at: http://framework.zend.com/manual/1.12/en/coding-standard.naming-conventions.html
- design class interface for TorrentClient and design child classes FolderClient and TransmissionClient
  - rename references to Transmission to some generic "torrent client" where appropriate and keep references to Transmission where appropriate, in case other torrent clients are added in the future
- design class interface for Favorite and rework all Add/Update/Delete Favorite functions to use it
- replace $html_out completely with ob_ functions
- switch from JQuery to ES5 per youmightnotneedjquery.com
- add ability to select a torrent and report just that item as having a detection bug (requires move away from GitHub Issues)
- write test suite and automate tests if possible
- sort torrents into resolutions by folder and allow for download of low-res version first, then high-res later, with toggle-able auto-delete of low-res version
- implement five-star rating system with separate subfolders for each to make watching the best shows first easier
- implement "probation" system for shows that haven't been liked enough to keep (perhaps zero stars out of five)

WILL NOT DO
-----------

- rewrite check_cache() and check_cache_episode() so that they are inverted; use check_cache() in processFeed()
- move $items assignment from inside process_feed() up to process_all_feeds()
- merge Javascript-side's #clientError div and showClientError() into #twError div and $.fn.showErrorPanel()

Validated files
---------------

These files have been completely validated:

- twxa_html.php
- twxa_parse.php
- twxa_parse_match*.php
- twxa_test_parser.php
- twxa_cli.php
- config.php
- twxa_fav_import.php

All other files have functions that need improvement or rewrites or validation.
