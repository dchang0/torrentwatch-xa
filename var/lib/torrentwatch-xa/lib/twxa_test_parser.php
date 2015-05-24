<?php

/* simple pseudo-unit-tester for the new twxa parsing engine */

include_once 'rss_dl_utils.php';
include_once 'twxa_parse.php';
include_once 'guess.php';

print_r(detectMatch($argv[1]));
