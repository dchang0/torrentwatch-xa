<?php

// contains just the matchTitle functions for exactly 2 numbers found in the title

function matchTitle2_1($ti, $seps) {
    // S01v2 or S01.v2
    $mat = [];
    $re = "/(Season|Saison|Seizoen|Sezona|\bSeas\.|\bSeas|\bSais\.|\bSais\.|\bSea|\bSea|\bSe\.|\bSe|\bS\.|\bS|Temporada|\bTemp\.|\bTemp|\bT\.|\bT)[$seps]?(\d{1,2})[$seps]?v[$seps]?(\d{1,2})\b.*/i";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[2],
            'seasEd' => $mat[2],
            'episSt' => 1,
            'episEd' => "",
            'itemVr' => $mat[3],
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "2_1"
        ];
    }
}

function matchTitle2_2($ti, $seps) {
    // S01E10
    // Season 1 Episode 10
    // Se.2.Ep.5
    // Seas 2, Epis 3
    // S3 - E6
    $mat = [];
    //$re = "/(Season|\bSeas\.|\bSeas|\bSe\.|\bSe|\bS\.|\bS)[$seps]?(\d+)[$seps]?[\,\-]?[$seps]?(Episode|Epis\.|Epis|Epi\.|Epi|Ep\.|Ep|E\.|E)[$seps]?(\d+).*/i";
    $re = "/(Season|Saison|Seizoen|Sezona|\bSeas\.|\bSeas|\bSais\.|\bSais\.|\bSea|\bSea|\bSe\.|\bSe|\bS\.|\bS|Temporada|\bTemp\.|\bTemp|\bT\.|\bT)[$seps]?(\d{1,2})[\,\-$seps]{0,3}(Episode|Epizode|Epis\.|Epis|Epi\.|Epi|Ep\.|Ep|E\.|E)[$seps]?(\d{1,4})\b.*/i";
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
            'matFnd' => "2_2"
        ];
    }
}

function matchTitle2_3($ti, $seps) {
    // ## - v# (Episode ## Version #)
    //TODO might need to move this below other superset matches
    $mat = [];
    $re = "/-?[$seps]?(\d{1,4})[\-$seps]{0,3}(v|V)(\d{1,2})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[1],
            'episEd' => $mat[1],
            'itemVr' => $mat[3],
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "2_3"
        ];
    }
}

function matchTitle2_4($ti, $seps) {
    // short-circuit Seasons ## through|thru|to ##
    // Seasons 2 to 4
    // S2 thru 4
    $mat = [];
    $re = "/(Seasons|Season|\bSeas\.|\bSeas|\bSe\.|\bSe|\bS.|\bS)[$seps]?(\d{1,2})[$seps]?(through|thru|to)[$seps]?(\d{1,2})\b.*/i"; // no minus!
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[2],
            'seasEd' => $mat[4],
            'episSt' => 1,
            'episEd' => "",
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "2_4"
        ];
    }
}

function matchTitle2_5($ti, $seps) {
    // short-circuit Seasons # - ##
    // Seasons 1 - 4
    $mat = [];
    $re = "/Seasons[$seps]?(\d{1,2})[$seps]?\-[$seps]?(\d{1,2})\b.*/i";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[1],
            'seasEd' => $mat[2],
            'episSt' => 1,
            'episEd' => "",
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "2_5"
        ];
    }
}

function matchTitle2_6($ti, $seps) {
    // short-circuit EP## - EP##
    $mat = [];
    $re = "/(Episode|Epis\.|Epis|\bEP\.|\bEP|\bE\.|\bE)[$seps]?(\d{1,4})[$seps]?\-[$seps]?(Episode|Epis\.|Epis|\bEP\.|\bEP|\bE\.|\bE|)[$seps]?(\d{1,4})\b.*/i";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[2],
            'episEd' => $mat[4],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "2_6"
        ];
    }
}

