<?php

// simple pseudo-unit-tester for the parsing and matching engine

require_once '/var/lib/torrentwatch-xa/lib/twxa_rss_dl_tools.php';
require_once '/var/lib/torrentwatch-xa/lib/twxa_parse.php';

print_r(detectMatch($argv[1]));
