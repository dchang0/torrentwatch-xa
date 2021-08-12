<?php

// functions for handling torrent files, magnet links, and Transmission
// NOTE: There is a difference between a .torrent file and a torrent.
function getClientData($encodeJson = true) {
    $fields = ['id', 'name', 'errorString', 'hashString', 'uploadRatio', 'percentDone',
        'leftUntilDone', 'downloadDir', 'totalSize', 'addedDate', 'status', 'eta',
        'peersSendingToUs', 'peersGettingFromUs', 'peersConnected', 'seedRatioLimit',
        'recheckProgress', 'rateDownload', 'rateUpload'];
    $request = ['arguments' => ['fields' => $fields], 'method' => 'torrent-get'];
    $response = transmission_rpc($request);
    if ($encodeJson) {
        return json_encode($response);
    } else {
        return $response;
    }
}

function startTorrent($torHash) {
    $idsArray = explode(',', $torHash); // this is okay because $torHash is a SHA1 hexadecimal number and never has commas
    $request = ['arguments' => ['ids' => $idsArray], 'method' => 'torrent-start'];
    $response = transmission_rpc($request);
    return json_encode($response);
}

function stopTorrent($torHash) {
    $idsArray = explode(',', $torHash);
    $request = ['arguments' => ['ids' => $idsArray], 'method' => 'torrent-stop'];
    $response = transmission_rpc($request);
    return json_encode($response);
}

function delTorrent($torHash, $toTrash = false, $checkCache = false) {
    $idsArray = explode(',', $torHash);
    if ($checkCache === true || $checkCache === "true") { // some parameter passing causes $checkCache to be a string instead of a boolean
        $deleteHashes = [];
        foreach ($idsArray as $hash) {
            if (check_cache_for_torHash($hash) !== "") {
                // torrent hash is found in download cache, append it to $deleteHashes
                $deleteHashes[] = $hash;
            }
        }
        $idsArray = $deleteHashes;
    }
    if (count($idsArray) >= 1) { //TODO maybe test || $checkCache === false too
        $request = ['arguments' => ['delete-local-data' => $toTrash, 'ids' => $idsArray], 'method' => 'torrent-remove'];
        $response = transmission_rpc($request);
        return json_encode($response);
    } else {
        return "{\"result\":\"nothing to delete\"}"; // fake error message because Transmission RPC returns success even when there's nothing to delete
    }
}

function auto_del_seeded_torrents() {
    $response = getClientData(false); // request torrents to look for deletable torrents; 0 was chosen by watching both 0 and 1 output
    if ($response['result'] === "success") {
        $torrents = $response['arguments']['torrents'];
        $deleted = false;
        foreach ($torrents as $torrent) {
            if (
                    $torrent['seedRatioLimit'] != -1 && // probably redundant with $torrent['status'] == 0
                    $torrent['status'] == 0 &&
                    $torrent['uploadRatio'] >= $torrent['seedRatioLimit'] &&
                    $torrent['leftUntilDone'] == 0
            ) {
                // torrent is downloaded and completely seeded
                $result = check_cache_for_torHash($torrent['hashString']);
                if ($result !== "") {
                    $deleted = true;
                    writeToLog("Auto-del torrent in cache: " . substr($result, 3) . "\n", 1);
                    delTorrent($torrent['hashString'], false, false); // torHash, toTrash, checkCache
                }
            }
        }
        if ($deleted === false) {
            writeToLog("No torrents eligible for auto-delete\n", 2);
        }
    } else {
        writeToLog("RPC error in auto-delete: " . print_r($response, true) . "\n", 0);
    }
}

function moveTorrent($location, $torHash) {
    $idsArray = explode(',', $torHash);
    $request1 = ['arguments' => ['fields' => ['leftUntilDone', 'totalSize'], 'ids' => $idsArray], 'method' => 'torrent-get'];
    $response1 = transmission_rpc($request1);
    $totalSize = $response1['arguments']['torrents']['0']['totalSize'];
    $leftUntilDone = $response1['arguments']['torrents']['0']['leftUntilDone'];
    if (isset($totalSize) && isset($leftUntilDone) && $totalSize > $leftUntilDone) {
        $move = true;
    } else {
        $move = false;
    }
    $request2 = ['arguments' => ['location' => $location, 'move' => $move, 'ids' => $torHash], 'method' => 'torrent-set-location'];
    $response2 = transmission_rpc($request2);
    return json_encode($response2);
}

