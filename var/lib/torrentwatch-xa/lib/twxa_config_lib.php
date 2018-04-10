<?php

// configuration-related functions
// OVERRIDABLE dynamic config file and config cache file location
if (!(function_exists('get_baseDir'))) {

    function get_baseDir() {
        return "/var/lib/torrentwatch-xa"; // default path
    }

}

if (!(function_exists('get_webDir'))) {

    function get_webDir() {
        return "/var/www/html/torrentwatch-xa"; // default path
    }

}

if (!(function_exists('get_logFile'))) {

    function get_logFile() {
        return "/tmp/twxalog"; // default path
    }

}

if (!(function_exists('get_tr_sessionIdFile'))) {

    function get_tr_sessionIdFile() {
        return '/tmp/.Transmission-Session-Id';
    }

} // END OVERRIDABLE

function getConfigCacheDir() {
    return get_baseDir() . "/config_cache";
}

function getConfigCache() {
    return getConfigCacheDir() . "/torrentwatch-xa-config.cache";
}

function getConfigFile() {
    return getConfigCacheDir() . "/torrentwatch-xa.config";
}

function setup_default_config() {
    global $config_values;

    function _default($a, $b) {
        global $config_values;
        if (!isset($config_values['Settings'][$a])) {
            $config_values['Settings'][$a] = $b;
        }
    }

    if (!isset($config_values['Settings'])) {
        $config_values['Settings'] = [];
    }
    // set defaults
    $baseDir = get_baseDir();
    // Interface tab
    _default('Combine Feeds', "0");
    _default('Disable Hide List', "0");
    _default('Show Debug', "0");
    _default('Hide Donate Button', "0");
    _default('Time Zone', 'UTC');
    // Client tab
    _default('Client', "Transmission");
    _default('Download Dir', '/var/lib/transmission-daemon/downloads');
    _default('Transmission Host', 'localhost');
    _default('Transmission Port', '9091');
    _default('Transmission Login', 'transmission'); // default for Debian's transmission-daemon
    _default('Transmission Password', 'transmission');
    _default('Transmission URI', '/transmission/rpc'); // hidden setting
    _default('Save Torrents', "0");
    _default('Save Torrents Dir', "");
    // Torrent tab
    _default('Deep Directories', "0");
    _default('Default Seed Ratio', "-1");
    _default('Auto-Del Seeded Torrents', "1");
    // Favorites tab
    _default('Match Style', "regexp");
    _default('Default Feed All', "1");
    _default('Require Episode Info', "1");
    _default('Only Newer', "1");
    _default('Ignore Batches', "1");
    _default('Download Versions', "1");
    _default('Resolutions Only', "0");
    // Trigger tab
    _default('Enable Script', "0");
    _default('Script', '');
    _default('SMTP Notifications', "0");
    _default('From Email', '');
    _default('To Email', '');
    _default('SMTP Server', 'localhost');
    _default('SMTP Port', '25');
    _default('SMTP Authentication', 'None');
    _default('SMTP Encryption', 'TLS');
    _default('SMTP User', '');
    _default('SMTP Password', '');
    // Other hidden settings
    _default('debugLevel', "0");
    _default('Torrent Extension', "torrent");
    _default('Magnet Extension', "magnet");
    _default('Cache Dir', $baseDir . "/dl_cache/");
    _default('History', $baseDir . "/dl_cache/dl_history");
}

