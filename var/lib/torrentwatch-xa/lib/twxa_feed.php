<?php

require_once('twxa_parse.php');

/* function detectahrefsInString($string) {
  // detects <a href=""> tags in a string and returns the contents of the href attributes as an array
  $hrefs = [];
  if (isset($string) && is_string($string) && $string !== '') {
  $aTagMatches = [];
  preg_match_all("/<[aA]\s+?.+?>/", $string, $aTagMatches, PREG_PATTERN_ORDER);
  for ($i = 0; $i < count($aTagMatches[0]); $i++) {
  $aTag = $aTagMatches[0][$i];
  $hrefMatches = [];
  preg_match("/href=\"(.*?)\"/i", $aTag, $hrefMatches);
  $hrefContents = $hrefMatches[1];
  $hrefs[] = $hrefContents;
  }
  }
  return $hrefs;
  } */

function detectAllTorrentLinks($item) {
    // finds every possible magnet link or link to a .torrent file in a feed item
    $links = [];

    // search enclosure for .torrent and magnet:
    if (
            isset($item['enclosure']['url']) &&
            !empty($item['enclosure']['url']) &&
            (
            (isset($item['enclosure']['type']) && $item['enclosure']['type'] === 'application/x-bittorrent') ||
            strpos($item['enclosure']['url'], '.torrent', -8) !== false ||
            (isset($item['enclosure']['type']) && $item['enclosure']['type'] === 'x-scheme-handler/magnet') ||
            strpos($item['enclosure']['url'], 'magnet:') === 0
            )
    ) {
        $links[] = $item['enclosure']['url'];
    }
    // search $fi['URL'] for .torrent and magnet:
    if (
            isset($item['URL']) &&
            (
            strpos($item['URL'], '.torrent', -8) !== false ||
            strpos($item['URL'], 'magnet:') === 0
            )
    ) {
        $links[] = $item['URL'];
    }
    // search enclosure for .torrent.gz
    if (
            isset($item['enclosure']['url']) &&
            !empty($item['enclosure']['url']) &&
            (
            (isset($item['enclosure']['type']) && $item['enclosure']['type'] === 'application/gzip') ||
            strpos($item['enclosure']['url'], '.torrent.gz', -11) !== false
            )
    ) {
        $links[] = $item['enclosure']['url'];
    }
    // search $fi['URL'] for .torrent.gz
    if (isset($item['URL']) && strpos($item['URL'], '.torrent.gz', -11) !== false) {
        $links[] = $item['URL'];
    }
    // accept the enclosure URL as long as it's not the wrong type
    if (
            isset($item['enclosure']['url']) &&
            !empty($item['enclosure']['url']) &&
            (
            isset($item['enclosure']['type']) &&
            (
            strpos($item['enclosure']['type'], 'text/') === false &&
            strpos($item['enclosure']['type'], 'image/') === false &&
            strpos($item['enclosure']['type'], 'audio/') === false &&
            strpos($item['enclosure']['type'], 'video/') === false &&
            $item['enclosure']['type'] !== 'application/xml'
            )
            )
    ) {
        $links[] = $item['enclosure']['url'];
    }
    // last resort, trust the feed and accept the URL
    if (
            isset($item['URL']) &&
            !empty($item['URL']) &&
            stripos($item['URL'], '.html', -5) === false &&
            stripos($item['URL'], '.htm', -4) === false
    ) {
        $links[] = $item['URL'];
    }
//    ///// search the feed item's contents (this section slows performance dramatically)
//    $itemContentsHrefs = detectahrefsInString($fi['content']); // finds all the <a href= values in this item's contents
//    // search $fi['content'] for .torrent and magnet:
//    foreach ($itemContentsHrefs as $href) {
//        if (
//                isset($href) &&
//                (
//                strpos($href, '.torrent', -8) !== false ||
//                strpos($href, 'magnet:') === 0
//                )
//        ) {
//            $links[] = $href;
//        }
//    }
//    // search $fi['content'] for .torrent.gz
//    foreach ($itemContentsHrefs as $href) {
//        if (isset($href) && strpos($href, '.torrent.gz', -11) !== false) {
//            $links[] = $href;
//        }
//    }
//    //TODO search all for infoHash and convert to magnet link
    // make links unique in the array
    $uniqueLinks = array_unique($links, SORT_STRING);
    //TODO validate URLs in array using filter_var($url, FILTER_VALIDATE_URL);
    return $uniqueLinks;
}

