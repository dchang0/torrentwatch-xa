<?php

require_once('/var/lib/torrentwatch-xa/lib/twxa_parse.php');

function human_readable($n) {
    $scales = [ 'bytes', 'KB', 'MB', 'GB', 'TB'];
    $scale = $scales[0];
    for ($i = 1; $i < count($scales); $i++) {
        if ($n / 1024 < 1) {
            break;
        }
        $n = $n / 1024;
        $scale = $scales[$i];
    }
    return round($n, 2) . " $scale";
}

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
    $link_best = "";
    $word_matches = 0;
    if (count($links) === 0) {
        return "";
    }
    //Check how many links has ".torrent" in them
    foreach ($links as $link) {
        //if (preg_match("/\.torrent/", $link)) {
        if (stripos($link, "/\.torrent/") !== false) { //TODO maybe use stripos() for case-insensitive search
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
    $filter = preg_replace('/\s/', '', $filter);

    if (!isset($itemS)) {
        $itemS = '';
    }
    if (!isset($itemE)) {
        $itemE = '';
    }
    list($itemS, $itemE) = explode('x', $item['episode']);

    if (preg_match('/^S\d*/i', $filter)) {
        //$filter = preg_replace('/S/i', '', $filter);
        $filter = strtr($filter, array('S' => '', 's' => ''));
        if (preg_match('/^\d*E\d*/i', $filter)) {
            //$filter = preg_replace('/E/i', 'x', $filter);
            $filter = strtr($filter, array('E' => 'x', 'e' => 'x'));
        }
    }
    // Split the filter(ex. 3x4-4x15 into 3,3 4,15).  @ to suppress error when no second item
    if (isset($start)) {
        $start = '';
    }
    if (isset($stop)) {
        $stop = '';
    }
    @list($start, $stop) = explode('-', $filter, 2);
    @list($startSeason, $startEpisode) = explode('x', $start, 2);
    if (!isset($stop)) {
        $stop = "9999x9999";
    }
    @list($stopSeason, $stopEpisode) = explode('x', $stop, 2);
    if (!($item['episode'])) {
        return false;
    }

    // Perform episode filter
    if (empty($filter)) {
        return true; // no filter, accept all
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
}

function check_for_torrent(&$item, $key, $opts) {
    //TODO third-most function, called by process_rss_feed()/process_atom_feed()
    //global $matched, $test_run, $config_values;
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
                $pattern = '/\b' . strtolower(str_replace(' ', '[\s._]', $item['Filter'])) . '/';
            } else {
                $pattern = '/\b' . strtolower(str_replace(' ', '[\s._]', $item['Filter'])) . '\b/';
            }
            $hit = (($item['Filter'] !== '' && preg_match($pattern, $ti)) &&
                    ($item['Not'] === '' || ! preg_match('/' . strtolower($item['Not']) . '/', $ti)) &&
                    (strtolower($item['Quality']) === 'all' || $item['Quality'] === '' || preg_match('/' . strtolower($item['Quality']) . '/', $ti)));
            break;
    }

    if (isset($item['Filter']) && strtolower($item['Filter']) === "any") {
        $hit = 1; // $hit is local and not used above this function
        $any = 1;
    }

    if ($hit) {
        $guess = detectMatch($ti);
    }

    if ($hit && episode_filter($guess, $item['Episodes']) == true) {
        $matched = "favStarted"; // used to be 'match'; set as default value here to be overwritten by exceptions below
        //twxa_debug("start with \$matched = \"favStarted\" for " . $rs['title'] . "\n", 2);
        if (preg_match('/^\d+p$/', $item['Episode'])) { //TODO improve this old means of recording Proper episodes in the Favorite
            $item['Episode'] = preg_replace('/^(\d+)p/', '\1', $item['Episode']);
            $PROPER = 1;
        }
        if (check_cache($rs['title'])) { // check_cache() is false if title is or title and episode are found in cache
            if ((!isset($any) || !$any) && isset_array_key($config_values['Settings'], 'Only Newer') == 1) { //TODO test !isset($any) || logic
                if (!empty($guess['episode']) && preg_match('/^(\d+)x(\d+)p?$|^(\d{8})p?$/i', $guess['episode'], $regs)) {
                    if (isset($regs[3]) && preg_match('/^(\d{8})$/', $regs[3]) && $item['Episode'] >= $regs[3]) {
                        twxa_debug("Old by Episode/date; ignoring: " . $item['Name'] . " (" . $item['Episode'] . ' >= ' . $regs[3] . ")\n", 1);
                        $matched = "favTooOld";
                        return false;
                    } else if (isset($regs[1]) && preg_match('/^(\d{1,3})$/', $regs[1]) && $item['Season'] > $regs[1]) {
                        twxa_debug("Old by Season; ignoring: " . $item['Name'] . " (" . $item['Season'] . ' > ' . $regs[1] . ")\n", 1);
                        $matched = "favTooOld";
                        return false;
                    } else if (isset($regs[2]) && preg_match('/^(\d{1,3})$/', $regs[1]) && $item['Season'] == $regs[1] && $item['Episode'] >= $regs[2]) {
                        if (!preg_match('/proper|repack|rerip/i', $rs['title'])) { //TODO coordinate with check_cache_episode() handling of v2/v3
                            twxa_debug("Old by Season x Episode; ignoring: " . $item['Name'] . " (S: " . $item['Season'] . " = " . $regs[1] . ", E: " . $item['Episode'] . ' >= ' . $regs[2] . ")\n", 1);
                            $matched = "favTooOld";
                            return false;
                        } else if ($PROPER == 1) {
                            twxa_debug("Already downloaded Proper, Repack, or Rerip of; ignoring: " . $item['Name'] . " ($regs[1]x$regs[2]$regs[3])\n", 1);
                            $matched = "favTooOld";
                            return false;
                        }
                    }
                } else if ($guess['episode'] == 'fullSeason') { //TODO handle batches in general, not just full seasons
                    twxa_debug("Ignoring batch of: " . $item['name'] . "\n", 2);
                    $matched = "favBatch"; // used to be 'season'
                    return false;
                } else if (($guess['episode'] != 'noShow' && !preg_match('/^(\d{1,2} \d{1,2} \d{2,4})$/', $guess['episode'])) || $config_values['Settings']['Require Episode Info'] == 1) {
                    twxa_debug("$item is not in a workable format.\n", 0);
                    $matched = "notAMatch";
                    return false;
                }
            }
            twxa_debug("Match found for " . $rs['title'] . "\n", 2);
            /*if ($test_run) {
                $matched = "favReady"; // used to be 'test'; interrupts default value of "favStarted"
                return;
            }*/
            if ($link = get_torrent_link($rs)) {
                $response = client_add_torrent($link, null, $rs['title'], $opts['URL'], $item);
                //if (preg_match('/^Error:/', $response)) {
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
        }
        else {
            //twxa_debug("cachehit for " . $rs['title'] . "\n", 2);
            $matched = "cachehit";
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
            $item['title'] = simplifyTitle($item['title']); //TODO first major function call, simplifyTitle() is needed for favorites matching somehow
        }
        $torHash = "";
        $matched = "notAMatch";
        if (isset($config_values['Favorites'])) {
            array_walk($config_values['Favorites'], 'check_for_torrent', [ 'Obj' => $item, 'URL' => $rs['URL']]); //TODO second major function call, $matched is inside check_for_torrent()
        }
        $client = $config_values['Settings']['Client'];
        if (isset($config_values['Settings']['Cache Dir'])) {
            $cache_file = $config_values['Settings']['Cache Dir'] . '/rss_dl_' . filename_encode($item['title']);
        }
        if (file_exists($cache_file)) { //TODO why does this not use check_cache() with cachehit?
            $torHash = get_torHash($cache_file);
            if ($matched !== "favStarted" && $matched != 'cachehit' && file_exists($cache_file)) { //TODO $matched comes from check_for_torrent() above
                $matched = 'downloaded';
                twxa_debug("Already downloaded; ignoring: " . $item['title'] . "\n", 1);
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
        $client = $config_values['Settings']['Client'];
        $cache_file = $config_values['Settings']['Cache Dir'] . '/rss_dl_' . filename_encode($item['title']);
        if (file_exists($cache_file)) {
            $torHash = get_torHash($cache_file); //TODO add ability to compare hashes here; why does this not use check_cache() with cachehit?
            if ($matched !== "favStarted" && $matched != 'cachehit' && file_exists($cache_file)) {
                $matched = 'downloaded';
                twxa_debug("Already downloaded; ignoring: " . $item['title'] . "\n", 1);
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
            array_push($htmlList, $htmlItems);
        }

        if ($alt === 'alt') {
            $alt = '';
        } else {
            $alt = 'alt';
        }
    }
    //twxa_debug("\$htmlList: " . print_r($htmlList, true), 2); //TODO dumps lots of output into log
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
    global $config_values, $html_out;

    if (isset($config_values['Global']['HTMLOutput'])) {
        //echo('<div class="progressBarUpdates">'); //TODO why does this echo to console and not into $html_out?
        show_feed_lists_container();
    }

    if (isset($config_values['Global']['HTMLOutput']) && $config_values['Settings']['Combine Feeds'] == 1) {
        show_feed_list(0);
    }

    setup_cache();
    foreach ($feeds as $key => $feed) {
        switch ($feed['Type']) {
            case 'RSS':
                if (isset($config_values['Global']['Feeds'][$feed['Link']])) {
                    process_rss_feed($config_values['Global']['Feeds'][$feed['Link']], $key, $feed['Name'], $feed['Link']);
                }
                break;
            case 'Atom':
                if (isset($config_values['Global']['Feeds'][$feed['Link']])) {
                    process_atom_feed($config_values['Global']['Feeds'][$feed['Link']], $key, $feed['Name'], $feed['Link']);
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
        //echo('</div>'); //TODO why does this echo to console and not into $html_out?
        close_feed_lists_container();
    }
}

function load_all_feeds($feeds, $update = null) {
    global $config_values;
    //$count = count($feeds);
    //twxa_debug("count(\$feeds): $count . \$feeds: " . print_r($feeds, true) . "\n", 2);
    foreach ($feeds as $feed) {
        //twxa_debug("\$feed: " . print_r($feed, true) . "\n", 2);
        switch ($feed['Type']) {
            case 'RSS':
                parse_one_rss($feed, $update);
                break;
            case 'Atom':
                parse_one_atom($feed);
                break;
            default:
                twxa_debug("Unknown " . $feed['Type'] . " feed: " . $feed['Link'] . "\n", -1);
        }
    }
}
