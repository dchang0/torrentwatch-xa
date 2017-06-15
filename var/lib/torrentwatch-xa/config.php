<?php

$twxaIncludePaths = ["/var/lib/torrentwatch-xa/lib"];
$includePath = get_include_path();
foreach ($twxaIncludePaths as $twxaIncludePath) {
    if (strpos($includePath, $twxaIncludePath) === false) {
        $includePath .= PATH_SEPARATOR . $twxaIncludePath;
    }
}
set_include_path($includePath);
require_once("twxa_config_lib.php");

/* 
 * You may change any setting in this static config file to fit your needs by
 * uncommenting the entire function and then changing the return value.
 * These functions override default functions in twxa_config_lib.php.
 */

// torrentwatch-xa base installation directory
/*function get_baseDir() {
    return "/var/lib/torrentwatch-xa";
}*/

// torrentwatch-xa web UI installation directory
/*function get_webDir() {
    return "/var/www/html/torrentwatch-xa";
}*/

// torrentwatch-xa log file path
/*function get_logFile() {
    return "/tmp/twxalog";
}*/

// Transmission session-id cache file path
/*function get_tr_sessionIdFile() {
    return '/tmp/.Transmission-Session-Id';
}*/