function getBestTorrentOrMagnetLinks($item) {
    // returns the first (assumed to be best) torrent file link in the item
    // can include gzipped torrent file links
    // tries to minimize computations and downloads for best performance
    $bestLink = "";
    $bestLinkType = "";
    $bestMagnetLink = "";
    $links = detectAllTorrentLinks($item);
    $linkCount = count($links);
    if ($linkCount > 1) {
        // categorize the links
        $endInDotTorrentLinks = [];
        $otherDotTorrentLinks = [];
        $unknownLinks = [];
        $endInDotTorrentGzLinks = [];
        $validMagnetLinks = [];
        $invalidMagnetLinks = [];

        foreach ($links as $link) {
            switch (true) {
                case true: // magnet link; this must be first since magnet links can contain ".torrent"
                    if (strpos($link, 'magnet:') === 0) {
                        // check that the magnet link has an xt (Exact Topic) with a valid Bittorrent Info Hash
                        if (preg_match('/xt=urn:btih:[a-fA-F0-9]{40}/', $links[$i])) {
                            $validMagnetLinks[] = $link;
                            break;
                        } else {
                            $invalidMagnetLinks[] = $link;
                        }
                    }
                case true: // link ends in .torrent
                    if (strpos($link, '.torrent', -8) !== false) {
                        $endInDotTorrentLinks[] = $link;
                        break;
                    }
                case true: // link ends in .torrent.gz
                    if (strpos($link, '.torrent.gz', -11) !== false) {
                        $endInDotTorrentGzLinks[] = $link;
                        break;
                    }
                case true: // link contains .torrent
                    if (strpos($link, '.torrent') !== false) {
                        $otherDotTorrentLinks[] = $link;
                        break;
                    }
                default:
                    $unknownLinks[] = $link;
            }
        }
        // select the best link overall
        switch (true) {
            case true: // if only one ended in .torrent, use it
                if (count($endInDotTorrentLinks) === 1) {
                    $bestLink = $endInDotTorrentLinks[0];
                    $bestLinkType = "torrent";
                    break;
                }
            case true: // if there's one or more valid magnet links, use the first one
                if (count($validMagnetLinks) > 0) {
                    $bestLink = $validMagnetLinks[0];
                    $bestLinkType = "magnet";
                    break;
                }
            case true: // if at least one ends with .torrent.gz, use the first one
                if (count($endInDotTorrentGzLinks) > 0) {
                    $bestLink = $endInDotTorrentGzLinks[0];
                    $bestLinkType = "torrent.gz";
                    break;
                }
            default: // combine all the non-magnet and non-torrent.gz links, download the Content-Type, and pick the first one that has the right Content-Type
                $nonMagnetLinks = array_merge($endInDotTorrentLinks, $otherDotTorrentLinks, $unknownLinks);
                foreach ($nonMagnetLinks as $link) {
                    $opts = [
                        'http' => ['timeout' => 10]
                    ];
                    stream_context_get_default($opts);
                    $headers = get_headers($link, 1);
                    if (
                            (
                            isset($headers['Content-Disposition']) &&
                            preg_match('/filename=.+\.torrent/i', $headers['Content-Disposition'])
                            ) ||
                            (
                            isset($headers['Content-Type']) &&
                            $headers['Content-Type'] == 'application/x-bittorrent'
                            )
                    ) {
                        $bestLink = $link;
                        $bestLinkType = "torrent";
                        break;
                    }
                }
        }
        // select the best valid magnet link
        if (count($validMagnetLinks) > 0) {
            $bestMagnetLink = $validMagnetLinks[0];
        }
    } else if ($linkCount === 1) {
        $bestLink = $links[0];
        if (strpos($bestLink, 'magnet:') === 0) {
            $bestMagnetLink = $bestLink;
            $bestLinkType = "magnet";
        } else if (strpos($bestLink, '.torrent.gz', -11) !== false) {
            $bestLinkType = "torrent.gz";
        } else {
            $bestLinkType = "torrent";
        }
    } else {
        // no links
        writeToLog("Array of links is empty--skipping this item...\n", 2);
    }
    return [
        'link' => $bestLink,
        'type' => $bestLinkType,
        'magnetLink' => $bestMagnetLink
    ];
}

