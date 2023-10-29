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
    $config_values['Settings']['Also Save Torrent Files'] = "0";
    $config_values['Settings']['Also Save Dir'] = "";
    // Torrent tab
    $config_values['Settings']['Deep Directories'] = "0";
    $config_values['Settings']['Default Seed Ratio'] = "-1";
    $config_values['Settings']['Auto-Del Seeded Torrents'] = "1";
    // Favorites tab
    $config_values['Settings']['Enable Super-Favorites'] = "0";
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
    $config_values['Settings']['From Name'] = "torrentwatch-xa";
    $config_values['Settings']['From Email'] = "";
    $config_values['Settings']['To Email'] = "";
    $config_values['Settings']['SMTP Server'] = "localhost";
    $config_values['Settings']['SMTP Port'] = "25";
    $config_values['Settings']['SMTP Authentication'] = "None";
    $config_values['Settings']['SMTP Encryption'] = "None";
    $config_values['Settings']['SMTP User'] = "";
    $config_values['Settings']['SMTP Password'] = "";
    $config_values['Settings']['HELO Override'] = "";
    // Settings not exposed through the web UI
    $config_values['Settings']['Cron Interval'] = 15; // measured in minutes, match the interval in torrentwatch-xa-cron
    // wipe the Super-Favorites section
    $config_values['Super-Favorites'] = [];
    // wipe the Favorites section
    $config_values['Favorites'] = [];
    // wipe the Hidden section
    $config_values['Hidden'] = [];
    // set defaults for the Feeds section
    $config_values['Feeds'] = [
        0 => [
            'Link' => 'https://nyaa.si/?page=rss',
            'seedRatio' => "",
            'enabled' => 1,
            'Name' => 'Nyaa Torrent File RSS',
            'Website' => 'https://nyaa.si'
        ],
        1 => [
            'Link' => 'https://eztv.re/ezrss.xml',
            'seedRatio' => "",
            'enabled' => 1,
            'Name' => 'TV Torrents RSS feed - EZTV',
            'Website' => 'https://eztv.re'
        ],
        2 => [
            'Link' => 'https://feed.animetosho.org/atom',
            'seedRatio' => "",
            'enabled' => 1,
            'Name' => 'AnimeTosho.org Atom',
            'Website' => 'https://animetosho.org'
        ],
        3 => [
            'Link' => 'https://tokyotosho.info/rss.php?filter=1',
            'seedRatio' => "",
            'enabled' => 0,
            'Name' => 'TokyoTosho.info Anime',
            'Website' => 'https://www.tokyotosho.info'
        ],
        4 => [
            'Link' => 'https://www.acgnx.se/rss.xml',
            'seedRatio' => "",
            'enabled' => 0,
            'Name' => 'AcgnX Torrent Resources Base.Global',
            'Website' => 'https://www.acgnx.se'
        ]
    ];
    setpHPTimeZone($config_values['Settings']['Time Zone']);
    return writejSONConfigFile();
}

