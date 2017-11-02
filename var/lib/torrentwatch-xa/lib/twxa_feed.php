<?php

require_once('/var/lib/torrentwatch-xa/lib/twxa_parse.php');

/* function guess_atom_torrent($summary) {
  $wc = '[\/\:\w\.\+\?\&\=\%\;]+';
  // Detects: A HREF=\"http://someplace/with/torrent/in/the/name\"
  $regs = [];
  if (preg_match('/A HREF=\\\"(http' . $wc . 'torrent' . $wc . ')\\\"/', $summary, $regs)) {
  twxaDebug("guess_atom_torrent: $regs[1]\n", 2);
  return $regs[1];
  } else {
  twxaDebug("guess_atom_torrent: failed\n", 2); //TODO return and fix this function
  }
  return false;
  } */

function get_torrent_link($rs) {
    $links = [];
    if (
            isset($rs['enclosure']) &&
            (
            $rs['enclosure']['type'] == 'application/x-bittorrent' ||
            $rs['enclosure']['type'] == 'application/gzip'
            )
    ) {
        $links[] = $rs['enclosure']['url'];
    } else {
        if (isset($rs['link'])) {
            $links[] = $rs['link'];
        }
        if (isset($rs['id']) && stristr($rs['id'], 'http://')) { // Atom
            $links[] = $rs['id'];
        }
        if (isset($rs['enclosure'])) { // RSS enclosure
            $links[] = $rs['enclosure']['url'];
        }
    }
    $link = str_replace(" ", "%20", choose_torrent_link($links));
    return html_entity_decode($link);
}

function choose_torrent_link($links) {
    $linkCount = count($links);
    if ($linkCount > 1) {
        $bestLink = "";
        $torrentLinkCount = 0;
        // check how many links have ".torrent" in them
        foreach ($links as $link) {
            if (stripos($link, ".torrent") !== false) {
                $bestLink = $link;
                $torrentLinkCount++;
            }
        }
        // if only one had ".torrent", use that, else check http content-type for each,
        // and use the first that returns the proper torrent type
        if ($torrentLinkCount > 1) {
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
                    $bestLink = $link;
                    break;
                }
            }
        }
        //TODO probably add application/gzip capability here using gzdecode()
        // search for .torrent.gz
        // if still no match has been made, just select the first, and hope the html torrent parser can find it
        if (empty($bestLink)) {
            $bestLink = $links[0];
        }
        return $bestLink;
    } else if ($linkCount === 1) {
        return $links[0];
    } else {
        return "";
    }
}

