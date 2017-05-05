<?php

// contains just the matchTitle functions for exactly 5 numbers found in the title

function matchTitle5_1($ti, $seps) {
    // ##x## - ##x##v#
    $mat = [];
    $re = "/\b(\d{1,2})[$seps]?[xX][$seps]?(\d{1,4})[$seps]?-[$seps]?(\d{1,2})[$seps]?[xX][$seps]?(\d{1,4})[$seps]?[Vv](\d{1,2})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[1],
            'seasEd' => $mat[3],
            'episSt' => $mat[2],
            'episEd' => $mat[4],
            'itemVr' => $mat[5],
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "5_1"
        ];
    }
}

function matchTitle5_2($ti, $seps) {
    // ##x##v# - ##x##
    $mat = [];
    $re = "/\b(\d{1,2})[$seps]?[xX][$seps]?(\d{1,4})[$seps]?[Vv](\d{1,2})[$seps]?-[$seps]?(\d{1,2})[$seps]?[xX][$seps]?(\d{1,4})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[1],
            'seasEd' => $mat[4],
            'episSt' => $mat[2],
            'episEd' => $mat[5],
            'itemVr' => $mat[3],
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "5_2"
        ];
    }
}
