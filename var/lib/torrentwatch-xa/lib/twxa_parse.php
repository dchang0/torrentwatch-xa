<?php

// functions for parsing torrent titles
// twxa_feed.php calls this file

$seps = '\s\.\_'; // separator chars: - and () were formerly also separators but caused problems; we need - for some Season and Episode notations
// load matchTitle function files
require_once("twxa_parse_match.php");
require_once("twxa_parse_match0.php");
require_once("twxa_parse_match1.php");
require_once("twxa_parse_match2.php");
require_once("twxa_parse_match3.php");
require_once("twxa_parse_match4.php");
require_once("twxa_parse_match5.php");
require_once("twxa_parse_match6.php");

function collapseExtraSeparators($ti) {
    $ti = str_replace("  ", " ", $ti);
    $ti = str_replace(" .", ".", $ti);
    $ti = str_replace(". ", ".", $ti);
    $ti = str_replace("..", ".", $ti);
    $ti = str_replace("__", "_", $ti);
    // trim beginning and ending spaces, periods, and minuses
    $ti = trim($ti, ".- \t\n\r\0\x0B");
    return $ti;
}

function collapseExtraMinuses($ti) {
    $ti = str_replace("- -", "-", $ti);
    $ti = str_replace("--", "-", $ti);
    return $ti;
}

function removeEmptyParens($ti) {
    // remove empty parentheses
    $ti = str_replace("( ", "(", $ti);
    $ti = str_replace(" )", ")", $ti);
    $ti = str_replace("(-)", "", $ti);
    $ti = str_replace("(.)", "", $ti);
    $ti = str_replace("( )", "", $ti);
    $ti = str_replace("()", "", $ti);
    $ti = rtrim($ti, "(");
    return $ti;
}

function sanitizeTitle($ti) {
    // cleans title of symbols, aiming to get the title down to just alphanumerics and reserved separators
    // we sanitize the title to make it easier to use Favorites and match episodes
    // remove soft hyphens
    $ti = str_replace("\xC2\xAD", "", $ti);
    // replace every tilde with a minus
    $ti = str_replace("~", "-", $ti);
    // replace brackets, etc., with space; keep parentheses, periods, underscores, and minus
    $ti = str_replace('[', ' ', $ti);
    $ti = str_replace(']', ' ', $ti);
    $ti = str_replace('{', ' ', $ti);
    $ti = str_replace('}', ' ', $ti);
    $ti = str_replace('<', ' ', $ti);
    $ti = str_replace('>', ' ', $ti);
    $ti = str_replace(',', ' ', $ti);
    $ti = str_replace('_', ' ', $ti);
    $ti = str_replace('/', ' ', $ti);
    $ti = str_replace('|', ' ', $ti);
    // IMPORTANT: reduce multiple reserved separators down to one separator
    return collapseExtraSeparators($ti);
}

function normalizeCodecs($ti, $seps = '\s\.\_') {
    $ti = preg_replace("/([XxHh])[$seps\-]+(264|265)/", "$1$2", $ti); // note the separator chars PLUS - char
    $ti = preg_replace("/(\d{1,3})[$seps\-]?bits?/i", "$1bit", $ti); // normalize ## bit
    $ti = preg_replace("/FLAC[$seps\-]+2(\.0)?/i", 'FLAC2', $ti);
    $ti = preg_replace("/AAC[$seps\-]+2(\.0)?/i", 'AAC2', $ti);
    return $ti;
}

function validateYYYYMMDD($date) {
    $YYYY = (int) substr($date, 0, 4);
    $MM = (int) substr($date, 4, 2);
    $DD = (int) substr($date, 6, 2);
    return checkdate($MM, $DD, $YYYY);
}

