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

function matchTitle2_1000($ti, $seps) {
    // word####word####
    $mat = [];
    $re = "/\b\b([A-Za-z]+\.?)[$seps]?(\d{1,4})(\ \-\ |\,\ |\-|\,|\.|\ |)([A-Za-z]+\.?)[$seps]?(\d{1,4})\b.*/i";
    if (preg_match($re, $ti, $mat)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[2],
            'seasEd' => $mat[2],
            'episSt' => $mat[5],
            'episEd' => $mat[5],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "2_1000"
        ];
    }
}

function matchTitle2_3($ti, $seps) {
    // ## - v# (Episode ## Version #)
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

function matchTitle2_4($ti, $seps, $detVid = false) {
    // word #### -|through|thru|to ####
    $mat = [];
    $re = "/\b([A-Za-z]+\.?)[$seps]?(\d{1,4})[$seps]?(\-|through|thru|to)[$seps]?(\d{1,4})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        switch (strtolower($mat[1])) {
            case 'seasons' :
            case 'saisons' :
                // Seasons ### - ###
                $result = [
                    'medTyp' => 1,
                    'numSeq' => 1,
                    'seasSt' => $mat[2],
                    'seasEd' => $mat[4],
                    'episSt' => 1,
                    'episEd' => "",
                    'itemVr' => 1,
                    'matFnd' => "2_4-1"
                ];
                break;
            case 'season' :
            case 'saison' :
            case 'seizoen' :
            case 'sezona' :
            case 'seas.' :
            //case 'seas' :
            case 'sais.' :
            case 'sais' :
            case 'sea.' :
            //case 'sea' :
            case 'se.' :
            case 'se' :
            case 's.' :
            case 's' :
            case 'temporada' :
            case 'temp.' :
            //case 'temp' :
            case 't.' :
                //case 't' :
                // Season, Temporada ### - EEE
                $result = [
                    'medTyp' => 1,
                    'numSeq' => 1,
                    'seasSt' => $mat[2],
                    'seasEd' => $mat[2],
                    'episSt' => $mat[4],
                    'episEd' => $mat[4],
                    'itemVr' => 1,
                    'matFnd' => "2_4-2"
                ];
                break;
            case 'volumes' :
            case 'volume' :
            case 'volumens' :
            case 'vols.' :
            case 'vols' :
            case 'vol.' :
            case 'vol' :
            case 'v.' : // with ### - ### we assume this is volumes not version
            case 'v' : // might catch roman numeral V as part of title
                if ($detVid) {
                    $result = [
                        // video Volumes
                        'medTyp' => 1,
                        'numSeq' => 4, // Season x Volume
                        'seasSt' => 1, // assume Season 1
                        'seasEd' => 1,
                        'episSt' => $mat[2],
                        'episEd' => $mat[4],
                        'itemVr' => 1,
                        'matFnd' => "2_4-3-1"
                    ];
                } else {
                    $result = [
                        // print Volumes
                        'medTyp' => 1,
                        'numSeq' => 1,
                        'seasSt' => $mat[2],
                        'seasEd' => $mat[4],
                        'episSt' => 1,
                        'episEd' => "",
                        'itemVr' => 1,
                        'matFnd' => "2_4-3-2"
                    ];
                }
                break;
            case 'chapters' :
            case 'chapter' :
            case 'capitulos' :
            case 'capitulo' :
            case 'chapitres' :
            case 'chapitre' :
            case 'chap.' :
            case 'chap' : // might be part of title
            case 'ch.' :
            case 'ch' :
            case 'c.' :
            case 'c' : // might be part of title
                $result = [
                    // print Chapters
                    'medTyp' => 4,
                    'numSeq' => 1,
                    'seasSt' => 1,
                    'seasEd' => 1,
                    'episSt' => $mat[2],
                    'episEd' => $mat[4],
                    'itemVr' => 1,
                    'matFnd' => "2_4-4"
                ];
                break;
            case 'batch' :
                // Batch ### - ###
                $result = [
                    'medTyp' => 1,
                    'numSeq' => 1,
                    'seasSt' => 1, // assume Season 1
                    'seasEd' => 1,
                    'episSt' => $mat[2],
                    'episEd' => $mat[4],
                    'itemVr' => 1,
                    'matFnd' => "2_4-5"
                ];
        }
        $result['favTi'] = preg_replace($re, "", $ti);
        return $result;
    }
}

