<?php

require_once('/var/lib/torrentwatch-xa/lib/twxa_parse.php');

function get_torrent_link($rs) {
    $links = [];
    $link = '';
    if ((isset($rs['enclosure'])) && ($rs['enclosure']['type'] == 'application/x-bittorrent')) {
        $links[] = $rs['enclosure']['url'];
    } else {
        if (isset($rs['link'])) {
            $links[] = $rs['link'];
        }
        if (isset($rs['id']) && stristr($rs['id'], 'http://')) { // Atom
            $links[] = $rs['id'];
        }
        if (isset($rs['enclosure'])) { // RSS Enclosure
            $links[] = $rs['enclosure']['url'];
        }
    }

    if (count($links) === 1) {
        $link = $links[0];
    } else if (count($links) > 0) {
        $link = choose_torrent_link($links);
    }
    $link = str_replace(" ", "%20", $link);
    return html_entity_decode($link);
}

function choose_torrent_link($links) {
    //TODO probably add gzip capability here
    $link_best = "";
    $word_matches = 0;
    if (count($links) === 0) {
        return "";
    }
    //Check how many links have ".torrent" in them
    foreach ($links as $link) {
        if (stripos($link, "/\.torrent/") !== false) {
            $link_best = $link;
            $word_matches++;
        }
    }
    //If only one had ".torrent", use that, else check http content-type for each,
    //and use the first that returns the proper torrent type
    if ($word_matches != 1) {
        foreach ($links as $link) {
            $opts = array('http' =>
                array('timeout' => 10)
            );
            stream_context_get_default($opts);
            $headers = get_headers($link, 1);
            if ((isset($headers['Content-Disposition']) &&
                    preg_match('/filename=.+\.torrent/i', $headers['Content-Disposition'])) ||
                    (isset($headers['Content-Type']) &&
                    $headers['Content-Type'] == 'application/x-bittorrent' )) {
                $link_best = $link;
                break;
            }
        }
    }
    //If still no match has been made, just select the first, and hope the html torrent parser can find it
    if (empty($link_best)) {
        $link_best = $links[0];
    }
    return $link_best;
}