function simplifyTitle($ti) {
    // combines all the title processing functions
    $ti = sanitizeTitle($ti);

    // MUST normalize these codecs/qualities now so that users get trained to use normalized versions
    $ti = normalizeCodecs($ti);

    // detect and strip out 7 or 8-character checksums
    $mat = [];
    if (preg_match("/([0-9a-f])[0-9a-f]{6,7}/i", $ti, $mat)) {
        // only handle first one--not likely to have more than one checksum in any title
        $wholeMatch = $mat[0];
        $firstChar = $mat[1];
        if (preg_match("/\D/", $wholeMatch)) {
            // any non-digit means it's a checksum
            $ti = str_replace($wholeMatch, "", $ti);
        } else if ($firstChar > 2) {
            // if first digit is not 0, 1, or 2, it's likely not a date
            $ti = str_replace($wholeMatch, "", $ti);
        } else {
            // remove 8-digit checksums that look like they might be dates
            if (!validateYYYYMMDD($wholeMatch)) {
                $ti = str_replace($wholeMatch, "", $ti);
            }
        }
    }
    // run collapse due to possibility of checksum removal leaving back-to-back separators
    return collapseExtraSeparators($ti);
}

function detectResolution($ti, $seps = '\s\.\_') {
    $wByHRegEx = "/(\d{3,4})[$seps]*[xX][$seps]*((\d{3,4})[iIpP]?)/";
    $hRegEx = "/\b(\d{3,4})[iIpP]\b/"; // added \b to end to block YUV444P10
    $bDHRegEx = "/\bBD(\d{3,4})([iIpP]?)\b/"; //TODO handle BD1280x720p
    $resolution = "";
    $matchedResolution = "";
    $verticalLines = "";
    $detQualities = [];
    $matches = [];

    if (preg_match($bDHRegEx, $ti, $matches)) {
        // standalone resolutions in BD### format
        // shouldn't be more than one resolution in title
        if (
                $matches[1] == 576 ||
                $matches[1] == 720 ||
                $matches[1] == 1076 || // some people are forcing 1920x1076
                $matches[1] == 1080 ||
                $matches[1] == 1200
        ) {
            $matchedResolution = $matches[0];
            $verticalLines = $matches[1];
            if ($matches[2] === "") {
                $resolution = $matches[1] . "p";
            } else {
                $resolution = strtolower($matches[1] . $matches[2]);
            }
        }
    } else if (preg_match($hRegEx, $ti, $matches)) {
        // standalone resolutions in ###p or ###i format
        // shouldn't be more than one resolution in title
        $matchedResolution = $matches[0];
        $resolution = strtolower($matchedResolution);
        $verticalLines = $matches[1];
    } else if (preg_match($wByHRegEx, $ti, $matches)) {
        // search arbitrarily for #### x #### (might also be Season x Episode or YYYY x MMDD)
        // check aspect ratios
        if (
                $matches[1] * 9 == $matches[3] * 16 || // 16:9 aspect ratio
                $matches[1] * 0.75 == $matches[3] || // 4:3 aspect ratio
                $matches[1] * 5 == $matches[3] * 8 || // 16:10 aspect ratio
                $matches[1] * 2 == $matches[3] * 3 || // 3:2 aspect ratio
                $matches[1] * 0.8 == $matches[3] || // 5:4 aspect ratio
                $matches[1] * 10 == $matches[3] * 19 || // 19:10 4K aspect ratio
                $matches[1] * 135 == $matches[3] * 256 || // 256:135 4K aspect ratio
                $matches[1] * 3 == $matches[3] * 7 || // 21:9 4K aspect ratio
                $matches[3] == 576 ||
                $matches[3] == 720 ||
                $matches[3] == 1040 || // some people are forcing 1920x1040
                $matches[3] == 1076 || // some people are forcing 1920x1076
                $matches[3] == 1080 ||
                $matches[3] == 1200 ||
                ($matches[1] == 720 && ($matches[3] == 406 || $matches[3] == 544)) || // some people are forcing 720x406 or 720x544
                ($matches[3] == 480 && $matches[1] >= 848 && $matches[1] <= 864) || // some people are forcing 848x480p or 852x480p
                ($matches[1] == 704 && $matches[3] == 400) || // some people are forcing 704x400
                ($matches[1] == 744 && $matches[3] == 418) // some people are forcing 744x418
        ) {
            $matchedResolution = $matches[0];
            $resolution = strtolower($matches[2]);
            $verticalLines = $matches[3];
            if ($resolution == $verticalLines) {
                $resolution .= 'p'; // default to p if no i or p is specified
            }
        }
    }
    $ti = str_replace($matchedResolution, "", $ti);
    if ($verticalLines == 720 || $verticalLines == 1080) {
        $detQualities = ["HD", "HDTV"];
    } else if ($verticalLines == 576) {
        $detQualities = ["ED", "EDTV"];
        $ti = preg_replace("/SD(TV)?/i", "", $ti); // remove SD also (ED will be removed by detectQualities())
    } else if ($verticalLines == 480) {
        $detQualities = ["SD", "SDTV"];
    }
    if ($resolution !== "") {
        $detQualities[] = $resolution;
    }
    return [
        'parsedTitle' => collapseExtraSeparators($ti),
        'detectedQualities' => $detQualities
    ];
}