function transmission_sessionId() {
    global $config_values;
    $sessionIdFile = getTransmissionSessionIdFile();
    if (file_exists($sessionIdFile) && !is_writable($sessionIdFile)) {
        $myuid = posix_getuid();
        echo "<div id=\"errorDialog\" class=\"dialog_window\" style=\"display: block\">$sessionIdFile is not writable for uid: $myuid</div>"; //TODO does this errorDialog work? Replace it with outputErrorDialog()
        writeToLog("Transmission session ID file: $sessionIdFile is not writable for uid: $myuid\n", -1);
        return;
    }

    if (file_exists($sessionIdFile)) {
        if (filesize($sessionIdFile) > 0) {
            $handle = fopen($sessionIdFile, 'r');
            $sessionId = trim(fread($handle, filesize($sessionIdFile)));
        } else {
            unlink($sessionIdFile);
        }
    } else {
        $tr_user = $config_values['Settings']['Transmission Login'];
        $tr_pass = get_client_passwd($config_values['Settings']['Transmission Password']);
        $tr_host = $config_values['Settings']['Transmission Host'];
        $tr_port = $config_values['Settings']['Transmission Port'];

        $curlOptions = [
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_USERPWD => "$tr_user:$tr_pass"
        ];
        $ID = [];
        preg_match("/X-Transmission-Session-Id:\s(\w+)/", getCurl("http://$tr_host:$tr_port" . getTransmissionrPCPath(), $curlOptions), $ID);

        if (isset($ID[1])) {
            $handle = fopen($sessionIdFile, "w");
            fwrite($handle, $ID[1]);
            fclose($handle);
            $sessionId = $ID[1];
        }
    }
    if (isset($sessionId)) {
        return $sessionId;
    }
}

