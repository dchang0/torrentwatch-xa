<?php

// functions for parsing torrent titles
// twxa_feed.php calls this file

$seps = '\s\.\_'; // separator chars: - and () were formerly also separators but caused problems; we need - for some Season and Episode notations

// shared word list constants for regex alternation groups
// season words include languages and abbreviations
define('SEASON_WORDS', 'Season|Saison|Seizoen|Sezona|\bSeas\.|\bSeas|\bSais\.|\bSea\.|\bSea|\bSe\.|\bSe|\bS\.|\bS|Temporada|\bTemp\.|\bTemp|\bT\.|\bT');
// episode words include languages and abbreviations
define('EPISODE_WORDS', 'Episode|Episodes|Epizode|Epizodes|\bEpis\.|\bEpis|\bEpi\.|\bEpi|\bEps\.|\bEps|\bEp\.|\bEp|\bE\.|\bE');
// volume words include languages and abbreviations
define('VOLUME_WORDS', 'Volumes|Volume|Volumen|Volumens|Vols\.|Vols|Vol\.|Vol|\bV\.|\bV');
// chapter words include languages and abbreviations
define('CHAPTER_WORDS', 'Chapters|Chapter|Capitulos|Capitulo|Chapitres|Chapitre|\bChap\.|\bChap|\bCh\.|\bCh|\bC\.|\bC');
// part words
define('PART_WORDS', 'Parts|Part|Pt\.|Pt');

// media type constants
define('MEDTYP_UNKNOWN', 0);
define('MEDTYP_VIDEO', 1);
define('MEDTYP_AUDIO', 2);
define('MEDTYP_PRINT', 4);

// numbering sequence constants
define('NUMSEQ_NONE', 0);
define('NUMSEQ_SEASON_EPISODE', 1);
define('NUMSEQ_DATE', 2);
define('NUMSEQ_SEASON_VOLUME', 4);
define('NUMSEQ_PREVIEW', 8);
define('NUMSEQ_SPECIAL', 16);
define('NUMSEQ_OVA', 32);
define('NUMSEQ_MOVIE', 64);
define('NUMSEQ_DISC_PART', 128);

// known valid vertical resolution heights (used by detectResolution)
define('RESOLUTION_HEIGHTS', [240, 360, 400, 480, 544, 576, 720, 768, 800, 900, 1024, 1050, 1080, 1200, 1440, 2160]);

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