function checkItemTitleMatchesSuperFavorite($superfav, $itemTitle, $feedUrl, $matchStyle) {
    // checks if this item's title matches this Super-Favorite's title
    $itemMatchesSuperFave = false;
    if (isset($superfav)) {
        if (isset($itemTitle) && !empty($itemTitle)) {
            if (!isset($superfav['Feed']) || $superfav['Feed'] === '' || strtolower($superfav['Feed']) === 'all' || $superfav['Feed'] == $feedUrl) {
                // if Super-Favorite's Feed is unset, blank, all, or matches the item, proceed
                switch ($matchStyle) {
                    case 'simple':
                        $itemMatchesSuperFave = (
                                (
                                $superfav['Filter'] !== '' &&
                                strpos(strtr($itemTitle, " .", "__"), strtr(strtolower($superfav['Filter']), " .", "__")) === 0
                                ) &&
                                (
                                $superfav['Not'] === '' ||
                                multi_str_search($itemTitle, strtolower($superfav['Not'])) === false
                                ) &&
                                (
                                strtolower($superfav['Quality']) == 'all' ||
                                $superfav['Quality'] === '' ||
                                multi_str_search($itemTitle, strtolower($superfav['Quality'])) !== false
                                )
                                );
                        break;
                    case 'glob':
                        $itemMatchesSuperFave = (
                                (
                                $superfav['Filter'] !== '' &&
                                fnmatch(strtolower($superfav['Filter']), $itemTitle)) &&
                                (
                                $superfav['Not'] === '' ||
                                !fnmatch(strtolower($superfav['Not']), $itemTitle)
                                ) &&
                                (
                                strtolower($superfav['Quality']) == 'all' ||
                                $superfav['Quality'] === '' ||
                                fnmatch(strtolower($superfav['Quality']), $itemTitle) !== false
                                )
                                );
                        break;
                    case 'regexp':
                    default:
                        if (substr($superfav['Filter'], -1) === '!') {
                            // last character of regex is an exclamation point, which fails to match when in front of \b
                            $pattern = '/\b' . strtolower(str_replace(' ', '[\s._]', $superfav['Filter'])) . '/u';
                        } else {
                            $pattern = '/\b' . strtolower(str_replace(' ', '[\s._]', $superfav['Filter'])) . '\b/u';
                        }
                        $itemMatchesSuperFave = (
                                (
                                $superfav['Filter'] !== '' &&
                                preg_match($pattern, $itemTitle)) &&
                                (
                                $superfav['Not'] === '' ||
                                !preg_match('/' . strtolower($superfav['Not']) . '/u', $itemTitle)
                                ) &&
                                (
                                $superfav['Quality'] === '' ||
                                preg_match('/' . strtolower($superfav['Quality']) . '/u', $itemTitle)
                                )
                                );
                }
            } else {
                // item does not match this Super-Favorite's Feed filter
            }
        } else {
            // no item to compare
            writeToLog("No item to compare to the Super-Favorite\n", 0);
        }
    } else {
        // no Super-Favorite to compare
        writeToLog("No Super-Favorite to compare to the item\n", 0);
    }
    return $itemMatchesSuperFave;
}

function checkItemTitleMatchesFavorite($fav, $itemTitle, $feedUrl, $matchStyle) {
    // checks if this item's title matches this Favorite's title
    $itemMatchesFave = false;
    if (isset($fav)) {
        if (isset($itemTitle) && !empty($itemTitle)) {
            if (!isset($fav['Feed']) || $fav['Feed'] === '' || strtolower($fav['Feed']) === 'all' || $fav['Feed'] == $feedUrl) {
                // if Favorite's Feed is unset, blank, all, or matches the item, proceed
                switch ($matchStyle) {
                    case 'simple':
                        $itemMatchesFave = (
                                (
                                $fav['Filter'] !== '' &&
                                strpos(strtr($itemTitle, " .", "__"), strtr(strtolower($fav['Filter']), " .", "__")) === 0
                                ) &&
                                (
                                $fav['Not'] === '' ||
                                multi_str_search($itemTitle, strtolower($fav['Not'])) === false
                                ) &&
                                (
                                strtolower($fav['Quality']) == 'all' ||
                                $fav['Quality'] === '' ||
                                multi_str_search($itemTitle, strtolower($fav['Quality'])) !== false
                                )
                                );
                        break;
                    case 'glob':
                        $itemMatchesFave = (
                                (
                                $fav['Filter'] !== '' &&
                                fnmatch(strtolower($fav['Filter']), $itemTitle)) &&
                                (
                                $fav['Not'] === '' ||
                                !fnmatch(strtolower($fav['Not']), $itemTitle)
                                ) &&
                                (
                                strtolower($fav['Quality']) == 'all' ||
                                $fav['Quality'] === '' ||
                                fnmatch(strtolower($fav['Quality']), $itemTitle) !== false
                                )
                                );
                        break;
                    case 'regexp':
                    default:
                        if (substr($fav['Filter'], -1) === '!') {
                            // last character of regex is an exclamation point, which fails to match when in front of \b
                            $pattern = '/\b' . strtolower(str_replace(' ', '[\s._]', $fav['Filter'])) . '/u';
                        } else {
                            $pattern = '/\b' . strtolower(str_replace(' ', '[\s._]', $fav['Filter'])) . '\b/u';
                        }
                        $itemMatchesFave = (
                                (
                                $fav['Filter'] !== '' &&
                                preg_match($pattern, $itemTitle)) &&
                                (
                                $fav['Not'] === '' ||
                                !preg_match('/' . strtolower($fav['Not']) . '/u', $itemTitle)
                                ) &&
                                (
                                $fav['Quality'] === '' ||
                                preg_match('/' . strtolower($fav['Quality']) . '/u', $itemTitle)
                                )
                                );
                }
            } else {
                // item does not match this Favorite's Feed filter
            }
        } else {
            // no item to compare
            writeToLog("No item to compare to the Favorite\n", 0);
        }
    } else {
        // no favorite to compare
        writeToLog("No Favorite to compare to the item\n", 0);
    }
    return $itemMatchesFave;
}

