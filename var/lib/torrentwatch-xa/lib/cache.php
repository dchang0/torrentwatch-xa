<?php

function cache_setup() {
    global $config_values, $test_run;
    if ($test_run)
        return;
    if (isset($config_values['Settings']['Cache Dir'])) {
        twxa_debug("Enabling cache in: " . $config_values['Settings']['Cache Dir'] . "\n", 2);
        if (!file_exists($config_values['Settings']['Cache Dir']) ||
                !is_dir($config_values['Settings']['Cache Dir'])) {
            if (!(file_exists($config_values['Settings']['Cache Dir'])))
                mkdir($config_values['Settings']['Cache Dir'], 0777, TRUE);
        }
    }
}

function add_cache($title) {
    global $config_values, $test_run;
    if ($test_run)
        return;
    if (isset($config_values['Settings']['Cache Dir'])) {
        $cache_file = $config_values['Settings']['Cache Dir'] . '/rss_dl_' . filename_encode($title);
        touch($cache_file);
        return($cache_file);
    }
}

function clear_cache_real($file) {
    global $config_values;
    $fileglob = $config_values['Settings']['Cache Dir'] . '/' . $file;
    twxa_debug("Clearing $fileglob\n", 1);
    foreach (glob($fileglob) as $fn) {
        twxa_debug("Removing $fn\n", 2);
        unlink($fn);
    }
}

function clear_cache() {
    if (isset($_GET['type'])) {
        switch ($_GET['type']) {
            case 'feeds':
                clear_cache_real("rsscache_*");
                clear_cache_real("atomcache_*");
                break;
            case 'torrents':
                clear_cache_real("rss_dl_*");
                break;
            case 'all':
                clear_cache_real("rss_dl_*");
                clear_cache_real("rsscache_*");
                clear_cache_real("atomcache_*");
                break;
        }
    }
}

/*
 * Returns 1 if there is no cache hit(dl now)
 * Returns 0 if there is a hit
 */

function check_cache_episode($title) {
    global $config_values, $matched;
    $guess = detectMatch($title, TRUE);
    if ($guess == False) {
        twxa_debug("Unable to guess for $title\n");
        return 1;
    }
    if ($handle = opendir($config_values['Settings']['Cache Dir'])) {
        while (false !== ($file = readdir($handle))) {
            if (!(substr($file, 0, 7) == "rss_dl_"))
                continue;
            if (!(preg_replace('/[. ]/', '_', substr($file, 7, strlen($guess['title']))) == preg_replace('/[. ]/', '_', $guess['title'])))
                continue;
            $cacheguess = detectMatch(substr($file, 7), TRUE);
            if ($cacheguess != false && $guess['episode'] == $cacheguess['episode']) {
                twxa_debug("Full Episode Match, ignoring " . $guess['episode'] . "\n", 2); //TODO what does this mean?
                $matched = "duplicate";
                return 0;
            }
        }
    } else {
        twxa_debug("Unable to open Cache Directory: " . $config_values['Settings']['Cache Dir'] . "\n", -1);
    }
    return 1;
}

/* Returns 1 if there is no cache hit(dl now)
 * Returns 0 if there is a hit
 */

function check_cache($title) {
    global $config_values, $matched;
    if (isset($config_values['Settings']['Cache Dir'])) {
        $cache_file = $config_values['Settings']['Cache Dir'] . '/rss_dl_' . filename_encode($title);
        if (!file_exists($cache_file)) {
            if ($config_values['Settings']['Verify Episode']) {
                return check_cache_episode($title);
            } else {
                return 1;
            }
        } else {
            $matched = "cachehit";
            return 0;
        }
    } else {
        // No Cache, Always download
        return 1;
    }
}
