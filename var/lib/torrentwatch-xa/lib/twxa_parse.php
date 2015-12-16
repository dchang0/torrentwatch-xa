<?php
/*
 * Helper functions for parsing torrent titles
 * currently part of Procedural Programming versions, will be replaced by OOP later
 * guess.php and feeds.php refer to this file
 */

$seps = '\s\.\_'; // separator chars: - and () were formerly also separators but caused problems; we need - for some Season and Episode notations

function sanitizeTitle($ti, $seps = '\s\.\_') {
    // cleans title of symbols, aiming to get the title down to just alphanumerics and reserved separators
    // we sanitize the title to make it easier to use Favorites and match episodes
    $sanitizeRegExPart = preg_quote('[]{}<>,_/','/');

    // Remove soft hyphens
    $ti = str_replace("\xC2\xAD", "", $ti);

    // Replace every tilde with a minus
    $ti = str_replace("~", "-", $ti);

    // replace with space any back-to-back sanitize chars that if taken singly would result in values getting smashed together
    $ti = preg_replace("/([a-z0-9\(\)])[$sanitizeRegExPart]+([a-z0-9\(\)])/i", "$1 $2", $ti);

    // remove all remaining sanitize chars
    $ti = preg_replace("/[$sanitizeRegExPart]/", '', $ti);

    // IMPORTANT: reduce multiple separators down to one separator (will break some matches if removed)
    $ti = preg_replace("/([$seps])+/", "$1", $ti);

    // trim beginning and ending spaces
    $ti = trim($ti);

    return $ti;
}

function normalizeCodecs($ti, $seps= '\s\.\_') {

    $ti = preg_replace("/x[$seps\-]+264/i", 'x264', $ti); // note the separator chars PLUS - char
    $ti = preg_replace("/h[$seps\-]+264/i", 'h264', $ti); // not sure why, but can't use | and $1 with x|h
    $ti = preg_replace("/x[$seps\-]+265/i", 'x265', $ti); // note the separator chars PLUS - char
    $ti = preg_replace("/h[$seps\-]+265/i", 'h265', $ti); // not sure why, but can't use | and $1 with x|h
    $ti = preg_replace("/10[$seps\-]?bit(s)?/i", '10bit', $ti); // normalize 10bit
    $ti = preg_replace("/8[$seps\-]?bit(s)?/i", '8bit', $ti);
    $ti = preg_replace("/FLAC[$seps\-]+2(\.0)?/i", 'FLAC2', $ti);
    $ti = preg_replace("/AAC[$seps\-]+2(\.0)?/i", 'AAC2', $ti);

    return $ti;
}

function simplifyTitle($ti, $seps = '\s\.\_') {
    // combines all the title processing functions

    $ti = sanitizeTitle($ti);

    // MUST normalize these codecs/qualities now so that users get trained to use normalized versions
    $ti = normalizeCodecs($ti);

    //TODO Maybe replace period-style separators with spaces (unless they are sanitized)
    //TODO Maybe pad parentheses with outside spaces (unless they are sanitized)
    //TODO Maybe remove audio codecs (not necessary if episode matching can handle being butted up against a codec)

    // detect and strip out 7 or 8-character checksums
    if(preg_match_all("/([0-9a-f])[0-9a-f]{6,7}/i", $ti, $mat, PREG_SET_ORDER)) { // do not initialize $mat--breaks checksum removal
        // only handle first one--not likely to have more than one checksum in any title
        $wholeMatch = $mat[0][0];
        $firstChar = $mat[0][1];
        if(preg_match("/\D/", $wholeMatch)) {
            // any non-digit means it's a checksum
            $ti = str_replace($wholeMatch, "", $ti);
        }
        else if($firstChar > 2) {
            // if first digit is not 0, 1, or 2, it's likely not a date
            $ti = str_replace($wholeMatch, "", $ti);
        }
        else {
            //TODO remove 8-digit checksums that look like they might be dates
        }
    }

    // run sanitize again due to possibility of checksum removal leaving back-to-back separators
    return sanitizeTitle($ti);
}

function detectResolution($ti, $seps = '\s\.\_') {
    $wByHRegEx = "/(\d{3,})[$seps]*[xX][$seps]*((\d{3,})[iIpP]?)/";
    $hRegEx = "/(\d{3,})[iIpP]/";
    $resolution = "";
    $matchedResolution = "";
    $verticalLines = "";
    $detQualities = [];
    $matches1 = [];
    $matches2 = [];

    // search arbitrarily for #### x #### (might also be Season x Episode or YYYY x MMDD)
    if(preg_match_all($wByHRegEx, $ti, $matches1, PREG_SET_ORDER)) {
        // check aspect ratios
        foreach($matches1 as $match) {
            if(
                $match[1] * 9 / 16 == $match[3] || // 16:9 aspect ratio
                $match[1] * 0.75 == $match[3] || // 4:3 aspect ratio
                $match[1] * 5 / 8 == $match[3] || // 16:10 aspect ratio
                $match[1] * 2 / 3 == $match[3] || // 3:2 aspect ratio
                $match[1] * 0.8 == $match[3] || // 5:4 aspect ratio
                $match[1] * 10 / 19 == $match[3] || // 19:10 4K aspect ratio
                $match[1] * 135 / 256 == $match[3] || // 256:135 4K aspect ratio
                $match[1] * 3 / 7 == $match[3] || // 21:9 4K aspect ratio
                $match[1] * 203 / 360 == $match[3] || // some people are forcing 720x406
                $match[1] * 25 / 44 == $match[3] || // 44:25 some people are forcing 704x400
                $match[1] * 30 / 53 == $match[3] || // 53:30 some people are forcing 848x480p
                $match[1] * 40 / 71 == $match[3] || // 71:41 some people are forcing 852x480p
                $match[1] * 34 / 45 == $match[3] || // 45:34 some people are forcing 720x544
                $match[3] == 576 ||
                $match[3] == 720 ||
                $match[3] == 1076 || // some people are forcing 1920x1076
                $match[3] == 1080 ||
                $match[3] == 1200
            ) {
                $matchedResolution = $match[0];
                $resolution = strtolower($match[2]);
                $verticalLines = $match[3];
                if($resolution == $verticalLines) {
                    $resolution .= 'p'; // default to p if no i or p is specified
                }
                break; // shouldn't be more than one resolution in title
            }
        }
    }
    else if(preg_match_all($hRegEx, $ti, $matches2, PREG_SET_ORDER)) {
        // search for standalone resolutions in ###p or ###i format
        // shouldn't be more than one resolution in title
        $matchedResolution = $matches2[0][0];
        $resolution = strtolower($matchedResolution);
        $verticalLines = $matches2[0][1];
    }

    $ti = preg_replace("/$matchedResolution/", "", $ti);

    if($verticalLines == 720 || $verticalLines == 1080) {
        $detQualities = ["HD","HDTV"];
    }
    else if($verticalLines == 576) {
        $detQualities = ["ED","EDTV"];
        $ti = preg_replace("/SD(TV)?/i", "", $ti); // remove SD also (ED will be removed by detectQualities())
    }
    else if($verticalLines == 480) {
        $detQualities = ["SD","SDTV"];
    }

    $detQualities[] = $resolution;

    return ['parsedTitle' => sanitizeTitle($ti),
        //'detectedResolution' => $resolution,
        'detectedQualities' => $detQualities
            ];
}

function detectQualities($ti, $seps = '\s\.\_') {
    $qualitiesFromResolution = detectResolution($ti, $seps);

    // search for more quality matches and prepend them to detectedQualities
    $ti = $qualitiesFromResolution['parsedTitle'];
    $detQualities = $qualitiesFromResolution['detectedQualities'];

    $qualityList = [
        'BDRip',
        'BRRip',
        'BluRay',
        'BD',
        'HR.HDTV',
        'HDTV',
        'HDTVRip',
        'DSRIP',
        'DVB',
        'DVBRip',
        'TVRip',
        'TVCap',
        'TVDub',
        'TV-Dub',
        'HR.PDTV',
        'PDTV',
        'SatRip',
        'WebRip',
        'DVDR',
        'DVDRip',
        'DVDScr',
        'DVD9',
        'DVD5',
        'XviDVD',
        // DVD regions
        'DVD R0',
        'DVD R1',
        'DVD R2',
        'DVD R3',
        'DVD R4',
        'DVD R5',
        'DVD R6',
        // END DVD regions
        'DVD',
        'DSR',
        'SVCD',
        'WEB-DL',
        'WEB.DL',
        'iTunes',
        // codecs--could be high or low quality, who knows?
        'XviD',
        'x264',
        'h264',
        'x265',
        'h265',
        'Hi10P',
        'Hi10',
        'Ma10p',
        '10bit',
        '8bit',
        'AVC',
        'AVI',
        'MP4',
        'MKV',
        'BT.709',
        'BT.601',
        // analog color formats
        'NTSC',
        'PAL',
        'SECAM',
        // text encodings
        'BIG5',
        'BIG5+GB',
        'BIG5_GB',
        'GB', // might match unintended abbreviations
        // framespeeds
        '60fps',
        '30fps',
        '24fps',
        // typically low quality
        'VHSRip',
        'TELESYNC'
        ];

    foreach ($qualityList as $qualityListItem) {
        $qualityListItemRegExPart = preg_quote($qualityListItem, '/');
        if(preg_match("/\b$qualityListItemRegExPart\b/i", $ti)) { // must use boundaries because SxE notation can collide with x264
            $detQualities[] = $qualityListItem;
            // cascade down through, removing immediately-surrouding dashes
            $ti = preg_replace("/\-+$qualityListItemRegExPart\-+/i", '', $ti);
            $ti = preg_replace("/\-+$qualityListItemRegExPart\b/i", '', $ti);
            $ti = preg_replace("/\b$qualityListItemRegExPart\-+/i", '', $ti);
            $ti = preg_replace("/\b$qualityListItemRegExPart\b/i", '', $ti);
        }
    }

    return [
        'parsedTitle' => $ti,
        //'detectedResolution' => $qualitiesFromResolution['detectedResolution'],
        'detectedQualities' => $detQualities,
    ];
}

function detectAudioCodecs($ti) {
    $detAudioCodecs = [];

    $audioCodecList = [
        'AC3',
        'AAC',
        'AACx2',
        'FLAC2',
        'FLAC',
        '320K',
        '320Kbps',
        'MP3',
        '5.1ch',
        '5.1',
    ];

    foreach ($audioCodecList as $audioCodecListItem) {
        $audioCodecListItemRegExPart = preg_quote($audioCodecListItem, '/');
        if(preg_match("/\b$audioCodecListItemRegExPart\b/i", $ti)) {
            $detAudioCodecs[] = $audioCodecListItem;
            // cascade down through, removing immediately-surrouding dashes
            $ti = preg_replace("/\-+$audioCodecListItemRegExPart\-+/i", '', $ti);
            $ti = preg_replace("/\-+$audioCodecListItemRegExPart\b/i", '', $ti);
            $ti = preg_replace("/\b$audioCodecListItemRegExPart\-+/i", '', $ti);
            $ti = preg_replace("/\b$audioCodecListItemRegExPart\b/i", '', $ti);
        }
    }

    return [
        'parsedTitle' => $ti,
        'detectedAudioCodecs' => $detAudioCodecs
    ];
}