function matchTitle2_5($ti, $seps) {
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
            'matFnd' => "2_5"
        ];
    }
}

function matchTitle2_6($ti, $seps) {
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
            'matFnd' => "2_6"
        ];
    }
}

function matchTitle2_7($ti, $seps) {
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
            'matFnd' => "2_7"
        ];
    }
}

function matchTitle2_8($ti, $seps) {
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
            'matFnd' => "2_8"
        ];
    }
}

function matchTitle2_9($ti, $seps, $detVid = false) {
    // V##.## (Software Version ##.##)
    $mat = [];
    $re = "/(Version|Vers\.|Vers|Ver\.|Ver|V\.|V)[$seps]?(\d{1,2})\.(\d{1,4})\b.*/";
    if (preg_match($re, $ti, $mat) && !$detVid) {
        return [
            'medTyp' => 0,
            'numSeq' => 0,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => 1,
            'episEd' => 1,
            'itemVr' => (float) $mat[2] . "." . $mat[3],
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "2_9"
        ];
    }
}

function matchTitle2_10($ti, $seps) {
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
            'matFnd' => "2_10"
        ];
    }
}

function matchTitle2_11($ti, $seps) {
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
            'matFnd' => "2_11"
        ];
    }
}

function matchTitle2_12($ti, $seps, $detVid = false) {
    // word ### of ###
    $mat = [];
    $re = "/\b([A-Za-z]+\.?)[$seps]?(\d{1,3})[$seps]?of[$seps]?(\d{1,3})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        switch (strtolower($mat[1])) {
            case 'volume' :
            case 'vol.' :
            case 'vol' :
            case 'v.' : // with ### - ### we assume this is volumes not version
            case 'v' : // might catch roman numeral V as part of title
                if ($detVid) {
                    $result = [
                        // video Volumes
                        'medTyp' => 1,
                        'numSeq' => 4,
                        'seasSt' => $mat[2],
                        'seasEd' => $mat[2],
                        'episSt' => 1,
                        'episEd' => "",
                        'itemVr' => 1,
                        'matFnd' => "2_12-1-1"
                    ];
                } else {
                    $result = [
                        // print Volumes
                        'medTyp' => 4,
                        'numSeq' => 1,
                        'seasSt' => $mat[2],
                        'seasEd' => $mat[2],
                        'episSt' => 1,
                        'episEd' => "",
                        'itemVr' => 1,
                        'matFnd' => "2_12-1-2"
                    ];
                }
                break;
            case 'chapter' :
            case 'capitulo' :
            case 'chapitre' :
            case 'chap.' :
            case 'chap' : // might be part of title
            case 'ch.' :
            case 'ch' :
            case 'c.' :
            case 'c' : // might be part of title
                $result = [
                    'medTyp' => 4,
                    'numSeq' => 1,
                    'seasSt' => 1,
                    'seasEd' => 1,
                    'episSt' => $mat[2],
                    'episEd' => $mat[2],
                    'itemVr' => 1,
                    'matFnd' => "2_12-2"
                ];
                break;
            case 'season' :
            case 'saison' :
            case 'seizoen' :
            case 'sezona' :
            case 'sais.' :
            case 'sais' :
            case 'sea.' :
            //case 'sea' :
            case 'se.' :
            case 'se' :
            case 's.' :
            case 's' :
            case 'temporada' :
            case 'temp.' :
            //case 'temp' :
            case 't.' :
                //case 't' :
                $result = [
                    'medTyp' => 1,
                    'numSeq' => 1,
                    'seasSt' => $mat[2],
                    'seasEd' => $mat[2],
                    'episSt' => 1,
                    'episEd' => "",
                    'itemVr' => 1,
                    'matFnd' => "2_12-3"
                ];
                break;
            case 'part' :
            case 'pt.' :
            case 'pt' :
                $result = [
                    'medTyp' => 1,
                    'numSeq' => 128,
                    'seasSt' => 1, // assume Volume 1
                    'seasEd' => 1,
                    'episSt' => $mat[2],
                    'episEd' => $mat[2],
                    'itemVr' => 1,
                    'matFnd' => "2_12-4"
                ];
                break;
            case 'episode' :
            case 'epis.' :
            case 'epis' :
            case 'epi.' :
            case 'epi' :
            case 'ep.' :
            // case 'ep' :
            case 'e.' :
                //case 'e' :
                $result = [
                    'medTyp' => 1,
                    'numSeq' => 1,
                    'seasSt' => 1,
                    'seasEd' => 1,
                    'episSt' => $mat[2],
                    'episEd' => $mat[2],
                    'itemVr' => 1,
                    'matFnd' => "2_12-5"
                ];
        }
        $result['favTi'] = preg_replace($re, "", $ti);
        return $result;
    }
}