function check_for_torrent(&$item, $key, $opts) {
    // third-most function, called by process_rss_feed()/process_atom_feed()
    global $itemState, $config_values;
    if (!isset($item['Feed']) || !(strtolower($item['Feed']) === 'all' || $item['Feed'] === '' || $item['Feed'] == $opts['URL'])) {
        return;
    }
    $rs = $opts['Obj']; // $rs holds each feed list item, $item holds each Favorite item
    $ti = strtolower($rs['title']);
    // apply initial filtering from Favorites settings, prior to detectMatch(); may be why simplifyTitle() is necessary before this
    switch (getArrayValueByKey($config_values['Settings'], 'Match Style')) {
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
                    ($item['Quality'] === '' || preg_match('/' . strtolower($item['Quality']) . '/u', $ti)));
    }
    if (isset($item['Filter']) && strtolower($item['Filter']) === "any") {
        $hit = 1; // $hit is local and not used above this function; it might not be used in check_cache() either
        $any = 1;
    }
    if ($hit) {
        $guessedItem = detectMatch($ti);
        if (
                $config_values['Settings']['Require Episode Info'] != 1 ||
                (
                $guessedItem['numberSequence'] > 0 &&
                episode_filter($guessedItem, $item['Episodes']) == true
                )
        ) {
            $itemState = "st_favReady"; // this is set as default value here to be overwritten by exceptions below
            if (check_cache($rs['title'])) { // check_cache() is false if title is or title and episode and version are found in cache
                if (
                        (!isset($any) || !$any) &&
                        $config_values['Settings']['Require Episode Info'] == 1 &&
                        getArrayValueByKey($config_values['Settings'], 'Only Newer') == 1
                ) {
                    if (is_numeric($guessedItem['seasBatEnd']) && is_numeric($guessedItem['seasBatStart'])) {
                        if ($guessedItem['seasBatEnd'] === $guessedItem['seasBatStart']) {
                            // within one season
                            if (is_numeric($guessedItem['episBatEnd'])) {
                                if ($guessedItem['episBatEnd'] === $guessedItem['episBatStart']) {
                                    // single episode of a single season
                                    if ($item['Season'] > $guessedItem['seasBatEnd']) {
                                        // too old by season
                                        twxaDebug("Ignoring: " . $item['Name'] . " (Fav:Cur S" . $item['Season'] . '>S' . $guessedItem['seasBatEnd'] . ")\n", 1);
                                        $itemState = "st_favTooOld";
                                        return false;
                                    } else if ($item['Season'] == $guessedItem['seasBatEnd']) { // must not use === here
                                        // seasons match, compare episodes
                                        if ($item['Episode'] > $guessedItem['episBatEnd'] || $item['Episode'] === "FULL") {
                                            // too old by episode within same season
                                            if ($guessedItem['itemVersion'] === 1) {
                                                twxaDebug("Ignoring: " . $item['Name'] . " (Fav:Cur " . $item['Season'] . "x" . $item['Episode'] . ">" . $guessedItem['seasBatEnd'] . "x" . $guessedItem['episBatEnd'] . ")\n", 1);
                                                $itemState = "st_favTooOld";
                                                return false;
                                            } else if ($config_values['Settings']['Download Versions'] != 1) {
                                                twxaDebug("Ignoring version: " . $item['Name'] . " (Fav:Cur " . $item['Season'] . "x" . $item['Episode'] . ">" . $guessedItem['seasBatEnd'] . "x" . $guessedItem['episBatEnd'] . " v" . $guessedItem['itemVersion'] . ")\n", 1);
                                                $itemState = "st_favTooOld";
                                                return false;
                                            } else {
                                                // automatically download anything with an itemVersion over 1, even older episodes (except cached items)
                                            }
                                        } else if ($item['Episode'] == $guessedItem['episBatEnd']) {
                                            // same season and episode, compare itemVersion
                                            if ($guessedItem['itemVersion'] === 1) {
                                                twxaDebug("Ignoring: " . $item['Name'] . " (Fav:Cur " . $item['Season'] . "x" . $item['Episode'] . "=" . $guessedItem['seasBatEnd'] . "x" . $guessedItem['episBatEnd'] . ")\n", 1);
                                                $itemState = "st_favTooOld";
                                                return false;
                                            } else if ($config_values['Settings']['Download Versions'] != 1) {
                                                twxaDebug("Ignoring version: " . $item['Name'] . " (Fav:Cur " . $item['Season'] . "x" . $item['Episode'] . "=" . $guessedItem['seasBatEnd'] . "x" . $guessedItem['episBatEnd'] . " v" . $guessedItem['itemVersion'] . ")\n", 1);
                                                $itemState = "st_favTooOld";
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
                                } else if ($guessedItem['episBatEnd'] > $guessedItem['episBatStart'] || $guessedItem['episBatEnd'] === "") {
                                    // batch of episodes in a single season
                                    if ($config_values['Settings']['Ignore Batches'] == 0) {
                                        if ($item['Episode'] >= $guessedItem['episBatEnd'] || $item['Episode'] === "FULL") {
                                            // nothing in the batch is newer than the favorite
                                            twxaDebug("Ignoring: " . $item['Name'] . " (Fav:Cur " . $item['Season'] . "x" . $item['Episode'] . ">=" . $guessedItem['seasBatEnd'] . "x" . $guessedItem['episBatEnd'] . ")\n", 1);
                                            $itemState = "st_favTooOld";
                                            return false;
                                        } else {
                                            // at least one episode in the batch is newer by episode, do download
                                            // for now, we don't care about itemVersion when dealing with batches
                                        }
                                    } else {
                                        // ignore batches
                                        twxaDebug("Ignoring batch: " . $guessedItem['title'] . "\n", 1);
                                        $itemState = "st_ignoredFavBatch";
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
                                    twxaDebug("Ignoring: " . $item['Name'] . " (Fav:Cur S" . $item['Season'] . '>S' . $guessedItem['seasBatEnd'] . ")\n", 1);
                                    $itemState = "st_favTooOld";
                                    return false;
                                } else if ($item['Season'] == $guessedItem['seasBatEnd']) { // must not use === here
                                    // last season in batch is equal to the favorite season, compare last episode
                                    if ($item['Episode'] >= $guessedItem['episBatEnd'] || $item['Episode'] === "FULL") {
                                        // nothing in the batch is newer than the favorite
                                        twxaDebug("Ignoring: " . $item['Name'] . " (Fav:Cur " . $item['Season'] . "x" . $item['Episode'] . ">=" . $guessedItem['seasBatEnd'] . "x" . $guessedItem['episBatEnd'] . ")\n", 1);
                                        $itemState = "st_favTooOld";
                                        return false;
                                    } else {
                                        // at least one episode in the batch is newer, do download
                                        // for now, we don't care about itemVersion when dealing with batches
                                    }
                                }
                            } else {
                                // ignore batches
                                twxaDebug("Ignoring batch: " . $guessedItem['title'] . "\n", 1);
                                $itemState = "st_ignoredFavBatch";
                                return false;
                            }
                        } else {
                            if ($config_values['Settings']['Ignore Batches'] == 0) {
                                // $guessedItem['seasBatEnd'] < $guessedItem['seasBatStart']; this should never occur, do download anyway
                            } else {
                                // ignore batches
                                twxaDebug("Ignoring batch: " . $guessedItem['title'] . "\n", 1);
                                $itemState = "st_ignoredFavBatch";
                                return false;
                            }
                        }
                    } else {
                        twxaDebug("Season start or end is not numeric: S" . $guessedItem['seasBatStart'] . "-S" . $guessedItem['seasBatStart'] . "\n", 2);
                        $itemState = "st_notSerialized";
                        return false;
                    }
                }
                twxaDebug("Match found for " . $rs['title'] . "\n", 2);
                $link = get_torrent_link($rs);
                if ($link) {
                    $response = client_add_torrent($link, null, $rs['title'], $opts['URL'], $item);
                    if (strpos($response, 'Error:') === 0) {
                        twxaDebug("Failed adding torrent $link\n", -1);
                        return false;
                    } else {
                        add_cache($rs['title']);
                        if ($config_values['Settings']['Client'] === "folder") {
                            $itemState = "st_downloaded";
                        } else {
                            $itemState = "st_downloading";
                        }
                        //TODO possibly remove st_downloading and st_downloaded states unless they make sense for client === folder
                    }
                } else {
                    twxaDebug("Unable to find URL for " . $rs['title'] . "\n", -1);
                    $itemState = "st_noURL"; // doesn't do anything except overwrite $itemState = "st_favReady" for future logic
                    //TODO probably add application/gzip capability here using gzdecode()
                }
            } else {
                $itemState = "st_inCache"; // only found in the cache; this state is transient
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
        twxaDebug("Error creating rss parser for " . $feed['Link'] . "\n", -1);
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
        twxaDebug("Error creating atom parser for " . $feed['Link'] . "\n", -1);
    } else {
        $config_values['Global']['Feeds'][$feed['Link']]['URL'] = $feed['Link'];
        $config_values['Global']['Feeds'][$feed['Link']]['Feed Type'] = 'Atom';
    }
    return;
}

function process_feed($feed, $idx, $feedName, $feedLink, $feedType) {
    // this is second-most function for feed processing, run by process_all_feeds()
    global $config_values, $itemState, $html_out; // $itemState is not used above this level
    twxaDebug("Started processing $feedType feed: $feedName\n", 2);
    switch ($feedType) {
        case "RSS":
            $itemCount = count($feed['items']);
            break;
        case "Atom":
            $feed = array_change_key_case_ext($feed, ARRAY_KEY_LOWERCASE);
            //$itemCount = count($atom['feed']);
            $itemCount = count($feed['feed']); //TODO test this
            break;
    }
    if ($itemCount === 0) {
        twxaDebug("Empty feed: $feedName\n", 0);
        show_feed_down_header($idx);
        return;
    }
    if (isset($config_values['Global']['HTMLOutput']) && $config_values['Settings']['Combine Feeds'] == 0) {
        show_feed_list($idx);
    }
    $alt = 'alt';
    switch ($feedType) {
        case "RSS":
            $items = array_reverse($feed['items']); //TODO move this up to process_all_feeds by feeding entire list in as parameter
            break;
        case "Atom";
            //$items = array_reverse($atom['feed']['entry']);
            $items = array_reverse($feed['feed']['entry']); //TODO test this
            break;
    }
    $htmlList = [];
    foreach ($items as $item) {
        if (!isset($item['title'])) {
            $item['title'] = "";
        } else {
            $item['title'] = simplifyTitle($item['title']); // first major function call, simplifyTitle() is somehow needed for accurate favorites matching
        }
        $torHash = "";
        $itemState = "st_notAMatch";
        if (isset($config_values['Favorites'])) {
            foreach ($config_values['Favorites'] as $favKey => $favValue) {
                // IMPORTANT: do not use $favValue, use $config_values['Favorites'][$favKey] so that the proper variable is passed by reference
                check_for_torrent($config_values['Favorites'][$favKey], $favKey, ['Obj' => $item, 'URL' => $feed['URL']]); // second major function call, $itemState of st_notAMatch might be overwritten and/or download might create cache file inside check_for_torrent() above
            }
        }
        if (isset($config_values['Settings']['Cache Dir'])) {
            $cache_file = $config_values['Settings']['Cache Dir'] . '/dl_' . sanitizeFilename($item['title']);
        }
        if (file_exists($cache_file)) { //TODO why does this not use check_cache() with inCache, rewrite check_cache to return values
            $torHash = get_torHash($cache_file);
            switch ($itemState) {
                /* These states become st_waitTorCheck in show_feed_item() if not saving torrent files to folder:
                 * st_favReady
                 * st_inCache
                 * st_downloading
                 * st_downloaded (may be removed from this list if PHP side fully-verifies the download succeeded) */
                case "st_favReady":
                case "st_favTooOld":
                case "st_ignoredFavBatch":
                    break;
                case "st_inCache":
                    twxaDebug("Equiv. in cache; ignoring: " . $item['title'] . "\n", 1);
                    break;
                default:
                    $itemState = "st_downloading";
                // no break!
                case "st_downloaded":
                    twxaDebug("Exact in cache; ignoring: " . $item['title'] . "\n", 1);
                    break;
            }
        }
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
                'itemState' => $itemState,
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
        show_feed_item($item['item'], $item['URL'], $item['feedName'], $item['alt'], $item['torHash'], $item['itemState'], $item['id']);
    }
    if (isset($config_values['Global']['HTMLOutput']) && $config_values['Settings']['Combine Feeds'] == 0) {
        close_feed_list();
    }
    unset($item);
    twxaDebug("Processed $feedType feed: $feedName\n", 1);
}

function process_all_feeds($feeds) {
    // this is the top-most function for feed processing, happens right after getting list of feeds
    global $config_values;

    if (isset($config_values['Global']['HTMLOutput'])) {
        show_feed_lists_container();
    }
    if (isset($config_values['Global']['HTMLOutput']) && $config_values['Settings']['Combine Feeds'] == 1) {
        show_feed_list(0);
    }
    setupCache();
    foreach ($feeds as $key => $feed) {
        switch ($feed['Type']) {
            case 'RSS':
            case 'Atom':
                if (isset($config_values['Global']['Feeds'][$feed['Link']]) && $feed['enabled'] == 1) {
                    process_feed($config_values['Global']['Feeds'][$feed['Link']], $key, $feed['Name'], $feed['Link'], $feed['Type']);
                } else if ($feed['enabled'] != 1) {
                    twxaDebug("Feed disabled: " . $feed['Name'] . "\n", 1);
                } else {
                    twxaDebug("Feed inaccessible: " . $feed['Name'] . "\n", 1);
                }
                break;
            default:
                twxaDebug("Unknown " . $feed['Type'] . " feed: " . $feed['Link'] . "\n", -1);
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
    foreach ($feeds as $feed) {
        switch ($feed['Type']) {
            case 'RSS':
                if ($enabled === true || $feed['enabled'] == 1) {
                    parse_one_rss($feed, $update);
                } else {
                    twxaDebug("Feed disabled: " . $feed['Name'] . "\n", 1);
                }
                break;
            case 'Atom':
                if ($enabled === true || $feed['enabled'] == 1) {
                    parse_one_atom($feed);
                } else {
                    twxaDebug("Feed disabled: " . $feed['Name'] . "\n", 1);
                }
                break;
            case 'Unknown':
            default:
                twxaDebug("Unknown feed type: " . $feed['Link'] . "\n", -1);
        }
    }
}

function guess_feed_type($feedurl) {
    $response = parseURLForCookies($feedurl);
    if (isset($response)) {
        $feedurl = $response['url'];
    }
    $get = curl_init();
    $curlOptions[CURLOPT_URL] = $feedurl;
    getcURLDefaults($curlOptions);
    curl_setopt_array($get, $curlOptions);
    $content = explode("\n", curl_exec($get));
    curl_close($get);
    // should be on the second line, but test up to the first 5 in case of doctype, etc.
    $contentCount = count($content);
    for ($i = 0; $i < $contentCount && $i < 5; $i++) {
        if (stripos($content[$i], '<feed xml') !== false) {
            twxaDebug("Feed $feedurl appears to be an Atom feed\n", 2);
            return "Atom";
        } else if (stripos($content[$i], '<rss') !== false) {
            twxaDebug("Feed $feedurl appears to be an RSS feed\n", 2);
            return "RSS";
        }
    }
    twxaDebug("Cannot determine feed type: $feedurl\n", 0);
    return "Unknown"; // was set to "RSS" as default, but this seemed to cause errors in add_feed()
}
