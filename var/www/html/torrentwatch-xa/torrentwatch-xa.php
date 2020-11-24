<?php

// disable any kind of caching
header("Expires: Mon, 20 Dec 2000 01:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

//error_reporting(E_ERROR | E_WARNING | E_PARSE);
error_reporting(E_ALL);

require_once("config.php");
require_once("twxa_tools.php");

$twxa_version[0] = "1.5.0";
$twxa_version[1] = php_uname("s") . " " . php_uname("r") . " " . php_uname("m");

if (get_magic_quotes_gpc()) {
    $process = [&$_GET, &$_POST, &$_COOKIE, &$_REQUEST];
    while (list($key, $val) = each($process)) {
        foreach ($val as $k => $v) {
            unset($process[$key][$k]);
            if (is_array($v)) {
                $process[$key][stripslashes($k)] = $v;
                $process[] = &$process[$key][stripslashes($k)];
            } else {
                $process[$key][stripslashes($k)] = stripslashes($v);
            }
        }
    }
    unset($process);
}

// parses commands sent from web UI (usually torrentwatch-xa.js)
function parse_options($twxa_version) {
    global $html_out, $config_values;

    array_keys($_GET);
    $commands = array_keys($_GET);
    if (empty($commands)) {
        return false;
    }

    if (strpos($commands[0], '/') === 0) {
        $commands[0] = preg_replace("/^\//", '', $commands[0]);
    }
    switch ($commands[0]) {
        case 'getClientData':
            echo getClientData();
            exit;
        case 'delTorrent':
            if (isset($_REQUEST['trash'])) {
                if (isset($_REQUEST['checkCache'])) {
                    $response = delTorrent($_REQUEST['delTorrent'], $_REQUEST['trash'], $_REQUEST['checkCache']);
                } else {
                    $response = delTorrent($_REQUEST['delTorrent'], $_REQUEST['trash'], false);
                }
            } else {
                if (isset($_REQUEST['checkCache'])) {
                    $response = delTorrent($_REQUEST['delTorrent'], false, $_REQUEST['checkCache']);
                } else {
                    $response = delTorrent($_REQUEST['delTorrent'], false, false);
                }
            }
            echo "$response";
            exit;
        case 'stopTorrent':
            echo stopTorrent($_REQUEST['stopTorrent']);
            exit;
        case 'startTorrent':
            echo startTorrent($_REQUEST['startTorrent']);
            exit;
        case 'moveTo':
            echo moveTorrent($_REQUEST['moveTo'], $_REQUEST['torHash']);
            exit;
        case 'updateFavorite':
            $response = update_favorite();
            if (strpos($response, 'Error:') === 0) {
                echo "<div id=\"fav_error\" class=\"dialog_window\" style=\"display: block\">$response</div>";
            }
            break;
        case 'updateFeed':
            updateFeed();
            break;
        case 'clearCache':
            clear_cache_by_cache_type();
            break;
        case 'setGlobals':
            updateGlobalConfig();
            break;
        case 'addFavorite':
            $feedLink = $_GET['rss'];
            foreach ($config_values['Feeds'] as $key => $feed) {
                if ($feed['Link'] == "$feedLink") {
                    $idx = $key;
                }
            }
            if (($tmp = detectMatch(html_entity_decode($_GET['title'])))) {
                $_GET['name'] = trim(strtr($tmp['favTitle'], "._", "  "));
                switch ($config_values['Settings']['Match Style']) {
                    case "simple":
                        $_GET['filter'] = trim($tmp['favTitle']);
                        $_GET['quality'] = $tmp['qualities']; // Add to Favorites uses the qualities from the item for the new Favorite
                        break;
                    case "glob":
                        $_GET['filter'] = trim(strtr($tmp['favTitle'], " ._", "???"));
                        $_GET['filter'] .= '*';
                        $_GET['quality'] = 'All'; // Add to Favorites makes the new Favorite accept all qualities
                        break;
                    case "regexp":
                        $_GET['filter'] = trim($tmp['favTitle']);
                        $_GET['quality'] = $tmp['qualitiesRegEx']; // Add to Favorites uses the detected qualities as a regex or .* if no qualities detected
                }
                $_GET['feed'] = $_GET['rss'];
                $_GET['button'] = 'Add';
                $_GET['downloaddir'] = '';
                $_GET['alsosavedir'] = '';
                $_GET['seedratio'] = "";
            } else {
                $_GET['name'] = $_GET['title'];
                $_GET['filter'] = $_GET['title'];
                $_GET['quality'] = 'All';
                $_GET['feed'] = $_GET['rss'];
                $_GET['button'] = 'Add';
                $_GET['downloaddir'] = '';
                $_GET['alsosavedir'] = '';
                $_GET['seedratio'] = "";
            }
            if ($config_values['Settings']['Default Feed All'] && $tmp['numberSequence'] > 0) { // set default feed to all only if serialized
                $_GET['feed'] = 'All';
            }
            $response = update_favorite();
            if ($response) {
                echo "$response";
            }
            exit;
        case 'hide':
            addHidden($_GET['hide']);
            exit;
        case 'delHidden':
            if (!empty($_GET['unhide'])) {
                delHidden($_GET['unhide']); // filter_input() will fail here because $_GET['unhide'] is an array
            }
            break;
        case 'dlTorrent':
            // Loaded via ajax
            foreach ($config_values['Favorites'] as $fav) {
                $guess = detectMatch(html_entity_decode($_GET['title']));
                $name = trim(strtr($guess['title'], "._", "  "));
                if ($name == $fav['Name']) {
                    $downloadDir = $fav['Download Dir'];
                }
            }
            if (
                    (
                    empty($downloadDir) ||
                    $downloadDir == "Default"
                    ) &&
                    !empty($config_values['Settings']['Download Dir'])
            ) {
                $downloadDir = $config_values['Settings']['Download Dir'];
            }
            $r = client_add_torrent(str_replace('/ /', '%20', trim($_GET['link'])), $downloadDir, $_GET['title'], $_GET['feed']);
            if ($r == "Success") {
                $torHash = get_torHash(add_cache(filter_input(INPUT_GET, 'title')));
            }
            if (isset($torHash)) {
                echo $torHash;
            } else {
                echo $r;
            }
            exit(0);
            break;
        case 'clearHistory':
            // Loaded via ajax
            $downloadHistoryFile = getDownloadHistoryFile();
            if (file_exists($downloadHistoryFile)) {
                unlink($downloadHistoryFile);
            }
            display_history();
            closeHtml();
            exit(0);
            break;
        case 'get_client':
            global $config_values;
            echo $config_values['Settings']['Client'];
            exit;
        case 'get_autodel':
            global $config_values;
            echo $config_values['Settings']['Auto-Del Seeded Torrents'];
            exit;
        case 'getDisableHideList':
            global $config_values;
            echo $config_values['Settings']['Disable Hide List'];
            exit;
        case 'sendTestEmail':
            $fromName = filter_input(INPUT_GET, 'fromName');
            $fromEmail = filter_input(INPUT_GET, 'fromEmail');
            $toEmail = filter_input(INPUT_GET, 'toEmail');
            $smtpServer = filter_input(INPUT_GET, 'smtpServer');
            $smtpPort = filter_input(INPUT_GET, 'smtpPort');
            $smtpAuthentication = filter_input(INPUT_GET, 'smtpAuthentication');
            $smtpEncryption = filter_input(INPUT_GET, 'smtpEncryption');
            $smtpUser = filter_input(INPUT_GET, 'smtpUser');
            $smtpPassword = filter_input(INPUT_GET, 'smtpPassword');
            $hiddensMTPPassword = str_repeat('*', strlen(decryptsMTPPassword($smtpPassword)));
            $hELOOverride = filter_input(INPUT_GET, 'hELOOverride');
            $subject = "Test email from torrentwatch-xa";
            $body = "Test email from torrentwatch-xa sent at " . date("c") . " with these settings:\n\nFrom: Name = $fromName\nFrom: Email = $fromEmail\nTo: Email = $toEmail\nSMTP Server = $smtpServer\nSMTP Port = $smtpPort\nSMTP Authentication = $smtpAuthentication\nSMTP Encryption = $smtpEncryption\nSMTP User = $smtpUser\nSMTP Password = $hiddensMTPPassword\nHELO Override = $hELOOverride\n\nThe Configure > Trigger > Test button does not save these settings to the config file. Remember to click the Configure > Trigger > Save button if necessary.";
            $output = sendEmail($fromName, $fromEmail, $toEmail, $smtpServer, $smtpPort, $smtpAuthentication, $smtpEncryption, $smtpUser, $smtpPassword, $hELOOverride, $subject, $body);
            echo $output['message'];
            exit;
        case 'checkVersion':
            global $config_values;
            if ($config_values['Settings']['Check for Updates'] == 1) {
                echo checkVersion($twxa_version);
            }
            exit;
        case 'get_dialog_data':
            switch (filter_input(INPUT_GET, 'get_dialog_data')) {
                case '#favorites':
                    display_favorites();
                    exit;
                case '#configuration':
                    display_global_config();
                    exit;
                case '#history':
                    display_history();
                    exit;
                case '#show_legend':
                    display_legend();
                    exit;
                case '#clear_cache':
                    display_clearCache();
                    exit;
                case '#show_transmission':
                    display_transmission();
                    exit;
                default:
                    exit;
            }
        default:
            $phpSelf = filter_input(INPUT_SERVER, 'PHP_SELF');
            $requestuRI = filter_input(INPUT_SERVER, 'REQUEST_URI');
            if ($phpSelf !== false && $requestuRI !== false) {
                $output = "<script type='text/javascript'>alert('Bad parameters passed to $phpSelf:  $requestuRI');</script>";
            } else {
                $output = "<script type='text/javascript'>alert('Bad parameters');</script>";
            }
    }

    if (isset($output)) {
        if (is_array($output)) {
            $output = implode("<br>", $output);
        }
        $html_out .= str_replace("\n", "<br>", "<div class='execoutput'>$output</div>");
        echo $html_out;
        $html_out = "";
    }
}

function display_global_config() {
    global $config_values;

// Interface tab
    $combinefeeds = $dishidelist = $showdebug = $hidedonate = $checkversion = '';
    $loglevelalert = $loglevelerror = $loglevelinfo = $logleveldebug = '';
    if ($config_values['Settings']['Combine Feeds'] == 1) {
        $combinefeeds = 'checked=1';
    }
    if ($config_values['Settings']['Disable Hide List'] == 1) {
        $dishidelist = 'checked=1';
    }
    if ($config_values['Settings']['Show Debug'] == 1) {
        $showdebug = 'checked=1';
    }
    if ($config_values['Settings']['Hide Donate Button'] == 1) {
        $hidedonate = 'checked=1';
    }
    if ($config_values['Settings']['Check for Updates'] == 1) {
        $checkversion = 'checked=1';
    }
    switch ($config_values['Settings']['Log Level']) {
        case '2':
            $logleveldebug = 'selected="selected"';
            break;
        case '1':
            $loglevelinfo = 'selected="selected"';
            break;
        case '-1':
            $loglevelalert = 'selected="selected"';
            break;
        case '0':
        default:
            $loglevelerror = 'selected="selected"';
    }

// Client tab
    $transmission = $folderclient = $savetorrents = '';
    switch ($config_values['Settings']['Client']) {
        case "Transmission":
            $transmission = 'selected="selected"';
            break;
        case "folder":
            $folderclient = 'selected="selected"';
    }
    if ($config_values['Settings']['Save Torrents'] == 1) {
        $savetorrents = 'checked=1';
    }

// Torrent tab
    $deeptitle = $deepTitleSeason = $deepoff = '';
    $autodel = '';
    switch ($config_values['Settings']['Deep Directories']) {
        case 'Title': $deeptitle = 'selected="selected"';
            break;
        case 'Title_Season': $deepTitleSeason = 'selected="selected"';
            break;
        default: $deepoff = 'selected="selected"';
    }
    if ($config_values['Settings']['Auto-Del Seeded Torrents'] == 1) {
        $autodel = 'checked=1';
    }

// Favorites tab
    $matchregexp = $matchglob = $matchsimple = $resoallqualities = $resoresolutionsonly = '';
    $favdefaultall = $require_epi_info = $onlynewer = $fetchversions = $ignorebatches = '';
    switch ($config_values['Settings']['Match Style']) {
        case 'glob': $matchglob = "selected='selected'";
            break;
        case 'simple': $matchsimple = "selected='selected'";
            break;
        case 'regexp':
        default: $matchregexp = "selected='selected'";
    }
    if ($config_values['Settings']['Default Feed All'] == 1) {
        $favdefaultall = 'checked=1';
    }
    if ($config_values['Settings']['Require Episode Info'] == 1) {
        $require_epi_info = 'checked=1';
    }
    if ($config_values['Settings']['Only Newer'] == 1) {
        $onlynewer = 'checked=1';
    }
    if ($config_values['Settings']['Download Versions'] == 1) {
        $fetchversions = 'checked=1';
    }
    if ($config_values['Settings']['Ignore Batches'] == 1) {
        $ignorebatches = 'checked=1';
    }
    switch ($config_values['Settings']['Resolutions Only']) {
        case 'yes': $resoresolutionsonly = "selected='selected'";
            break;
        case 'all':
        default: $resoallqualities = "selected='selected'";
    }

// Trigger tab
    $enableScript = $enableSMTP = '';
    $smtpAuthNone = $smtpAuthLOGIN = $smtpAuthPLAIN = '';
    $smtpEncNone = $smtpEncTLS = $smtpEncSSL = '';
    if ($config_values['Settings']['Enable Script'] == 1) {
        $enableScript = 'checked=1';
    }
    if ($config_values['Settings']['SMTP Notifications'] == 1) {
        $enableSMTP = 'checked=1';
    }
    switch ($config_values['Settings']['SMTP Authentication']) {
        case 'None':
            $smtpAuthNone = 'selected="selected"';
            break;
        case 'LOGIN':
            $smtpAuthLOGIN = 'selected="selected"';
            break;
        case 'PLAIN':
            $smtpAuthPLAIN = 'selected="selected"';
    }
    switch ($config_values['Settings']['SMTP Encryption']) {
        case 'None':
            $smtpEncNone = 'selected="selected"';
            break;
        case 'TLS':
            $smtpEncTLS = 'selected="selected"';
            break;
        case 'SSL':
            $smtpEncSSL = 'selected="selected"';
    }

// Include the templates and append the results to html_out
    ob_start();
    require('templates/global_config.tpl');
    return ob_get_contents();
}

function display_favorites_info($item, $key) { // $key gets fed into favorites_info.tpl
    global $config_values;
    $feed_options = '<option value="none">None</option>';
    $feed_options .= '<option value="all"';
    if (strtolower($item['Feed']) === "all" || $item['Name'] === "") {
        $feed_options .= ' selected="selected">All</option>';
    } else {
        $feed_options .= '>All</option>';
    }
    if (isset($config_values['Feeds'])) {
        foreach ($config_values['Feeds'] as $feed) {
            $feed_options .= '<option value="' . urlencode($feed['Link']) . '"';
            if ($feed['Link'] == $item['Feed']) {
                $feed_options .= ' selected="selected"';
            }
            if ($feed['enabled'] !== 1) {
                $feed_options .= ' disabled';
            }
            $feed_options .= '>' . $feed['Name'] . '</option>';
        }
    }
// Dont handle with object buffer, is called inside display_favorites ob_start
    require('templates/favorites_info.tpl');
}

function display_favorites() {
    global $config_values, $html_out;
    ob_start();
    require('templates/favorites.tpl');
    return ob_get_contents();
}

function update_hidelist() {
    global $config_values;
    foreach ($config_values['Hidden'] as $key => $hidden) {
        unset($config_values['Hidden'][$key]);
        $config_values['Hidden'][strtolower(strtr($key, [":" => "", "," => "", "'" => "", "." => " ", "_" => " "]))] = "hidden";
    }
}

function display_history() {
    global $html_out;
    $downloadHistoryFile = getDownloadHistoryFile();
    if (file_exists($downloadHistoryFile)) {
        $historyContents = unserialize(file_get_contents($downloadHistoryFile));
        if ($historyContents === false) {
            writeToLog("Unable to unserialize history file: $downloadHistoryFile\n", 0);
            $history = [];
        } else {
            $history = array_reverse($historyContents);
        }
    } else {
        $history = [];
    }
    ob_start();
    require('templates/history.tpl');
    return ob_get_contents();
}

function display_legend() {
    global $html_out;
    ob_start();
    require('templates/legend.tpl');
    return ob_get_contents();
}

function display_transmission() {
    global $html_out;
    $host = getTransmissionWebuRL();
    ob_start();
    require('templates/transmission.tpl');
    return ob_get_contents();
}

function display_clearCache() {
    global $html_out;
    ob_start();
    require('templates/clear_cache.tpl');
    return ob_get_contents();
}

function checkpHPRequirements() {
    if (!(function_exists('json_encode'))) {
        outputErrorDialog("No JSON support found in your PHP installation.<br>Try installing php5-json and restart the web server.");
        return 1;
    }
    if (!(function_exists('curl_init'))) {
        outputErrorDialog("No cURL support found in your PHP installation.<br/>Try installing php-curl or php5-curl and restart the web server.");
        return 1;
    }
    if (!(function_exists('mb_convert_kana'))) {
        outputErrorDialog("No mbstring (multibyte string) support found in your PHP installation.<br/>Try installing php-mbstring and restart the web server.");
        return 1;
    }
    if (!(function_exists('posix_getuid'))) {
        outputErrorDialog("No posix_getuid() support found in your PHP installation.<br/>Try installing php-process and restart the web server.");
        return 1;
    }
}

function checkFilesAndDirs() {
    global $config_values;

    $myuID = posix_getuid();
    $configCacheDir = getConfigCacheDir();
    $configFile = getConfigFile();
    $configCacheFile = getConfigCacheFile();
    $downloadCacheDir = getDownloadCacheDir();
    $downloadHistoryFile = getDownloadHistoryFile();
    $downloadDir = $config_values['Settings']['Download Dir'];
    $saveTorrentsDir = $config_values['Settings']['Save Torrents Dir'];

    // only check DownloadDir if it is local and client is folder
    if ($config_values['Settings']['Client'] === "folder") {
        $checkLocalDownloadDir = true;
    } else {
        $checkLocalDownloadDir = false;
    }
    // only check Save Torrents Dir if Save Torrents is on
    if (
            $config_values['Settings']['Client'] !== "folder" &&
            $config_values['Settings']['Save Torrents'] &&
            !empty($config_values['Settings']['Save Torrents Dir'])
    ) {
        $checkSaveTorrentsDir = true;
    } else {
        $checkSaveTorrentsDir = false;
    }

    $errorMessage = false;
    // cascade through checks
    switch (true) {
        case true:
            if (!file_exists($configCacheDir)) {
                $errorMessage .= "ConfigCacheDir <b>$configCacheDir</b> not found.<br/>";
                break;
            }
        case true:
            if (!is_dir($configCacheDir)) {
                $errorMessage .= "ConfigCacheDir <b>$configCacheDir</b> isn't a directory.<br/>";
                break;
            }
        case true:
            $temp = checkPathReadableAndWriteable($configCacheDir, "ConfigCacheDir", $myuID);
            if (!empty($temp)) {
                $errorMessage .= $temp;
                break;
            }
        case true:
            // check ConfigFile file; if it exists, it needs to be readable and writable
            if (is_file($configFile)) {
                $temp = checkPathReadableAndWriteable($configFile, "ConfigFile", $myuID);
                if (!empty($temp)) {
                    $errorMessage .= $temp;
                    break;
                }
            }
        case true:
            // check ConfigCacheFile file; if it exists, it needs to be readable and writable
            if (is_file($configCacheFile)) {
                $temp = checkPathReadableAndWriteable($configCacheFile, "ConfigCacheFile", $myuID);
                if (!empty($temp)) {
                    $errorMessage .= $temp;
                    break;
                }
            }
        case true:
            if (!file_exists($downloadCacheDir)) {
                $errorMessage .= "DownloadCacheDir <b>$downloadCacheDir</b> not found.<br/>";
                break;
            }
        case true:
            if (!is_dir($downloadCacheDir)) {
                $errorMessage .= "DownloadCacheDir <b>$downloadCacheDir</b> isn't a directory.<br/>";
                break;
            }
        case true:
            $temp = checkPathReadableAndWriteable($downloadCacheDir, "DownloadCacheDir", $myuID);
            if (!empty($temp)) {
                $errorMessage .= $temp;
                break;
            }
        case true:
            // check download history file; if it exists, it needs to be readable and writable
            if (is_file($downloadHistoryFile)) {
                $temp = checkPathReadableAndWriteable($downloadHistoryFile, "DownloadHistoryFile", $myuID);
                if (!empty($temp)) {
                    $errorMessage .= $temp;
                    break;
                }
            }
        case true:
            // if local, check DownloadDir
            if ($checkLocalDownloadDir) {
                if (!file_exists($downloadDir)) {
                    $errorMessage .= "DownloadDir <b>$downloadDir</b> not found.<br/>";
                    break;
                }
            }
        case true:
            // if local, check DownloadDir
            if ($checkLocalDownloadDir) {
                if (!is_dir($downloadDir)) {
                    $errorMessage .= "DownloadDir <b>$downloadDir</b> isn't a directory.<br/>";
                    break;
                }
            }
        case true:
            // if local, check DownloadDir
            if ($checkLocalDownloadDir) {
                $temp = checkPathReadableAndWriteable($downloadDir, "DownloadDir", $myuID);
                if (!empty($temp)) {
                    $errorMessage .= $temp;
                    break;
                }
            }
        case true:
            // if Save Torrents is on, check 'Save Torrents Dir'
            if ($checkSaveTorrentsDir) {
                if (!file_exists($saveTorrentsDir)) {
                    $errorMessage .= "Save Torrents Dir <b>$saveTorrentsDir</b> not found.<br/>";
                    break;
                }
            }
        case true:
            // if Save Torrents is on, check 'Save Torrents Dir'
            if ($checkSaveTorrentsDir) {
                if (!is_dir($saveTorrentsDir)) {
                    $errorMessage .= "Save Torrents Dir <b>$saveTorrentsDir</b> isn't a directory.<br/>";
                    break;
                }
            }
        case true:
            // if Save Torrents is on, check 'Save Torrents Dir'
            if ($checkSaveTorrentsDir) {
                $temp = checkPathReadableAndWriteable($saveTorrentsDir, "Save Torrents Dir", $myuID);
                if (!empty($temp)) {
                    $errorMessage .= $temp;
                    break;
                }
            }
        //TODO check all Also Save Dir paths in all Favorites
    }

    // output any errors to the web UI
    if ($errorMessage) {
        outputErrorDialog($errorMessage);
    }
}

function checkPathReadableAndWriteable($path, $description, $uID) {
    // check if path is readable and writable
    if (!is_readable($path) && !is_writeable($path)) {
        return "UID $uID can't read or write $description <b>$path</b><br/>";
    } else {
        // check if path is readable
        if (!is_readable($path)) {
            return "UID $uID can't read $description <b>$path</b><br/>";
        }
        // check if path is writeable
        if (!is_writeable($path)) {
            return "UID $uID can't write $description <b>$path</b><br/>";
        }
    }
}

function checkVersion($twxa_version) {
    if (!isset($_COOKIE['VERSION-CHECK'])) {
        $get = curl_init();
        $curlOptions[CURLOPT_URL] = 'http://silverlakecorp.com/torrentwatch-xa/VERSION.txt';
        $curlOptions[CURLOPT_USERAGENT] = "torrentwatch-xa/$twxa_version[0] ($twxa_version[1])";
        getcURLDefaults($curlOptions);
        curl_setopt_array($get, $curlOptions);
        $latestFromWebsite = curl_exec($get);
        curl_close($get);
        $isLatestHigher = false;
        $thisVersion = explode(".", $twxa_version[0]);
        $latestVersion = explode(".", $latestFromWebsite);

        // Assume there are 3 numeric parts to the version number; compare them part by part
        if ((int)$thisVersion[0] > (int)$latestVersion[0]) {
            //$isLatestHigher = false;
        } else if ((int)$thisVersion[0] === (int)$latestVersion[0]) {
            // first parts are the same, compare the second parts
            if ((int)$thisVersion[1] > (int)$latestVersion[1]) {
                //$isLatestHigher = false;
            } else if ((int)$thisVersion[1] === (int)$latestVersion[1]) {
                // second parts are the same, compare the third parts
                if ((int)$thisVersion[2] >= (int)$latestVersion[2]) {
                    //$isLatestHigher = false;
                } else if ((int)$thisVersion[2] < (int)$latestVersion[2]) {
                    $isLatestHigher = true;
                } else {
                    // one of the values is non-numeric
                }
            } else if ((int)$thisVersion[1] < (int)$latestVersion[1]) {
                $isLatestHigher = true;
            } else {
                // one of the values is non-numeric
            }
        } else if ((int)$thisVersion[0] < (int)$latestVersion[0]) {
            $isLatestHigher = true;
        } else {
            // one of the values is non-numeric
        }

        if ($isLatestHigher) {
            return "<div id=\"newVersion\" class=\"dialog_window\" style=\"display: block\">torrentwatch-xa $latestFromWebsite is available.
                   Click <a href=\"https://github.com/dchang0/torrentwatch-xa/\">here</a> for more information.</div>";
        }
    }
}

function outputClient() {
    global $config_values;
    echo "<div id='clientId' class='hidden'>";
    echo $config_values['Settings']['Client'];
    echo "</div>";
}

function closehTML() {
    global $html_out;
    echo $html_out;
    $html_out = "";
}

function outputErrorDialog($message) {
    echo "<div id=\"errorDialog\" class=\"dialog_window\" style=\"display: block\">" . $message . "</div>";
}

/// main

$main_timer = getElapsedMicrotime(0);
readjSONConfigFile();

$config_values['Global']['HTMLOutput'] = 1;
$html_out = "";

parse_options($twxa_version);
if (checkpHPRequirements()) {
    return;
}
checkFilesAndDirs();
closehTML();
//flush();

writeToLog("=====torrentwatch-xa.php started running at $main_timer\n", 2); // cannot put this line any earlier

load_all_feeds($config_values['Feeds']);
process_all_feeds($config_values['Feeds']);

outputClient();
closehTML();

echo "<div id=\"footer\">Thank you for enjoying <a href=\"https://github.com/dchang0/torrentwatch-xa/\" target=\"_blank\"><img id=\"footerLogo\" src=\"images/torrentwatch-xa-logo16@2x.png\" alt=\"torrentwatch-xa logo\" width=\"16\" height=\"16\"/></a> <a href=\"https://github.com/dchang0/torrentwatch-xa/\" target=\"_blank\">$twxa_version[0]</a>!&nbsp;Please <a href=\"https://github.com/dchang0/torrentwatch-xa/issues\" target=\"_blank\">report bugs here</a>.</div>";

if ($config_values['Settings']['Hide Donate Button'] != 1) {
    echo '<div id="donate"><form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_donations">
<input type="hidden" name="business" value="foss@silverlakecorp.com">
<input type="hidden" name="lc" value="US">
<input type="hidden" name="item_name" value="foss@silverlakecorp.com">
<input type="hidden" name="item_number" value="torrentwatch-xa">
<input type="hidden" name="currency_code" value="USD">
<input type="hidden" name="bn" value="PP-DonationsBF:btn_donate_SM.gif:NonHostedGuest">
<input type="image" src="images/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
</form></div>';
}

writeToLog("=====torrentwatch-xa.php finished running in " . getElapsedMicrotime($main_timer) . "s\n", 2);
exit(0);
