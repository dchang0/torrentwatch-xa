<?php

// config.php: Main config file for torrentwatch-xa
// As of version 1.0.0, this file is required. twxa_config_lib.php no longer provides default settings
// 
// NOTE: do not put trailing slashes on the paths below
// torrentwatch-xa base installation directory
function get_baseDir() {
    return "/var/lib/torrentwatch-xa";
}

// torrentwatch-xa web UI installation directory
function get_webDir() {
    return "/var/www/html/torrentwatch-xa"; // if you change this, be sure to change it in torrentwatch-xa too
}

// torrentwatch-xa log file path
function get_logFile() {
    return "/var/log/torrentwatch-xa.log";
}

// NOTE: More settings are located at the top of twxa_config_lib.php, but it is better to leave them alone.
// Set include paths
$twxaIncludePaths = [get_baseDir() . "/lib"];
$includePath = get_include_path();
foreach ($twxaIncludePaths as $twxaIncludePath) {
    if (strpos($includePath, $twxaIncludePath) === false) {
        $includePath .= PATH_SEPARATOR . $twxaIncludePath;
    }
}
set_include_path($includePath);