function detectQualities($ti, $seps = '\s\.\_') {
    $qualitiesFromResolution = detectResolution($ti, $seps);
    // more quality matches and prepend them to detectedQualities
    $ti = $qualitiesFromResolution['parsedTitle'];
    $detQualities = $detResolutions = $qualitiesFromResolution['detectedQualities'];
    $qualityList = [
        'BD-rip',
        'BDRip',
        'BRRip',
        'BluRay',
        'Blu-ray',
        'BD',
        'HR.HDTV',
        'HDTVRip',
        'HDTV',
        'HDrip',
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
        'DVDRip',
        'DVDR',
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
        'HTML5',
        'iTunes',
        // codecs--could be high or low quality, who knows?
        'XviD',
        'x264',
        'h264',
        'x265',
        'h265',
        'Hi10P',
        'Hi10',
        'HEVC-265',
        'HEVC 265',
        'HEVC265',
        'HEVC2',
        'HEVC',
        'NVENC',
        'Ma10p',
        '24bit',
        '10bit',
        '8bit',
        //'AVC',
        //'AVI',
        //'MP4',
        //'MKV',
        'BT.709',
        'BT.601',
        // colorspaces
        'YUV420p10',
        'YUV444p10',
        'YUV440p12',
        'GBRP10',
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
        if (preg_match("/\b" . $qualityListItem . "\b/i", $ti)) {
            $detQualities[] = $qualityListItem;
            $ti = preg_replace("/\b" . $qualityListItem . "\b/i", "", $ti);
        }
    }
    return [
        'parsedTitle' => collapseExtraSeparators($ti),
        'detectedQualities' => $detQualities,
        'detectedResolutions' => $detResolutions
    ];
}

function detectAudioCodecs($ti) {
    $detAudioCodecs = [];
    $audioCodecList = [// watch the order!
        'EAC3',
        'AC3',
        'AACx2',
        'AAC2',
        'AAC',
        'FLACx2',
        'FLAC2',
        'FLAC',
        '320Kbps',
        '320kbps',
        '320K',
        'MP3',
        'M4A',
        '5.1ch',
        '5.1',
        '2ch'
    ];
    foreach ($audioCodecList as $audioCodecListItem) {
        if (preg_match("/\b" . $audioCodecListItem . "\b/i", $ti)) {
            $detAudioCodecs[] = $audioCodecListItem;
            //TODO cascade down through, removing immediately-surrouding dashes
            $ti = preg_replace("/\b" . $audioCodecListItem . "\b/i", "", $ti);
        }
    }
    return [
        'parsedTitle' => collapseExtraSeparators($ti),
        'detectedAudioCodecs' => $detAudioCodecs
    ];
}

