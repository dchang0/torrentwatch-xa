<?php

/*
 * tor_client.php
 * client specific functions
 */

function transmission_sessionId() {
    global $config_values;
    $sessionIdFile = get_tr_sessionIdFile();
    if (file_exists($sessionIdFile) && !is_writable($sessionIdFile)) {
        $myuid = posix_getuid();
        echo "<div id=\"errorDialog\" class=\"dialog_window\" style=\"display: block\">$sessionIdFile is not writable for uid: $myuid</div>";
        twxa_debug("Transmission Session ID File: $sessionIdFile is not writable for uid: $myuid\n", -1);
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
        $tr_uri = $config_values['Settings']['Transmission URI']; //TODO what to do if this is blank and not /transmission/rpc ?

        $sid = curl_init();
        $curl_options = array(CURLOPT_URL => "http://$tr_host:$tr_port$tr_uri",
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_USERPWD => "$tr_user:$tr_pass"
        );
        get_curl_defaults($curl_options);

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
        echo "<div id=\"errorDialog\" class=\"dialog_window\" style=\"display: block\">$sessionIdFile is not writable for uid: $myuid</div>";
        twxa_debug("Transmission Session ID File: $sessionIdFile is not writable for uid: $myuid\n", -1);
        return;
    }

    $tr_user = $config_values['Settings']['Transmission Login'];
    $tr_pass = get_client_passwd();
    $tr_uri = $config_values['Settings']['Transmission URI'];
    $tr_host = $config_values['Settings']['Transmission Host'];
    $tr_port = $config_values['Settings']['Transmission Port'];

    $request = json_encode($request);
    $reqLen = strlen("$request");

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
        get_curl_defaults($curl_options);
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
            if(isset($guess['favTitle'])) {
                switch ($guess['numberSequence']) {
                    case 1:
                    case 4:
                        // season numbering
                        //TODO fix this so that it can handle Volume x Chapter and Volume x Part (case 128)
                        $dest = $dest . "/" . ucwords(strtolower($guess['favTitle'])) . "/Season " . $guess['seasBatEnd'];
                        break;
                    case 2:
                        // date numbering
                        $year = [];
                        preg_match('/^(\d{4})\d{4}$/', $guess['episBatEnd'], $year);
                        $dest = $dest . "/" . ucwords(strtolower($guess['favTitle'])) . "/" . $year[1];
                        break;
                    //TODO handle other numbering styles
                    default:
                        $dest = $dest . "/" . ucwords(strtolower($guess['favTitle']));
                }
                break;
            }
            twxa_debug("Deep Directories: Couldn't match $tor_name Reverting to Full\n", 1);
        case 'Title':
            $guess = detectMatch($tor_name);
            if (isset($guess['favTitle'])) {
                $dest = $dest . "/" . ucwords(strtolower($guess['title']));
                break;
            }
            twxa_debug("Deep Directories: Couldn't match $tor_name Reverting to Full\n", 1);
        case 'Full':
        default:
            $dest = $dest . "/" . ucwords(strtolower($tor_name));
            break;
    }
    return $dest;
}

function folder_add_torrent($tor, $dest, $ti) {
    global $config_values;
    // remove invalid chars
    $ti = strtr($ti, '/', '_');
    // add the directory and extension
    $dest = "$dest/$ti." . $config_values['Settings']['Extension'];
    // save it
    file_put_contents($dest, $tor);
    return 0;
}

