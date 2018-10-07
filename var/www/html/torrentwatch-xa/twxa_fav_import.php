<?php

// twxa_fav_import.php
// torrentwatch-xa bulk Favorites importer
// Imports a tab-separated text file into multiple Favorites.
// WARNING: This file is experimental and may be removed from torrentwatch-xa
// at any time.

error_reporting(E_ERROR | E_WARNING | E_PARSE);

require_once("config.php");
require_once("twxa_tools.php");

function usage() {
    print (__FILE__ . " [-h | --help] <favorites TSV file>\ntorrentwatch-xa bulk Favorites importer\nOptions:\n");
    print ("           -h | --help : show this help\n");
    print ("           <favorites TSV file> : tab-separated plain text file containing Name, Filter, and Quality columns, one Favorite per line\n");
}

function parse_args($argc, $argv) {
    for ($i = 1; $i < $argc; $i++) {
        switch ($argv[$i]) {
            case '-h':
            case '--help':
                usage();
                exit(1);
        }
    }
}

/// main
$main_timer = getElapsedMicrotime(0);
readjSONConfigFile();

parse_args($argc, $argv);

// get the owner of the current config file
$configFile = getConfigFile();
$configOwner = fileowner($configFile);

// check if favorites file is specified and exists
if (is_readable($argv[1])) {
    writeToLog("=====Start twxa_fav_import.php\n", 2);

    // loop through favorites file, creating a new favorite for each Name and Filter
    $row = 1;
    if (($handle = fopen($argv[1], "r")) !== false) {
        while (($data = fgetcsv($handle, 1000, "\t")) !== false) {
            $return = null;
            if (count($data) === 1) {
                // first field is only field provided, use it as Name and Filter
                print("Importing Favorite: Name: " . $data[0] . "\tFilter: " . $data[0] . "\n");
                writeToLog("Importing Favorite: Name: " . $data[0] . "\tFilter: " . $data[0] . "\n", 1);
                $return = addOneBulkFavorite($data[0], $data[0]);
            } else if (count($data) === 2) {
                // first field is Name, second is Filter
                print("Importing Favorite: Name: " . $data[0] . "\tFilter: " . $data[1] . "\n");
                writeToLog("Importing Favorite: Name: " . $data[0] . "\tFilter: " . $data[1] . "\n", 1);
                $return = addOneBulkFavorite($data[0], $data[1]);
            } else if (count($data) >= 3) {
                // first field is Name, second is Filter, third is Quality
                print("Importing Favorite: Name: " . $data[0] . "\tFilter: " . $data[1] . "\tQuality: " . $data[2] . "\n");
                writeToLog("Importing Favorite: Name: " . $data[0] . "\tFilter: " . $data[1] . "\tQuality: " . $data[2] . "\n", 1);
                $return = addOneBulkFavorite($data[0], $data[1], $data[2]);
            } else {
                print("Can't detect at least one tab-separated field, skipping line: $row\n");
                writeToLog("Can't detect at least one tab-separated field, skipping line: $row\n", 0);
            }
            if (!empty($return)) {
                print($return . "\n");
                writeToLog($return . "\n", 1);
            }
            $row++;
        }
        fclose($handle);
    }

    if (is_writable($configFile) && is_numeric($configOwner)) {
        if (writejSONConfigFile()) {
            // IMPORTANT: must change ownership on new config file to the user Apache2 is running as
            if (chown($configFile, $configOwner)) {
                //TODO maybe chmod here, but chown would have failed if insufficient permissions
            } else {
                // failed to chown config file
                print("Failed to chown config file $configFile with UID $configOwner\n");
                writeToLog("Failed to chown config file $configFile with UID $configOwner\n", -1);
            }
        }
    }

    writeToLog("=====End twxa_fav_import.php: processed in " . getElapsedMicrotime($main_timer) . "s\n", 2);
} else {
    // file is not readable
    print("File not readable: " . $argv[1] . "\n");
    writeToLog("File not readable: " . $argv[1] . "\n", 0);
}

function addOneBulkFavorite($name, $filter, $quality = "") {
    global $config_values;

    if (isset($name)) {
        foreach ($config_values['Favorites'] as $fav) {
            if ($name === $fav['Name']) {
                return("Error: \"" . $name . "\" already exists in Favorites.");
            }
        }

        $config_values['Favorites'][]['Name'] = urldecode($name);
        $arrayKeys = array_keys($config_values['Favorites']);
        $idx = end($arrayKeys);

        $config_values['Favorites'][$idx]['Filter'] = urldecode($filter);
        $config_values['Favorites'][$idx]['Quality'] = urldecode($quality);
        $config_values['Favorites'][$idx]['Feed'] = 'All';

        $list = array(
            //"name" => "Name",
            //"filter" => "Filter",
            "not" => "Not",
            "downloaddir" => "Download Dir",
            "alsosavedir" => "Also Save Dir",
            "episodes" => "Episodes",
            //"feed" => "Feed",
            //"quality" => "Quality",
            "seedratio" => "seedRatio",
            "season" => "Season",
            "episode" => "Episode"
        );
        foreach ($list as $key => $data) {
            $config_values['Favorites'][$idx][$data] = "";
        }
    } else {
        // Name is not set, quit
        return("Name not supplied, skipping.");
    }
    return("Successfully added.");
}
