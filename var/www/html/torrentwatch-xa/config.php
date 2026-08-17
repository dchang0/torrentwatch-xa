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
// NOTE: this value is informational only and is not referenced by the application
function get_webDir() {
    return "/var/www/html/torrentwatch-xa";
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
    if (!is_dir($twxaIncludePath)) {
        continue;
    }
    $found = false;
    foreach (explode(PATH_SEPARATOR, $includePath) as $entry) {
        if (rtrim($entry, "/\\") === $twxaIncludePath) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        $includePath .= PATH_SEPARATOR . $twxaIncludePath;
    }
}
if (set_include_path($includePath) === false) {
    error_log("torrentwatch-xa config.php: failed to set include path: $includePath");
}