function readjSONConfigFile() {
    // reads config file written in PHP's JSON format
    global $config_values; //TODO remove use of global

    $configFile = getConfigFile();
    $configCache = getConfigCache();

    //twxaDebug("Reading config file: $configFile\n", 2); // this line will fill the log file due to getClientData calls

    if (!file_exists($configFile) && !file_exists($configFile . "_tmp")) { // check for temp file assumes rename is in progress
        twxaDebug("No config file found--creating default config at: $configFile\n", 1);
        writejSONConfigFile();
    }

    $toggleReadConfigFile = false;

    // handle config cache file
    if (file_exists($configCache)) {
        $cacheAge = time() - filemtime($configCache);
    }
    if (file_exists($configCache) && $cacheAge <= 300 && $cacheAge <= time() - filemtime($configFile)) {
        $config_values = unserialize(file_get_contents($configCache));
        if (!$config_values['Settings']) {
            unlink($configCache);
            $toggleReadConfigFile = true;
        }
    } else {
        $toggleReadConfigFile = true;
    }

    // read config file if necessary
    if ($toggleReadConfigFile) {
        $config_values = json_decode(file_get_contents($configFile), true);

        // create the base arrays if not already populated
        //TODO move this entire section to setup_default_config()
        if (!isset($config_values['Favorites'])) {
            $config_values['Favorites'] = [];
        }
        if (!isset($config_values['Hidden'])) {
            $config_values['Hidden'] = [];
        }
        if (!isset($config_values['Feeds'])) {
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
                    'Link' => 'https://eztv.wf/ezrss.xml',
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
                    'enabled' => 1,
                    'Name' => 'AniDex'
                ],
                5 => [
                    'Link' => 'https://www.acgnx.se/rss.xml',
                    'Type' => 'RSS',
                    'seedRatio' => "",
                    'enabled' => 1,
                    'Name' => 'AcgnX Torrent Resources Base.Global'
                ]
            ];
            writejSONConfigFile();
        }
        if (isset($config_values['Settings']['Time Zone']) && date_default_timezone_get() !== $config_values['Settings']['Time Zone']) {
            //TODO compare current timezone before writing
            $return = date_default_timezone_set($config_values['Settings']['Time Zone']);
            if ($return === false) {
                twxaDebug("Unable to set timezone to: " . $config_values['Settings']['Time Zone'] . "; using UTC instead\n", -1);
                date_default_timezone_set("UTC");
            } else {
                writejSONConfigFile();
            }
        }

        // write new cache file
        //TODO possibly remove $config_values['Global']['Feeds'] before writing config cache, but read_config_file() did not do so
        file_put_contents($configCache, serialize($config_values));
        chmod($configCache, 0660);
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
    //global $config_values;
    //return base64_decode(preg_replace('/^\$%&(.*)\$%&$/', '$1', $config_values['Settings']['SMTP Password']));
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
        twxaDebug("Creating configuration directory: $dir\n", 1);
        if (file_exists($dir)) {
            unlink($dir);
        }
        if (mkdir($dir)) { //TODO cannot create dir without proper permissions of parent directory! Check it first with fileperms(parent directory)
            if (chmod($dir, 0755)) {
                return true;
            } else {
                twxaDebug("Unable to chmod config directory: $dir\n", -1);
                return false;
            }
        } else {
            twxaDebug("Unable to create config directory: $dir\n", -1);
            return false;
        }
    }
}

function writejSONConfigFile() {
    global $config_values; //TODO remove use of global
    // replacement for old write_config_file() that avoids array_walk callbacks and recursion and uses PHP's JSON format

    $configFile = getConfigFile();
    $configCache = getConfigCache();

    if ($config_values['Settings']['debugLevel'] == 2) {
        twxaDebug(debug_backtrace()[1]['function'] . " calling to write config file: $configFile\n", 2);
    }

    set_client_passwd(); //TODO this should happen outside this function
    set_smtp_passwd(); //TODO this should happen outside this function
    // copy everything but $config_values['Global'] so that it doesn't pollute the config file
    $configOut = $config_values;
    unset($configOut['Global']);

    createConfigDir();

    if (file_put_contents($configFile . "_tmp", print_r(json_encode($configOut, JSON_PRETTY_PRINT), true), LOCK_EX) !== false) {
        if (file_exists($configFile . "_tmp")) {
            if (rename($configFile . "_tmp", $configFile)) {
                if (chmod($configFile, 0600)) {
                    if (file_exists($configCache)) {
                        twxaDebug("Removing config cache: $configCache\n", 2);
                        unlink($configCache);
                    }
                    twxaDebug("Successfully wrote config file: $configFile\n", 2);
                    return true;
                } else {
                    twxaDebug("Unable to chmod config file: $configFile\n", -1);
                    return false;
                }
            } else {
                twxaDebug("Unable to rename temp config file: $configFile" . "_tmp\n", -1);
                return false;
            }
        } else {
            twxaDebug("Unable to find temp config file to rename: $configFile" . "_tmp\n", -1);
        }
    } else {
        twxaDebug("Unable to write temp config file: $configFile" . "_tmp\n", -1);
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
        'Time Zone' => 'tz',
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
    } else {
        return;
    }
}