function matchTitle2_7($ti, $seps) {
    // short-circuit S1 - ##
    // S1 - 24
    // Se 1 - 5
    // Season 1 - 3
    $mat = [];
    $re = "/(Season|\bSeas\.|\bSeas|\bSe\.|\bSe|\bS\.|\bS)[$seps]?1[$seps]?\-[$seps]?(\d{1,4}).*/i";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[2],
            'episEd' => $mat[2],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "2_7"
        ];
    }
}

function matchTitle2_8($ti, $seps) {
    // (Season, Temporada ##) - ###
    $mat = [];
    $re = "/\([$seps]?(Season|Saison|Seizoen|Sezona|\bSeas\.|\bSeas|\bSais\.|\bSais\.|\bSea|\bSea|\bSe\.|\bSe|\bS\.|\bS|Temporada|\bTemp\.|\bTemp|\bT\.|\bT)[$seps]?(\d{1,2})[$seps]?\)[\-$seps]{0,3}(\d{1,4}).*/i";
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
            'matFnd' => "2_8"
        ];
    }
}

function matchTitle2_9($ti, $seps) {
    // Season, Temporada ## - ##
    $mat = [];
    $re = "/(Season|Saison|Seizoen|Sezona|\bSeas\.|\bSeas|\bSais\.|\bSais\.|\bSea|\bSea|\bSe\.|\bSe|\bS\.|\bS|Temporada|\bTemp\.|\bTemp|\bT\.|\bT)[$seps]?(\d{1,2})[\-$seps]{1,3}(\d{1,4})\b.*/i";
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
            'matFnd' => "2_9"
        ];
    }
}

function matchTitle2_10($ti, $seps) {
    // 1st|2nd|3rd Season ##
    $mat = [];
    $re = "/(\d{1,2})(st|nd|rd|th)[$seps]?(Season|Saison|Seizoen|Sezona)[\-$seps]{0,3}(\d{1,4})\b.*/i";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[1],
            'seasEd' => $mat[1],
            'episSt' => $mat[4],
            'episEd' => $mat[4],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "2_10"
        ];
    }
}

function matchTitle2_14($ti, $seps) {
    // ### - ###END
    $mat = [];
    $re = "/\b(\d{1,4})[$seps]?(-|to|thru|through)[$seps]?(\d{1,4})[$seps]?end\b.*/i";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[1],
            'episEd' => $mat[3], // could be 6 - 13END and not a full season
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "2_14"
        ];
    }
}

function matchTitle2_16($ti, $seps) {
    // - ##x##
    $mat = [];
    $re = "/-[$seps]?(\d{1,2})[$seps]?[xX][$seps]?(\d{1,4})\b.*/";
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
            'matFnd' => "2_16"
        ];
    }
}

function matchTitle2_17($ti, $seps) {
    // isolated ##x##
    $mat = [];
    $re = "/\b(\d{1,2})[$seps]?[xX][$seps]?(\d{1,4})\b.*/";
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
            'matFnd' => "2_17"
        ];
    }
}

function matchTitle2_18($ti, $seps) {
    // isolated ## of ##
    //TODO may be Part ## of ##
    $mat = [];
    $re = "/\b(\d{1,3})[$seps]?(OF|Of|of)[$seps]?(\d{1,3})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[1],
            'episEd' => $mat[1],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "2_18"
        ];
    }
}

function matchTitle2_22($ti, $seps) {
    // isolated ## & ##
    $mat = [];
    $re = "/\b(\d{1,4})[$seps]?(\&|and|\+|y|et)[$seps]?(\d{1,4})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[1],
            'episEd' => $mat[3],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "2_22"
        ];
    }
}

function matchTitle2_23($ti, $seps, $detVid) {
    // Volume ## - ##
    $mat = [];
    $re = "/(Volumes|Volumens|Volume|\bVols\.|Vols|\bVol\.|Vol|\bV\.|\bV)[$seps]?(\d{1,3})[$seps]?(-|to|thru|through)[$seps]?(\d{1,3})\b.*/i";
    if (preg_match($re, $ti, $mat)) {
        if ($detVid == true) {
            return [
                // video Volumes
                'medTyp' => 1,
                'numSeq' => 128,
                'seasSt' => $mat[2],
                'seasEd' => $mat[4],
                'episSt' => 1,
                'episEd' => "",
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_23-1"
            ];
        } else {
            return [
                // print Volumes
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => $mat[2],
                'seasEd' => $mat[4],
                'episSt' => 1,
                'episEd' => "",
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_23-2"
            ];
        }
    }
}