function episode_filter($item, $filter) {
    /*
     * NEW NOTATION:
     * SxE = single episode
     * SxEv# = single episode with version number
     * YYYYMMDD = single date
     * S1xE1-S1-E2 = batch of episodes within one season
     * YYYYMMD1-YYYYMMD2 = batch of dates
     * S1xFULL = one full season
     * S1xE1-S2xE2 = batch of episodes starting in one season and ending in a later season
     * S1xE1v2-S2xE2v3 = batch of episodes starting in one season and ending in a later season, with version numbers
     */
    if ($item['episode']) {
        $filter = preg_replace('/\s/', '', $filter);

        //list($itemS, $itemE) = explode('x', $item['episode']);
        $itemEpisodePieces = explode('x', $item['episode']);
        if (isset($itemEpisodePieces[0])) {
            $itemS = $itemEpisodePieces[0];
        } else {
            $itemS = "";
        }
        if (isset($itemEpisodePieces[1])) {
            $itemE = $itemEpisodePieces[1];
        } else {
            $itemE = "";
        }

        if (preg_match('/^S\d*/i', $filter)) {
            //$filter = preg_replace('/S/i', '', $filter);
            $filter = strtr($filter, array('S' => '', 's' => ''));
            if (preg_match('/^\d*E\d*/i', $filter)) {
                //$filter = preg_replace('/E/i', 'x', $filter);
                $filter = strtr($filter, array('E' => 'x', 'e' => 'x'));
            }
        }
        // Split the filter(ex. 3x4-4x15 into 3,3 4,15).  @ to suppress error when no second item
        //@list($start, $stop) = explode('-', $filter, 2);
        $filterPieces = explode('-', $filter, 2);
        if (isset($filterPieces[0])) {
            $start = $filterPieces[0];
        } else {
            $start = "";
        }
        if (isset($filterPieces[1])) {
            $stop = $filterPieces[1];
        } else {
            $stop = "9999x9999";
        }
        //@list($startSeason, $startEpisode) = explode('x', $start, 2);
        $startPieces = explode('x', $start, 2);
        if (isset($startPieces[0])) {
            $startSeason = $startPieces[0];
        } else {
            $startSeason = "";
        }
        if (isset($startPieces[1])) {
            $startEpisode = $startPieces[1];
        } else {
            $startEpisode = "";
        }
        /* if (!isset($stop)) {
          $stop = "9999x9999";
          } */
        //@list($stopSeason, $stopEpisode) = explode('x', $stop, 2);
        $stopPieces = explode('x', $stop, 2);
        if (isset($stopPieces[0])) {
            $stopSeason = $stopPieces[0];
        } else {
            $stopSeason = "";
        }
        if (isset($stopPieces[1])) {
            $stopEpisode = $stopPieces[1];
        } else {
            $stopEpisode = "";
        }

        /* if (!($item['episode'])) {
          return false;
          } */

        // Perform episode filter
        if (empty($filter)) {
            return true; // no filter left, accept all
        }

        // the following reg accepts the 1x1-2x27, 1-2x27, 1-3 or just 1
        $validateReg = '([0-9]+)(?:x([0-9]+))?';
        if (preg_match("/\dx\d-\dx\d/", $filter)) {
            if (preg_match("/^{$validateReg}-{$validateReg}/", $filter) === 0 || preg_match("/^{$validateReg}/", $filter) === 0) {
                twxa_debug("Bad episode filter: $filter\n", 0);
                return true; // bad filter, just accept all
            }
        }

        if (!($stopSeason)) {
            $stopSeason = $startSeason;
        }
        if (!($startEpisode)) {
            $startEpisode = 1;
        }
        if (!($stopEpisode)) {
            $stopEpisode = $startEpisode - 1;
        }

        $startEpisodeLen = strlen($startEpisode);
        if ($startEpisodeLen == 1) {
            $startEpisode = "0$startEpisode";
        }
        $stopEpisodeLen = strlen($stopEpisode);
        if ($stopEpisodeLen == 1) {
            $stopEpisode = "0$stopEpisode";
        }

        if (!preg_match('/^\d\d$/', $startSeason)) {
            $startSeason = 0 . $startSeason;
        }
        if (!preg_match('/^\d\d$/', $startEpisode)) {
            $startEpisode = 0 . $startEpisode;
        }
        if (!preg_match('/^\d\d$/', $stopSeason)) {
            $stopSeason = 0 . $stopSeason;
        }
        if (!preg_match('/^\d\d$/', $stopEpisode)) {
            $stopEpisode = 0 . $stopEpisode;
        }
        if (!preg_match('/^\d\d$/', $itemS)) {
            $itemS = 0 . $itemS;
        }
        if (!preg_match('/^\d\d$/', $itemE)) {
            $itemE = 0 . $itemE;
        }

        // Season filter mismatch
        if (!("$itemS$itemE" >= "$startSeason$startEpisode" && "$itemS$itemE" <= "$stopSeason$stopEpisode")) {
            twxa_debug("Season filter mismatch: $itemS$itemE $startSeason$startEpisode - $itemS$itemE $stopSeason$stopEpisode\n", 1);
            return false;
        }
        return true;
    } else {
        // $item['episode'] evaluates to false; should only happen for debugMatch of 0_, 1_, and so on
        return false;
    }
}

