<?php

// simple pseudo-unit-tester for the parsing and matching engine

require_once '/var/lib/torrentwatch-xa/lib/rss_dl_utils.php';
require_once '/var/lib/torrentwatch-xa/lib/twxa_parse.php';

print_r(detectMatch($argv[1]));