function detectNumericCrew($ti, $seps = '\s\.\_') {
    // detect crew name with numerals in title and remove it
    // assume crew name is always at the beginning of the title and is often in parentheses or brackets
    $rmCrewName = "";
    $mat = [];
    $crewNameList = [
        "(C72)",
        "&#40;C72&#41;", //TODO why do these HTML entities make it into our $ti in the first place?
        "(C85)",
        "&#40;C85&#41;",
        "(C88)",
        "&#40;C88&#41;",
        "(C91)",
        "&#40;C91&#41;",
        "Doujinshi (C91)",
        "(C92)",
        "&#40;C92&#41;",
        //TODO maybe switch to (C\d\d) regex
        "Al3asq",
        "F4A-MDS",
        "blad761",
        "bonkai77",
        "Ch4" // Channel 4 documentaries
    ];
    foreach ($crewNameList as $crewName) {
        $quotedCrewName = preg_quote($crewName);
        if (preg_match("/^" . $quotedCrewName . "[" . $seps . "]*/", $ti, $mat)) { // can't use strpos because we need $mat
            // found it at the beginning, now remove it to be re-added later
            $ti = preg_replace("/" . $quotedCrewName . "[" . $seps . "]*/", "", $ti);
            $rmCrewName = $mat[0];
            break;
        }
    }
    return [
        'rmCrewName' => $rmCrewName,
        'parsedTitle' => $ti
    ];
}

function detectpROPERrEPACK($ti) {
    $mat = [];
    $detected = "";
    $re = "/\b(PROPER|REPACK|Repack|RERIP|RERip|RERiP)\b/";
    if (preg_match($re, $ti, $mat)) {
        $detected = $mat[0];
        $ti = collapseExtraSeparators(str_replace($detected, "", $ti));
    }
    return [
        'detectedpROPERrEPACK' => $detected,
        'parsedTitle' => $ti
    ];
}

function detectMatch($ti) {
    global $config_values;

    $episGuess = "";

    // detect qualities
    $detQualitiesOutput = detectQualities(simplifyTitle($ti));
    $detQualitiesJoined = implode(' ', $detQualitiesOutput['detectedQualities']); //TODO is it really necessary to avoid using count?
    if ($config_values['Settings']['Resolutions Only'] == "yes") {
        $detQualities = $detQualitiesOutput['detectedResolutions'];
    } else {
        $detQualities = $detQualitiesOutput['detectedQualities'];
    }
    $detQualitiesRegEx = ".*";

    // don't use count() on arrays because it returns 1 if not countable; it is enough to know if any quality was detected
    if (strlen($detQualitiesJoined) > 0) {
        $wereQualitiesDetected = true;
        $detQualitiesTemp = [];
        foreach ($detQualities as $detQuality) {
            $detQualitiesTemp[] = preg_quote($detQuality);
        }
        if (count($detQualitiesTemp) > 1) {
            $detQualitiesRegEx = "(" . implode('|', $detQualitiesTemp) . ")";
        } else if (isset($detQualitiesTemp[0])) {
            $detQualitiesRegEx = $detQualitiesTemp[0];
        }
    } else {
        $wereQualitiesDetected = false;
    }

    //TODO detect video-related words like Sub and Dub
    // strip out audio codecs
    $detAudioCodecsOutput = detectAudioCodecs($detQualitiesOutput['parsedTitle']);

    // after removing Qualities and Audio Codecs, there may be ( ) or () left behind
    $detAudioCodecsOutput['parsedTitle'] = removeEmptyParens(collapseExtraMinuses($detAudioCodecsOutput['parsedTitle']));

    // strip the crew name
    $detNumericCrewOutput = detectNumericCrew($detAudioCodecsOutput['parsedTitle']);

    // detect PROPER/REPACK/RERIP
    $detPROutput = detectpROPERrEPACK($detNumericCrewOutput['parsedTitle']);

    // detect episode
    $detItemOutput = detectItem($detPROutput['parsedTitle'], $wereQualitiesDetected);
    $detItemOutput['favTitle'] = removeEmptyParens($detItemOutput['favTitle']);
    $seasBatEnd = $detItemOutput['seasBatEnd'];
    $seasBatStart = $detItemOutput['seasBatStart'];
    $episBatEnd = $detItemOutput['episBatEnd'];
    $episBatStart = $detItemOutput['episBatStart'];

    // set itemVersion to 99 for PROPER/REPACK/RERIP
    if ($detPROutput['detectedpROPERrEPACK'] != "") {
        $detItemOutput['itemVersion'] = 99;
    }

    // parse episode output into human-friendly notation
    // our numbering style is 1x2v2-2x3v3
    if ($seasBatEnd > -1) {
        // found a ending season, probably detected other three values too
        if ($seasBatEnd == $seasBatStart) {
            // within one season
            if ($episBatEnd == $episBatStart && $episBatEnd > -1) {
                // single episode
                if ($seasBatEnd == 0) {
                    // date notation
                    $episGuess = $episBatEnd;
                } else {
                    $episGuess = $seasBatEnd . 'x' . $episBatEnd;
                }
                if ($detItemOutput['itemVersion'] > 1) {
                    $episGuess .= "v" . $detItemOutput['itemVersion'];
                }
            } else if ($episBatEnd > $episBatStart && $episBatStart > -1) {
                // batch of episodes within one season
                if ($seasBatEnd == 0) {
                    // date notation
                    $episGuess = $episBatStart . '-' . $episBatEnd;
                } else {
                    $episGuess = $seasBatStart . 'x' . $episBatStart . '-' . $seasBatStart . 'x' . $episBatEnd;
                }
            } else if ($episBatEnd == "") {
                // assume full season
                $episGuess = $seasBatEnd . 'xFULL';
            } else {
                // not sure of what exceptions there might be to the above
            }
        } else if ($seasBatEnd > $seasBatStart) {
            // batch spans multiple seasons, treat EpisodeStart as paired with SeasonStart and EpisodeEnd as paired with SeasonEnd
            if ($episBatEnd == "") {
                $episGuess = $seasBatStart . 'xFULL-' . $seasBatEnd . 'xFULL';
            } else {
                $episGuess = $seasBatStart . 'x' . $episBatStart . '-' . $seasBatEnd . 'x' . $episBatEnd;
            }
        }
    } else {
        $episGuess = "notSerialized";
    }
    // add the removed crew name back if one was removed
    $favTitle = collapseExtraSeparators($detItemOutput['favTitle']);
    if ($detNumericCrewOutput['rmCrewName'] !== "") {
        $favTitle = $detNumericCrewOutput['rmCrewName'] . $favTitle;
    }

    return [
        'title' => collapseExtraSeparators($detAudioCodecsOutput['parsedTitle']),
        'favTitle' => $favTitle,
        'qualities' => $detQualitiesJoined,
        'qualitiesRegEx' => $detQualitiesRegEx,
        'episode' => $episGuess,
        'seasBatEnd' => $detItemOutput['seasBatEnd'],
        'seasBatStart' => $detItemOutput['seasBatStart'],
        'episBatEnd' => $detItemOutput['episBatEnd'],
        'episBatStart' => $detItemOutput['episBatStart'],
        'isVideo' => $wereQualitiesDetected, //TODO replace this with mediaType
        'mediaType' => $detItemOutput['mediaType'],
        'itemVersion' => $detItemOutput['itemVersion'],
        'numberSequence' => $detItemOutput['numberSequence'],
        'debugMatch' => $detItemOutput['debugMatch']
    ];
}

