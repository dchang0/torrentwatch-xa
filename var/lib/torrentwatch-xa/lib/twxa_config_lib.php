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
    _default('Sanitize Hidelist', "0");
}

function read_config_file() {
    // This function is from
    // http://www.codewalkers.com/c/a/Miscellaneous/Configuration-File-Processing-with-PHP/2/
    // It has been modified to support multidimensional arrays in the form of
    // group[] = key => data as equivalent of group[key] => data
    global $config_values;
    $config_file = getConfigFile();
    $config_cache = getConfigCache();

    $comment = ";";
    $group = "NONE";

    if (!file_exists($config_file)) {
        twxaDebug("No config file found--creating default config at $config_file\n", 1);
        write_config_file();
    }

    if (file_exists($config_cache)) {
        $CacheAge = time() - filemtime($config_cache);
        $ConfigAge = time() - filemtime($config_file);
    }

    if (file_exists($config_cache) && $CacheAge <= 300 && $CacheAge <= $ConfigAge) {
        $config_values = unserialize(file_get_contents($config_cache));
        if (!$config_values['Settings']) {
            unlink($config_cache);
            read_config_file();
        }
    } else {
        if (!($fp = fopen($config_file, "r"))) {
            twxaDebug("Could not open $config_file\n", -1);
            exit(1);
        }

        if (flock($fp, LOCK_EX)) {
            while (!feof($fp)) {
                $line = trim(fgets($fp));
                if ($line && \strpos($line, $comment) !== 0) {
                    if (strpos($line, "[") === 0 && substr($line, -1) === "]") {
                        $line = trim(trim($line, "["), "]");
                        $group = trim($line);
                    } else {
                        $pieces = explode("=", $line, 2);
                        $pieces[0] = trim($pieces[0], "\"");
                        $pieces[1] = trim($pieces[1], "\"");
                        $option = trim($pieces[0]);
                        $value = trim($pieces[1]);
                        if (substr($option, -2) === "[]") {
                            $option = substr($option, 0, strlen($option) - 2);
                            $pieces = explode("=>", $value, 2);
                            if (isset($pieces[1])) {
                                $config_values[$group][$option][trim($pieces[0])] = trim($pieces[1]);
                            } else {
                                $config_values[$group][$option][] = $value;
                            }
                        } else {
                            $config_values[$group][$option] = $value;
                        }
                    }
                }
            }
            flock($fp, LOCK_UN);
        }
        fclose($fp);
        file_put_contents($config_cache, serialize($config_values));
        chmod($config_cache, 0660);
    }

    // Create the base arrays if not already

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
        write_config_file(); //TODO add error handling
    }
    if (isset($config_values['Settings']['Time Zone'])) {
        $return = date_default_timezone_set($config_values['Settings']['Time Zone']);
        if ($return === false) {
            twxaDebug("Unable to set timezone to: " . $config_values['Settings']['Time Zone'] . "; using UTC instead\n", -1);
            date_default_timezone_set("UTC");
        }
    }
    return true; //TODO add error handling
}

function get_client_passwd() {
    global $config_values;
    return base64_decode(preg_replace('/^\$%&(.*)\$%&$/', '$1', $config_values['Settings']['Transmission Password']));
}

function get_smtp_passwd() {
    global $config_values;
    return base64_decode(preg_replace('/^\$%&(.*)\$%&$/', '$1', $config_values['Settings']['SMTP Password']));
}

