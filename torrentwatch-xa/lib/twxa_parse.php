<?php
/*
 * Helper functions for parsing torrent titles
 * currently part of Procedural Programming versions, will be replaced by OOP later
 * guess.php and feeds.php refer to this file
 */

$separators = '\s\.\_'; // - and () were formerly also separators but caused problems; we need - for some Season and Episode notations

function sanitizeTitle($title, $separators = '\s\.\_') {
    // cleans title of symbols, aiming to get the title down to just alphanumerics and reserved separators
    // we sanitize the title to make it easier to use Favorites and match episodes
    $sanitizeRegExPart = preg_quote('[]{}<>,_/','/'); //TODO get sanitize chars from user-defined config

    // Remove soft hyphens
    $title = str_replace("\xC2\xAD", "", $title);

    // replace with space any back-to-back sanitize chars that if taken singly would result in values getting smashed together
    //TODO add [] {} <> () to values IF they are not in the sanitize list
    $title = preg_replace("/([a-z0-9\(\)])[$sanitizeRegExPart]+([a-z0-9\(\)])/i", "$1 $2", $title);

    // remove all remaining sanitize chars
    $title = preg_replace("/[$sanitizeRegExPart]/", '', $title);

    // IMPORTANT: reduce multiple separators down to one separator (will break some matches if removed)
    $title = preg_replace("/([$separators])+/", "$1", $title);

    // trim beginning and ending spaces
    $title = trim($title);

    return $title;
}

function simplifyTitle($title, $separators = '\s\.\_') {
    // combines all the title processing functions

    $title = sanitizeTitle($title);

    //TODO MUST normalize H264 and X264 now so that users get trained to use normalized versions
    $title = preg_replace("/x[$separators\-]+264/i", 'x264', $title); // note the separator chars PLUS - char
    $title = preg_replace("/h[$separators\-]+264/i", 'h264', $title); // not sure why, but can't use | and $1 with x|h

    //TODO Maybe replace period-style separators with spaces (unless they are sanitized)
    //TODO Maybe pad parentheses with outside spaces (unless they are sanitized)
    //TODO Maybe remove audio codecs

    return $title;
}

function detectResolution($title, $separators = '\s\.\_') {
    $SdRegEx = "/(480[ip]?)/i";
    //TODO Add 576p/576i Enhanced Def. TV
    $HdRegEx = "/(720[ip]?|1080[ip]?)[$separators]*[^x]/i"; // designed for future HD resolutions, the [^x] blocks long matches
    $longSdRegEx = "/720x(480[ip]?)/i";
    $longHdRegEx = "/1280\s*x\s*(720[ip]?)|1920[$separators]*x[$separators]*(1080[ip]?)/i"; // designed for future HD resolutions
    $resolution = "";
    $detectedQualities = [];
    $matches = [];

    // check for long resolutions first, since the short ones will also partially match the long
    // removing matches from the title makes it easier to detect season and episode later
    if(preg_match($longHdRegEx, $title, $matches)) {
        $detectedQualities = ["HD","HDTV"];
        $title = preg_replace($longHdRegEx, "", $title);
        $title = preg_replace("/HD(TV)?/i", "", $title); // sadly, "(HD collection)" will become "( collection)"
    }
    else if(preg_match($longSdRegEx, $title, $matches)) {
        $detectedQualities = ["SD","SDTV"];
        $title = preg_replace($longSdRegEx, "", $title);
        $title = preg_replace("/SD(TV)?/i", "", $title);
    }
    else if(preg_match($HdRegEx, $title, $matches)) {
        $detectedQualities = ["HD","HDTV"];
        $title = preg_replace($HdRegEx, "", $title);
        $title = preg_replace("/HD(TV)?/i", "", $title);
    }
    else if(preg_match($SdRegEx, $title, $matches)) {
        $detectedQualities = ["SD","SDTV"];
        $title = preg_replace($SdRegEx, "", $title);
        $title = preg_replace("/SD(TV)?/i", "", $title);
    }

    for($i = 1; $i < count($matches); $i++) {
        if($matches[$i] != '') {
            $resolution = $matches[$i];
            if(preg_match("/\d+$/", $resolution)) {
                $resolution = $resolution . 'p'; // default to p if no i or p is specified
            }
            break;
        }
    }

    $detectedQualities[] = $resolution;

    return ['parsedTitle' => sanitizeTitle($title), 'detectedQualities' => $detectedQualities];
}

