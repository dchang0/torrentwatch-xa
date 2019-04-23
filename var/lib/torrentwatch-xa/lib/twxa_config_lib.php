<?php

// configuration-related functions

// NOTE: DO NOT put trailing slashes on any of these paths
function getConfigCacheDir() {
    return get_baseDir() . "/config_cache";
}

function getConfigCacheFile() {
    return getConfigCacheDir() . "/torrentwatch-xa-config.cache";
}

function getConfigFile() {
    return getConfigCacheDir() . "/torrentwatch-xa.config";
}

function getDownloadCacheDir() {
    return get_baseDir() . "/dl_cache";
}

function getDownloadHistoryFile() {
    return get_baseDir() . "/dl_cache/dl_history";
}

function getTransmissionSessionIdFile() {
    return getDownloadCacheDir() . "/.Transmission-Session-Id";
}

function getTransmissionrPCPath() {
    return "/transmission/rpc"; // do not change this without good reason
}

function getTransmissionWebPath() {
    return "/transmission/web"; // do not change this without good reason
}

function getTorrentFileExtension() {
    return "torrent"; // do not include leading period
}

function getMagnetFileExtension() {
    return "magnet"; // do not include leading period
}

function getTransmissionWebuRL() {
    global $config_values;
    $host = $config_values['Settings']['Transmission Host'];
    if (preg_match('/(localhost|127\.0\.0\.1)/', $host)) {
        $host = preg_replace('/:.*/', "", filter_input(INPUT_SERVER, 'HTTP_HOST'));
    }
    if (preg_match('/(localhost|127\.0\.0\.1)/', $host)) {
        $host = preg_replace('/:.*/', "", filter_input(INPUT_SERVER, 'SERVER_NAME'));
    }
    $host = $host . ':' . $config_values['Settings']['Transmission Port'] . getTransmissionWebPath() . "/"; // ending slash is required
    return $host;
}

