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

function matchTitle5_3($ti, $seps) {
    // isolated E1 E2 E3 E4 E5
    $mat = [];
    $re = "/\b(\d{1,3})[$seps](\d{1,3})[$seps](\d{1,3})[$seps](\d{1,3})[$seps](\d{1,3})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        if (
                $mat[1] + 1 === $mat[2] + 0 &&
                $mat[1] + 2 === $mat[3] + 0 &&
                $mat[1] + 3 === $mat[4] + 0 &&
                $mat[1] + 4 === $mat[5] + 0
        ) {
            // almost certainly sequence of episodes
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $mat[1],
                'episEd' => $mat[5],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "5_3"
            ];
        }
    }
}
