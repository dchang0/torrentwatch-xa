<?php

/*
 * Upgrades config file to torrentwatch-xa 0.7.0 format.
 * This script is only provided with 0.7.0 and will be removed in later versions.
 * Execute this script as root via the sudo command:
 * 
 * sudo /usr/bin/php /var/lib/torrentwatch-xa/upgradeConfigTo0_7_0.php
 * 
 * Run this file only once on a pre-0.7.0 config file. If run more than once, duplicate lines will result!
 */

// init
$config_out = "";
$config_file = "/var/lib/torrentwatch-xa/config_cache/torrentwatch-xa.config";
$config_file_bak = "/var/lib/torrentwatch-xa/config_cache/torrentwatch-xa.config.bak";
$config_cache = "/var/lib/torrentwatch-xa/config_cache/torrentwatch-xa-config.cache";

// back up config file
if (file_exists($config_file)) {
    if (!file_exists($config_file_bak)) {
        if (copy($config_file, $config_file_bak)) {
            // open config file
            if (!($fp = fopen($config_file, "r"))) {
                print "Could not open $config_file\n";
                exit(1);
            }

            if (flock($fp, LOCK_EX)) {
                while (!feof($fp)) {
                    $line = trim(fgets($fp));
                    // replace every instance of = Save In => with = Download Dir =>
                    $line = str_replace("= Save In =>", "= Download Dir =>", $line);
                    // add a line after every = Download Dir =>
                    $line = preg_replace("/^(\d+)\[\] = Download Dir => .*$/", "$0\n$1[] = Also Save Dir => Default", $line);
                    $config_out .= "$line\n";
                }
                flock($fp, LOCK_UN);
            }
            fclose($fp);

            if (!($fp = fopen($config_file . "_tmp", "w"))) {
                print "Could not open $config_file\n";
                exit(1);
            }

            if (flock($fp, LOCK_EX)) {
                if (fwrite($fp, $config_out)) {
                    flock($fp, LOCK_UN);
                    if (!(rename($config_file . "_tmp", $config_file))) {
                        print "Unable to rename " . $config_file . "_tmp to $config_file, exiting without changes.\n";
                        exit(1);
                    }
                }
                // set permissions on the new config file
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