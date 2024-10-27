<?php

// contains just the matchTitle functions for exactly 4 numbers found in the title

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
                    'medTyp' => 4,
                    'numSeq' => 1,
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
                    'medTyp' => 4,
                    'numSeq' => 1,
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
            'medTyp' => 4,
            'numSeq' => 1,
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

function matchTitle4_3($ti, $seps) {
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
            'matFnd' => "4_3"
        ];
    }
}

function matchTitle4_4($ti, $seps) {
    // isolated E1 E2 E3 E4
    $mat = [];
    $re = "/\b(\d{1,3})[$seps](\d{1,3})[$seps](\d{1,3})[$seps](\d{1,3})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        if (
                (int) $mat[1] + 1 === (int) $mat[2] &&
                (int) $mat[1] + 2 === (int) $mat[3] &&
                (int) $mat[1] + 3 === (int) $mat[4]
        ) {
            // almost certainly sequence of episodes
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $mat[1],
                'episEd' => $mat[4],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "4_4"
            ];
        }
    }
}
