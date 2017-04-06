<?php

// simple pseudo-unit-tester for the parsing and matching engine

require_once 'rss_dl_utils.php';
require_once 'twxa_parse.php';

print_r(detectMatch($argv[1]));