function matchTitle2_24($ti, $seps) {
    // Volume ## Chapter ##
    $mat = [];
    $re = "/(Volumen|Volume|\bVol\.|\bVol|\bV\.)[$seps]?(\d{1,3})[$seps]?(Chapitre|Chapter|Chap|\bCh|\bC\.)[$seps]?(\d{1,4})\b.*/i";
    if (preg_match($re, $ti, $mat)) {
        return [
            // print Volume x Chapter
            'medTyp' => 4,
            'numSeq' => 1,
            'seasSt' => $mat[2],
            'seasEd' => $mat[2],
            'episSt' => $mat[4],
            'episEd' => $mat[4],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "2_24"
        ];
    }
}

function matchTitle2_26($ti, $seps) {
    // Chapter ##-##
    $mat = [];
    $re = "/(Chapters|Chapter|Capitulos|Capitulo|Chapitres|Chapitre|\bChap\.|\bChap|\bCh\.|\bCh|\bC\.|\bC)[$seps]?(\d{1,4})[$seps]?(-|to|thru|through)[$seps]?(\d{1,4})\b.*/i";
    if (preg_match($re, $ti, $mat)) {
        return [
            // print Chapters
            'medTyp' => 4,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[2],
            'episEd' => $mat[4],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "2_26"
        ];
    }
}

function matchTitle2_32($ti, $seps) {
    // isolated SS - Episode ##
    $mat = [];
    $re = "/\b(\d{1,2})[\-$seps]{0,3}(Episode|\bEpis\.|\bEpis|\bEpi\.|\bEpi|\bEp\.|\bEp|\bE\.|\bE)[$seps]?(\d{1,4})\b.*/i";
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
            'matFnd' => "2_32"
        ];
    }
}

function matchTitle2_34($ti, $seps) {
    // Japanese ##-## Print Media Books/Volumes
    $mat = [];
    $re = "/\x{7B2C}(\d{1,4})[\-$seps]{1,3}(\d{1,4})\x{5dfb}.*/u";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 4,
            'numSeq' => 1,
            'seasSt' => mb_convert_kana($mat[1], "a", "UTF-8"),
            'seasEd' => mb_convert_kana($mat[2], "a", "UTF-8"),
            'episSt' => 0,
            'episEd' => "",
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "2_34"
        ];
    }
}

function matchTitle2_37($ti, $seps) {
    // Japanese YYYY MM or YYYY ## Print Media
    $mat = [];
    $re = "/(\b|\D)(\d{2}|\d{4})\x{5e74}?[\-$seps]{1,3}(\d{1,2})\x{6708}?\x{53f7}.*/u";
    if (preg_match($re, $ti, $mat)) {
        if (strlen($mat[3]) == 1) {
            $mat[3] = '0' . $mat[3];
        }
        if ($mat[3] != '' && checkdate($mat[3], 1, $mat[2])) {
            // moon character = month
            return [
                'medTyp' => 4,
                'numSeq' => 2,
                'seasSt' => 0, // date notation gets Season 0
                'seasEd' => 0,
                'episSt' => $mat[2] . $mat[3],
                'episEd' => $mat[2] . $mat[3],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_37-1"
            ];
        } else {
            // assume issue instead of month
            return [
                'medTyp' => 4,
                'numSeq' => 1,
                'seasSt' => $mat[2], // use VolumexChapter
                'seasEd' => $mat[2],
                'episSt' => $mat[3],
                'episEd' => $mat[3],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_37-2"
            ];
        }
    }
}

function matchTitle2_38($ti, $seps) {
    // #nd EE
    $mat = [];
    $re = "/\b(\d{1,2})(st|nd|rd|th)[$seps]?(\d{1,4})\b.*/i";
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
            'matFnd' => "2_38"
        ];
    }
}