function detectQualities($title, $separators = '\s\.\_') {
    $qualitiesFromResolution = detectResolution($title, $separators);

    // search for more quality matches and append them to detectedQualities
    $title = $qualitiesFromResolution['parsedTitle'];
    $detectedQualities = $qualitiesFromResolution['detectedQualities'];

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
        'HR.PDTV',
        'PDTV',
        'SatRip',
        'WebRip',
        'DVDR',
        'DVDRip',
        'DVDScr',
        'XviDVD',
        'DSR',
        'SVCD',
        'WEB-DL',
        'WEB.DL',
        'iTunes',
        // codecs--could be high or low quality, who knows?
        'XviD',
        'x264',
        'H264',
        // typically low quality
        'VHSRip',
        'TELESYNC'
        ];

    foreach ($qualityList as $qualityListItem) {
        $qualityListItemRegExPart = preg_quote($qualityListItem, '/');
        if(preg_match("/\b$qualityListItemRegExPart\b/i", $title)) { // must use boundaries because SxE notation can collide with x264
            $detectedQualities[] = $qualityListItem;
            // cascade down through, removing immediately-surrouding dashes
            $title = preg_replace("/\-+$qualityListItemRegExPart\-+/i", '', $title);
            $title = preg_replace("/\-+$qualityListItemRegExPart\b/i", '', $title);
            $title = preg_replace("/\b$qualityListItemRegExPart\-+/i", '', $title);            
            $title = preg_replace("/\b$qualityListItemRegExPart\b/i", '', $title);
        }
    }

    return ['parsedTitle' => $title, 'detectedQualities' => $detectedQualities];
}



