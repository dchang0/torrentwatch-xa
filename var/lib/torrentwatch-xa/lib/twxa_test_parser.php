<?php

// simple pseudo-unit-tester for the parsing and matching engine

error_reporting(E_ERROR | E_WARNING | E_PARSE);

// NOTE: we should include config.php, but it is not necessary for testing parsing unless we need writeToLog()
require_once 'twxa_tools.php';
require_once 'twxa_parse.php';
$title = $argv[1];
$title = preg_replace("/<span .*>(.*)<\/span>/", "$1", $title);
print "\nParsing exactly what is between >>> and <<< on the next line:\n>>>$title<<<\n\n";
print_r(detectMatch($title));