function check_for_torrent(&$item, $key, $opts) {
    //TODO third-most function, called by process_rss_feed()/process_atom_feed()
    global $matched, $config_values;

    if (!isset($item['Feed']) || !(strtolower($item['Feed']) === 'all' || $item['Feed'] === '' || $item['Feed'] == $opts['URL'])) {
        return;
    }
    $rs = $opts['Obj']; // $rs holds each feed list item, $item holds each Favorite item
    $ti = strtolower($rs['title']);

    // apply initial filtering from Favorites settings, prior to detectMatch(); may be why simplifyTitle() is necessary before this
    switch (isset_array_key($config_values['Settings'], 'Match Style')) { //TODO maybe simplify this
        case 'simple':
            $hit = (($item['Filter'] !== '' && strpos(strtr($ti, " .", "__"), strtr(strtolower($item['Filter']), " .", "__")) === 0) &&
                    ($item['Not'] === '' OR multi_str_search($ti, strtolower($item['Not'])) === false) &&
                    (strtolower($item['Quality']) == 'all' OR $item['Quality'] === '' OR multi_str_search($ti, strtolower($item['Quality'])) !== false));
            break;
        case 'glob':
            $hit = (($item['Filter'] !== '' && fnmatch(strtolower($item['Filter']), $ti)) &&
                    ($item['Not'] === '' OR ! fnmatch(strtolower($item['Not']), $ti)) &&
                    (strtolower($item['Quality']) == 'all' OR $item['Quality'] === '' OR strpos($ti, strtolower($item['Quality'])) !== false));
            break;
        case 'regexp':
        default:
            if (substr($item['Filter'], -1) === '!') {
                // last character of regex is an exclamation point, which fails to match when in front of \b
                $pattern = '/\b' . strtolower(str_replace(' ', '[\s._]', $item['Filter'])) . '/u';
            } else {
                $pattern = '/\b' . strtolower(str_replace(' ', '[\s._]', $item['Filter'])) . '\b/u';
            }
            $hit = (($item['Filter'] !== '' && preg_match($pattern, $ti)) &&
                    ($item['Not'] === '' || !preg_match('/' . strtolower($item['Not']) . '/u', $ti)) &&
                    (strtolower($item['Quality']) === 'all' || $item['Quality'] === '' || preg_match('/' . strtolower($item['Quality']) . '/u', $ti)));
            break;
    }

    if (isset($item['Filter']) && strtolower($item['Filter']) === "any") {
        $hit = 1; // $hit is local and not used above this function; it might not be used in check_cache() either
        $any = 1;
    }

    if ($hit) {
        $guessedItem = detectMatch($ti);
        //TODO add 'Require Episode Info' handling here to match items that don't have episode numbering at all
        //if ($hit && episode_filter($guess, $item['Episodes']) == true) {
        if (
                $config_values['Settings']['Require Episode Info'] != 1 ||
                (
                $guessedItem['numberSequence'] > 0 &&
                episode_filter($guessedItem, $item['Episodes']) == true
                )
        ) {
            $matched = "favStarted"; // used to be 'match'; this is set as default value here to be overwritten by exceptions below
            //twxa_debug("start with \$matched = \"favStarted\" for " . $rs['title'] . "\n", 2);
            if (check_cache($rs['title'])) { // check_cache() is false if title is or title and episode and version are found in cache
                if (
                        (!isset($any) || !$any) && 
                        $config_values['Settings']['Require Episode Info'] == 1 &&
                        isset_array_key($config_values['Settings'], 'Only Newer') == 1
                        ) {
                    if (is_numeric($guessedItem['seasBatEnd']) && is_numeric($guessedItem['seasBatStart'])) {
                        if ($guessedItem['seasBatEnd'] === $guessedItem['seasBatStart']) {
                            // within one season
                            if (is_numeric($guessedItem['episBatEnd'])) {
                                if ($guessedItem['episBatEnd'] === $guessedItem['episBatStart']) {
                                    // single episode of a single season
                                    if ($item['Season'] > $guessedItem['seasBatEnd']) {
                                        // too old by season
                                        twxa_debug("Ignoring: " . $item['Name'] . " (Fav:Cur S" . $item['Season'] . '>S' . $guessedItem['seasBatEnd'] . ")\n", 1);
                                        $matched = "favTooOld";
                                        return false;
                                    } else if ($item['Season'] == $guessedItem['seasBatEnd']) { // must not use === here
                                        // seasons match, compare episodes
                                        if ($item['Episode'] > $guessedItem['episBatEnd']) {
                                            // too old by episode within same season
                                            if ($guessedItem['itemVersion'] === 1) {
                                                twxa_debug("Ignoring: " . $item['Name'] . " (Fav:Cur " . $item['Season'] . "x" . $item['Episode'] . ">" . $guessedItem['seasBatEnd'] . "x" . $guessedItem['episBatEnd'] . ")\n", 1);
                                                $matched = "favTooOld";
                                                return false;
                                            } else if ($config_values['Settings']['Download Versions'] !== 1) {
                                                twxa_debug("Ignoring version: " . $item['Name'] . " (Fav:Cur " . $item['Season'] . "x" . $item['Episode'] . ">" . $guessedItem['seasBatEnd'] . "x" . $guessedItem['episBatEnd'] . " v" . $guessedItem['itemVersion'] . ")\n", 1);
                                                $matched = "favTooOld";
                                                return false;
                                            } else {
                                                // automatically download anything with an itemVersion over 1, even older episodes (except cached items)
                                            }
                                        } else if ($item['Episode'] == $guessedItem['episBatEnd']) {
                                            // same season and episode, compare itemVersion
                                            if ($guessedItem['itemVersion'] === 1) {
                                                twxa_debug("Ignoring: " . $item['Name'] . " (Fav:Cur " . $item['Season'] . "x" . $item['Episode'] . "=" . $guessedItem['seasBatEnd'] . "x" . $guessedItem['episBatEnd'] . ")\n", 1);
                                                $matched = "favTooOld";
                                                return false;
                                            } else if ($config_values['Settings']['Download Versions'] !== 1) {
                                                twxa_debug("Ignoring version: " . $item['Name'] . " (Fav:Cur " . $item['Season'] . "x" . $item['Episode'] . "=" . $guessedItem['seasBatEnd'] . "x" . $guessedItem['episBatEnd'] . " v" . $guessedItem['itemVersion'] . ")\n", 1);
                                                $matched = "favTooOld";
                                                return false;
                                            } else {
                                                // automatically download anything with an itemVersion over 1, even older episodes (except cached items)
                                            }
                                        } else {
                                            // episode is newer than Favorite, do download
                                        }
                                    } else {
                                        // season is newer, but might be a jump from 1xEE to 2017xEE notation; use episode_filter() to prevent this
                                    }
                                } else if ($guessedItem['episBatEnd'] > $guessedItem['episBatStart']) {
                                    // batch of episodes in a single season
                                    if ($config_values['Settings']['Ignore Batches'] == 0) {
                                        if ($item['Episode'] >= $guessedItem['episBatEnd']) {
                                            // nothing in the batch is newer than the favorite
                                            twxa_debug("Ignoring: " . $item['Name'] . " (Fav:Cur " . $item['Season'] . "x" . $item['Episode'] . ">=" . $guessedItem['seasBatEnd'] . "x" . $guessedItem['episBatEnd'] . ")\n", 1);
                                            $matched = "favTooOld";
                                            return false;
                                        } else {
                                            // at least one episode in the batch is newer by episode, do download
                                            //TODO for now, we don't care about itemVersion when dealing with batches
                                        }
                                    } else {
                                        // ignore batches
                                        twxa_debug("Ignoring batch: " . $guessedItem['title'] . "\n", 1);
                                        $matched = "ignFavBatch";
                                        return false;
                                    }
                                }
                            } else {
                                // probably empty string, do download
                            }
                        } else if ($guessedItem['seasBatEnd'] > $guessedItem['seasBatStart']) {
                            if ($config_values['Settings']['Ignore Batches'] == 0) {

                                // batch that spans seasons
                                if ($item['Season'] > $guessedItem['seasBatEnd']) {
                                    // last season in batch is older than favorite season
                                    twxa_debug("Ignoring: " . $item['Name'] . " (Fav:Cur S" . $item['Season'] . '>S' . $guessedItem['seasBatEnd'] . ")\n", 1);
                                    $matched = "favTooOld";
                                    return false;
                                } else if ($item['Season'] == $guessedItem['seasBatEnd']) { // must not use === here
                                    // last season in batch is equal to the favorite season, compare last episode
                                    if ($item['Episode'] >= $guessedItem['episBatEnd']) {
                                        // nothing in the batch is newer than the favorite
                                        twxa_debug("Ignoring: " . $item['Name'] . " (Fav:Cur " . $item['Season'] . "x" . $item['Episode'] . ">=" . $guessedItem['seasBatEnd'] . "x" . $guessedItem['episBatEnd'] . ")\n", 1);
                                        $matched = "favTooOld";
                                        return false;
                                    } else {
                                        // at least one episode in the batch is newer, do download
                                        //TODO for now, we don't care about itemVersion when dealing with batches
                                    }
                                }
                            } else {
                                // ignore batches
                                twxa_debug("Ignoring batch: " . $guessedItem['title'] . "\n", 1);
                                $matched = "ignFavBatch";
                                return false;
                            }
                        } else {
                            if ($config_values['Settings']['Ignore Batches'] == 0) {
                                // $guessedItem['seasBatEnd'] < $guessedItem['seasBatStart']; this should never occur, do download anyway
                            } else {
                                // ignore batches
                                twxa_debug("Ignoring batch: " . $guessedItem['title'] . "\n", 1);
                                $matched = "ignFavBatch";
                                return false;
                            }
                        }
                    } else {
                        twxa_debug("Season start or end is not numeric: S" . $guessedItem['seasBatStart'] . "-S" . $guessedItem['seasBatStart'] . "\n", 2);
                    }
                }
                twxa_debug("Match found for " . $rs['title'] . "\n", 2);
                $link = get_torrent_link($rs);
                if ($link) {
                    $response = client_add_torrent($link, null, $rs['title'], $opts['URL'], $item);
                    if (strpos($response, 'Error:') === 0) {
                        twxa_debug("Failed adding torrent $link\n", -1);
                        return false;
                    } else {
                        add_cache($rs['title']);
                    }
                } else {
                    twxa_debug("Unable to find URL for " . $rs['title'] . "\n", -1);
                    $matched = "noURL"; // doesn't do anything except overwrite $matched = "favStarted" for future logic
                    //TODO probably add gzip capability here
                }
            } else {
                //twxa_debug("cachehit for " . $rs['title'] . "\n", 2);
                $matched = "cachehit";
            }
        }
    }
}

