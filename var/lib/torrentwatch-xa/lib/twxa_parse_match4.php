<?php

// contains matchTitle functions for exactly 4 numbers found in the title
// plus shared unified functions (matchTitleBatchRange, matchTitleSequential) used by levels 4-6

function matchTitle4_1($ti, $seps) {
    // v##-## (YYYY-YYYY)
    $mat = [];
    $re = "/\b(Volumes|volumes|Vols\.|vols\.|Vol\.|vol\.|V\.|v\.|V|v|)[$seps]?(\d{1,4})[$seps]?\-?[$seps]?(\d{1,4})[$seps]?\((\d{4})[$seps]?\-?[$seps]?(\d{4})\).*/";
    if (preg_match($re, $ti, $mat)) {
        if (
                (int) $mat[4] <= getdate()['year'] &&
                (int) $mat[4] > 1895 &&
                (int) $mat[5] <= getdate()['year'] &&
                (int) $mat[5] > 1895
        ) {
            if ($mat[1] == "") {
                // print Chapter - Chapter
                $result = [
                    'medTyp' => MEDTYP_PRINT,
                    'numSeq' => NUMSEQ_SEASON_EPISODE,
                    'seasSt' => $mat[4],
                    'seasEd' => $mat[5],
                    'episSt' => $mat[2],
                    'episEd' => $mat[3],
                    'itemVr' => 1,
                    'matFnd' => "4_1-1"
                ];
            } else {
                // print Volume - Volume
                $result = [
                    'medTyp' => MEDTYP_PRINT,
                    'numSeq' => NUMSEQ_SEASON_EPISODE,
                    'seasSt' => $mat[2],
                    'seasEd' => $mat[3],
                    'episSt' => 1,
                    'episEd' => "",
                    'itemVr' => 1,
                    'matFnd' => "4_1-2"
                ];
            }
            $result['favTi'] = preg_replace($re, "", $ti);
            return $result;
        }
    }
}

function matchTitle4_2($ti, $seps) {
    // ####-#### as v####-####
    $mat = [];
    $re = "/\b(\d{1,4})[$seps]?\-[$seps]?(\d{1,4}) as v\.?(\d{1,4})[$seps]?\-[$seps]?(\d{1,4})\b.*/i";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => MEDTYP_PRINT,
            'numSeq' => NUMSEQ_SEASON_EPISODE,
            'seasSt' => $mat[3],
            'seasEd' => $mat[4],
            'episSt' => 1,
            'episEd' => "",
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "4_2"
        ];
    }
}

function matchTitleBatchRange($ti, $seps) {
    // Unified batch range: handles 4, 5, and 6 numbers
    // 4: ##x## - ##x##
    // 5: ##x## - ##x##v#  or  ##x##v# - ##x##
    // 6: ##x##v# - ##x##v#
    $mat = [];
    $re = "/\b(\d{1,2})[$seps]?[xX][$seps]?(\d{1,4})(?:[$seps]?[Vv](\d{1,2}))?[$seps]?-[$seps]?(\d{1,2})[$seps]?[xX][$seps]?(\d{1,4})(?:[$seps]?[Vv](\d{1,2}))?\b.*/";
    if (preg_match($re, $ti, $mat)) {
        $itemVr = 1;
        if (isset($mat[6]) && $mat[6] !== "") {
            $itemVr = $mat[6];
        } else if (isset($mat[3]) && $mat[3] !== "") {
            $itemVr = $mat[3];
        }
        return [
            'medTyp' => MEDTYP_VIDEO,
            'numSeq' => NUMSEQ_SEASON_EPISODE,
            'seasSt' => $mat[1],
            'seasEd' => $mat[4],
            'episSt' => $mat[2],
            'episEd' => $mat[5],
            'itemVr' => $itemVr,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "batch_range"
        ];
    }
}

function matchTitleSequential($ti, $seps, $count) {
    // Unified sequential episodes: handles 4, 5, or 6 consecutive numbers
    $mat = [];
    $re = "/\\b(\\d{1,3})";
    for ($i = 2; $i <= $count; $i++) {
        $re .= "[$seps](\\d{1,3})";
    }
    $re .= "\\b.*/";

    if (preg_match($re, $ti, $mat)) {
        for ($i = 2; $i <= $count; $i++) {
            if ((int)$mat[$i] !== (int)$mat[1] + ($i - 1)) {
                return null;
            }
        }
        return [
            'medTyp' => MEDTYP_VIDEO,
            'numSeq' => NUMSEQ_SEASON_EPISODE,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[1],
            'episEd' => $mat[$count],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => $count . "_seq"
        ];
    }
}

function matchTitle4_3($ti, $seps) {
    // DEPRECATED: use matchTitleBatchRange instead
    return matchTitleBatchRange($ti, $seps);
}

function matchTitle4_4($ti, $seps) {
    // DEPRECATED: use matchTitleSequential instead
    return matchTitleSequential($ti, $seps, 4);
}