function detectItem($ti, $wereQualitiesDetected = false, $seps = '\s\.\_') {
    // our numbering style is 1x2v2-2x3v3
    // $wereQualitiesDetected is a param because some manga use "Vol. ##" notation
    // $medTyp state table
    // 0 = Unknown
    // 1 = Video
    // 2 = Audio
    // 4 = Print media
    // $numSeq allows for parallel numbering sequences
    // like Movie 1, Movie 2, Movie 3 alongside Episode 1, Episode 2, Episode 3
    // 0 = None/unknown
    // 1 = Video: Season x Episode or FULL, Print Media: Volume x Chapter or FULL, Audio: Season x Episode or FULL
    // 2 = Video: Date, Print Media: Date, Audio: Date (all these get Season = 0)
    // 4 = Video: Season x Volume (x Episode), Print Media: N/A, Audio: N/A
    // 8 = Video: Preview, Print Media: N/A, Audio: Opening songs
    // 16 = Video: Special, Print Media: N/A, Audio: Ending songs
    // 32 = Video: OVA episode sequence, Print Media: N/A, Audio: Character songs
    // 64 = Video: Movie sequence (Season = 0), Print Media: N/A, Audio: OST
    // 128 = Video: (Season x) Volume x Disc/Part sequence, Print Media: N/A, Audio: N/A
    // IMPORTANT NOTES:
    // treat anime notation as Season 1
    // treat date-based episodes as Season 0 EXCEPT...
    // ...when YYYY-##, use year as the Season and ## as the Episode
    // because of PHP left-to-right matching order, (Season|Seas|Se|S) works but (S|Se|Seas|Season) will match S and move on
    //TODO decode HTML and URL encoded characters to reduce number of extraneous numerals
    $ti = html_entity_decode($ti, ENT_QUOTES);

    // bucket the matches of all numbers of different lengths
    $matNums = [];
    preg_match_all("/(\d+)/u", $ti, $matNums, \PREG_SET_ORDER); // can't initialize $matNums here due to isset tests later
    // is there at least one number? can't have an episode otherwise (except in case of PV preview episode)
    $numbersDetected = count($matNums);
    if (isset($matNums[0])) {
        switch ($numbersDetected) {
            case 8:
            case 7:
            case 6:
                $result = matchTitle6_($ti, $seps);
                if ($result['matFnd'] !== "6_") {
                    if ($numbersDetected !== 6) {
                        $result['matFnd'] = $numbersDetected . "_ (" . $result['matFnd'] . ")";
                    }
                    break;
                }
            case 5:
                $result = matchTitle5_($ti, $seps);
                if ($result['matFnd'] !== "5_") {
                    if ($numbersDetected !== 5) {
                        $result['matFnd'] = $numbersDetected . "_ (" . $result['matFnd'] . ")";
                    }
                    break;
                }
            case 4:
                $result = matchTitle4_($ti, $seps);
                if ($result['matFnd'] !== "4_") {
                    if ($numbersDetected !== 4) {
                        $result['matFnd'] = $numbersDetected . "_ (" . $result['matFnd'] . ")";
                    }
                    break;
                }
            case 3:
                $result = matchTitle3_($ti, $seps);
                if ($result['matFnd'] !== "3_") {
                    if ($numbersDetected !== 3) {
                        $result['matFnd'] = $numbersDetected . "_ (" . $result['matFnd'] . ")";
                    }
                    break;
                }
            case 2:
                $result = matchTitle2_($ti, $seps, $wereQualitiesDetected);
                if ($result['matFnd'] !== "2_") {
                    if ($numbersDetected !== 2) {
                        $result['matFnd'] = $numbersDetected . "_ (" . $result['matFnd'] . ")";
                    }
                    break;
                }
            case 1:
                $result = matchTitle1_($ti, $seps, $wereQualitiesDetected);
                if ($result['matFnd'] !== "1_") {
                    if ($numbersDetected !== 1) {
                        $result['matFnd'] = $numbersDetected . "_ (" . $result['matFnd'] . ")";
                    }
                    break;
                }
            default:
                $result['matFnd'] = $numbersDetected . "_"; // didn't find any match
                $result['favTi'] = $ti;
        }
        // trim off leading zeroes
        if (isset($result['episEd']) && $result['episEd'] != "") {
            if (is_numeric($result['episEd'])) {
                $result['episEd'] += 0;
            } else {
                writeToLog($result['matFnd'] . ": " . $result['episEd'] . " is not numeric in $ti\n", -1);
            }
        }
        if (isset($result['episSt']) && $result['episSt'] != "") {
            if (is_numeric($result['episSt'])) {
                $result['episSt'] += 0;
            } else {
                writeToLog($result['matFnd'] . ": " . $result['episSt'] . " is not numeric in $ti\n", -1);
            }
        }
        if (isset($result['seasEd']) && $result['seasEd'] != "") {
            if (is_numeric($result['seasEd'])) {
                $result['seasEd'] += 0;
            } else {
                writeToLog($result['matFnd'] . ": " . $result['seasEd'] . " is not numeric in $ti\n", -1);
            }
        }
        if (isset($result['seasSt']) && $result['seasSt'] != "") {
            if (is_numeric($result['seasSt'])) {
                $result['seasSt'] += 0;
            } else {
                writeToLog($result['matFnd'] . ": " . $result['seasSt'] . " is not numeric in $ti\n", -1);
            }
        }
    } else {
        // handle no-numeral episodes
        $result = matchTitle0_($ti, $seps);
    } // END if(isset($matNums[0]))
    if (!isset($result['seasSt'])) {
        $result['seasSt'] = "";
    }
    if (!isset($result['seasEd'])) {
        $result['seasEd'] = "";
    }
    if (!isset($result['episSt'])) {
        $result['episSt'] = "";
    }
    if (!isset($result['episEd'])) {
        $result['episEd'] = "";
    }
    if (!isset($result['medTyp'])) {
        $result['medTyp'] = "";
    }
    if (!isset($result['itemVr'])) {
        $result['itemVr'] = "";
    }
    if (!isset($result['numSeq'])) {
        $result['numSeq'] = "";
    }
    if (!isset($result['favTi'])) {
        $result['favTi'] = "";
    }
    if (!isset($result['matFnd'])) {
        $result['matFnd'] = "";
    }
    return [
        'seasBatStart' => $result['seasSt'], // detected season batch start
        'seasBatEnd' => $result['seasEd'],
        'episBatStart' => $result['episSt'], // detected episode batch start
        'episBatEnd' => $result['episEd'],
        'mediaType' => $result['medTyp'],
        'itemVersion' => $result['itemVr'],
        'numberSequence' => $result['numSeq'],
        'favTitle' => sanitizeTitle($result['favTi']), // favorite title
        'debugMatch' => $result['matFnd']
    ];
}