function parse_one_rss($feed, $update = null) {
    global $config_values;
    $rss = new lastRSS;
    $rss->stripHTML = true;
    $rss->CDATA = 'content';
    if ((isset($config_values['Settings']['Cache Time'])) && ((int) $config_values['Settings']['Cache Time'])) {
        $rss->cache_time = (int) $config_values['Settings']['Cache Time'];
    } else if (!isset($update)) {
        $rss->cache_time = 86400;
    } else {
        $rss->cache_time = (15 * 60) - 20;
    }
    $rss->date_format = 'M d, H:i';

    if (isset($config_values['Settings']['Cache Dir'])) {
        $rss->cache_dir = $config_values['Settings']['Cache Dir'];
    }
    if (!$config_values['Global']['Feeds'][$feed['Link']] = $rss->get($feed['Link'])) {
        twxa_debug("Error creating rss parser for " . $feed['Link'] . "\n", -1);
    } else {
        if ($config_values['Global']['Feeds'][$feed['Link']]['items_count'] == 0) {
            unset($config_values['Global']['Feeds'][$feed['Link']]);
            return false;
        }
        $config_values['Global']['Feeds'][$feed['Link']]['URL'] = $feed['Link'];
        $config_values['Global']['Feeds'][$feed['Link']]['Feed Type'] = 'RSS';
    }
    return;
}

