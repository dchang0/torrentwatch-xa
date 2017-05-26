<?php

// contains just the matchTitle functions for exactly 1 number found in the title

function matchTitle1_1_1($ti, $seps) {
    // Season, Temporada; should also catch Season ## Complete
    $mat = [];
    $re = "/(Season|Saison|Seizoen|Sezona|\bSeas\.|\bSeas|\bSais\.|\bSais\.|\bSea|\bSea|\bSe\.|\bSe|\bS\.|\bS|Temporada|\bTemp\.|\bTemp|\bT\.|\bT)[$seps]?S?(\d+)\b.*/i";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1, // assume Video Media
            'numSeq' => 4, // video Season x Volume/Part numbering
            'seasSt' => $mat[2],
            'seasEd' => $mat[2],
            'episSt' => 1,
            'episEd' => "",
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "1_1_1"
        ];
    }
}

function matchTitle1_1_2($ti, $seps) {
    // ##rd/##th Season
    $mat = [];
    $re = "/(\d{1,2})(st|nd|rd|th)[$seps]?(Season|Seas\b|Seas\b|Sea\b|Se\b|S\b).*/i";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1, // assume Video Media
            'numSeq' => 4, // video Season x Volume/Part numbering
            'seasSt' => $mat[1],
            'seasEd' => $mat[1],
            'episSt' => 1,
            'episEd' => "",
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "1_1_2"
        ];
    }
}

function matchTitle1_1_3($ti, $seps, $detVid) {
    // Volume, Volumen ##
    $mat = [];
    $re = "/(Volumen|Volume|\bVol\.|\bVol)[$seps]?(\d+).*/i";
    if (preg_match($re, $ti, $mat)) {
        if ($detVid === true) {
            return [
                'medTyp' => 1, // Video Media
                'numSeq' => 4, // video Season x Volume/Part numbering
                'seasSt' => 1, // assume Season 1
                'seasEd' => 1,
                'episSt' => $mat[2],
                'episEd' => $mat[2],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "1_1_3-1"
            ];
        } else {
            return [
                'medTyp' => 4, // assume Print Media
                'numSeq' => 1, // Volume x FULL
                'seasSt' => $mat[2],
                'seasEd' => $mat[2],
                'episSt' => 1,
                'episEd' => "",
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "1_1_3-4"
            ];
        }
    }
}

function matchTitle1_1_4($ti, $seps, $detVid) {
    // V. ##--Volume, not version, and not titles like ARC-V
    $mat = [];
    $re = "/[$seps]V[$seps]{1,2}(\d+).*/";
    if (preg_match($re, $ti, $mat)) {
        if ($detVid === true) {
            return [
                'medTyp' => 1, // Video Media
                'numSeq' => 4, // video Season x Volume/Part numbering
                'seasSt' => 1, // assume Season 1
                'seasEd' => 1,
                'episSt' => $mat[1],
                'episEd' => $mat[1],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "1_1_4-1"
            ];
        } else {
            return [
                'medTyp' => 4, // assume Print Media
                'numSeq' => 4, // video Season x Volume/Part numbering
                'seasSt' => $mat[1],
                'seasEd' => $mat[1],
                'episSt' => 0,
                'episEd' => 0,
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "1_1_4-4"
            ];
        }
    }
}

function matchTitle1_1_5($ti, $seps) {
    // Chapter, Capitulo ##
    $mat = [];
    $re = "/(Chapter|Capitulo|Chapitre|\bChap\.|\bChap|\bCh\.|\bCh|\bC\.|\bC)[$seps]?(\d+).*/i";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 4, // assume Print Media
            'numSeq' => 1, // assume ## x ## number sequence
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[2],
            'episEd' => $mat[2],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "1_1_5"
        ];
    }
}

function matchTitle1_1_6($ti, $seps) {
    // Movie ##
    $mat = [];
    if (preg_match("/(Movie|\bMov)[$seps]?(\d+)/i", $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 64,
            'seasSt' => 0,
            'seasEd' => 0,
            'episSt' => $mat[2],
            'episEd' => $mat[2],
            'itemVr' => 1,
            'favTi' => preg_replace("/(\d+).*/", "", $ti),
            'matFnd' => "1_1_6"
        ];
    }
}

