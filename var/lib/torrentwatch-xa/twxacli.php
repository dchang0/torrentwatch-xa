#!/usr/bin/php -q
<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// twxacli.php
// command line interface to torrentwatch-xa
//ini_set('include_path', 'lib');

require_once('/var/lib/torrentwatch-xa/lib/twxa_tools.php');

$verbosity = 0;

function usage() {
    twxaDebug(__FILE__ . " <options>\nCommand line interface to torrentwatch-xa\nOptions:\n", 0);
    twxaDebug("           -c <dir> : enable cache\n", 0);
    twxaDebug("           -C : disable cache\n", 0);
    /*twxaDebug("           -d : skip watch dir\n", 0);
    twxaDebug("           -D : start torrents in watch dir\n", 0);*/
    twxaDebug("           -h : show this help\n", 0);
    twxaDebug("           -q : quiet (no output)\n", 0);
    twxaDebug("           -v : verbose output\n", 0);
    twxaDebug("           -vv: verbose output(even more)\n", 0);
    twxaDebug("    NOTE: This interface only writes to the config file when using the -i option\n", 0);
}

function parse_args() {
    global $config_values, $argc, $verbosity;
    for ($i = 1; $i < $argc; $i++) {
        switch ($_SERVER['argv'][$i]) {
            case '-c':
                $i++;
                $config_values['Settings']['Cache Dir'] = $_SERVER['argv'][$i];
                break;
            case '-C':
                unset($config_values['Settings']['Cache Dir']);
                break;
            /*case '-d':
                $config_values['Settings']['Process Watch Dir'] = 0;
                break;
            case '-D':
                $config_values['Settings']['Process Watch Dir'] = 1;
                break;*/
            case '-h':
            case '--help':
                usage();
                exit(1);
            case '-q':
                $verbosity = -1;
                break;
            case '-v':
                $verbosity = 1;
                break;
            case '-vv':
                $verbosity = 2;
                break;
            default:
                twxaDebug("Invalid command line argument:  " . $_SERVER['argv'][$i] . "\n", 0);
        }
    }
}

/// main
$main_timer = timer_get_time(0);
if (file_exists(getConfigFile())) {
    read_config_file();
} else {
    setup_default_config();
}
parse_args();
twxaDebug("=====Start twxacli.php\n", 2);
if (isset($config_values['Feeds'])) {
    load_all_feeds($config_values['Feeds'], 1);
    process_all_feeds($config_values['Feeds']);
}
/*if (isset($config_values['Settings']['Process Watch Dir']) &&
        $config_values['Settings']['Process Watch Dir'] == 1) {
    if ($config_values['Settings']['Watch Dir']) {
        process_watch_dir();
    } else {
        twxaDebug("Watch Dir is not set\n", 2);
    }
} else {
    twxaDebug("Process Watch Dir is disabled\n", 2);
}*/
if (isset($config_values['Settings']['Auto-Del Seeded Torrents']) &&
        $config_values['Settings']['Auto-Del Seeded Torrents'] == 1) {
    auto_del_seeded_torrents();
} else {
    twxaDebug("Auto-Del Seeded Torrents is disabled\n", 2);
}
twxaDebug("=====End twxacli.php: processed in " . timer_get_time($main_timer) . "s\n\n", 2);