function checkItemNumberingMatchesFavorite(&$itemState, $fav, $guessItem, $dloadVersions = 0, $ignBatches = 0) {
    // checks if this item's numbering matches this Favorite's numbering
    $itemMatchesFave = false;
    if (is_numeric($guessItem['seasBatEnd']) && is_numeric($guessItem['seasBatStart'])) {
        if ($guessItem['seasBatEnd'] === $guessItem['seasBatStart']) {
            // within one season
            if (is_numeric($guessItem['episBatEnd'])) {
                if ($guessItem['episBatEnd'] === $guessItem['episBatStart']) {
                    // single episode of a single season
                    if ($fav['Season'] > $guessItem['seasBatEnd']) {
                        // too old by season
                        writeToLog("Ignoring: " . $fav['Name'] . " (Fav:Cur S" . $fav['Season'] . '>S' . $guessItem['seasBatEnd'] . ")\n", 1);
                        $itemState = "st_favTooOld";
                    } else if ($fav['Season'] == $guessItem['seasBatEnd']) { // must not use === here
                        // seasons match, compare episodes
                        if ($fav['Episode'] > $guessItem['episBatEnd'] || $fav['Episode'] === "FULL") {
                            // too old by episode within same season
                            if ($guessItem['itemVersion'] === 1) {
                                writeToLog("Ignoring: " . $fav['Name'] . " (Fav:Cur " . $fav['Season'] . "x" . $fav['Episode'] . ">" . $guessItem['seasBatEnd'] . "x" . $guessItem['episBatEnd'] . ")\n", 1);
                                $itemState = "st_favTooOld";
                            } else if ($dloadVersions != 1) {
                                writeToLog("Ignoring version: " . $fav['Name'] . " (Fav:Cur " . $fav['Season'] . "x" . $fav['Episode'] . ">" . $guessItem['seasBatEnd'] . "x" . $guessItem['episBatEnd'] . " v" . $guessItem['itemVersion'] . ")\n", 1);
                                $itemState = "st_favTooOld";
                            } else {
                                // automatically download anything with an itemVersion over 1, even older episodes (except cached items)
                                $itemState = "st_favReady";
                                $itemMatchesFave = true;
                            }
                        } else if ($fav['Episode'] == $guessItem['episBatEnd']) {
                            // same season and episode, compare itemVersion
                            if ($guessItem['itemVersion'] === 1) {
                                writeToLog("Ignoring: " . $fav['Name'] . " (Fav:Cur " . $fav['Season'] . "x" . $fav['Episode'] . "=" . $guessItem['seasBatEnd'] . "x" . $guessItem['episBatEnd'] . ")\n", 1);
                                $itemState = "st_favTooOld";
                            } else if ($dloadVersions != 1) {
                                writeToLog("Ignoring version: " . $fav['Name'] . " (Fav:Cur " . $fav['Season'] . "x" . $fav['Episode'] . "=" . $guessItem['seasBatEnd'] . "x" . $guessItem['episBatEnd'] . " v" . $guessItem['itemVersion'] . ")\n", 1);
                                $itemState = "st_favTooOld";
                            } else {
                                // automatically download anything with an itemVersion over 1, even older episodes (except cached items)
                                $itemState = "st_favReady";
                                $itemMatchesFave = true;
                            }
                        } else {
                            // episode is newer than Favorite, do download
                            $itemState = "st_favReady";
                            $itemMatchesFave = true;
                        }
                    } else {
                        // season is newer, but might be a jump from 1xEE to 2017xEE notation; use episode_filter() to prevent this
                        $itemState = "st_favReady";
                        $itemMatchesFave = true;
                    }
                } else if ($guessItem['episBatEnd'] > $guessItem['episBatStart'] || $guessItem['episBatEnd'] === "") {
                    // batch of episodes in a single season
                    if ($ignBatches == 0) {
                        if ($fav['Episode'] >= $guessItem['episBatEnd'] || $fav['Episode'] === "FULL") {
                            // nothing in the batch is newer than the favorite
                            writeToLog("Ignoring: " . $fav['Name'] . " (Fav:Cur " . $fav['Season'] . "x" . $fav['Episode'] . ">=" . $guessItem['seasBatEnd'] . "x" . $guessItem['episBatEnd'] . ")\n", 1);
                            $itemState = "st_favTooOld";
                        } else {
                            // at least one episode in the batch is newer by episode, do download
                            // for now, we don't care about itemVersion when dealing with batches
                            $itemState = "st_favReady";
                            $itemMatchesFave = true;
                        }
                    } else {
                        // ignore batches
                        writeToLog("Ignoring batch: " . $guessItem['title'] . "\n", 1);
                        $itemState = "st_ignoredFavBatch";
                    }
                }
            } else {
                // probably empty string (FULL season)
                if ($ignBatches == 0) {
                    // do download
                    $itemState = "st_favReady";
                    $itemMatchesFave = true;
                } else {
                    // ignore batches
                    writeToLog("Ignoring batch: " . $guessItem['title'] . "\n", 1);
                    $itemState = "st_ignoredFavBatch";
                }
            }
        } else if ($guessItem['seasBatEnd'] > $guessItem['seasBatStart']) {
            if ($ignBatches == 0) {
                // batch that spans seasons
                if ($fav['Season'] > $guessItem['seasBatEnd']) {
                    // last season in batch is older than favorite season
                    writeToLog("Ignoring: " . $fav['Name'] . " (Fav:Cur S" . $fav['Season'] . '>S' . $guessItem['seasBatEnd'] . ")\n", 1);
                    $itemState = "st_favTooOld";
                } else if ($fav['Season'] == $guessItem['seasBatEnd']) { // must not use === here
                    // last season in batch is equal to the favorite season, compare last episode
                    if ($fav['Episode'] >= $guessItem['episBatEnd'] || $fav['Episode'] === "FULL") {
                        // nothing in the batch is newer than the favorite
                        writeToLog("Ignoring: " . $fav['Name'] . " (Fav:Cur " . $fav['Season'] . "x" . $fav['Episode'] . ">=" . $guessItem['seasBatEnd'] . "x" . $guessItem['episBatEnd'] . ")\n", 1);
                        $itemState = "st_favTooOld";
                    } else {
                        // at least one episode in the batch is newer, do download
                        // for now, we don't care about itemVersion when dealing with batches
                        $itemState = "st_favReady";
                        $itemMatchesFave = true;
                    }
                }
            } else {
                // ignore batches
                writeToLog("Ignoring batch: " . $guessItem['title'] . "\n", 1);
                $itemState = "st_ignoredFavBatch";
            }
        } else {
            if ($ignBatches == 0) {
                // $guessedItem['seasBatEnd'] < $guessedItem['seasBatStart']; this should never occur, do download anyway
                $itemState = "st_favReady";
                $itemMatchesFave = true;
            } else {
                // ignore batches
                writeToLog("Ignoring batch: " . $guessItem['title'] . "\n", 1);
                $itemState = "st_ignoredFavBatch";
            }
        }
    } else {
        writeToLog("Season start or end is not numeric: S" . $guessItem['seasBatStart'] . "-S" . $guessItem['seasBatStart'] . "\n", 2);
        $itemState = "st_notSerialized";
    }
    return $itemMatchesFave;
}

