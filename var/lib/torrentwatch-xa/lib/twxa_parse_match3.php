<?php

// contains just the matchTitle functions for exactly 3 numbers found in the title

function matchTitle3_1($ti, $seps) {
    // isolated YYYY MM DD or MM DD YYYY or DD MM YYYY
    $mat = [];
    $re = "/\b(\d{1,4})[$seps\-\/](\d{1,4})[$seps\-\/](\d{1,4})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        if (validateYYYYMMDD($mat[1] . $mat[2] . $mat[3])
        ) {
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 0,
                'seasEd' => 0,
                'episSt' => $mat[1] . $mat[2] . $mat[3],
                'episEd' => $mat[1] . $mat[2] . $mat[3],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "3_1-1"
            ];
        } else if (validateYYYYMMDD($mat[3] . $mat[1] . $mat[2])) {
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 0,
                'seasEd' => 0,
                'episSt' => $mat[3] . $mat[1] . $mat[2],
                'episEd' => $mat[3] . $mat[1] . $mat[2],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "3_1-2"
            ];
        } else if (validateYYYYMMDD($mat[3] . $mat[2] . $mat[1])) {
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 0,
                'seasEd' => 0,
                'episSt' => $mat[3] . $mat[2] . $mat[1],
                'episEd' => $mat[3] . $mat[2] . $mat[1],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "3_1-3"
            ];
        }
    }
}

function matchTitle3_2($ti, $seps) {
    // explicit S## E### - ### or S## ( E### - ### )
    // must have minus or space between E### or E## will match
    $mat = [];
    $re = "/(Season|Saison|Seizoen|Sezona|\bSeas\.|\bSeas|\bSais\.|\bSais\.|\bSea|\bSea|\bSe\.|\bSe|\bS\.|\bS|Temporada|\bTemp\.|\bTemp|\bT\.|\bT)[$seps]?(\d{1,2})[$seps]?[\,\-\(]?[$seps]?(Episodes|Episode|Epizodes|Epizode|Epis\.|Epis|Epi\.|Epi|Ep\.|Ep|E\.|E)[$seps]?(\d{1,4})[\-$seps]{1,3}(\d{1,4})[$seps]?\)?\b.*/i";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[2],
            'seasEd' => $mat[2],
            'episSt' => $mat[4],
            'episEd' => $mat[5],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "3_2"
        ];
    }
}

function matchTitle3_3($ti, $seps) {
    // isolated S##E## - E## range
    $mat = [];
    $re = "/\b[Ss](\d{1,2})[\-$seps]{0,3}[Ee](\d{1,4})[\-$seps]{0,3}[Ee](\d{1,4})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[1],
            'seasEd' => $mat[1],
            'episSt' => $mat[2],
            'episEd' => $mat[3],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "3_3"
        ];
    }
}

function matchTitle3_4($ti, $seps) {
    // isolated S# - ####.#
    $mat = [];
    $re = "/\b[Ss](\d{1,2})[$seps]?\-[$seps]?(\d{1,4}\.\d)\b.*/";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[1],
            'seasEd' => $mat[1],
            'episSt' => $mat[2],
            'episEd' => $mat[2],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "3_4"
        ];
    }
}

function matchTitle3_5($ti, $seps) {
    // S# - ### - ###
    $mat = [];
    $re = "/\b[Ss](\d{1,2})[\-$seps]{1,3}(\d{1,3})[$seps]?\-[$seps]?(\d{1,3})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        if ($mat[3] > $mat[2]) {
            // isolated S# - EE - EE
            // probably range of Episodes within one Season
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => $mat[1],
                'seasEd' => $mat[1],
                'episSt' => $mat[2],
                'episEd' => $mat[3],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "3_5-1"
            ];
        } else {
            // isolated S# - EE, extra ##
            // not sure what it is, probably extra number on end
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => $mat[1],
                'seasEd' => $mat[1],
                'episSt' => $mat[2],
                'episEd' => $mat[2],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "3_5-2"
            ];
        }
    }
}

