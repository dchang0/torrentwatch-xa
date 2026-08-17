<?php

// cache and history functions

function add_history($ti) {
    $downloadHistoryFile = getDownloadHistoryFile();
    $handle = @fopen($downloadHistoryFile, 'c+');
    if ($handle) {
        // exclusive lock around the read-modify-write so concurrent processes don't clobber history
        flock($handle, LOCK_EX);
        $contents = stream_get_contents($handle);
        $history = $contents !== false ? @unserialize($contents) : [];
        if (!is_array($history)) {
            $history = [];
        }
        $history[] = ['Title' => $ti, 'Date' => date("Y.m.d H:i")];
        if (ftruncate($handle, 0)) {
            fseek($handle, 0);
            if (fwrite($handle, serialize($history)) === false) {
                writeToLog("Unable to write history to $downloadHistoryFile\n", 0);
            }
        } else {
            writeToLog("Unable to write history to $downloadHistoryFile\n", 0);
        }
        flock($handle, LOCK_UN);
        fclose($handle);
    } else {
        writeToLog("Unable to write history to $downloadHistoryFile\n", 0);
    }
}

function setupDownloadCacheDir() {
    $downloadCacheDir = getDownloadCacheDir();
    writeToLog("Checking Download Cache: $downloadCacheDir\n", 2);
    if (file_exists($downloadCacheDir)) {
        if (is_dir($downloadCacheDir)) {
            if (is_writeable($downloadCacheDir)) {
                // Download Cache Dir is already set up
                writeToLog("Download Cache Dir is already set up correctly: $downloadCacheDir\n", 2);
                return true;
            } else {
                writeToLog("Download Cache Dir exists but is not writeable, attempting to chmod it: $downloadCacheDir\n", -1);
                return chmodPath($downloadCacheDir, 0775);
            }
        } else {
            writeToLog("Download Cache Dir exists but is not a directory: $downloadCacheDir\n", -1);
            return false;
        }
    } else {
        writeToLog("Download Cache Dir does not exist or does not have correct permissions, creating: $downloadCacheDir\n", 1);
        if (mkdir($downloadCacheDir, 0775, true)) {
            if (!is_dir($downloadCacheDir) || !is_writeable($downloadCacheDir)) {
                writeToLog("Download Cache Dir could not be accessed after creation: $downloadCacheDir\n", -1);
                return false;
            }
            writeToLog("Successfully set up Download Cache Dir: $downloadCacheDir\n", 2);
            return true;
        } else {
            writeToLog("Unable to create Download Cache Dir: $downloadCacheDir\n", -1);
            return false;
        }
    }
}

function getCacheFile($ti) {
    return getDownloadCacheDir() . '/dl_' . sanitizeFilename($ti) . '.json';
}