function matchTitle2_39($ti, $seps) {
    // isolated ###.#
    $mat = [];
    $re = "/\b(\d{1,4}\.\d)\b.*/";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[1],
            'episEd' => $mat[1],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "2_39"
        ];
    }
}

function matchTitle2_40($ti, $seps) {
    // isolated YYYY-MM or or YYYY-EE or title #### - EE
    //TODO might match Magic Kaito 1412 - EE, which should match in 2_51-1
    //TODO make a decision about which is more common, YYYY-MM or YYYY-EE like Berserk 2017 - 05
    //TODO maybe if there are no seps like YYYY-MM, treat it like a date, if there are seps, treat it like YYYY - EE
    $mat = [];
    $re = "/\b(\d{4})[\-$seps]{1,3}(\d{1,4})\b.*/"; //TODO the minus can disappear, does this cause problems for #### ##?
    if (preg_match($re, $ti, $mat)) {
        if (checkdate($mat[2] + 0, 1, $mat[1] + 0) && $mat[1] + 0 <= getdate()['year'] && $mat[1] + 0 > 1895) {
            // YYYY-MM
            if (strlen($mat[2]) == 1) {
                $mat[2] = "0" . $mat[2];
            }
            return [
                'medTyp' => 1,
                'numSeq' => 2,
                'seasSt' => 0,
                'seasEd' => 0,
                'episSt' => $mat[1] . $mat[2],
                'episEd' => $mat[1] . $mat[2],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_40-1"
            ];
        } else {
            // #### is probably part of the title
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $mat[2],
                'episEd' => $mat[2],
                'itemVr' => 1,
                'favTi' => preg_replace("/[\-$seps]{1,2}(\d{1,4})\b.*/", "", $ti), // may leave a "- "
                'matFnd' => "2_40-2"
            ];
        }
    }
}

function matchTitle2_41($ti, $seps) {
    // isolated MM-YYYY
    $mat = [];
    $re = "/\b(\d{1,2})[$seps]?-[$seps]?(\d{4})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        if (checkdate($mat[1] + 0, 1, $mat[2] + 0) && $mat[2] + 0 <= getdate()['year'] && $mat[2] + 0 > 1895) {
            // MM-YYYY
            if (strlen($mat[1]) == 1) {
                $mat[1] = "0" . $mat[1];
            }
            return [
                'medTyp' => 1,
                'numSeq' => 2,
                'seasSt' => 0,
                'seasEd' => 0,
                'episSt' => $mat[2] . $mat[1],
                'episEd' => $mat[2] . $mat[1],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_41-1"
            ];
        } else {
            // assume EE-YYYY
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => $mat[2], // Half date notation and half episode: let Season = YYYY
                'seasEd' => $mat[2],
                'episSt' => $mat[1],
                'episEd' => $mat[1],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_41-2"
            ];
        }
    }
}

function matchTitle2_42($ti, $seps) {
    // (YYYY) - EE or title (####) - EE
    $mat = [];
    $re = "/\((\d{4})\)[\-$seps]{0,3}(\d{1,4})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        if ($mat[1] + 0 <= getdate()['year'] && $mat[1] + 0 > 1895) {
            // (YYYY) - EE
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => $mat[1], // Half date notation and half episode: let Season = YYYY
                'seasEd' => $mat[1],
                'episSt' => $mat[2],
                'episEd' => $mat[2],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_42-1"
            ];
        } else {
            // #### is probably part of the title
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $mat[2],
                'episEd' => $mat[2],
                'itemVr' => 1,
                'favTi' => preg_replace("/(\))[\-$seps]{0,3}(\d{1,4})\b.*/", "$1", $ti),
                'matFnd' => "2_42-2"
            ];
        }
    }
}

function matchTitle2_43($ti, $seps) {
    // EEE - (YYYY)
    $mat = [];
    $re = "/\b(\d{1,3})[-$seps]{0,3}\((\d{4})\).*/";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[2], // Half date notation and half episode: let Season = YYYY
            'seasEd' => $mat[2],
            'episSt' => $mat[1],
            'episEd' => $mat[1],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "2_43-1"
        ];
    }
}

