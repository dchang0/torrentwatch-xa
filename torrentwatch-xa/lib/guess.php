<?php
require_once('twxa_parse.php');

// This file purposefully over-commented to help with rewrite of season and episode guessing engine

/* OLD guess_match(), will be removed
function guess_match($title, $normalize = FALSE) {

    // Episode regex
    $epi = '/[_.\s\(]';  // open regex and start with _ , . ( or space
    $epi.= '('; // open grouping
    $epi.='S\d+[_.\s]?EP? ?\d+(?:-EP? ?\d+)?' . '|';  // S12E1 or S12EP1-EP2
    $epi.='S\d+[_.\s]?-?[_.\s]?\d+' . '|'; // S12 - 001
    $epi.='S\d\d?' . '|'; // Full season S03
    $epi.='\d{1,2}x#?S?(\d+|special)(?:-\d+)?' . '|';  // 1x23 or 1x23-24
    $epi.='\d+[_.\s]?of[_.\s]?\d+' . '|';  // 03of18
    $epi.='Season[_.\s]?\d+,?[_.\s]?Episode[_.\s]?\d+' . '|'; // Season 4, episode 15
    $epi.='Season[_.\s]?\d\d?' . '|'; // Full Season: Season1 or Season 02
    $epi.='0?\d{3}[_.\s]' . '|'; // 306 or 0306
    $epi.='Pa?r?t[_.\s]?\d+[_.\s]' . '|';
    $epi.='EP?(?:PS[_.\s]?)?\d+(?:-\d+)?' . '|'; // E137 or EP137 or EPS1-23
    $epi.='\d{1,2}[-.]\d{1,2}[-.x]\d{2,4}[_.\s]' . '|'; // 23-8-2007 or 07.23.2008 or 07-23-09
    $epi.='\d{4}[-.x]\d{1,2}[-.x]\d{1,2}[_.\s]' . '|'; // 2007-8-23, 2010x03.12 or 2008.23.7
    $epi.='\d{4}[-.x]E?\d{1,2}[^\d]([.-x]All)?' . '|'; // 2007-8, 2010x03 or 2008.23
    $epi.='\d{8}[_.\s]' . '|';   // 20082306 etc
    $epi.='\d{1,4}[_.\s]'; // any integer from 1 to 4 digits, for anime episode numbering without season
    $epi.=')/i'; // close grouping, close regex, case-insensitive

    // Quality regex
    $quality = '/[_. -(](DVB ' . '|';
    $quality.='DSRIP' . '|';
    $quality.='DVBRip' . '|';
    $quality.='BRRip' . '|';
    $quality.='BluRay' . '|';
    $quality.='DVDR' . '|';
    $quality.='DVDRip' . '|';
    $quality.='DVDScr' . '|';
    $quality.='XviDVD' . '|';
    $quality.='DSR' . '|';
    $quality.='HDTVRip' . '|';
    $quality.='VHSRiP' . '|';
    $quality.='BDRip' . '|';
    $quality.='TELESYNC' . '|';
    $quality.='HR.HDTV' . '|';
    $quality.='iTunes' . '|';
    $quality.='HDTV' . '|';
    $quality.='XviD' . '|';
    $quality.='x264' . '|';
    $quality.='H264' . '|';
    $quality.='H 264' . '|';
    $quality.='HR.PDTV' . '|';
    $quality.='PDTV' . '|';
    $quality.='SatRip' . '|';
    $quality.='SVCD' . '|';
    $quality.='TVRip' . '|';
    $quality.='TVCap' . '|';
    $quality.='WebRip' . '|';
    $quality.='WEB-DL' . '|';
    $quality.='WEB.DL' . '|';
    $quality.='720p' . '|';
    $quality.='1080i' . '|';
    $quality.='1080p)[_. -)]/i';

    $title = simplifyTitle($title);

    if (preg_match($epi, $title, $match)) {
        $episode_guess = trim($match[1], ' .');
        // $key_guess is the title guess
        $key_guess = trim(preg_replace("/([^-\(\.]+)[\. ]*(?:[_.\s]-[_.\s].*)?(:?[_.\s]\(.+)?" .
                        $episode_guess . "(.*$)/", '\1', $title), ' .');
    } elseif (preg_match($quality, $title, $match)) {
        $quality_guess = trim($match[1], ' .');
        $key_guess = trim(preg_replace("/([^-\(\.]+)[\. ]*(?:[_.\s]-[_.\s].*)?(:?[_.\s]\(.+)?" .
                        $quality_guess . "(.*$)/", '\1', $title), ' .');
        $episode_guess = "noShow";
    } else {
        return False;
    }

    if (preg_match($quality, $title, $qregs)) {
        $data_guess = str_replace("'", "&#39;", trim($qregs[1]));
    } else {
        $data_guess = '';
    }

    if ($normalize == TRUE) {
        // Convert . and _ to spaces, and trim result
        $from = "._";
        $to = "  ";
        $key_guess = trim(strtr($key_guess, $from, $to));
        $data_guess = trim(strtr($data_guess, $from, $to));
        $episode_guess = trim(strtr($episode_guess, $from, $to));

        // Standardize episode output to SSxEE, strip leading 0
        $epiGuess = array('/\b(?:S(\d+))?[\s]?EP? ?(\d+)(?:-EP? ?\d+)?\b/i',
            '/\bS\d+[_.\s]?-?[_.\s]?\d+\b/i', //TODO why isn't this working for S2 - 001
            '/\b(\d+)x#?S?(\d+)/i',
            '/(\d+)[\s]?of[\s]?\d+\b/i',
            '/\bseason[\s]?(\d+),?[\s]?episode[\s]?(\d+)\b/i',
            '/\b0?(\d)(\d\d)\b/i',
            '/\bEps[\s]?(\d+)-\d+\b/i',
            '/\bPa?r?t[\s]?(\d+)\b/i');

        $dateGuess = '/(\d\d\d\d)[\sx-](\d\d)[\sx-](\d\d).?/i';

        foreach ($epiGuess as $guess) {
            $episode_guess = preg_replace($guess, '\1x\2', $episode_guess, -1, $replaceCount);
            if ($replaceCount > 0) {
                $episode_guess = preg_replace('/^(\d+)x$/', '1x\1', $episode_guess); //Match shows with EPI nr only.
                break;
            } else {
                $episode_guess = preg_replace($dateGuess, '\1\2\3', $episode_guess);
            }
        }
        $episode_guess = preg_replace('/0*(\d+)x0*(\d+)/', '\1x\2', $episode_guess);

        if (preg_match('/^(S\d\d?|Season[_\s]?\d\d?|\d{2,4}[\sx-]\d{1,2}[\sx-]All)$/i', $episode_guess)) {
            $episode_guess = 'fullSeason';
        }

        global $config_values;
        // append "p" to Episode if PROPER or REPACK or RERIP
        if (($config_values['Settings']['Download Proper'] == 1) && (preg_match('/[\s_.]PROPER[\s_.]|[\s_.]REPACK[\s_.]|[\s_.]RERIP[\s_.]/i', $title))) {
            $episode_guess .= "p";
        }

        // set Episode to Special if special is detected
        if (preg_match('/^(\d{1,2}xspecial)$/i', $episode_guess)) {
            $episode_guess = 'Special';
        }
    }
    return array("key" => $key_guess, "data" => $data_guess, "episode" => $episode_guess);
}
*/