function detectSeasonAndEpisode($title, $qualitiesCount = 0, $separators = '\s\.\_') {
    // $qualitiesCount is a param because some manga use "Vol. ##" notation

    $detectedSeasonBatchStart = '';
    $detectedSeasonBatchEnd = '';
    $detectedEpisodeBatchStart = '';
    $detectedEpisodeBatchEnd = '';
    $matches = [];

    // IMPORTANT NOTES:
    // treat anime notation as Season 1
    // treat date-based episodes as Season 0
    // because of PHP matching order, (Season|Seas\.|Seas|Se\.|Se|S\.|S) works but (S|Se|Se\.|Seas|Seas\.|Season) will match S and move on

    // GOALS:
    // handle Special and OVA episodes
    // handle PROPER and REPACK episodes
    // use short circuits to reduce overhead

    // MATCHES STILL IN PROGRESS, NOT DONE OR NOT TESTED ENOUGH:
    // ###.#v3 (anime episode number, version 3)
    // YYYY (collides with anime episode and YYMM)
    // YYYY MM (no DD)
    // '96 (abbreviated year)
    // 01 of 20 1978
    // 4x04 (2014)
    // The.Haunting.Of.S04.Revealed.Special (Season 4, Special)
    // "DBZ Abridged Episodes 1-44 + Movies (TFS)" (big batch)
    // ( 5th Season )
    // CFL.2014.RS.Week18.(25 oct).BC.Lions.v.WPG.Blue.Bombers.504p
    // Batch XX-XX
    // Movie 01 (prevent from matching as 1x1)
    // Film 01 (prevent from matching as 1x1)
    // 27th October 2014
    // Serie.A.2014.Day08(26 oct).Cesena.v.Inter.400p
    // Japanese Unicode \x{3010} left lenticular bracket, \x{3011} right lenticular bracket
    // Japanese Unicode \x{7B2C} wa
    
    if(strpbrk($title, "0123456789")) {
        // found a numeral (can't have an episode number without at least one numeral)


        if(preg_match_all("/(Season|Seas\.|Seas|Se\.|Se|S\.|S)[$separators]?(\d+\.\d|\d+)[$separators]?[\,\-]?[$separators]?(Episode|Epis\.|Epis|Epi\.|Epi|Ep\.|Ep|E)[$separators]?(\d+\.\d|\d+)/i", $title, $matches, PREG_SET_ORDER)) {
            // search for explicit S##.#E###.#
            // Example:
            // S01.5E10.5
            // Season 1 Episode 10
            // Se.2.Ep.5
            // Seas 2, Epis 3
            // S3 - E6
            if(count($matches) > 1) {
                // more than one S##.#E###.# found--probably a range
                //TODO handle range
            }
            else {
                $detectedSeasonBatchEnd = $detectedSeasonBatchStart = $matches[0][2];
                $detectedEpisodeBatchEnd = $detectedEpisodeBatchStart = $matches[0][4];
            }
        }
        else if(preg_match_all("/(\d{4})\.(\d+\.\d|\d+)[$separators]?[xX][$separators]?(\d+\.\d|\d+)/", $title, $matches, PREG_SET_ORDER) && preg_match("/^[12]+/", $matches[0][1])) {
            // short-circuit YYYY.SSxEE so that "Doctor.Who.2005.8x10.In" doesn't match later
            if(count($matches) > 1) {
                // more than one YYYY.SSxEE found--probably a range
                //TODO handle range
            }
            else {
                $detectedSeasonBatchEnd = $detectedSeasonBatchStart = $matches[0][2];
                $detectedEpisodeBatchEnd = $detectedEpisodeBatchStart = $matches[0][3];
            }
        }
        else if(preg_match_all("/(\d+\.\d|\d+)[$separators]?[xX][$separators]?(\d+\.\d|\d+)[$separators]/", $title, $matches, PREG_SET_ORDER)) {
            // search for explicit SSxEE notation (but only if Season is present, x264 may match otherwise)
            if(count($matches) > 1) {
                // more than one SSxEE found--probably a range
                //TODO handle range
            }
            else {
                $detectedSeasonBatchEnd = $detectedSeasonBatchStart = $matches[0][1];
                $detectedEpisodeBatchEnd = $detectedEpisodeBatchStart = $matches[0][2];
            }
        }
        else if(preg_match_all("/(\d+\.\d|\d+)[$separators]?(OF|Of|of)[$separators]?(\d+\.\d|\d+)/", $title, $matches, PREG_SET_ORDER)) {
            // search for ## of ##
            if(count($matches) > 1) {
                // more than one ## of ## found--probably a range
                //TODO handle range
            }
            else {
                $detectedSeasonBatchEnd = $detectedSeasonBatchStart = 1; // If no mention of Season, assume Season 1
                $detectedEpisodeBatchEnd = $detectedEpisodeBatchStart = $matches[0][1];
            }
        }
        else if(preg_match_all("/(\d{4})[$separators](\d{1,2})[$separators](\d{1,2})/", $title, $matches, PREG_SET_ORDER)) {
            // search for explicit YYYY MM DD or YYYY M D but not YYYYMMDD
            if(count($matches) > 1) {
                // more than one YYYY MM DD found--probably a range
                //TODO handle range of dates
            }
            else {
                //TODO make sure YYYY MM and DD make sense
                $detectedSeasonBatchEnd = $detectedSeasonBatchStart = 0; // date notation gets Season 0
                $detectedEpisodeBatchEnd = $detectedEpisodeBatchStart = $matches[0][1] . $matches[0][2] . $matches[0][3];
            }
        }
        else if(preg_match_all("/(\d{4})(\d{2})(\d{2})/", $title, $matches, PREG_SET_ORDER) && preg_match("/^[12]+/", $matches[0][1])) {
            // search for explicit YYYYMMDD (must have two-digit MM and DD or could be MDD or MMD)
            // check that YYYY begins with 1 or 2 (no year 3000!)
            if(count($matches) > 1) {
                // more than one YYYY MM DD found--probably a range
                //TODO handle range of dates
            }
            else {
                //TODO make sure YYYY MM and DD make sense
                $detectedSeasonBatchEnd = $detectedSeasonBatchStart = 0; // date notation gets Season 0
                $detectedEpisodeBatchEnd = $detectedEpisodeBatchStart = $matches[0][1] . $matches[0][2] . $matches[0][3];
            }
        }
        else if(preg_match_all("/(\d+\.\d|\d+)[$separators](Episode|Epis\.|Epis|Epi\.|Epi|Ep\.|Ep|E)[$separators]?(\d+\.\d|\d+)[\(\)$separators]/i", $title, $matches, PREG_SET_ORDER)) {
            // search for ## Episode ## but not ##E## (no mention of Season, as that would have matched earlier, must have space to block checksum matches)
            if(count($matches) > 1) {
                // more than one found--probably a range
                //TODO handle range of Episodes
            }
            else {
                $detectedSeasonBatchEnd = $detectedSeasonBatchStart = $matches[0][1];
                $detectedEpisodeBatchEnd = $detectedEpisodeBatchStart = $matches[0][3];
            }
        }
        else if(preg_match_all("/(\d+\.\d|\d+)[$separators](Episode|Epis\.|Epis|Epi\.|Epi|Ep\.|Ep|E)[$separators]?(\d+\.\d|\d+)$/i", $title, $matches, PREG_SET_ORDER)) {
            // search for ## Episode ## but not ##E## at very end of title
            if(count($matches) > 1) {
                // more than one found--probably a range
                //TODO handle range of Episodes
            }
            else {
                $detectedSeasonBatchEnd = $detectedSeasonBatchStart = $matches[0][1];
                $detectedEpisodeBatchEnd = $detectedEpisodeBatchStart = $matches[0][3];
            }
        }
        else if(preg_match_all("/[\(\)$separators](Episode|Epis\.|Epis|Epi\.|Epi|Ep\.|Ep|E)[$separators]?(\d+\.\d|\d+)[\(\)$separators]?/i", $title, $matches, PREG_SET_ORDER)) {
            // search for Episode ## or (no mention of Season, as that would have matched earlier)
            if(count($matches) > 1) {
                // more than one found--probably a range
                //TODO handle range of Episodes
            }
            else {
                $detectedSeasonBatchEnd = $detectedSeasonBatchStart = 1; // anime episode notation gets Season 1
                $detectedEpisodeBatchEnd = $detectedEpisodeBatchStart = $matches[0][2];
            }
        }
        else if(preg_match_all("/[\(\)$separators](Season|Seas\.|Seas|Se\.|Se|S\.|S)[$separators]?(\d+\.\d|\d+)[\(\)$separators]?/i", $title, $matches, PREG_SET_ORDER)) {
            // search for Season ## or Season ## Complete (no mention of Episode, as that would have matched earlier)
            if(count($matches) > 1) {
                // more than one found--probably a range
                //TODO handle range of Seasons
            }
            else {
                $detectedSeasonBatchEnd = $detectedSeasonBatchStart = $matches[0][2];
                $detectedEpisodeBatchStart = 1;
                $detectedEpisodeBatchEnd = 0;
            }
        }
        else if(preg_match_all("/(\d{1,3}\.\d|\d{1,3})[$separators]?(through|thru|to)[$separators]?(\d{1,3}\.\d|\d{1,3})/i", $title, $matches, PREG_SET_ORDER)) {
            // search for ###.# to ###.# (assume episodes here, because search for multiple seasons happened earlier)
            //TODO fix it so that 2005.08 to 50 doesn't match
            if(count($matches) > 1) {
                // more than one found--not likely to ever happen, since this would be two sets of ranges
                //TODO handle range of range of episodes
            }
            else {
                $detectedSeasonBatchEnd = $detectedSeasonBatchStart = 1; // anime episode notation gets Season 1
                $detectedEpisodeBatchStart = $matches[0][1];
                $detectedEpisodeBatchEnd = $matches[0][3];
            }
        }
        else if(preg_match_all("/[\(\)$separators](\d{1,3}\.\d|\d{1,3})[$separators]?\-[$separators]?(\d{1,3}\.\d|\d{1,3})[\(\)$separators]/", $title, $matches, PREG_SET_ORDER)) {
            // search for ## - ##, is it SS - EE or EE - EE?
            // TODO fix the edges!
            if(count($matches) > 1) {
                // more than one found--not likely to ever happen, since this would be two sets of ranges
                //TODO handle range of range of episodes
            }
            else {
                if($matches[0][1] == 1) {
                    // probably EE - EE, since people rarely refer to Season 1 without mentioning Season|Seas|Sea|Se|S
                    $detectedSeasonBatchEnd = $detectedSeasonBatchStart = 1; // anime episode notation gets Season 1
                    $detectedEpisodeBatchStart = $matches[0][1];
                    $detectedEpisodeBatchEnd = $matches[0][2];
                }
                else if(preg_match("/\d\d/", $matches[0][1])) {
                    // if it's more than 1 digit, probably not a season but EE - EE, such as 09 - 11
                    $detectedSeasonBatchEnd = $detectedSeasonBatchStart = 1; // anime episode notation gets Season 1
                    $detectedEpisodeBatchStart = $matches[0][1];
                    $detectedEpisodeBatchEnd = $matches[0][2];
                }
                else {
                    // assume S - EE
                    $detectedSeasonBatchEnd = $detectedSeasonBatchStart = $matches[0][1];
                    $detectedEpisodeBatchEnd = $detectedEpisodeBatchStart = $matches[0][2];
                }
            }
        }
        else if(preg_match_all("/(Volume|Vol\.|Vol)[$separators]?(\d{1,3}\.\d|\d{1,3})/i", $title, $matches, PREG_SET_ORDER)) {
            // search for Vol ##.# with at most tenths place AND video qualities (otherwise assume it's manga)
            // unlike to have a range of Volumes
            if($qualitiesCount > 0) {
                // if video qualities are detected
                $detectedSeasonBatchEnd = $detectedSeasonBatchStart = $matches[0][2]; // treat Volume like a Season
                $detectedEpisodeBatchStart = 1;
                $detectedEpisodeBatchEnd = 0;
            }
        }
        //TODO enable Japanese UTF-8 searches
        /* if(preg_match_all("/\x{7B2C}(\d+\.\d|\d+)/u", $title, $matches, PREG_SET_ORDER)) {
            // search for explicit Japanese episode numbering
            if(count($matches) > 1) {
                // more than one episode found--probably a range
                //TODO handle range
            }
            else {
                $detectedSeasonBatchEnd = $detectedSeasonBatchStart = 1; // anime episode notation gets Season 1
                $detectedEpisodeBatchEnd = $detectedEpisodeBatchStart = $matches[0][1];                
            }
        } */
        // SAVE THE BELOW FOR THE END, BECAUSE SINGLE NUMBERS CAN MATCH SO MANY LONGER PATTERNS
        else if(preg_match_all("/\D[$separators]?\-[$separators]?(\d{1,3}\.\d|\d{1,3})/", $title, $matches, PREG_SET_ORDER)) {
            // search for - ##, not ## - ## (which is matched earlier)
            if(count($matches) > 1) {
                // more than one found--not likely to ever happen, since this should be matched as a range earlier
                //TODO handle range of episodes
            }
            else {
                $detectedSeasonBatchEnd = $detectedSeasonBatchStart = 1; // anime episode notation gets Season 1
                $detectedEpisodeBatchEnd = $detectedEpisodeBatchStart = $matches[0][1];
            }
        }
        else if(preg_match_all("/[\(\)$separators](\d{1,3}\.\d|\d{1,3})[\(\)$separators]/", $title, $matches, PREG_SET_ORDER)) {
            // search for 1 to 3-digit numbers with at most tenths place (last resort, otherwise may match earlier, longer strings)
            if(count($matches) > 1) {
                // more than one ###.# found--probably a range
                //TODO handle range of episodes
            }
            else {
                //TODO make sure this is not part of a date!
                $detectedSeasonBatchEnd = $detectedSeasonBatchStart = 1; // anime episode notation gets Season 1
                $detectedEpisodeBatchEnd = $detectedEpisodeBatchStart = $matches[0][1];
            }
        }
        else if(preg_match_all("/[\(\)$separators](\d{1,3}\.\d|\d{1,3})$/", $title, $matches, PREG_SET_ORDER)) {
            // search for 1 to 3-digit numbers with at most tenths place (last resort, otherwise may match earlier, longer strings)
            // searches at the very end of the title
            if(count($matches) > 1) {
                // more than one ###.# found--probably a range
                //TODO handle range of episodes
            }
            else {
                //TODO make sure this is not part of a date!
                $detectedSeasonBatchEnd = $detectedSeasonBatchStart = 1; // anime episode notation gets Season 1
                $detectedEpisodeBatchEnd = $detectedEpisodeBatchStart = $matches[0][1];
            }
        }
        // HANDLE 4-DIGIT NUMBERS BY CHECKING THAT THEY ARE NOT YYYY OR MMDD
        else if(preg_match_all("/[\(\)$separators](\d{4}\.\d|\d{4})[\(\)$separators]/", $title, $matches, PREG_SET_ORDER)) {
            // search for 4-digit numbers with at most tenths place (very last resort, otherwise may short-circuit earlier matches)
            if(count($matches) > 1) {
                // more than one ####.# found--probably a range
                //TODO handle range of dates or episodes
            }
            else {
                // invention of moving pictures on film was in 1896
                if($matches[0][1] > 1895) {
                    // probably YYYY
                    $detectedSeasonBatchEnd = $detectedSeasonBatchStart = 0; // date-notation gets Season 0
                    $detectedEpisodeBatchEnd = $detectedEpisodeBatchStart = $matches[0][1];
                }
                else if(substr($matches[0][1], 0, 1) == '0') {
                    // probably MMDD
                    $detectedSeasonBatchEnd = $detectedSeasonBatchStart = 0; // date-notation gets Season 0
                    $detectedEpisodeBatchEnd = $detectedEpisodeBatchStart = $matches[0][1];                    
                }
                else {
                    // probably anime episode between 1000 and 1895, could be MMDD
                    $detectedSeasonBatchEnd = $detectedSeasonBatchStart = 1; // anime episode notation gets Season 1
                    $detectedEpisodeBatchEnd = $detectedEpisodeBatchStart = $matches[0][1];
                }
            }
        }
        
        // trim off leading zeroes
        if($detectedEpisodeBatchEnd != '') {
            $detectedEpisodeBatchEnd += 0;
        }
        if($detectedEpisodeBatchStart != '') {
            $detectedEpisodeBatchStart += 0;
        }
        if($detectedSeasonBatchEnd != '') {
            $detectedSeasonBatchEnd += 0;
        }
        if($detectedSeasonBatchStart != '') {
            $detectedSeasonBatchStart +=0;
        }
    } //END if(strpbrk($title, "0123456789"))

    return ['detectedSeasonBatchStart' => $detectedSeasonBatchStart, 'detectedSeasonBatchEnd' => $detectedSeasonBatchEnd, 'detectedEpisodeBatchStart' => $detectedEpisodeBatchStart, 'detectedEpisodeBatchEnd' => $detectedEpisodeBatchEnd];
}
//print_r(detectSeasonAndEpisode("Anime-Koi Ai Tenchi Muyou! - 16 h264-720p"));