function matchTitle3_6($ti, $seps) {
    // Japanese YYYY MM DD Print Media
    $mat = [];
    $re = "/(\b|\D)(\d{2}|\d{4})\x{5e74}?\-?(\d{1,2})\x{6708}?\-?(\d{1,2})\x{65e5}?\x{53f7}.*/u";
    if (preg_match($re, $ti, $mat)) {
        if (strlen($mat[3]) == 1) {
            $mat[3] = "0" . $mat[3];
        }
        if (strlen($mat[4]) == 1) {
            $mat[4] = "0" . $mat[4];
        }
        return [
            'medTyp' => 4,
            'numSeq' => 2,
            'seasSt' => 0,
            'seasEd' => 0,
            'episSt' => $mat[2] . $mat[3] . $mat[4],
            'episEd' => $mat[2] . $mat[3] . $mat[4],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "3_6"
        ];
    }
}

function matchTitle3_7($ti, $seps) {
    // explicit S##E###.#
    $mat = [];
    $re = "/(Season|Saison|Seizoen|Sezona|\bSeas\.|\bSeas|\bSais\.|\bSais\.|\bSea|\bSea|\bSe\.|\bSe|\bS\.|\bS|Temporada|\bTemp\.|\bTemp|\bT\.|\bT)[$seps]?(\d{1,2})[$seps]?[\,\-]?[$seps]?(Episode|Epizode|Epis\.|Epis|Epi\.|Epi|Ep\.|Ep|E\.|E|[$seps])[$seps]?(\d{1,4}\.\d)\b.*/i";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[2],
            'seasEd' => $mat[2],
            'episSt' => $mat[4],
            'episEd' => $mat[4],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "3_7"
        ];
    }
}

function matchTitle3_8($ti, $seps) {
    // YYYY.SSxEE
    $mat = [];
    $re = "/\b(\d{4})[$seps](\d{1,2})[$seps]?[xX][$seps]?(\d{1,4})\b.*/";
    $thisYear = getdate()['year'];
    if (preg_match($re, $ti, $mat) && $mat[1] + 0 <= $thisYear && $mat[1] + 0 > 1895) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[2],
            'seasEd' => $mat[2],
            'episSt' => $mat[3],
            'episEd' => $mat[3],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "3_8"
        ];
    }
}

function matchTitle3_9($ti, $seps) {
    // ## Episode ###.# but not ##E##.#
    // (no mention of Season, as that would have matched earlier, must have space to block checksum matches)
    $mat = [];
    $re = "/\b(\d{1,2})[$seps](Episode|Epis\.|Epis|Epi\.|Epi|Ep\.|Ep|E\.|E)[$seps]?(\d{1,4}\.\d)\b.*/i";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[1],
            'seasEd' => $mat[1],
            'episSt' => $mat[3],
            'episEd' => $mat[3],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "3_9"
        ];
    }
}

function matchTitle3_10($ti, $seps) {
    // Episode ##.# - ##
    // (no mention of Season, as that would have matched earlier)
    $mat = [];
    $re = "/(Episodes|Episode|Epis\.|Epis|Epi\.|Epi|Ep\.|Ep|E\.|E)[$seps]?(\d{1,4}\.\d)[\(\)$seps]?\-[$seps]?(\d{1,4})\b.*/i";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[2],
            'seasEd' => $mat[2],
            'episSt' => $mat[3],
            'episEd' => $mat[3],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "3_10"
        ];
    }
}

function matchTitle3_11($ti, $seps) {
    // Episode ## - ##.#
    // (no mention of Season, as that would have matched earlier)
    $mat = [];
    $re = "/(Episodes|Episode|Epis\.|Epis|Epi\.|Epi|Ep\.|Ep|E\.|E)[$seps]?(\d{1,4})[\(\)$seps]?\-[$seps]?(\d{1,4}\.\d)\b.*/i";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[2],
            'seasEd' => $mat[2],
            'episSt' => $mat[3],
            'episEd' => $mat[3],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "3_11"
        ];
    }
}

function matchTitle3_12($ti, $seps) {
    // ### to ###.# episodes
    $mat = [];
    $re = "/\b(\d{1,4})[$seps]?(through|thru|to)[$seps]?(\d{1,4}\.\d)\b.*/i";
    if (preg_match($re, $ti, $mat) && $mat[1] <= $mat[3] + 0) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[1],
            'episEd' => $mat[3],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "3_12"
        ];
    }
}

function matchTitle3_13($ti, $seps) {
    // ###.# to ### episodes
    $mat = [];
    $re = "/\b(\d{1,4}\.\d)[$seps]?(through|thru|to)[$seps]?(\d{1,4})\b.*/i";
    if (preg_match($re, $ti, $mat) && $mat[1] + 0 <= $mat[3]) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[1],
            'episEd' => $mat[3],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "3_13"
        ];
    }
}