function matchTitle1_1_7($ti, $seps) {
    // Movie v##
    $mat = [];
    if (preg_match("/(Movie|\bMov)[$seps]v(\d+)/i", $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 64,
            'seasSt' => 0,
            'seasEd' => 0,
            'episSt' => 1,
            'episEd' => 1,
            'itemVr' => $mat[2],
            //'favTi' => "Cannot match movies without sequential numbering.",
            'favTi' => preg_replace("/v(\d+).*/i", "", $ti),
            'matFnd' => "1_1_7"
        ];
    }
}

function matchTitle1_1_8($ti, $seps) {
    // Film ##
    $mat = [];
    if (preg_match("/(Film|\bF)[$seps]?(\d+)/i", $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 64,
            'seasSt' => 0,
            'seasEd' => 0,
            'episSt' => $mat[2],
            'episEd' => $mat[2],
            'itemVr' => 1,
            'favTi' => preg_replace("/(\d+).*/", "", $ti),
            'matFnd' => "1_1_8"
        ];
    }
}

function matchTitle1_1_9($ti, $seps) {
    // Film v##
    $mat = [];
    if (preg_match("/(Film|\bF)[$seps]v(\d+)/i", $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 64,
            'seasSt' => 0,
            'seasEd' => 0,
            'episSt' => 1,
            'episEd' => 1,
            'itemVr' => $mat[2],
            'favTi' => preg_replace("/v(\d+).*/i", "", $ti),
            'matFnd' => "1_1_9"
        ];
    }
}

function matchTitle1_1_10($ti, $seps) {
    // Part ##
    $mat = [];
    $re = "/(Part|\bPt)[$seps]?(\d+).*/i";
    if (preg_match("/(Part|\bPt)[$seps]?(\d+)/i", $ti, $mat)) {
        //TODO handle Part ##
        return [
            'medTyp' => 1,
            'numSeq' => 4,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[2],
            'episEd' => $mat[2],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "1_1_10"
        ];
    }
}

function matchTitle1_1_11($ti, $seps) {
    // Episode ##
    // should not be any mention of Season ## before Episode ## because only one ## found
    $mat = [];
    $re = "/(Episode|Epis\.|Epis|Epi\.|Epi|\bEp\.|\bEp|\bE\.|\bE)[$seps]?(\d+).*/i";
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
            'matFnd' => "1_1_11"
        ];
    }
}

function matchTitle1_1_12($ti, $seps) {
    // Special v##
    $mat = [];
    $re = "/\b(Special|Spec\.|Spec|Spc\.|Spc|Sp\.|Sp)[$seps]v(\d+).*/i";
    if (preg_match("/\b(Special|Spec\.|Spec|Spc\.|Spc|Sp\.|Sp)[$seps]v(\d+)/i", $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 16,
            'seasSt' => 1, // assume Season 1
            'seasEd' => 1,
            'episSt' => 1, // assume Special Episode 1
            'episEd' => 1,
            'itemVr' => $mat[2], // only number is the version number
            //'favTi' => preg_replace("/v(\d+)/i", "", $ti),
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "1_1_12"
        ];
    }
}

function matchTitle1_1_13($ti, $seps) {
    // 02 - Special
    $mat = [];
    $re = "/\b(\d{1,2})[\-$seps]{0,3}(Specials|Special|Spec\.|Spec|Spc\.|Spc|Sp\.|Sp\b).*/i";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 16,
            'seasSt' => 1, // assume Season 1
            'seasEd' => 1,
            'episSt' => $mat[1],
            'episEd' => $mat[1],
            'itemVr' => 1,
            //'favTi' => preg_replace("/\b(\d+)[$seps]?-?/", "", $ti),
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "1_1_13"
        ];
    }
}

