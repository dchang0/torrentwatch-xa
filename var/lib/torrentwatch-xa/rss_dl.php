#!/usr/bin/php -q
<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// rss_dl.php
// command line interface to torrentwatch-xa

//ini_set('include_path', 'lib');
//ini_set("precision", 4); // seems only useful for the timer calculation

require_once('/var/lib/torrentwatch-xa/lib/twxa_rss_dl_tools.php');

//$config_values;
$verbosity = 0;

function usage() {
    twxa_debug(__FILE__ . " <options>\nCommand line interface to torrentwatch-xa\nOptions:\n", 0);
    twxa_debug("           -c <dir> : enable cache\n", 0);
    twxa_debug("           -C : disable cache\n", 0);
    twxa_debug("           -d : skip watch dir\n", 0);
    twxa_debug("           -D : start torrents in watch dir\n", 0);
    twxa_debug("           -h : show this help\n", 0);
    twxa_debug("           -q : quiet (no output)\n", 0);
    twxa_debug("           -v : verbose output\n", 0);
    twxa_debug("           -vv: verbose output(even more)\n", 0);
    twxa_debug("    NOTE: This interface only writes to the config file when using the -i option\n", 0);
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
            case '-d':
                $config_values['Settings']['Process Watch Dir'] = 0;
                break;
            case '-D':
                $config_values['Settings']['Process Watch Dir'] = 1;
                break;
            case '-h':
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
                twxa_debug("Invalid command line argument:  " . $_SERVER['argv'][$i] . "\n", 0);
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
twxa_debug("Start rss_dl.php\n", 2);

if (isset($config_values['Feeds'])) {
    load_all_feeds($config_values['Feeds'], 1);
    process_all_feeds($config_values['Feeds']);
}

//TODO later, break the watch dir into a function
if (isset($config_values['Settings']['Process Watch Dir']) &&
        $config_values['Settings']['Process Watch Dir'] === 1 &&
        $config_values['Settings']['Watch Dir']) { //TODO test if Watch Dir is a valid directory and exists
    twxa_debug("Checking Watch Dir: " . $config_values['Settings']['Watch Dir'] . "\n", 2);
    foreach ($config_values['Favorites'] as $fav) {
        $guess = detectMatch(html_entity_decode($_GET['title']));
        $name = trim(strtr($guess['title'], "._", "  ")); //TODO title matching is too simple here
        if ($name == $fav['Name']) {
            $downloadDir = $fav['Save In'];
        }
    }
    if (!$downloadDir || $downloadDir == "Default") {
        $downloadDir = $config_values['Settings']['Download Dir'];
    }

    add_torrents_in_dir($config_values['Settings']['Watch Dir'], $downloadDir);
    //TODO add error message if no matching torrents found in dir
} else {
    twxa_debug("Skipping Watch Dir\n", 2);
}

twxa_debug("End rss_dl.php: processed in " . timer_get_time($main_timer) . "s\n\n", 2);