function writeDefaultConfigFile() {
    global $config_values;

    // wipe the Settings section
    $config_values['Settings'] = [];
    // set defaults for the Settings section
    // Interface tab
    $config_values['Settings']['Combine Feeds'] = "0";
    $config_values['Settings']['Disable Hide List'] = "0";
    $config_values['Settings']['Show Debug'] = "0";
    $config_values['Settings']['Hide Donate Button'] = "0";
    $config_values['Settings']['Check for Updates'] = "1";
    $config_values['Settings']['Time Zone'] = "UTC";
    $config_values['Settings']['Log Level'] = "0";
    // Client tab
    $config_values['Settings']['Client'] = "Transmission";
    $config_values['Settings']['Download Dir'] = "/var/lib/transmission-daemon/downloads";
    $config_values['Settings']['Transmission Host'] = "localhost";
    $config_values['Settings']['Transmission Port'] = "9091";
    $config_values['Settings']['Transmission Login'] = "transmission"; // default for Debian's transmission-daemon
    $config_values['Settings']['Transmission Password'] = "transmission";
    $config_values['Settings']['Save Torrents'] = "0";
    $config_values['Settings']['Save Torrents Dir'] = "";
    // Torrent tab
    $config_values['Settings']['Deep Directories'] = "0";
    $config_values['Settings']['Default Seed Ratio'] = "-1";
    $config_values['Settings']['Auto-Del Seeded Torrents'] = "1";
    // Favorites tab
    $config_values['Settings']['Match Style'] = "regexp";
    $config_values['Settings']['Default Feed All'] = "1";
    $config_values['Settings']['Require Episode Info'] = "1";
    $config_values['Settings']['Only Newer'] = "1";
    $config_values['Settings']['Ignore Batches'] = "1";
    $config_values['Settings']['Download Versions'] = "1";
    $config_values['Settings']['Resolutions Only'] = "0";
    // Trigger tab
    $config_values['Settings']['Enable Script'] = "0";
    $config_values['Settings']['Script'] = "";
    $config_values['Settings']['SMTP Notifications'] = "0";
    $config_values['Settings']['From Email'] = "";
    $config_values['Settings']['To Email'] = "";
    $config_values['Settings']['SMTP Server'] = "localhost";
    $config_values['Settings']['SMTP Port'] = "25";
    $config_values['Settings']['SMTP Authentication'] = "None";
    $config_values['Settings']['SMTP Encryption'] = "None";
    $config_values['Settings']['SMTP User'] = "";
    $config_values['Settings']['SMTP Password'] = "";
    // wipe the Favorites section
    $config_values['Favorites'] = [];
    // wipe the Hidden section
    $config_values['Hidden'] = [];
    // set defaults for the Feeds section
    $config_values['Feeds'] = [
        0 => [
            'Link' => 'http://horriblesubs.info/rss.php?res=all',
            'Type' => 'RSS',
            'seedRatio' => "",
            'enabled' => 1,
            'Name' => 'HorribleSubs Latest RSS'
        ],
        1 => [
            'Link' => 'https://nyaa.si/?page=rss',
            'Type' => 'RSS',
            'seedRatio' => "",
            'enabled' => 1,
            'Name' => 'Nyaa Torrent File RSS'
        ],
        2 => [
            'Link' => 'https://eztv.io/ezrss.xml',
            'Type' => 'RSS',
            'seedRatio' => "",
            'enabled' => 1,
            'Name' => 'TV Torrents RSS feed - EZTV'
        ],
        3 => [
            'Link' => 'http://tokyotosho.info/rss.php?filter=1',
            'Type' => 'RSS',
            'seedRatio' => "",
            'enabled' => 1,
            'Name' => 'TokyoTosho.info Anime'
        ],
        4 => [
            'Link' => 'https://anidex.info/rss/cat/0',
            'Type' => 'RSS',
            'seedRatio' => "",
            'enabled' => 0,
            'Name' => 'AniDex'
        ],
        5 => [
            'Link' => 'https://www.acgnx.se/rss.xml',
            'Type' => 'RSS',
            'seedRatio' => "",
            'enabled' => 0,
            'Name' => 'AcgnX Torrent Resources Base.Global'
        ]
    ];
    setpHPTimeZone($config_values['Settings']['Time Zone']);
    return writejSONConfigFile();
}