function matchTitle2_13($ti, $seps) {
    // isolated ## of ##
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
            'matFnd' => "2_13"
        ];
    }
}

function matchTitle2_14($ti, $seps, $detVid = false) {
    // word ### & ###
    $mat = [];
    $re = "/\b([A-Za-z]+\.?)[$seps]?(\d{1,3})[$seps]?(\&|and|\+|y|et)[$seps]?(\d{1,3})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        switch (strtolower($mat[1])) {
            case 'volumes' :
            case 'volume' :
            case 'volumens' :
            case 'vols.' :
            case 'vols' :
            case 'vol.' :
            case 'vol' :
            case 'v.' : // with ### - ### we assume this is volumes not version
            case 'v' : // might catch roman numeral V as part of title
                if ($detVid) {
                    $result = [
                        // video Volumes
                        'medTyp' => 1,
                        'numSeq' => 4,
                        'seasSt' => $mat[2],
                        'seasEd' => $mat[4],
                        'episSt' => 1,
                        'episEd' => "",
                        'itemVr' => 1,
                        'matFnd' => "2_14-1-1"
                    ];
                } else {
                    $result = [
                        // print Volumes
                        'medTyp' => 4,
                        'numSeq' => 1,
                        'seasSt' => $mat[2],
                        'seasEd' => $mat[4],
                        'episSt' => 1,
                        'episEd' => "",
                        'itemVr' => 1,
                        'matFnd' => "2_14-1-2"
                    ];
                }
                break;
            case 'chapters' :
            case 'chapter' :
            case 'capitulos' :
            case 'capitulo' :
            case 'chapitres' :
            case 'chapitre' :
            case 'chap.' :
            case 'chap' : // might be part of title
            case 'ch.' :
            case 'ch' :
            case 'c.' :
            case 'c' : // might be part of title
                $result = [
                    'medTyp' => 4,
                    'numSeq' => 1,
                    'seasSt' => 1,
                    'seasEd' => 1,
                    'episSt' => $mat[2],
                    'episEd' => $mat[4],
                    'itemVr' => 1,
                    'matFnd' => "2_14-2"
                ];
                break;
            case 'seasons' :
            case 'saisons' :
            case 'season' :
            case 'saison' :
            case 'seizoen' :
            case 'sezona' :
            case 'seas.' :
            //case 'seas' :
            case 'sais.' :
            case 'sais' :
            case 'sea.' :
            //case 'sea' :
            case 'se.' :
            case 'se' :
            case 's.' :
            case 's' :
            case 'temporada' :
            case 'temp.' :
            //case 'temp' :
            case 't.' :
                //case 't' :
                $result = [
                    'medTyp' => 1,
                    'numSeq' => 1,
                    'seasSt' => $mat[2],
                    'seasEd' => $mat[4],
                    'episSt' => 1,
                    'episEd' => "",
                    'itemVr' => 1,
                    'matFnd' => "2_14-3"
                ];
                break;
            case 'parts' :
            case 'part' :
            case 'pt.' :
            case 'pt' :
                $result = [
                    'medTyp' => 1,
                    'numSeq' => 128,
                    'seasSt' => 1, // assume Volume 1
                    'seasEd' => 1,
                    'episSt' => $mat[2],
                    'episEd' => $mat[4],
                    'itemVr' => 1,
                    'matFnd' => "2_14-4"
                ];
                break;
            case 'episodes' :
            case 'episode' :
            case 'epis.' :
            case 'epis' :
            case 'epi.' :
            case 'epi' :
            case 'eps.' :
            case 'eps' :
            case 'ep.' :
            // case 'ep' :
            case 'e.' :
                //case 'e' :
                $result = [
                    'medTyp' => 1,
                    'numSeq' => 1,
                    'seasSt' => 1,
                    'seasEd' => 1,
                    'episSt' => $mat[2],
                    'episEd' => $mat[4],
                    'itemVr' => 1,
                    'matFnd' => "2_14-5"
                ];
        }
        $result['favTi'] = preg_replace($re, "", $ti);
        return $result;
    }
}