function matchTitle1_1_14($ti, $seps) {
    // Special - 02
    // Spec02
    // SP# (Special #)
    $mat = [];
    $re = "/\b(Specials|Special|Spec\.|Spec|Spc\.|Spc|Sp\.)[\-$seps]{0,3}(\d{1,2}).*/i";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 16,
            'seasSt' => 1, // assume Season 1
            'seasEd' => 1,
            'episSt' => $mat[2],
            'episEd' => $mat[2],
            'itemVr' => 1,
            //'favTi' => preg_replace("/[\-$seps]{0,3}(\d{1,2})/i", "", $ti),
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "1_1_14"
        ];
    }
}

function matchTitle1_1_15($ti, $seps) {
    // OVA v##
    $mat = [];
    $re = "/\b(OVA|OAV)[$seps]?v(\d+).*/i";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 32,
            'seasSt' => 1, // assume Season 1
            'seasEd' => 1,
            'episSt' => 1, // assume OVA Episode 1
            'episEd' => 1,
            'itemVr' => $mat[2], // only number is the version number
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "1_1_15"
        ];
    }
}

function matchTitle1_1_16($ti, $seps) {
    // OVA (####) or OVA (YYYY) or OVA ####
    $mat = [];
    $re = "/\b(OVA|OAV)[$seps]?\(?[$seps]?(\d{1,4})[$seps]?\)?.*/i";
    if (preg_match($re, $ti, $mat)) {
        if ($mat[2] > 1895 && $mat[2] <= getdate()['year']) {
            // probably a date, use as Season
            return [
                'medTyp' => 1,
                'numSeq' => 32,
                'seasSt' => $mat[2],
                'seasEd' => $mat[2],
                'episSt' => 1, // assume OVA Episode 1
                'episEd' => 1,
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "1_1_16-1"
            ];
        } else {
            return [
                'medTyp' => 1,
                'numSeq' => 32,
                'seasSt' => 1, // assume Season 1
                'seasEd' => 1,
                'episSt' => $mat[2],
                'episEd' => $mat[2],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "1_1_16-2"
            ];
        }
    }
}

function matchTitle1_1_18($ti, $seps) {
    // Roman numeral SS-EE; we only handle Seasons I, II, and III, not IV and up
    // NOTE: The ability to detect Roman numeral seasons means that to match the title, one must NOT
    // put the Roman numeral season in the Favorite filter. For example: "Sword Art Online II" would not
    // work as a Favorite Filter because the "II" would get stripped out of the title by detectItem().
    // Use "Sword Art Online" instead. But this is counterintuitive--people would think of the "II" as being
    // part of the title.
    $mat = [];
    $re = "/\b(I{1,3})[\-$seps]{0,3}(\d+).*/";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => strlen($mat[1]),
            'seasEd' => strlen($mat[1]),
            'episSt' => $mat[2],
            'episEd' => $mat[2],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "1_1_18"
        ];
    }
}

function matchTitle1_1_19($ti, $seps) {
    // pound sign and number
    // could be Vol., Number, but assume it's an Episode
    $mat = [];
    $re = "/#[$seps]?(\d{1,3}).*/";
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
            'matFnd' => "1_1_19"
        ];
    }
}

function matchTitle1_1_20($ti, $seps) {
    // apostrophe ## (abbreviated year)
    $mat = [];
    $re = "/'(\d\d)\b.*/";
    if (preg_match($re, $ti, $mat)) {
        $thisYear = getdate()['year'];
        $guessedYearCurrentCentury = substr($thisYear, 0, 2) . $mat[1];
        $guessedYearPriorCentury = substr($thisYear - 100, 0, 2) . $mat[1];
        if ($guessedYearCurrentCentury + 0 <= $thisYear && $guessedYearCurrentCentury + 0 > 1895) {
            return [
                'medTyp' => 1,
                'numSeq' => 2,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $guessedYearCurrentCentury,
                'episEd' => $guessedYearCurrentCentury,
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "1_1_20-1"
            ];
        } else if ($guessedYearPriorCentury + 0 <= $thisYear && $guessedYearPriorCentury + 0 > 1895) {
            return [
                'medTyp' => 1,
                'numSeq' => 2,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $guessedYearPriorCentury,
                'episEd' => $guessedYearPriorCentury,
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "1_1_20-2"
            ];
        } else {
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $mat[1],
                'episEd' => $mat[1],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "1_1_20-3"
            ];
        }
    }
}