function readjSONConfigFile() {
    // reads config file written in PHP's JSON format
    global $config_values;

    $configFile = getConfigFile();
//    $configTempFile = $configFile . "_tmp";
    $configCacheFile = getConfigCacheFile();

    //writeToLog("Reading config file: $configFile\n", 2); // this line will fill the log file due to getClientData calls
    // file ages are integer UNIX timestamp: seconds since epoch
    // check for config file
    if (file_exists($configFile)) {
        // config file exists
        $configFileExists = true;
        // check age of config cache file
        $configFileAge = time() - filemtime($configFile);
    } else {
        // config file doesn't exist
        $configFileExists = false;
        $configFileAge = -1;
    }
//    // check for _tmp file
//    if (file_exists($configTempFile)) {
//        // config temp file exists, assume it is in the midst of a rename
//        $configTempFileExists = true;
//        $configTempFileAge = time() - filemtime($configTempFile);
//    } else {
//        // config temp file does not exist
//        $configTempFileExists = false;
//        $configTempFileAge = -1;
//    }
    // check for config cache file
    if (file_exists($configCacheFile)) {
        // check whether config temp file or config cache file is newer and read that
        $configCacheFileExists = true;
        $configCacheFileAge = time() - filemtime($configCacheFile);
    } else {
        // config cache file does not exist
        $configCacheFileExists = false;
        $configCacheFileAge = -1;
    }

    // figure out where to get the config
    $useWhich = "";
    if ($configFileExists) {
        if ($configCacheFileExists) {
            if ($configFileAge > 300 && $configCacheFileAge <= 300) {
                $useWhich = "cache";
            } else {
                $useWhich = "config";
            }
        } else {
            $useWhich = "config";
        }
    } else {
        /* if ($configTempFileExists) {
          if ($configCacheFileExists && $configCacheFileAge <= $configTempFileAge) {
          $useWhich = "cache";
          } else {
          $useWhich = "temp";
          }
          } else */ if ($configCacheFileExists) {
            $useWhich = "cacheToConfig";
        } else {
            // create new default config file
            writeToLog("No config file found--creating default config at: $configFile\n", 1);
            $useWhich = "new";
        }
    }

    // get the config
    $return = false;
    switch ($useWhich) {
//        case "temp":
//            $config_values = json_decode(file_get_contents($configTempFile), true);
//            //TODO handle errors--what if temp file doesn't contain useful settings?
//            if (isset($config_values)) {
//                $return = writejSONConfigFile() && writeConfigCacheFile($configCacheFile, $config_values);
//            } else {
//                writeToLog("Unable to read JSON config temp file: $configTempFile\n", -1);
//            }
//            break;
        case "cache":
            $config_values = unserialize(file_get_contents($configCacheFile));
            if ($config_values === false) {
                // fall through to config below
            } else {
                //TODO test to see if $config_values contains useful settings
                break;
            }
        case "config":
            $config_values = json_decode(file_get_contents($configFile), true);
            if (isset($config_values)) {
                $return = writeConfigCacheFile($configCacheFile, $config_values);
            } else {
                writeToLog("Unable to read JSON config file: $configFile\n", -1);
            }
            break;
        case "cacheToConfig":
            $config_values = unserialize(file_get_contents($configCacheFile));
            if ($config_values === false) {
                // fall through to new below
            } else {
                //TODO test to see if $config_values contains useful settings
                if (isset($config_values)) {
                    $return = writejSONConfigFile();
                } else {
                    writeToLog("Unable to read JSON config cache file: $configCacheFile\n", -1);
                }
                break;
            }
        case "new":
        default:
            $return = writeDefaultConfigFile() && writeConfigCacheFile($configCacheFile, $config_values);
    }

    if (isset($config_values['Settings']['Time Zone'])) {
        setpHPTimeZone($config_values['Settings']['Time Zone']);
    }
    return $return;
}

function writeConfigCacheFile($configCacheFile, $config_values) {
    // write cache file
    //TODO possibly remove $config_values['Global']['Feeds'] before writing config cache, but read_config_file() did not do so
    file_put_contents($configCacheFile, serialize($config_values)); // apparently serialize can't return a failure
    chmod($configCacheFile, 0660);
}