function matchTitle2_15($ti, $seps) {
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
            'matFnd' => "2_15"
        ];
    }
}

function matchTitle2_16($ti, $seps, $detVid = false) {
    // word #### word ####
    // word ####, word ####
    $mat = [];
    $re = "/\b([A-Za-z]+\.?)[$seps]?(\d{1,4}),?[$seps]?([A-Za-z]+\.?)[$seps]?(\d{1,4})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        switch (strtolower($mat[1])) {
            case 'volumes' :
            case 'volume' :
            case 'volumens' :
            case 'vols.' :
            case 'vols' :
            case 'vol.' :
            case 'vol' :
            case 'v.' : // with ### - ### we assume this is volumes not version
            case 'v' : // might catch roman numeral V as part of title
                switch (strtolower($mat[3])) {
                    case 'chapters' :
                    case 'chapter' :
                    case 'capitulos' :
                    case 'capitulo' :
                    case 'chapitres' :
                    case 'chapitre' :
                    case 'chap.' :
                    case 'chap' : // might be part of title
                    case 'ch.' :
                    case 'ch' :
                    case 'c.' :
                    case 'c' : // might be part of title
                        $result = [
                            'medTyp' => 4, // print Volume x Chapter
                            'numSeq' => 1,
                            'seasSt' => $mat[2],
                            'seasEd' => $mat[2],
                            'episSt' => $mat[4],
                            'episEd' => $mat[4],
                            'itemVr' => 1,
                            'matFnd' => "2_16-1"
                        ];
                }
                break;
            case 'chapters' :
            case 'chapter' :
            case 'capitulos' :
            case 'capitulo' :
            case 'chapitres' :
            case 'chapitre' :
            case 'chap.' :
            case 'chap' : // might be part of title
            case 'ch.' :
            case 'ch' :
            case 'c.' :
            case 'c' : // might be part of title
                switch (strtolower($mat[3])) {
                    case 'volumes' :
                    case 'volume' :
                    case 'volumens' :
                    case 'vols.' :
                    case 'vols' :
                    case 'vol.' :
                    case 'vol' :
                    case 'v.' : // with ### - ### we assume this is volumes not version
                    case 'v' : // might catch roman numeral V as part of title
                        $result = [
                            'medTyp' => 4, // print Volume x Chapter
                            'numSeq' => 1,
                            'seasSt' => $mat[4],
                            'seasEd' => $mat[4],
                            'episSt' => $mat[2],
                            'episEd' => $mat[2],
                            'itemVr' => 1,
                            'matFnd' => "2_16-2"
                        ];
                }
                break;
            case 'seasons' :
            case 'saisons' :
            case 'season' :
            case 'saison' :
            case 'seizoen' :
            case 'sezona' :
            case 'seas.' :
            case 'seas' :
            case 'sais.' :
            case 'sais' :
            case 'sea.' :
            case 'sea' :
            case 'se.' :
            case 'se' :
            case 's.' :
            case 's' :
            case 'temporada' :
            case 'temp.' :
            case 'temp' :
            case 't.' :
            case 't' :
                switch (strtolower($mat[3])) {
                    case 'episodes' :
                    case 'episode' :
                    case 'epis.' :
                    case 'epis' :
                    case 'epi.' :
                    case 'epi' :
                    case 'eps.' :
                    case 'eps' :
                    case 'ep.' :
                    case 'ep' :
                    case 'e.' :
                    case 'e' :
                        $result = [
                            'medTyp' => 1,
                            'numSeq' => 1,
                            'seasSt' => $mat[2],
                            'seasEd' => $mat[2],
                            'episSt' => $mat[4],
                            'episEd' => $mat[4],
                            'itemVr' => 1,
                            'matFnd' => "2_16-3"
                        ];
                }
                break;
            case 'episodes' :
            case 'episode' :
            case 'epis.' :
            case 'epis' :
            case 'epi.' :
            case 'epi' :
            case 'eps.' :
            case 'eps' :
            case 'ep.' :
            case 'ep' :
            case 'e.' :
            case 'e' :
                switch (strtolower($mat[3])) {
                    case 'seasons' :
                    case 'saisons' :
                    case 'season' :
                    case 'saison' :
                    case 'seizoen' :
                    case 'sezona' :
                    case 'seas.' :
                    case 'seas' :
                    case 'sais.' :
                    case 'sais' :
                    case 'sea.' :
                    case 'sea' :
                    case 'se.' :
                    case 'se' :
                    case 's.' :
                    case 's' :
                    case 'temporada' :
                    case 'temp.' :
                    case 'temp' :
                    case 't.' :
                    case 't' :
                        $result = [
                            'medTyp' => 1,
                            'numSeq' => 1,
                            'seasSt' => $mat[4],
                            'seasEd' => $mat[4],
                            'episSt' => $mat[2],
                            'episEd' => $mat[2],
                            'itemVr' => 1,
                            'matFnd' => "2_16-4"
                        ];
                }
        }
        $result['favTi'] = preg_replace($re, "", $ti);
        return $result;
    }
}