function parse_one_atom($feed) {
    global $config_values;
    if (isset($config_values['Settings']['Cache Dir'])) {
        $atom_parser = new myAtomParser($feed['Link'], $config_values['Settings']['Cache Dir']);
    } else {
        $atom_parser = new myAtomParser($feed['Link']);
    }

    if (!$config_values['Global']['Feeds'][$feed['Link']] = $atom_parser->getRawOutput()) {
        twxa_debug("Error creating atom parser for " . $feed['Link'] . "\n", -1);
    } else {
        $config_values['Global']['Feeds'][$feed['Link']]['URL'] = $feed['Link'];
        $config_values['Global']['Feeds'][$feed['Link']]['Feed Type'] = 'Atom';
    }
    return;
}

function get_torHash($cache_file) {
    $handle = fopen($cache_file, "r");
    if (filesize($cache_file)) {
        $torHash = fread($handle, filesize($cache_file));
        return $torHash;
    }
}

function process_rss_feed($rs, $idx, $feedName, $feedLink) {
    //TODO this is second-most function for feed processing, run by process_all_feeds()
    global $config_values, $matched, $html_out; // $matched is not used above this level

    twxa_debug("Started processing RSS feed: $feedName\n", 2);
    if (count($rs['items']) === 0) {
        twxa_debug("Feed is down: $feedName\n", 0);
        show_feed_down_header($idx);
        return;
    }

    if (isset($config_values['Global']['HTMLOutput']) && $config_values['Settings']['Combine Feeds'] == 0) {
        show_feed_list($idx);
    }
    $alt = 'alt';
    $items = array_reverse($rs['items']);
    $htmlList = [];
    foreach ($items as $item) {
        if (!isset($item['title'])) {
            $item['title'] = "";
        } else {
            $item['title'] = simplifyTitle($item['title']); //TODO first major function call, simplifyTitle() is somehow needed for accurate favorites matching
        }
        $torHash = "";
        $matched = "notAMatch";
        if (isset($config_values['Favorites'])) {
            array_walk($config_values['Favorites'], 'check_for_torrent', [ 'Obj' => $item, 'URL' => $rs['URL']]); //TODO second major function call, $matched is inside check_for_torrent()
        }
        //$client = $config_values['Settings']['Client'];
        if (isset($config_values['Settings']['Cache Dir'])) {
            $cache_file = $config_values['Settings']['Cache Dir'] . '/rss_dl_' . filename_encode($item['title']);
        }
        if (file_exists($cache_file)) { //TODO why does this not use check_cache() with cachehit, rewrite check_cache to return values
            $torHash = get_torHash($cache_file);
            //if ($matched !== "favStarted" && $matched != 'cachehit' && file_exists($cache_file)) { //TODO $matched comes from check_for_torrent() above
            if ($matched !== "favStarted" && $matched != 'cachehit') {
                $matched = 'downloaded';
                twxa_debug("Exact in cache; ignoring: " . $item['title'] . "\n", 1);
            } else if ($matched === 'cachehit') {
                //TODO if not going to use check_cache(), add item version handling here
                twxa_debug("Equiv. in cache; ignoring: " . $item['title'] . "\n", 1);
            }
        } //TODO check this block's logic and $matched states, when finished, copy to process_atom_feed() below
        if (isset($config_values['Global']['HTMLOutput'])) {
            if (!isset($rsnr)) {
                $rsnr = 1;
            } else {
                $rsnr++;
            }
            if (strlen($rsnr) <= 1) {
                $rsnr = 0 . $rsnr;
            }
            $id = $idx . $rsnr;
            $htmlList[] = [
                'item' => $item,
                'URL' => $feedLink,
                'feedName' => $feedName,
                'alt' => $alt,
                'torHash' => $torHash,
                'matched' => $matched,
                'id' => $id
            ];
        }

        if ($alt === 'alt') {
            $alt = '';
        } else {
            $alt = 'alt';
        }
    }
    $htmlList = array_reverse($htmlList, true);
    foreach ($htmlList as $item) {
        show_feed_item($item['item'], $item['URL'], $item['feedName'], $item['alt'], $item['torHash'], $item['matched'], $item['id']);
    }

    if (isset($config_values['Global']['HTMLOutput']) && $config_values['Settings']['Combine Feeds'] == 0) {
        close_feed_list();
    }
    unset($item);
    twxa_debug("Processed RSS feed: $feedName\n", 1);
}