function matchTitle3_14($ti, $seps) {
    // (YYYY) EE - EE (must precede (YYYY) - EE (EEE))
    $mat = [];
    $re = "/\([$seps]?(\d{4})[$seps]?\)[\-$seps]{0,3}(\d{1,4})[\-$seps]{1,3}(\d{1,4})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        if ($mat[1] + 0 <= getdate()['year'] && $mat[1] + 0 > 1895) {
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => $mat[1], // Half date notation and half episode: let Season = YYYY
                'seasEd' => $mat[1],
                'episSt' => $mat[2],
                'episEd' => $mat[3],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "3_14"
            ];
        }
        //TODO handle else
    }
}

function matchTitle3_15($ti, $seps) {
    // (YYYY) - EE (EEE) (must precede (YYYY) - EE)
    $mat = [];
    $re = "/\((\d{4})\)[\-$seps]{0,3}(\d{1,3})[\-$seps]{0,3}\((\d{1,4})\).*/";
    if (preg_match($re, $ti, $mat)) {
        if ($mat[1] + 0 <= getdate()['year'] && $mat[1] + 0 > 1895) {
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => $mat[1], // Half date notation and half episode: let Season = YYYY
                'seasEd' => $mat[1],
                'episSt' => $mat[2],
                'episEd' => $mat[2],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "3_15"
            ];
        }
        //TODO handle else
    }
}

function matchTitle3_16($ti, $seps) {
    // isolated S## ##v# (Season ## Episode ## Version #) (must precede isolated SS ##v#)
    $mat = [];
    $re = "/\b[Ss](\d{1,2})[\-$seps]{1,3}(\d{1,4})[\-$seps]{0,3}(v|V)(\d{1,2})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[1],
            'seasEd' => $mat[1],
            'episSt' => $mat[2],
            'episEd' => $mat[2],
            'itemVr' => $mat[4],
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "3_16"
        ];
    }
}

function matchTitle3_17($ti, $seps) {
    // isolated SS ##v# (Season ## Episode ## Version #)
    $mat = [];
    $re = "/\b(\d{1,2})[\-$seps]{1,3}(\d{1,4})[\-$seps]{0,3}(v|V)(\d{1,2})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[1],
            'seasEd' => $mat[1],
            'episSt' => $mat[2],
            'episEd' => $mat[2],
            'itemVr' => $mat[4],
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "3_17"
        ];
    }
}

function matchTitle3_18($ti, $seps) {
    // isolated YYYY (EE-EE) or SS (EE-EE)
    $mat = [];
    $re = "/\b(\d{1,4})[$seps]?\([$seps]?(\d{1,4})[\-$seps]{1,3}(\d{1,4})[$seps]?\).*/";
    if (preg_match($re, $ti, $mat)) {
        if ($mat[2] < $mat[3]) {
            // could be #### (EE - EE)
            if (strlen($mat[1]) > 2) {
                // first ### is probably not a season and is probably part of the title
                return [
                    'medTyp' => 1,
                    'numSeq' => 1,
                    'seasSt' => 1,
                    'seasEd' => 1,
                    'episSt' => $mat[2],
                    'episEd' => $mat[3],
                    'itemVr' => 1,
                    'favTi' => preg_replace($re, "", $ti),
                    'matFnd' => "3_18-1"
                ];
            } else {
                // assume SS (EE - EE)
                return [
                    'medTyp' => 1,
                    'numSeq' => 1,
                    'seasSt' => $mat[1],
                    'seasEd' => $mat[1],
                    'episSt' => $mat[2],
                    'episEd' => $mat[3],
                    'itemVr' => 1,
                    'favTi' => preg_replace($re, "", $ti),
                    'matFnd' => "3_18-2"
                ];
            }
        } else {
            //TODO handle unidentifiable ## (## - ##)
        }
    }
}

