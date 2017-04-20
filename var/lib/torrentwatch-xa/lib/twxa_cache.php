<?php

function setup_cache() {
    //global $config_values, $test_run;
    global $config_values;
    /* if ($test_run) {
      return;
      } */
    if (isset($config_values['Settings']['Cache Dir'])) {
        twxa_debug("Enabling cache in: " . $config_values['Settings']['Cache Dir'] . "\n", 2);
        if (!file_exists($config_values['Settings']['Cache Dir']) ||
                !is_dir($config_values['Settings']['Cache Dir'])) {
            if (!file_exists($config_values['Settings']['Cache Dir'])) {
                mkdir($config_values['Settings']['Cache Dir'], 0775, true);
            }
        }
    }
}

function add_cache($ti) {
    //global $config_values, $test_run;
    global $config_values;
    /* if ($test_run) {
      return;
      } */
    if (isset($config_values['Settings']['Cache Dir'])) {
        $cache_file = $config_values['Settings']['Cache Dir'] . '/rss_dl_' . filename_encode($ti);
        touch($cache_file);
        return($cache_file);
    }
}

function clear_cache_by_feed_type($file) {
    global $config_values;
    $fileglob = $config_values['Settings']['Cache Dir'] . '/' . $file;
    twxa_debug("Clearing $fileglob\n", 2);
    foreach (glob($fileglob) as $fn) {
        twxa_debug("Removing $fn\n", 2);
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
                clear_cache_by_feed_type("rss_dl_*");
                break;
            case 'all':
                clear_cache_by_feed_type("rss_dl_*");
                clear_cache_by_feed_type("rsscache_*");
                clear_cache_by_feed_type("atomcache_*");
        }
    }
}

function check_cache_episode($ti) {
    // attempts to find previous downloads that have the same parsed title but different episode numbering styles
    //global $config_values, $matched;
    global $config_values;
    $guess = detectMatch($ti);
    //twxa_debug("check_cache_episode: detectMatch(): " . print_r($guess, true) . "\n", 2);
    if ($guess['favoriteTitle'] === "") {
        twxa_debug("Unable to guess a favoriteTitle for $ti\n", 0);
        return true; // do download
    }
    if ($handle = opendir($config_values['Settings']['Cache Dir'])) {
        while (false !== ($file = readdir($handle))) {
            // loop through each cache file in the Cache Directory
            if (substr($file, 0, 7) !== "rss_dl_") {
                continue;
            }
            // check for a match by parsed title
            if (preg_replace('/[. ]/', '_', substr($file, 7, strlen($guess['favoriteTitle']))) !== preg_replace('/[. ]/', '_', $guess['favoriteTitle'])) {
                continue;
            }
            //twxa_debug("check_cache_episode: matched by title: " . $guess['favoriteTitle'] . "\n", 2);
            // if match by title, check for a match by episode
            $cacheguess = detectMatch(substr($file, 7)); // ignores first 7 characters, 'rss_dl_'
            if ($cacheguess['episode'] !== "" &&
                    $guess['episode'] === $cacheguess['episode']) {
                if($config_values['Settings']['Download Proper'] && $guess['itemVersion'] === $cacheguess['itemVersion']) {
                    //TODO handle Proper/Repack
                    return true; // difference in item version, do download
                } else {
                twxa_debug("Found in download cache; ignoring: $ti (" . $guess['episode'] . ")\n", 2);
                //$matched = "duplicate";
                return false; // title is found in cache, don't download
                }
            }
            //twxa_debug("Found by title but not a match by episode: $ti (" . $guess['episode'] . ")\n", 2);
            //return true; //TODO remove later
        }
    } else {
        twxa_debug("Unable to open Cache Directory: " . $config_values['Settings']['Cache Dir'] . "\n", -1);
        //return true; //TODO remove later
    }
    //twxa_debug("Never found by title: ". $guess['favoriteTitle'] . "\n", 2);
    return true; // do download
}

function check_cache($ti) {
    //global $config_values, $matched;
    global $config_values;
    if (isset($config_values['Settings']['Cache Dir'])) {
        $cache_file = $config_values['Settings']['Cache Dir'] . '/rss_dl_' . filename_encode($ti);
        if (!file_exists($cache_file)) {
            //if ($config_values['Settings']['Verify Episode']) {
            return check_cache_episode($ti);
            //} else {
            //    return true; // title is not found in cache, do download
            //}
        } else {
            //$matched = "cachehit"; // title is found in cache, don't download
            return false;
        }
    } else {
        return true; // cache is disabled, always download
    }
}
