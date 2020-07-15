Change Log
===============

0.1.0
Functional changes
- cloned from TorrentWatch-X 0.8.9 (https://code.google.com/p/torrentwatch-x/) to torrentwatch-xa 0.1.0 (https://github.com/dchang0/torrentwatch-xa/)
- changed footer to show gratitude for support from the community
- replaced Paypal donation account and fixed button so that it displays properly according to config setting
- replaced all references to torrentwatch and TorrentWatch-X with torrentwatch-xa
- split basedir into baseDir and webDir in order to put files in traditional Debian 7 paths, making it easier for future .deb packaging
    This is a BIG change; the split will probably break a lot of things that will be fixed over time.
    Splitting it up also required hardcoding paths in several places due to the poor use of libraries. These will be fixed soon for sure.
- renamed configDir to configCacheDir because the config held there is dynamic
- added default Transmission login to default config
- temporarily added season and episode labels for items that are detected as episodes or full seasons
- rewrote method of sanitizing titles to reduce errors
- added new default anime feeds: nyaa.se and tokyotosho.info (anime only)
- removed default feed ezRSS.it because they serve gzipped torrent files, for which there is currently no support
- improved error alert for "Error: No torrent file found on..." to clue user to possibility of gzipping
- removed default feed ThePirateBay HiRes Shows to reduce clutter
- changed favicon to new logo
- added logo at far left edge of top navbar and in footer (will revamp graphic design layout in future version)
- modified version check to use new URLs
- disabled Bug Report button
- added Bug Report link to footer
- added anime-style episode guessing, treating all anime episodes as part of season 1
    - fixed failure to match TV-style episodes introduced by anime-style episode guessing
    - added ability to detect specific resolutions (like 1280x720p, 720i, 1920x1080p, 480i) as certain qualities
    - rewrote resolution and quality detection
    - rewrote Season and Episode and Date detection
    - temporarily removed REPACK, PROPER, RERIP detection because of anime-style 01v2 and 03v3 repacks

Code changes
- replaced some quickie php tags like <?php echo key; ?> with proper full <?php print([HTML code]) ?> in global_config.tpl, favorites.tpl, and favorites_info.tpl
- changed Filter input field from type="text" to type="search" to gain Esc key functionality and removed old magnifying glass icon via CSS
- cleaned up typos discovered by IDE in several files
- typo cleanup inadvertently fixed torrent buttons in clientButtons
- add alt attributes to img tags
- removed ob_end_clean(); from several functions in torrentwatch-xa.php to fix "unreachable statement" IDE warnings, but this is a temporary cleanup
- fixed unquoted CURL-related constants in config_lib.php:get_curl_defaults()
- replaced existing variable name prefix of tw_ with twxa_ (will expand use of twxa_ prefix in future)
- used IDE source reformatting tools to clean up some files
- started new collection of "member" functions in twxa_parse.php
- removed get_item_filter(); started transition to allow user-defined Sanitize Characters and Separator Characters strings in config
- renamed some variables and functions according to Zend naming convention
- commented out old guess_match() and replaced with new, partially-completed detectMatch()
- completed commenting out $epiDiv (part of tvDB feature that was already commented out) to fix PHP notices

0.1.1
Functional changes
- renamed var/www/torrentwatch-xa-web to torrentwatch-xa, which means the URL changes
- fixed Delete Torrent button; it used to trash the downloaded file
- moved torrent list container down 6px in phone.css so that the filter bar no longer partially obscures it
- totally revamped detectResolution() to detect ###x### ####x### or ####x#### and check it for aspect ratios
- added Enhanced Definition TV resolution 576i and 576p
- temporarily removed font size setting because it only changes the font size of the Configure UI
- added removal of audio codecs before episode detection (AC3 is being seen as an episode number)
- improved removal of all-numeric 8-digit checksums
- revamped Season and Episode detection to improve performance by focusing on "low-hanging fruit"
  - detection engine now counts occurrences of numbers in title and divides actions into groups by frequency of numbers
    - benefit is that we can go after standalone anime-style episodes sooner with fewer mistakes
    - also improves match debug output by grouping them together
  - added many new pattern detections
    - NOTE: Roman numeral Season I, II, or III - Arabic numeral Episode causes slightly counter-intuitive behavior in Favorites filters
  - improved detection of non-delimited dates such as YYYYMMDD, YYMMDD and so on
  - added conversion of fullwidth numerals to halfwidth for Japanese
  - added episode version (includes PROPER, REPACK, RERIP as version 2)
  - added detection of abbreviated years like '96
  - added $numberSequence to handle parallel numbering sequences like Movie 1, Movie 2, Movie 3 alongside Episode 1, Episode 2, Episode 3
  - added $detectedMediaType as groundwork for handling other media types than video
- removed The Pirate Bay from default feeds as they no longer offer RSS
- removed BT-Chat from default feeds as they were shut down

Code changes
- fixed undefined $showEpisodeNumber in feed_item.tpl
- removed undefined and unused $eta in feed_item.tpl
- fixed undefined $status
- renamed $torStop to $torPause
- renamed .torStop to .torPause
- renamed div.delete to div.torDelete
- renamed div.trash to div.torTrash
- added twxa_test_parser.php wrapper to make it easier to diagnose mismatches
- renamed _debug.php() to twxa_debug()
- added strtolower() for resolution matches so that 1080P becomes 1080p and so on
- moved <div> tag out of $footer to match its closing </div>
- single-quoted array keys in feeds.php:443
- renamed detectMatch()=>'key' to detectMatch()=>'title'
- renamed detectMatch()=>'data' to detectMatch()=>'qualities'
- added detectMatch()=>'isVideo'
- moved normalizing of codecs into normalizeCodecs()
- added debug logging of Season and Episode detection into /tmp/twlog
- changed Full Season $detectedEpisodeBatchEnd = 0 to $detectedEpisodeBatchEnd = '' for Preview episode 0 numbering
- refactored detectSeasonAndEpisode() to detectItem()
- added a simple pseudo-unit-tester for the parsing engine called twxa_test_parser.php
- refactored (renamed) $title to $ti throughout
- refactored (renamed) $separators to $seps throughout
- refactored (abbreviated) $detectedSeason... to $detSeas... throughout
- refactored (abbreviated) $detectedEpisode... to $detEpis... throughout
- refactored (abbreviated) $detected... to $det... throughout
- fixed missing mediaType in detectItem() return
- fixed (changed) $matches[1][] to $matches[0][] in twxa_parse.php

0.2.0

Functional changes
- fixed bug where "Download and seed ratio met" items return to the Downloading filter if browser is refreshed (torrentwatch-xa.js:588)
- replaced incorrect Start Torrent tor_start_10x10.png icon (had resume icon instead of start)
- moved Donate button from Paypal's website to local file
- designed new Move File tor_move_20x20.png and tor_move_10x10.png buttons (seen only in the Transmission filter's button bar)
- added Auto-Delete Seeded Torrents to automatically delete completely downloaded and seeded torrents from Transmission, leaving behind just the torrent's contents
- updated Transmission icon with latest official design
- fixed but temporarily removed Episodes Only toggle from config panel (entire concept of Episodes needs to be reworked now that print media can be faved)
- removed all NMT and Mac OS X support
- fixed feed section header and filter button match counts so that they obey the selected filter
- added horizontal scrolling capability to History panel for long titles

Code changes
- cleaned up variable declarations and isset checks in feed_item.tpl
- fixed missing semicolons and curly braces throughout torrentwatch-xa.js (appears to have fixed browser crashes)
- commented out Javascript function and its call for changing font size in config panels
- added missing curly braces in various PHP files
- cleaned up typos in various places
- removed redundant logic in Update/Delete button Javascript and prepared for future "Update button pins panel" functionality
- commented out $oldStatus in torrentwatch-xa.js as it is not used

0.2.1

Functional changes
- stripped season and episode data from title when using Add to Favorites button in toolbar
- added AVI to video qualities list

Code changes
- restructured most of huge if...else if...else control structure into switch...case in detectItem(), breaking out code into separate functions
- added missing braces to if blocks in cache.php
- removed $platform, $config_values where unused in its scope from config_lib.php
- removed unused $exec variable and related lines from torrentwatch-xa.php
- removed $html_out where unused in its scope from torrentwatch-xa.php
- fixed Undefined offset: 1 on line 24 of torrentwatch-xa.php by changing append to assign
- suppressed error message Undefined offset: 1 on line 106 of feeds.php
- fixed Undefined variable: any on line 223 of feeds.php and line 318 of tor_client.php by adding !isset($any) || logic
- fixed No such file or directory on line 269 of config_lib.php with file_exists() check
- fixed Undefined variable: response on line 345 of config_lib.php with isset() check

0.2.2

Code changes
- moved /var/www/torrentwatch-xa to /var/www/html/torrentwatch-xa to match change from Debian 7.x to Debian 8.x
  - corrected default get_webDir() corresponding to above move

0.2.3

Functional changes
- consolidated and improved color coding in feed list, Transmission list, and Legend
- improved Legend verbiage
- added back Transmission label to Web UI button
- added hide/show UI elements depending on browser window width (does not apply to when phone.css and tablet.css are used)
- shortened text in footer for narrow screens
- lightened tints of alternating list rows
- removed cutesy icons except Transmission's from main button bar and squared off each button's corners

Code changes
- removed all code related to the unused "Report Bug" feature that was cloned from TorrentWatch-X
- fixed typo in e.stopImmediatePropagation() at torrentwatch-xa.js:1242

0.2.4

Functional changes
- made Default Seed Ratio global setting be the default seed ratio for the blank New Favorite form
- minor edits to Favorite Info template's help text
- added ability to use SSxEE or YYYYMMDD notation to Last Downloaded Episode on the New Favorite form
- added FeedBurner aggregator of other large anime torrent RSS feeds to default config

Code changes
- cleaned up CSS warnings caused by filter:progid: entries (for IE8--IE8 support should be removed in the future)
- removed overlooked CSS background tags referring to missing favorites.png
- removed switch-case for torrent client, since Transmission is the only supported client
- fixed Undefined variable: magnet in tor_client.php on line 217
- completely removed mostly-useless torInfo() in tools.php, leaving the infoDiv updates to Javascript
- added some twxa_debug() logging to feeds.php
- fixed minutes declarations and calculations in torrentwatch-xa.js
- improved logic for creating or recreating Download Dir and made it only attempt to do either if Transmission Host is 127.0.0.1 or localhost
- removed $func_timer because it does nothing
- moved /tmp/twlog to /tmp/twxalog

0.2.5

Functional changes
- added leading zero to hour in History list timestamps so that it matches the Feed list timestamps and titles line up

Code changes
- fixed recap episode decimal numbering #.5 as in "HorribleSubs 3-gatsu no Lion - 11.5 480p.mkv", which becomes 11x5 under debug 3_25-1 due to \-? regex
- fixed bug where Title - 1x01 480p.mkv leaves - and . in Favorites title when added using Add Favorite.
- fixed regex bug where Filter field ending in an exclamation point never matches, as with New Game!
- removed unused $argv in rss_dl.php
- added missing braces to some conditional blocks in several files
- fixed PHP Notice:  Undefined index: Feed in /var/lib/torrentwatch-xa/lib/feeds.php on line 183
- fixed PHP Notice:  Undefined index: Filter in /var/lib/torrentwatch-xa/lib/feeds.php on line 207

0.2.6

No functional changes this release

Code changes
- completely cleaned up update_hidelist.php and moved its contents into update_hidelist() in torrentwatch-xa.php
- completely cleaned up twxa_test_parser.php
- deleted useless $platform global variable and platform_initialize()
- merged platform.php into config_lib.php 
- refactored platform_getConfigFile(), platform_getConfigCache(), and platform_get_configCacheDir(), removing platform_ prefix from each
- deleted unused and obsolete cleanup.sh
- refactored setup_rss_list_html() into start_feed_list()
- refactored show_feed_html() into show_feed_list()
- refactored close_feed_html() into close_feed_list()
- refactored show_down_feed() into show_feed_down_header()
- refactored show_torrent_html() into show_feed_item()
- converted global $html_out in html.php to parameters
- renamed html.php to twxa_html.php
- removed $normalize toggle from detectMatch
- call detectMatch() only once between show_feed_item() and its call to templates/feed_item.tpl to improve performance
- merged guess.php into twxa_parse.php
- fixed: after clearing all caches, PHP Warning:  preg_match(): No ending delimiter '^' found in /var/lib/torrentwatch-xa/lib/tor_client.php on line 376 (377)
- upgraded jquery to latest 1.12.4, following 1.9 upgrade guide http://jquery.com/upgrade-guide/1.9/#live-removed and using JQuery Migrate 1.4.1 plugin
- upgraded jquery.form.js from 2.43 to 4.2.1 minified per https://github.com/jquery-form/form
- fixed: PHP Notice:  Undefined offset: 1 in /var/lib/torrentwatch-xa/lib/twxa_parse.php on line 387
- fixed: PHP Notice:  Undefined variable: idx in /var/lib/torrentwatch-xa/lib/config_lib.php on line 551
- refactored guess_feedtype() to guess_feed_type() and cleaned it up
- set default return value of guess_feed_type() back to "Unknown" for add_feed() to properly handle bad feeds, bypassing the following errors:
    - PHP Notice:  Undefined index: http://eztv.ag/ in /var/lib/torrentwatch-xa/lib/config_lib.php on line 538
    - PHP Notice:  Undefined index: http://eztv.ag/ in /var/lib/torrentwatch-xa/lib/feeds.php on line 500
- cleaned up add_feed() and fixed: PHP Notice: Only variables should be passed by reference in /var/lib/torrentwatch-xa/lib/config_lib.php on line 532
- fixed: PHP Notice: Only variables should be passed by reference in /var/lib/torrentwatch-xa/lib/config_lib.php on line 421
- refactored update_feedData() to update_feed_data()
- renamed NMT-mailscript.sh to example-mailscript.sh and improved its comments,
- hid the Configure > Other > Script field since it is read-only
- upgraded PHPMailer from 5.2 to 5.2.23, but not using any of the SMTP auth features yet
- cleaned up all the undefined variable PHP notices that occur when Configure is saved:
  - PHP Notice:  Undefined index: combinefeeds in /var/lib/torrentwatch-xa/lib/config_lib.php on line 330
  - PHP Notice:  Undefined index: epionly in /var/lib/torrentwatch-xa/lib/config_lib.php on line 330
  - PHP Notice:  Undefined index: require_epi_info in /var/lib/torrentwatch-xa/lib/config_lib.php on line 330
  - PHP Notice:  Undefined index: dishidelist in /var/lib/torrentwatch-xa/lib/config_lib.php on line 330
  - PHP Notice:  Undefined index: hidedonate in /var/lib/torrentwatch-xa/lib/config_lib.php on line 330
  - PHP Notice:  Undefined index: savetorrents in /var/lib/torrentwatch-xa/lib/config_lib.php on line 330
- fixed fatal typo bugs in MailNotify()
- fixed: when deleting a downloaded torrent from the Transmission list, PHP Notice:  Undefined index: trash in /var/www/html/torrentwatch-xa/torrentwatch-xa.php on line 67
- renamed TODO to TODO.md since it was already in Markdown syntax
- refactored mailonhit to emailnotify
- changed 'TimeZone' to 'Time Zone' and 'TZ' to 'tz' in $config_values['Settings']
- moved Time Zone: field from Configure > Other to Configure > Interface
- changed 'Email Address' label to 'To: Email Address' in Configure > Other
- fixed: after clearing all caches and refreshing, PHP Notice:  Undefined index: torrent-added in /var/lib/torrentwatch-xa/lib/tor_client.php on line 173
- fixed: PHP Notice:  Undefined property: lastRSS::$rsscp in /var/lib/torrentwatch-xa/lib/lastRSS.php on line 115 by adding private member $rsscp to lastRSS class
- cleaned up lastRSS.php a bit
- deleted curl.php because it is a buggy replacement for PHP's built-in curl support (php5-curl package on Ubuntu)
- changed CURLOPT_ constants in get_curl_defaults() from strings to integers to conform to spec

0.3.0

Functional changes
- added check for mb_convert_kana()
- renamed Configure > Other to Configure > Trigger
- added SMTP authentication options to Configure > Trigger for email notifications
- added Script option to Configure > Trigger for shell scripts triggered by downloads or errors
- commented out soft-hyphen insertions

Code changes

- cleaned up logic in transmission_add_torrent() to match current Transmission RPC spec
- improved twxa_debug() logic
- add ERR:, INF:, and DBG: keywords to every twxa_debug() message for easier grep
- add verbosity setting to all twxa_debug() calls (but twxa_debug() doesn't yet hide messages by verbosity--it is DBG all the time)
- removed timer_init() and replaced it with timer_get_time(0)
- minor cleanup in client_add_torrent()
- fixed: PHP Notice:  Undefined index: ... in /var/lib/torrentwatch-xa/lib/feeds.php on line 498
- fixed annoying bug where browser thinks Cmd is still depressed after switching to browser using Cmd-Tab on Mac OS X, EXCEPT in the rare case of rotating all the way through the running apps list back to the browser (without switching focus away from the browser)
- slightly improved default seed ratio limit logic
- switched back to global $html_out--CPU util is somewhat improved with this change
- attempted to improve CPU util by switching preg_ functions to strreplace() in sanitizeTitle()
- converted some preg_ functions to str functions

0.3.1

Functional changes

- Downloaded filter now includes match_old_download (Cached Download, dark grey) items
- Downloaded filter total count includes match_old_download items
- temporarily added back item classes to the Legend for diagnostic purposes
- added infoDiv to downloading or downloaded items in the filters other than Transmission
- removed infoDiv from completed items in the filters other than Transmission
- deleting via context menus removes infoDiv from and changes state of removed torrent items immediately
- removed $test_run and match_favReady functionality (in the browser, favorites start downloading immediately on page load, rather than going into Ready state)
- removed Verify Episode option, since we don't want to re-download anything already in the download cache

Code changes

- cleaned up all but two functions in rss_dl_utils.php
- renamed rss_dl_utils.php to twxa_rss_dl_tools.php
- made a few more preg_ to str_ conversions to improve performance
- cleaned up default settings in config_lib.php
- changed match_season to match_favBatch in preparation for capability of downloading batches automatically
- changed match_test to match_favReady for clarity
- changed match_match to match_favStarted for clarity
- changed match_to_check to match_waitTorCheck for clarity
- changed match_old_download to match_inCacheNotActive for clarity
- renamed clear_cache_real() to clear_cache_by_feed_type()
- renamed clear_cache() to clear_cache_by_cache_type()
- renamed cache_setup() to setup_cache()
- renamed cache.php to twxa_cache.php
- cleaned up check_cache() and check_cache_episode() logic a bit
- renamed perform_feeds_matching() to process_all_feeds()
- renamed load_feeds() to load_all_feeds()
- renamed rss_perform_matching() to process_rss_feed()
- renamed atom_perform_matching() to process_atom_feed()

0.4.0

Functional changes

- renamed Configure > Favorites > Download PROPER/REPACK setting to Download Versions >1 in switch to itemVersion numbering system
- temporarily disabled PROPER/REPACK handling in favor of itemVersion
- added ability to auto-download batches as long as one episode in a batch is newer than the Favorite's last downloaded episode
- can now auto-download Print media (Volume x Chapter or batch of Chapters or full Volume or batch of Volumes)
- added Configure > Favorites > Ignore Batches
- changed Favorite Batch to Ignored Favorite Batch in Legend (part of new auto-download batch feature)
- drastically reorganized pattern detection engine logic for code reuse
- corrected, streamlined, or added pattern detection regexes for many numbering styles
- partially fixed removal of multiple selected active torrents from display when deleted via context menu
- added batch capability to all current pattern detectors
- added favTi (aka show_title) processing to all current pattern detectors
- added Configure > Interface > Show Item Debug Info to make it easier to diagnose detection engine errors
- added always-available on-hover debugMatch values to the episode labels
- added Glummy face as episode label for items that didn't match so that hover debugMatch works
- added ability to match multibyte characters in RegEx mode, which allows matching Japanese/Chinese/Korean titles
- removed default feed NyaaTorrents RSS because it got shut down
- removed default feed Feedburner Anime (Aggregated) because it got shut down
- added AniDex as a new default feed
- added TorrentFunk RSS - Anime as a new default feed
- added HorribleSubs Latest RSS as a new default feed
- added AcgnX Torrent Resources Base.Global as a new default feed
- added checkbox to turn a feed on or off (if off, any Favorites assigned to only that feed will not be processed)
- added back Require Episode Info: if unchecked, Favorite items without episode numbering will also be matched 

Code changes

- added pass-through of detectItem() season and episode output to detectMatch()
- improved Favorite to item season and episode comparison in check_for_torrent() and updateFavoriteEpisode()
- added extra collapseExtraSeparators() to several parsing functions to remove extra separators from the title
- improved human-friendly episode notation conversion in detectMatch()
- fixed boolean use of detectMatch() now that it returns an array
- rearranged order of resolution checks and improved pattern detection in detectResolution()
- added ability to detect, remove, and re-insert crew names with numerals such as (C88)
- improved check_cache_episode() logic to make it more reliably detect an item in the download cache
- performance improvement by switching more preg_ to str_ functions
- moved the matchTitle functions to their own files, grouped by number of numbers
- moved much of detectItem()'s logic to its own file
- corrected pre-2.4 Transmission TR_STATUS code translations so that 2.4 codes are now the norm
- removed torrentwatch-xa-md.css since it appears to be unused
- added is_numeric() checks of the season and episode values
- shortened $detect prefix on several variables to $det
- shortened $seasonBatch prefix on variables to $seasBat
- shortened $episodeBatch prefix on variables to $episBat
- renamed $episode_guess to $episGuess

0.4.1

Functional changes

- removed Episodes Only functionality (all items will be shown, including those without episode numbering, as those are in the small minority)
- removed Verify Episodes functionality (check_cache_episode() will always be run to avoid double-downloading same torrent under different numbering)
- minor changes to numbering system involving Volumes
- changed Add to Favorites' Qualities filter back to the detected qualities of the selected item from "All"
- validated favTi processing to make sure only the pertinent data is removed from the title
- renamed rss_dl.php to twxacli.php (especially since we're not limited to RSS feeds only)
- renamed rss_cache to dl_cache (will require all personalized config files to be updated/re-created)
- renamed rss_dl.history to dl_history
- changed rss_dl_ prefixes to dl_
- removed tvDB code that has been commented out for a long time
- removed Configure panel font size selector that has been commented out for a long time
- fixed bug introduced in 0.3.1 where highlighted items in Transmission filter lose the highlight after a few seconds have passed

Code changes

- renamed tools.php to twxa_tools.php
- finally renamed twxa_debug() to twxaDebug()
- commented out unused portion of twxaDebug()
- merged contents of twxa_rss_dl_tools.php into twxa_tools.php
- moved torrent-related functions from twxa_tools.php (after merge of twxa_rss_dl_tools.php) into tor_client.php
- moved get_curl_defaults() from config_lib.php to twxa_tools.php
- moved add_history() from config_lib.php to twxa_cache.php
- renamed config_lib.php to twxa_config_lib.php
- moved add_torrents_in_dir() from twxa_tools.php to tor_client.php
- moved guess_feed_type() from twxa_parse.php to feeds.php
- moved guess_atom_torrent() from twxa_parse.php to feeds.php and commented it out since nothing seems to use it
- moved get_torHash() from twxa_parse.php to twxa_cache.php
- moved episode_filter() from feeds.php to twxa_parse.php
- cleaned up get_torrent_link() and choose_torrent_link()
- corrected typo ['SMTP Username'] to ['SMTP User'] in twxa_config_lib.php
- replaced array() functions with brackets
- repaired customized atomparser.php by comparing it to the latest official version
  - renamed constructor function to fix PHP deprecation
  - put back missing "if (!function_exists('mb_detect_encoding'))" on line 204
  - can't really test these fixes because of rarity of Atom feeds with torrents

0.5.0

Functional changes

- further improved creation of Qualities filter when using Add to Favorites to account for the different matching styles
  - Simple => match any of the detected qualities as strings
  - Glob => match All qualities (list of detected qualities would almost never match as a glob, so match All is best)
  - RegExp => match any of the detected qualities as a regular expression
- removed Qualities filter 'All' when matching style is RegExp
- added Auto-Del Seeded Torrents to twxacli.php with check to see if torrent is in download cache before deleting
- add check of download cache to Auto-Del Seeded Torrents in web UI's Javascript
- renamed Favorite Started to Started and favStarted to justStarted because non-Favorite items can be in this state
- when manually starting brand new torrent, state now changes from Favorite Started to Downloading sooner than when it is fully downloaded
- added --keep-config option to installtwxa.sh
- added logic to installtwxa.sh to check for new torrentwatch-xa package before installing

Code changes

- added rtrim() to remove leftover unmatched left-parenthesis and removed a few now-unnecessary pattern matches
- renamed remaining $batch to $isBatch
- renamed tor_client.php to twxa_torrent.php
- renamed feeds.php to twxa_feed.php
- renamed lastRSS.php to twxa_lastRSS.php (because it has been customized enough that there can be no drop-in replacement of the file with a newer lastRSS.php)
- renamed atomparser.php to twxa_atomparser.php (same reason)
- conformed permissions on all files and directories
- added EZTV as a new default RSS feed
- fixed typo in twxa_cache.php, lines 101 and 105: 'version' should be 'itemVersion'
- changed Ratio to use Transmission uploadRatio instead of dividing uploadedEver by downloadedEver
  - fixed Infinity seed ratio problem caused by downloadedEver being 0
- changed Percentage to use Transmission percentDone instead of calculating from totalSize and leftUntilDone
- moved Watch Dir processing in twxacli.php to process_watch_dir() function and cleaned up the logic
- commented out all Watch Dir code because find_torrent_link() hasn't worked on .torrent files since TorrentWatch-X 0.8.9 and Transmission already has watch directory capability built-in
- validated and simplified Javascript .delTorrent function and changed some 1 values to true
- started towards removing isBatch logic by commenting out $isBatch checks in torrent functions

0.6.0

Functionality changes

- color of downloading item in Transmission filter now matches bright green of same item in the other filters
- clicking X in search input clears the field no longer requires pressing enter key to enact
- torrents added outside of torrentwatch-xa now show in Transmission filter even when completed (must be manually removed, even if auto-delete is on)
- changed Save Torrent Files to Also Save Torrent Files and made it so it does not work if Client is "Save torrent in folder"
- added Save Torrent Files Dir to Also Save Torrent Files feature so that .torrent files can be saved to any locally-accessible path
- removed email.tpl and slightly reworded email subjects and messages for successful and failed auto-downloading
- corrected error messages and README.md about config.php (now no longer necessary since twxa_config_lib.php is self-contained)
- changed torResume icon from Play icon to Resume icon
- rewrote getClientData() and processClientData() in torrentwatch-xa.js to fully sync the web UI with transmission-daemon
  - cleaned up item states further
    - merged Transmission legend colors into other filters' legend colors in CSS
    - minor tweaks to legend text
    - smoother transitions through the states
    - changed match_inCache (formerly match_cacheHit) color into Previously Downloaded color because it is a transient state
  - title of item in Transmission filter starts as item.hashString but is now correctly updated to item.name when Transmission learns it
  - progress bar is now correctly shown or hidden even across browser refreshes
  - eta text is now shown in more states across all active torrents, especially when seeding
- moved Usage and Design Decisions Explained sections into USAGE.md file
- fixed bug from TorrentWatch-X 0.8.9 where context menu slideUp() does not completely put away the contextMenu, leaving it partially showing
- tweaks to web UI colors, gradients, and radiused corners
- improved layout of Favorites dialog
  - moved Favorites list item text left to use space formerly occupied by the Favorite icon (heart)
  - finally fixed word break problem in Favorites list with CSS3's `word-break` property
  - corrected position of Close button
  - adjusted spacing and sizing of elements
- finally fixed vertical positioning of button bar for iOS devices
- added the newly rebuilt NyaaTorrents as new default feed
- moved Also Save Torrent Files and Also Save Torrent Files Dir to Configure > Client tab

Code changes

- completely removed commented-out Watch Dir code
- commented out remainder of isBatch and batch code related to torrent functions
- switched from hard-coded paths to PHP include paths
- renamed div.torStart to div.torResume
- renamed torStartStopToggle to toggleTorResumePause (to match toggleTorMove)
- renamed div.dlTorrent to div.torStart
- validated, cleaned up, and clarified item states
  - removed commented-out match_duplicate state (was merged into match_inCacheNotActive in a prior version)
  - renamed match_favBatch to match_ignoredFavBatch for clarity
  - changed noShow to notSerialized for clarity
  - renamed match_cacheHit to match_inCache for clarity
  - renamed $matched to $itemState for clarity
  - renamed 'matched' to 'itemState' for clarity
  - renamed match_ prefix to st_ prefix for clarity (affects all list item states)
  - added tc_ (stands for "torrent client") prefix to #transmission_list states: waiting, verifying, paused, downloading
  - added tc_seeding state, though it is not really used, to complete logic
  - merged st_justStarted into st_favReady
- class "clientId_###" is now properly removed from items in filters other than Transmission when the matching item is removed from Transmission
- removed unused getScrollBarWidth() function
- cleaning up CSS with csslint.net
  - fixed ID collision with li#webui and span#webui by renaming span#webui to span#webUILabel
  - fixed ID collision with div.config_form and form#config_form by renaming div.config_form to div.config_form_container
  - fixed ID collision with div#showURL and input#showURL by removing input#showURL
  - fixed class collision with span.contextButton and a.contextButton by renaming span.contextButton to span.contextButtonContainer
  - div#trash_tor_data might collide with a.trash_tor_data, the latter of which might not be used for anything
  - fixed ID collision with ul#mainoptions and ul.mainoptions by removing class ul.mainoptions
  - commented out `filter: alpha(opacity=##);` functions that are for IE8 compatibility
- combined process_rss_feed() and process_atom_feed() into one function process_feed()
- added or improved matchTitle functions
- rewrote folder_add_torrent() with error handling and to return same output as transmission_add_torrent()
- changed state logic in process_feed()

0.7.0

Functional changes

- Time Zone setting defaults to UTC if unacceptable value is supplied
- added additional logic in check_cache_episode() to handle batch comparisons
- commented out Configure > Client > File Extension as torrent files should always have .torrent as the extension
- fixed Client: Save Torrent In Folder web UI behavior
  - hide Seed Ratio settings in Favorites
- added capability of saving magnet links to .magnet files (magnet links can be converted into .torrent files by third party utilities)
- renamed Favorite > Save In to Favorite > Download Dir to match global Download Dir (that it overrides)
- added Favorite > Also Save Dir to override Also Save Torrent Files Dir
- changed Configure and Favorite dialogs to hide/show fields based on Configure > Client > Also Save Torrent Files
- renamed Last Downloaded Item label to Last Downloaded
- twxaDebug() error messages are capped by hidden 'debugLevel' config setting
- finished logic to handle "FULL" as episode to denote full season
- added many pattern detectors to season and episode detection engine, including Volume x Part
- improved seed ratio inheritance behavior
- added installation instructions for Fedora Server 25
- added detection of PROPER/REPACK/RERIP as itemVersion 99
- fixed Configure > Favorites > Download Versions>1 failing even when checked
- removed TorrentFunk RSS - Anime from default feeds

Code changes

- cleaned up changeClient function
- renamed MailNotify() to notifyByEmail()
- renamed run_script() to runScript()
- renamed microtime_float() to getCurrentMicrotime()
- renamed timer_get_time() to getElapsedMicrotime()
- renamed unused authenticate() to authenticateFeeds()
- renamed filename_encode() to sanitizeFilename()
- improved logic and email and debug messages in runScript()
- renamed check_for_cookies() to parseURLForCookies()
- converted most of Favorites dialog from absolute positioning to relative positioning
- renamed setup_cache() to setupCache() and improved its logic
- renamed TWFILTER in cookie to TWXAFILTER
- renamed isset_array_key() to getArrayValueByKey()
- renamed $getOptions to $curlOptions
- renamed get_curl_defaults() to getcURLDefaults()
- renamed version_check() to checkVersion()
- replaced $verbosity with $config_values['Settings']['debugLevel']
- removed global usage of $twxa_version
- improved major.minor.patch comparison logic in checkVersion()
- commented out 'recent' logic in getClientData() functions
- modified install script to try Debian/Ubuntu then Fedora apache2 username

0.8.0

Functional changes

- added HiDPI logos
- added HiDPI favicons
- added Configure > Favorites > New Add to Favorites Get: (Detected Resolutions Only) selector
- rewrote episode_filter() to handle new season and episode notation style
- switched config file from Windows INI format to PHP's JSON format by rewriting read_config_file() as readjSONConfigFile() and writeConfigFile() as writejSONConfigFile(); also fixed bug with other parts of $config_values['Global']['Feeds'][] ending up in the $config_values['Settings']['Favorites'] section of the config file by avoiding use of array_walk() to generate Windows INI format config file
- removed pre-0.7.0 to 0.7.0 config file converter
- fixed "3-gatsu no Lion 25" is treated as 1x3 even though the episode number 25 occurs later in the title
- decided to modify match function to ignore 1080, 720, 480 resolutions without i or p on the end so that 05 720 is not detected as 5x720 but as 1x5

Code changes

- changed DOCTYPE from XHTML to HTML5 in order for HiDPI favicons to work
- changed CSS media queries to improve detection of tablets and phones
- removed commented-out code
- rewrote write_config_file() as writeConfigFile() to avoid using recursive array_walk callbacks, but commented it out for new JSON config file format
- removed array_walk() callback in process_feed() that used to callback check_for_torrent() that broke updateFavoriteEpisode()
- removed array_walk() callback in favorites.tpl
- rewrote update_global_config() as updateGlobalConfig() so it sets all missing settings to null
- added missing button classes as empty styles in index.html to address warnings
- added filter_input() in some reads (not writes) of $_GET or $_SERVER
- changed comparison operators == to === and != to !== in multiple places in torrentwatch-xa.js


0.9.0

Functional changes

- add_feed() now validates the feed URL before attempting to add it
- converted all context menu and button bar and Filter bar icons to HiDPI
- changed Hide List to hide all items that match the detected favTitle regardless of season and episode (a Hide List entry acts like an anti-Favorite)
- items in Hide List are no longer all-lowercase
- added notice of disabled Hide List to top of Hide List
- changed "You have not hidden any shows." to "No entries found." in Hide List
- if Disable Hide List is checked, button bar now disables the Hide button
- removed 0.7.0 to 0.8.0 config file converter
- validated Deep Directories (all Configure options have now been validated working)
- removed Sanitize Hidelist feature, since Hide List now uses favTitles, which are already sanitized
- commented out Deep Directories > Full setting as it is pretty much useless
- Feed header item counter no longer flips to (0) if the feed is hidden

Code changes

- improved del_feed() and renamed it to deleteFeed()
- improved update_feed() and renamed it to updateFeed()
- improved add_feed() and renamed it to addFeed()
- improved update_feed_data() and renamed it to updateFeedData()
- improved del_favorite() and renamed it to deleteFavorite()
- removed global $config_values from get_smtp_passwd() and renamed it to decryptsMTPPassword()
- improved del_hidden() and renamed it to delHidden()
- consolidated add_hidden(), changed its error handling and logging, and renamed it to addHidden()
- removed read_config_file()
- reduced unnecessary writes to config file
- fixed: PHP Warning:  rename(/var/lib/torrentwatch-xa/config_cache/torrentwatch-xa.config_tmp,/var/lib/torrentwatch-xa/config_cache/torrentwatch-xa.config): No such file or directory in /var/lib/torrentwatch-xa/lib/twxa_config_lib.php on line 290 by reducing time zone calls to writejSONConfigFile()
- added rudimentary protection against writing config file while ongoing write is renaming config temp file
- twxaDebug() can now write to the log file before $config_values is populated by readjSONConfigFile()
- migrating from JQuery 1.12.4 to 3.2.1 using JQuery-migrate 3.0.1 (all changes were replacing deprecated shortcuts)

1.0.0

Functional changes

- corrected feed header item counters in Matched/Downloaded/Downloading depending on active filter
- committed to a three-part version number and fixed a bug in the version number comparison logic
- fixed "Steins Gate 0" where 0 is part of the title and not a season nor episode
- fixed "Mob Pyscho 100 OVA - 01" where 100 is part of the title and OVA is present
- fixed "Persona 5 The Animation - 01" where 5 is part of the title and OVA is not present
- added (C85) and "Doujinshi (C91)" to crew names
- when opening Favorites, New Favorite is again selected by default (broke in 0.9.0 because .selector was removed in JQuery 3.0)
- fixed when torrent is deleted from outside twxa while active, it disappears from Transmission list but not other filter lists
- minor bugfixes to twxacli.php

Code changes

- removed older JQuery 1.12.4 files
- upgraded from JQuery 3.2.1 to 3.3.1
- tuned updateMatchCounts and listSelector functions because of apparent slow memory leak in JQuery's rebinding of on mousedown that became obvious with JQuery 3.3.1
- fixed PHP Warning about type cast from string to int when calling checkdate()
- removed redundant PHP include path code from multiple files; it should be in only one place, at the end of config.php
- moved default get_baseDir(), get_webDir(), get_logFile(), and get_tr_sessionIdFile() out of twxa_config_lib.php into config.php
- moved config.php and twxacli.php into /var/www/html/torrentwatch-xa
- fixed ""synchronous XMLHttpRequest on the main thread is deprecated" by merging contents of configure.js into global_config.tpl
- removed json2.js because modern web browsers have JSON.parse built-in
- started testing tinysort.min.js, but it produces benign warnings when the Transmission list is empty, so we stuck with jquery.tinysort.min.js for now
- switched back to preg_ functions in video and audio codecs detection to fix bug with avi being removed from the middle of Davinci
- fixed minor bug in audio codecs function
- validated twxacli.php and removed obsolete code

1.1.0

Functional changes

- renamed twxacli.php to twxa_cli.php to conform to file naming convention
- added twxa_fav_import.php in alpha testing
- improved check_requirements() and renamed it to checkpHPRequirements()
- added Configure > Interface > Check for Updates
- increased Check for Updates delay from 1 to 7 days
- moved and renamed hidden debugLevel to Configure > Interface > Log Level
- updated Paypal donation button
- massive rewrite of check_files() as checkFilesAndDirs()
  - added check for DownloadCacheDir
  - added check for Save Torrents Dir
  - added check for permissions on ConfigFile if it exists
  - added check for permissions on ConfigCacheFile if it exists
  - added check for permissions on DownloadHistoryFile if it exists
  - improved error messages
- Favorite > Also Save Dir now defaults to global Also Save Torrent Files Dir if left blank or if path is not writable ("Default" keyword no longer works)
- Favorite > Download Dir now defaults to global Download Dir if left blank ("Default" keyword no longer works)
- adjusted Configure > Feeds so that rows don't wrap when vertical scrollbar shows
- bulk favorite importer now recognizes one field as both Name and Filter
- fixed detection of Roman numeral seasons

Code changes

- fixed bad bug where most of the Configure settings are lost and replaced with some default settings (Feeds)
  - massive rewrite of setup_default_config() as setupDefaultConfig()
  - massive rewrite of readjSONConfigFile()
  - moved some code from readjSONConfigFile() to smaller functions
  - improved writejSONConfigFile()
- moved hidden settings out of torrentwatch-xa.config into twxa_config_lib.php:
  - download cache directory location
  - history file location
  - torrent file extension
  - magnet file extension
  - Transmission URI (path to Transmission RPC)
- added setpHPTimeZone()
- renamed get_tr_location() to getTransmissionWebuRL() and moved it to twxa_config_lib.php
- added getTransmissionWebPath()
- renamed close_html() to closehTML()
- added outputErrorDialog()
- renamed get_client() to outputClient()
- renamed twxaDebug() to writeToLog()
- fixed bug in writeToLog() where ERROR wrote every level
- removed $SERVER global from twxa_cli.php
- fixed bug where FULL seasons are not treated as batches
- feed type is correctly saved to config file

1.2.0

Functional Changes

- CURL now passes through the user's User-Agent header value
- due to systemd's PrivateTmp security feature in Ubuntu 18.04:
  - moved /tmp/twxalog to /var/log/twxalog
  - moved /tmp/.Transmission-Session-Id to /var/lib/torrentwatch-xa/dl_cache/.Transmission-Session-Id
  - updated documentation to reflect new twxalog path
- fixed detection of Roman-numeral season
- changed client_add_torrent() to use torrent hash from URL if DDoS blockers like CloudFlare drop the connection
- updated EZTV feed URL in default feeds

Code Changes

- renamed get_tr_sessionIdFile() to getTransmissionSessionIdFile()
- moved getTransmissionSessionIdFile() from config.php to twxa_config_lib.php
- added explicit int casts to checkdate() calls

1.3.0

Functional Changes

- dropped support for Ubuntu 14.04 and Debian 8.x
- added Test button to SMTP settings on Trigger config tab
- removed explicit HELO from email due to HELO impersonation blocking on some SMTP servers
- added FromName (aka DisplayName) to SMTP notifications

Code Changes

- replaced some "+ 0" with explicit (int) casts, especially in checkdate() calls

1.4.0

Functional Changes

- redesigned Configuration > Feeds tab
- disabled Feeds are no longer selectable in each Favorite
- added Configure > Trigger > From: Name
- added Configure > Trigger > HELO Override to satisfy some SMTP servers

Code Changes

- converted linkButtons from an id to a class
- removed commented-out code
- replaced remaining "+ 0" with explicit (int) casts

1.4.1

Code Changes

- removed commented-out code
- upgraded jQuery from 3.3.1 to 3.5.1
- upgraded jquery.cookie.js to js-cookie at https://github.com/js-cookie/js-cookie
- changed sameSite from default of 'none' to 'lax' so cookies comply with Firefox browser security requirements
- changed non-standard CSS zoom property to standard CSS transform and transform-origin properties
- simplified selector in $.checkHiddenFeeds
- renamed setupCache() to setupDownloadCacheDir() and improved it
- renamed createConfigDir() to setupConfigCacheDir() and improved it
- added chmod of config file to twxa_fav_import.php
- commented out window.getFail logic
- changed window.gotAllData from 0 or 1 to false or true
- issued unique DOM ids to the hidden inputs named "idx" on the favinfo forms
- started replacing jQuery with ES5
  - .val()
  - .hide()
  - .show()
- started using 'use strict'; in blocks of torrentwatch-xa.js
- split torrentwatch-xa.js into smaller files and started moving functions into them

Next Version

Functional Changes

IN PROGRESS

- handle gzipped torrent file (using gzuncompress() if file contents are returned directly or gzopen() and gzread() if file is downloaded)
- add PHP prerequisite check to twxa_cli.php
- fix rare bug where button bar stays visible when multiple items are trashed from Transmission list
- fix vertical alignment of title line in Transmission filter on iPhone (first line of text sits too low and is too close to the progress bar)
- sometimes adding/updating a Favorite does not close the dialog and refresh the browser
- Add to Favorites and Hide Item in contextual menu doesn't go away if the item is already in favorites or already hidden, respectively
- fix slow timeout on first processClientData update of active torrent items after browser refresh (may be related to window.gotAllData)
- show alerts in web UI

Code Changes

IN PROGRESS

- JQuery.fx.interval is deprecated (might be a benign warning)
- continue adding filter_input() in some reads (not writes) of $_GET or $_SERVER
- move set_client_passwd() and set_smtp_passwd() calls outside of writejSONConfigFile() so that they are only run when needed
- add function that detects errors in $config_values
- figure out window.gotAllData logic, maybe merge window.gotAllData into window.updatingClientData or remove one
  - setting window.gotAllData = 0 at end of processClientData causes progressBar to disappear from active torrents in #torrentlist_container
- continue cleaning up CSS with csslint.net
- fix Quality filtering in check_for_torrent() before checking the download cache
- adding a favorite from Nyaa feed (second in the feed list at the time) seems to end up with Feed = Nyaa rather than Feed = All