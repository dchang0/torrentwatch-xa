<?php

// contains just the matchTitle functions for exactly 6 numbers found in the title

function matchTitle6_1($ti, $seps) {
    // DEPRECATED: use matchTitleBatchRange instead
    return matchTitleBatchRange($ti, $seps);
}

function matchTitle6_2($ti, $seps) {
    // isolated YYYY MM DD - YYYY MM DD
    // MM DD YYYY - MM DD YYYY
    // DD MM YYYY - DD MM YYYY
    $mat = [];
    $re = "/\b(\d{1,4})[$seps\-\/](\d{1,4})[$seps\-\/](\d{1,4})[$seps]?-[$seps]?(\d{1,4})[$seps\-\/](\d{1,4})[$seps\-\/](\d{1,4})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        $ymd1 = $mat[1] . str_pad($mat[2], 2, '0', STR_PAD_LEFT) . str_pad($mat[3], 2, '0', STR_PAD_LEFT);
        $ymd2 = $mat[4] . str_pad($mat[5], 2, '0', STR_PAD_LEFT) . str_pad($mat[6], 2, '0', STR_PAD_LEFT);
        $mdy1 = $mat[3] . str_pad($mat[1], 2, '0', STR_PAD_LEFT) . str_pad($mat[2], 2, '0', STR_PAD_LEFT);
        $mdy2 = $mat[6] . str_pad($mat[4], 2, '0', STR_PAD_LEFT) . str_pad($mat[5], 2, '0', STR_PAD_LEFT);
        $dmy1 = $mat[3] . str_pad($mat[2], 2, '0', STR_PAD_LEFT) . str_pad($mat[1], 2, '0', STR_PAD_LEFT);
        $dmy2 = $mat[6] . str_pad($mat[5], 2, '0', STR_PAD_LEFT) . str_pad($mat[4], 2, '0', STR_PAD_LEFT);
        if (strlen($ymd1) === 8 && strlen($ymd2) === 8 && validateYYYYMMDD($ymd1) && validateYYYYMMDD($ymd2)) {
            return [
                'medTyp' => MEDTYP_VIDEO,
                'numSeq' => NUMSEQ_SEASON_EPISODE,
                'seasSt' => 0,
                'seasEd' => 0,
                'episSt' => $ymd1,
                'episEd' => $ymd2,
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "6_2-1"
            ];
        } else if (strlen($mdy1) === 8 && strlen($mdy2) === 8 && validateYYYYMMDD($mdy1) && validateYYYYMMDD($mdy2)) {
            return [
                'medTyp' => MEDTYP_VIDEO,
                'numSeq' => NUMSEQ_SEASON_EPISODE,
                'seasSt' => 0,
                'seasEd' => 0,
                'episSt' => $mdy1,
                'episEd' => $mdy2,
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "6_2-2"
            ];
        } else if (strlen($dmy1) === 8 && strlen($dmy2) === 8 && validateYYYYMMDD($dmy1) && validateYYYYMMDD($dmy2)) {
            return [
                'medTyp' => MEDTYP_VIDEO,
                'numSeq' => NUMSEQ_SEASON_EPISODE,
                'seasSt' => 0,
                'seasEd' => 0,
                'episSt' => $dmy1,
                'episEd' => $dmy2,
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "6_2-3"
            ];
        }
    }
}

function matchTitle6_3($ti, $seps) {
    // DEPRECATED: use matchTitleSequential instead
    return matchTitleSequential($ti, $seps, 6);
}