function process_atom_feed($atom, $idx, $feedName, $feedLink) {
    //TODO this is second-most function for feed processing, run by process_all_feeds()
    global $config_values, $matched, $html_out; // $matched is not used above this level

    $atom = array_change_key_case_ext($atom, ARRAY_KEY_LOWERCASE);

    twxa_debug("Starting processing Atom feed: $feedName\n", 2);
    if (count($atom['feed']) === 0) {
        twxa_debug("Empty feed: $feedName\n", 2);
        return;
    }

    if (isset($config_values['Global']['HTMLOutput']) && $config_values['Settings']['Combine Feeds'] == 0) {
        show_feed_list($idx);
    }
    $alt = 'alt';
    $htmlList = [];
    $items = array_reverse($atom['feed']['entry']);
    foreach ($items as $item) {
        $item['title'] = simplifyTitle($item['title']);
        $torHash = '';
        $matched = "notAMatch";
        array_walk($config_values['Favorites'], 'check_for_torrent', [ 'Obj' => $item, 'URL' => $feedLink]);
        //$client = $config_values['Settings']['Client'];
        $cache_file = $config_values['Settings']['Cache Dir'] . '/rss_dl_' . filename_encode($item['title']);
        if (file_exists($cache_file)) {
            $torHash = get_torHash($cache_file);
            if ($matched !== "favStarted" && $matched != 'cachehit' && file_exists($cache_file)) {
                $matched = 'downloaded';
                twxa_debug("Exact in cache; ignoring: " . $item['title'] . "\n", 1);
            }
        }
        if (isset($config_values['Global']['HTMLOutput'])) {
            if (!($rsnr)) {
                $rsnr = 1;
            } else {
                $rsnr ++;
            }
            if (strlen($rsnr) <= 1) {
                $rsnr = 0 . $rsnr;
            }
            $id = $idx . $rsnr;
            $htmlItems = [
                'item' => $item,
                'URL' => $feedLink,
                'feedName' => $feedName,
                'alt' => $alt,
                'torHash' => $torHash,
                'matched' => $matched,
                'id' => $id
            ];
            //array_push($htmlList, $htmlItems);
            $htmlList[] = $htmlItems;
        }

        if ($alt === 'alt') {
            $alt = '';
        } else {
            $alt = 'alt';
        }
    }
    $htmlList = array_reverse($htmlList, true);
    foreach ($htmlList as $item) {
        show_feed_item($item['item'], $item['URL'], $item['feedName'], $item['alt'], $item['torHash'], $item['matched'], $item['id']);
    }
    if (isset($config_values['Global']['HTMLOutput']) && $config_values['Settings']['Combine Feeds'] == 0) {
        close_feed_list();
    }
    unset($item);
    twxa_debug("Processed Atom feed: $feedName\n", 1);
}

