<?php

// functions for handling torrent files and Transmission
//function getClientData($recent, $encodeJson = true) {
function getClientData($encodeJson = true) {
    $fields = array('id', 'name', 'errorString', 'hashString', 'uploadRatio', 'percentDone',
        'leftUntilDone', 'downloadDir', 'totalSize', 'addedDate', 'status', 'eta',
        'peersSendingToUs', 'peersGettingFromUs', 'peersConnected', 'seedRatioLimit',
        'recheckProgress', 'rateDownload', 'rateUpload');
    /* if ($recent) { //TODO if converting to boolean, beware of string parameter passing of "true" or "false"
      $request = array('arguments' => array('fields' => $fields, 'ids' => 'recently-active'), 'method' => 'torrent-get');
      } else { */
    $request = array('arguments' => array('fields' => $fields), 'method' => 'torrent-get');
    //}
    $response = transmission_rpc($request);
    if ($encodeJson) {
        return json_encode($response);
    } else {
        return $response;
    }
}

function startTorrent($torHash) {
    $idsArray = explode(',', $torHash); // this is okay because $torHash is a SHA1 hexadecimal number and never has commas
    $request = array('arguments' => array('ids' => $idsArray), 'method' => 'torrent-start');
    $response = transmission_rpc($request);
    return json_encode($response);
}

function stopTorrent($torHash) {
    $idsArray = explode(',', $torHash);
    $request = array('arguments' => array('ids' => $idsArray), 'method' => 'torrent-stop');
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
        $request = array('arguments' => array('delete-local-data' => $toTrash, 'ids' => $idsArray), 'method' => 'torrent-remove');
        $response = transmission_rpc($request);
        return json_encode($response);
    } else {
        return "{\"result\":\"nothing to delete\"}"; // fake error message because Transmission RPC returns success even when there's nothing to delete
    }
}

function auto_del_seeded_torrents() {
    //$response = getClientData(0, false);
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
                    twxaDebug("Auto-del torrent in cache: " . substr($result, 3) . "\n", 1);
                    delTorrent($torrent['hashString'], false, false); // torHash, toTrash, checkCache
                }
            }
        }
        if ($deleted === false) {
            twxaDebug("No torrents eligible for auto-delete\n", 2);
        }
    } else {
        twxaDebug("RPC error in auto-delete: " . print_r($response, true) . "\n", 0);
    }
}

function moveTorrent($location, $torHash) {
    $idsArray = explode(',', $torHash);
    $request1 = array('arguments' => array('fields' => array('leftUntilDone', 'totalSize'), 'ids' => $idsArray), 'method' => 'torrent-get');
    $response1 = transmission_rpc($request1);
    $totalSize = $response1['arguments']['torrents']['0']['totalSize'];
    $leftUntilDone = $response1['arguments']['torrents']['0']['leftUntilDone'];
    if (isset($totalSize) && isset($leftUntilDone) && $totalSize > $leftUntilDone) {
        $move = true;
    } else {
        $move = false;
    }
    $request2 = array('arguments' => array('location' => $location, 'move' => $move, 'ids' => $torHash), 'method' => 'torrent-set-location');
    $response2 = transmission_rpc($request2);
    return json_encode($response2);
}