function transmission_add_torrent($tor, $dest, $ti, $seedRatio) {
    global $config_values;
    // transmission dies with bad folder if it doesn't end in a /
    if (substr($dest, strlen($dest) - 1, 1) != '/') {
        $dest .= '/';
    }

    if (strpos($tor, 'magnet:') === 0) {
        $request1 = array(
            'method' => 'torrent-add',
            'arguments' => array(
                'download-dir' => $dest,
                'filename' => $tor
            )
        );
    } else {
        $request1 = array(
            'method' => 'torrent-add',
            'arguments' => array(
                'download-dir' => $dest,
                'metainfo' => base64_encode($tor)
            )
        );
    }
    $response1 = transmission_rpc($request1);
    if (isset($response1['result'])) {
        if ($response1['result'] === 'success') {
            if (isset($response1['arguments']['torrent-added'])) {
                $cache = $config_values['Settings']['Cache Dir'] . "/rss_dl_" . filename_encode($ti);
                $torHash = $response1['arguments']['torrent-added']['hashString'];
                // write torrent hash to item's cache file
                // TODO handle if $torHash is empty; cache files should not be empty
                $handle = fopen("$cache", "w");
                fwrite($handle, $torHash);
                fclose($handle);
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
                        twxa_debug("Failed setting seed ratio limit for $ti\n", 0);
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
                    'errorCode' => 2,
                    'errorMessage' => "Transmission RPC Error: " . print_r($response1, true)
                ];
            }
        } else {
            // result is not success, should be an error string according to spec
            return [
                'errorCode' => 2,
                'errorMessage' => "Transmission RPC Error: " . print_r($response1, true)
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
    global $config_values, $hit, $twxa_version;
    if (strtolower($fav['Filter']) === "any") {
        $any = 1;
    }
    $hit = 1; //TODO trace $hit down through this function

    if (strpos($filename, 'magnet:') === 0) {
        $tor = $filename;
        $magnet = 1;
    }

    if (!isset($magnet) || !$magnet) {
        $filename = htmlspecialchars_decode($filename);

        // Detect and append cookies from the feed url
        $url = $filename;

        if ($feed && strpos($feed, ':COOKIE:') !== false && strpos($url, ':COOKIE:') === false) {
            $url .= stristr($feed, ':COOKIE:');
        }

        $get = curl_init();
        $response = check_for_cookies($url);
        if ($response) {
            $url = $response['url'];
            $cookies = $response['cookies'];
        }
        $getOptions[CURLOPT_URL] = $url;
        if (isset($cookies)) {
            $getOptions[CURLOPT_COOKIE] = $cookies;
        }
        $getOptions[CURLOPT_USERAGENT] = "torrentwatch-xa/$twxa_version[0]";
        get_curl_defaults($getOptions);
        curl_setopt_array($get, $getOptions);
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
                twxa_debug("No torrent file found on $url. Might be a gzipped torrent.\n", -1);
                return "No torrent file found on $url. Might be a gzipped torrent.";
            }
        }

        if (!$tor) {
            print '<pre>' . print_r($_GET, true) . '</pre>';
            twxa_debug("Couldn't open torrent: $filename \n", -1);
            return "Error: Couldn't open torrent: $filename";
        }
    }

    $tor_info = new BDecode("", $tor);
    if (!($tor_name = $tor_info->{'result'}['info']['name'])) { //TODO are curly-braces correct for this?
        $tor_name = $ti;
    }

    if (!isset($dest)) {
        $dest = $config_values['Settings']['Download Dir'];
    }
    if (isset($fav) && $fav['Save In'] != 'Default') {
        $dest = $fav['Save In'];
    }

    $dest = get_deep_dir(preg_replace('/\/$/', '', $dest), $tor_name);

    $transmissionHost = $config_values['Settings']['Transmission Host'];
    if ($transmissionHost == '127.0.0.1' || $transmissionHost == 'localhost') { //TODO add other tests to see if transmission is running locally, such as checking the hostname and IPs on this machine
        if (file_exists($dest)) { //TODO add error handling
            // path exists, is it a file or directory?
            if (!is_dir($dest)) {
                // it's a file--destroy and recreate it as a directory
                $old_umask = umask(0);
                twxa_debug("Attempting to destroy file and recreate as directory: $dest\n", 2);
                unlink($dest);
                mkdir($dest, 0777, true);
                umask($old_umask);
            }
        } else {
            // path doesn't exist, create it as a directory
            $old_umask = umask(0);
            twxa_debug("Attempting to create directory: $dest\n", 2);
            mkdir($dest, 0777, true);
            umask($old_umask);
        }
    }

    foreach ($config_values['Feeds'] as $key => $feedLink) {
        if ($feedLink['Link'] == "$feed") {
            $idx = $key;
        }
    }
    if ($config_values['Feeds'][$idx]['seedRatio'] >= 0) {
        $seedRatio = $config_values['Feeds'][$idx]['seedRatio'];
    } else if (is_numeric($config_values['Settings']['Default Seed Ratio'])) {
        $seedRatio = $config_values['Settings']['Default Seed Ratio'];
    } else {
        $seedRatio = -1;
    }

    switch ($config_values['Settings']['Client']) {
        case 'Transmission':
            $return = transmission_add_torrent($tor, $dest, $ti, isset_array_key($fav, '$seedRatio', $seedRatio));
            break;
        case 'folder':
            if ($magnet) {
                twxa_debug("Cannot save magnet links to a folder\n", 0);
            } else {
                $return = folder_add_torrent($tor, $dest, $tor_name);
            }
            break;
        default:
            twxa_debug("Invalid Torrent Client: " . $config_values['Settings']['Client'] . "\n", -1);
            exit(1); //TODO deal with this in revamping return of this function
    }
    if ($return['errorCode'] === 0) {
        add_history($tor_name);
        twxa_debug("Started: $tor_name in $dest\n", 1);
        if (isset($fav)) {
            if ($config_values['Settings']['SMTP Notifications']) {
                $subject = "torrentwatch-xa: $tor_name started downloading.";
                $msg = "torrentwatch-xa started downloading Favorite $tor_name";
                MailNotify($msg, $subject);
            }
            if ($config_values['Settings']['Enable Script']) {
                run_script('favstart', $ti);
            }
            if (!isset($any) || !$any) {
                updateFavoriteEpisode($fav, $ti); //TODO test for success
                twxa_debug("Updated Favorite: $ti\n", 2);
            }
        } else {
            /* if ($config_values['Settings']['SMTP Notifications']) {
              $subject = "torrentwatch-xa: $tor_name started downloading.";
              $msg = "torrentwatch-xa started downloading $tor_name";
              MailNotify($msg, $subject);
              } */
            if ($config_values['Settings']['Enable Script']) {
                run_script('nonfavstart', $ti);
            }
        }
        if ($config_values['Settings']['Save Torrents'])
            file_put_contents("$dest/$tor_name.torrent", $tor);
        return "Success"; //TODO deal with this in revamping return of this function
    } else {
        twxa_debug("Failed starting: $tor_name : " . print_r($return, true). "\n", -1);
        //TODO improve error reporting for this block
        $msg = "torrentwatch-xa tried to start \"$tor_name\". But this failed with the following error:\n\n";
        $msg .= $return['errorMessage'] . "\n";
        if ($config_values['Settings']['SMTP Notifications']) {
            $subject = "torrentwatch-xa: Error while trying to start $tor_name.";
            MailNotify($msg, $subject);
        }
        if ($config_values['Settings']['Enable Script']) {
            run_script('error', $ti, $msg);
        }
        return "Error: " . $return['errorMessage']; //TODO deal with this in revamping return of this function
    }
}

function find_torrent_link($url_old, $content) {
    $url = "";
    $matches = [];
    if ($ret = preg_match('/["\']([^\'"]*?\.torrent[^\'"]*?)["\']/', $content, $matches)) {
        if (isset($ret)) {
            $url = $matches[1];
            if (stripos($url, 'http://') === false && stripos($url, 'https://') === false) {
                if (strpos($url, '/') === 0) {
                    $url = dirname($url_old) . $url;
                } else {
                    $url = dirname($url_old) . '/' . $url;
                }
            }
        }
    } else {
        $ret = preg_match_all('/href=["\']([^#].+?)["\']/', $content, $matches);
        if ($ret) {
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
                $opts = array('http' =>
                    array('timeout' => 10)
                );
                stream_context_get_default($opts);
                $headers = get_headers($match, 1);
                if ((isset($headers['Content-Disposition']) &&
                        preg_match('/filename=.+\.torrent/i', $headers['Content-Disposition'])) ||
                        (isset($headers['Content-Type']) &&
                        $headers['Content-Type'] == 'application/x-bittorrent' )) {
                    $url = $match;
                }
            }
        }
    }
    return $url;
}