function transmission_rpc($request) {
    global $config_values;
    $sessionIdFile = getTransmissionSessionIdFile();
    if (file_exists($sessionIdFile) && !is_writable($sessionIdFile)) { //TODO break this out into a small function
        $myuid = posix_getuid();
        echo "<div id=\"errorDialog\" class=\"dialog_window\" style=\"display: block\">$sessionIdFile is not writable for uid: $myuid</div>"; //TODO does this errorDialog work?  Replace it with outputErrorDialog()
        writeToLog("Transmission session ID file: $sessionIdFile is not writable for uid: $myuid\n", -1);
        return;
    }

    $tr_user = $config_values['Settings']['Transmission Login'];
    $tr_pass = get_client_passwd($config_values['Settings']['Transmission Password']);
    $tr_host = $config_values['Settings']['Transmission Host'];
    $tr_port = $config_values['Settings']['Transmission Port'];

    $requestjSON = json_encode($request);
    $reqLen = strlen($requestjSON);

    $run = 1;
    while ($run) {
        $SessionId = transmission_sessionId();
        $curlOptions = [
            CURLOPT_USERPWD => "$tr_user:$tr_pass",
            CURLOPT_HTTPHEADER => [
                "POST " . getTransmissionrPCPath() . " HTTP/1.1",
                "Host: $tr_host",
                "X-Transmission-Session-Id: $SessionId",
                'Connection: Close',
                "Content-Length: $reqLen",
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => "$requestjSON"
        ];
        $raw = getCurl("http://$tr_host:$tr_port" . getTransmissionrPCPath(), $curlOptions);
        if (preg_match('/409:? Conflict/', $raw)) {
            if (file_exists($sessionIdFile)) {
                unlink($sessionIdFile);
            }
        } else {
            $run = 0;
        }
    }
    return json_decode($raw, true);
}

function get_deep_dir($dest, $tor_name, $deepDirs = '0') {
    switch ($deepDirs) {
        case '0':
            break;
        case 'Title_Season':
            $guess = detectMatch($tor_name);
            if (isset($guess['favTitle'])) {
                switch ($guess['numberSequence']) {
                    case 1: // Video: Season x Episode or FULL, Print Media: Volume x Chapter or FULL, Audio: Season x Episode or FULL
                    case 4: // Video: Season x Volume (x Episode), Print Media: N/A, Audio: N/A
                        $dest = $dest . "/" . ucwords(strtolower($guess['favTitle'])) . "/Season " . $guess['seasBatEnd'];
                        break;
                    case 2:
                        // Video: Date, Print Media: Date, Audio: Date (all these get Season = 0)
                        $year = [];
                        preg_match('/^(\d{4})\d{4}$/', $guess['episBatEnd'], $year);
                        $dest = $dest . "/" . ucwords(strtolower($guess['favTitle'])) . "/" . $year[1];
                        break;
                    case 8:
                        // Video: Preview, Print Media: N/A, Audio: Opening songs
                        switch ($guess['mediaType']) {
                            case 1:
                                $dest = $dest . "/" . ucwords(strtolower($guess['favTitle'])) . "/Preview";
                                break;
                            case 2:
                                $dest = $dest . "/" . ucwords(strtolower($guess['favTitle'])) . "/OP";
                                break;
                        }
                        break;
                    case 16:
                        // Video: Special, Print Media: N/A, Audio: Ending songs
                        switch ($guess['mediaType']) {
                            case 1:
                                $dest = $dest . "/" . ucwords(strtolower($guess['favTitle'])) . "/Special";
                                break;
                            case 2:
                                $dest = $dest . "/" . ucwords(strtolower($guess['favTitle'])) . "/ED";
                                break;
                        }
                        break;
                    case 32:
                        // Video: OVA episode sequence, Print Media: N/A, Audio: Character songs
                        switch ($guess['mediaType']) {
                            case 1:
                                $dest = $dest . "/" . ucwords(strtolower($guess['favTitle'])) . "/OVA";
                                break;
                            case 2:
                                $dest = $dest . "/" . ucwords(strtolower($guess['favTitle'])) . "/CharSongs";
                                break;
                        }
                        break;
                    case 64:
                        // Video: Movie sequence (Season = 0), Print Media: N/A, Audio: OST
                        switch ($guess['mediaType']) {
                            case 1:
                                $dest = $dest . "/" . ucwords(strtolower($guess['favTitle'])) . "/Movie";
                                break;
                            case 2:
                                $dest = $dest . "/" . ucwords(strtolower($guess['favTitle'])) . "/OST";
                                break;
                        }
                        break;
                    case 128:
                        // Video: (Season x) Volume x Disc/Part sequence, Print Media: N/A, Audio: N/A
                        $dest = $dest . "/" . ucwords(strtolower($guess['favTitle'])) . "/Set";
                        break;
                    default:
                        $dest = $dest . "/" . ucwords(strtolower($guess['favTitle']));
                }
                break;
            }
            writeToLog("Deep Directories: Couldn't match $tor_name Reverting to Full\n", 1);
        case 'Title':
            $guess = detectMatch($tor_name);
            if (isset($guess['favTitle'])) {
                $dest = $dest . "/" . ucwords(strtolower($guess['favTitle']));
                break;
            }
            writeToLog("Deep Directories: Couldn't match $tor_name Reverting to Full\n", 1);
    }
    return $dest;
}

function makeTorrentOrMagnetFilename($ti, $isMagnet) {
    // prepare filesystem-safe path
    $filename = trim(sanitizeFilename($ti));
    if ($isMagnet) {
        $extension = ltrim(trim(sanitizeFilename(getMagnetFileExtension())), ".");
    } else {
        $extension = ltrim(trim(sanitizeFilename(getTorrentFileExtension())), ".");
    }
    if ($extension !== "") {
        return "$filename.$extension";
    } else {
        return "$filename";
    }
}

function folder_add_torrent($tor, $dest, $ti, $isMagnet = false) {
    if (is_dir($dest) && is_writeable($dest)) {
        $fullFilename = makeTorrentOrMagnetFilename($ti, $isMagnet);
        if ($fullFilename !== "") {
            $fullPath = "$dest/$fullFilename";
            if (!file_exists($fullPath)) {
                // save it
                $return = file_put_contents($fullPath, $tor);
                if ($return === false) {
                    return [
                        'errorCode' => 1,
                        'errorMessage' => "Failed to write: $fullPath"
                    ];
                } else {
                    return [
                        'errorCode' => 0,
                        'errorMessage' => "Successfully saved torrent: $ti"
                    ];
                }
            } else {
                return [
                    'errorCode' => 0, // ordinarily should be an error, but why warn when we have the file already?
                    'errorMessage' => "File already exists, skipping: $fullPath"
                ];
            }
        } else {
            return [
                'errorCode' => 1,
                'errorMessage' => "No filename to save: $ti"
            ];
        }
    } else {
        return [
            'errorCode' => 1,
            'errorMessage' => "Directory inaccessible: $dest"
        ];
    }
}

function folderAddTorrent($tor, $dest, $ti, $linkType) {
    if (is_dir($dest) && is_writeable($dest)) {
        $fullFilename = makeTorrentOrMagnetFilename($ti, ($linkType === 'magnet'));
        if ($fullFilename !== "") {
            $fullPath = "$dest/$fullFilename";
            if (!file_exists($fullPath)) {
                // save it
                $return = file_put_contents($fullPath, $tor);
                if ($return === false) {
                    return [
                        'errorCode' => 1,
                        'errorMessage' => "Failed to write: $fullPath"
                    ];
                } else {
                    return [
                        'errorCode' => 0,
                        'errorMessage' => "Successfully saved torrent: $ti"
                    ];
                }
            } else {
                return [
                    'errorCode' => 0, // ordinarily should be an error, but why warn when we have the file already?
                    'errorMessage' => "File already exists, skipping: $fullPath"
                ];
            }
        } else {
            return [
                'errorCode' => 1,
                'errorMessage' => "No filename to save: $ti"
            ];
        }
    } else {
        return [
            'errorCode' => 1,
            'errorMessage' => "Directory inaccessible: $dest"
        ];
    }
}

function transmission_add_torrent($tor, $dest, $ti, $seedRatio) {
    // transmission dies with bad folder if it doesn't end in a /
    if (substr($dest, strlen($dest) - 1, 1) != '/') {
        $dest .= '/';
    }
    if (strpos($tor, 'magnet:') === 0) {
        $request1 = ['method' => 'torrent-add', 'arguments' => ['download-dir' => $dest, 'filename' => $tor]];
    } else {
        $request1 = ['method' => 'torrent-add', 'arguments' => ['download-dir' => $dest, 'metainfo' => base64_encode($tor)]];
    }
    $response1 = transmission_rpc($request1);
    if (isset($response1['result'])) {
        if ($response1['result'] === 'success') {
            if (isset($response1['arguments']['torrent-added'])) {
                $cache = getDownloadCacheDir() . "/dl_" . sanitizeFilename($ti);
                $torHash = $response1['arguments']['torrent-added']['hashString'];
                if (!isset($torHash) || $torHash === "") {
                    writeToLog("Empty torrent hash for: $ti\n", 0);
                }
                // write torrent hash to item's cache file
                if (file_put_contents($cache, $torHash) === false) {
                    writeToLog("Failed writing $torHash into: $cache\n", -1);
                }
                // set seed ratio
                if ($seedRatio >= 0) {
                    $request2 = [
                        'method' => 'torrent-set',
                        'arguments' => [
                            'ids' => $torHash,
                            'seedRatioLimit' => $seedRatio,
                            'seedRatioMode' => 1
                        ]
                    ];
                    $response2 = transmission_rpc($request2);
                    if ($response2['result'] !== 'success') {
                        writeToLog("Failed setting seed ratio limit for $ti\n", 0);
                    }
                }
                return [
                    'errorCode' => 0,
                    'errorMessage' => 'Successfully added torrent'
                ];
            } else if (isset($response1['arguments']['torrent-duplicate'])) {
                return [
                    'errorCode' => 1,
                    'errorMessage' => 'Torrent already exists, ignoring'
                ];
            } else {
                // undocumented situation where result is success but neither torrent-added nor torrent-duplicate exists
                return [
                    'errorCode' => 1,
                    'errorMessage' => "RPC Error adding torrent succeeded: " . print_r($response1, true)
                ];
            }
        } else {
            // result is not success, should be an error string according to spec
            return [
                'errorCode' => 2,
                'errorMessage' => "RPC Error adding torrent failed: " . print_r($response1, true)
            ];
        }
    } else {
        // no response at all
        return [
            'errorCode' => 3,
            'errorMessage' => "Failure connecting to Transmission"
        ];
    }
}

function getDestinationPath($clientType, $globalDownloadDir, $deepDirs, $fav, $torName) {
    // figure out the destination for the torrent download
    $dest = $globalDownloadDir;
    //TODO if $fav is null, then loop through the Favorites to see if the title matches a Favorite and get the Favorite's Download Dir
    if (isset($fav['Download Dir']) && $fav['Download Dir'] !== '') {
        if (
                ($clientType === "folder" &&
                is_dir($fav['Download Dir']) &&
                is_writeable($fav['Download Dir'])) ||
                $clientType !== "folder"
        ) {
            $dest = $fav['Download Dir'];
        }
    }
    return get_deep_dir(preg_replace('/\/$/', '', $dest), $torName, $deepDirs);
}

function getFeedSeedRatio($configFeeds, $feedUrl) {
    // get the seed ratio from a specific feed
    // $configFeeds is $config_values['Feeds']
    // this function should really be in twxa_feed.php but since twxa_torrent.php is called first, it must stay here
    $seedRatio = null;
    if (isset($configFeeds) && isset($feedUrl)) {
        $idx = '';
        foreach ($configFeeds as $key => $value) {
            if ($value['Link'] == $feedUrl) {
                $idx = $key;
                break;
            }
        }
        if (isset($configFeeds[$idx]['seedRatio']) && is_numeric($configFeeds[$idx]['seedRatio']) && $configFeeds[$idx]['seedRatio'] >= 0) {
            $seedRatio = $configFeeds[$idx]['seedRatio'];
        }
    }
    return $seedRatio;
}

function clientAddTorrent(
        $link,
        $linkType,
        $ti,
        $clientType,
        $globalDownloadDir,
        $deepDirs,
        $sMTPNotifications,
        $enableScript,
        $alsoSaveTorrentFiles,
        $alsoSaveDir,
        &$fav = null,
        $feed = null,
        $configFeeds = null,
        $defaultSeedRatio = -1
) {
    // adds a single torrent to the specified client
    if (isset($link) && $link !== '') {
        $torrentFile = "";
        if ($linkType === 'magnet') {
            // it's a magnet link; do nothing for now
        } else if ($linkType === 'torrent' || $linkType === 'torrent.gz') {
            // download the .torrent or .torrent.gz file
            $tempUrl = htmlspecialchars_decode($link);
            // transfer a cookie from the feed URL to the item URL if the feed URL has a cookie and the item URL does not
            if ($feed && strpos($feed, ':COOKIE:') !== false && strpos($tempUrl, ':COOKIE:') === false) {
                $tempUrl .= stristr($feed, ':COOKIE:');
            }
            $tempParsedCookies = parseURLForCookies($tempUrl);
            if (isset($tempParsedCookies['url'])) {
                $tempUrl = $tempParsedCookies['url'];
            }
            $curlOptions[CURLOPT_URL] = $tempUrl;
            if (isset($tempParsedCookies['cookies'])) {
                $curlOptions[CURLOPT_COOKIE] = $tempParsedCookies['cookies'];
            }
            $torrentFile = getCurl(null, $curlOptions);
            if ($torrentFile !== false) {
                if ($linkType === 'torrent.gz') {
                    // gunzip the .torrent.gz file into $torrentFile
                    $torrentFile = gzdecode($torrentFile);
                    $linkType = 'torrent';
                }
                // check $torrentFile for torrent magic entry
                if (strncasecmp($torrentFile, 'd8:announce', 11) !== 0) {
                    // not a torrent file; try to salvage the download
                    // search for a torrent info hash in the URL itself
                    $mat = [];
                    if (preg_match('/([a-fA-F0-9]{40})/', $link, $mat) === 1) {
                        writeToLog("Using torrent hash found in the URL: " . $mat[1] . "\n", 1);
                        $link = "magnet:?xt=urn:btih:$mat[1]";
                        $linkType = 'magnet';
                    } else {
                        //TODO search through the retrieved content itself using detectahrefsInString()
                        writeToLog("Failed to retrieve .torrent file from: $link\n", 0);
                        $linkType = 'unknown';
                    }
                } else {
                    // it's a valid .torrent file; do nothing for now
                }
            }
        }
        // should have valid .torrent file or magnet: link by now; start the torrent download
        if ($linkType === 'torrent' || $linkType === 'magnet') {
            // figure out the torrent download filename
            $torName = $ti;
            if ($linkType === 'torrent') { // no need to check if $torrentFile is a valid torrent file
                // if it's a torrent file, get the torrent name from within the file
                $torInfo = new BDecode("", $torrentFile);
                if (isset($torInfo->{'result'}['info']['name']) && $torInfo->{'result'}['info']['name'] !== '') {
                    $torName = $torInfo->{'result'}['info']['name'];
                }
            }
            // figure out the full download path
            $dest = getDestinationPath($clientType, $globalDownloadDir, $deepDirs, $fav, $torName);
            // client can be 'Transmission' or 'Save Torrent File or Magnet Link In Folder'
            if ($clientType === 'folder') {
                switch ($linkType) {
                    case 'magnet':
                        $addResult = folderAddTorrent($link, $dest, $torName, $linkType);
                        break;
                    case 'torrent':
                    default:
                        $addResult = folderAddTorrent($torrentFile, $dest, $torName, $linkType);
                }
            } else {
                // get the seed ratio
                if (isset($fav) && isset($fav['seedRatio']) && is_numeric($fav['seedRatio']) && $fav['seedRatio'] >= 0) {
                    $seedRatio = $fav['seedRatio'];
                } else {
                    $feedSeedRatio = getFeedSeedRatio($configFeeds, $feed);
                    if (is_numeric($feedSeedRatio) && $feedSeedRatio >= 0) {
                        $seedRatio = $feedSeedRatio;
                    } else if (is_numeric($defaultSeedRatio)) {
                        $seedRatio = $defaultSeedRatio;
                    } else {
                        $seedRatio = -1;
                    }
                }
                switch ($linkType) {
                    case 'magnet':
                        $addResult = transmission_add_torrent($link, $dest, $ti, $seedRatio);
                        break;
                    case 'torrent':
                    default:
                        $addResult = transmission_add_torrent($torrentFile, $dest, $ti, $seedRatio);
                }
            }
            // if succeeded, update Favorite if Filter is not 'any', then run Triggers
            if ($addResult['errorCode'] === 0) {
                add_history($torName);
                writeToLog("Started: $torName in $dest\n", 1);
                if (isset($fav)) {
                    if ($sMTPNotifications) {
                        $subject = "\"$torName\" started downloading";
                        $msg = "torrentwatch-xa started downloading \"$torName\"";
                        notifyByEmail($msg, $subject);
                    }
                    if ($enableScript) {
                        runScript('favstart', $ti);
                    }
                    if (isset($fav) && isset($fav['Filter']) && strtolower($fav['Filter']) === "any") {
                        // do nothing
                    } else {
                        if (updateFavoriteEpisode($fav, $ti)) {
                            writeToLog("Updated Favorite: $ti\n", 2);
                        } else {
                            writeToLog("Failed to update Favorite: $ti\n", 2);
                        }
                    }
                } else if ($enableScript) {
                    runScript('nonfavstart', $ti);
                }
                if ($clientType !== "folder" &&
                        $alsoSaveTorrentFiles) {
                    if (
                            isset($fav) &&
                            !empty($fav['Also Save Dir']) &&
                            is_dir($fav['Also Save Dir']) &&
                            is_writeable($fav['Also Save Dir'])
                    ) {
                        $alsodest = $fav['Also Save Dir'];
                    } else {
                        $alsodest = $alsoSaveDir;
                    }
                    writeToLog("Also saving to file: $alsodest/" . makeTorrentOrMagnetFilename($torName, ($linkType === 'magnet')) . "\n", 2);
                    switch ($linkType) {
                        case 'magnet':
                            $alsoSaveResult = folderAddTorrent($link, $alsodest, $torName, $linkType);
                            break;
                        case 'torrent':
                        default:
                            $alsoSaveResult = folderAddTorrent($torrentFile, $alsodest, $torName, $linkType);
                    }
                    if ($alsoSaveResult['errorCode'] !== 0) {
                        return [
                            'errorCode' => 1,
                            'errorMessage' => "Error: " . $alsoSaveResult['errorMessage']
                        ];
                    }
                }
                return [
                    'errorCode' => 0,
                    'errorMessage' => "Success"
                ];
            } else {
                writeToLog("Failed starting: $torName : " . $addResult['errorMessage'] . "\n", -1);
                $msg = "torrentwatch-xa tried to start \"$torName\" but failed with the following error:\n\n";
                $msg .= $addResult['errorMessage'] . "\n";
                if ($sMTPNotifications) {
                    $subject = "Failed starting: \"$torName\"";
                    notifyByEmail($msg, $subject);
                }
                if ($enableScript) {
                    runScript('error', $ti, $msg);
                }
                return [
                    'errorCode' => 2,
                    'errorMessage' => "Error: " . $addResult['errorMessage']
                ];
            }
        } else {
            writeToLog("No valid .torrent file or magnet: link at: $link\n", 0);
            return [
                'errorCode' => 2,
                'errorMessage' => "Error: No valid .torrent file or magnet: link at: $link"
            ];
        }
    } else {
        writeToLog("Empty link, skipping download: $link\n", 0);
        return [
            'errorCode' => 2,
            'errorMessage' => "Error: Empty link, skipping download: $link"
        ];
    }
}