function checkItemMatchesSuperFavorite($superfav, $item, $feedUrl, $matchStyle = "regexp", $reqEpisInfo = 1) {
    // checks if this item matches this Super-Favorite
    $itemMatchesSuperFave = false;
    $ti = strtolower($item['title']);
    if (checkItemTitleMatchesSuperFavorite($superfav, $ti, $feedUrl, $matchStyle)) {
        $guessItem = detectMatch($ti);
        writeToLog("reqEpisodeInfo = $reqEpisInfo\n", 2);
        if (
                $reqEpisInfo != 1 ||
                (
                $guessItem['numberSequence'] > 0 &&
                episode_filter($guessItem, $superfav['Episodes']) == true
                )
        ) {
            // match
            $itemMatchesSuperFave = true;
        }
    } else {
        // no match between Item Title and Super-Favorite
    }
    if ($itemMatchesSuperFave) {
        writeToLog("Super-Favorite match found for " . $item['title'] . "\n", 2);
    }
    return $itemMatchesSuperFave;
}

function checkItemMatchesFavorite(&$itemState, $fav, $item, $feedUrl, $matchStyle = "regexp", $reqEpisInfo = 1, $onlyNewer = 1, $dloadVersions = 1, $ignBatches = 0) {
    // checks if this item matches this Favorite
    $itemMatchesFave = false;
    $ti = strtolower($item['title']);
    $isFilterAny = (isset($item['Filter']) && strtolower($item['Filter']) === "any");
    if ($isFilterAny || checkItemTitleMatchesFavorite($fav, $ti, $feedUrl, $matchStyle)) {
        $guessItem = detectMatch($ti);
        if (
                $reqEpisInfo != 1 ||
                (
                $guessItem['numberSequence'] > 0 &&
                episode_filter($guessItem, $fav['Episodes']) == true
                )
        ) {
            if (
                    !$isFilterAny &&
                    $reqEpisInfo == 1 &&
                    $onlyNewer == 1
            ) {
                $itemMatchesFave = checkItemNumberingMatchesFavorite(
                        $itemState,
                        $fav,
                        $guessItem,
                        $dloadVersions,
                        $ignBatches
                );
            } else {
                // match it anyway
                $itemMatchesFave = true;
                $itemState = "st_favReady";
            }
        } else {
            // match it anyway
            $itemMatchesFave = true;
            $itemState = "st_favReady";
        }
    } else {
        // no match between Item Title and Favorite, leave $itemState blank
    }
    if ($itemMatchesFave) {
        writeToLog("Favorite match found for " . $item['title'] . "\n", 2);
    }
    return $itemMatchesFave;
}