function detectItem($ti, $wereQualitiesDetected = false, $seps = '\s\.\_') {
    // $wereQualitiesDetected is a param because some manga use "Vol. ##" notation

    // $medTyp state table
    // 0 = Unknown
    // 1 = Video
    // 2 = Audio
    // 4 = Print media

    // $numSeq allows for parallel numbering sequences
    // like Movie 1, Movie 2, Movie 3 alongside Episode 1, Episode 2, Episode 3

    // 0 = Unknown
    // 1 = Video: Season x Episode, Print Media: Volume x Chapter, Audio: Season x Episode
    // 2 = Video: Date, Print Media: Date, Audio: Date (all these get Season = 0)
    // 4 = Video: Full Season x Volume/Part, Print Media: Full Volume, Audio: Full Season
    // 8 = Video: Preview, Print Media: N/A, Audio: Opening songs
    // 16 = Video: Special, Print Media: N/A, Audio: Ending songs
    // 32 = Video: OVA episode sequence, Print Media: N/A, Audio: Character songs
    // 64 = Video: Movie sequence (Season = 0), Print Media: N/A, Audio: OST
    // 128 = Video: Volume x Disc sequence, Print Media: N/A, Audio: N/A

    // IMPORTANT NOTES:
    // treat anime notation as Season 1
    // treat date-based episodes as Season 0 EXCEPT...
    // ...when YYYY-##, use year as the Season and ## as the Episode
    // because of PHP left-to-right matching order, (Season|Seas|Se|S) works but (S|Se|Seas|Season) will match S and move on

    // GOALS:
    // handle Special and OVA episodes
    // handle PROPER and REPACK episodes as version 2 if not specified
    // use short circuits to reduce overhead

    //TODO go back and restrict \d+ to \d{1,4} where appropriate

    // MATCHES STILL IN PROGRESS, NOT DONE OR NOT TESTED ENOUGH:
    // S##E##.#
    // S##.#E##
    // ###.#v3 (anime episode number, version 3)
    // 01 of 20 1978
    // 4x04 (2014)
    // The.Haunting.Of.S04.Revealed.Special (Season 4, Special)
    // "DBZ Abridged Episodes 1-44 + Movies (TFS)" (big batch)
    // CFL.2014.RS.Week18.(25 oct).BC.Lions.v.WPG.Blue.Bombers.504p
    // Batch XX-XX
    // 27th October 2014
    // 14Apr3
    // Serie.A.2014.Day08(26 oct).Cesena.v.Inter.400p
    
    // decode HTML and URL encoded characters to reduce number of extraneous numerals
    $ti = html_entity_decode($ti, ENT_QUOTES);

    // split off v2 from ##v2
    $ti = preg_replace('/\b(\d{1,3})([Vv]\d{1,2})\b/', "$1 $2", $ti);

    // bucket the matches of all numbers of different lengths
    preg_match_all("/(\d+)/u", $ti, $matNums, PREG_SET_ORDER); // can't initialize $matNums here due to isset tests later

    // is there at least one number? can't have an episode otherwise (except in case of PV preview episode)
    if(isset($matNums[0])) {
        //TODO add detection of isolated PV (assign episode = 0)
        if(!isset($matNums[1])) {
            // only one integer found, probably anime-style episode number, but look for preceding words
            $matNum = $matNums[0][1];
            $matNumLen = strlen($matNum);

            switch ($matNumLen) {
                case 1 :
                case 2 :
                case 3 :
                    // three digits or less
                    // NOTE: Yes, the switch-case-if-break control structure is stupid, but it is a result
                    // of how PHP handles the assignment of a return value from a function called inside
                    // a conditional. In the statement if ($output = function()), $output = 1 if the
                    // function call succeeds and NULL if it doesn't, not the value returned by function().
                    // The cheesy switch-case-if-break still beats a deep if-else if-else control structure.
                    switch (true) {
                        case (true) :
                            $result = matchTitle1_1_1($ti, $seps);
                            if(is_string($result['matFnd'])) {
                                break;
                            }
                        case (true) :
                            $result = matchTitle1_1_2($ti, $seps);
                            if(is_string($result['matFnd'])) {
                                break;
                            }
                        case (true) :
                            $result = matchTitle1_1_3($ti, $seps, $wereQualitiesDetected);
                            if(is_string($result['matFnd'])) {
                                break;
                            }
                        case (true) :
                            $result = matchTitle1_1_4($ti, $seps, $wereQualitiesDetected);
                            if(is_string($result['matFnd'])) {
                                break;
                            }
                        case (true) :
                            $result = matchTitle1_1_5($ti, $seps);
                            if(is_string($result['matFnd'])) {
                                break;
                            }
                        case (true) :
                            $result = matchTitle1_1_6($ti, $seps);
                            if(is_string($result['matFnd'])) {
                                break;
                            }
                        case (true) :
                            $result = matchTitle1_1_7($ti, $seps);
                            if(is_string($result['matFnd'])) {
                                break;
                            }
                        case (true) :
                            $result = matchTitle1_1_8($ti, $seps);
                            if(is_string($result['matFnd'])) {
                                break;
                            }
                        case (true) :
                            $result = matchTitle1_1_9($ti, $seps);
                            if(is_string($result['matFnd'])) {
                                break;
                            }
                        case (true) :
                            $result = matchTitle1_1_10($ti, $seps);
                            if(is_string($result['matFnd'])) {
                                break;
                            }
                        case (true) :
                            $result = matchTitle1_1_11($ti, $seps);
                            if(is_string($result['matFnd'])) {
                                break;
                            }
                        case (true) :
                            $result = matchTitle1_1_12($ti, $seps);
                            if(is_string($result['matFnd'])) {
                                break;
                            }
                        case (true) :
                            $result = matchTitle1_1_13($ti, $seps);
                            if(is_string($result['matFnd'])) {
                                break;
                            }
                        case (true) :
                            $result = matchTitle1_1_14($ti, $seps);
                            if(is_string($result['matFnd'])) {
                                break;
                            }
                        case (true) :
                            $result = matchTitle1_1_15($ti, $seps);
                            if(is_string($result['matFnd'])) {
                                break;
                            }
                        //TODO handle "OVA (1983)"
                        //TODO handle "Nichijou no 0 Wa | Nichijou OVA"
                        case (true) :
                            $result = matchTitle1_1_18($ti, $seps);
                            if(is_string($result['matFnd'])) {
                                break;
                            }
                        case (true) :
                            $result = matchTitle1_1_19($ti, $seps);
                            if(is_string($result['matFnd'])) {
                                break;
                            }
                        case (true) :
                            $result = matchTitle1_1_20($ti, $seps);
                            if(is_string($result['matFnd'])) {
                                break;
                            }
                        case (true) :
                            $result = matchTitle1_1_21($ti, $seps);
                            if(is_string($result['matFnd'])) {
                                break;
                            }
                        case (true) :
                            $result = matchTitle1_1_22($ti, $seps);
                            if(is_string($result['matFnd'])) {
                                break;
                            }
                        case (true) :
                            $result = matchTitle1_1_23($ti, $seps);
                            if(is_string($result['matFnd'])) {
                                break;
                            }
                        default :
                            // assume it's an anime-style episode number
                            //TODO make sure it's not butted up against text
                            switch (true) {
                                case (true) :
                                    $result = matchTitle1_1_30_1($ti, $seps);
                                    if(is_string($result['matFnd'])) {
                                        break;
                                    }
                                case (true) :
                                    $result = matchTitle1_1_30_2($ti, $seps);
                                    if(is_string($result['matFnd'])) {
                                        break;
                                    }
                                case (true) :
                                    $result = matchTitle1_1_30_3($ti, $seps);
                                    if(is_string($result['matFnd'])) {
                                        break;
                                    }
                                case (true) :
                                    $result = matchTitle1_1_30_4($ti, $seps);
                                    if(is_string($result['matFnd'])) {
                                        break;
                                    }
                                default :
                                    $result['matFnd'] = "1_1_30";
                            }
                            if(is_null($result)) {
                                $result['matFnd'] = "1_1";
                            }
                    }
                    break;
                case 4 :
                    // check if YYYY or MMDD or DDMM or MMYY or YYMM, otherwise assume ####
                    // 1896 was year of first moving picture

                    $thisYear = getdate()['year'];

                    if($matNum > 1895 && $matNum <= $thisYear) {
                        // probably YYYY
                        $result['numSeq'] = 2;
                        $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                        $result['episEd'] = $result['episSt'] = $matNum;
                        $result['matFnd'] = "1_2-1";
                    }
                    else {
                        $pair1 = substr($matNum, 0, 2);
                        $pair2 = substr($matNum, 2);
                        if(checkdate($pair2, $pair1, $thisYear)) {
                            // probably DDMM (assume YYYY is current year)
                            $result['numSeq'] = 2;
                            $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                            $result['episEd'] = $result['episSt'] = $pair2 . $pair1;
                            $result['matFnd'] = "1_2-2-1";
                        }
                        else if(checkdate($pair1, $pair2, $thisYear)) {
                            // probably MMDD (assume YYYY is current year)
                            $result['numSeq'] = 2;
                            $result['seasEd']= $result['seasSt'] = 0; // date notation gets Season 0
                            $result['episEd'] = $result['episSt'] = $matNum;
                            $result['matFnd'] = "1_2-2-2";
                        }
                        // we don't handle MMYY or YYMM because it is too tedious to figure out YYYY from YY
                        else {
                            $result['seasEd'] = $result['seasSt'] = 1; // episode notation gets Season 1
                            $result['episEd'] = $result['episSt'] = $matNum;
                            $result['matFnd'] = "1_2-2-3";
                        }
                    }
                    break;
                case 8 :
                    // YYYYMMDD
                    // YYYYDDMM
                    // MMDDYYYY
                    // DDMMYYYY
                    // ######## (not likely)
                    // 8-digit numeric checksum (should have been filtered out by now)

                    // split into four pairs of numerals
                    $four1 = substr($matNum, 0, 4);
                    $four2 = substr($matNum, 4, 4);
                    $pair1 = substr($four1, 0, 2);
                    $pair2 = substr($four1, 2, 2);
                    $pair3 = substr($four2, 0, 2);
                    $pair4 = substr($four2, 2, 2);
                    $thisYear = getdate()['year'];

                    if(checkdate($pair3 + 0, $pair4 + 0, $four1 + 0) && $four1 + 0 <= $thisYear && $four1 + 0 > 1895) {
                        // YYYYMMDD
                        $result['numSeq'] = 2;
                        $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                        $result['episEd'] = $result['episSt'] = $matNum;
                        $result['matFnd'] = "1_3-1";
                    }
                    else if(checkdate($pair4 + 0, $pair3 + 0, $four1 + 0) && $four1 + 0 <= $thisYear && $four1 + 0 > 1895) {
                        // YYYYDDMM
                        $result['numSeq'] = 2;
                        $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                        $result['episEd'] = $result['episSt'] = $four1 . $pair4 . $pair3;
                        $result['matFnd'] = "1_3-2";
                    }
                    else if(checkdate($pair1 + 0, $pair2 + 0, $four2 + 0) && $four2 + 0 <= $thisYear && $four2 + 0 > 1895) {
                        // MMDDYYYY
                        $result['numSeq'] = 2;
                        $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                        $result['episEd'] = $result['episSt'] = $four2 . $four1;
                        $result['matFnd'] = "1_3-3";
                    }
                    else if(checkdate($pair2 + 0, $pair1 + 0, $four2 + 0) && $four2 + 0 <= $thisYear && $four2 + 0 > 1895) {
                        // DDMMYYYY
                        $result['numSeq'] = 2;
                        $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                        $result['episEd'] = $result['episSt'] = $four2 . $pair2 . $pair1;
                        $result['matFnd'] = "1_3-4";
                    }
                    else {
                        // Unknown ########
                        if($wereQualitiesDetected) {
                            $result['medTyp'] = 1;
                        }
                        else {
                            $result['medTyp'] = 0;
                        }
                        $result['numSeq'] = 0;
                        $result['matFnd'] = "1_3-5";
                    }
                    break;
                case 6 :
                    // YYMMDD
                    // YYDDMM
                    // MMDDYY
                    // DDMMYY
                    // YYYYMM
                    // MMYYYY
                    // ######

                    // split into three pairs of numerals
                    $pair1 = substr($matNum, 0, 2);
                    $pair2 = substr($matNum, 2, 2);
                    $pair3 = substr($matNum, 4, 2);
                    $thisYear = getdate()['year'];
                    $thisYearPair1 = substr($thisYear, 0, 2);

                    if(checkdate($pair3 + 0, 1, $pair1 . $pair2 + 0) && $pair1 . $pair2 + 0 <= $thisYear && $pair1 . $pair2 + 0 > 1895) {
                        // YYYYMM
                        $result['numSeq'] = 2;
                        $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                        $result['episEd'] = $result['episSt'] = $matNum;
                        $result['matFnd'] = "1_4-1";
                    }
                    else if(checkdate($pair1 + 0, 1, $pair2 . $pair3 + 0) && $pair2 . $pair3 + 0 <= $thisYear && $pair2 . $pair3 + 0 > 1895) {
                        // MMYYYY
                        $result['numSeq'] = 2;
                        $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                        $result['episEd'] = $result['episSt'] = $pair2 . $pair3 . $pair1;
                        $result['matFnd'] = "1_4-2";
                    }
                    else if(checkdate($pair2 + 0, $pair3 + 0, $thisYearPair1 . $pair1 + 0) && $thisYearPair1 . $pair1 + 0 <= $thisYear && $thisYearPair1 . $pair1 + 0 > 1895) {
                        // YYMMDD
                        $result['numSeq'] = 2;
                        $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                        $result['episEd'] = $result['episSt'] = $thisYearPair1 . $pair1 . $pair2 . $pair3;
                        $result['matFnd'] = "1_4-3";
                    }
                    else if(checkdate($pair1 + 0, $pair2 + 0, $thisYearPair1 . $pair3 + 0) && $thisYearPair1 . $pair3 + 0 <= $thisYear && $thisYearPair1 . $pair3 + 0 > 1895) {
                        // MMDDYY
                        $result['numSeq'] = 2;
                        $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                        $result['episEd'] = $result['episSt'] = $thisYearPair1 . $pair3 . $pair1 . $pair2;
                        $result['matFnd'] = "1_4-4";
                    }
                    else if(checkdate($pair2 + 0, $pair1 + 0, $thisYearPair1 . $pair3 + 0) && $thisYearPair1 . $pair3 + 0 <= $thisYear && $thisYearPair1 . $pair3 + 0 > 1895) {
                        // DDMMYY
                        $result['numSeq'] = 2;
                        $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                        $result['episEd'] = $result['$episSt'] = $thisYearPair1 . $pair3 . $pair2 . $pair1;
                        $result['matFnd'] = "1_4-5";
                    }
                    else if(checkdate($pair3 + 0, $pair2 + 0, $thisYearPair1 . $pair1 + 0) && $thisYearPair1 . $pair1 + 0 <= $thisYear && $thisYearPair1 . $pair1 + 0 > 1895) {
                        // YYDDMM
                        $result['numSeq'] = 2;
                        $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                        $result['episEd'] = $result['episSt'] = $thisYearPair1 . $pair1 . $pair3 . $pair2;
                        $result['matFnd'] = "1_4-6";
                    }
                    else {
                        // Unknown ######
                        $result['numSeq'] = 0;
                        if($wereQualitiesDetected) {
                            $result['medTyp'] = 1;
                        }
                        else {
                            $result['medTyp'] = 0;
                        }
                        $result['matFnd'] = "1_4-7";
                    }
                    break;
                case 12 :
                    // YYYYMMDDHHMM
                    $result['numSeq'] = 2;
                    $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                    $result['episEd'] = $result['episSt'] = substr($matNum, 0, 8); // truncate the lengthy Date notation
                    $result['matFnd'] = "1_5";
                    break;
                case 14 :
                    // YYYYMMDDHHMMSS
                    $result['numSeq'] = 2;
                    $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                    $result['episEd'] = $result['episSt'] = substr($matNum, 0, 8); // truncate the lengthy Date notation
                    $result['matFnd'] = "1_6";
                    break;
                default:
                    // unidentifiable #
                    $result['numSeq'] = 0;
                    if($wereQualitiesDetected) {
                        $result['medTyp'] = 1;
                    }
                    else {
                        $result['medTyp'] = 0;
                    }
                    $result['matFnd'] = "1_";
            }
        }
        else if(!isset($matNums[2])) {
            // only two numbers found
            switch (true) {
                case (true) :
                    $result = matchTitle2_1($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_2($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_3($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_4($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_5($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_6($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_7($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                //TODO handle Volume ## Chapter ##
                //TODO handle Chapter ## Volume ##
                //TODO handle c## (v##)
                case (true) :
                    $result = matchTitle2_11($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                //TODO handle V##.## (Software Version ##.##)
                case (true) :
                    $result = matchTitle2_13($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_14($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                //TODO handle Volume ## & ##
                //TODO handle Chapter ## & ##
                //TODO handle Season ## & ##
                //TODO handle Episode ## & ##
                //TODO handle Part ## & ##
                case (true) :
                    $result = matchTitle2_20($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_21($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_22($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_23($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_24($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_25($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_26($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_27($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_28($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                //TODO search for unlabeled Season ## before the word Episode
                case (true) :
                    $result = matchTitle2_30($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_31($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_32($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_33($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_34($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_35($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_36($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_37($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_38($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_39($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_40($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_41($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_42($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_43($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_44($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_45($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_46($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle2_47($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                default :
                    $result['matFnd'] = "2_";
            }
        }
        else if(!isset($matNums[3])) {
            // three numbers found
            //TODO handle the decimal episodes here like S##E##.# and so on
            //TODO remove numbers embedded in the middle of words (common with crew names)            switch (true) {
            switch (true) {
                case (true) :
                    $result = matchTitle3_1($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_2($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_3($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_4($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_5($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_6($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_7($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_8($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_9($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_10($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_11($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_12($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_13($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_14($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_15($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_16($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_17($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_18($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                //TODO handle YYYY EE - EE
                case (true) :
                    $result = matchTitle3_20($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_21($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_22($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_23($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_24($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_25($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_26($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_27($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_28($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                // SAVE THE BELOW FOR THE END, BECAUSE SINGLE NUMBERS CAN MATCH SO MANY LONGER PATTERNS
                case (true) :
                    $result = matchTitle3_29($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_30($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                case (true) :
                    $result = matchTitle3_31($ti, $seps);
                    if(is_string($result['matFnd'])) {
                        break;
                    }
                default :
                    $result['matFnd'] = "3_";
            }
        }
        else if(!isset($matNums[4])) {
            // four numbers found
            $result['matFnd'] = "4_";
        }
        else {
            // five or more numbers found, ignore
            $result['matFnd'] = "5_";
        } // end if(!isset($matNums[1]))

        // trim off leading zeroes
        if(isset($result['episEd']) && $result['episEd'] != '') {
            $result['episEd'] += 0;
        }
        if(isset($result['episSt']) && $result['episSt'] != '') {
            $result['episSt'] += 0;
        }
        if(isset($result['seasEd']) && $result['seasEd'] != '') {
            $result['seasEd'] += 0;
        }
        if(isset($result['seasSt']) && $result['seasSt'] != '') {
            $result['seasSt'] +=0;
        }
    }
    else {
        // handle no-numeral episodes
        switch (true) {
            case (true) :
                $result = matchTitle0_1($ti, $seps);
                if(is_string($result['matFnd'])) {
                    break;
                }
            case (true) :
                $result = matchTitle0_1($ti, $seps);
                if(is_string($result['matFnd'])) {
                    break;
                }
            default :
                $result['matFnd'] = "0_";
        }
    } //END if(isset($matNums[0]))
    
    if(!isset($result['seasSt'])) {
        $result['seasSt'] = '';
    }
    if(!isset($result['seasEd'])) {
        $result['seasEd'] = '';
    }
    if(!isset($result['episSt'])) {
        $result['episSt'] = '';
    }
    if(!isset($result['episEd'])) {
        $result['episEd'] = '';
    }
    if(!isset($result['medTyp'])) {
        $result['medTyp'] = '';
    }
    if(!isset($result['itemVr'])) {
        $result['itemVr'] = '';
    }
    if(!isset($result['numSeq'])) {
        $result['numSeq'] = '';
    }
    if(!isset($result['favTi'])) {
        $result['favTi'] = '';
    }
    if(!isset($result['matFnd'])) {
        $result['matFnd'] = '';
    }

    return [ 'detectedSeasonBatchStart' => $result['seasSt'],
        'detectedSeasonBatchEnd' => $result['seasEd'],
        'detectedEpisodeBatchStart' => $result['episSt'],
        'detectedEpisodeBatchEnd' => $result['episEd'],
        'mediaType' => $result['medTyp'],
        'itemVersion' => $result['itemVr'],
        'numberSequence' => $result['numSeq'],
        'favoriteTitle' => $result['favTi'],
        'debugMatch' => $result['matFnd']
            ];
}

function matchTitle1_1_1($ti, $seps) {
    // search for the word Season, Temporada; should also catch Season ## Complete
    $mat=[];
    if(preg_match_all("/(Season|Saison|Seizoen|\bSeas|\bSais|\bSea|\bSe|\bS|Temporada|\bTemp|\bT)[$seps]?(\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1, // assume Video Media
            'numSeq' => 4,
            'seasSt' => $mat[0][2],
            'seasEd' => $mat[0][2],
            'episSt' => 1,
            'episEd' => '',
            'itemVr' => 1,
            'favTi' => "Cannot match entire seasons.", //TODO figure out favTi for Seasons
            'matFnd' => "1_1_1"
        ];
    }
}

function matchTitle1_1_2($ti, $seps) {
    // search for ##rd/##th Season
    $mat=[];
    if(preg_match_all("/(\d{1,2})(rd|nd|th)[$seps]?(Season|Seas\b|Sea\b|Se\b|S\b)/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1, // assume Video Media
            'numSeq' => 4,
            'seasSt' => $mat[0][1],
            'seasEd' => $mat[0][1],
            'episSt' => 1,
            'episEd' => '',
            'itemVr' => 1,
            'favTi' => "Cannot match entire seasons.", //TODO figure out favTi for Seasons
            'matFnd' => "1_1_2"
        ];
    }
}

function matchTitle1_1_3($ti, $seps, $detVid) {
    // search for the word Volume, Volumen
    $mat=[];
    if(preg_match_all("/(Volumen|Volume|\bVol)[$seps]?(\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        if($detVid === TRUE) {
            return [
                'medTyp' => 1, // assume Video Media
                'numSeq' => 4, // video Season x Volume/Part numbering
                'seasSt' => 1, // assume Season 1
                'seasEd' => 1,
                'episSt' => $mat[0][2],
                'episEd' => $mat[0][2],
                'itemVr' => 1,
                'favTi' => "Cannot match entire volumes.", //TODO figure out favTi for Volumes
                'matFnd' => "1_1_3-1"
            ];
        }
        else {
            return [
                'medTyp' => 4, // assume Print Media
                'numSeq' => 4, // video Season x Volume/Part numbering
                'seasSt' => $mat[0][2],
                'seasEd' => $mat[0][2],
                'episSt' => 0,
                'episEd' => 0,
                'itemVr' => 1,
                'favTi' => "Cannot match entire volumes.", //TODO figure out favTi for Volumes
                'matFnd' => "1_1_3-4"
            ];
        }
    }
}

function matchTitle1_1_4($ti, $seps, $detVid) {
    // search for V. ##--Volume, not version, and not titles like ARC-V
    $mat=[];
    if(preg_match_all("/[$seps]V[$seps]{1,2}(\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        if($detVid === TRUE) {
            return [
                'medTyp' => 1, // assume Video Media
                'numSeq' => 4, // video Season x Volume/Part numbering
                'seasSt' => 1, // assume Season 1
                'seasEd' => 1,
                'episSt' => $mat[0][2],
                'episEd' => $mat[0][2],
                'itemVr' => 1,
                'favTi' => "Cannot match entire volumes.", //TODO figure out favTi for Volumes
                'matFnd' => "1_1_4-1"
            ];
        }
        else {
            return [
                'medTyp' => 4, // assume Print Media
                'numSeq' => 4, // video Season x Volume/Part numbering
                'seasSt' => $mat[0][2],
                'seasEd' => $mat[0][2],
                'episSt' => 0,
                'episEd' => 0,
                'itemVr' => 1,
                'favTi' => "Cannot match entire volumes.", //TODO figure out favTi for Volumes
                'matFnd' => "1_1_4-4"
            ];
        }
    }
}

function matchTitle1_1_5($ti, $seps) {
    // search for the word Chapter, Capitulo
    $mat=[];
    if(preg_match_all("/(Chapter|Capitulo|Chapitre|\bChap|\bCh|\bC)[$seps]?(\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 4, // assume Print Media
            'numSeq' => 1, // assume ## x ## number sequence
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[0][2],
            'episEd' => $mat[0][2],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]?(\d+)[$seps]*/", '', $ti),
            'matFnd' => "1_1_5"
        ];
    }
}

function matchTitle1_1_6($ti, $seps) {
    // search for Movie ##
    $mat=[];
    if(preg_match_all("/(Movie|\bMov)[$seps]?(\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 64,
            'seasSt' => 0,
            'seasEd' => 0,
            'episSt' => $mat[0][2],
            'episEd' => $mat[0][2],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]?(\d+)[$seps]*/", '', $ti),
            'matFnd' => "1_1_6"
        ];
    }
}

function matchTitle1_1_7($ti, $seps) {
    // search for Movie v##
    $mat=[];
    if(preg_match_all("/(Movie|\bMov)[$seps]?v(\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 64,
            'seasSt' => 0,
            'seasEd' => 0,
            'episSt' => 1,
            'episEd' => 1,
            'itemVr' => $mat[0][2],
            'favTi' => "Cannot match movies without sequential numbering.",
            'matFnd' => "1_1_7"
        ];
    }
}

function matchTitle1_1_8($ti, $seps) {
    // search for Film ##
    $mat=[];
    if(preg_match_all("/(Film|\bF)[$seps]?(\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 64,
            'seasSt' => 0,
            'seasEd' => 0,
            'episSt' => $mat[0][2],
            'episEd' => $mat[0][2],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]?(\d+)[$seps]*/", '', $ti),
            'matFnd' => "1_1_8"
        ];
    }
}

function matchTitle1_1_9($ti, $seps) {
    // search for Film v##
    $mat=[];
    if(preg_match_all("/(Film|\bF)[$seps]?v(\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 64,
            'seasSt' => 0,
            'seasEd' => 0,
            'episSt' => 1,
            'episEd' => 1,
            'itemVr' => $mat[0][2],
            'favTi' => "Cannot match movies without sequential numbering.",
            'matFnd' => "1_1_9"
        ];
    }
}

function matchTitle1_1_10($ti, $seps) {
    // search for Part ##
    $mat=[];
    if(preg_match_all("/(Part|\bPt)[$seps]?(\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        //TODO handle Part
        return [
            'medTyp' => NULL,
            'numSeq' => NULL,
            'seasSt' => NULL,
            'seasEd' => NULL,
            'episSt' => NULL,
            'episEd' => NULL,
            'itemVr' => 1,
            'favTi' => "Cannot match Part.",
            'matFnd' => "1_1_10"
        ];
    }
}

function matchTitle1_1_11($ti, $seps) {
    // search for Episode ##
    // should not be any mention of Season ## before Episode ## because only one ## found
    $mat=[];
    if(preg_match_all("/(Episode|\bEpis|\bEp|\bE)[$seps]?(\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[0][2],
            'episEd' => $mat[0][2],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]?(\d+)[$seps]*/", '', $ti),
            'matFnd' => "1_1_11"
        ];
    }
}

function matchTitle1_1_12($ti, $seps) {
    // search for Special v##
    $mat=[];
    if(preg_match_all("/[$seps](Special|Spec)[$seps]?v(\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 16,
            'seasSt' => 1, // assume Season 1
            'seasEd' => 1,
            'episSt' => 1, // assume Special Episode 1
            'episEd' => 1,
            'itemVr' => $mat[0][2], // only number is the version number
            'favTi' => preg_replace("/[$seps]?v(\d+)[$seps]*/i", '', $ti),
            'matFnd' => "1_1_12"
        ];
    }
}

function matchTitle1_1_13($ti, $seps) {
    // search for "02 - Special"
    $mat=[];
    if(preg_match_all("/\b(\d+)[$seps]?-?[$seps]?(Special|Spec\b|Sp\b)/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 16,
            'seasSt' => 1, // assume Season 1
            'seasEd' => 1,
            'episSt' => $mat[0][1],
            'episEd' => $mat[0][1],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]?(\d+)[$seps]?-?[$seps]?/i", '', $ti),
            'matFnd' => "1_1_13"
        ];
    }
}

function matchTitle1_1_14($ti, $seps) {
    // search for "Special - 02"
    // Special - 02
    // Spec02
    // SP# (Special #)
    $mat=[];
    if(preg_match_all("/\b(Special|Spec|Sp)[$seps]?-?[$seps]?(\d+)\b/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 16,
            'seasSt' => 1, // assume Season 1
            'seasEd' => 1,
            'episSt' => $mat[0][2],
            'episEd' => $mat[0][2],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]?-?[$seps]?(\d+)\b/i", '', $ti),
            'matFnd' => "1_1_14"
        ];
    }
}

function matchTitle1_1_15($ti, $seps) {
    // search for OVA v##
    $mat=[];
    if(preg_match_all("/[$seps](OVA|OAV)[$seps]?v(\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 32,
            'seasSt' => 1, // assume Season 1
            'seasEd' => 1,
            'episSt' => 1, // assume OVA Episode 1
            'episEd' => 1,
            'itemVr' => $mat[0][2], // only number is the version number
            'favTi' => preg_replace("/[$seps]?v(\d+)[$seps]?/i", '', $ti),
            'matFnd' => "1_1_15"
        ];
    }
}

function matchTitle1_1_18($ti, $seps) {
    // Roman numeral SS-EE, only handle Seasons I, II, and III
    // NOTE: The ability to detect Roman numeral seasons means that to match the title, one must NOT
    // put the Roman numeral season in the Favorite filter. For example: "Sword Art Online II" would not
    // work as a Favorite Filter because the "II" would get stripped out of the title by detectItem().
    // Use "Sword Art Online" instead. But this is counterintuitive--people would think of the "II" as being
    // part of the title.
    $mat=[];
    if(preg_match_all("/\b(I{1,3})[$seps]?\-?[$seps]?(\d+)/", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => strlen($mat[0][1]),
            'seasEd' => strlen($mat[0][1]),
            'episSt' => $mat[0][2],
            'episEd' => $mat[0][2],
            'itemVr' => 1,
            'favTi' => preg_replace("/\b(I{1,3})[$seps]?\-?[$seps]?(\d+)/", '', $ti),
            'matFnd' => "1_1_18"
        ];
    }
}

function matchTitle1_1_19($ti, $seps) {
    // pound sign and number
    // could be Vol., Number, but assume it's an Episode
    $mat=[];
    if(preg_match_all("/#[$seps]?(\d+)/", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[0][1],
            'episEd' => $mat[0][1],
            'itemVr' => 1,
            'favTi' => preg_replace("/#[$seps]?(\d+)/", '', $ti),
            'matFnd' => "1_1_19"
        ];
    }
}

function matchTitle1_1_20($ti, $seps) {
    // apostrophe ## (abbreviated year)
    $mat=[];
    if(preg_match_all("/\[$seps]?'(\d\d)\b/", $ti, $mat, \PREG_SET_ORDER)) {
        $thisYear = getdate()['year'];
        $guessedYearCurrentCentury = substr($thisYear, 0, 2) . $mat[0][1];
        $guessedYearPriorCentury = substr($thisYear - 1, 0, 2) . $mat[0][1];
        if($guessedYearCurrentCentury + 0 <= $thisYear && $guessedYearCurrentCentury + 0 > 1895) {
            return [
                'medTyp' => 1,
                'numSeq' => 2,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $guessedYearCurrentCentury,
                'episEd' => $guessedYearCurrentCentury,
                'itemVr' => 1,
                'favTi' => preg_replace("/\[$seps]?'(\d\d)\b/", '', $ti),
                'matFnd' => "1_1_20-1"
            ];
        }
        else if($guessedYearPriorCentury + 0 <= $thisYear && $guessedYearPriorCentury + 0 > 1895) {
            return [
                'medTyp' => 1,
                'numSeq' => 2,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $guessedYearPriorCentury,
                'episEd' => $guessedYearPriorCentury,
                'itemVr' => 1,
                'favTi' => preg_replace("/\[$seps]?'(\d\d)\b/", '', $ti),
                'matFnd' => "1_1_20-2"
            ];
        }
        else {
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $mat[0][1],
                'episEd' => $mat[0][1],
                'itemVr' => 1,
                'favTi' => preg_replace("/\[$seps]?'(\d\d)\b/", '', $ti),
                'matFnd' => "1_1_20-3"
            ];
        }
    }
}

function matchTitle1_1_21($ti, $seps) {
    // search for uppercase PV 0
    $mat=[];
    if(preg_match_all("/\bPV[$seps]?0{1,2}\b/", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 8,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => 0,
            'episEd' => 0,
            'itemVr' => 1,
            'favTi' => preg_replace("/\bPV[$seps]?0{1,2}\b/", '', $ti),
            'matFnd' => "1_1_21"
        ];
    }
}

function matchTitle1_1_22($ti, $seps) {
    // search for uppercase PV ##
    $mat=[];
    if(preg_match_all("/\bPV[$seps]?(\d{1,2})\b/", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 8,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[0][1],
            'episEd' => $mat[0][1],
            'itemVr' => 1,
            'favTi' => preg_replace("/\bPV[$seps]?(\d{1,2})\b/", '', $ti),
            'matFnd' => "1_1_22"
        ];
    }
}

function matchTitle1_1_23($ti, $seps) {
    // search for standalone Version ##
    $mat=[];
    if(preg_match_all("/[$seps]v(\d{1,2})/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 64, // assume Movie numbering
            'seasSt' => 0, // for Movies, assume Season = 0
            'seasEd' => 0,
            'episSt' => 1, // assume Movie 1
            'episEd' => 1,
            'itemVr' => $mat[0][1],
            'favTi' => preg_replace("/[$seps]v(\d{1,2})[$seps]*/i", '', $ti),
            'matFnd' => "1_1_23"
        ];
    }
}

function matchTitle1_1_30_1($ti, $seps) {
    // isolated EEE
    $mat=[];
    if(preg_match_all("/[$seps\-\(\)\[\]#\x{3010}\x{3011}\x{7B2C}](\d+)([$seps\-\(\)\[\]\x{3010}\x{3011}]|$)/u", $ti, $mat, \PREG_SET_ORDER)) {
        if($mat[0][1] + 0 > 0) {
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $mat[0][1],
                'episEd' => $mat[0][1],
                'itemVr' => 1,
                'favTi' => preg_replace("/[$seps\+\-\(\)\[\]#\x{3010}\x{3011}\x{7B2C}]+(\d+)([$seps\+\-\(\)\[\]\x{3010}\x{3011}]*|$)/u", '', $ti),
                'matFnd' => "1_1_30_1-1"
            ];
        }
        else {
            //isolated EEE = 0, treat as PV 0
            return [
                'medTyp' => 1,
                'numSeq' => 8,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => 0,
                'episEd' => 0,
                'itemVr' => 1,
                'favTi' => preg_replace("/[$seps\-\(\)\[\]#\x{3010}\x{3011}\x{7B2C}](\d+)([$seps\-\(\)\[\]\x{3010}\x{3011}]|$)/u", '', $ti),
                'matFnd' => "1_1_30_1-2"
            ];
        }
    }
}

function matchTitle1_1_30_2($ti, $seps) {
    // Japanese ## Episode
    $mat=[];
    if(preg_match_all("/\x{7B2C}(\d+)(\x{8a71}|\x{8bdd})/u", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => mb_convert_kana($mat[0][1], "a", "UTF-8"),
            'episEd' => mb_convert_kana($mat[0][1], "a", "UTF-8"),
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*\x{7B2C}(\d+)(\x{8a71}|\x{8bdd})[$seps]*/u", '', $ti),
            'matFnd' => "1_1_30_2"
        ];
    }
}

function matchTitle1_1_30_3($ti, $seps) {
    // Japanese ## Print Media Book/Volume
    $mat=[];
    if(preg_match_all("/(\x{7B2C}|\x{5168})(\d+)\x{5dfb}/u", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 4,
            'numSeq' => 4,
            'seasSt' => mb_convert_kana($mat[0][2], "a", "UTF-8"),
            'seasEd' => mb_convert_kana($mat[0][2], "a", "UTF-8"),
            'episSt' => 0,
            'episEd' => 0,
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*(\x{7B2C}|\x{5168})(\d+)\x{5dfb}[$seps]*/u", '', $ti),
            'matFnd' => "1_1_30_3"
        ];
    }
}

function matchTitle1_1_30_4($ti, $seps) {
    // Japanese ##
    $mat=[];
    if(preg_match_all("/\x{7B2C}(\d+)/u", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => mb_convert_kana($mat[0][1], "a", "UTF-8"),
            'episEd' => mb_convert_kana($mat[0][1], "a", "UTF-8"),
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*\x{7B2C}(\d+)[$seps]*/u", '', $ti),
            'matFnd' => "1_1_30_3"
        ];
    }
}

function matchTitle2_1($ti, $seps) {
    // go straight for S##E##
    // S01E10
    // Season 1 Episode 10
    // Se.2.Ep.5
    // Seas 2, Epis 3
    // S3 - E6
    // S2 - 32
    $mat=[];
    if(preg_match_all("/(Season|\bSeas|\bSe|\bS)[$seps]?(\d+)[$seps]?[\,\-]?[$seps]?(Episode|Epis|Epi|Ep|E|)[$seps]?(\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[0][2],
            'seasEd' => $mat[0][2],
            'episSt' => $mat[0][4],
            'episEd' => $mat[0][4],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*(Season|\bSeas|\bSe|\bS)[$seps]?(\d+)[$seps]?[\,\-]?[$seps]?(Episode|Epis|Epi|Ep|E|)[$seps]?(\d+)[$seps]*/i", '', $ti),
            'matFnd' => "2_1"
        ];
    }
}

function matchTitle2_2($ti, $seps) {
    // \b##v#\b (Episode ## Version #)
    //TODO might need to move this below other superset matches
    $mat=[];
    if(preg_match_all("/\b(\d+)[$seps]?-?[$seps]?(v|V)(\d{1,2})\b/", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[0][1],
            'episEd' => $mat[0][1],
            'itemVr' => $mat[0][3],
            'favTi' => preg_replace("/[$seps]*\b(\d+)[$seps]?-?[$seps]?(v|V)(\d{1,2})\b[$seps]*/", '', $ti),
            'matFnd' => "2_2"
        ];
    }
}

function matchTitle2_3($ti, $seps) {
    // short-circuit Seasons ## through|thru|to ##
    // Example:
    // Seasons 2 to 4
    // S2 thru 4
    $mat=[];
    if(preg_match_all("/(Seasons|Season|\bSeas|\bSe|\bS)[$seps]?(\d+)[$seps]?(through|thru|to)[$seps]?(\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[0][2],
            'seasEd' => $mat[0][4],
            'episSt' => 1,
            'episEd' => '',
            'itemVr' => 1,
            'favTi' => "Cannot match batches of seasons.",
            'matFnd' => "2_3"
        ];
    }
}

function matchTitle2_4($ti, $seps) {
    // short-circuit Seasons 1 - ##
    // Example:
    // Seasons 1 - 4
    // Seas. 1 - 5
    // assume count($mat) == 1
    $mat=[];
    if(preg_match_all("/(Seasons|\bSeas)[$seps]?1[$seps]?\-[$seps]?(\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => $mat[0][2],
            'episSt' => 1,
            'episEd' => '',
            'itemVr' => 1,
            'favTi' => "Cannot match batches of seasons.",
            'matFnd' => "2_4"
        ];
    }
}

function matchTitle2_5($ti, $seps) {
    // short-circuit S1 - ###
    // Example:
    // S1 - 24
    // Se 1 - 5
    // Season 1 - 3
    $mat=[];
    if(preg_match_all("/(Season|\bSe|\bS)[$seps]?1[$seps]?\-[$seps]?(\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => $mat[0][2],
            'episSt' => 1,
            'episEd' => '',
            'itemVr' => 1,
            'favTi' => "Cannot match batches of seasons.",
            'matFnd' => "2_5"
        ];
    }
}

function matchTitle2_6($ti, $seps) {
    // search for the word Season, Temporada; should also catch Season ## Complete; put this last in Season matches
    $mat=[];
    if(preg_match_all("/(Season|Saison|Seizoen|Sezona|\bSeas|\bSais|\bSea|\bSe|\bS|Temporada|\bTemp|\bT)[$seps]?(\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[0][1],
            'seasEd' => $mat[0][1],
            'episSt' => 1,
            'episEd' => '',
            'itemVr' => 1,
            'favTi' => "Cannot match entire seasons.", //TODO figure out favTi for Seasons
            'matFnd' => "2_6"
        ];
    }
}

function matchTitle2_7($ti, $seps) {
    // search for 1st|2nd|3rd Season ##
    $mat=[];
    if(preg_match_all("/(\d{1,2})(st|th|nd)[$seps]?(Season|Saison|Seizoen|Sezona)[$seps]?-?[$seps]?(\d+)\b/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[0][1],
            'seasEd' => $mat[0][1],
            'episSt' => $mat[0][4],
            'episEd' => $mat[0][4],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*(\d{1,2})(st|th|nd)[$seps]?(Season|Saison|Seizoen|Sezona)[$seps]?-?[$seps]?(\d+)\b[$seps]*/i", '', $ti),
            'matFnd' => "2_7"
        ];
    }
}

function matchTitle2_11($ti, $seps) {
    // search for 1st|2nd|3rd Season ##
    $mat=[];
    if(preg_match_all("/\b(\d+)[$seps]?(-|to|thru|through)[$seps]?end\b/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[0][1],
            'episEd' => '',
            'itemVr' => 1,
            'favTi' => "Cannot match entire seasons.", //TODO figure out favTi for Seasons
            'matFnd' => "2_11"
        ];
    }
}

function matchTitle2_13($ti, $seps) {
    // isolated ##x##
    //TODO make sure x264 doesn't match
    $mat=[];
    if(preg_match_all("/\b(\d+)[$seps]?[xX][$seps]?(\d+)\b/", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[0][1],
            'seasEd' => $mat[0][1],
            'episSt' => $mat[0][2],
            'episEd' => $mat[0][2],
            'itemVr' => 1,
            'favTi' => preg_replace("/\b(\d+)[$seps]?[xX][$seps]?(\d+)\b/", '', $ti),
            'matFnd' => "2_13"
        ];
    }
}

function matchTitle2_14($ti, $seps) {
    // isolated ## of ##
    //TODO may be Part ## of ##
    $mat=[];
    if(preg_match_all("/\b(\d+)[$seps]?(OF|Of|of)[$seps]?(\d+)\b/", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[0][1],
            'episEd' => $mat[0][1],
            'itemVr' => 1,
            'favTi' => preg_replace("/\b(\d+)[$seps]?(OF|Of|of)[$seps]?(\d+)\b/", '', $ti),
            'matFnd' => "2_14"
        ];
    }
}

function matchTitle2_20($ti, $seps) {
    // isolated ## & ##
    // must be after other ## & ## but before ## elsewheres
    $mat=[];
    if(preg_match_all("/\b(\d+)[$seps]?(\&|and|\+|y|et)[$seps]?(\d+)\b/", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[0][1],
            'episEd' => $mat[0][3],
            'itemVr' => 1,
            'favTi' => preg_replace("/\b(\d+)[$seps]?(\&|and|\+|y|et)[$seps]?(\d+)\b/", '', $ti),
            'matFnd' => "2_20"
        ];
    }
}

function matchTitle2_21($ti, $seps) {
    // Volume ## - ##
    $mat=[];
    if(preg_match_all("/(Volumes|Volumens|Volume|\bVol|\bV)[$seps]?(\d+)[$seps]?(-|to|thru|through)[$seps]?(\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            //TODO handle batch of Volumes, video or print
        /*    'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => 0,
            'episEd' => 0,
            'itemVr' => 1,
            'favTi' => preg_replace("/(Volumes|Volumens|Volume|\bVol|\bV)[$seps]?(\d+)[$seps]?(-|to|thru|through)[$seps]?(\d+)/i", '', $ti), */
            'matFnd' => "2_21"
        ];
    }
}

function matchTitle2_22($ti, $seps) {
    // Volume ## Chapter ##
    $mat=[];
    if(preg_match_all("/(Volumen|Volume|\bVol|\bV\.)[$seps]?(\d+)[$seps]?(Chapitre|Chapter|Chap|\bCh|\bC\.)[$seps]?(\d+)\b/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            //TODO handle print Volume x Chapter
        /*    'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => 0,
            'episEd' => 0,
            'itemVr' => 1,
            'favTi' => preg_replace("/(Volumen|Volume|\bVol|\bV\.)[$seps]?(\d+)[$seps]?(Chapitre|Chapter|Chap|\bCh|\bC\.)[$seps]?(\d+)\b/i", '', $ti), */
            'matFnd' => "2_22"
        ];
    }
}

function matchTitle2_23($ti, $seps) {
    // Volume ##, # elsewhere
    $mat=[];
    if(preg_match_all("/(Volumen|Volume|\bVol|\bV\.)[$seps]?(\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            //TODO handle Volume, video or print
        /*    'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => 0,
            'episEd' => 0,
            'itemVr' => 1,
            'favTi' => preg_replace("/(Volumen|Volume|\bVol|\bV\.)[$seps]?(\d+)/i", '', $ti), */
            'matFnd' => "2_23"
        ];
    }
}

function matchTitle2_24($ti, $seps) {
    // Chapter ##-##
    $mat=[];
    if(preg_match_all("/(Chapters|Chapter|Capitulos|Capitulo|Chapitres|Chapitre|\bChap|\bCh|\bC)[$seps]?(\d+)[$seps]?(-|to|thru|through)[$seps]?(\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            //TODO handle print Chapters
        /*    'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => 0,
            'episEd' => 0,
            'itemVr' => 1,
            'favTi' => preg_replace("/(Chapters|Chapter|Capitulos|Capitulo|Chapitres|Chapitre|\bChap|\bCh|\bC)[$seps]?(\d+)[$seps]?(-|to|thru|through)[$seps]?(\d+)/i", '', $ti), */
            'matFnd' => "2_24"
        ];
    }
}

function matchTitle2_25($ti, $seps) {
    // Chapter ##, # elsewhere
    $mat=[];
    if(preg_match_all("/(Chapter|Capitulo|Chapitre|\bChap|\bCh|\bC)[$seps]?(\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            //TODO handle print Chapter
        /*    'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => 0,
            'episEd' => 0,
            'itemVr' => 1,
            'favTi' => preg_replace("/(Chapter|Capitulo|Chapitre|\bChap|\bCh|\bC)[$seps]?(\d+)/i", '', $ti), */
            'matFnd' => "2_25"
        ];
    }
}

function matchTitle2_26($ti, $seps) {
    // Movie ##, # elsewhere
    $mat=[];
    if(preg_match_all("/(Movie|\bMov)[$seps]?(\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            //TODO handle Movie ##
        /*    'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => 0,
            'episEd' => 0,
            'itemVr' => 1,
            'favTi' => preg_replace("/(Movie|\bMov)[$seps]?(\d+)/i", '', $ti), */
            'matFnd' => "2_26"
        ];
    }
}

function matchTitle2_27($ti, $seps) {
    // Film ##, # elsewhere
    $mat=[];
    if(preg_match_all("/(Film|\bF)[$seps]?(\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            //TODO handle Film ##
        /*    'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => 0,
            'episEd' => 0,
            'itemVr' => 1,
            'favTi' => preg_replace("/(Film|\bF)[$seps]?(\d+)/i", '', $ti), */
            'matFnd' => "2_27"
        ];
    }
}

function matchTitle2_28($ti, $seps) {
    // Part ##, # elsewhere
    $mat=[];
    if(preg_match_all("/(Part|\bPt)[$seps]?(\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            //TODO handle Part ##
        /*    'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => 0,
            'episEd' => 0,
            'itemVr' => 1,
            'favTi' => preg_replace("/(Part|\bPt)[$seps]?(\d+)/i", '', $ti), */
            'matFnd' => "2_28"
        ];
    }
}

function matchTitle2_30($ti, $seps) {
    // isolated SS - Episode ##
    // should not be any mention of Season ## before Episode ## because of entire section far above searching for the word Season
    $mat=[];
    if(preg_match_all("/\b(\d+)[$seps]?\-?[$seps]?(Episode|\bEpis|\bEp|\bE)[$seps]?(\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            //TODO handle Part ##
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[0][1],
            'seasEd' => $mat[0][1],
            'episSt' => $mat[0][3],
            'episEd' => $mat[0][3],
            'itemVr' => 1,
            'favTi' => preg_replace("/\b(\d+)[$seps]?\-?[$seps]?(Episode|\bEpis|\bEp|\bE)[$seps]?(\d+)/i", '', $ti),
            'matFnd' => "2_30"
        ];
    }
}

function matchTitle2_31($ti, $seps) {
    // Episode ##, # elsewhere
    $mat=[];
    if(preg_match_all("/(Episode|\bEpis|\bEp|\bE)[$seps]?(\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[0][2],
            'episEd' => $mat[0][2],
            'itemVr' => 1,
            'favTi' => preg_replace("/(Episode|\bEpis|\bEp|\bE)[$seps]?(\d+)/i", '', $ti),
            'matFnd' => "2_31"
        ];
    }
}

function matchTitle2_32($ti, $seps) {
    // Japanese ##-## Print Media Books/Volumes
    $mat=[];
    if(preg_match_all("/\x{7B2C}(\d+)[$seps]?-[$seps]?(\d+)\x{5dfb}/u", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 4,
            'numSeq' => 4,
            'seasSt' => mb_convert_kana($mat[0][1], "a", "UTF-8"),
            'seasEd' => mb_convert_kana($mat[0][2], "a", "UTF-8"),
            'episSt' => 0,
            'episEd' => 0,
            'itemVr' => 1,
            'favTi' => "Cannot match batches of Volumes.",
            'matFnd' => "2_32"
        ];
    }
}

function matchTitle2_33($ti, $seps) {
    // Japanese ## Episode, ## elsewhere
    $mat=[];
    if(preg_match_all("/\x{7B2C}(\d+)(\x{8a71}|\x{8bdd})/u", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => mb_convert_kana($mat[0][1], "a", "UTF-8"),
            'episEd' => mb_convert_kana($mat[0][1], "a", "UTF-8"),
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*\x{7B2C}(\d+)(\x{8a71}|\x{8bdd})[$seps]*/u", '', $ti),
            'matFnd' => "2_33"
        ];
    }
}

function matchTitle2_34($ti, $seps) {
    // Japanese ## Print Media Book/Volume, ## elsewhere
    $mat=[];
    if(preg_match_all("/\x{7B2C}(\d+)\x{5dfb}/u", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 4,
            'numSeq' => 4,
            'seasSt' => $mat[0][1],
            'seasEd' => $mat[0][1],
            'episSt' => 0,
            'episEd' => 0,
            'itemVr' => 1,
            'favTi' => preg_replace("[$seps]*/\x{7B2C}(\d+)\x{5dfb}[$seps]*/u", '', $ti),
            'matFnd' => "2_34"
        ];
    }
}

function matchTitle2_35($ti, $seps) {
    // Japanese YYYY MM Print Media
    $mat=[];
    if(preg_match_all("/(\b|\D)(\d{2}|\d{4})\x{5e74}?[\-$seps]?(\d{1,2})\x{6708}?\x{53f7}/u", $ti, $mat, \PREG_SET_ORDER)) {
        if(strlen($mat[0][3]) == 1) {
            $mat[0][3] = '0' . $mat[0][3];
        }
        return [
            'medTyp' => 4,
            'numSeq' => 2,
            'seasSt' => 0, // date notation gets Season 0
            'seasEd' => 0,
            'episSt' => $mat[0][2] . $mat[0][3],
            'episEd' => $mat[0][2] . $mat[0][3],
            'itemVr' => 1,
            'favTi' => preg_replace("[$seps]*(\b|\D)(\d{2}|\d{4})\x{5e74}?[\-$seps]?(\d{1,2})\x{6708}?\x{53f7}[$seps]*/u", '', $ti),
            'matFnd' => "2_35"
        ];
    }
}

function matchTitle2_36($ti, $seps) {
    // #nd EE
    $mat=[];
    if(preg_match_all("/\b(\d{1,2})(nd|th|st)[$seps]?(\d{1,2})\b/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[0][1],
            'seasEd' => $mat[0][1],
            'episSt' => $mat[0][3],
            'episEd' => $mat[0][3],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*\b(\d{1,2})(nd|th|st)[$seps]?(\d{1,2})\b[$seps]*/", '', $ti),
            'matFnd' => "2_36"
        ];
    }
}

function matchTitle2_37($ti, $seps) {
    // isolated ###.#
    $mat=[];
    if(preg_match_all("/\b(\d{1,3}\.\d)\b/", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[0][1],
            'episEd' => $mat[0][1],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*\b(\d{1,3}\.\d)\b[$seps]*/", '', $ti),
            'matFnd' => "2_37"
        ];
    }
}

function matchTitle2_38($ti, $seps) {
    // isolated YYYY-MM or title #### - EE
    $mat=[];
    if(preg_match_all("/\b(\d{4})[-$seps](\d{1,2})\b/", $ti, $mat, \PREG_SET_ORDER)) {
        if(checkdate($mat[0][2] + 0, 1, $mat[0][1] + 0) && $mat[0][1] + 0 <= getdate()['year'] && $mat[0][1] + 0 > 1895) {
            // YYYY-MM
            if(strlen($mat[0][2]) == 1) {
                $mat[0][2] = "0" . $mat[0][2];
            }
            return [
                'medTyp' => 1,
                'numSeq' => 2,
                'seasSt' => 0,
                'seasEd' => 0,
                'episSt' => $mat[0][1] . $mat[0][2],
                'episEd' => $mat[0][1] . $mat[0][2],
                'itemVr' => 1,
                'favTi' => preg_replace("/[$seps]*\b(\d{4})[-$seps](\d{1,2})\b[$seps]*/", '', $ti),
                'matFnd' => "2_38-1"
            ];
        }
        else {
            // #### is probably part of the title
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $mat[0][2],
                'episEd' => $mat[0][2],
                'itemVr' => 1,
                'favTi' => preg_replace("/[$seps]*\b(\d{1,3}\.\d)\b[$seps]*/", '', $ti),
                'matFnd' => "2_38-2"
            ];
        }
    }
}

function matchTitle2_39($ti, $seps) {
    // isolated MM-YYYY
    $mat=[];
    if(preg_match_all("/\b(\d{1,2})[-$seps](\d{4})\b/", $ti, $mat, \PREG_SET_ORDER)) {
        if(checkdate($mat[0][1] + 0, 1, $mat[0][2] + 0) && $mat[0][2] + 0 <= getdate()['year'] && $mat[0][2] + 0 > 1895) {
            // MM-YYYY
            if(strlen($mat[0][1]) == 1) {
                        $mat[0][1] = "0" . $mat[0][1];
            }
            return [
                'medTyp' => 1,
                'numSeq' => 2,
                'seasSt' => 0,
                'seasEd' => 0,
                'episSt' => $mat[0][2] . $mat[0][1],
                'episEd' => $mat[0][2] . $mat[0][1],
                'itemVr' => 1,
                'favTi' => preg_replace("/[$seps]*\b(\d{1,2})[-$seps](\d{4})\b[$seps]*/", '', $ti),
                'matFnd' => "2_39-1"
            ];
        }
/*        else {
            //TODO: handle else
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $mat[0][2],
                'episEd' => $mat[0][2],
                'itemVr' => 1,
                'favTi' => preg_replace("/\b(\d{1,3}\.\d)\b/u", '', $ti),
                'matFnd' => "2_39-2"
            ];
        } */
    }
}

function matchTitle2_40($ti, $seps) {
    // (YYYY) - EE or title (####) - EE
    $mat=[];
    if(preg_match_all("/\((\d{4})\)[$seps]?-?[$seps]?(\d{1,3})\b/", $ti, $mat, \PREG_SET_ORDER)) {
        if($mat[0][1] + 0 <= getdate()['year'] && $mat[0][1] + 0 > 1895) {
            // (YYYY) - EE
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => $mat[0][1], // Half date notation and half episode: let Season = YYYY
                'seasEd' => $mat[0][1],
                'episSt' => $mat[0][2],
                'episEd' => $mat[0][2],
                'itemVr' => 1,
                'favTi' => preg_replace("/\((\d{4})\)[$seps]?-?[$seps]?(\d{1,3})\b[$seps]*/", '', $ti),
                'matFnd' => "2_40-1"
            ];
        }
        else {
            // #### is probably part of the title
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $mat[0][2],
                'episEd' => $mat[0][2],
                'itemVr' => 1,
                'favTi' => preg_replace("/\((\d{4})\)[$seps]?-?[$seps]?(\d{1,3})\b[$seps]*/", '', $ti),
                'matFnd' => "2_40-2"
            ];
        }
    }
}

function matchTitle2_41($ti, $seps) {
    // isolated No.##-No.##, Print Media Book/Volume
    $mat=[];
    if(preg_match_all("/\b(Num|No\.|No)[$seps]?(\d+)[$seps]?(-|to|thru|through)[$seps]?(Num|No\.|No|)[$seps]?(\d+)\b/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 4,
            'numSeq' => 4,
            'seasSt' => $mat[0][2],
            'seasEd' => $mat[0][5],
            'episSt' => 0,
            'episEd' => 0,
            'itemVr' => 1,
            'favTi' => "Cannot match batches of Volumes.",
            'matFnd' => "2_41"
        ];
    }
}

function matchTitle2_42($ti, $seps) {
    // isolated S1 #10
    $mat=[];
    if(preg_match_all("/\b(s|S)(\d{1,2})[$seps]?#(\d+)\b/", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[0][2],
            'seasEd' => $mat[0][2],
            'episSt' => $mat[0][3],
            'episEd' => $mat[0][3],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*\b(s|S)(\d{1,2})[$seps]?#(\d+)\b[$seps]*/", '', $ti),
            'matFnd' => "2_42"
        ];
    }
}

function matchTitle2_43($ti, $seps) {
    // isolated ## to ##
    $mat=[];
    if(preg_match_all("/\b(\d{1,3})[$seps]?(through|thru|to|\x{e0})[$seps]?(\d{1,3})\b/iu", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[0][1],
            'episEd' => $mat[0][3],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*\b(\d{1,3})[$seps]?(through|thru|to|\x{e0})[$seps]?(\d{1,3})\b[$seps]*/iu", '', $ti),
            'matFnd' => "2_43"
        ];
    }
}

function matchTitle2_44($ti, $seps) {
    // isolated ##-##
    $mat=[];
    if(preg_match_all("/\b(\d{1,3})[$seps]?\-[$seps]?(\d+)\b/", $ti, $mat, \PREG_SET_ORDER)) {
        // MUST keep first ### less than 4 digits to prevent Magic Kaito 1412 - EE from matching
        if(substr($mat[0][2], 0, 1) == '0' && substr($mat[0][1], 0, 1) != '0') {
            // certainly S - EE
            // Examples:
            // Sword Art Online 2 - 07
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => $mat[0][1],
                'seasEd' => $mat[0][1],
                'episSt' => $mat[0][2],
                'episEd' => $mat[0][2],
                'itemVr' => 1,
                'favTi' => preg_replace("/[$seps]*\b(\d{1,3})[$seps]?\-[$seps]?(\d+)\b[$seps]*/", '', $ti),
                'matFnd' => "2_44-1"
            ];
        }
        else if($mat[0][1] == 1) {
            // probably 1 - EE, since people rarely refer to Season 1 without mentioning Season|Seas|Sea|Se|S
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $mat[0][1],
                'episEd' => $mat[0][2],
                'itemVr' => 1,
                'favTi' => preg_replace("/[$seps]*\b(\d{1,3})[$seps]?\-[$seps]?(\d+)\b[$seps]*/", '', $ti),
                'matFnd' => "2_44-2"
            ];
        }
        else if(substr($mat[0][1], 0, 1) == '0' ||
                        (strlen($mat[0][1]) > 1 && strlen($mat[0][2]) - strlen($mat[0][1]) < 2 && $mat[0][1] + 0  < $mat[0][2] + 0)) {
            // isolated EE - EE
            // if leading digit of first ## is 0 or
            // it's more than 1 digit and second ## is no more than 1 digit longer and second ## is greater than first ##,
            // it's probably not a season but EE - EE, such as 09 - 11
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $mat[0][1],
                'episEd' => $mat[0][2],
                'itemVr' => 1,
                'favTi' => preg_replace("/[$seps]*\b(\d{1,3})[$seps]?\-[$seps]?(\d+)\b[$seps]*/", '', $ti),
                'matFnd' => "2_44-3"
            ];
        }
        else {
            // isolated S - EE
            // assume S - EE
            // Examples:
            // 3 - 17
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => $mat[0][1],
                'seasEd' => $mat[0][1],
                'episSt' => $mat[0][2],
                'episEd' => $mat[0][2],
                'itemVr' => 1,
                'favTi' => preg_replace("/[$seps]*\b(\d{1,3})[$seps]?\-[$seps]?(\d+)\b[$seps]*/", '', $ti),
                'matFnd' => "2_44-4"
            ];
        }

    }
}

function matchTitle2_45($ti, $seps) {
    // isolated SS EE or isolated EE EE
    $mat=[];
    if(preg_match_all("/\b(\d{1,3})[$seps](\d{1,3})\b/", $ti, $mat, \PREG_SET_ORDER)) {
        if($mat[0][1] < $mat[0][2] &&
                        $mat[0][1] < 6 && // most cours never pass 5
                        $mat[0][2] - $mat[0][1] < 15) {
            // isolated SS EE
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => $mat[0][1],
                'seasEd' => $mat[0][1],
                'episSt' => $mat[0][2],
                'episEd' => $mat[0][2],
                'itemVr' => 1,
                'favTi' => preg_replace("/[$seps]*\b(\d{1,3})[$seps](\d{1,3})\b[$seps]*/", '', $ti),
                'matFnd' => "2_45-1"
            ];
        }
        else {
            // isolated EE EE
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $mat[0][1],
                'episEd' => $mat[0][2],
                'itemVr' => 1,
                'favTi' => preg_replace("/[$seps]*\b(\d{1,3})[$seps](\d{1,3})\b[$seps]*/", '', $ti),
                'matFnd' => "2_45-2"
            ];
        }
    }
}

function matchTitle2_46($ti, $seps) {
    // isolated - EEE, # elsewhere
    $mat=[];
    if(preg_match_all("/[$seps]?\-[$seps]?(\d{1,3})\b/", $ti, $mat, \PREG_SET_ORDER)) {
        if($mat[0][1] > 0) {
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $mat[0][1],
                'episEd' => $mat[0][1],
                'itemVr' => 1,
                'favTi' => preg_replace("/[$seps]*\-[$seps]?(\d{1,3})\b[$seps]*/", '', $ti),
                'matFnd' => "2_46-1"
            ];
        }
        else {
            // isolated EEE = 0, # elsewhere
            // treat as PV 0
            return [
                'medTyp' => 1,
                'numSeq' => 8,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => 0,
                'episEd' => 0,
                'itemVr' => 1,
                'favTi' => preg_replace("/[$seps]*\-[$seps]?(\d{1,3})\b[$seps]*/", '', $ti),
                'matFnd' => "2_46-2"
            ];
        }
    }
}

function matchTitle2_47($ti, $seps) {
    // isolated EEE, # elsewhere
    $mat=[];
    if(preg_match_all("/[$seps\-\(\)\[\]#\x{3010}\x{3011}\x{7B2C}](\d+)([$seps\-\(\)\[\]\x{3010}\x{3011}]|$)/u", $ti, $mat, \PREG_SET_ORDER)) {
        if($mat[0][1] > 0) {
            // isolated EEE, # elsewhere
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => $mat[0][1],
                'episEd' => $mat[0][1],
                'itemVr' => 1,
                'favTi' => preg_replace("/[$seps\-\(\)\[\]#\x{3010}\x{3011}\x{7B2C}](\d+)([$seps\-\(\)\[\]\x{3010}\x{3011}]|$)/u", '', $ti),
                'matFnd' => "2_47-1"
            ];
        }
        else {
            // isolated EEE = 0, # elsewhere
            // treat as PV 0
            return [
                'medTyp' => 1,
                'numSeq' => 8,
                'seasSt' => 1,
                'seasEd' => 1,
                'episSt' => 0,
                'episEd' => 0,
                'itemVr' => 1,
                'favTi' => preg_replace("/[$seps\-\(\)\[\]#\x{3010}\x{3011}\x{7B2C}](\d+)([$seps\-\(\)\[\]\x{3010}\x{3011}]|$)/u", '', $ti),
                'matFnd' => "2_47-2"
            ];
        }
    }
}

function matchTitle3_1($ti, $seps) {
    // isolated YYYY MM DD
    $mat=[];
    if(preg_match_all("/\b(\d{4})[$seps\-](\d{1,2})[$seps\-](\d{1,2})\b/", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 0,
            'seasEd' => 0,
            'episSt' => $mat[0][1] . $mat[0][2] . $mat[0][3],
            'episEd' => $mat[0][1] . $mat[0][2] . $mat[0][3],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*\b(\d{4})[$seps\-](\d{1,2})[$seps\-](\d{1,2})\b[$seps]*/i", '', $ti),
            'matFnd' => "3_1"
        ];
    }
}

function matchTitle3_2($ti, $seps) {
    // isolated S##E## - E## range
    $mat=[];
    if(preg_match_all("/\b[Ss](\d+)[Ee](\d+)[$seps]?\-?[$seps]?[Ee](\d+)\b/", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[0][1],
            'seasEd' => $mat[0][1],
            'episSt' => $mat[0][2],
            'episEd' => $mat[0][3],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*\b[Ss](\d+)[Ee](\d+)[$seps]?\-?[$seps]?[Ee](\d+)\b[$seps]*/i", '', $ti),
            'matFnd' => "3_2"
        ];
    }
}

function matchTitle3_3($ti, $seps) {
    // isolated S##E##, # elsewhere
    $mat=[];
    if(preg_match_all("/\b[Ss](\d+)[Ee](\d+)\b/", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[0][1],
            'seasEd' => $mat[0][1],
            'episSt' => $mat[0][2],
            'episEd' => $mat[0][2],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*\b[Ss](\d+)[Ee](\d+)\b[$seps]*/i", '', $ti),
            'matFnd' => "3_3"
        ];
    }
}

function matchTitle3_4($ti, $seps) {
    // isolated SSxEE, # elsewhere
    //TODO make sure x264 doesn't match
    $mat=[];
    if(preg_match_all("/\b(\d+)[$seps]?[xX][$seps]?(\d+)\b/", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[0][1],
            'seasEd' => $mat[0][1],
            'episSt' => $mat[0][2],
            'episEd' => $mat[0][2],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*\b(\d+)[$seps]?[xX][$seps]?(\d+)\b[$seps]*/i", '', $ti),
            'matFnd' => "3_4"
        ];
    }
}

function matchTitle3_5($ti, $seps) {
    // isolated S# - ###.#
    $mat=[];
    if(preg_match_all("/\bS(\d+)[$seps]?\-[$seps]?(\d{1,3}\.\d|\d+)\b/", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[0][1],
            'seasEd' => $mat[0][1],
            'episSt' => $mat[0][2],
            'episEd' => $mat[0][2],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*\bS(\d+)[$seps]?\-[$seps]?(\d{1,3}\.\d|\d+)\b[$seps]*/i", '', $ti),
            'matFnd' => "3_5"
        ];
    }
}

function matchTitle3_6($ti, $seps) {
    // S2 - ### - ### (keep just before S2 - ###, # elsewhere)
    $mat=[];
    if(preg_match_all("/\bS(\d+)[$seps]?\-?[$seps]?\b(\d{1,3})\b[$seps]?\-?[$seps]?\b(\d{1,3})\b/", $ti, $mat, \PREG_SET_ORDER)) {
        if($mat[0][3] > $mat[0][2]) {
            // isolated S# - EE - EE
            // probably range of Episodes within one Season
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => $mat[0][1],
                'seasEd' => $mat[0][1],
                'episSt' => $mat[0][2],
                'episEd' => $mat[0][3],
                'itemVr' => 1,
                'favTi' => preg_replace("/[$seps]*\bS(\d+)[$seps]?\-?[$seps]?\b(\d{1,3})\b[$seps]?\-?[$seps]?\b(\d{1,3})\b[$seps]*/", '', $ti),
                'matFnd' => "3_6-1"
            ];
        }
        else {
            // isolated S# - EE, extra ##
            // not sure what it is, probably extra number on end
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => $mat[0][1],
                'seasEd' => $mat[0][1],
                'episSt' => $mat[0][2],
                'episEd' => $mat[0][2],
                'itemVr' => 1,
                'favTi' => preg_replace("/[$seps]*\bS(\d+)[$seps]?\-?[$seps]?\b(\d{1,3})\b[$seps]?\-?[$seps]?\b(\d{1,3})\b[$seps]*/", '', $ti),
                'matFnd' => "3_6-2"
            ];
        }
    }
}

function matchTitle3_7($ti, $seps) {
    // isolated S2 - ###, # elsewhere (must be preceded by S2 - ### - ### to trap Episode range)
    $mat=[];
    if(preg_match_all("/\bS(\d+)[$seps]?\-?[$seps]?(\d{1,3})\b/", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[0][1],
            'seasEd' => $mat[0][1],
            'episSt' => $mat[0][2],
            'episEd' => $mat[0][2],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*\bS(\d+)[$seps]?\-?[$seps]?(\d{1,3})\b[$seps]*/i", '', $ti),
            'matFnd' => "3_7"
        ];
    }
}

function matchTitle3_8($ti, $seps) {
    // Japanese YYYY MM DD Print Media
    $mat=[];
    if(preg_match_all("/(\b|\D)(\d{2}|\d{4})\x{5e74}?\-?(\d{1,2})\x{6708}?\-?(\d+)\x{65e5}?\x{53f7}/u", $ti, $mat, \PREG_SET_ORDER)) {
        if(strlen($mat[0][3]) == 1) {
            $mat[0][3] = "0" . $mat[0][3];
        }
        if(strlen($mat[0][4]) == 1) {
            $mat[0][4] = "0" . $mat[0][4];
        }
        return [
            'medTyp' => 4,
            'numSeq' => 2,
            'seasSt' => 0,
            'seasEd' => 0,
            'episSt' => $mat[0][2] . $mat[0][3] . $mat[0][4],
            'episEd' => $mat[0][2] . $mat[0][3] . $mat[0][4],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*(\b|\D)(\d{2}|\d{4})\x{5e74}?\-?(\d{1,2})\x{6708}?\-?(\d+)\x{65e5}?\x{53f7}[$seps]*/u", '', $ti),
            'matFnd' => "3_8"
        ];
    }
}

function matchTitle3_9($ti, $seps) {
    // Japanese YYYY MM Print Media, # elsewhere
    $mat=[];
    if(preg_match_all("/(\b|\D)(\d{2}|\d{4})\x{5e74}?\-?(\d+)\x{53f7}/u", $ti, $mat, \PREG_SET_ORDER)) {
        if(strlen($mat[0][3]) == 1) {
            $mat[0][3] = "0" . $mat[0][3];
        }
        if(strlen($mat[0][4]) == 1) {
            $mat[0][4] = "0" . $mat[0][4];
        }
        return [
            'medTyp' => 4,
            'numSeq' => 2,
            'seasSt' => 0,
            'seasEd' => 0,
            'episSt' => $mat[0][2] . $mat[0][3],
            'episEd' => $mat[0][2] . $mat[0][3],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*(\b|\D)(\d{2}|\d{4})\x{5e74}?\-?(\d+)\x{53f7}[$seps]*/u", '', $ti),
            'matFnd' => "3_9"
        ];
    }
}

function matchTitle3_10($ti, $seps) {
    // explicit S##.#E###.#
    // Example:
    // S01.5E10.5
    // Season 1 Episode 10
    // Se.2.Ep.5
    // Seas 2, Epis 3
    // S3 - E6
    // S2 - 32 (NOTE: passed S1 - ### short-circuit above, so assume Season 2, Episode 32)
    $mat=[];
    if(preg_match_all("/(Season|Saison|Seizoen|Sezona|Seas\b|\bSe\b|\bS\b)[$seps]?(\d+\.\d|\d+)[$seps]?[\,\-]?[$seps]?(Episode|Epizode|\bEpis\b|\bEpi\b|\bEp\b|\bE\b|Capitulo|)[$seps]?(\d+\.\d|\d+)/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[0][2],
            'seasEd' => $mat[0][2],
            'episSt' => $mat[0][4],
            'episEd' => $mat[0][4],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*(Season|Saison|Seizoen|Sezona|Seas\b|\bSe\b|\bS\b)[$seps]?(\d+\.\d|\d+)[$seps]?[\,\-]?[$seps]?(Episode|Epizode|\bEpis\b|\bEpi\b|\bEp\b|\bE\b|Capitulo|)[$seps]?(\d+\.\d|\d+)[$seps]*/i", '', $ti),
            'matFnd' => "3_10"
        ];
    }
}

function matchTitle3_11($ti, $seps) {
    // short-circuit YYYY.SSxEE so that "Doctor.Who.2005.8x10.In" doesn't match later
    $mat=[];
    if(preg_match_all("/(\d{4})\.(\d+\.\d|\d+)[$seps]?[xX][$seps]?(\d+\.\d|\d+)/", $ti, $mat, \PREG_SET_ORDER) && preg_match("/^[12]+/", $mat[0][1])) {
        // YYYY.SSxEE
        // short-circuit YYYY.SSxEE so that "Doctor.Who.2005.8x10.In" doesn't match later
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[0][2],
            'seasEd' => $mat[0][2],
            'episSt' => $mat[0][3],
            'episEd' => $mat[0][3],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*(\d{4})\.(\d+\.\d|\d+)[$seps]?[xX][$seps]?(\d+\.\d|\d+)[$seps]*/", '', $ti),
            'matFnd' => "3_11"
        ];
    }
}

function matchTitle3_12($ti, $seps) {
    // YYYY MM DD or YYYY M D but not YYYYMMDD
    $mat=[];
    if(preg_match_all("/(\d{4})[$seps](\d{1,2})[$seps](\d{1,2})/", $ti, $mat, \PREG_SET_ORDER) && preg_match("/^[12]+/", $mat[0][1])) {
        // YYYY.SSxEE
        //TODO make sure YYYY MM and DD make sense
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 0,
            'seasEd' => 0,
            'episSt' => $mat[0][1] . $mat[0][2] . $mat[0][3],
            'episEd' => $mat[0][1] . $mat[0][2] . $mat[0][3],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*(\d{4})[$seps](\d{1,2})[$seps](\d{1,2})[$seps]*/", '', $ti),
            'matFnd' => "3_12"
        ];
    }
}

function matchTitle3_13($ti, $seps) {
    // search for explicit YYYYMMDD (must have two-digit MM and DD or could be MDD or MMD), TWO OTHER # ELSEWHERE
    $mat=[];
    if(preg_match_all("/(\d{4})(\d{2})(\d{2})/", $ti, $mat, \PREG_SET_ORDER) && preg_match("/^[12]+/", $mat[0][1])) {
        // YYYYMMDD
        //TODO make sure YYYY MM and DD make sense
        //TODO handle the two extra numbers elsewhere
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 0,
            'seasEd' => 0,
            'episSt' => $mat[0][1] . $mat[0][2] . $mat[0][3],
            'episEd' => $mat[0][1] . $mat[0][2] . $mat[0][3],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*(\d{4})(\d{2})(\d{2})[$seps]*/", '', $ti),
            'matFnd' => "3_13"
        ];
    }
}

function matchTitle3_14($ti, $seps) {
    // ## Episode ## but not ##E##, # elsewhere
    // (no mention of Season, as that would have matched earlier, must have space to block checksum matches)
    $mat=[];
    if(preg_match_all("/(\d+\.\d|\d+)[$seps](Episode|Epis|Epi|Ep|E)[$seps]?(\d+\.\d|\d+)[\(\)$seps]/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[0][1],
            'seasEd' => $mat[0][1],
            'episSt' => $mat[0][3],
            'episEd' => $mat[0][3],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*(\d+\.\d|\d+)[$seps](Episode|Epis|Epi|Ep|E)[$seps]?(\d+\.\d|\d+)[\(\)$seps][$seps]*/", '', $ti),
            'matFnd' => "3_14"
        ];
    }
}

function matchTitle3_15($ti, $seps) {
    // search for ## Episode ## but not ##E## at very end of title, # elsewhere
    $mat=[];
    if(preg_match_all("/(\d+\.\d|\d+)[$seps](Episode|Epis|Epi|Ep|E)[$seps]?(\d+\.\d|\d+)$/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[0][1],
            'seasEd' => $mat[0][1],
            'episSt' => $mat[0][3],
            'episEd' => $mat[0][3],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*(\d+\.\d|\d+)[$seps](Episode|Epis|Epi|Ep|E)[$seps]?(\d+\.\d|\d+)$[$seps]*/", '', $ti),
            'matFnd' => "3_15"
        ];
    }
}

function matchTitle3_16($ti, $seps) {
    // Episode ## - ##, # elsewhere
    // (no mention of Season, as that would have matched earlier)
    $mat=[];
    if(preg_match_all("/[\(\)$seps](Episodes|Episode|Epis|Epi|Ep|E)[$seps]?(\d+\.\d|\d+)[\(\)$seps]?\-[$seps]?(\d{1,3}\.\d|\d{1,3})/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[0][2],
            'seasEd' => $mat[0][2],
            'episSt' => $mat[0][3],
            'episEd' => $mat[0][3],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*[\(\)$seps](Episodes|Episode|Epis|Epi|Ep|E)[$seps]?(\d+\.\d|\d+)[\(\)$seps]?\-[$seps]?(\d{1,3}\.\d|\d{1,3})[$seps]*/i", '', $ti),
            'matFnd' => "3_16"
        ];
    }
}

function matchTitle3_17($ti, $seps) {
    // Episode ##, TWO OTHER # ELSEWHERE
    // search for Episode ## (no mention of Season, as that would have matched earlier)
    //TODO handle the two extra numbers elsewhere
    $mat=[];
    if(preg_match_all("/[\(\)$seps](Episode|Epis|Epi|Ep|E)[$seps]?(\d+\.\d|\d+)[\(\)$seps]?/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[0][2],
            'episEd' => $mat[0][2],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*[\(\)$seps](Episode|Epis|Epi|Ep|E)[$seps]?(\d+\.\d|\d+)[\(\)$seps]?[$seps]*/i", '', $ti),
            'matFnd' => "3_17"
        ];
    }
}

function matchTitle3_18($ti, $seps) {
    // ###.# to ###.# episodes
    // search for ###.# to ###.# (assume episodes here, because search for multiple seasons happened earlier)
    //TODO fix it so that 2005.08 to 50 doesn't match
    $mat=[];
    if(preg_match_all("/(\d{1,3}\.\d|\d{1,3})[$seps]?(through|thru|to)[$seps]?(\d{1,3}\.\d|\d{1,3})/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[0][1],
            'episEd' => $mat[0][3],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*(\d{1,3}\.\d|\d{1,3})[$seps]?(through|thru|to)[$seps]?(\d{1,3}\.\d|\d{1,3})[$seps]*/i", '', $ti),
            'matFnd' => "3_18"
        ];
    }
}

function matchTitle3_20($ti, $seps) {
    // (YYYY) - EE (EEE) (must precede (YYYY) - EE)
    $mat=[];
    if(preg_match_all("/\((\d{4})\)[$seps]?\-?[$seps]?(\d{1,3})[$seps]?\-?[$seps]?\((\d{1,4})\)/", $ti, $mat, \PREG_SET_ORDER)) {
        if($mat[0][1] + 0 <= getdate()['year'] && $mat[0][1] + 0 > 1895) {
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => $mat[0][1], // Half date notation and half episode: let Season = YYYY
                'seasEd' => $mat[0][1],
                'episSt' => $mat[0][2],
                'episEd' => $mat[0][2],
                'itemVr' => 1,
                'favTi' => preg_replace("/[$seps]*\((\d{4})\)[$seps]?\-?[$seps]?(\d{1,3})[$seps]?\-?[$seps]?\((\d{1,4})\)[$seps]*/", '', $ti),
                'matFnd' => "3_20"
            ];
        }
        //TODO handle else
    }
}

function matchTitle3_21($ti, $seps) {
    // (YYYY) - EE, # elsewhere
    $mat=[];
    if(preg_match_all("/\((\d{4})\)[$seps]?-?[$seps]?(\d{1,3})\b/", $ti, $mat, \PREG_SET_ORDER)) {
        if($mat[0][1] + 0 <= getdate()['year'] && $mat[0][1] + 0 > 1895) {
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => $mat[0][1], // Half date notation and half episode: let Season = YYYY
                'seasEd' => $mat[0][1],
                'episSt' => $mat[0][2],
                'episEd' => $mat[0][2],
                'itemVr' => 1,
                'favTi' => preg_replace("/[$seps]*\((\d{4})\)[$seps]?-?[$seps]?(\d{1,3})\b[$seps]*/", '', $ti),
                'matFnd' => "3_21"
            ];
        }
        //TODO handle else
    }
}

function matchTitle3_22($ti, $seps) {
    // isolated SS ##v# (Season ## Episode ## Version #)
    //TODO might need to move this below other superset matches
    $mat=[];
    if(preg_match_all("/\b(\d+)\b[$seps]?\-?[$seps]?\b(\d+)[$seps]?\-?[$seps]?(v|V)(\d{1,2})\b/", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => $mat[0][1],
            'seasEd' => $mat[0][1],
            'episSt' => $mat[0][2],
            'episEd' => $mat[0][2],
            'itemVr' => $mat[0][4],
            'favTi' => preg_replace("/[$seps]*\b(\d+)\b[$seps]?\-?[$seps]?\b(\d+)[$seps]?\-?[$seps]?(v|V)(\d{1,2})\b[$seps]*/", '', $ti),
            'matFnd' => "3_22"
        ];
    }
}

function matchTitle3_23($ti, $seps) {
    // isolated ##v#, # elsewhere (Episode ## Version #)
    //TODO might need to move this below other superset matches
    $mat=[];
    if(preg_match_all("/\b(\d+)[$seps]?\-?[$seps]?(v|V)(\d{1,2})\b/", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[0][1],
            'episEd' => $mat[0][1],
            'itemVr' => $mat[0][3],
            'favTi' => preg_replace("/[$seps]*\b(\d+)[$seps]?\-?[$seps]?(v|V)(\d{1,2})\b[$seps]*/", '', $ti),
            'matFnd' => "3_23"
        ];
    }
}

function matchTitle3_24($ti, $seps) {
    // #### EE - EE
    $mat=[];
    if(preg_match_all("/\b(\d{1,4})[$seps]?\-?[$seps]?\b(\d{1,3})[$seps]?\-[$seps]?(\d{1,3})\b/", $ti, $mat, \PREG_SET_ORDER)) {
        if($mat[0][2] < $mat[0][3]) {
            // could be #### EE - EE
            if(strlen($mat[0][1]) > 2) {
                // first ### is probably not a season and is probably part of the title
                return [
                    'medTyp' => 1,
                    'numSeq' => 1,
                    'seasSt' => 1,
                    'seasEd' => 1,
                    'episSt' => $mat[0][2],
                    'episEd' => $mat[0][3],
                    'itemVr' => 1,
                    'favTi' => preg_replace("/[$seps]*\b(\d{1,4})[$seps]?\-?[$seps]?\b(\d{1,3})[$seps]?\-[$seps]?(\d{1,3})\b[$seps]*/", '', $ti),
                    'matFnd' => "3_24-1"
                ];
            }
            else {
                // assume SS EE - EE (not the same as S2 EE - EE handled far above, because letter S is not specified)
                return [
                    'medTyp' => 1,
                    'numSeq' => 1,
                    'seasSt' => $mat[0][1],
                    'seasEd' => $mat[0][1],
                    'episSt' => $mat[0][2],
                    'episEd' => $mat[0][3],
                    'itemVr' => 1,
                    'favTi' => preg_replace("/[$seps]*\b(\d{1,4})[$seps]?\-?[$seps]?\b(\d{1,3})[$seps]?\-[$seps]?(\d{1,3})\b[$seps]*/", '', $ti),
                    'matFnd' => "3_24-2"
                ];
            }
        }
        else {
            //TODO handle unidentifiable ## ## - ##
        }
    }
}

function matchTitle3_25($ti, $seps) {
    // ## - ##, # elsewhere (must be preceded by SS EE - EE)
    $mat=[];
    if(preg_match_all("/\b(\d{1,3})\b[$seps]?\-?[$seps]?\b(\d{1,3})\b/", $ti, $mat, PREG_SET_ORDER)) {
        // search for ## - ##, is it SS - EE or EE - EE? Make sure EE - EE first EE < second EE
        if($mat[0][1] >= $mat[0][2]) {
            // probably SS - EE, not EE - EE
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => $mat[0][1],
                'seasEd' => $mat[0][1],
                'episSt' => $mat[0][2],
                'episEd' => $mat[0][2],
                'itemVr' => 1,
                'favTi' => preg_replace("/[$seps]*\b(\d{1,3})\b[$seps]?\-?[$seps]?\b(\d{1,3})\b[$seps]*/", '', $ti),
                'matFnd' => "3_25-1"
            ];
        }
        else {
            if(substr($mat[0][2], 0, 1) == '0' && substr($mat[0][1], 0, 1) != '0') {
                // almost certainly S - EE, not EE - EE
                return [
                    'medTyp' => 1,
                    'numSeq' => 1,
                    'seasSt' => $mat[0][1],
                    'seasEd' => $mat[0][1],
                    'episSt' => $mat[0][2],
                    'episEd' => $mat[0][2],
                    'itemVr' => 1,
                    'favTi' => preg_replace("/[$seps]*\b(\d{1,3})\b[$seps]?\-?[$seps]?\b(\d{1,3})\b[$seps]*/", '', $ti),
                    'matFnd' => "3_25-2"
                ];
            }
            else {
                // probably EE - EE
                return [
                    'medTyp' => 1,
                    'numSeq' => 1,
                    'seasSt' => 1,
                    'seasEd' => 1,
                    'episSt' => $mat[0][1],
                    'episEd' => $mat[0][2],
                    'itemVr' => 1,
                    'favTi' => preg_replace("/[$seps]*\b(\d{1,3})\b[$seps]?\-?[$seps]?\b(\d{1,3})\b[$seps]*/", '', $ti),
                    'matFnd' => "3_25-3"
                ];
            }
        }
    }
}

function matchTitle3_26($ti, $seps) {
    // Volume ##, Chapter ##, # elsewhere
    $mat=[];
    if(preg_match_all("/(Volumen|Volume|\bVol|\bV\.)[$seps]?(\d+)[$seps]?(Capitulo|Chapter|Chap|\bCh|\bC\.)[$seps]?(\d+)\b/i", $ti, $mat, \PREG_SET_ORDER)) {
        return [
            'medTyp' => 4,
            'numSeq' => 1,
            'seasSt' => $mat[0][2],
            'seasEd' => $mat[0][2],
            'episSt' => $mat[0][4],
            'episEd' => $mat[0][4],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*(Volumen|Volume|\bVol|\bV\.)[$seps]?(\d+)[$seps]?(Capitulo|Chapter|Chap|\bCh|\bC\.)[$seps]?(\d+)\b[$seps]*/i", '', $ti),
            'matFnd' => "3_26"
        ];
    }
}

function matchTitle3_27($ti, $seps) {
    // - ##, not ## - ## (which is matched earlier), TWO OTHER # ELSEWHERE
    //TODO handle possibility of matching the wrong ##s
    $mat=[];
    if(preg_match_all("/\D[$seps]?\-[$seps]?(\d{1,3}\.\d|\d{1,3})/", $ti, $mat, PREG_SET_ORDER)) {
        // search for - ##, not ## - ## (which is matched earlier)
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[0][1],
            'episEd' => $mat[0][1],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*\D[$seps]?\-[$seps]?(\d{1,3}\.\d|\d{1,3})[$seps]*/", '', $ti),
            'matFnd' => "3_27"
        ];
    }
}

function matchTitle3_28($ti, $seps) {
    // ###.#, # elsewhere
    // search for 1 to 3-digit numbers with at most tenths place (last resort, otherwise may match earlier, longer strings)
    //TODO handle possibility of matching the wrong ##s
    $mat=[];
    if(preg_match_all("/[\(\)$seps](\d{1,3}\.\d|\d{1,3})[\(\)$seps]/", $ti, $mat, PREG_SET_ORDER)) {
        // search for - ##, not ## - ## (which is matched earlier)
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[0][1],
            'episEd' => $mat[0][1],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*[\(\)$seps](\d{1,3}\.\d|\d{1,3})[\(\)$seps][$seps]*/", '', $ti),
            'matFnd' => "3_28"
        ];
    }
}

function matchTitle3_29($ti, $seps) {
    // isolated ###.#, # elsewhere
    //TODO handle possibility of matching the wrong ##s
    $mat=[];
    if(preg_match_all("/\b(\d{1,3}\.\d|\d+)\b/", $ti, $mat, PREG_SET_ORDER)) {
        // search for - ##, not ## - ## (which is matched earlier)
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[0][1],
            'episEd' => $mat[0][1],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*\b(\d{1,3}\.\d|\d+)\b[$seps]*/", '', $ti),
            'matFnd' => "3_29"
        ];
    }
}

function matchTitle3_30($ti, $seps) {
    // ###.# at end, TWO OTHER # ELSEWHERE
    //TODO handle possibility of matching the wrong ##s
    $mat=[];
    if(preg_match_all("/[\(\)$seps](\d{1,3}\.\d|\d{1,3})$/", $ti, $mat, PREG_SET_ORDER)) {
        //TODO make sure this is not part of a date!
        return [
            'medTyp' => 1,
            'numSeq' => 1,
            'seasSt' => 1,
            'seasEd' => 1,
            'episSt' => $mat[0][1],
            'episEd' => $mat[0][1],
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*[\(\)$seps](\d{1,3}\.\d|\d{1,3})$/", '', $ti),
            'matFnd' => "3_30"
        ];
    }
}

function matchTitle3_31($ti, $seps) {
    // 4-digit #### at end, TWO OTHER # ELSEWHERE
    // (very last resort, otherwise may short-circuit earlier matches)
    //TODO handle possibility of matching the wrong ##s
    $mat=[];
    if(preg_match_all("/[\(\)$seps](\d{4}\.\d|\d{4})[\(\)$seps]/", $ti, $mat, PREG_SET_ORDER)) {
        //TODO make sure this is not part of a date!
        // invention of moving pictures on film was in 1896
        if($mat[0][1] > 1895) {
            // probably YYYY
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 0, // date-notation gets Season 0
                'seasEd' => 0,
                'episSt' => $mat[0][1],
                'episEd' => $mat[0][1],
                'itemVr' => 1,
                'favTi' => preg_replace("/[$seps]*[\(\)$seps](\d{4}\.\d|\d{4})[\(\)$seps][$seps]*/", '', $ti),
                'matFnd' => "3_31-1"
            ];
        }
        else if(substr($mat[0][1], 0, 1) == '0') {
            // probably MMDD
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 0,
                'seasEd' => 0,
                'episSt' => $mat[0][1],
                'episEd' => $mat[0][1],
                'itemVr' => 1,
                'favTi' => preg_replace("/[$seps]*[\(\)$seps](\d{4}\.\d|\d{4})[\(\)$seps][$seps]*/", '', $ti),
                'matFnd' => "3_31-2"
            ];
        }
        else {
            // probably anime episode between 1000 and 1895, could be MMDD
            return [
                'medTyp' => 1,
                'numSeq' => 1,
                'seasSt' => 1, // anime episode notation gets Season 1
                'seasEd' => 1,
                'episSt' => $mat[0][1],
                'episEd' => $mat[0][1],
                'itemVr' => 1,
                'favTi' => preg_replace("/[$seps]*[\(\)$seps](\d{4}\.\d|\d{4})[\(\)$seps][$seps]*/", '', $ti),
                'matFnd' => "3_31-3"
            ];
        }
    }
}

function matchTitle0_1($ti, $seps) {
    // search for the isolated word Special
    $mat=[];
    if(preg_match_all("/(\bSpecial|\bSpec)[$seps]?/i", $ti, $mat, PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 16,
            'seasSt' => 1, // assume Season 1
            'seasEd' => 1,
            'episSt' => 1, // assume Special Episode 1
            'episEd' => 1,
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*(\bSpecial|\bSpec)[$seps]?/i", '', $ti),
            'matFnd' => "0_1"
        ];
    }
}

function matchTitle0_2($ti, $seps) {
    // search for the isolated word OVA
    $mat=[];
    if(preg_match_all("/\bOVA[$seps]?/", $ti, $mat, PREG_SET_ORDER)) {
        return [
            'medTyp' => 1,
            'numSeq' => 32,
            'seasSt' => 1, // assume Season 1
            'seasEd' => 1,
            'episSt' => 1, // assume OVA 1
            'episEd' => 1,
            'itemVr' => 1,
            'favTi' => preg_replace("/[$seps]*\bOVA[$seps]?/", '', $ti),
            'matFnd' => "0_2"
        ];
    }
}