function matchTitle2_17($ti, $seps) {
    // c## (v##)
    $mat = [];
    $re = "/(Chapters|Chapter|Capitulos|Capitulo|Chapitres|Chapitre|\bChap\.|\bChap|\bCh\.|\bCh|\bC\.|\bC)[$seps]?(\d{1,4})[$seps]?\([$seps]?(Volumen|Volume|\bVol\.|\bVol|\bV\.)[$seps]?(\d{1,3})[$seps]?\).*/i";
    if (preg_match($re, $ti, $mat)) {
        return [
            // print Volume x Chapter
            'medTyp' => 4,
            'numSeq' => 1,
            'seasSt' => $mat[4],
            'seasEd' => $mat[4],
            'episSt' => $mat[2],
            'episEd' => $mat[2],
            'itemVr' => 1,
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "2_17"
        ];
    }
}

function matchTitle2_18($ti, $seps) {
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
            'matFnd' => "2_18"
        ];
    }
}

function matchTitle2_19($ti, $seps) {
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
            'matFnd' => "2_19"
        ];
    }
}

function matchTitle2_20($ti, $seps) {
    // Japanese YYYY MM or YYYY ## Print Media
    $mat = [];
    $re = "/(\b|\D)(\d{2}|\d{4})\x{5e74}?[\-$seps]{1,3}(\d{1,2})\x{6708}?\x{53f7}.*/u";
    if (preg_match($re, $ti, $mat)) {
        if (strlen($mat[3]) == 1) {
            $mat[3] = '0' . $mat[3];
        }
        if ($mat[3] != '' && checkdate((int) $mat[3], 1, (int) $mat[2])) {
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
                'matFnd' => "2_20-1"
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
                'matFnd' => "2_20-2"
            ];
        }
    }
}

function matchTitle2_21($ti, $seps) {
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
            'matFnd' => "2_21"
        ];
    }
}

function matchTitle2_22($ti) {
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
            'matFnd' => "2_22"
        ];
    }
}

function matchTitle2_23($ti, $seps, $detVid = false) {
    // isolated YYYY-YYYY or YYYY-MM or or YYYY-EE or title #### - EE
    $mat = [];
    $re = "/\b(\d{4})[\-$seps]{1,3}(\d{1,4})\b.*/"; // this blocks #### ##
    if (preg_match($re, $ti, $mat)) {
        $thisYear = getdate()['year'];
        if (
                strlen($mat[2]) === 4 &&
                (int) $mat[2] > (int) $mat[1] &&
                (int) $mat[2] <= $thisYear &&
                (int) $mat[1] > 1895 &&
                (int) $mat[2] < (int) $mat[1] + 20
        ) {
            // YYYY - YYYY
            if ($detVid) {
                return [
                    'medTyp' => 1,
                    'numSeq' => 2,
                    'seasSt' => 0,
                    'seasEd' => 0,
                    'episSt' => $mat[1],
                    'episEd' => $mat[2],
                    'itemVr' => 1,
                    'favTi' => preg_replace($re, "", $ti),
                    'matFnd' => "2_23-1-1"
                ];
            } else {
                return [
                    'medTyp' => 4,
                    'numSeq' => 1,
                    'seasSt' => $mat[1],
                    'seasEd' => $mat[2],
                    'episSt' => 1,
                    'episEd' => "",
                    'itemVr' => 1,
                    'favTi' => preg_replace($re, "", $ti),
                    'matFnd' => "2_23-1-2"
                ];
            }
        } else if ((int) $mat[1] <= $thisYear && (int) $mat[1] > 1895) {
            // YYYY-MM or YYYY-EE
            if ($detVid) {
                // definitely Video, probably YYYY-EE, highly unlikely to be YYYY-MM
                return [
                    'medTyp' => 1,
                    'numSeq' => 1,
                    'seasSt' => $mat[1],
                    'seasEd' => $mat[1],
                    'episSt' => $mat[2],
                    'episEd' => $mat[2],
                    'itemVr' => 1,
                    'favTi' => preg_replace($re, "", $ti),
                    'matFnd' => "2_23-2-1"
                ];
            } else {
                if (checkdate((int) $mat[2], 1, (int) $mat[1])) {
                    // Print Media YYYY-MM
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
                        'matFnd' => "2_23-2-2"
                    ];
                } else {
                    // Print Media YYYY-WW
                    return [
                        'medTyp' => 4,
                        'numSeq' => 1,
                        'seasSt' => $mat[1],
                        'seasEd' => $mat[1],
                        'episSt' => $mat[2],
                        'episEd' => $mat[2],
                        'itemVr' => 1,
                        'favTi' => preg_replace($re, "", $ti),
                        'matFnd' => "2_23-2-3"
                    ];
                }
            }
        } else {
            // #### is probably part of the title
            //TODO add $detVid since we have it already
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $mat[2],
                'episEd' => $mat[2],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "$1", $ti),
                'matFnd' => "2_23-3"
            ];
        }
    }
}

