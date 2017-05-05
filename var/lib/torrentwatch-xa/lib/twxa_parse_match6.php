<?php

// contains just the matchTitle functions for exactly 6 numbers found in the title

function matchTitle6_1($ti, $seps) {
    // ##x##v# - ##x##v#
    $mat = [];
    $re = "/\b(\d{1,2})[$seps]?[xX][$seps]?(\d{1,4})[$seps]?[Vv](\d{1,2})[$seps]?-[$seps]?(\d{1,2})[$seps]?[xX][$seps]?(\d{1,4})[$seps]?[Vv](\d{1,2})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[1],
            'seasEd' => $mat[4],
            'episSt' => $mat[2],
            'episEd' => $mat[5],
            'itemVr' => $mat[6], // ignore the start version number
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "6_1"
        ];
    }
}

function matchTitle6_2($ti, $seps) {
    // isolated YYYY MM DD - YYYY MM DD
    // MM DD YYYY - MM DD YYYY
    // DD MM YYYY - DD MM YYYY
    $mat = [];
    $re = "/\b(\d{1,4})[$seps\-\/](\d{1,4})[$seps\-\/](\d{1,4})[$seps]?-[$seps]?(\d{1,4})[$seps\-\/](\d{1,4})[$seps\-\/](\d{1,4})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        if (validateYYYYMMDD($mat[1] . $mat[2] . $mat[3]) && validateYYYYMMDD($mat[4] . $mat[5] . $mat[6])) {
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 0,
                'seasEd' => 0,
                'episSt' => $mat[1] . $mat[2] . $mat[3],
                'episEd' => $mat[4] . $mat[5] . $mat[6],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "6_2-1"
            ];
        } else if (validateYYYYMMDD($mat[3] . $mat[1] . $mat[2]) && validateYYYYMMDD($mat[3] . $mat[1] . $mat[2])) {
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 0,
                'seasEd' => 0,
                'episSt' => $mat[3] . $mat[1] . $mat[2],
                'episEd' => $mat[6] . $mat[4] . $mat[5],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "6_2-2"
            ];
        } else if (validateYYYYMMDD($mat[3] . $mat[2] . $mat[1]) && validateYYYYMMDD($mat[3] . $mat[2] . $mat[1])) {
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 0,
                'seasEd' => 0,
                'episSt' => $mat[3] . $mat[2] . $mat[1],
                'episEd' => $mat[6] . $mat[5] . $mat[4],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "6_2-3"
            ];
        }
    }
}