function transmission_sessionId() {
    global $config_values;
    $sessionIdFile = get_tr_sessionIdFile();
    if (file_exists($sessionIdFile) && !is_writable($sessionIdFile)) {
        $myuid = posix_getuid();
        echo "<div id=\"errorDialog\" class=\"dialog_window\" style=\"display: block\">$sessionIdFile is not writable for uid: $myuid</div>"; //TODO does this errorDialog work?
        twxaDebug("Transmission session ID file: $sessionIdFile is not writable for uid: $myuid\n", -1);
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
        $tr_pass = get_client_passwd();
        $tr_host = $config_values['Settings']['Transmission Host'];
        $tr_port = $config_values['Settings']['Transmission Port'];
        $tr_uri = $config_values['Settings']['Transmission URI'];

        $sid = curl_init();
        $curl_options = array(
            CURLOPT_URL => "http://$tr_host:$tr_port$tr_uri",
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_USERPWD => "$tr_user:$tr_pass"
        );
        getcURLDefaults($curl_options);

        curl_setopt_array($sid, $curl_options);

        $header = curl_exec($sid);
        curl_close($sid);
        $ID = [];
        preg_match("/X-Transmission-Session-Id:\s(\w+)/", $header, $ID);

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
    $sessionIdFile = get_tr_sessionIdFile();
    if (file_exists($sessionIdFile) && !is_writable($sessionIdFile)) { //TODO break this out into a small function
        $myuid = posix_getuid();
        echo "<div id=\"errorDialog\" class=\"dialog_window\" style=\"display: block\">$sessionIdFile is not writable for uid: $myuid</div>"; //TODO does this errorDialog work?
        twxaDebug("Transmission session ID file: $sessionIdFile is not writable for uid: $myuid\n", -1);
        return;
    }

    $tr_user = $config_values['Settings']['Transmission Login'];
    $tr_pass = get_client_passwd();
    $tr_uri = $config_values['Settings']['Transmission URI'];
    $tr_host = $config_values['Settings']['Transmission Host'];
    $tr_port = $config_values['Settings']['Transmission Port'];

    $request = json_encode($request);
    $reqLen = strlen($request);

    $run = 1;
    while ($run) {
        $SessionId = transmission_sessionId();

        $post = curl_init();
        $curl_options = array(
            CURLOPT_URL => "http://$tr_host:$tr_port$tr_uri",
            CURLOPT_USERPWD => "$tr_user:$tr_pass",
            CURLOPT_HTTPHEADER => array(
                "POST $tr_uri HTTP/1.1",
                "Host: $tr_host",
                "X-Transmission-Session-Id: $SessionId",
                'Connection: Close',
                "Content-Length: $reqLen",
                'Content-Type: application/json'
            ),
            CURLOPT_POSTFIELDS => "$request"
        );
        getcURLDefaults($curl_options);
        curl_setopt_array($post, $curl_options);
        $raw = curl_exec($post);
        curl_close($post);
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

function get_deep_dir($dest, $tor_name) {
    global $config_values;
    switch ($config_values['Settings']['Deep Directories']) {
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
            twxaDebug("Deep Directories: Couldn't match $tor_name Reverting to Full\n", 1);
        case 'Title':
            $guess = detectMatch($tor_name);
            if (isset($guess['favTitle'])) {
                $dest = $dest . "/" . ucwords(strtolower($guess['title']));
                break;
            }
            twxaDebug("Deep Directories: Couldn't match $tor_name Reverting to Full\n", 1);
        case 'Full':
        default:
            $dest = $dest . "/" . ucwords(strtolower($tor_name));
            break;
    }
    return $dest;
}

function makeTorrentOrMagnetFilename($ti, $isMagnet) {
    global $config_values;
    // prepare filesystem-safe path
    $filename = trim(sanitizeFilename($ti));
    if ($isMagnet) {
        $extension = ltrim(trim(sanitizeFilename($config_values['Settings']['Magnet Extension'])), ".");
    } else {
        $extension = ltrim(trim(sanitizeFilename($config_values['Settings']['Torrent Extension'])), ".");
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

function transmission_add_torrent($tor, $dest, $ti, $seedRatio) {
    global $config_values;
    // transmission dies with bad folder if it doesn't end in a /
    if (substr($dest, strlen($dest) - 1, 1) != '/') {
        $dest .= '/';
    }

    if (strpos($tor, 'magnet:') === 0) {
        $request1 = array('method' => 'torrent-add', 'arguments' => array('download-dir' => $dest, 'filename' => $tor));
    } else {
        $request1 = array('method' => 'torrent-add', 'arguments' => array('download-dir' => $dest, 'metainfo' => base64_encode($tor)));
    }
    $response1 = transmission_rpc($request1);
    if (isset($response1['result'])) {
        if ($response1['result'] === 'success') {
            if (isset($response1['arguments']['torrent-added'])) {
                $cache = $config_values['Settings']['Cache Dir'] . "/dl_" . sanitizeFilename($ti);
                $torHash = $response1['arguments']['torrent-added']['hashString'];
                if (!isset($torHash) || $torHash === "") {
                    twxaDebug("Empty torrent hash for: $ti\n", 0);
                }
                // write torrent hash to item's cache file
                if (file_put_contents($cache, $torHash) === false) {
                    twxaDebug("Failed writing $torHash into: $cache\n", -1);
                }
                // set seed ratio
                if ($seedRatio >= 0) {
                    $request2 = array(
                        'method' => 'torrent-set',
                        'arguments' => array(
                            'ids' => $torHash,
                            'seedRatioLimit' => $seedRatio,
                            'seedRatioMode' => 1
                        )
                    );
                    $response2 = transmission_rpc($request2);
                    if ($response2['result'] !== 'success') {
                        twxaDebug("Failed setting seed ratio limit for $ti\n", 0);
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

function client_add_torrent($filename, $dest, $ti, $feed = null, &$fav = null, $retried = false) {
    //TODO this function needs major cleanup! Be aware that return value should be an array but Javascript .dlTorrent expects a string
    //global $config_values, $hit, $twxa_version;
    global $config_values, $twxa_version;
    if (strtolower($fav['Filter']) === "any") {
        $any = 1;
    }
    //$hit = 1;
    if (strpos($filename, 'magnet:') === 0) {
        $tor = $filename;
        $magnet = true; // was 1
    } else {
        $magnet = false; // was 0
        $filename = htmlspecialchars_decode($filename);

        // Detect and append cookies from the feed url
        $url = $filename;
        if ($feed && strpos($feed, ':COOKIE:') !== false && strpos($url, ':COOKIE:') === false) {
            $url .= stristr($feed, ':COOKIE:');
        }

        $get = curl_init();
        $response = parseURLForCookies($url);
        if ($response) {
            $url = $response['url'];
            $cookies = $response['cookies'];
        }
        $curlOptions[CURLOPT_URL] = $url;
        if (isset($cookies)) {
            $curlOptions[CURLOPT_COOKIE] = $cookies;
        }
        $curlOptions[CURLOPT_USERAGENT] = "torrentwatch-xa/$twxa_version[0] ($twxa_version[1])";
        getcURLDefaults($curlOptions);
        curl_setopt_array($get, $curlOptions);
        $tor = curl_exec($get);
        curl_close($get);

        if (strncasecmp($tor, 'd8:announce', 11) != 0) { // Check for torrent magic-entry
            //This was not a torrent-file, so it's probably some kind of xml / html.
            if (!$retried) {
                //Try to retrieve a .torrent link from the content.
                $link = find_torrent_link($url, $tor);
                return client_add_torrent($link, $dest, $ti, $feed, $fav, $url); // $url is used as boolean in $retried here but also as value in else below
            } else {
                if (isset($retried)) {
                    $url = $retried;
                }
                $errMsg = "No torrent file link found in $url. Might be a gzipped torrent.";
                twxaDebug("$errMsg\n", -1);
                return $errMsg;
            }
        } // do not add else with return here as it will break adding some torrent files

        if (!$tor) {
            $errMsg = "Couldn't open torrent: $filename";
            twxaDebug("$errMsg\n", -1);
            return $errMsg;
        }
    }

    $tor_info = new BDecode("", $tor);
    if (!($tor_name = $tor_info->{'result'}['info']['name'])) {
        $tor_name = $ti; //TODO do we really need $tor_name in place of $ti?
    }

    if (!isset($dest)) {
        $dest = $config_values['Settings']['Download Dir'];
    }
    if (isset($fav) && $fav['Download Dir'] != 'Default') {
        if (is_dir($fav['Download Dir']) && is_writeable($fav['Download Dir'])) {
            $dest = $fav['Download Dir'];
        } else {
            $dest = $config_values['Settings']['Download Dir'];
        }
    }

    $dest = get_deep_dir(preg_replace('/\/$/', '', $dest), $tor_name);

    $transmissionHost = $config_values['Settings']['Transmission Host'];
    if ($transmissionHost === '127.0.0.1' || $transmissionHost === 'localhost') {
        if (file_exists($dest)) {
            // path exists, is it a file or directory?
            if (!is_dir($dest)) {
                // it's a file--destroy and recreate it as a directory
                $old_umask = umask(0);
                twxaDebug("Attempting to destroy file and recreate as directory: $dest\n", 2);
                unlink($dest);
                mkdir($dest, 0775, true);
                umask($old_umask);
            }
        } else {
            // path doesn't exist, create it as a directory
            $old_umask = umask(0);
            twxaDebug("Attempting to create directory: $dest\n", 2);
            mkdir($dest, 0775, true);
            umask($old_umask);
        }
    }

    foreach ($config_values['Feeds'] as $key => $feedLink) {
        if ($feedLink['Link'] == "$feed") {
            $idx = $key;
        }
    }
    if (is_numeric($fav['seedRatio']) && $fav['seedRatio'] >= 0) {
        $seedRatio = $fav['seedRatio'];
    } else if (is_numeric($config_values['Feeds'][$idx]['seedRatio']) && $config_values['Feeds'][$idx]['seedRatio'] >= 0) {
        $seedRatio = $config_values['Feeds'][$idx]['seedRatio'];
    } else if (is_numeric($config_values['Settings']['Default Seed Ratio'])) {
        $seedRatio = $config_values['Settings']['Default Seed Ratio'];
    } else {
        $seedRatio = -1;
    }

    switch ($config_values['Settings']['Client']) {
        case "Transmission":
            $return1 = transmission_add_torrent($tor, $dest, $ti, getArrayValueByKey($fav, '$seedRatio', $seedRatio));
            break;
        case "folder":
            /* if ($magnet) {
              twxaDebug("Cannot save magnet links to a folder\n", 0);
              } else {
              $return1 = folder_add_torrent($tor, $dest, $tor_name);
              } */
            $return1 = folder_add_torrent($tor, $dest, $tor_name, $magnet);
            break;
        default:
            twxaDebug("Invalid torrent client: " . $config_values['Settings']['Client'] . "\n", -1);
            exit(1); //TODO deal with this in revamping return of this function
    }
    if ($return1['errorCode'] === 0) {
        add_history($tor_name);
        twxaDebug("Started: $tor_name in $dest\n", 1);
        if (isset($fav)) {
            if ($config_values['Settings']['SMTP Notifications']) {
                $subject = "\"$tor_name\" started downloading";
                $msg = "torrentwatch-xa started downloading \"$tor_name\"";
                notifyByEmail($msg, $subject);
            }
            if ($config_values['Settings']['Enable Script']) {
                runScript('favstart', $ti);
            }
            if (!isset($any) || !$any) {
                if (updateFavoriteEpisode($fav, $ti)) {
                    twxaDebug("Updated Favorite: $ti\n", 2);
                } else {
                    twxaDebug("Failed to update Favorite: $ti\n", 2);
                }
            }
        } else if ($config_values['Settings']['Enable Script']) {
            runScript('nonfavstart', $ti);
        }
        if ($config_values['Settings']['Client'] !== "folder" &&
                $config_values['Settings']['Save Torrents']) {
            /* if ($magnet) {
              twxaDebug("Cannot save magnet links to a folder\n", 0);
              } else {
              $return2 = folder_add_torrent($tor, $config_values['Settings']['Save Torrents Dir'], $tor_name);
              } */
            twxaDebug("Also saving to file: " . makeTorrentOrMagnetFilename($tor_name, $magnet) . "\n", 2);
            if (isset($fav) && $fav['Also Save Dir'] != 'Default') {
                $alsodest = $fav['Also Save Dir'];
            } else {
                $alsodest = $config_values['Settings']['Save Torrents Dir'];
            }
            $return2 = folder_add_torrent($tor, $alsodest, $tor_name, $magnet);
            if ($return2['errorCode'] !== 0) {
                return "Error: " . $return2['errorMessage']; //TODO deal with this in revamping return of this function
            }
        }
        return "Success"; //TODO deal with this in revamping return of this function
    } else {
        twxaDebug("Failed starting: $tor_name : " . $return1['errorMessage'] . "\n", -1);
        $msg = "torrentwatch-xa tried to start \"$tor_name\" but failed with the following error:\n\n";
        $msg .= $return1['errorMessage'] . "\n";
        if ($config_values['Settings']['SMTP Notifications']) {
            $subject = "Failed starting: \"$tor_name\"";
            notifyByEmail($msg, $subject);
        }
        if ($config_values['Settings']['Enable Script']) {
            runScript('error', $ti, $msg);
        }
        return "Error: " . $return1['errorMessage']; //TODO deal with this in revamping return of this function
    }
}

function find_torrent_link($url_old, $content) {
    $url = "";
    $matches = [];
    if (preg_match('/["\']([^\'"]*?\.torrent[^\'"]*?)["\']/', $content, $matches)) {
        $url = $matches[1];
        if (stripos($url, 'http://') === false && stripos($url, 'https://') === false) {
            if (strpos($url, '/') === 0) {
                $url = dirname($url_old) . $url;
            } else {
                $url = dirname($url_old) . '/' . $url;
            }
        }
    } else if (preg_match_all('/href=["\']([^#].+?)["\']/', $content, $matches)) {
        foreach ($matches[1] as $match) {
            if (stripos($match, 'http://') === false && stripos($match, 'https://') === false) {
                if (strpos($match, '/') === 0) {
                    $match = dirname($url_old) . $match;
                } else {
                    $match = dirname($url_old) . '/' . $match;
                }
            }
            if (stripos($match, 'w3.org') !== false) {
                break;
            }
            $opts = array('http' => array('timeout' => 10));
            stream_context_get_default($opts);
            $headers = get_headers($match, 1);
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
                $url = $match;
            }
        }
    }
    return $url;
}