function process_all_feeds($feeds) {
    //TODO this is the top-most function for feed processing, happens right after getting list of feeds
    //global $config_values, $html_out;
    global $config_values;

    if (isset($config_values['Global']['HTMLOutput'])) {
        show_feed_lists_container();
    }

    if (isset($config_values['Global']['HTMLOutput']) && $config_values['Settings']['Combine Feeds'] == 1) {
        show_feed_list(0);
    }

    setup_cache();
    foreach ($feeds as $key => $feed) {
        switch ($feed['Type']) {
            case 'RSS':
                if (isset($config_values['Global']['Feeds'][$feed['Link']]) && $feed['enabled'] == "1") {
                    process_rss_feed($config_values['Global']['Feeds'][$feed['Link']], $key, $feed['Name'], $feed['Link']);
                } else if ($feed['enabled'] != "1") {
                    twxa_debug("Feed disabled: " . $feed['Name'] . "\n", 1);
                } else {
                    twxa_debug("Feed inaccessible: " . $feed['Name'] . "\n", 1);
                }
                break;
            case 'Atom':
                if (isset($config_values['Global']['Feeds'][$feed['Link']]) && $feed['enabled'] == "1") {
                    process_atom_feed($config_values['Global']['Feeds'][$feed['Link']], $key, $feed['Name'], $feed['Link']);
                } else if ($feed['enabled'] != "1") {
                    twxa_debug("Feed disabled: " . $feed['Name'] . "\n", 1);
                } else {
                    twxa_debug("Feed inaccessible: " . $feed['Name'] . "\n", 1);
                }
                break;
            default:
                twxa_debug("Unknown " . $feed['Type'] . " feed: " . $feed['Link'] . "\n", -1);
                break;
        }
    }

    if (isset($config_values['Global']['HTMLOutput']) && $config_values['Settings']['Combine Feeds'] == 1) {
        close_feed_list();
    }

    if ($config_values['Settings']['Client'] == "Transmission") {
        show_transmission_div();
    }

    if (isset($config_values['Global']['HTMLOutput'])) {
        close_feed_lists_container();
    }
}

function load_all_feeds($feeds, $update = null, $enabled = false) {
    //global $config_values;
    foreach ($feeds as $feed) {
        switch ($feed['Type']) {
            case 'RSS':
                if ($enabled === true || $feed['enabled'] == "1") {
                    parse_one_rss($feed, $update);
                } else {
                    twxa_debug("Feed disabled: " . $feed['Name'] . "\n", 1);
                }
                break;
            case 'Atom':
                if ($enabled === true || $feed['enabled'] == "1") {
                    parse_one_atom($feed);
                } else {
                    twxa_debug("Feed disabled: " . $feed['Name'] . "\n", 1);
                }
                break;
            default:
                twxa_debug("Unknown " . $feed['Type'] . " feed: " . $feed['Link'] . "\n", -1);
        }
    }
}
