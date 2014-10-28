<?php

require_once("/var/lib/torrentwatch-xa/lib/config_lib.php"); //TODO set to use baseDir/lib

// You may change any default in this static config file to fit your needs.

// dynamic config file and config cache file location
function platform_get_configCacheDir() {
    global $platform;
    if ($platform == 'NMT') { //TODO probably broken due to split of webDir and baseDir
        return get_baseDir() . "/etc";
    } else {
        return get_baseDir() . "/config_cache";
    }
}

//torrentwatch-xa base installation directory; uncomment and modify to override the default
//function get_baseDir() {
//    return "/var/lib/torrentwatch-xa";
//}

//torrentwatch-xa web UI installation directory; uncomment and modify to override automatic get_webDir function
//function get_webDir() {
//    return "/var/www/torrentwatch-xa-web";
//}

//Transmission session-id cache file
function get_tr_sessionIdFile() {
    global $platform;
    if ($platform == 'NMT') {
        return '/share/Apps/torrentwatch-xa/tmp/.Transmission-Session-Id';
    } else {
        return '/tmp/.Transmission-Session-Id';
    }
}

?>