function readjSONConfigFile() {
    // reads config file written in PHP's JSON format
    global $config_values;

    $configFile = getConfigFile();
    $configCacheFile = getConfigCacheFile();

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
        if ($configCacheFileExists) {
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
        case "cache":
            $config_values = unserialize(file_get_contents($configCacheFile));
            if ($config_values === false) {
                // fall through to config below
            } else {
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

function get_client_passwd($configClientPassword) {
    return base64_decode(preg_replace('/^\$%&(.*)\$%&$/', '$1', $configClientPassword));
}

function set_client_passwd(&$configClientPassword) {
    if (!(preg_match('/^\$%&(.*)\$%&$/', $configClientPassword))) {
        if ($configClientPassword) {
            $configClientPassword = preg_replace('/^(.*)$/', '\$%&$1\$%&', base64_encode($configClientPassword));
        } else {
            $configClientPassword = "";
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

function setupConfigCacheDir() {
    $configCacheDir = getConfigCacheDir();
    writeToLog("Checking Config Cache: $configCacheDir\n", 2);
    if (file_exists($configCacheDir)) {
        if (is_dir($configCacheDir)) {
            if (is_writeable($configCacheDir)) {
                // Config Cache Dir is already set up
                writeToLog("Config Cache Dir is already set up correctly: $configCacheDir\n", 2);
                return true;
            } else {
                writeToLog("Config Cache Dir exists but is not writeable, attempting to chmod it: $configCacheDir\n", -1);
                return chmodPath($configCacheDir, 0775);
            }
        } else {
            writeToLog("Config Cache Dir exists but is not a directory: $configCacheDir\n", -1);
            return false;
        }
    } else {
        writeToLog("Config Cache Dir does not exist or does not have correct permissions, attempting to create: $configCacheDir\n", 1);
        //TODO make sure $configCacheDir looks like a path
        if (mkdir($configCacheDir, 0775, true)) {
            writeToLog("Successfully set up Config Cache Dir: $configCacheDir\n", 2);
            return true;
        } else {
            writeToLog("Unable to create Config Cache Dir: $configCacheDir\n", -1);
            return false;
        }
    }
}

function writejSONConfigFile() {
    global $config_values;

    $configFile = getConfigFile();
    $configTempFile = $configFile . "_tmp";
    $configCache = getConfigCacheFile();

    writeToLog("Writing config file: $configFile\n", 2);

    set_client_passwd($config_values['Settings']['Transmission Password']); //TODO this should happen outside this function
    set_smtp_passwd(); //TODO this should happen outside this function
    // copy everything but $config_values['Global'] so that it doesn't pollute the config file
    $configOut = $config_values;
    unset($configOut['Global']);

    setupConfigCacheDir();

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
    $input = [
        // Interface tab
        'Combine Feeds' => 'combinefeeds',
        'Disable Hide List' => 'dishidelist',
        'Show Debug' => 'showdebug',
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
        'Also Save Torrent Files' => 'alsosavetorrentfiles',
        'Also Save Dir' => 'alsosavedir',
        // Torrent tab
        'Deep Directories' => 'deepdir',
        'Default Seed Ratio' => 'defaultratio',
        'Auto-Del Seeded Torrents' => 'autodel',
        // Favorites tab
        'Enable Super-Favorites' => 'enablesuperfavorites',
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
        'From Name' => 'fromName',
        'From Email' => 'fromEmail',
        'To Email' => 'toEmail',
        'SMTP Server' => 'smtpServer',
        'SMTP Port' => 'smtpPort',
        'SMTP Authentication' => 'smtpAuthentication',
        'SMTP Encryption' => 'smtpEncryption',
        'SMTP User' => 'smtpUser',
        'SMTP Password' => 'smtpPassword',
        'HELO Override' => 'hELOOverride'
    ];
    foreach ($input as $key => $data) {
        $config_values['Settings'][$key] = filter_input(INPUT_GET, $data);
    }
    return(writejSONConfigFile());
}

function updateSuperFavoriteFromgET() {
    if (!isset($_GET['button'])) {
        return;
    }
    switch ($_GET['button']) {
        case 'Add':
        case 'Update':
            $response = addSuperFavoriteFromgET();
            //writejSONConfigFile();
            break;
        case 'Delete':
            $response = deleteSuperFavoriteFromgET();
    }
    if (isset($response)) {
        return $response;
    }
}

function updateFavoriteFromgET() {
    if (!isset($_GET['button'])) {
        return;
    }
    switch ($_GET['button']) {
        case 'Add':
        case 'Update':
            $response = addFavoriteFromgET();
            //writejSONConfigFile();
            break;
        case 'Delete':
            $response = deleteFavoriteFromgET();
    }
    if (isset($response)) {
        return $response;
    }
}

function addHidden($name) {
    global $config_values;
    if (!empty($name)) {
        $guess = detectMatch(html_entity_decode($name));
        if (!empty($guess['favTitle'])) {
            $lctitle = strtolower(trim(strtr($guess['favTitle'], [":" => "", "," => "", "'" => "", "." => " ", "_" => " "])));
            foreach ($config_values['Favorites'] as $fav) {
                if ($lctitle == strtolower(strtr($fav['Name'], [":" => "", "," => "", "'" => "", "." => " ", "_" => " "]))) {
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

function addSuperFavoriteFromgET() {
    global $config_values;

    // $superfavInfo is returned all the way to Javascript for updating Super-Favorite in web UI dynamically
    // Theoretically we could use $_GET or $config_values['Super-Favorites'][$idx] to return, but $superfavInfo has error info
    $superfavInfo['type'] = "superfavorite";
    $superfavInfo['idx'] = null;
    $superfavInfo['errorCode'] = 0;
    $superfavInfo['errorMessage'] = "";

    if (empty($_GET['filter'])) {
        $superfavInfo['errorCode'] = 1;
        $superfavInfo['errorMessage'] = "Error: Missing Filter, cannot add Super-Favorite";
        return(json_encode($superfavInfo));
    }
    if (!isset($_GET['idx']) || $_GET['idx'] == 'new') {
        foreach ($config_values['Super-Favorites'] as $superfav) {
            if ($_GET['name'] == $superfav['Name']) {
                $superfavInfo['errorCode'] = 1;
                $superfavInfo['errorMessage'] = "Error: \"" . $_GET['name'] . "\" already exists in Super-Favorites.";
                return(json_encode($superfavInfo));
            }
        }
    }
    if (isset($_GET['idx']) && $_GET['idx'] != 'new') {
        $idx = $_GET['idx'];
    } else if (isset($_GET['name'])) {
        $config_values['Super-Favorites'][]['Name'] = $_GET['name'];
        $arrayKeys = array_keys($config_values['Super-Favorites']);
        $idx = end($arrayKeys);
        $_GET['idx'] = $idx; // so display_superfavorite_info() can see it
    } else {
        $superfavInfo['errorCode'] = 1;
        $superfavInfo['errorMessage'] = "Error: Missing index or Name, cannot add Super-Favorite";
        return(json_encode($superfavInfo));
    }
    $superfavInfo['idx'] = $idx;

    $list = [
        "name" => "Name",
        "filter" => "Filter",
        "not" => "Not",
        //"downloaddir" => "Download Dir",
        //"alsosavedir" => "Also Save Dir",
        //"episodes" => "Episodes",
        "feed" => "Feed",
        "quality" => "Quality",
        //"seedratio" => "seedRatio"
    ];
    foreach ($list as $key => $data) {
        if (isset($_GET[$key])) {
            $superfavInfo[$key] = $config_values['Super-Favorites'][$idx][$data] = $_GET[$key];
        } else {
            $superfavInfo[$key] = $config_values['Super-Favorites'][$idx][$data] = "";
        }
    }
    if (isset($_GET['feed'])) {
        $config_values['Super-Favorites'][$idx]['Feed'] = urldecode($_GET['feed']);
        $superfavInfo['feed'] = $_GET['feed'];
    }
    //$superfavInfo['title'] = $_GET['name']; // not needed for Super-Favorites
    writejSONConfigFile();
    return(json_encode($superfavInfo));
}

function addFavoriteFromParams(
        $name, // required
        $filter, // required, otherwise every item will match
        $feed = null,
        $quality = null,
        $not = null,
        $episodes = null,
        $seedratio = null,
        $season = null,
        $episode = null,
        $downloaddir = null,
        $alsosavedir = null,
        $idx = null
) {
    global $config_values;

    if (isset($filter) && !is_null($filter)) {
        $trimmedfilter = trim((string) $filter);
        if (strlen($trimmedfilter) > 0) {
            if (isset($name) && !is_null($name)) {
                // name is set, but is it useful?
                $trimmedname = trim((string) $name);
                if (strlen($trimmedname) > 0) {
                    // name is useful, check for index
                    //TODO should we check if name is unique in list of Favorites?
                    if (isset($idx) && $idx != 'new') {
                        // idx is set and not 'new', will update
                        $targetidx = $idx;
                    } else {
                        // idx is not set or is 'new', try add by name
                        foreach ($config_values['Favorites'] as $favKey => $favValue) {
                            if ($trimmedname == $favValue['Name']) {
                                return [
                                    'index' => $favKey,
                                    'errorCode' => 2,
                                    'errorMessage' => "\"$trimmedname\" already exists in Favorites at index \"$favKey\"."
                                ];
                            }
                        }
                        // okay to add by name, then update with details later
                        $config_values['Favorites'][]['Name'] = $trimmedname;
                        $arrayKeys = array_keys($config_values['Favorites']);
                        $targetidx = end($arrayKeys);
                    }
                } else {
                    // trimmed name is blank
                    return [
                        'index' => null,
                        'errorCode' => 1,
                        'errorMessage' => "Cannot add Favorite without Name."
                    ];
                }
            } else {
                // no name specified; it is absolutely required
                return [
                    'index' => null,
                    'errorCode' => 1,
                    'errorMessage' => "Cannot add Favorite without Name."
                ];
            }
        } else {
            // no filter specified; it is absolutely required
            return [
                'index' => null,
                'errorCode' => 1,
                'errorMessage' => "Cannot add Favorite without Filter."
            ];
        }
    } else {
        // no filter specified; it is absolutely required
        return [
            'index' => null,
            'errorCode' => 1,
            'errorMessage' => "Cannot add Favorite without Filter."
        ];
    }

    // update Favorite at $targetidx
    $config_values['Favorites'][$targetidx]['Name'] = $trimmedname;
    $config_values['Favorites'][$targetidx]['Filter'] = $trimmedfilter;
    if (isset($not)) {
        $config_values['Favorites'][$targetidx]['Not'] = $not;
    } else {
        $config_values['Favorites'][$targetidx]['Not'] = "";
    }
    if (isset($downloaddir)) {
        $config_values['Favorites'][$targetidx]['Download Dir'] = $downloaddir;
    } else {
        $config_values['Favorites'][$targetidx]['Download Dir'] = "";
    }
    if (isset($alsosavedir)) {
        $config_values['Favorites'][$targetidx]['Also Save Dir'] = $alsosavedir;
    } else {
        $config_values['Favorites'][$targetidx]['Also Save Dir'] = "";
    }
    if (isset($episodes)) {
        $config_values['Favorites'][$targetidx]['Episodes'] = $episodes;
    } else {
        $config_values['Favorites'][$targetidx]['Episodes'] = "";
    }
    if (isset($feed)) {
        $config_values['Favorites'][$targetidx]['Feed'] = $feed;
    } else {
        $config_values['Favorites'][$targetidx]['Feed'] = "";
    }
    if (isset($quality)) {
        $config_values['Favorites'][$targetidx]['Quality'] = $quality;
    } else {
        $config_values['Favorites'][$targetidx]['Quality'] = "";
    }
    if (isset($seedratio)) {
        $config_values['Favorites'][$targetidx]['seedRatio'] = $seedratio;
    } else {
        $config_values['Favorites'][$targetidx]['seedRatio'] = "";
    }
    if (isset($season)) {
        $config_values['Favorites'][$targetidx]['Season'] = $season;
    } else {
        $config_values['Favorites'][$targetidx]['Season'] = "";
    }
    if (isset($episode)) {
        $config_values['Favorites'][$targetidx]['Episode'] = $episode;
    } else {
        $config_values['Favorites'][$targetidx]['Episode'] = "";
    }
    writejSONConfigFile();
    // assume success by now
    return [
        'index' => $targetidx,
        'errorCode' => 0,
        'errorMessage' => "Successfully added/updated Favorite \"$trimmedname\" at index \"$targetidx\"."
    ];
}

function addFavoriteFromSuperFavoriteMatch(
        $name, // required
        $filter, // required
        $feed = null,
        $quality = null
) {
    writeToLog("Adding Favorite \"$name\" from Super-Favorite match.\n", 2);
    $return = addFavoriteFromParams(
            $name, // required
            $filter, // required
            $feed,
            $quality,
            null, // $not
            null, // $episodes
            null, // $seedratio
            null, // $season
            null, // $episode
            null, // $downloaddir
            null, // $alsosavedir
            null // $idx
    );
    switch ($return['errorCode']) {
        case 2: // Favorite already exists
            writeToLog($return['errorMessage'] . "\n", 1);
            break;
        case 1:
            writeToLog($return['errorMessage'] . "\n", 0);
            break;
        case 0:
            writeToLog($return['errorMessage'] . "\n", 1);
            break;
        default:
        // do nothing
    }
    return $return;
}

function addFavoriteFromgET() { //TODO rewrite this as wrapper to addFavoriteFromParams()
    global $config_values;

    // $favInfo is returned all the way to Javascript for updating Favorite in web UI dynamically
    // Theoretically we could use $_GET or $config_values['Favorites'][$idx] to return, but $favInfo has error info
    $favInfo['type'] = "favorite";
    $favInfo['idx'] = null;
    $favInfo['errorCode'] = 0;
    $favInfo['errorMessage'] = "";

    if (empty($_GET['filter'])) {
        $favInfo['errorCode'] = 1;
        $favInfo['errorMessage'] = "Error: Missing Filter, cannot add Favorite";
        return(json_encode($favInfo));
    }
    if (!isset($_GET['idx']) || $_GET['idx'] == 'new') {
        foreach ($config_values['Favorites'] as $fav) {
            if ($_GET['name'] == $fav['Name']) {
                $favInfo['errorCode'] = 1;
                $favInfo['errorMessage'] = "Error: \"" . $_GET['name'] . "\" already exists in Favorites.";
                return(json_encode($favInfo));
            }
        }
    }
    if (isset($_GET['idx']) && $_GET['idx'] != 'new') {
        $idx = $_GET['idx'];
    } else if (isset($_GET['name'])) {
        $config_values['Favorites'][]['Name'] = $_GET['name'];
        $arrayKeys = array_keys($config_values['Favorites']);
        $idx = end($arrayKeys);
        $_GET['idx'] = $idx; // so display_favorite_info() can see it
    } else {
        $favInfo['errorCode'] = 1;
        $favInfo['errorMessage'] = "Error: Missing index or Name, cannot add Favorite";
        return(json_encode($favInfo));
    }
    $favInfo['idx'] = $idx;

    $list = [
        "name" => "Name",
        "filter" => "Filter",
        "not" => "Not",
        "downloaddir" => "Download Dir",
        "alsosavedir" => "Also Save Dir",
        "episodes" => "Episodes",
        "quality" => "Quality",
        "seedratio" => "seedRatio",
        "season" => "Season",
        "episode" => "Episode"
    ];
    foreach ($list as $key => $data) {
        if (isset($_GET[$key])) {
            $favInfo[$key] = $config_values['Favorites'][$idx][$data] = $_GET[$key];
        } else {
            $favInfo[$key] = $config_values['Favorites'][$idx][$data] = "";
        }
    }
    if (isset($_GET['feed'])) {
        $config_values['Favorites'][$idx]['Feed'] = urldecode($_GET['feed']);
        $favInfo['feed'] = $_GET['feed'];
    }
    //$favInfo['title'] = $favInfo['name']; // this was for Add Favorites button
    // split single field for new Favorite's Season x Episode into separate Season x Episode
    if ($config_values['Favorites'][$idx]['Season'] == '' && $config_values['Favorites'][$idx]['Episode'] != '') {
        $tempMatches = [];
        if (preg_match('/(\d+)\s*[xX]\s*(\d+|FULL)/', $config_values['Favorites'][$idx]['Episode'], $tempMatches)) { // we ignore S##E## notation and version number
            $favInfo['episode'] = $config_values['Favorites'][$idx]['Episode'] = $tempMatches[2];
            $favInfo['season'] = $config_values['Favorites'][$idx]['Season'] = $tempMatches[1];
        } else if (preg_match('/^(\d{8})$/', $config_values['Favorites'][$idx]['Episode'])) {
            $favInfo['season'] = $config_values['Favorites'][$idx]['Season'] = 0; // for date notation, Season = 0
        }
    }
    writejSONConfigFile();
    return(json_encode($favInfo));
}

function deleteSuperFavoriteFromgET() {
    global $config_values;
    $index = filter_input(INPUT_GET, 'idx');
    if ($index !== false && isset($config_values['Super-Favorites'][$index])) {
        unset($config_values['Super-Favorites'][$index]);
        writejSONConfigFile();
    }
    //TODO add error handling and return a response, either error or JSON with empty favorite fields
}

function deleteFavoriteFromgET() {
    global $config_values;
    $index = filter_input(INPUT_GET, 'idx');
    if ($index !== false && isset($config_values['Favorites'][$index])) {
        unset($config_values['Favorites'][$index]);
        writejSONConfigFile();
    }
    //TODO add error handling and return a response, either error or JSON with empty favorite fields
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

function addFeed($feedItem) {
    global $config_values;
    if (filter_var($feedItem['feedLink'], FILTER_VALIDATE_URL)) {
        writeToLog("Checking feed: " . $feedItem['feedLink'] . "\n", 2);
        if (parseOneFeed(['Link' => $feedItem['feedLink']], true)) { // 2nd parameter forces update
            writeToLog("Adding feed: " . $feedItem['feedLink'] . "\n", 1);
            $config_values['Feeds'][]['Link'] = $feedItem['feedLink'];
            $arrayKeys = array_keys($config_values['Feeds']);
            $idx = end($arrayKeys);
            // check that seedRatio is numeric
            if (is_numeric($feedItem['seedRatio'])) {
                $config_values['Feeds'][$idx]['seedRatio'] = $feedItem['seedRatio'];
            } else {
                $config_values['Feeds'][$idx]['seedRatio'] = '';
            }
            // feed on/off
            if ($feedItem['feedOn'] === 'feed_on') {
                $config_values['Feeds'][$idx]['enabled'] = 1;
            } else {
                $config_values['Feeds'][$idx]['enabled'] = '';
            }
            if ($feedItem['feedName'] === '') {
                $config_values['Feeds'][$idx]['Name'] = $config_values['Global']['Feeds'][$feedItem['feedLink']]['feed']['title']; //TODO get rid of ['feed']
            } else {
                $config_values['Feeds'][$idx]['Name'] = $feedItem['feedName'];
            }
            // feed website
            if (
                    (
                    !isset($config_values['Feeds'][$idx]['Website']) || $$config_values['Feeds'][$idx]['Website'] === ''
                    ) &&
                    (
                    isset($config_values['Global']['Feeds'][$feedItem['feedLink']]['feed']['website']) &&
                    $config_values['Global']['Feeds'][$feedItem['feedLink']]['feed']['website'] !== ''
                    )
            ) {
                $config_values['Feeds'][$idx]['Website'] = filter_var($config_values['Global']['Feeds'][$feedItem['feedLink']]['feed']['website'], FILTER_VALIDATE_URL);
            }
        } else {
            writeToLog("Could not connect to and parse feed: " . $feedItem['feedLink'] . "\n", -1);
        }
    } else {
        writeToLog("Ignoring invalid feed URL\n", 0);
    }
}

function updateFeed() {
    global $config_values;

    // loop through each feedItem's URL input
    $writeChanges = false;
    $keys = array_keys($_GET); // unfortunately, must use superglobal here
    foreach ($keys as $key) {
        if (strpos($key, 'feed_url_') === 0) {
            // get the item index to look up the feedItem's other inputs
            $idx = substr($key, 9);
            // if it's new, add this feedItem
            if ($idx === 'new') {
                // add this feedItem if the feed_url_ is not blank
                $brandNewFeedItem = parseFeedItemFromGET('new');
                if ($brandNewFeedItem['feedLink'] !== false && $brandNewFeedItem['feedLink'] !== '') {
                    // add the new feedItem
                    addFeed($brandNewFeedItem);
                    $writeChanges = true;
                }
            } else {
                // not a new feedItem
                $feedItem = parseFeedItemFromGET($idx);
                // if Delete exists and is checked, delete this feedItem
                if ($feedItem['feedDelete'] === 'feed_delete') {
                    // delete this feed
                    deleteFeed($idx);
                    $writeChanges = true;
                } else {
                    // Delete is not checked
                    // if feed link is now blank, delete the entire feedItem
                    if ($feedItem['feedLink'] === false || $feedItem['feedLink'] === '') {
                        deleteFeed($idx);
                        $writeChanges = true;
                    } else {
                        // check if each part of the feedItem has changed and update it if necessary
                        $feedItemChanged = false; // reset the toggle for this one feedItem
                        if ($feedItem['index'] !== false && $feedItem['index'] !== '') {
                            // compare and update the feed link
                            $oldFeedLink = $config_values['Feeds'][$idx]['Link'];
                            $newFeedLink = filter_var($feedItem['feedLink'], FILTER_VALIDATE_URL);
                            if ($newFeedLink !== false && $newFeedLink !== '') {
                                if ($newFeedLink !== $oldFeedLink) {
                                    // update the feed link on all associated Favorites
                                    foreach ($config_values['Favorites'] as &$favorite) {
                                        if ($favorite['Feed'] == $oldFeedLink) {
                                            $favorite['Feed'] = str_replace(' ', '%20', $newFeedLink);
                                        }
                                    }
                                    $config_values['Feeds'][$idx]['Link'] = $newFeedLink;
                                    $feedItemChanged = true;
                                }
                            } else {
                                writeToLog("Ignoring invalid feed URL: " . $feedItem['feedLink'] . "\n", 0);
                            }
                            // compare and update the feed name
                            if ($config_values['Feeds'][$idx]['Name'] !== $feedItem['feedName']) {
                                // if the new feed name is blank, get the official feed name from the feed
                                if ($feedItem['feedName'] === false || $feedItem['feedName'] === '') {
                                    if (parseOneFeed(['Link' => $feedItem['feedLink']])) {
                                        $config_values['Feeds'][$idx]['Name'] = $config_values['Global']['Feeds'][$feedItem['feedLink']]['feed']['title']; //TODO get rid of ['feed']
                                        $feedItemChanged = true;
                                    } else {
                                        writeToLog("Could not connect to and parse feed: " . $feedItem['feedLink'] . "\n", -1);
                                    }
                                } else {
                                    // update the feed name
                                    $config_values['Feeds'][$idx]['Name'] = $feedItem['feedName'];
                                    $feedItemChanged = true;
                                }
                            }
                            // compare and update the feed website
                            $oldFeedWebsite = $config_values['Feeds'][$idx]['Website'];
                            $newFeedWebsite = filter_var($feedItem['feedWebsite'], FILTER_VALIDATE_URL);
                            if ($newFeedWebsite !== false && $newFeedWebsite !== '') {
                                if ($newFeedWebsite !== $oldFeedWebsite) {
                                    $config_values['Feeds'][$idx]['Website'] = $newFeedWebsite;
                                    $feedItemChanged = true;
                                }
                            } else {
                                writeToLog("Ignoring invalid feed website URL: " . $feedItem['feedWebsite'] . "\n", 0);
                            }
                            // compare and update the feed on/off
                            if ($config_values['Feeds'][$idx]['enabled'] === 1 && $feedItem['feedOn'] !== 'feed_on') {
                                $config_values['Feeds'][$idx]['enabled'] = '';
                                $feedItemChanged = true;
                            } else if ($config_values['Feeds'][$idx]['enabled'] !== 1 && $feedItem['feedOn'] === 'feed_on') {
                                $config_values['Feeds'][$idx]['enabled'] = 1;
                                $feedItemChanged = true;
                            }
                            // compare and update the seed ratio
                            if ($config_values['Feeds'][$idx]['seedRatio'] !== $feedItem['seedRatio']) {
                                if (is_numeric($feedItem['seedRatio']) || $feedItem['seedRatio'] === '') {
                                    $config_values['Feeds'][$idx]['seedRatio'] = $feedItem['seedRatio'];
                                    $feedItemChanged = true;
                                } else {
                                    writeToLog("Did not change seed ratio due to non-numeric input\n", 0);
                                }
                            }
                            if ($feedItemChanged === true) {
                                $writeChanges = true;
                                writeToLog("Updated feed: $oldFeedLink\n", 1);
                            }
                        }
                    }
                }
            }
        }
    }
    // if even one setting of one already-existing feedItem changed, write the config file
    if ($writeChanges === true) {
        writejSONConfigFile();
    }
}

function parseFeedItemFromGET($key) {
    // parses the GET superglobal for the feedItem specified by $key
    $feedLink = filter_input(INPUT_GET, 'feed_url_' . $key);
    $feedName = filter_input(INPUT_GET, 'feed_name_' . $key);
    $feedWebsite = filter_input(INPUT_GET, 'feed_website_' . $key);
    $feedOn = filter_input(INPUT_GET, 'feed_on_' . $key);
    $seedRatio = filter_input(INPUT_GET, 'seed_ratio_' . $key);
    $feedDelete = filter_input(INPUT_GET, 'feed_delete_' . $key);
    return [
        'index' => $key,
        'feedLink' => $feedLink,
        'feedName' => $feedName,
        'feedWebsite' => $feedWebsite,
        'feedOn' => $feedOn,
        'seedRatio' => $seedRatio,
        'feedDelete' => $feedDelete
    ];
}

function deleteFeed($index) {
    global $config_values;
    if ($index !== false && isset($config_values['Feeds'][$index])) {
        writeToLog("Deleting feed with index: $index\n", 1);
        unset($config_values['Feeds'][$index]);
    } else {
        writeToLog("Unable to delete feed. Could not find feed index: $index\n", -1);
    }
}