function matchTitle2_24($ti, $seps) {
    // isolated MM-YYYY
    $mat = [];
    $re = "/\b(\d{1,2})[$seps]?-[$seps]?(\d{4})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        if (checkdate((int) $mat[1], 1, (int) $mat[2]) && (int) $mat[2] <= getdate()['year'] && (int) $mat[2] > 1895) {
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
                'matFnd' => "2_24-1"
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
                'matFnd' => "2_24-2"
            ];
        }
    }
}

function matchTitle2_25($ti, $seps) {
    // (YYYY) - EE or title (####) - EE
    $mat = [];
    $re = "/\((\d{4})\)[\-$seps]{0,3}(\d{1,4})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        if ((int) $mat[1] <= getdate()['year'] && (int) $mat[1] > 1895) {
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
                'matFnd' => "2_25-1"
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
                'matFnd' => "2_25-2"
            ];
        }
    }
}

function matchTitle2_26($ti, $seps) {
    // EEE - (YYYY) or title #### (YYYY)
    $mat = [];
    $re = "/\b(\d{1,4})[-$seps]{0,3}\((\d{4})\).*/";
    if (preg_match($re, $ti, $mat)) {
        if ((int) $mat[2] <= getdate()['year'] && (int) $mat[2] > 1895) {
            //TODO use smaller threshold for videos, larger one for print media
            if ((int) $mat[1] > 998 || (int) $mat[1] == 666) {
                // EEE is probably part of the title, such as 666 or 1000 or 2045
                return [
                    'medTyp' => 1,
                    'numSeq' => 1,
                    'seasSt' => $mat[2], // full Season = YYYY
                    'seasEd' => $mat[2],
                    'episSt' => 1,
                    'episEd' => "",
                    'itemVr' => 1,
                    'favTi' => preg_replace($re, "", $ti),
                    'matFnd' => "2_26-1"
                ];
            } else {
                // EEE is probably an episode and not part of the title
                return [
                    'medTyp' => 1,
                    'numSeq' => 1,
                    'seasSt' => $mat[2], // Half date notation and half episode: let Season = YYYY
                    'seasEd' => $mat[2],
                    'episSt' => $mat[1],
                    'episEd' => $mat[1],
                    'itemVr' => 1,
                    'favTi' => preg_replace($re, "", $ti),
                    'matFnd' => "2_26-2"
                ];
            }
        }
    }
}

function matchTitle2_28($ti, $seps) {
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
            'matFnd' => "2_28"
        ];
    }
}

function matchTitle2_29($ti, $seps) {
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
            'matFnd' => "2_29"
        ];
    }
}

function matchTitle2_30($ti, $seps) {
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
            'matFnd' => "2_30"
        ];
    }
}

function matchTitle2_31($ti, $seps) {
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
            'matFnd' => "2_31"
        ];
    }
}