/* function add_hidden($name) {
  global $config_values;
  $guess = detectMatch($name);
  if ($guess) {
  $name = strtolower(trim(strtr($guess['title'], array(":" => "", "," => "", "'" => "", "." => " ", "_" => " "))));
  foreach ($config_values['Favorites'] as $fav) {
  if ($name == strtolower(strtr($fav['Name'], array(":" => "", "," => "", "'" => "", "." => " ", "_" => " ")))) {
  return($fav['Name'] . " exists in favorites. Not adding to hide list.");
  }
  }
  $config_values['Hidden'][$name] = 'hidden';
  writejSONConfigFile();
  } else {
  return("Bad form data, not added to favorites"); // Bad form data
  }
  } */

function addHidden($name) {
    global $config_values;
    if (!empty($name)) {
        $guess = detectMatch(html_entity_decode($name));
        if (!empty($guess['favTitle'])) {
            $lctitle = strtolower(trim(strtr($guess['favTitle'], array(":" => "", "," => "", "'" => "", "." => " ", "_" => " "))));
            foreach ($config_values['Favorites'] as $fav) {
                if ($lctitle == strtolower(strtr($fav['Name'], array(":" => "", "," => "", "'" => "", "." => " ", "_" => " ")))) {
                    twxaDebug($fav['Name'] . " exists in favorites. Not adding to hide list.\n", 0);
                    echo "twxa-ERROR:" . $fav['Name'] . " exists in favorites. Not adding to hide list.";
                    return;
                }
            }
            $config_values['Hidden'][strtolower($guess['favTitle'])] = $guess['favTitle'];
            writejSONConfigFile();
            twxaDebug("Hid: $name\n", 1);
            echo $guess['favTitle']; // use favTitle, not title
            return;
        }
    } else {
        twxaDebug("Nothing to hide--ignoring.\n", 0);
        echo "twxa-ERROR:Nothing to hide--ignoring.";
        return;
    }
}

function delHidden($list) {
    global $config_values;
    if (is_array($list)) { // !empty() is in the call to this function
        foreach ($list as $item) {
            if (isset($config_values['Hidden'][$item])) {
                unset($config_values['Hidden'][$item]);
                twxaDebug("Unhid: $item\n", 1);
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
        //$feedLink = str_replace(' ', '%20', $feedLink);
        //$feedLink = preg_replace('/^%20|%20$/', '', $feedLink);
        twxaDebug("Checking feed: $feedLink\n", 2);
        if ($guessedFeedType = guess_feed_type($feedLink) != 'Unknown') {
            twxaDebug("Adding feed: $feedLink\n", 1);
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
            twxaDebug("Could not connect to feed or guess feed type: $feedLink\n", -1);
        }
    } else {
        twxaDebug("Ignoring empty or invalid feed URL\n", 0);
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
                twxaDebug("Updating feed: $old_feedurl\n", 1);
                foreach ($config_values['Favorites'] as &$favorite) {
                    if ($favorite['Feed'] == $old_feedurl) {
                        $favorite['Feed'] = str_replace(' ', '%20', $feedLink);
                    }
                }
                $config_values['Feeds'][$index]['Name'] = $feedName;
                //$config_values['Feeds'][$index]['Link'] = preg_replace('/^%20|%20$/', '', str_replace(' ', '%20', $feedLink));
                $config_values['Feeds'][$index]['Link'] = $feedLink;
                $config_values['Feeds'][$index]['seedRatio'] = filter_input(INPUT_GET, 'seed_ratio');
                if (filter_input(INPUT_GET, 'feed_on') == "feed_on") {
                    $config_values['Feeds'][$index]['enabled'] = 1;
                } else {
                    $config_values['Feeds'][$index]['enabled'] = "";
                }
                writejSONConfigFile();
            } else {
                twxaDebug("Ignoring empty feed Name", 0);
            }
        } else {
            twxaDebug("Ignoring empty or invalid feed URL", 0);
        }
    } else {
        twxaDebug("Unable to update feed. Could not find feed index: $index\n", -1);
    }
}

function deleteFeed() {
    global $config_values;
    $index = filter_input(INPUT_GET, 'idx');
    if ($index !== false && isset($config_values['Feeds'][$index])) {
        twxaDebug("Deleting feed with index: $index\n", 1);
        unset($config_values['Feeds'][$index]);
        writejSONConfigFile();
    } else {
        twxaDebug("Unable to delete feed. Could not find feed index: $index\n", -1);
    }
}