function matchTitle2_44($ti, $seps) {
    // isolated No.##-No.##, Print Media Book/Volume
    $mat = [];
    $re = "/\b(Num|No\.|No)[$seps]?(\d{1,4})[$seps]?(-|to|thru|through)[$seps]?(Num|No\.|No|)[$seps]?(\d{1,4})\b.*/i";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 4,
            'numSeq' => 1,
            'seasSt' => $mat[2],
            'seasEd' => $mat[5],
            'episSt' => 0,
            'episEd' => "",
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "2_44"
        ];
    }
}

function matchTitle2_45($ti, $seps) {
    // isolated S1 #10
    $mat = [];
    $re = "/\b[Ss](\d{1,2})[$seps]?#(\d{1,4})\b.*/";
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
            'matFnd' => "2_45"
        ];
    }
}

function matchTitle2_46($ti, $seps) {
    // isolated ## to ##
    $mat = [];
    $re = "/\b(\d{1,4})[$seps]?(through|thru|to|\x{e0})[$seps]?(\d{1,4})\b.*/i";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[1],
            'episEd' => $mat[3],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "2_46"
        ];
    }
}

function matchTitle2_47($ti, $seps) {
    // ID-## - ## (different spacing around minuses)
    $mat = [];
    if (preg_match("/\-(\d{1,4})[$seps]\-[$seps](\d{1,4})\b/", $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[2],
            'episEd' => $mat[2],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]\-[$seps](\d{1,4})\b.*/", "", $ti),
            'matFnd' => "2_47"
        ];
    }
}

function matchTitle2_48($ti, $seps) {
    // (##-##)
    $mat = [];
    $re = "/\([$seps]?(\d{1,4})[$seps]?\-[$seps]?(\d{1,4})[$seps]?\).*/";
    if (preg_match($re, $ti, $mat)) {
        if (substr($mat[2], 0, 1) == '0' && substr($mat[1], 0, 1) != '0') {
            // certainly (S - 0EE)
            // Examples:
            // Sword Art Online 2 - 07
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => $mat[1],
                'seasEd' => $mat[1],
                'episSt' => $mat[2],
                'episEd' => $mat[2],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_48-1"
            ];
        } else if ($mat[1] == 1) {
            // probably (1 - EE), since people rarely refer to Season 1 without mentioning Season|Seas|Sea|Se|S
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $mat[1],
                'episEd' => $mat[2],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_48-2"
            ];
        } else if (
                $mat[1] + 0 > 0 && // no Season 0
                strlen($mat[1]) < strlen($mat[2]) &&
                substr($mat[2], 0, 1) == '0'
        ) {
            // (SS - 0EE) or (S - 0E)
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => $mat[1],
                'seasEd' => $mat[1],
                'episSt' => $mat[2],
                'episEd' => $mat[2],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_48-3"
            ];
        } else if (
                substr($mat[1], 0, 1) == '0' || // leading digit of first ## is 0
                (
                strlen($mat[1]) > 1 && // first ## is more than 1 digit
                strlen($mat[2]) - strlen($mat[1]) < 2 && // second ## is no more than 1 digit longer
                $mat[1] + 0 < $mat[2] + 0 // second ## is greater than first ##
                )
        ) {
            // probably not a season but (EE - EE), such as 09 - 11
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $mat[1],
                'episEd' => $mat[2],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_48-4"
            ];
        } else {
            // (S - EE)
            // assume S - EE
            // Examples:
            // 3 - 17
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => $mat[1],
                'seasEd' => $mat[1],
                'episSt' => $mat[2],
                'episEd' => $mat[2],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_48-5"
            ];
        }
    }
}