function matchTitle2_32($ti, $seps) {
    // isolated ##-##
    $mat = [];
    $re = "/\b[$seps]?(\d{1,3})[$seps]?\-[$seps]?(\d{1,4})[$seps]?\b.*/";
    if (preg_match($re, $ti, $mat)) {
        // MUST keep first ### less than 4 digits to prevent Magic Kaito 1412 - EE from matching, but we intercept it above in 2_34-5
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
                'matFnd' => "2_32-1"
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
                'matFnd' => "2_32-2"
            ];
        } else if (
                (int) $mat[1] > 0 && // no Season 0
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
                'matFnd' => "2_32-3"
            ];
        } else if (
                strlen($mat[1]) > 1 && // first ## is more than 1 digit
                (substr($mat[1], 0, 1) == '0' || // leading digit of first ## is 0
                (
                strlen($mat[2]) - strlen($mat[1]) < 2 && // second ## is no more than 1 digit longer
                (int) $mat[1] < (int) $mat[2] // second ## is greater than first ##
                )
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
                'matFnd' => "2_32-4"
            ];
        } else if ($mat[1] == "0" && strlen($mat[2]) > 1) {
            // isolated 0 - EE
            // assume 0 is part of title, Season 1 - EE
            // Examples:
            // Steins Gate 0
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $mat[2],
                'episEd' => $mat[2],
                'itemVr' => 1,
                'favTi' => preg_replace("/[$seps]?\-[$seps]?(\d{1,4})[$seps]?\b.*/", "", $ti),
                'matFnd' => "2_32-5"
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
                'matFnd' => "2_32-6"
            ];
        }
    }
}

function matchTitle2_33($ti, $seps) {
    // S (EEE)
    // episode is in parentheses
    $mat = [];
    $re = "/\b(\d{1,2})[$seps]?[\-\(\)#\x{3010}\x{3011}][$seps]?(\d{1,4})[$seps]?[\-\(\)\x{3010}\x{3011}].*/u";
    if (preg_match($re, $ti, $mat)) {
        if (
                (
                $mat[2] == '1080' ||
                $mat[2] == '720' ||
                $mat[2] == '480'
                ) &&
                (
                (int) $mat[1] > 1 ||
                substr($mat[1], 0, 1) == '0' // leading digit of first ## is 0
                )
        ) {
            // probably EE with Resolution
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $mat[1],
                'episEd' => $mat[1],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_33-1"
            ];
        } else if ((int) $mat[2] > 0) {
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
                'matFnd' => "2_33-2"
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
                'matFnd' => "2_33-3"
            ];
        }
    }
}

function matchTitle2_34($ti, $seps) {
    // month DD, YYYY
    $mat = [];
    $re = "/\b([A-Za-z]+)[$seps](\d{1,2})[$seps\,](\d{1,4})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        $MM = convertMonthToMM($mat[1]);
        if (is_numeric($MM) && validateYYYYMMDD($mat[3] . $MM . $mat[2])
        ) {
            return [
                'medTyp' => 1,
                'numSeq' => 2,
                'seasSt' => 0,
                'seasEd' => 0,
                'episSt' => $mat[3] . $MM . $mat[2],
                'episEd' => $mat[3] . $MM . $mat[2],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_34"
            ];
        }
    }
}

function matchTitle2_35($ti, $seps) {
    // DD month, YYYY
    $mat = [];
    $re = "/\b(\d{1,2})[$seps]([A-Za-z]+)[$seps\,](\d{1,4})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        $MM = convertMonthToMM($mat[2]);
        if (is_numeric($MM) && validateYYYYMMDD($mat[3] . $MM . $mat[1])
        ) {
            return [
                'medTyp' => 1,
                'numSeq' => 2,
                'seasSt' => 0,
                'seasEd' => 0,
                'episSt' => $mat[3] . $MM . $mat[1],
                'episEd' => $mat[3] . $MM . $mat[1],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_35"
            ];
        }
    }
}

function matchTitle2_36($ti, $seps) {
    // word ### ### (put near the end)
    $mat = [];
    $re = "/\b([A-Za-z]+\.?)[$seps]?(\d{1,4})[$seps]?(\d{1,4})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        switch (strtolower($mat[1])) {
            case 'week' :
                if ((int) $mat[3] <= getdate()['year'] && (int) $mat[3] > 1895) {
                    // week ### YYYY; assume print media
                    $result = [
                        'medTyp' => 4,
                        'numSeq' => 1,
                        'seasSt' => $mat[3],
                        'seasEd' => $mat[3],
                        'episSt' => $mat[2],
                        'episEd' => $mat[2],
                        'itemVr' => 1,
                        'matFnd' => "2_36"
                    ];
                }
        }
        $result['favTi'] = preg_replace($re, "", $ti);
        return $result;
    }
}

