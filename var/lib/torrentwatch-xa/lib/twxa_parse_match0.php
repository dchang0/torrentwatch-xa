<?php

// contains just the matchTitle functions for no numbers found in the title

function matchTitle0_1($ti, $seps) {
    // bounded word Special
    $mat = [];
    $re = "/\b(Specials|Special|Spec\.|Spec|Spc\.|Spc|Sp\.)\b.*/i";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 16,
            'seasSt' => 1, // assume Season 1
            'seasEd' => 1,
            'episSt' => 1, // assume Special Episode 1
            'episEd' => 1,
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "0_1"
        ];
    }
}

function matchTitle0_2($ti, $seps) {
    // bounded word OVA
    $mat = [];
    $re = "/\b(OVA|OAV)\b.*/";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 32,
            'seasSt' => 1, // assume Season 1
            'seasEd' => 1,
            'episSt' => 1, // assume OVA 1
            'episEd' => 1,
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "0_2"
        ];
    }
}