function convertMonthToMM($month) {
    $MM = null;
    switch (strtolower($month)) {
        case "january" :
        case "jan" :
            $MM = "01";
            break;
        case "february" :
        case "feb" :
            $MM = "02";
            break;
        case "march" :
        case "mar" :
            $MM = "03";
            break;
        case "april" :
        case "apr" :
            $MM = "04";
            break;
        case "may" :
            $MM = "05";
            break;
        case "june" :
        case "jun" :
            $MM = "06";
            break;
        case "july" :
        case "jul" :
            $MM = "07";
            break;
        case "august" :
        case "aug" :
            $MM = "08";
            break;
        case "september" :
        case "sept" :
        case "sep" :
            $MM = "09";
            break;
        case "october" :
        case "oct" :
            $MM = "10";
            break;
        case "november" :
        case "nov" :
            $MM = "11";
            break;
        case "december" :
        case "dec" :
            $MM = "12";
    }
    return $MM;
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

function resolveAspectRatio($w, $h) {
    $ratios = [[16,9], [4,3], [16,10], [3,2], [5,4], [19,10], [256,135], [21,9]];
    foreach ($ratios as list($numer, $denom)) {
        if ($w * $denom == $h * $numer) {
            return true;
        }
    }
    return false;
}

function qualityLabelFromHeight($h) {
    if ($h == 720 || $h == 1080 || $h == 1440 || $h == 2160) {
        return ["HD", "HDTV"];
    } else if ($h == 576) {
        return ["ED", "EDTV"];
    } else if ($h == 240 || $h == 360 || $h == 480) {
        return ["SD", "SDTV"];
    }
    return [];
}

function detectResolution($ti, $seps = '\s\.\_') {
    // patterns ordered: ###p|i (~65%), BD### (~4%), BD WxH (~1%), WxH (~20%)
    // BD patterns before WxH so BD1280x720p isn't caught by plain WxH regex
    $hRegEx = "/\b(\d{3,4})[iIpP]\b/"; // ###p or ###i
    $bDHRegEx = "/\bBD(\d{3,4})([iIpP]?)\b/"; // BD###
    $bDWxHRegEx = "/\bBD(\d{3,4})[$seps]*[xX][$seps]*(\d{3,4})[iIpP]?\b/"; // BD####x####p
    $wByHRegEx = "/(\d{3,4})[$seps]*[xX][$seps]*((\d{3,4})[iIpP]?)/"; // ####x####p
    $resolution = "";
    $matchedResolution = "";
    $verticalLines = "";
    $detQualities = [];
    $matches = [];

    // 1. ###p or ###i (most common)
    if ($resolution === "" && preg_match($hRegEx, $ti, $matches)) {
        $matchedResolution = $matches[0];
        $resolution = strtolower($matchedResolution);
        $verticalLines = $matches[1];
    }

    // 2. BD### (historical)
    if ($resolution === "" && preg_match($bDHRegEx, $ti, $matches)) {
        $h = (int) $matches[1];
        if (in_array($h, RESOLUTION_HEIGHTS)) {
            $matchedResolution = $matches[0];
            $verticalLines = $matches[1];
            if ($matches[2] === "") {
                $resolution = $matches[1] . "p";
            } else {
                $resolution = strtolower($matches[1] . $matches[2]);
            }
        }
    }

    // 3. BD####x####p (rare, the former BD1280x720p bug)
    if ($resolution === "" && preg_match($bDWxHRegEx, $ti, $matches)) {
        $h = (int) $matches[2];
        if (in_array($h, RESOLUTION_HEIGHTS) || resolveAspectRatio((int) $matches[1], $h)) {
            $matchedResolution = $matches[0];
            $verticalLines = $matches[2];
            $resolution = $matches[2] . "p";
        }
    }

    // 4. ####x####p (common in raws and non-English groups)
    if ($resolution === "" && preg_match($wByHRegEx, $ti, $matches)) {
        $h = (int) $matches[3];
        if (in_array($h, RESOLUTION_HEIGHTS) || resolveAspectRatio((int) $matches[1], $h)) {
            $matchedResolution = $matches[0];
            $resolution = strtolower($matches[2]);
            $verticalLines = $matches[3];
            if ($resolution == $verticalLines) {
                $resolution .= 'p'; // default to p if no i or p is specified
            }
        }
    }

    $ti = str_replace($matchedResolution, "", $ti);
    $detQualities = qualityLabelFromHeight((int) $verticalLines);
    if ($verticalLines == 576) {
        $ti = preg_replace("/SD(TV)?/i", "", $ti); // remove SD also (ED will be removed by detectQualities())
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
        'HR\.HDTV',
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
        'WEB\.DL',
        'HTML5',
        'iTunes',
        // codecs--could be high or low quality, who knows?
        'XviD',
        'x264-w4f',
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
        'BT\.709',
        'BT\.601',
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
        'EAC3\ 2\.0',
        'EAC3',
        'AC3\ 2\.0',
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
        '5\.1ch',
        '5\.1',
        '2ch'
    ];
    foreach ($audioCodecList as $audioCodecListItem) {
        if (preg_match("/\b" . $audioCodecListItem . "\b/i", $ti)) {
            $detAudioCodecs[] = $audioCodecListItem;
            $ti = preg_replace("/\b" . $audioCodecListItem . "\b/i", "", $ti);
            // remove dashes that were surrounding the codec
            $ti = preg_replace("/-([.\s])/", "$1", $ti);
            $ti = preg_replace("/([.\s])-/", "$1", $ti);
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
    // match convention codes like (C72) or &#40;C72&#41; (HTML entities)
    $conventionCodeRe = "/^(\(C\d\d\)|&#40;C\d\d&#41;)[" . $seps . "]*/";
    if (preg_match($conventionCodeRe, $ti, $mat)) {
        $ti = preg_replace($conventionCodeRe, "", $ti);
        $rmCrewName = $mat[0];
    } else {
        $crewNameList = [
            "Doujinshi (C91)",
            "Al3asq",
            "F4A-MDS",
            "blad761",
            "bonkai77",
            "Ch4" // Channel 4 documentaries
        ];
        foreach ($crewNameList as $crewName) {
            $quotedCrewName = preg_quote($crewName);
            if (preg_match("/^" . $quotedCrewName . "[" . $seps . "]*/", $ti, $mat)) {
                // found it at the beginning, now remove it to be re-added later
                $ti = preg_replace("/" . $quotedCrewName . "[" . $seps . "]*/", "", $ti);
                $rmCrewName = $mat[0];
                break;
            }
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
    $detQualitiesJoined = implode(' ', $detQualitiesOutput['detectedQualities']);
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
        'mediaType' => $detItemOutput['mediaType'],
        'itemVersion' => $detItemOutput['itemVersion'],
        'numberSequence' => $detItemOutput['numberSequence'],
        'debugMatch' => $detItemOutput['debugMatch']
    ];
}

function detectItem($ti, $wereQualitiesDetected = false, $seps = '\s\.\_') {
    // our numbering style is 1x2v2-2x3v3
    // $wereQualitiesDetected is a param because some manga use "Vol. ##" notation
    // IMPORTANT NOTES:
    // treat anime notation as Season 1
    // treat date-based episodes as Season 0 EXCEPT...
    // ...when YYYY-##, use year as the Season and ## as the Episode
    // because of PHP left-to-right matching order, (Season|Seas|Se|S) works but (S|Se|Seas|Season) will match S and move on
    $ti = html_entity_decode($ti, ENT_QUOTES);
    $ti = rawurldecode($ti);

    // bucket the matches of all numbers of different lengths
    $matNums = [];
    preg_match_all("/(\d+)/u", $ti, $matNums, \PREG_SET_ORDER); // can't initialize $matNums here due to isset tests later
    // is there at least one number? can't have an episode otherwise (except in case of PV preview episode)
    $numbersDetected = count($matNums);
    if (isset($matNums[0])) {
        // helper: try a match level and annotate matFnd on success
        $tryLevel = function ($level, $ti, $seps, $wereQualitiesDetected, $numbersDetected) {
            $func = "matchTitle{$level}_";
            $result = $func($ti, $seps, $wereQualitiesDetected);
            if ($result['matFnd'] !== "{$level}_") {
                if ($numbersDetected !== $level) {
                    $result['matFnd'] = $numbersDetected . "_ (" . $result['matFnd'] . ")";
                }
                return $result;
            }
            return null;
        };
        switch ($numbersDetected) {
            case 8:
            case 7:
            case 6:
                $result = $tryLevel(6, $ti, $seps, $wereQualitiesDetected, $numbersDetected);
                if ($result) break;
            case 5:
                $result = $tryLevel(5, $ti, $seps, $wereQualitiesDetected, $numbersDetected);
                if ($result) break;
            case 4:
                $result = $tryLevel(4, $ti, $seps, $wereQualitiesDetected, $numbersDetected);
                if ($result) break;
            case 3:
                $result = $tryLevel(3, $ti, $seps, $wereQualitiesDetected, $numbersDetected);
                if ($result) break;
            case 2:
                $result = $tryLevel(2, $ti, $seps, $wereQualitiesDetected, $numbersDetected);
                if ($result) break;
            case 1:
                $result = $tryLevel(1, $ti, $seps, $wereQualitiesDetected, $numbersDetected);
                if ($result) break;
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

                // check if item/range overlaps with this filter set
                // use overlap check so that batch items are not wrongly blocked before
                // checkItemNumberingMatchesFavorite() can evaluate the Ignore Batches setting
                if (
                        ($item['seasBatStart'] <= $stopSeason && $item['seasBatEnd'] >= $startSeason) &&
                        ($item['episBatStart'] <= $stopEpisode && $item['episBatEnd'] >= $startEpisode)
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

function isWordSeason($word) {
    return (bool) preg_match("/^(?:" . SEASON_WORDS . ")$/i", $word);
}

function isWordEpisode($word) {
    return (bool) preg_match("/^(?:" . EPISODE_WORDS . ")$/i", $word);
}