function setpHPTimeZone($timezone) {
    if (date_default_timezone_get() !== $timezone) {
        $rc1 = date_default_timezone_set($timezone);
        if ($rc1 === false) {
            writeToLog("Unable to set timezone to: " . $timezone . "; using UTC instead\n", -1);
            $rc2 = date_default_timezone_set("UTC"); // could use recursion, but might make an infinite loop
            if ($rc2 === false) {
                writeToLog("Unable to set timezone to UTC; leaving at PHP default\n", -1);
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }
}

function get_client_passwd() {
    global $config_values;
    return base64_decode(preg_replace('/^\$%&(.*)\$%&$/', '$1', $config_values['Settings']['Transmission Password']));
}

function set_client_passwd() {
    global $config_values; //TODO remove use of global
    if (!(preg_match('/^\$%&(.*)\$%&$/', $config_values['Settings']['Transmission Password']))) {
        if ($config_values['Settings']['Transmission Password']) {
            $config_values['Settings']['Transmission Password'] = preg_replace('/^(.*)$/', '\$%&$1\$%&', base64_encode($config_values['Settings']['Transmission Password']));
        } else {
            $config_values['Settings']['Transmission Password'] = "";
        }
    }
}

function decryptsMTPPassword($encryptedPassword) {
    return base64_decode(preg_replace('/^\$%&(.*)\$%&$/', '$1', $encryptedPassword));
}

function set_smtp_passwd() {
    global $config_values;
    if (!(preg_match('/^\$%&(.*)\$%&$/', $config_values['Settings']['SMTP Password']))) {
        if ($config_values['Settings']['SMTP Password']) {
            $config_values['Settings']['SMTP Password'] = preg_replace('/^(.*)$/', '\$%&$1\$%&', base64_encode($config_values['Settings']['SMTP Password']));
        } else {
            $config_values['Settings']['SMTP Password'] = "";
        }
    }
}

function createConfigDir() {
    $dir = getConfigCacheDir();
    if (!is_dir($dir)) {
        writeToLog("Creating configuration directory: $dir\n", 1);
        if (file_exists($dir)) {
            unlink($dir);
        }
        if (mkdir($dir)) { //TODO cannot create dir without proper permissions of parent directory! Check it first with fileperms(parent directory)
            if (chmod($dir, 0755)) {
                return true;
            } else {
                writeToLog("Unable to chmod config directory: $dir\n", -1);
                return false;
            }
        } else {
            writeToLog("Unable to create config directory: $dir\n", -1);
            return false;
        }
    }
}

function writejSONConfigFile() {
    global $config_values; //TODO remove use of global
    // replacement for old write_config_file() that avoids array_walk callbacks and recursion and uses PHP's JSON format

    $configFile = getConfigFile();
    $configTempFile = $configFile . "_tmp";
    $configCache = getConfigCacheFile();

    writeToLog("Writing config file: $configFile\n", 2);

    set_client_passwd(); //TODO this should happen outside this function
    set_smtp_passwd(); //TODO this should happen outside this function
    // copy everything but $config_values['Global'] so that it doesn't pollute the config file
    $configOut = $config_values;
    unset($configOut['Global']);

    createConfigDir();

    $configjSON = json_encode($configOut, JSON_PRETTY_PRINT);
    if ($configjSON !== false) {
        if (file_put_contents($configTempFile, print_r($configjSON, true), LOCK_EX) !== false) {
            if (file_exists($configTempFile)) {
                if (rename($configTempFile, $configFile)) {
                    if (chmod($configFile, 0600)) {
                        if (file_exists($configCache)) {
                            writeToLog("Removing config cache: $configCache\n", 2);
                            unlink($configCache);
                        }
                        writeToLog("Successfully wrote config file: $configFile\n", 2);
                        return true;
                    } else {
                        writeToLog("Unable to chmod config file: $configFile\n", -1);
                        return false;
                    }
                } else {
                    writeToLog("Unable to rename temp config file: $configTempFile\n", 0);
                    return false;
                }
            } else {
                writeToLog("Unable to find temp config file to rename: $configTempFile\n", 1);
                return false;
            }
        } else {
            writeToLog("Unable to write temp config file: $configTempFile\n", -1);
            return false;
        }
    } else {
        // failed to encode JSON
        writeToLog("Unable to encode config as JSON: $configFile\n", -1);
        return false;
    }
}

function updateGlobalConfig() {
    global $config_values;

    /* Receives HTTP input from the Configure panels into $config_values
     * Do not put settings that are only accessible by editing the config file
     * in this array, as they will get overwritten by null.
     */
    $input = array(
        // Interface tab
        'Combine Feeds' => 'combinefeeds',
        'Disable Hide List' => 'dishidelist',
        'Show Debug' => 'showdebug',
        'Hide Donate Button' => 'hidedonate',
        'Check for Updates' => 'checkversion',
        'Time Zone' => 'tz',
        'Log Level' => 'loglevel',
        // Client tab
        'Client' => 'client',
        'Download Dir' => 'downdir',
        'Transmission Host' => 'trhost',
        'Transmission Port' => 'trport',
        'Transmission Login' => 'truser',
        'Transmission Password' => 'trpass',
        'Save Torrents' => 'savetorrents',
        'Save Torrents Dir' => 'savetorrentsdir',
        // Torrent tab
        'Deep Directories' => 'deepdir',
        'Default Seed Ratio' => 'defaultratio',
        'Auto-Del Seeded Torrents' => 'autodel',
        // Favorites tab
        'Match Style' => 'matchstyle',
        'Default Feed All' => 'favdefaultall',
        'Require Episode Info' => 'require_epi_info',
        'Only Newer' => 'onlynewer',
        'Ignore Batches' => 'ignorebatches',
        'Download Versions' => 'fetchversions',
        'Resolutions Only' => 'resolutionsonly',
        // Trigger tab
        'Enable Script' => 'enableScript',
        'Script' => 'script',
        'SMTP Notifications' => 'enableSMTP',
        'From Email' => 'fromEmail',
        'To Email' => 'toEmail',
        'SMTP Server' => 'smtpServer',
        'SMTP Port' => 'smtpPort',
        'SMTP Authentication' => 'smtpAuthentication',
        'SMTP Encryption' => 'smtpEncryption',
        'SMTP User' => 'smtpUser',
        'SMTP Password' => 'smtpPassword'
    );
    foreach ($input as $key => $data) {
        $config_values['Settings'][$key] = filter_input(INPUT_GET, $data);
    }
    return(writejSONConfigFile());
}

function update_favorite() { //TODO seems to break Add to Favorites javascript if this is rewritten--might be an array
    if (!isset($_GET['button'])) {
        return;
    }
    switch ($_GET['button']) {
        case 'Add':
        case 'Update':
            $response = add_favorite();
            writejSONConfigFile();
            break;
        case 'Delete':
            deleteFavorite();
            break;
    }
    if (isset($response)) {
        return $response;
    } /* else {
      return;
      } */
}

function addHidden($name) {
    global $config_values;
    if (!empty($name)) {
        $guess = detectMatch(html_entity_decode($name));
        if (!empty($guess['favTitle'])) {
            $lctitle = strtolower(trim(strtr($guess['favTitle'], array(":" => "", "," => "", "'" => "", "." => " ", "_" => " "))));
            foreach ($config_values['Favorites'] as $fav) {
                if ($lctitle == strtolower(strtr($fav['Name'], array(":" => "", "," => "", "'" => "", "." => " ", "_" => " ")))) {
                    writeToLog($fav['Name'] . " exists in favorites. Not adding to hide list.\n", 0);
                    echo "twxa-ERROR:" . $fav['Name'] . " exists in favorites. Not adding to hide list.";
                    return;
                }
            }
            $config_values['Hidden'][strtolower($guess['favTitle'])] = $guess['favTitle'];
            writejSONConfigFile();
            writeToLog("Hid: $name\n", 1);
            echo $guess['favTitle']; // use favTitle, not title
            return;
        }
    } else {
        writeToLog("Nothing to hide--ignoring.\n", 0);
        echo "twxa-ERROR:Nothing to hide--ignoring.";
    }
}

function delHidden($list) {
    global $config_values;
    if (is_array($list)) { // !empty() is in the call to this function
        foreach ($list as $item) {
            if (isset($config_values['Hidden'][$item])) {
                unset($config_values['Hidden'][$item]);
                writeToLog("Unhid: $item\n", 1);
            }
        }
        return(writejSONConfigFile());
    }
}

function add_favorite() {
    global $config_values;

    if (!isset($_GET['idx']) || $_GET['idx'] == 'new') {
        foreach ($config_values['Favorites'] as $fav) {
            if ($_GET['name'] == $fav['Name']) {
                return("Error: \"" . $_GET['name'] . "\" already exists in Favorites.");
            }
        }
    }
    if (isset($_GET['idx']) && $_GET['idx'] != 'new') {
        $idx = $_GET['idx'];
    } else if (isset($_GET['name'])) {
        $config_values['Favorites'][]['Name'] = $_GET['name'];
        $arrayKeys = array_keys($config_values['Favorites']);
        $idx = end($arrayKeys);
        $_GET['idx'] = $idx; // So display_favorite_info() can see it
    } else {
        return("Error: Missing index or Name, cannot add Favorite");
    }

    $list = array(
        "name" => "Name",
        "filter" => "Filter",
        "not" => "Not",
        "downloaddir" => "Download Dir",
        "alsosavedir" => "Also Save Dir",
        "episodes" => "Episodes",
        "feed" => "Feed",
        "quality" => "Quality",
        "seedratio" => "seedRatio",
        "season" => "Season",
        "episode" => "Episode"
    );
    foreach ($list as $key => $data) {
        if (isset($_GET[$key])) {
            $config_values['Favorites'][$idx][$data] = urldecode($_GET[$key]);
            //TODO if still occurring, prevent problematic data from corrupting Favorites stanzas
        } else {
            $config_values['Favorites'][$idx][$data] = "";
        }
    }

    // split single field for new Favorite's Season x Episode into separate Season x Episode
    if ($config_values['Favorites'][$idx]['Season'] == '' && $config_values['Favorites'][$idx]['Episode'] != '') {
        $tempMatches = [];
        if (preg_match('/(\d+)\s*[xX]\s*(\d+|FULL)/', $config_values['Favorites'][$idx]['Episode'], $tempMatches)) { // we ignore S##E## notation and version number
            $config_values['Favorites'][$idx]['Episode'] = $tempMatches[2];
            $config_values['Favorites'][$idx]['Season'] = $tempMatches[1];
        } else if (preg_match('/^(\d{8})$/', $config_values['Favorites'][$idx]['Episode'])) {
            $config_values['Favorites'][$idx]['Season'] = 0; // for date notation, Season = 0
        }
    }
    $favInfo['title'] = $_GET['name'];
    $favInfo['quality'] = $_GET['quality'];
    $favInfo['feed'] = urlencode($_GET['feed']);

    return(json_encode($favInfo));
}

function deleteFavorite() {
    global $config_values;
    $index = filter_input(INPUT_GET, 'idx');
    if ($index !== false && isset($config_values['Favorites'][$index])) {
        unset($config_values['Favorites'][$index]);
        writejSONConfigFile();
//TODO switch to new, empty Favorite by using CSS to change id=favorite_new to display: block;
    }
}

function updateFavoriteEpisode(&$fav, $ti) {
    $guess = detectMatch($ti);
    if ($guess['numberSequence'] > 0) {
        if (is_numeric($guess['seasBatEnd'])) {
            if ($guess['seasBatEnd'] > $fav['Season']) {
                // item has higher season than favorite
                if (is_numeric($guess['episBatEnd'])) {
                    // item episode is numeric, update favorite season and episode
                    $fav['Season'] = $guess['seasBatEnd'];
                    $fav['Episode'] = $guess['episBatEnd'];
                } else if ($guess['episBatEnd'] === '') {
                    // full season
                    $fav['Season'] = $guess['seasBatEnd'];
                    $fav['Episode'] = "FULL";
                } else {
                    // not supposed to happen
                    return false;
                }
            } else if ($guess['seasBatEnd'] == $fav['Season']) {
                // same season, compare episodes
                if (is_numeric($guess['episBatEnd'])) {
                    if (is_numeric($fav['Episode'])) {
                        if ($guess['episBatEnd'] > $fav['Episode']) { // this can handle decimal episodes
                            // episode is newer, update favorite
                            $fav['Episode'] = $guess['episBatEnd'];
                        }
                    } else if ($fav['Episode'] === "FULL") {
                        // can't have later episode than full season, do nothing
                    } else {
                        // favorite episode is not numeric and not FULL, overwrite it
                        $fav['Episode'] = $guess['episBatEnd'];
                    }
                } else if ($guess['episBatEnd'] === '') {
                    // full season
                    $fav['Episode'] = "FULL";
                } else {
                    // not supposed to happen
                    return false;
                }
            }
        } else {
            // season batch end is not numeric, not sure what to do
            return false;
        }
        return(writejSONConfigFile());
    } else {
        return false;
    }
}

/// feed config functions

function addFeed($feedLink) {
    global $config_values;
    if (!empty($feedLink) && filter_var($feedLink, FILTER_VALIDATE_URL)) {
        writeToLog("Checking feed: $feedLink\n", 2);
        $guessedFeedType = guess_feed_type($feedLink);
        if ($guessedFeedType != 'Unknown') {
            writeToLog("Adding feed: $feedLink\n", 1);
            $config_values['Feeds'][]['Link'] = $feedLink;
            $arrayKeys = array_keys($config_values['Feeds']);
            $idx = end($arrayKeys);
            $config_values['Feeds'][$idx]['Type'] = $guessedFeedType;
            $config_values['Feeds'][$idx]['seedRatio'] = "";
            $config_values['Feeds'][$idx]['enabled'] = 1;
            load_all_feeds(array(0 => array('Type' => $guessedFeedType, 'Link' => $feedLink)), 1, true); // pass true for newly added feeds
            switch ($guessedFeedType) {
                case 'RSS':
                    $config_values['Feeds'][$idx]['Name'] = $config_values['Global']['Feeds'][$feedLink]['title'];
                    break;
                case 'Atom':
                    $config_values['Feeds'][$idx]['Name'] = $config_values['Global']['Feeds'][$feedLink]['FEED']['TITLE'];
            }
            writejSONConfigFile();
        } else {
            writeToLog("Could not connect to feed or guess feed type: $feedLink\n", -1);
        }
    } else {
        writeToLog("Ignoring empty or invalid feed URL\n", 0);
    }
}

function updateFeed() {
    switch (filter_input(INPUT_GET, 'button')) {
        case 'Delete':
            deleteFeed();
            break;
        case 'Update':
            updateFeedData();
            break;
        default:
            addFeed(filter_input(INPUT_GET, 'link'));
    }
}

function updateFeedData() {
    global $config_values;
    $index = filter_input(INPUT_GET, 'idx');
    if ($index !== false && isset($config_values['Feeds'][$index])) {
        $feedLink = filter_input(INPUT_GET, 'feed_link');
        if ($feedLink !== false && filter_var($feedLink, FILTER_VALIDATE_URL)) {
            $feedName = filter_input(INPUT_GET, 'feed_name');
            if ($feedName !== false) {
                $old_feedurl = $config_values['Feeds'][$index]['Link'];
                writeToLog("Updating feed: $old_feedurl\n", 1);
                foreach ($config_values['Favorites'] as &$favorite) {
                    if ($favorite['Feed'] == $old_feedurl) {
                        $favorite['Feed'] = str_replace(' ', '%20', $feedLink);
                    }
                }
                $config_values['Feeds'][$index]['Name'] = $feedName;
                $config_values['Feeds'][$index]['Link'] = $feedLink;
                $config_values['Feeds'][$index]['seedRatio'] = filter_input(INPUT_GET, 'seed_ratio');
                if (filter_input(INPUT_GET, 'feed_on') == "feed_on") {
                    $config_values['Feeds'][$index]['enabled'] = 1;
                } else {
                    $config_values['Feeds'][$index]['enabled'] = "";
                }
                writejSONConfigFile();
            } else {
                writeToLog("Ignoring empty feed Name", 0);
            }
        } else {
            writeToLog("Ignoring empty or invalid feed URL", 0);
        }
    } else {
        writeToLog("Unable to update feed. Could not find feed index: $index\n", -1);
    }
}

function deleteFeed() {
    global $config_values;
    $index = filter_input(INPUT_GET, 'idx');
    if ($index !== false && isset($config_values['Feeds'][$index])) {
        writeToLog("Deleting feed with index: $index\n", 1);
        unset($config_values['Feeds'][$index]);
        writejSONConfigFile();
    } else {
        writeToLog("Unable to delete feed. Could not find feed index: $index\n", -1);
    }
}
