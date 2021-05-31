<?php

// twxa_cli.php
// torrentwatch-xa command line interface typically used with cron

error_reporting(E_ERROR | E_WARNING | E_PARSE);

require_once("config.php");
require_once("twxa_tools.php");

function usage() {
    print(__FILE__ . " [options]\ntorrentwatch-xa command line interface\nOptions:\n");
    print("           -h : show this help\n");
    print("           -d : debug output\n");
}

function parse_args($argc, $argv) {
    global $config_values;
    for ($i = 1; $i < $argc; $i++) {
        switch ($argv[$i]) {
            case '-h':
            case '--help':
                usage();
                exit(1);
            case '-d':
            case '--debug':
                $config_values['Settings']['Log Level'] = 2;
                break;
            default:
                print ("Invalid command line argument:  " . $argv[$i] . "\n");
        }
    }
}

/// main
$main_timer = getElapsedMicrotime(0);
readjSONConfigFile();

parse_args($argc, $argv);
writeToLog("=====Start twxa_cli.php\n", 2);
//TODO add PHP prerequisite check
//TODO add files and directories check
if (isset($config_values['Feeds'])) {
    load_all_feeds($config_values['Feeds'], 1);
    process_all_feeds($config_values['Feeds']);
}
if (
        isset($config_values['Settings']['Client']) &&
        $config_values['Settings']['Client'] === 'Transmission' &&
        isset($config_values['Settings']['Auto-Del Seeded Torrents']) &&
        $config_values['Settings']['Auto-Del Seeded Torrents'] == 1
) {
    auto_del_seeded_torrents();
} else {
    writeToLog("Auto-Del Seeded Torrents is disabled\n", 2);
}
writeToLog("=====End twxa_cli.php: processed in " . getElapsedMicrotime($main_timer) . "s\n", 2);
