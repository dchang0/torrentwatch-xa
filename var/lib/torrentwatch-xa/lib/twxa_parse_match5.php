<?php

// contains just the matchTitle functions for exactly 5 numbers found in the title

function matchTitle5_1($ti, $seps) {
    // DEPRECATED: use matchTitleBatchRange instead
    return matchTitleBatchRange($ti, $seps);
}

function matchTitle5_2($ti, $seps) {
    // DEPRECATED: use matchTitleBatchRange instead
    return matchTitleBatchRange($ti, $seps);
}

function matchTitle5_3($ti, $seps) {
    // DEPRECATED: use matchTitleSequential instead
    return matchTitleSequential($ti, $seps, 5);
}