function parseSxEvVNotation($input) {
    $season = $episode = $version = '';
    $success = true;
    switch (true) {
        case true:
            if ($input === '') {
                break;
            }
        case true:
            $mat = [];
            if (preg_match("/^(\d+)x(\d+|\d+\.\d+)v(\d+)$/", $input, $mat)) {
                $season = $mat[1];
                $episode = $mat[2];
                $version = $mat[3];
                break;
            }
        case true:
            $mat = [];
            if (preg_match("/^(\d+)x(\d+|\d+\.\d+)$/", $input, $mat)) {
                $season = $mat[1];
                $episode = $mat[2];
                break;
            }
        case true:
            $mat = [];
            if (preg_match("/^(\d+)x(|full)$/", $input, $mat)) {
                $season = $mat[1];
                $episode = 99999;
                break;
            }
        case true:
            $mat = [];
            if (preg_match("/^x(\d+|\d+\.\d+)v(\d+)$/", $input, $mat)) {
                $season = 1;
                $episode = $mat[1];
                $version = $mat[2];
                break;
            }
        case true:
            $mat = [];
            if (preg_match("/^\d{8}$/", $input, $mat) && validateYYYYMMDD($input)) {
                $season = 0;
                $episode = $input;
                break;
            }
        case true:
            $mat = [];
            if (preg_match("/^x?(\d+|\d+\.\d+)$/", $input, $mat)) {
                $season = 1;
                $episode = $mat[1];
                break;
            }
        default:
            $success = false;
    }
    return [
        'season' => $season,
        'episode' => $episode,
        'version' => $version,
        'success' => $success
    ];
}