function processMatchedItemDownload(
        &$itemState,
        &$fav,
        $item,
        $feedUrl,
        $clientType,
        $globalDownloadDir,
        $deepDirs,
        $sMTPNotifications,
        $enableScript,
        $alsoSaveTorrentFiles,
        $alsoSaveDir,
        $configFeeds,
        $defaultSeedRatio
) {
    // item should be downloaded; figure out if it is already downloaded and start the download using the correct Client if not
    $startedDownload = false;
    if (check_cache($item['title'])) { // check_cache() is false if title is or title and episode and version are found in cache
        $bestLink = getBestTorrentOrMagnetLinks($item);
        if (isset($bestLink['link'])) {
            $response = clientAddTorrent(
                    $bestLink['link'],
                    $bestLink['type'],
                    $item['title'],
                    $clientType,
                    $globalDownloadDir,
                    $deepDirs,
                    $sMTPNotifications,
                    $enableScript,
                    $alsoSaveTorrentFiles,
                    $alsoSaveDir,
                    $fav,
                    $feedUrl,
                    $configFeeds,
                    $defaultSeedRatio
            );
            if ($response['errorCode'] === 0) {
                $startedDownload = true; // download started successfully
                add_cache($item['title']); //TODO also tie $startedDownload to successful write to cache
                if ($clientType === "folder") {
                    //$itemState = "st_downloaded";
                    $itemState = "st_inCacheNotActive";
                } else {
                    $itemState = "st_downloading";
                }
            } else {
                // leave $itemState alone
                writeToLog("Failed adding torrent " . $bestLink['link'] . "\n", -1);
            }
        } else {
            writeToLog("Unable to find URL for " . $item['title'] . " from feed: " . $fav['Feed'] . "\n", -1);
            $itemState = "st_noURL"; // doesn't do anything except overwrite $itemState = "st_favReady" for future logic; use it to disable buttons
        }
    } else {
        writeToLog("Equiv. in cache; ignoring: " . $item['title'] . "\n", 1); // could be exact or equiv. but we say equiv.
        $itemState = "st_inCache"; // only found in the cache; this state is transient
    }
    return $startedDownload;
}

function parseOneFeed($feed, $update = false) {
    global $config_values;
    // get Cron Interval and calculate cronCacheExpires
    if (isset($config_values['Settings']['Cron Interval']) && (int) $config_values['Settings']['Cron Interval']) {
        $cronInterval = (int) $config_values['Settings']['Cron Interval'];
    } else {
        $cronInterval = 15;
    }
    // update or not
    if (isset($update) && $update !== false) {
        $cacheExpires = ($cronInterval * 60) - 20;
    } else {
        $cacheExpires = ($cronInterval * 120) - 20;
    }
    // enforce maximum cache age
    $maxCacheAge = 3580;
    if ($cacheExpires > $maxCacheAge) {
        $cacheExpires = $maxCacheAge;
    }
    if ($cacheExpires < 0) {
        $cacheExpires = 0;
    }
    $feed_parser = new FeedParserWrapper($feed['Link'], getDownloadCacheDir(), 'M d, H:i', $config_values['Settings']['Time Zone'], $cacheExpires);
    if (!$config_values['Global']['Feeds'][$feed['Link']] = $feed_parser->getParsedData()) {
        writeToLog("Error creating feed parser for " . $feed['Link'] . "\n", -1);
        return false;
    } else {
        $config_values['Global']['Feeds'][$feed['Link']]['URL'] = $feed['Link'];
        return true;
    }
}