function write_config_file() {
    global $config_values, $config_out;
    $config_file = getConfigFile();
    $config_cache = getConfigCache();

    twxaDebug("Preparing to write config file to $config_file\n", 2);

    if (!(preg_match('/^\$%&(.*)\$%&$/', $config_values['Settings']['Transmission Password']))) {
        if ($config_values['Settings']['Transmission Password']) {
            $config_values['Settings']['Transmission Password'] = preg_replace('/^(.*)$/', '\$%&$1\$%&', base64_encode($config_values['Settings']['Transmission Password']));
        } else {
            $config_values['Settings']['Transmission Password'] = "";
        }
    }

    if (!(preg_match('/^\$%&(.*)\$%&$/', $config_values['Settings']['SMTP Password']))) {
        if ($config_values['Settings']['SMTP Password']) {
            $config_values['Settings']['SMTP Password'] = preg_replace('/^(.*)$/', '\$%&$1\$%&', base64_encode($config_values['Settings']['SMTP Password']));
        } else {
            $config_values['Settings']['SMTP Password'] = "";
        }
    }

    $config_out = ";;\n;; torrentwatch-xa config file\n;;\n\n";
    if (!function_exists('group_callback')) {

        function group_callback($group, $key) { // $group is used in key_callback() below
            global $config_values, $config_out;
            if ($key == 'Global') {
                return;
            }
            $config_out .= "[$key]\n";
            array_walk($config_values[$key], 'key_callback');
            $config_out .= "\n\n";
        }

    }

    if (!function_exists('key_callback')) {

        function key_callback($group, $key, $subkey = null) {
            global $config_out;
            if (is_array($group)) {
                array_walk($group, 'key_callback', $key . '[]');
            } else {
                if ($subkey) {
                    if (!is_numeric($key)) {
                        $group = "$key => $group";
                    }
                    $key = $subkey;
                }
                $config_out .= "$key = $group\n";
            }
        }

    }
    array_walk($config_values, 'group_callback');
    $dir = dirname($config_file);
    if (!is_dir($dir)) {
        twxaDebug("Creating configuration directory $dir\n", 1);
        if (file_exists($dir)) {
            unlink($dir);
        }
        if (!mkdir($dir)) {
            twxaDebug("Unable to create config directory $dir\n", -1);
            return false;
        }
    }
    $config_out = html_entity_decode($config_out);

    if (!($fp = fopen($config_file . "_tmp", "w"))) {
        twxaDebug("Could not open $config_file\n", -1);
        exit(1);
    }

    if (flock($fp, LOCK_EX)) {
        if (fwrite($fp, $config_out)) {
            flock($fp, LOCK_UN);
            rename($config_file . "_tmp", $config_file);
        }
        chmod($config_file, 0600);
        if (file_exists($config_cache)) {
            unlink($config_cache);
        }
    }
    unset($config_out);
}

function update_global_config() {
    global $config_values;
    $input = array(
        'Time Zone' => 'tz',
        'Client' => 'client',
        //'Torrent Extension' => 'torrentextension',
        //'Magnet Extension' => 'magnetextension',
        'Download Dir' => 'downdir',
        'Transmission Host' => 'trhost',
        'Transmission Port' => 'trport',
        'Transmission Login' => 'truser',
        'Transmission Password' => 'trpass',
        'Transmission URI' => 'truri',
        'Save Torrents Dir' => 'savetorrentsdir',
        'Deep Directories' => 'deepdir',
        'Default Seed Ratio' => 'defaultratio',
        'Match Style' => 'matchstyle',
        'Script' => 'script',
        'From Email' => 'fromEmail',
        'To Email' => 'toEmail',
        'SMTP Server' => 'smtpServer',
        'SMTP Port' => 'smtpPort',
        'SMTP Authentication' => 'smtpAuthentication',
        'SMTP Encryption' => 'smtpEncryption',
        'SMTP User' => 'smtpUser',
        'SMTP Password' => 'smtpPassword'
    );
    $checkboxes = array(
        'Combine Feeds' => 'combinefeeds',
        'Disable Hide List' => 'dishidelist',
        'Show Debug' => 'showdebug',
        'Hide Donate Button' => 'hidedonate',
        'Save Torrents' => 'savetorrents',
        'Auto-Del Seeded Torrents' => 'autodel',
        'Default Feed All' => 'favdefaultall',
        'Require Episode Info' => 'require_epi_info',
        'Only Newer' => 'onlynewer',
        'Download Versions' => 'fetchversions',
        'Ignore Batches' => 'ignorebatches',
        'Enable Script' => 'enableScript',
        'SMTP Notifications' => 'enableSMTP'
    );
    foreach ($input as $key => $data) {
        if (isset($_GET[$data])) {
            $config_values['Settings'][$key] = $_GET[$data];
        } // cannot overwrite $config_values['Settings'][$key] with null because $config_values['Settings']['truri'] isn't accessible via Configure pane
    }
    foreach ($checkboxes as $key => $data) {
        if (isset($_GET[$data])) {
            $config_values['Settings'][$key] = $_GET[$data];
        } else {
            $config_values['Settings'][$key] = null; // this works until there is a checkbox that doesn't get set by the Configure pane
        }
    }
    return;
}

function update_favorite() {
    if (!isset($_GET['button'])) {
        return;
    }
    switch ($_GET['button']) {
        case 'Add':
        case 'Update':
            $response = add_favorite();
            break;
        case 'Delete':
            del_favorite();
            break;
    }
    write_config_file();
    if (isset($response)) {
        return $response;
    } else {
        return;
    }
}

function update_feed() {
    if ($_GET['button'] == "Delete") {
        del_feed();
    } else if ($_GET['button'] == "Update") {
        update_feed_data();
    } else {
        $link = $_GET['link'];
        add_feed($link);
    }
    write_config_file();
}

