<?php

// config.php: Main config file for torrentwatch-xa
// As of version 1.0.0, this file is required. twxa_config_lib.php no longer provides default settings

// torrentwatch-xa base installation directory
function get_baseDir() {
    return "/var/lib/torrentwatch-xa"; // default path
}

// torrentwatch-xa web UI installation directory
function get_webDir() {
    return "/var/www/html/torrentwatch-xa";
}

// torrentwatch-xa log file path
function get_logFile() {
    return "/tmp/twxalog";
}

// Transmission session-id cache file path
function get_tr_sessionIdFile() {
    return '/tmp/.Transmission-Session-Id';
}

// Set include paths
$twxaIncludePaths = [get_baseDir() . "/lib"];
$includePath = get_include_path();
foreach ($twxaIncludePaths as $twxaIncludePath) {
    if (strpos($includePath, $twxaIncludePath) === false) {
        $includePath .= PATH_SEPARATOR . $twxaIncludePath;
    }
}
set_include_path($includePath);