function matchTitle1_1_21($ti, $seps) {
    // PV ##
    $mat = [];
    $re = "/\bPV[$seps]?(\d{1,2})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 8,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[1],
            'episEd' => $mat[1],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "1_1_21"
        ];
    }
}

function matchTitle1_1_22($ti, $seps) {
    // standalone Version ##
    $mat = [];
    $re = "/(Version|Vers\.|Vers|\bVer\.|\bVer|\bv)[$seps]?(\d{1,2}).*/i";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 64, // assume Movie numbering
            'seasSt' => 0, // for Movies, assume Season = 0
            'seasEd' => 0,
            'episSt' => 1, // assume Movie 1
            'episEd' => 1,
            'itemVr' => $mat[2],
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "1_1_22"
        ];
    }
}

function matchTitle1_1_30_1($ti, $seps) {
    // Japanese ## Episode
    $mat = [];
    $re = "/\x{7B2C}(\d+)(\x{8a71}|\x{8bdd}).*/u";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => mb_convert_kana($mat[1], "a", "UTF-8"),
            'episEd' => mb_convert_kana($mat[1], "a", "UTF-8"),
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "1_1_30_1"
        ];
    }
}

function matchTitle1_1_30_2($ti, $seps) {
    // Japanese ## Print Media Book/Volume
    $mat = [];
    $re = "/(\x{7B2C}|\x{5168})(\d+)\x{5dfb}.*/u";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 4,
            'numSeq' => 1,
            'seasSt' => mb_convert_kana($mat[2], "a", "UTF-8"),
            'seasEd' => mb_convert_kana($mat[2], "a", "UTF-8"),
            'episSt' => 1,
            'episEd' => "",
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "1_1_30_2"
        ];
    }
}

function matchTitle1_1_30_3($ti, $seps) {
    // Japanese ##
    $mat = [];
    $re = "/\x{7B2C}(\d+).*/u";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => mb_convert_kana($mat[1], "a", "UTF-8"),
            'episEd' => mb_convert_kana($mat[1], "a", "UTF-8"),
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "1_1_30_3"
        ];
    }
}

function matchTitle1_1_30_4($ti, $seps) {
    // Chinese sequel ##
    $mat = [];
    $re = "/\x{7EED}(\d+).*/u";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => mb_convert_kana($mat[1], "a", "UTF-8"),
            'episEd' => mb_convert_kana($mat[1], "a", "UTF-8"),
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "1_1_30_4"
        ];
    }
}

function matchTitle1_1_30_5($ti, $seps) {
    // Chinese ## preview image/video
    $mat = [];
    $re = "/(\d+)\x{9884}\x{544a}\x{5f71}\x{50cf}.*/u";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 8,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => mb_convert_kana($mat[1], "a", "UTF-8"),
            'episEd' => mb_convert_kana($mat[1], "a", "UTF-8"),
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "1_1_30_5"
        ];
    }
}

function matchTitle1_1_30_6($ti, $seps) {
    // isolated or buttressed EEE
    $mat = [];
    //$re = "/([\+\-\(#\x{3010}\x{3011}\x{7B2C}]?[$seps]?)(\d+)([$seps]?)([\+\-\)\x{3010}\x{3011}]?|$)/u";
    $re = "/([$seps\+\-\(\)#\x{3010}\x{3011}\x{7B2C}])(\d+)([$seps\+\-\(\)\x{3010}\x{3011}]|$).*/u";
    if (preg_match($re, $ti, $mat)) {
        if ($mat[2] + 0 > 0) {
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $mat[2],
                'episEd' => $mat[2],
                'itemVr' => 1,
                //'favTi' => preg_replace($re, "$1$3", $ti),
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "1_1_30_6-1"
            ];
        } else {
            // isolated or buttressed EEE = 0, treat as PV 0
            return [
                'medTyp' => 1,
                'numSeq' => 8,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => 0,
                'episEd' => 0,
                'itemVr' => 1,
                //'favTi' => preg_replace($re, "$1$3", $ti),
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "1_1_30_6-2"
            ];
        }
    }
}