function add_hidden($name) {
    global $config_values;
    $guess = detectMatch($name);
    if ($guess) {
        $name = strtolower(trim(strtr($guess['title'], array(":" => "", "," => "", "'" => "", "." => " ", "_" => " "))));

        foreach ($config_values['Favorites'] as $fav) {
            if ($name == strtolower(strtr($fav['Name'], array(":" => "", "," => "", "'" => "", "." => " ", "_" => " ")))) {
                return($fav['Name'] . " exists in favorites. Not adding to hide list.");
            }
        }
        if (isset($name)) {
            $config_values['Hidden'][$name] = 'hidden';
        } else {
            return("Bad form data, not added to favorites"); // Bad form data
        }
        write_config_file();
    } else {
        return("Unable to add $name to the hide list.");
    }
}

function del_hidden($list) {
    global $config_values;
    foreach ($list as $item) {
        if (isset($config_values['Hidden'][$item])) {
            unset($config_values['Hidden'][$item]);
        }
    }
    write_config_file();
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

function del_favorite() {
    global $config_values;
    if (isset($_GET['idx']) AND isset($config_values['Favorites'][$_GET['idx']])) {
        unset($config_values['Favorites'][$_GET['idx']]);
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
                    //$fav['Episode'] = $guess['episBatEnd'];
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
                    //$fav['Episode'] = $guess['episBatEnd'];
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
        write_config_file();
        return true; //TODO add error handling; until write_config_file() has a return value, we assume success and return true
    } else {
        return false;
    }
}

function add_feed($feedLink) {
    global $config_values;
    $feedLink = str_replace(' ', '%20', $feedLink);
    $feedLink = preg_replace('/^%20|%20$/', '', $feedLink);
    twxaDebug("Checking feed: $feedLink\n", 2);

    if (isset($feedLink) AND ( $guessedFeedType = guess_feed_type($feedLink)) != 'Unknown') {
        twxaDebug("Adding feed: $feedLink\n", 1);
        $config_values['Feeds'][]['Link'] = $feedLink;
        $arrayKeys = array_keys($config_values['Feeds']);
        $idx = end($arrayKeys);
        $config_values['Feeds'][$idx]['Type'] = $guessedFeedType;
        //$config_values['Feeds'][$idx]['seedRatio'] = $config_values['Settings']['Default Seed Ratio'];
        $config_values['Feeds'][$idx]['seedRatio'] = "";
        $config_values['Feeds'][$idx]['enabled'] = 1;
        load_all_feeds(array(0 => array('Type' => $guessedFeedType, 'Link' => $feedLink)), 1, true); // pass true for newly added feeds
        switch ($guessedFeedType) {
            case 'RSS':
                $config_values['Feeds'][$idx]['Name'] = $config_values['Global']['Feeds'][$feedLink]['title'];
                break;
            case 'Atom':
                $config_values['Feeds'][$idx]['Name'] = $config_values['Global']['Feeds'][$feedLink]['FEED']['TITLE'];
                break;
        }
    } else {
        twxaDebug("Could not connect to feed or guess feed type: $feedLink\n", -1);
    }
}

function update_feed_data() {
    global $config_values;
    if (isset($_GET['idx']) && isset($config_values['Feeds'][$_GET['idx']])) {
        if (!($_GET['feed_name']) || !($_GET['feed_link'])) {
            return;
        }

        $old_feedurl = $config_values['Feeds'][$_GET['idx']]['Link'];

        twxaDebug("Updating feed: $old_feedurl\n", 1);

        foreach ($config_values['Favorites'] as &$favorite) {
            if ($favorite['Feed'] == $old_feedurl) {
                $favorite['Feed'] = str_replace(' ', '%20', $_GET['feed_link']);
            }
        }

        $config_values['Feeds'][$_GET['idx']]['Name'] = $_GET['feed_name'];
        $config_values['Feeds'][$_GET['idx']]['Link'] = str_replace(' ', '%20', $_GET['feed_link']);
        $config_values['Feeds'][$_GET['idx']]['Link'] = preg_replace('/^%20|%20$/', '', $_GET['feed_link']);
        $config_values['Feeds'][$_GET['idx']]['seedRatio'] = $_GET['seed_ratio'];
        if (isset($_GET['feed_on']) && $_GET['feed_on'] == "feed_on") {
            $config_values['Feeds'][$_GET['idx']]['enabled'] = 1;
        } else {
            $config_values['Feeds'][$_GET['idx']]['enabled'] = "";
        }
    } else {
        twxaDebug("Unable to update feed. Could not find feed index: " . $_GET['idx'] . "\n", -1);
    }
}

function del_feed() {
    global $config_values;
    if (isset($_GET['idx']) && isset($config_values['Feeds'][$_GET['idx']])) {
        unset($config_values['Feeds'][$_GET['idx']]);
    } else {
        twxaDebug("Unable to delete feed. Could not find feed index: " . $_GET['idx'] . "\n", -1);
    }
}
