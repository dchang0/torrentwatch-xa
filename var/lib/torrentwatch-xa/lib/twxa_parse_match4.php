<?php

// contains just the matchTitle functions for exactly 4 numbers found in the title

function matchTitle4_1($ti, $seps) {
    // ##x## - ##x##
    $mat = [];
    $re = "/\b(\d{1,2})[$seps]?[xX][$seps]?(\d{1,4})[$seps]?-[$seps]?(\d{1,2})[$seps]?[xX][$seps]?(\d{1,4})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[1],
            'seasEd' => $mat[3],
            'episSt' => $mat[2],
            'episEd' => $mat[4],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "4_1"
        ];
    }
}