function processOneFeed($feed, $idx, $feedName, $feedLink) {
    global $config_values;
    // store some settings in temp variables
    $matchStyle = getArrayValueByKey($config_values['Settings'], 'Match Style');
    $reqEpisInfo = getArrayValueByKey($config_values['Settings'], 'Require Episode Info');
    $onlyNewer = getArrayValueByKey($config_values['Settings'], 'Only Newer');
    $dloadVersions = getArrayValueByKey($config_values['Settings'], 'Download Versions');
    $ignBatches = getArrayValueByKey($config_values['Settings'], 'Ignore Batches');
    $clientType = getArrayValueByKey($config_values['Settings'], 'Client');
    $globalDownloadDir = getArrayValueByKey($config_values['Settings'], 'Download Dir');
    $deepDirs = getArrayValueByKey($config_values['Settings'], 'Deep Directories');
    $sMTPNotifications = getArrayValueByKey($config_values['Settings'], 'SMTP Notifications');
    $enableScript = getArrayValueByKey($config_values['Settings'], 'Enable Script');
    $alsoSaveTorrentFiles = getArrayValueByKey($config_values['Settings'], 'Also Save Torrent Files');
    $alsoSaveDir = getArrayValueByKey($config_values['Settings'], 'Also Save Dir');
    $configFeeds = $config_values['Feeds'];
    $defaultSeedRatio = getArrayValueByKey($config_values['Settings'], 'Default Seed Ratio');

    writeToLog("Started processing feed: $feedName\n", 2);
    if (isset($feed['feed']) && isset($feed['feed']['entry']) && count($feed['feed']['entry']) > 0) {
        if (isset($config_values['Global']['HTMLOutput'])) {
            if ($config_values['Settings']['Combine Feeds'] == 0) {
                show_feed_list($idx);
            }
            $htmlList = []; // init item list for HTML output
            $alt = 'alt'; // alternating row background for HTML output
        }
        $items = array_reverse($feed['feed']['entry']);
        foreach ($items as $item) { // BEGIN loop through every item in this feed
            if (isset($item['title'])) {
                $item['title'] = simplifyTitle($item['title']); // simplifyTitle() is somehow needed for accurate favorites matching
            } else {
                $item['title'] = "";
            }
            $torHash = "";
            $itemState = "st_notAMatch"; // assign initial state to each item, to be overwritten if a match; $itemState is not used above this function
            if (isset($config_values['Settings']['Enable Super-Favorites']) && $config_values['Settings']['Enable Super-Favorites'] == 1) {
                // compare this item to Super-Favorites
                if (isset($config_values['Super-Favorites'])) {
                    // loop through every Super-Favorite and compare this item to each Super-Favorite
                    foreach ($config_values['Super-Favorites'] as $superfavKey => $superfavValue) { // BEGIN loop through Super-Favorites; $superfavValue is not used
                        if (checkItemMatchesSuperFavorite(
                                        $config_values['Super-Favorites'][$superfavKey],
                                        $item,
                                        $feed['URL'],
                                        $matchStyle,
                                        $reqEpisInfo
                                )
                        ) {
                            // item matches a Super-Favorite, create a Favorite from it
                            $guess = detectMatch($item['title']);
                            addFavoriteFromSuperFavoriteMatch(
                                    $guess['favTitle'], // name (item guessed title)
                                    $guess['favTitle'], // filter (item guessed title)
                                    $config_values['Super-Favorites'][$superfavKey]['Feed'], // feed (same as Super-Favorite)
                                    $config_values['Super-Favorites'][$superfavKey]['Quality'] // quality (same as Super-Favorite)
                            );
                            break;
                        }
                    } // END loop through Super-Favorites
                }
            }
            // compare this item to Favorites
            if (isset($config_values['Favorites'])) {
                // loop through every Favorite and compare this item to each Favorite
                foreach ($config_values['Favorites'] as $favKey => $favValue) { // BEGIN loop through Favorites; $favValue is not used
                    if (checkItemMatchesFavorite(
                                    $itemState,
                                    $config_values['Favorites'][$favKey],
                                    $item,
                                    $feed['URL'],
                                    $matchStyle,
                                    $reqEpisInfo,
                                    $onlyNewer,
                                    $dloadVersions,
                                    $ignBatches
                            )
                    ) {
                        break; // first Favorite to match wins and starts download; $itemState should no longer be st_notAMatch at this point
                    }
                } // END loop through Favorites
                if ($itemState === "st_favReady") {
                    // item matches a Favorite; start the download, even though it might have been downloaded already
                    // IMPORTANT: must use $config_values['Favorites'][$favKey] instead of $favValue or updateFavoriteEpisode() call will not work
                    processMatchedItemDownload(
                            $itemState,
                            $config_values['Favorites'][$favKey],
                            $item,
                            $feedLink,
                            $clientType,
                            $globalDownloadDir,
                            $deepDirs,
                            $sMTPNotifications,
                            $enableScript,
                            $alsoSaveTorrentFiles,
                            $alsoSaveDir,
                            $configFeeds,
                            $defaultSeedRatio
                    );
                }
            }
            // check every item, regardless of whether it matches a Favorite
            // get $torHash after download has started for $htmlList
            $cache_file = getDownloadCacheDir() . '/dl_' . sanitizeFilename($item['title']);
            if (file_exists($cache_file)) {
                if ($clientType !== 'folder') {
                    $torHash = get_torHash($cache_file);
                }
                if ($itemState === 'st_notAMatch') {
                    $itemState = 'st_inCache'; // not a Favorite but is seen in cache--probably a manual download or a deleted Favorite
                }
            }
            // prepare and add item to item list for HTML output
            if (isset($config_values['Global']['HTMLOutput'])) {
                // assemble id using feed index and a sequential number
                if (!isset($rsnr)) {
                    $rsnr = 1;
                } else {
                    $rsnr++;
                }
                if (strlen($rsnr) <= 1) {
                    $rsnr = 0 . $rsnr;
                }
                $id = $idx . $rsnr;
                // append this item to the item list for HTML output
                $htmlList[] = [
                    'item' => $item,
                    'URL' => $feedLink,
                    'feedName' => $feedName,
                    'alt' => $alt,
                    'torHash' => $torHash,
                    'itemState' => $itemState,
                    'id' => $id
                ];
                // toggle alternating row background for HTML output
                if ($alt === 'alt') {
                    $alt = '';
                } else {
                    $alt = 'alt';
                }
            }
        } // END loop through every item in this feed
        if (isset($config_values['Global']['HTMLOutput'])) {
            $htmlList = array_reverse($htmlList, true);
            foreach ($htmlList as $item) {
                show_feed_item($item['item'], $item['URL'], $item['feedName'], $item['alt'], $item['torHash'], $item['itemState'], $item['id']);
            }
            if ($config_values['Settings']['Combine Feeds'] == 0) {
                close_feed_list();
            }
        }
        unset($item);
        writeToLog("Processed feed: $feedName\n", 1);
    } else {
        writeToLog("Empty feed: $feedName\n", 0);
        show_feed_down_header($idx);
    }
}