function matchTitle2_49($ti, $seps) {
    // isolated ##-##
    $mat = [];
    $re = "/\b[$seps]?(\d{1,2})[$seps]?\-[$seps]?(\d{1,4})[$seps]?\b.*/";
    if (preg_match($re, $ti, $mat)) {
        //TODO MUST keep first ### less than 4 digits to prevent Magic Kaito 1412 - EE from matching, but may need to intercept it above
        if (substr($mat[2], 0, 1) == '0' && substr($mat[1], 0, 1) != '0') {
            // certainly S - 0EE
            // Examples:
            // Sword Art Online 2 - 07
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => $mat[1],
                'seasEd' => $mat[1],
                'episSt' => $mat[2],
                'episEd' => $mat[2],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_49-1"
            ];
        } else if ($mat[1] == 1) {
            // probably 1 - EE, since people rarely refer to Season 1 without mentioning Season|Seas|Sea|Se|S
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $mat[1],
                'episEd' => $mat[2],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_49-2"
            ];
        } else if (
                $mat[1] + 0 > 0 && // no Season 0
                strlen($mat[1]) < strlen($mat[2]) &&
                substr($mat[2], 0, 1) == '0'
        ) {
            // SS - 0EE or S - 0E
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => $mat[1],
                'seasEd' => $mat[1],
                'episSt' => $mat[2],
                'episEd' => $mat[2],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_49-3"
            ];
        } else if (
                substr($mat[1], 0, 1) == '0' || // leading digit of first ## is 0
                (
                strlen($mat[1]) > 1 && // first ## is more than 1 digit
                strlen($mat[2]) - strlen($mat[1]) < 2 && // second ## is no more than 1 digit longer
                $mat[1] + 0 < $mat[2] + 0 // second ## is greater than first ##
                )
        ) {
            // probably not a season but isolated EE - EE, such as 09 - 11
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $mat[1],
                'episEd' => $mat[2],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_49-4"
            ];
        } else {
            // isolated S - EE
            // assume S - EE
            // Examples:
            // 3 - 17
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => $mat[1],
                'seasEd' => $mat[1],
                'episSt' => $mat[2],
                'episEd' => $mat[2],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_49-5"
            ];
        }
    }
}

function matchTitle2_50($ti, $seps) {
    // S (EEE)
    // episode is in parentheses
    $mat = [];
    $re = "/\b(\d{1,2})[$seps]?[\-\(\)#\x{3010}\x{3011}][$seps]?(\d{1,4})[$seps]?[\-\(\)\x{3010}\x{3011}].*/u";
    if (preg_match($re, $ti, $mat)) {
        if ($mat[2] > 0) {
            // S (EEE)
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => $mat[1],
                'seasEd' => $mat[1],
                'episSt' => $mat[2],
                'episEd' => $mat[2],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_50-1"
            ];
        } else {
            // treat as PV 0
            return [
                'medTyp' => 1,
                'numSeq' => 8,
                'seasSt' => $mat[1],
                'seasEd' => $mat[1],
                'episSt' => 0,
                'episEd' => 0,
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_50-2"
            ];
        }
    }
}

function matchTitle2_51($ti, $seps) {
    // isolated SS EE, SS EEE, or isolated EE EE
    $mat = [];
    $re = "/\b(\d{1,4})[$seps](\d{1,4})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        if (
                (strlen($mat[1]) < strlen($mat[2])) || // SS EEE or S EE
                ($mat[1] >= $mat[2]) // EE EE usually has lower episodes first
        ) {
            // isolated SS EE
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => $mat[1],
                'seasEd' => $mat[1],
                'episSt' => $mat[2],
                'episEd' => $mat[2],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_51-1"
            ];
        } else if (
                $mat[1] < $mat[2] &&
                $mat[1] < 6 && // most cours never pass 5
                $mat[1] + 1 != $mat[2] && // seq. numbers likely EE EE
                $mat[2] - $mat[1] < 14 // seasons usually have less than 14 episodes
        ) {
            // isolated SS EE
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => $mat[1],
                'seasEd' => $mat[1],
                'episSt' => $mat[2],
                'episEd' => $mat[2],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_51-2"
            ];
        } else {
            // isolated EE EE
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $mat[1],
                'episEd' => $mat[2],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_51-3"
            ];
        }
    }
}
