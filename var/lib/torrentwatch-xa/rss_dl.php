#!/usr/bin/php -q
<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE);

// rss_dl.php
// This program is a command line interface to torrentwatch-xa
// 
//ini_set('include_path', '.:'.dirname(__FILE__).'/web/php');
ini_set('include_path', 'lib');
ini_set("precision", 4);
   
// These are our extra functions
require_once('lib/rss_dl_utils.php');

$config_values;
$test_run = 0;
$verbosity = 0;
$func_timer = 0;

function usage() {
    twxa_debug( __FILE__ . "<options> - CLI Interface to torrentwatch-xa\n",0);
    twxa_debug( "           -c <dir> : Enable Cache\n",0);
    twxa_debug( "           -C : Disable Cache\n",0);
    twxa_debug( "           -d : skip watch folder\n",0);
    twxa_debug( "           -D : Start torrents in watch folder\n",0);
    twxa_debug( "           -h : show this help\n",0);
    twxa_debug( "           -nv: not verbose (default)\n",0);
    twxa_debug( "           -q : quiet (no output)\n",0);
    twxa_debug( "           -v : verbose output\n",0);
    twxa_debug( "           -vv: verbose output(even more)\n",0);
    twxa_debug( "    Note: This interface only writes to the config file when using the -i option\n",0);
}

function parse_args() {
    global $config_values, $argc, $argv, $test_run, $verbosity;
    for($i=1;$i<$argc;$i++) {
        switch( $_SERVER['argv'][$i]) {
            case '-c':
                $i++;
                $config_values['Settings']['Cache Dir'] =  $_SERVER['argv'][$i];
                break;
            case '-C':
                unset($config_values['Settings']['Cache Dir']);
                break;
            case '-d':
                $config_values['Settings']['Run torrentwatch-xa'] = 0;
                break;
            case '-D':
                $config_values['Settings']['Run torrentwatch-xa'] = 1;
                break;
            case '-h':
                usage();
                exit(1);
            case '-nv':
                $verbosity = 0;
                break;
            case '-q':
                $verbosity = -1;
                break;
            case '-t':
                $test_run = 1;
                break;
            case '-v':
                $verbosity = 1;
                break;
            case '-vv':
                $verbosity = 2;
                break;
            default:
                twxa_debug("Unknown command line argument:  " . $_SERVER['argv'][$i] . "\n",0);
                break;
        }
    }
}

//
// Begin Main Function
//
//

    $main_timer = timer_init();
    if(file_exists(platform_getConfigFile()))
        read_config_file();
    else
        setup_default_config();

    if(isset($config_values['Settings']['Verbose']))
        $verbosity = $config_values['Settings']['Verbose'];
    parse_args();
    twxa_debug(date("F j, Y, g:i a")."\n",0);

    if(isset($config_values['Feeds'])) {
        load_feeds($config_values['Feeds'], 1);
        feeds_perform_matching($config_values['Feeds']);
    }

    if(_isset($config_values['Settings'], 'Run torrentwatch-xa', FALSE) and !$test_run and $config_values['Settings']['Watch Dir']) {
        global $hit;
        $hit = 0;
        foreach($config_values['Favorites'] as $fav) {
            $guess = detectMatch(html_entity_decode($_GET['title']));
            $name = trim(strtr($guess['title'], "._", "  "));
            if($name == $fav['Name']) {
                  $downloadDir = $fav['Save In'];
            } 
        }
        if(!$downloadDir || $downloadDir == "Default" ) $downloadDir = $config_values['Settings']['Download Dir'];

        check_for_torrents($config_values['Settings']['Watch Dir'], $downloadDir);
        if(!$hit)
            twxa_debug("No New Torrents to add from watch folder\n", 0);
    } else {
        twxa_debug("Skipping Watch Folder\n");
    }

    unlink_temp_files();

    twxa_debug($func_timer."s\n",0);

    twxa_debug(timer_get_time($main_timer)."s\n",0);