function matchTitle2_37($ti, $seps) {
    // isolated SS EE, SS EEE, or isolated EE EE
    $mat = [];
    $re = "/\b(\d{1,4})[$seps](\d{1,4})\b.*/";
    if (preg_match($re, $ti, $mat)) {
        if (
                (
                $mat[2] == '1080' ||
                $mat[2] == '720' ||
                $mat[2] == '480'
                ) &&
                (
                (int) $mat[1] > 1 ||
                substr($mat[1], 0, 1) == '0' // leading digit of first ## is 0
                )
        ) {
            // probably EE with Resolution
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $mat[1],
                'episEd' => $mat[1],
                'itemVr' => 1,
                'favTi' => preg_replace($re, "", $ti),
                'matFnd' => "2_37-1"
            ];
        } else if (
                (strlen($mat[1]) < strlen($mat[2])) || // SS EEE or S EE
                ((int) $mat[1] >= (int) $mat[2]) // EE EE usually has lower episodes first
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
                'matFnd' => "2_37-2"
            ];
        } else if (
                (int) $mat[1] < (int) $mat[2] &&
                (int) $mat[1] < 6 && // most cours never pass 5
                (int) $mat[1] + 1 != (int) $mat[2] && // seq. numbers likely EE EE
                (int) $mat[2] < (int) $mat[1] + 14 // seasons usually have less than 14 episodes
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
                'matFnd' => "2_37-3"
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
                'matFnd' => "2_37-4"
            ];
        }
    }
}

function matchTitle2_39($ti, $seps) {
    // EE word v##
    // assume that a v## after another number indicates version, not volume
    $mat = [];
    $re = "/\b(\d{1,4})([A-Za-z]+)[$seps][Vv]\.?[$seps]?(\d{1,4})\b.*/";
    if (
            preg_match($re, $ti, $mat)
    ) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[1],
            'episEd' => $mat[1],
            'itemVr' => $mat[3],
            'favTi' => preg_replace($re, "", $ti),
            'matFnd' => "2_39"
        ];
    }
}

function matchTitle2_40($ti, $seps) {
    // ## words - EE
    // assume the first number is part of the title
    $mat = [];
    if (
            preg_match("/(\d{1,4})(\D+)[$seps]\-[$seps](\d{1,4})\b/", $ti, $mat) &&
            preg_match("/(OVA|OAV)/", $mat[2]) != 1
    ) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[3],
            'episEd' => $mat[3],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]\-[$seps](\d{1,4})\b.*/", "", $ti),
            'matFnd' => "2_40"
        ];
    }
}

function matchTitle2_42($ti, $seps) {
    // #### - word ###
    // assume the first number is part of the title
    $mat = [];
    if (
            preg_match("/(\d{1,4})[$seps]?\-[$seps]?([a-zA-Z\.]+)[$seps]?(\d{1,3})\b/", $ti, $mat)
    ) {
        switch ($mat[2]) {
            case "OAV":
            case "OVA" :
                return [
                    'medTyp' => 1,
                    'numSeq' => 32,
                    'seasSt' => 1,
                    'seasEd' => 1,
                    'episSt' => $mat[3],
                    'episEd' => $mat[3],
                    'itemVr' => 1,
                    'favTi' => preg_replace("/[$seps]?\-[$seps]?[a-zA-Z\.]+[$seps]?\d{1,3}\b.*/", "", $ti),
                    'matFnd' => "2_42-1"
                ];
            case "Volume":
            case "volume" :
            case "Vol." :
            case "vol." :
                return [
                    'medTyp' => 1, // not sure if video or print media, assume video
                    'numSeq' => 1,
                    'seasSt' => $mat[3],
                    'seasEd' => $mat[3],
                    'episSt' => 1,
                    'episEd' => "",
                    'itemVr' => 1,
                    'favTi' => preg_replace("/[$seps]?\-[$seps]?[a-zA-Z\.]+[$seps]?\d{1,3}\b.*/", "", $ti),
                    'matFnd' => "2_42-2"
                ];
        }
    }
}
