<?php

// disable any kind of caching
header("Expires: Mon, 20 Dec 2000 01:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

//error_reporting(E_ERROR | E_WARNING | E_PARSE);
error_reporting(E_ALL);

$twxaIncludePaths = ["/var/lib/torrentwatch-xa/lib"];
$includePath = get_include_path();
foreach ($twxaIncludePaths as $twxaIncludePath) {
    if (strpos($includePath, $twxaIncludePath) === false) {
        $includePath .= PATH_SEPARATOR . $twxaIncludePath;
    }
}
set_include_path($includePath);
require_once("twxa_tools.php");

$twxa_version[0] = "0.8.0";
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
            update_feed();
            break;
        case 'clearCache':
            clear_cache_by_cache_type();
            break;
        case 'setGlobals':
            updateGlobalConfig();
            writejSONConfigFile();
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
                $_GET['downloaddir'] = 'Default';
                $_GET['alsosavedir'] = 'Default';
                $_GET['seedratio'] = "";
            } else {
                $_GET['name'] = $_GET['title'];
                $_GET['filter'] = $_GET['title'];
                $_GET['quality'] = 'All';
                $_GET['feed'] = $_GET['rss'];
                $_GET['button'] = 'Add';
                $_GET['downloaddir'] = 'Default';
                $_GET['alsosavedir'] = 'Default';
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
            $response = add_hidden(ucwords($_GET['hide']));
            if ($response) {
                echo "ERROR:$response";
            } else {
                $guess = detectMatch(html_entity_decode($_GET['hide']));
                echo $guess['favTitle']; // use favTitle, not title
            }
            exit;
        case 'delHidden':
            del_hidden($_GET['unhide']);
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
            if ((!isset($downloadDir) || $downloadDir == "Default" ) &&
                    isset($config_values['Settings']['Download Dir'])) {
                $downloadDir = $config_values['Settings']['Download Dir'];
            }
            $r = client_add_torrent(str_replace('/ /', '%20', trim($_GET['link'])), $downloadDir, $_GET['title'], $_GET['feed']);
            if ($r == "Success") {
                //$torHash = get_torHash(add_cache($_GET['title']));
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
            if (file_exists($config_values['Settings']['History'])) {
                unlink($config_values['Settings']['History']);
            }
            display_history();
            close_html();
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
        case 'checkVersion':
            echo checkVersion($twxa_version);
            exit;
        case 'get_dialog_data':
            //switch ($_GET['get_dialog_data']) {
            switch (filter_input(INPUT_GET, 'get_dialog_data')) {
                case '#favorites':
                    display_favorites();
                    exit;
                case '#configuration':
                    display_global_config();
                    exit;
                case '#hidelist':
                    display_hidelist();
                    exit;
                case '#feeds':
                    display_feeds();
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
            //TODO check filter_input() is not false before using it
            $output = "<script type='text/javascript'>alert('Bad Parameters passed to " . filter_input(INPUT_SERVER, 'PHP_SELF') . ":  " . filter_input(INPUT_SERVER, 'REQUEST_URI') . "');</script>";
    }

    if (isset($output)) {
        if (is_array($output)) {
            $output = implode("<br>", $output);
        }
        $html_out .= str_replace("\n", "<br>", "<div class='execoutput'>$output</div>");
        echo $html_out;
        $html_out = "";
    }
    return;
}

function display_global_config() {
    global $config_values;

    // Interface tab
    $combinefeeds = $dishidelist = $showdebug = $hidedonate = '';
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
    $deepfull = $deeptitle = $deepTitleSeason = $deepoff = '';
    $autodel = '';
    switch ($config_values['Settings']['Deep Directories']) {
        case 'Full': $deepfull = 'selected="selected"';
            break;
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

function display_hidelist() {
    global $config_values, $html_out;
    ob_start();
    require('templates/hidelist.tpl');
    return ob_get_contents();
}

function update_hidelist() {
    global $config_values;
    foreach ($config_values['Hidden'] as $key => $hidden) {
        unset($config_values['Hidden'][$key]);
        $config_values['Hidden'][strtolower(strtr($key, [":" => "", "," => "", "'" => "", "." => " ", "_" => " "]))] = "hidden";
    }
    return;
}

function display_feeds() {
    global $config_values, $html_out;
    ob_start();
    require('templates/feeds.tpl');
    return ob_get_contents();
}

function display_history() {
    global $html_out, $config_values;
    if (file_exists($config_values['Settings']['History'])) {
        $history = array_reverse(unserialize(file_get_contents($config_values['Settings']['History'])));
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
    $host = get_tr_location();
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

function close_html() {
    global $html_out;
    echo $html_out;
    $html_out = "";
}

function check_requirements() {
    if (!(function_exists('json_encode'))) {
        echo "<div id=\"errorDialog\" class=\"dialog_window\" style=\"display: block\">
            No JSON support found in your PHP installation.<br>
	    In Ubuntu 14.04 or Debian 8.x, install php5-json and restart Apache2.</div>";
        return 1;
    }
    if (!(function_exists('curl_init'))) {
        echo "<div id=\"errorDialog\" class=\"dialog_window\" style=\"display: block\">
            No cURL support found in your PHP installation. In Ubuntu 14.04 or Debian 8.x install php5-curl and restart Apache2.</div>";
        return 1;
    }
    if (!(function_exists('mb_convert_kana'))) {
        echo "<div id=\"errorDialog\" class=\"dialog_window\" style=\"display: block\">
            No mbstring (multibyte string) support found in your PHP installation. In Ubuntu 16.04 or Fedora install php-mbstring and restart Apache2.</div>";
        return 1;
    }
    if (!(function_exists('posix_getuid'))) {
        echo "<div id=\"errorDialog\" class=\"dialog_window\" style=\"display: block\">
            No posix_getuid() support found in your PHP installation. In Fedora install php-process and restart Apache2.</div>";
        return 1;
    }
}

function check_files() {
    global $config_values;

    $myuid = posix_getuid();
    $configDir = getConfigCacheDir() . '/';
    if (!is_writable($configDir)) {
        echo "<div id=\"errorDialog\" class=\"dialog_window\" style=\"display: block\">Please create the directory $configDir and make sure it's readable and writeable for the user running the webserver (uid: $myuid). </div>";
    }
    $cwd = getcwd();
    if (!(get_webDir() == $cwd)) {
        echo "<div id=\"errorDialog\" class=\"dialog_window\" style=\"display: block\">Please edit " . get_baseDir() . "/config.php and change the webDir from " . get_webDir() . " to:<br /> \"$cwd\".<br />Then click your browser's reload button.</div>";
        return;
    }

    $toCheck['cache_dir'] = $config_values['Settings']['Cache Dir'];
    if (strtolower($config_values['Settings']['Transmission Host']) == 'localhost' ||
            $config_values['Settings']['Transmission Host'] == '127.0.0.1') {
        $toCheck['download_dir'] = $config_values['Settings']['Download Dir'];
    }

    $deepDir = $config_values['Settings']['Deep Directories'];

    $error = false;
    foreach ($toCheck as $key => $file) {
        if (!(file_exists($file))) {
            $error .= "$key:&nbsp;<i>\"$file\"</i>&nbsp;&nbsp;does not exist <br />";
        }
        if (!($deepDir) && $key == 'download_dir') {
            break;
        }
        if (!(is_writable($file))) {
            $error .= "$key:&nbsp;<i>\"$file\"</i>&nbsp;&nbsp;is not writable for uid: $myuid <br />";
        }
        if (!(is_readable($file))) {
            $error .= "$key:&nbsp;<i>\"$file\"</i>&nbsp;&nbsp;is not readable for uid: $myuid <br />";
        }
    }

    if ($error) {
        echo "<div id=\"errorDialog\" class=\"dialog_window\" style=\"display: block\">$error</div>";
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
        $maxPartCount = max(count($thisVersion), count($latestVersion));
        for ($i = 0; $i < $maxPartCount; $i++) {
            if (isset($thisVersion[$i])) {
                if (isset($latestVersion[$i])) {
                    if ($latestVersion[$i] + 0 > $thisVersion[$i] + 0) {
                        $isLatestHigher = true;
                    }
                }
            } else {
                if (isset($latestVersion[$i])) {
                    if ($latestVersion[$i] + 0 > 0) {
                        $isLatestHigher = true;
                    }
                }
            }
        }
        if ($isLatestHigher) {
            return "<div id=\"newVersion\" class=\"dialog_window\" style=\"display: block\">torrentwatch-xa $latestFromWebsite is available.
                   Click <a href=\"https://github.com/dchang0/torrentwatch-xa/\">here</a> for more information.</div>";
        }
    }
}

function get_tr_location() {
    global $config_values;
    $host = $config_values['Settings']['Transmission Host'];
    if (preg_match('/(localhost|127\.0\.0\.1)/', $host)) {
        //$host = preg_replace('/:.*/', "", $_SERVER['HTTP_HOST']);
        $host = preg_replace('/:.*/', "", filter_input(INPUT_SERVER, 'HTTP_HOST'));
    }
    if (preg_match('/(localhost|127\.0\.0\.1)/', $host)) {
        //$host = preg_replace('/:.*/', "", $_SERVER['SERVER_NAME']);
        $host = preg_replace('/:.*/', "", filter_input(INPUT_SERVER, 'SERVER_NAME'));
    }
    $host = $host . ':' . $config_values['Settings']['Transmission Port'] . "/transmission/web/";
    return $host;
}

function get_client() {
    global $config_values;
    echo "<div id='clientId' class='hidden'>";
    echo $config_values['Settings']['Client'];
    echo "</div>";
}

/// main

$main_timer = getElapsedMicrotime(0);
setup_default_config();
readjSONConfigFile();
if (!isset($config_values['Settings']['Sanitize Hidelist']) || $config_values['Settings']['Sanitize Hidelist'] != 1) {
    // cleans titles of items in hidelist of most symbols
    update_hidelist();
    $config_values['Settings']['Sanitize Hidelist'] = 1;
    twxaDebug("Updated Hide List\n", 2);
    writejSONConfigFile();
}
//authenticateFeeds();

$config_values['Global']['HTMLOutput'] = 1;
$html_out = "";

parse_options($twxa_version);
if (check_requirements()) {
    return;
}
check_files();

echo $html_out;
$html_out = "";
flush();
twxaDebug("=====torrentwatch-xa.php started running at $main_timer\n", 2);
// Feeds
load_all_feeds($config_values['Feeds']);
process_all_feeds($config_values['Feeds']);

get_client();
close_html();

$footer = "Thank you for enjoying <a href=\"https://github.com/dchang0/torrentwatch-xa/\" target=\"_blank\"><img id=\"footerLogo\" src=\"images/torrentwatch-xa-logo16@2x.png\" alt=\"torrentwatch-xa logo\" width=\"16\" height=\"16\"/></a> <a href=\"https://github.com/dchang0/torrentwatch-xa/\" target=\"_blank\">$twxa_version[0]</a>!&nbsp;Please <a href=\"https://github.com/dchang0/torrentwatch-xa/issues\" target=\"_blank\">report bugs here</a>.";
echo "<div id=\"footer\">$footer</div>";

if ($config_values['Settings']['Hide Donate Button'] != 1) {
    echo '<div id="donate">
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
        <input type="hidden" name="cmd" value="_s-xclick">
        <input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
        <input type="image" src="images/donate-button.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
        </form>
    </div>';
}

twxaDebug("=====torrentwatch-xa.php finished running in " . getElapsedMicrotime($main_timer) . "s\n", 2);
exit(0);