function process_all_feeds($feeds) {
    // processes all enabled feeds after loadAllFeeds()
    global $config_values;
    if (isset($config_values['Global']['HTMLOutput']) && $config_values['Settings']['Combine Feeds'] == 1) {
        show_feed_list(0);
    }
    setupDownloadCacheDir();
    foreach ($feeds as $key => $feed) {
        if (isset($config_values['Global']['Feeds'][$feed['Link']]) && $feed['enabled'] == 1) {
            processOneFeed($config_values['Global']['Feeds'][$feed['Link']], $key, $feed['Name'], $feed['Link']);
        } else if ($feed['enabled'] != 1) {
            writeToLog("Feed disabled, not processed: " . $feed['Name'] . "\n", 1);
        } else {
            writeToLog("Feed inaccessible, not processed: " . $feed['Name'] . "\n", 1);
        }
    }
    if (isset($config_values['Global']['HTMLOutput']) && $config_values['Settings']['Combine Feeds'] == 1) {
        close_feed_list();
    }
}

function loadAllFeeds($feeds, $update = false) {
    // loads and parses all enabled feeds
    foreach ($feeds as $feed) {
        if (isset($feed['enabled']) && $feed['enabled'] == 1) {
            parseOneFeed($feed, $update);
        } else {
            writeToLog("Feed disabled, not loaded: " . $feed['Name'] . "\n", 2);
        }
    }
}
