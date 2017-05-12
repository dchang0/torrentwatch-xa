<?php

// cache and history functions

function add_history($ti) {
    global $config_values;
    if (file_exists($config_values['Settings']['History'])) {
        $history = unserialize(file_get_contents($config_values['Settings']['History']));
    }
    $history[] = array('Title' => $ti, 'Date' => date("Y.m.d H:i"));
    file_put_contents($config_values['Settings']['History'], serialize($history));
}

function setup_cache() {
    global $config_values;
    if (isset($config_values['Settings']['Cache Dir'])) {
        twxaDebug("Enabling cache in: " . $config_values['Settings']['Cache Dir'] . "\n", 2);
        if (!file_exists($config_values['Settings']['Cache Dir']) ||
                !is_dir($config_values['Settings']['Cache Dir'])) {
            if (!file_exists($config_values['Settings']['Cache Dir'])) {
                mkdir($config_values['Settings']['Cache Dir'], 0775, true);
            }
        }
    }
}

function add_cache($ti) {
    global $config_values;
    if (isset($config_values['Settings']['Cache Dir'])) {
        $cache_file = $config_values['Settings']['Cache Dir'] . '/dl_' . filename_encode($ti);
        touch($cache_file);
        return($cache_file);
    }
}

function clear_cache_by_feed_type($file) {
    global $config_values;
    $fileglob = $config_values['Settings']['Cache Dir'] . '/' . $file;
    twxaDebug("Clearing $fileglob\n", 2);
    foreach (glob($fileglob) as $fn) {
        twxaDebug("Removing $fn\n", 2);
        unlink($fn);
    }
}

function clear_cache_by_cache_type() {
    if (isset($_GET['type'])) {
        switch ($_GET['type']) {
            case 'feeds':
                clear_cache_by_feed_type("rsscache_*");
                clear_cache_by_feed_type("atomcache_*");
                break;
            case 'torrents':
                clear_cache_by_feed_type("dl_*");
                break;
            case 'all':
                clear_cache_by_feed_type("dl_*");
                clear_cache_by_feed_type("rsscache_*");
                clear_cache_by_feed_type("atomcache_*");
        }
    }
}

function get_torHash($cache_file) {
    $handle = fopen($cache_file, "r");
    if (filesize($cache_file)) {
        $torHash = fread($handle, filesize($cache_file));
        return $torHash;
    }
}

function check_cache_episode($ti) {
    // attempts to find previous downloads that have the same parsed title but different episode numbering styles
    global $config_values;
    $guess = detectMatch($ti);
    if ($guess['favTitle'] === "") {
        twxaDebug("Unable to guess a favoriteTitle for $ti\n", 0);
        return true; // do download
    }
    $handle = opendir($config_values['Settings']['Cache Dir']);
    if ($handle !== false) {
        while (false !== ($file = readdir($handle))) {
            // loop through each cache file in the Cache Directory
            if (substr($file, 0, 3) !== "dl_") {
                continue;
            }
            // check for a match by parsed title
            if (preg_replace('/[. ]/', '_', substr($file, 3, strlen($guess['favTitle']))) !== preg_replace('/[. ]/', '_', $guess['favTitle'])) {
                continue;
            }
            // if match by title, check for a match by episode
            $cacheguess = detectMatch(substr($file, 3)); // ignores first 3 characters, 'dl_'
            if ($cacheguess['numberSequence'] > 0 &&
                    $guess['numberSequence'] === $cacheguess['numberSequence'] &&
                    $guess['seasBatEnd'] === $cacheguess['seasBatEnd'] &&
                    $guess['episBatEnd'] === $cacheguess['episBatEnd']) { //TODO add in better logic so that an episode in a middle of a batch is counted
                if ($guess['itemVersion'] > $cacheguess['itemVersion']) {
                    if ($config_values['Settings']['Download Versions']) {
                        return true; // difference in item version, do download
                    } else {
                        twxaDebug("Older version in cache: ignoring newer: $ti (" . $guess['episode'] . "v" . $guess['version'] . ")\n", 2);
                        return false; // title is found in cache, version is newer, Download Versions is off, so don't download
                    }
                } else {
                    twxaDebug("Equiv. in cache: ignoring: $ti (" . $guess['episode'] . "v" . $guess['version'] . ")\n", 2);
                    return false; // title and same version is found in cache, don't download
                }
            }
        }
    } else {
        twxaDebug("Unable to open Cache Directory: " . $config_values['Settings']['Cache Dir'] . "\n", -1);
    }
    return true; // do download
}

function check_cache($ti) {
    global $config_values;
    if (isset($config_values['Settings']['Cache Dir'])) {
        $cache_file = $config_values['Settings']['Cache Dir'] . '/dl_' . filename_encode($ti);
        if (!file_exists($cache_file)) {
            return check_cache_episode($ti);
        } else {
            return false;
        }
    } else {
        return true; // cache is disabled, always download
    }
}