function episode_filter($item, $filter) {
    /*
     * NEW NOTATION (letters below symbolize numerals--do not actually type S, E, Y, M, D characters in the filter):
     * SxE = single episode
     * SxEv# = single episode with version number (use v99 instead of PROPER/REPACK)
     * YYYYMMDD = single date
     * S1xE1-S1xE2 = batch of episodes within one season
     * YYYYMMD1-YYYYMMD2 = batch of dates
     * S1xFULL = one full season
     * S1xE1-S2xE2 = batch of episodes starting in one season and ending in a later season
     * S1xE1v2-S2xE2v3 = batch of episodes starting in one season and ending in a later season, with version numbers
     */
    if ($item['episode']) {

        $filter = strtolower(preg_replace('/\s+/', '', $filter));
        if ($filter === '') {
            // no filter, accept all
            return true;
        } else {
            $passesFilter = false;
            // split the episode filter by commas and process each set
            $filterSets = explode(',', $filter);
            $filterSetsCount = count($filterSets);
            for ($i = 0; $i < $filterSetsCount; $i++) {
                // convert from old notation style to new
                if (preg_match('/\b[s]\d+/', $filterSets[$i])) {
                    $filterSets[$i] = str_replace('s', '', $filterSets[$i]);
                    if (preg_match('/\d+[e]\d+/', $filterSets[$i])) {
                        $filterSets[$i] = str_replace('e', 'x', $filterSets[$i]);
                    }
                }

                // split the filter set (ex. 3x4-4x15 into 3,4 4,15)
                if (strpos($filterSets[$i], '-') !== false) {
                    $filterPieces = explode('-', $filterSets[$i]);
                    if (isset($filterPieces[2])) {
                        writeToLog("Bad episode filter: $filter\n", 0);
                    }
                    if (isset($filterPieces[0])) {
                        $start = parseSxEvVNotation($filterPieces[0]);
                        if ($start['success']) {
                            $startSeason = $start['season'];
                            $startEpisode = $start['episode'];
                            $startEpisodeVersion = $start['version'];
                        } else {
                            writeToLog("Bad episode filter: $filter\n", 0);
                        }
                    } else {
                        $startSeason = 1;
                        $startEpisode = 1;
                        $startEpisodeVersion = '';
                    }
                    if (isset($filterPieces[1])) {
                        $stop = parseSxEvVNotation($filterPieces[1]);
                        if ($stop['success']) {
                            if ($stop['episode'] === '') {
                                $stopEpisode = 99999;
                            } else {
                                $stopEpisode = $stop['episode'];
                            }
                            if ($stop['season'] === '') {
                                $stopSeason = $startSeason;
                            } else {
                                $stopSeason = $stop['season'];
                            }
                            $stopEpisodeVersion = $stop['version'];
                        } else {
                            writeToLog("Bad episode filter: $filter\n", 0);
                        }
                    } else {
                        $stopSeason = 99999;
                        $stopEpisode = 99999;
                        $stopEpisodeVersion = '';
                    }
                    if ($startEpisode === 99999) {
                        $startEpisode = 1;
                    }
                } else {
                    // no minus, is either one episode or entire season
                    $start = parseSxEvVNotation($filterSets[$i]);
                    if ($start['success']) {
                        $startSeason = $stopSeason = $start['season'];
                        if ($start['episode'] === 99999) {
                            $startEpisode = 1;
                        } else {
                            $startEpisode = $start['episode'];
                        }
                        $stopEpisode = $start['episode'];
                        $startEpisodeVersion = $stopEpisodeVersion = $start['version'];
                    } else {
                        writeToLog("Bad episode filter: $filter\n", 0);
                    }
                }

                if (is_numeric($startSeason)) {
                    $startSeason += 0;
                }
                if (is_numeric($startEpisode)) {
                    $startEpisode += 0;
                }
                if (is_numeric($stopSeason)) {
                    $stopSeason += 0;
                }
                if (is_numeric($stopEpisode)) {
                    $stopEpisode += 0;
                }

                // check if item/range is in this filter set
                // add zeros to convert to numbers
                if (
                        ($item['seasBatStart'] >= $startSeason && $item['seasBatStart'] <= $stopSeason) &&
                        ($item['seasBatEnd'] >= $startSeason && $item['seasBatEnd'] <= $stopSeason) &&
                        ($item['episBatStart'] >= $startEpisode && $item['episBatStart'] <= $stopEpisode) &&
                        ($item['episBatEnd'] >= $startEpisode && $item['episBatEnd'] <= $stopEpisode)
                ) {
                    if ($item['itemVersion'] && ($startEpisodeVersion !== '' || $stopEpisodeVersion !== '')) {
                        if (
                                $item['seasBatEnd'] === $stopSeason &&
                                $item['episBatEnd'] === $stopEpisode &&
                                $item['itemVersion'] <= $stopEpisodeVersion
                        ) {
                            $passesFilter = true;
                        }
                    } else {
                        $passesFilter = true;
                    }
                }
            }
            return $passesFilter;
        }
    } else {
        // $item['episode'] evaluates to false; should only happen for debugMatch of 0_, 1_, and so on
        return false;
    }
}