// NEW guess_match()
function detectMatch($title, $normalize = FALSE) {
    // param $normalize is not really used any more, but keeping it to maintain compatibility
    
    // detect qualities
    $detectQualitiesOutput = detectQualities(simplifyTitle($title));
    $detectedQualitiesJoined = \implode(' ', $detectQualitiesOutput['detectedQualities']);
    $detectedQualitiesCount = count($detectQualitiesOutput['detectedQualities']) - 1;
    
    //TODO remove audio codecs
    
    // detect episode
    $detectSeasonAndEpisodeOutput = detectSeasonAndEpisode($detectQualitiesOutput['parsedTitle'], $detectedQualitiesCount);
    $seasonBatchEnd = $detectSeasonAndEpisodeOutput['detectedSeasonBatchEnd'];
    $seasonBatchStart = $detectSeasonAndEpisodeOutput['detectedSeasonBatchStart'];
    $episodeBatchEnd = $detectSeasonAndEpisodeOutput['detectedEpisodeBatchEnd'];
    $episodeBatchStart = $detectSeasonAndEpisodeOutput['detectedEpisodeBatchStart'];
    
    // parse episode output
    if($seasonBatchEnd > -1) {
        // found a ending season, probably detected other three values too        
        if($seasonBatchEnd == $seasonBatchStart) {
            // within one season
            if($episodeBatchEnd == $episodeBatchStart) {
                // single episode
                if($seasonBatchEnd == 0) {
                    // date notation
                    $episode_guess = $episodeBatchEnd;
                }
                else {
                    $episode_guess = $seasonBatchEnd . 'x' . $episodeBatchEnd;
                }
            }
            else if($episodeBatchEnd < $episodeBatchStart) {
                if($episodeBatchStart == 1) {
                    // assume full season
                    $episode_guess = "fullSeason"; //TODO figure out how to return season value                    
                }
                else {
                    // ERROR
                }
            }
            else {
                // batch of episodes within one season
                // TODO handle episode ranges that are not full seasons like 09-12 or 02-03
            }
        }
        else if($seasonBatchEnd > $seasonBatchStart) {
            // batch spans multiple seasons, treat EpisodeStart as paired with SeasonStart and EpisodeEnd as paired with SeasonEnd
            //TODO For now, this outputs the LATEST episode of the LATEST season, but change this function to output a range
            $episode_guess = $seasonBatchEnd . 'x' . $episodeBatchEnd;
        }
    }
    else {
        $episode_guess = "noShow";
    }
    //TODO add a return value that contains "noShow", "singleEpisode", "range", or "fullSeason", etc.
    return array("key" => $detectQualitiesOutput['parsedTitle'], "data" => $detectedQualitiesJoined, "episode" => $episode_guess);
}

function guess_feedtype($feedurl) {
    global $config_values;
    $response = check_for_cookies($feedurl);
    if (isset($response)) {
        $feedurl = $response['url'];
    }
    $get = curl_init();
    $getOptions[CURLOPT_URL] = $feedurl;
    get_curl_defaults($getOptions);
    curl_setopt_array($get, $getOptions);
    $content = explode('\n', curl_exec($get));
    curl_close($get);

    // Should be on the second line, but test the first 5 incase
    // of doctype etc.
    for ($i = 0; $i < 5; $i++) {
        if (preg_match('/<feed xml/', $content[$i], $regs))
            return 'Atom';
        else if (preg_match('/<rss/', $content[$i], $regs))
            return 'RSS';
    }
    return "RSS";
}

function guess_atom_torrent($summary) {
    $wc = '[\/\:\w\.\+\?\&\=\%\;]+';
    // Detects: A HREF=\"http://someplace/with/torrent/in/the/name\"
    if (preg_match('/A HREF=\\\"(http' . $wc . 'torrent' . $wc . ')\\\"/', $summary, $regs)) {
        _debug("guess_atom_torrent: $regs[1]\n", 2);
        return $regs[1];
    } else {
        _debug("guess_atom_torrent: failed\n", 2);
    }
    return FALSE;
}

?>
