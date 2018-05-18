<?php

// twxacli.php
// torrentwatch-xa command line interface typically used with cron

error_reporting(E_ERROR | E_WARNING | E_PARSE);

require_once("config.php");
require_once("twxa_tools.php");

function usage() {
    print(__FILE__ . " [options]\ntorrentwatch-xa command line interface\nOptions:\n");
//    print("           -c <dir> : enable cache\n");
//    print("           -C : disable cache\n");
    print("           -h : show this help\n");
    print("           -q : quiet (no output)\n");
    print("           -v : verbose output\n");
    print("           -vv: verbose output(even more)\n");
}

function parse_args() {
    global $config_values, $argc;
    for ($i = 1; $i < $argc; $i++) {
        switch ($_SERVER['argv'][$i]) {
//            case '-c':
//                $i++;
//                $config_values['Settings']['Cache Dir'] = $_SERVER['argv'][$i];
//                break;
//            case '-C':
//                unset($config_values['Settings']['Cache Dir']);
//                break;
            case '-h':
            case '--help':
                usage();
                exit(1);
            case '-q':
                $config_values['Settings']['debugLevel'] = -99;
                break;
            case '-v':
                $config_values['Settings']['debugLevel'] = 1;
                break;
            case '-vv':
                $config_values['Settings']['debugLevel'] = 2;
                break;
            default:
                print ("Invalid command line argument:  " . $_SERVER['argv'][$i] . "\n");
        }
    }
}

/// main
$main_timer = getElapsedMicrotime(0);
if (file_exists(getConfigFile())) {
    readjSONConfigFile();
} else {
    setup_default_config();
}
parse_args();
twxaDebug("=====Start twxacli.php\n", 2);
if (isset($config_values['Feeds'])) {
    load_all_feeds($config_values['Feeds'], 1);
    process_all_feeds($config_values['Feeds']);
}
if (isset($config_values['Settings']['Auto-Del Seeded Torrents']) &&
        $config_values['Settings']['Auto-Del Seeded Torrents'] == 1) {
    auto_del_seeded_torrents();
} else {
    twxaDebug("Auto-Del Seeded Torrents is disabled\n", 2);
}
twxaDebug("=====End twxacli.php: processed in " . getElapsedMicrotime($main_timer) . "s\n", 2);
