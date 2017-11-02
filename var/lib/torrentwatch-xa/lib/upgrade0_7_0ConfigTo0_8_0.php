<?php

/*
 * Upgrades config file from torrentwatch-xa 0.7.0 Windows INI to 0.8.0 JSON format.
 * This script is only provided with 0.8.0 and will be removed in later versions.
 * 
 * It is best to not use this script and start fresh with a default JSON config file,
 * but if you have a lot of Favorites you would like to carry over from 0.7.0,
 * this script will perform the conversion. It does not work on pre-0.7.0 config files.
 * 
 * Execute this script as root via the sudo command immediately after upgrading to
 * 0.8.0 and BEFORE cron has a chance to run 0.8.0 on the old Windows INI format
 * config file (which will overwrite the config file with a new, default JSON one).:
 * 
 * sudo /usr/bin/php /var/lib/torrentwatch-xa/upgrade0_7_0ConfigTo0_8_0.php
 * 
 * Run this file only once on a 0.7.0 config file.
 */

$twxaIncludePaths = ["/var/lib/torrentwatch-xa/lib"];
$includePath = get_include_path();
foreach ($twxaIncludePaths as $twxaIncludePath) {
    if (strpos($includePath, $twxaIncludePath) === false) {
        $includePath .= PATH_SEPARATOR . $twxaIncludePath;
    }
}
set_include_path($includePath);
require_once("twxa_tools.php");

// init
$config_out = "";
$config_file = "/var/lib/torrentwatch-xa/config_cache/torrentwatch-xa.config";
$config_file_bak = "/var/lib/torrentwatch-xa/config_cache/torrentwatch-xa.config.bak";
$config_cache = "/var/lib/torrentwatch-xa/config_cache/torrentwatch-xa-config.cache";

// back up config file
if (file_exists($config_file)) {
    if (!file_exists($config_file_bak)) {
        if (copy($config_file, $config_file_bak)) {

            // read in 0.7.0 config file
            read_config_file();

            // write out 0.8.0 JSON config file to same location
            writejSONConfigFile();

            // IMPORTANT: set permissions on the new config file
            $result0 = chmod($config_file, 0600);
            $result1 = chown($config_file, "www-data");
            $result2 = chgrp($config_file, "www-data");
            if ($result0 && $result1 && $result2) {
                if (file_exists($config_cache)) {
                    unlink($config_cache);
                }
                print "Successfully updated config file at: $config_file\n";
            } else {
                print "Unable to set permissions and ownership on new config file at: $config_file -- you must do this manually: www-data:www-data 0600\n";
            }
        } else {
            print "Failed to back up config to: $config_file_bak, exiting without changes.\n";
        }
    } else {
        print "Backup already exists at: $config_file_bak, exiting without changes.\n";
    }
} else {
    print "No config file at: $config_file\n";
}
