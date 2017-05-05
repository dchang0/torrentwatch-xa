<?php

// simple pseudo-unit-tester for the parsing and matching engine
require_once '/var/lib/torrentwatch-xa/lib/twxa_rss_dl_tools.php';
require_once '/var/lib/torrentwatch-xa/lib/twxa_parse.php';
$title = $argv[1];
$title = preg_replace("/<span .*>(.*)<\/span>/", "$1", $title);
print "\nParsing exactly what is between >>> and <<< on the next line:\n>>>$title<<<\n\n";
print_r(detectMatch($title));
