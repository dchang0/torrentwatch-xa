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
    print ("           <favorites TSV file> : tab-separated plain text file containing Name, Filter, Not, and Quality columns, one Favorite per line\n");
}

function parse_args($argc, $argv) {
    for ($i = 1; $i < $argc; $i++) {
        switch ($argv[$i]) {
            case '-h':
            case '--help':
                usage();
                exit(0);
        }
    }
}

/// main
if (PHP_SAPI !== 'cli') {
    print(__FILE__ . " is a command-line tool and cannot be run from the web.\n");
    exit(1);
}

parse_args($argc, $argv);

if ($argc < 2 || $argv[1] === '') {
    usage();
    exit(1);
}

$main_timer = getElapsedMicrotime(0);
readjSONConfigFile();

// get the owner of the current config file
$configFile = getConfigFile();
$configOwner = fileowner($configFile);
if ($configOwner === false) {
    print("Unable to determine owner of config file: $configFile\n");
    writeToLog("Unable to determine owner of config file: $configFile\n", 0);
}

// check if favorites file is specified and exists
if (is_file($argv[1])) {
    writeToLog("=====Start twxa_fav_import.php\n", 2);

    // loop through favorites file, creating a new favorite for each Name and Filter
    $row = 1;
    if (($handle = fopen($argv[1], "r")) !== false) {
        while (($data = fgetcsv($handle, 0, "\t", "\"", "\\")) !== false) {
            // skip blank lines; fgetcsv returns a single null field for an empty line
            if (!isset($data[0])) {
                $row++;
                continue;
            }
            $return = null;
            if (count($data) === 1) {
                // first field is only field provided, use it as Name and Filter
                print("Importing Favorite: Name: " . $data[0] . "\tFilter: " . $data[0] . "\n");
                writeToLog("Importing Favorite: Name: " . $data[0] . "\tFilter: " . $data[0] . "\n", 1);
                $return = addFavoriteFromImport($data[0], $data[0]);
            } else if (count($data) === 2) {
                // first field is Name, second is Filter
                print("Importing Favorite: Name: " . $data[0] . "\tFilter: " . $data[1] . "\n");
                writeToLog("Importing Favorite: Name: " . $data[0] . "\tFilter: " . $data[1] . "\n", 1);
                $return = addFavoriteFromImport($data[0], $data[1]);
            } else if (count($data) === 3) {
                // first field is Name, second is Filter, third is Not
                print("Importing Favorite: Name: " . $data[0] . "\tFilter: " . $data[1] . "\tNot: " . $data[2] . "\n");
                writeToLog("Importing Favorite: Name: " . $data[0] . "\tFilter: " . $data[1] . "\tNot: " . $data[2] . "\n", 1);
                $return = addFavoriteFromImport($data[0], $data[1], $data[2]);
            } else if (count($data) >= 4) {
                // first field is Name, second is Filter, third is Not, fourth is Quality
                print("Importing Favorite: Name: " . $data[0] . "\tFilter: " . $data[1] . "\tNot: " . $data[2] . "\tQuality: " . $data[3] . "\n");
                writeToLog("Importing Favorite: Name: " . $data[0] . "\tFilter: " . $data[1] . "\tNot: " . $data[2] . "\tQuality: " . $data[3] . "\n", 1);
                $return = addFavoriteFromImport($data[0], $data[1], $data[2], $data[3]);
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
    } else {
        print("Unable to open file: " . $argv[1] . "\n");
        writeToLog("Unable to open file: " . $argv[1] . "\n", 0);
    }

    if (is_writable($configFile)) {
        if (!writejSONConfigFile()) {
            print("Failed to write config file: $configFile\n");
            writeToLog("Failed to write config file: $configFile\n", -1);
            exit(1);
        }
        // IMPORTANT: must change ownership on new config file to the user Apache2 is running as
        if ($configOwner !== false) {
            if (chown($configFile, $configOwner)) {
                // try chmod here, but chown would have failed if insufficient permissions
                if (chmodPath($configFile, 0640)) {
                    // success
                } else {
                    print("Failed to chmod config file $configFile to 0640\n");
                    writeToLog("Failed to chmod config file $configFile to 0640\n", -1);
                }
            } else {
                print("Failed to chown config file $configFile with UID $configOwner\n");
                writeToLog("Failed to chown config file $configFile with UID $configOwner\n", -1);
            }
        }
    } else {
        print("Config file is not writable: $configFile\n");
        writeToLog("Config file is not writable: $configFile\n", 0);
        exit(1);
    }

    writeToLog("=====End twxa_fav_import.php: processed in " . getElapsedMicrotime($main_timer) . "s\n", 2);
} else {
    // file is not a regular readable file
    print("File not found or not a regular file: " . $argv[1] . "\n");
    writeToLog("File not found or not a regular file: " . $argv[1] . "\n", 0);
}

function addFavoriteFromImport($name, $filter, $not = "", $quality = "") {
    if (!isset($name)) {
        return "Name not supplied, skipping.";
    }
    $result = addFavoriteFromParams(
            urldecode($name),
            urldecode($filter),
            'All',
            urldecode($quality),
            urldecode($not)
    );
    if (!is_array($result)) {
        return "Unexpected error while adding Favorite: " . (is_string($result) ? $result : var_export($result, true));
    }
    if ($result['errorCode'] === 2) {
        return "Error: \"" . urldecode($name) . "\" already exists in Favorites.";
    }
    if ($result['errorCode'] !== 0) {
        return $result['errorMessage'];
    }
    return "Successfully added.";
}
