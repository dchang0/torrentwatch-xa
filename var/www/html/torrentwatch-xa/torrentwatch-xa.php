<?php

// disable any kind of caching
header("Expires: Mon, 20 Dec 2000 01:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

ini_set('include_path', '.:./php');
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
error_reporting(E_ALL);
require_once('/var/lib/torrentwatch-xa/lib/rss_dl_utils.php'); //TODO switch this to use get_base_dir()

// TVDB and TMDB Disabled for now
/*
  require_once('api/TMDb.php');
  require_once('api/TVDB.php');
 */

global $platform;

$twxa_version[0] = "0.2.3";

$twxa_version[1] = php_uname("s") . " " . php_uname("r") . " " . php_uname("m");

$test_run = 0;

if (get_magic_quotes_gpc()) {
    $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
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

if (!(file_exists('/var/lib/torrentwatch-xa/config.php'))) { //TODO set to use baseDir
    $config = '/var/lib/torrentwatch-xa/config.php';
    echo "<div id=\"errorDialog\" class=\"dialog_window\" style=\"display: block\">Please copy $config.dist to $config and edit it to match your environment. Then click your browser's reload button.</div>";
    return;
}

// This function parses commands sent from a PC browser
function parse_options() {

    global $html_out, $config_values;
    $filler = "<br>";

    array_keys($_GET);
    $commands = array_keys($_GET);
    if (empty($commands)) {
        return FALSE;
    }

    if (preg_match("/^\//", $commands[0])) {
        $commands[0] = preg_replace("/^\//", '', $commands[0]);
    }
    switch ($commands[0]) {
        case 'getClientData':
            if ($_REQUEST['recent']) {
                $response = getClientData(1);
            } else {
                $response = getClientData(0);
            }
            echo $response;
            exit;
        case 'delTorrent':
            $response = delTorrent($_REQUEST['delTorrent'], $_REQUEST['trash'], $_REQUEST['batch']);
            echo "$response";
            exit;
        case 'stopTorrent':
            $response = stopTorrent($_REQUEST['stopTorrent'], $_REQUEST['batch']);
            echo "$response";
            exit;
        case 'startTorrent':
            $response = startTorrent($_REQUEST['startTorrent'], $_REQUEST['batch']);
            echo "$response";
            exit;
        case 'moveTo':
            $response = moveTorrent($_REQUEST['moveTo'], $_REQUEST['torHash'], $_REQUEST['batch']);
            echo "$response";
            exit;
        case 'updateFavorite':
            $response = update_favorite();
            if (preg_match("/^Error:/", $response)) {
                echo "<div id=\"fav_error\" class=\"dialog_window\" style=\"display: block\">$response</div>";
            }
            break;
        case 'updateFeed':
            update_feed();
            break;
        case 'clearCache':
            clear_cache();
            break;
        case 'setGlobals':
            update_global_config();
            write_config_file();
            break;
        case 'matchTitle':
            $feedLink = $_GET['rss'];
            foreach ($config_values['Feeds'] as $key => $feed) {
                if ($feed['Link'] == "$feedLink") {
                    $idx = $key;
                }
            }
            if ($config_values['Feeds'][$idx]['seedRatio']) {
                $seedRatio = $config_values['Feeds'][$idx]['seedRatio'];
            } else {
                $seedRatio = $config_values['Settings']['Default Seed Ratio'];
            }

            if (!($seedRatio)) {
                $seedRatio = -1;
            }
            if (($tmp = detectMatch(html_entity_decode($_GET['title']), TRUE))) {
                $_GET['name'] = trim(strtr($tmp['title'], "._", "  "));
                if ($config_values['Settings']['MatchStyle'] == "glob") {
                    $_GET['filter'] = trim(strtr($tmp['title'], " ._", "???"));
                    $_GET['filter'] .= '*';
                } else {
                    $_GET['filter'] = trim($tmp['title']);
                }
                //$_GET['quality'] = $tmp['qualities'];
                $_GET['quality'] = 'All';
                $_GET['feed'] = $_GET['rss'];
                $_GET['button'] = 'Add';
                $_GET['savein'] = 'Default';
                $_GET['seedratio'] = $seedRatio;
            } else {
                $_GET['name'] = $_GET['title'];
                $_GET['filter'] = $_GET['title'];
                $_GET['quality'] = 'All';
                $_GET['feed'] = $_GET['rss'];
                $_GET['button'] = 'Add';
                $_GET['savein'] = 'Default';
                $_GET['seedratio'] = $seedRatio;
            }
            if ($config_values['Settings']['Default Feed All'] &&
                    preg_match('/^(\d+)x(\d+)p?$|^(\d{8})$/i', $tmp['episode'])) {
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
                $guess = detectMatch(html_entity_decode($_GET['hide']), TRUE);
                echo $guess['title'];
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
                    $downloadDir = $fav['Save In'];
                }
            }
            if ((!isset($downloadDir) || $downloadDir == "Default" ) &&
                    isset($config_values['Settings']['Download Dir'])) {
                $downloadDir = $config_values['Settings']['Download Dir'];
            }
            $r = client_add_torrent(preg_replace('/ /', '%20', trim($_GET['link'])), $downloadDir, $_GET['title'], $_GET['feed']);
            if ($r == "Success") {
                $torHash = get_torHash(add_cache($_GET['title']));
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
        case 'version_check':
            echo version_check();
            exit;
        case 'get_dialog_data':
            switch ($_GET['get_dialog_data']) {
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
                case '#show_info':
                    show_info(urldecode($_GET['episode_name']));
                    exit;
                default:
                    exit;
            }
        default:
            $output = "<script type='text/javascript'>alert('Bad Parameters passed to " . $_SERVER['PHP_SELF'] . ":  " . $_SERVER['REQUEST_URI'] . "');</script>";
    }

    if (isset($output)) {
        if (is_array($output)) {
            $output = implode($filler, $output);
        }
        $html_out .= str_replace("\n", "<br>", "<div class='execoutput'>$output</div>");
        echo $html_out;
        $html_out = "";
    }
    return;
}

function display_global_config() {
    global $config_values;

    $hidedonate = $savetorrent = $transmission = "";
    $deepfull = $deeptitle = $deepTitleSeason = $deepoff = $verifyepisode = "";
    $matchregexp = $matchglob = $matchsimple = $dishidelist = $mailonhit = "";
    $favdefaultall = $onlynewer = $fetchproper = $autodel = $folderclient = $epionly = $combinefeeds = $require_epi_info = "";

    switch ($config_values['Settings']['Client']) {
        case 'Transmission':
            $transmission = 'selected="selected"';
            break;
        case 'folder':
            $folderclient = 'selected="selected"';
            break;
    }
    if ($config_values['Settings']['Episodes Only'] == 1) {
        $epionly = 'checked=1';
    }
    if ($config_values['Settings']['Combine Feeds'] == 1) {
        $combinefeeds = 'checked=1';
    }
    if ($config_values['Settings']['Require Episode Info'] == 1) {
        $require_epi_info = 'checked=1';
    }
    if ($config_values['Settings']['Disable Hide List'] == 1) {
        $dishidelist = 'checked=1';
    }
    if ($config_values['Settings']['Hide Donate Button'] == 1) {
        $hidedonate = 'checked=1';
    }
    if ($config_values['Settings']['Save Torrents'] == 1) {
        $savetorrent = 'checked=1';
    }
    if ($config_values['Settings']['Email Notifications'] == 1) {
        $mailonhit = 'checked=1';
    }

    switch ($config_values['Settings']['Deep Directories']) {
        case 'Full': $deepfull = 'selected="selected"';
            break;
        case 'Title': $deeptitle = 'selected="selected"';
            break;
        case 'Title_Season': $deepTitleSeason = 'selected="selected"';
            break;
        default: $deepoff = 'selected="selected"';
    }

    if ($config_values['Settings']['Verify Episode'] == 1) {
        $verifyepisode = 'checked=1';
    }
    if ($config_values['Settings']['Only Newer'] == 1) {
        $onlynewer = 'checked=1';
    }
    if ($config_values['Settings']['Download Proper'] == 1) {
        $fetchproper = 'checked=1';
    }
    if ($config_values['Settings']['Auto-Del Seeded Torrents'] == 1) {
        $autodel = 'checked=1';
    }
    if ($config_values['Settings']['Default Feed All'] == 1) {
        $favdefaultall = 'checked=1';
    }

    switch ($config_values['Settings']['MatchStyle']) {
        case 'glob': $matchglob = "selected='selected'";
            break;
        case 'simple': $matchsimple = "selected='selected'";
            break;
        case 'regexp':
        default: $matchregexp = "selected='selected'";
    }

    // Include the templates and append the results to html_out
    ob_start();
    require('templates/global_config.tpl');
    return ob_get_contents();
}

function display_favorites_info($item, $key) {
    global $config_values;
    $feed_options = '<option value="none">None</option>';
    $feed_options .= '<option value="all"';
    if (preg_match('/all/i', $item['Feed']) || $item['Name'] == "") {
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
    global $config_values, $html_out; //TODO these are required for this function to work, somehow--figure out how and fix

    ob_start();
    require('templates/favorites.tpl');
    return ob_get_contents(); //TODO figure out why ob_get_clean() or ob_get_contents() followed by ob_end_clean() doesn't work
}

function display_hidelist() {
    global $config_values, $html_out;

    ob_start();
    require('templates/hidelist.tpl');
    return ob_get_contents();
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

function episode_info($show, $episode_num, $isShow, $epiInfo) {
    $temp = explode('x', $episode_num);
    $episode = $show->getEpisode($temp[0], $temp[1]);
    twxa_debug(print_r($episode, TRUE));

    $name = $show->seriesName;
    $episode_name = $episode->name;
    $text = empty($episode->overview) ? $show->overview : $episode->overview;
    $image = empty($episode->filename) ? '' : cacheImage('http://thetvdb.com/banners/' . $episode->filename);
    $rating = $show->rating;
    $airdate = date('M d, Y', $episode->firstAired);
    $actors = [];
    if ($episode->guestStars) {
        foreach ($episode->guestStars as $person_name) {
            $guests[] = $person_name;
        }
    }
    if ($show->actors) {
        foreach ($show->actors as $person_name) {
            $actors[] = $person_name;
        }
    }
    $directors = [];
    if ($episode->directors) {
        foreach ($episode->directors as $person_name) {
            $directors[] = $person_name;
        }
    }
    $writers = [];
    if ($episode->writers) {
        foreach ($episode->writers as $person_name) {
            $writers[] = $person_name;
        }
    }
    ob_start();
    require('templates/episode.tpl');
    return ob_get_contents();
}

function show_info($ti) {
    // remove soft hyphens
    $ti = str_replace("\xC2\xAD", "", $ti);
    $episode_data = detectMatch($ti, true);

    if ($episode_data === false) {
        $isShow = false;
        $name = $ti;
        $data = '';
    } else {
        if (preg_match('/\d+x\d+/', $episode_data['episode'])) {
            $epiInfo = 1;
        } else {
            $epiInfo = 0;
        }
        $isShow = $episode_data['episode'] == 'noShow' ? false : true;
        $name = $episode_data['title'];
        $data = $episode_data['qualities'];
    }

    $episode_num = $episode_data['episode'];
    $shows = TV_Shows::search($name);
    if (count($shows) == 1) {
        episode_info($shows[0], $episode_num, $isShow, $epiInfo);
    } else if (count($shows) > 1) {
        episode_info($shows[0], $episode_num, $isShow, $epiInfo);
    } else {
        episode_info($shows[0], $episode_num, 0, 0);
    }
}

function cacheImage($url) {
    global $config_values;
    $path_parts = pathinfo($url);
    $filename = $path_parts['filename'] . "." . $path_parts['extension'];
    //TODO Use non-hardcoded cache path
    $img_url = 'tvdb_cache/' . $filename;
    $img_local = $config_values['Settings']['TVDB Dir'] . $filename;
    if (!file_exists($img_local)) {
        $x = file_put_contents($img_local, file_get_contents($url));
    }

    return $img_url;
}

function display_clearCache() {
    global $html_out;

    ob_start();
    require('templates/clear_cache.tpl');
    return ob_get_contents();
}

function close_html() {
    //global $html_out, $debug_output, $main_timer;
    global $html_out;
    echo $html_out;
    $html_out = "";
}

function check_requirements() {
    if (!(function_exists('json_encode'))) {
        echo "<div id=\"errorDialog\" class=\"dialog_window\" style=\"display: block\">
            No JSON support found. Please make sure PHP is compiled with JSON support.<br>
	    In some cases there is a package like php5-json that has to be installed.</div>";
        return 1;
    }
    if (!(function_exists('curl_init'))) {
        echo "<div id=\"errorDialog\" class=\"dialog_window\" style=\"display: block\">
            No CURL support found. Please make sure php5-curl is installed.</div>";
        return 1;
    }
}

function check_files() {
    global $config_values;

    $myuid = posix_getuid();
    $configDir = platform_get_configCacheDir() . '/';
    if (!is_writable($configDir)) {
        echo "<div id=\"errorDialog\" class=\"dialog_window\" style=\"display: block\">Please create the directory $configDir and make sure it's readable and writeable for the user running the webserver (uid: $myuid). </div>";
    }
    $cwd = getcwd();
    if (!(get_webDir() == $cwd)) {
        echo "<div id=\"errorDialog\" class=\"dialog_window\" style=\"display: block\">Please edit the config.php file and change the webDir from " . get_webDir() . " to:<br /> \"$cwd\".<br />Then click your browser's reload button.</div>";
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

function version_check() {
    global $twxa_version;
    if (!isset($_COOKIE['VERSION-CHECK'])) {
        $get = curl_init();
        $getOptions[CURLOPT_URL] = 'http://silverlakecorp.com/torrentwatch-xa/VERSION.txt';
        $getOptions[CURLOPT_USERAGENT] = "torrentwatch-xa/$twxa_version[0] ($twxa_version[1])";
        get_curl_defaults($getOptions);
        curl_setopt_array($get, $getOptions);
        $latest = curl_exec($get);
        curl_close($get);
        $version = (int) str_replace('.', '', $twxa_version[0]);
        $tmplatest = (int) str_replace('.', '', $latest);
        if ($tmplatest && $tmplatest > $version) {
            return "<div id=\"newVersion\" class=\"dialog_window\" style=\"display: block\">torrentwatch-xa $latest is available.
                   Click <a href=\"https://github.com/dchang0/torrentwatch-xa/\">here</a> for more information.</div>";
        }
    }
}

function get_tr_location() {
    global $config_values;
    $host = $config_values['Settings']['Transmission Host'];
    if (preg_match('/(localhost|127.0.0.1)/', $host)) {
        $host = preg_replace('/:.*/', "", $_SERVER['HTTP_HOST']);
    }
    if (preg_match('/(localhost|127.0.0.1)/', $host)) {
        $host = preg_replace('/:.*/', "", $_SERVER['SERVER_NAME']);
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

// MAIN routine
$main_timer = timer_init();
platform_initialize();
setup_default_config();
read_config_file();
if ($config_values['Settings']['Sanitize Hidelist'] != 1) {
    include '/var/lib/torrentwatch-xa/lib/update_hidelist.php'; //TODO set to use baseDir/lib
    $config_values['Settings']['Sanitize Hidelist'] = 1;
    twxa_debug("Updated Hidelist\n");
    write_config_file();
}
//authenticate();

$config_values['Global']['HTMLOutput'] = 1;
$html_out = "";
//$debug_output = "torrentwatch-xa debug:";

parse_options();
if (check_requirements()) {
    return;
}
check_files();

echo $html_out;
$html_out = "";
flush();

// Feeds
load_feeds($config_values['Feeds']);
feeds_perform_matching($config_values['Feeds']);

get_client();
close_html();

$footer = "Thank you for enjoying <img id=\"footerLogo\" src=\"images/torrentwatch-xa-logo16.png\" alt=\"torrentwatch-xa logo\" /> $twxa_version[0]!&nbsp;Please <a href=\"https://github.com/dchang0/torrentwatch-xa/issues\" target=\"_blank\">report bugs here</a>.";
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

unlink_temp_files();
exit(0);