function matchTitle3_19($ti, $seps) {
    // #### Ep EE - EE
    $mat = [];
    $re = "/\b(\d{1,4})[$seps]?(Episodes|Episode|Epizodes|Epizode|Epis\.|Epis|Epi\.|Epi|Ep\.|Ep|E\.|E)[$seps]?\b(\d{1,4})[$seps]?\-[$seps]?(\d{1,4})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        if ($mat[3] < $mat[4]) {
            if ($mat[1] + 0 <= getdate()['year'] && $mat[1] + 0 > 1895) {
                // probably YYYY Ep EE - EE
                return [
                    'medTyp' => 1,
                    'numSeq' => 1,
                    'seasSt' => $mat[1], // Half date notation and half episode: let Season = YYYY
                    'seasEd' => $mat[1],
                    'episSt' => $mat[3],
                    'episEd' => $mat[4],
                    'itemVr' => 1,
                    'favTi' => preg_replace($re, "", $ti),
                    'matFnd' => "3_19-1"
                ];
            } else if (strlen($mat[1]) > 2) {
                // first ### is probably not a season and is probably part of the title
                return [
                    'medTyp' => 1,
                    'numSeq' => 1,
                    'seasSt' => 1,
                    'seasEd' => 1,
                    'episSt' => $mat[3],
                    'episEd' => $mat[4],
                    'itemVr' => 1,
                    'favTi' => preg_replace("/(Episodes|Episode|Epizodes|Epizode|Epis\.|Epis|Epi\.|Epi|Ep\.|Ep|E\.|E)[$seps]?\b(\d{1,4})[$seps]?\-[$seps]?(\d{1,4})\b.*/", "", $ti),
                    'matFnd' => "3_19-2"
                ];
            } else {
                // assume SS Ep EE - EE
                return [
                    'medTyp' => 1,
                    'numSeq' => 1,
                    'seasSt' => $mat[1],
                    'seasEd' => $mat[1],
                    'episSt' => $mat[3],
                    'episEd' => $mat[4],
                    'itemVr' => 1,
                    'favTi' => preg_replace($re, "", $ti),
                    'matFnd' => "3_19-3"
                ];
            }
        } else {
            //TODO handle unidentifiable ## Ep ## - ##
        }
    }
}

function matchTitle3_20($ti, $seps) {
    // #### EE - EE
    $mat = [];
    $re = "/\b(\d{1,4})[\-$seps]{1,3}(\d{1,4})[$seps]?\-[$seps]?(\d{1,4})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        if ($mat[2] < $mat[3]) {
            if ($mat[1] + 0 <= getdate()['year'] && $mat[1] + 0 > 1895) {
                // probably YYYY EE - EE
                return [
                    'medTyp' => 1,
                    'numSeq' => 1,
                    'seasSt' => $mat[1], // Half date notation and half episode: let Season = YYYY
                    'seasEd' => $mat[1],
                    'episSt' => $mat[2],
                    'episEd' => $mat[3],
                    'itemVr' => 1,
                    'favTi' => preg_replace($re, "", $ti),
                    'matFnd' => "3_20-1"
                ];
            } else if (strlen($mat[1]) > 2) {
                // first ### is probably not a season and is probably part of the title
                return [
                    'medTyp' => 1,
                    'numSeq' => 1,
                    'seasSt' => 1,
                    'seasEd' => 1,
                    'episSt' => $mat[2],
                    'episEd' => $mat[3],
                    'itemVr' => 1,
                    'favTi' => preg_replace("/[\-$seps]{1,3}(\d{1,4})[$seps]?\-[$seps]?(\d{1,4})\b.*/", " ", $ti),
                    'matFnd' => "3_20-2"
                ];
            } else {
                // assume SS EE - EE (not the same as S2 EE - EE handled far above, because letter S is not specified)
                return [
                    'medTyp' => 1,
                    'numSeq' => 1,
                    'seasSt' => $mat[1],
                    'seasEd' => $mat[1],
                    'episSt' => $mat[2],
                    'episEd' => $mat[3],
                    'itemVr' => 1,
                    'favTi' => preg_replace($re, "", $ti),
                    'matFnd' => "3_20-3"
                ];
            }
        } else {
            //TODO handle unidentifiable ## ## - ##
        }
    }
}

function matchTitle3_21($ti, $seps) {
    // isolated E1 E2 E3
    $mat = [];
    $re = "/\b(\d{1,3})[$seps](\d{1,3})[$seps](\d{1,3})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        if (
                $mat[1] + 1 === $mat[2] + 0 &&
                $mat[1] + 2 === $mat[3] + 0
        ) {
            // almost certainly sequence of episodes
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $mat[1],
                'episEd' => $mat[3],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "3_21"
            ];
        }
    }
}
