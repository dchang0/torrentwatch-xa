<?php

require_once('twxa_parse.php');

function human_readable($n) {
    $scales = [ 'bytes', 'KB', 'MB', 'GB', 'TB' ];
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

    if (count($links) == 1) {
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
    if (count($links) == 0) {
        return "";
    }
    //Check how many links has ".torrent" in them
    foreach ($links as $link) {
        if (preg_match("/\.torrent/", $link)) {
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
        $filter = preg_replace('/S/i', '', $filter);
        if (preg_match('/^\d*E\d*/i', $filter)) {
            $filter = preg_replace('/E/i', 'x', $filter);
        }
    }
    // Split the filter(ex. 3x4-4x15 into 3,3 4,15).  @ to suppress error when no second item
    if (isset($start)) {
        $start = '';
    }
    if (isset($stop)) {
        $stop = '';
    }
    list($start, $stop) = explode('-', $filter, 2); //TODO fix PHP Notice:  Undefined offset: 1
    @list($startSeason, $startEpisode) = explode('x', $start, 2);
    if (!isset($stop)) {
        $stop = "9999x9999";
    }
    @list($stopSeason, $stopEpisode) = explode('x', $stop, 2);
    if (!($item['episode'])) {
        return False;
    }

    // Perform episode filter
    if (empty($filter)) {
        return true; // no filter, accept all    
    }

    // the following reg accepts the 1x1-2x27, 1-2x27, 1-3 or just 1
    $validateReg = '([0-9]+)(?:x([0-9]+))?';
    if (preg_match("/\dx\d-\dx\d/", $filter)) {
        if (preg_match("/^{$validateReg}-{$validateReg}/", $filter) === 0) {
            twxa_debug('bad episode filter: ' . $filter . '\n');
            return True; // bad filter, just accept all
        } else if (preg_match("/^{$validateReg}/", $filter) === 0) {
            twxa_debug('bad episode filter: ' . $filter . '\n');
            return True; // bad filter, just accept all
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

    // Season filter mis-match
    if (!("$itemS$itemE" >= "$startSeason$startEpisode" && "$itemS$itemE" <= "$stopSeason$stopEpisode")) {
        twxa_debug("$itemS$itemE $startSeason$startEpisode - $itemS$itemE $stopSeason$stopEpisode\n");
        return False;
    }
    return True;
}

function check_for_torrent(&$item, $key, $opts) {
    global $matched, $test_run, $config_values;

    if (!(strtolower($item['Feed']) == 'all' || $item['Feed'] === '' || $item['Feed'] == $opts['URL'])) {
        return;
    }
    $rs = $opts['Obj'];
    $ti = strtolower($rs['title']);
    switch (_isset($config_values['Settings'], 'MatchStyle')) {
        case 'simple':
            $hit = (($item['Filter'] != '' && strpos(strtr($ti, " .", "__"), strtr(strtolower($item['Filter']), " .", "__")) === 0) &&
                    ($item['Not'] == '' OR my_strpos($ti, strtolower($item['Not'])) === FALSE) &&
                    ($item['Quality'] == 'All' OR $item['Quality'] == '' OR my_strpos($ti, strtolower($item['Quality'])) !== FALSE));
            break;
        case 'glob':
            $hit = (($item['Filter'] != '' && fnmatch(strtolower($item['Filter']), $ti)) &&
                    ($item['Not'] == '' OR ! fnmatch(strtolower($item['Not']), $ti)) &&
                    ($item['Quality'] == 'All' OR $item['Quality'] == '' OR strpos($ti, strtolower($item['Quality'])) !== FALSE));
            break;
        case 'regexp':
        default:
            $hit = (($item['Filter'] != '' && preg_match('/\b' . strtolower(str_replace(' ', '[\s._]', $item['Filter'])) . '\b/', $ti)) &&
                    ($item['Not'] == '' OR ! preg_match('/' . strtolower($item['Not']) . '/', $ti)) &&
                    ($item['Quality'] == 'All' OR $item['Quality'] == '' OR preg_match('/' . strtolower($item['Quality']) . '/', $ti)));
            break;
    }

    if (strtolower($item['Filter']) == "any") {
        $hit = 1;
        $any = 1;
    }

    if ($hit) {
        $guess = detectMatch($ti, TRUE);
    }

    if ($hit && episode_filter($guess, $item['Episodes']) == true) {
        $matched = 'match';
        if (preg_match('/^\d+p$/', $item['Episode'])) {
            $item['Episode'] = preg_replace('/^(\d+)p/', '\1', $item['Episode']);
            $PROPER = 1;
        }
        if (check_cache($rs['title'])) {
            if (!$any && _isset($config_values['Settings'], 'Only Newer') == 1) {
                if (!empty($guess['episode']) && preg_match('/^(\d+)x(\d+)p?$|^(\d{8})p?$/i', $guess['episode'], $regs)) {
                    if (isset($regs[3]) && preg_match('/^(\d{8})$/', $regs[3]) && $item['Episode'] >= $regs[3]) {
                        twxa_debug($item['Name'] . ": " . $item['Episode'] . ' >= ' . $regs[3] . "\r\n", 1);
                        $matched = "old";
                        return FALSE;
                    } else if (isset($regs[1]) && preg_match('/^(\d{1,3})$/', $regs[1]) && $item['Season'] > $regs[1]) {
                        twxa_debug($item['Name'] . ": " . $item['Season'] . ' > ' . $regs[1] . "\r\n", 1);
                        $matched = "old";
                        return FALSE;
                    } else if (isset($regs[2]) && preg_match('/^(\d{1,3})$/', $regs[1]) && $item['Season'] == $regs[1] && $item['Episode'] >= $regs[2]) {
                        if (!preg_match('/proper|repack|rerip/i', $rs['title'])) {
                            twxa_debug($item['Name'] . ": " . $item['Episode'] . ' >= ' . $regs[2] . "\r\n", 1);
                            $matched = "old";
                            return FALSE;
                        } else if ($PROPER == 1) {
                            twxa_debug("Already downloaded this Proper, Repack or Rerip of " . $item['Name'] . " $regs[1]x$regs[2]$regs[3]\r\n");
                            $matched = "old";
                            return FALSE;
                        }
                    }
                } else if ($guess['episode'] == 'fullSeason') {
                    $matched = "season";
                    return FALSE;
                } else if (($guess['episode'] != 'noShow' && !preg_match('/^(\d{1,2} \d{1,2} \d{2,4})$/', $guess['episode'])) || $config_values['Settings']['Require Episode Info'] == 1) {
                    twxa_debug("$item is not in a workable format.\n");
                    $matched = "nomatch";
                    return FALSE;
                }
            }
            twxa_debug('Match found for ' . $rs['title'] . "\n");
            if ($test_run) {
                $matched = 'test';
                return;
            }
            if ($link = get_torrent_link($rs)) {
                $response = client_add_torrent($link, NULL, $rs['title'], $opts['URL'], $item);
                if (preg_match('/^Error:/', $response)) {
                    twxa_debug("Failed adding torrent $link\n", -1);
                    return FALSE;
                } else {
                    add_cache($rs['title']);
                }
            } else {
                twxa_debug("Unable to find URL for " . $rs['title'] . "\n", -1);
                $matched = "nourl";
            }
        }
    }
}

function parse_one_rss($feed, $update = NULL) {
    global $config_values;
    $rss = new lastRSS;
    $rss->stripHTML = True;
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
    } 
    else {
        if ($config_values['Global']['Feeds'][$feed['Link']]['items_count'] == 0) {
            unset($config_values['Global']['Feeds'][$feed['Link']]);
            return False;
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
    }
    else {
        $atom_parser = new myAtomParser($feed['Link']);
    }

    if (!$config_values['Global']['Feeds'][$feed['Link']] = $atom_parser->getRawOutput()) {
        twxa_debug("Error creating atom parser for " . $feed['Link'] . "\n", -1);
    }
    else {
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

function rss_perform_matching($rs, $idx, $feedName, $feedLink) {
    global $config_values, $matched;
    if (count($rs['items']) == 0) {
        show_down_feed($idx);
        return;
    }

    $percPerFeed = 80 / count($config_values['Feeds']);
    $percPerItem = $percPerFeed / count($rs['items']);
    if (isset($config_values['Global']['HTMLOutput']) && $config_values['Settings']['Combine Feeds'] == 0) {
        show_feed_html($idx);
    }
    $alt = 'alt';

    $items = array_reverse($rs['items']);
    $htmlList = [];
    foreach ($items as $item) {
        if (!isset($item['title'])) {
            $item['title'] = '';
        }
        else {
            $item['title'] = simplifyTitle($item['title']);
        }
        $torHash = '';
        $matched = 'nomatch';
        if (isset($config_values['Favorites'])) {
            array_walk($config_values['Favorites'], 'check_for_torrent', [ 'Obj' => $item, 'URL' => $rs['URL'] ]);
        }
        $client = $config_values['Settings']['Client'];
        if (isset($config_values['Settings']['Cache Dir'])) {
            $cache_file = $config_values['Settings']['Cache Dir'] . '/rss_dl_' . filename_encode($item['title']);
        }
        if (file_exists($cache_file)) {
            $torHash = get_torHash($cache_file);
            if ($matched != "match" && $matched != 'cachehit' && file_exists($cache_file)) {
                $matched = 'downloaded';
                twxa_debug("matched downloaded: " . $item['title'] . "\n", 1);
            }
        }
        if (isset($config_values['Global']['HTMLOutput'])) {
            if (!isset($rsnr)) {
                $rsnr = 1;
            } else {
                $rsnr++;
            };
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

        if ($alt == 'alt') {
            $alt = '';
        } else {
            $alt = 'alt';
        }
    }
    $htmlList = array_reverse($htmlList, true);
    foreach ($htmlList as $item) {
        show_torrent_html($item['item'], $item['URL'], $item['feedName'], $item['alt'], $item['torHash'], $item['matched'], $item['id']);
    }

    if (isset($config_values['Global']['HTMLOutput']) && $config_values['Settings']['Combine Feeds'] == 0) {
        close_feed_html($idx, 0);
    }
    unset($item);
}

function atom_perform_matching($atom, $idx, $feedName, $feedLink) {
    global $config_values, $matched;

    $atom = array_change_key_case_ext($atom, ARRAY_KEY_LOWERCASE);
    if (count($atom['feed']) == 0) {
        return;
    }

    if (isset($config_values['Global']['HTMLOutput']) && $config_values['Settings']['Combine Feeds'] == 0) {
        show_feed_html($idx);
    }
    $alt = 'alt';
    $htmlList = [];
    $items = array_reverse($atom['feed']['entry']);
    foreach ($items as $item) {
        $item['title'] = simplifyTitle($item['title']);
        $torHash = '';
        $matched = "nomatch";
        array_walk($config_values['Favorites'], 'check_for_torrent', array('Obj' => $item, 'URL' => $feedLink));
        $client = $config_values['Settings']['Client'];
        $cache_file = $config_values['Settings']['Cache Dir'] . '/rss_dl_' . filename_encode($item['title']);
        if (file_exists($cache_file)) {
            $torHash = get_torHash($cache_file);
            if ($matched != "match" && $matched != 'cachehit' && file_exists($cache_file)) {
                $matched = 'downloaded';
                twxa_debug("matched downloaded: " . $item['title'] . "\n", 1);
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

        if ($alt == 'alt') {
            $alt = '';
        } else {
            $alt = 'alt';
        }
    }
    //twxa_debug(print_r($htmlList, true)); //TODO dumps lots of output into log
    $htmlList = array_reverse($htmlList, true);
    foreach ($htmlList as $item) {
        show_torrent_html($item['item'], $item['URL'], $item['feedName'], $item['alt'], $item['torHash'], $item['matched'], $item['id']);
    }
    if (isset($config_values['Global']['HTMLOutput']) && $config_values['Settings']['Combine Feeds'] == 0) {
        close_feed_html($idx, 0);
    }
    unset($item);
}

function feeds_perform_matching($feeds) {
    global $config_values;

    if (isset($config_values['Global']['HTMLOutput'])) {
        echo('<div class="progressBarUpdates">');
        setup_rss_list_html();
    }

    if (isset($config_values['Global']['HTMLOutput']) && $config_values['Settings']['Combine Feeds'] == 1) {
        show_feed_html(0);
    }

    cache_setup();
    foreach ($feeds as $key => $feed) {
        switch ($feed['Type']) {
            case 'RSS':
                rss_perform_matching($config_values['Global']['Feeds'][$feed['Link']], $key, $feed['Name'], $feed['Link']);
                break;
            case 'Atom':
                atom_perform_matching($config_values['Global']['Feeds'][$feed['Link']], $key, $feed['Name'], $feed['Link']);
                break;
            default:
                twxa_debug("Unknown Feed. Feed: " . $feed['Link'] . "Type: " . $feed['Type'] . "\n", -1);
                break;
        }
    }

    if (isset($config_values['Global']['HTMLOutput']) && $config_values['Settings']['Combine Feeds'] == 1) {
        close_feed_html();
    }


    if ($config_values['Settings']['Client'] == "Transmission") {
        show_transmission_div();
    }

    if (isset($config_values['Global']['HTMLOutput'])) {
        echo('</div>');
        if (function_exists('finish_rss_list_html')) {
            finish_rss_list_html();
        }
    }
}

function load_feeds($feeds, $update = NULL) {
    global $config_values;
    $count = count($feeds);
    foreach ($feeds as $feed) {
        switch ($feed['Type']) {
            case 'RSS':
                parse_one_rss($feed, $update);
                break;
            case 'Atom':
                parse_one_atom($feed);
                break;
            default:
                twxa_debug("Unknown Feed. Feed: " . $feed['Link'] . "Type: " . $feed['Type'] . "\n", -1);
                break;
        }
    }
}