function getCacheData($cache_file) {
    if (!file_exists($cache_file) || !is_readable($cache_file)) {
        return [];
    }
    $handle = @fopen($cache_file, 'r');
    if ($handle === false) {
        return [];
    }
    // shared lock so we never read a cache file mid-write
    flock($handle, LOCK_SH);
    $contents = stream_get_contents($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
    if ($contents === false) {
        return [];
    }
    $data = @json_decode($contents, true);
    if (!is_array($data)) {
        return [];
    }
    return $data;
}

function updateCacheData($ti, $data) {
    $cache_file = getCacheFile($ti);
    $handle = @fopen($cache_file, 'c+');
    if ($handle === false) {
        return false;
    }
    // exclusive lock across the read-modify-write so concurrent processes don't lose updates
    flock($handle, LOCK_EX);
    $contents = stream_get_contents($handle);
    $existing = [];
    if ($contents !== false && $contents !== '') {
        $existing = @json_decode($contents, true);
        if (!is_array($existing)) {
            $existing = [];
        }
    }
    foreach ($data as $key => $value) {
        $existing[$key] = $value;
    }
    $json = json_encode($existing, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    $success = false;
    if ($json !== false) {
        $success = ftruncate($handle, 0) && rewind($handle) && fwrite($handle, $json) !== false;
    }
    flock($handle, LOCK_UN);
    fclose($handle);
    if ($success !== true) {
        return false;
    }
    @chmodPath($cache_file, 0666);
    return $cache_file;
}

function add_cache($ti, $data = []) {
    $cache_file = getCacheFile($ti);
    if (file_exists($cache_file) && !is_readable($cache_file)) {
        writeToLog("Unable to read cache file: $cache_file\n", 0);
        return false;
    }
    $cacheData = [
        'version' => 1,
        'title' => $ti,
        'dateAdded' => date("Y.m.d H:i"),
        'parsed' => detectMatch($ti)
    ];
    foreach ($data as $key => $value) {
        $cacheData[$key] = $value;
    }
    if (updateCacheData($ti, $cacheData) === false) {
        writeToLog("Unable to create cache file: $cache_file\n", 0);
        return false;
    }
    return $cache_file;
}

function delete_cache_files($file) {
    $fileglob = getDownloadCacheDir() . '/' . $file;
    writeToLog("Deleting: $fileglob\n", 1);
    foreach ((glob($fileglob) ?: []) as $fn) {
        if (unlink($fn)) {
            writeToLog("Deleted: $fn\n", 2);
        } else {
            writeToLog("Failed to delete: $fn\n", 0);
        }
    }
}

function clear_cache($type) {
    switch ($type) {
        case 'feeds':
            delete_cache_files("feedcache_*");
            break;
        case 'all':
            delete_cache_files("feedcache_*");
            // fall through intentionally: 'all' clears the torrent cache too
        case 'torrents':
            delete_cache_files("dl_*.json");
    }
}

function get_torHash($cache_file) {
    if (!file_exists($cache_file) || !is_readable($cache_file)) {
        writeToLog("Unable to open cache file: $cache_file\n", 0);
        return "";
    }
    $data = getCacheData($cache_file);
    $torHash = isset($data['torHash']) ? $data['torHash'] : "";
    return is_string($torHash) ? $torHash : "";
}

function check_cache_for_torHash($torHash) {
    $downloadCacheDir = getDownloadCacheDir();
    if (!is_dir($downloadCacheDir) || !is_readable($downloadCacheDir)) {
        writeToLog("Unable to open Download Cache Dir: $downloadCacheDir\n", -1);
        return "";
    }
    foreach ((glob($downloadCacheDir . "/dl_*.json") ?: []) as $cache_file) {
        $data = getCacheData($cache_file);
        if (!empty($data['torHash']) && $data['torHash'] === $torHash) {
            return basename($cache_file);
        }
    }
    return "";
}

function check_cache_episode($ti) {
    // attempts to find previous downloads that have the same parsed title but different episode numbering styles
    global $config_values;
    $guess = detectMatch($ti);
    if ($guess['favTitle'] === "") {
        writeToLog("Unable to guess a favoriteTitle for $ti\n", 0);
        return true; // do download
    }
    $downloadCacheDir = getDownloadCacheDir();
    if (!is_dir($downloadCacheDir) || !is_readable($downloadCacheDir)) {
        writeToLog("Unable to open Download Cache Dir: $downloadCacheDir\n", -1);
        return true; // do download
    }
    foreach ((glob($downloadCacheDir . "/dl_*.json") ?: []) as $cache_file) {
        // loop through each cache file in the Download Cache Dir
        $cacheData = getCacheData($cache_file);
        if (empty($cacheData['parsed']) || !is_array($cacheData['parsed'])) {
            continue;
        }
        $cacheguess = $cacheData['parsed'];
        // check for a match by parsed title
        if (preg_replace('/[. ]/', '_', $cacheguess['favTitle']) !== preg_replace('/[. ]/', '_', $guess['favTitle'])) {
            continue;
        }
        // if match by title, check for a match by episode
        //TODO does Ignore Batches need to be implemented here?
        if ($cacheguess['numberSequence'] > 0 && $guess['numberSequence'] === $cacheguess['numberSequence']) {
                    if ($guess['seasBatEnd'] === $cacheguess['seasBatEnd']) {
                        // end is in same season, compare episodes only
                        if ($guess['episBatEnd'] === "") {
                            // full season, compare
                            if ($cacheguess['episBatEnd'] !== "" && is_numeric($cacheguess['episBatEnd'])) {
                                return true; // title is a full season and is likely newer than the last episode in cache, do download
                            } else {
                                writeToLog("Equiv. in cache: ignoring: $ti (" . $guess['seasBatEnd'] . "x" . $guess['episBatEnd'] . ")\n", 2);
                                return false; // both are full seasons
                            }
                        } else if ($guess['episBatEnd'] === $cacheguess['episBatEnd']) {
                            $guessVersion = is_numeric($guess['itemVersion']) ? (int)$guess['itemVersion'] : 1;
                            $cacheVersion = is_numeric($cacheguess['itemVersion']) ? (int)$cacheguess['itemVersion'] : 1;
                            if ($guessVersion > $cacheVersion) {
                                if ($config_values['Settings']['Download Versions']) {
                                    return true; // difference in item version, do download
                                } else {
                                    writeToLog("Older version in cache: ignoring newer: $ti (" . $guess['episode'] . "v" . $guess['itemVersion'] . ")\n", 2);
                                    return false; // title is found in cache, version is newer, Download Versions is off, so don't download
                                }
                            } else {
                                writeToLog("Equiv. in cache: ignoring: $ti (" . $guess['episode'] . "v" . $guess['itemVersion'] . ")\n", 2);
                                return false; // title and same version is found in cache, don't download
                            }
                        } else if (
                                is_numeric($guess['episBatEnd']) &&
                                is_numeric($cacheguess['episBatStart']) &&
                                is_numeric($cacheguess['episBatEnd']) &&
                                $guess['episBatEnd'] >= $cacheguess['episBatStart'] &&
                                $guess['episBatEnd'] < $cacheguess['episBatEnd']
                        ) {
                            writeToLog("Ignoring: $ti (Cur:Cache " . $cacheguess['seasBatEnd'] . "x" . $cacheguess['episBatStart']
                                    . "<=" . $guess['seasBatEnd'] . "x" . $guess['episBatEnd'] .
                                    "<" . $cacheguess['seasBatEnd'] . "x" . $cacheguess['episBatEnd'] . ")\n", 2);
                            return false; // end episode is within the episode batch found in cache, don't download
                        } else {
                            // end episode appears to be newer than the last episode found in cache OR
                            // older than the earliest episode found in cache, do download
                            return true;
                        }
                    } else if (
                            is_numeric($guess['seasBatEnd']) &&
                            is_numeric($cacheguess['seasBatStart']) &&
                            is_numeric($cacheguess['seasBatEnd']) &&
                            $guess['seasBatEnd'] >= $cacheguess['seasBatStart'] &&
                            $guess['seasBatEnd'] < $cacheguess['seasBatEnd']
                    ) {
                        writeToLog("Ignoring: $ti (Cur:Cache " . $cacheguess['seasBatEnd'] . "x" . $cacheguess['episBatStart']
                                . "<=" . $guess['seasBatEnd'] . "x" . $guess['episBatEnd'] .
                                "<" . $cacheguess['seasBatEnd'] . "x" . $cacheguess['episBatEnd'] . ")\n", 2);
                        return false; // end season appears to overlap with season range in cache, but is too old to compare episodes; don't download
                    } else {
                        // end season appears to be entirely older than the earliest season found in cache OR
                        // entirely newer than the last season found in cache, do download
                        return true;
                    }
                }
    }
    return true; // do download
}

function check_cache($ti) {
    $cache_file = getCacheFile($ti);
    if (!file_exists($cache_file)) {
        return check_cache_episode($ti);
    } else {
        return false;
    }
}
