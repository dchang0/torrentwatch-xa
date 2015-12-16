<?php
require_once('twxa_parse.php');

// This file purposefully over-commented to help with rewrite of season and episode guessing engine

function detectMatch($ti, $normalize = FALSE) {
    $episode_guess = '';

    // detect qualities
    $detectQualitiesOutput = detectQualities(simplifyTitle($ti));
    $detQualitiesJoined = implode(' ', $detectQualitiesOutput['detectedQualities']);
    // don't use count() on arrays because it returns 1 if not countable; it is enough to know if any quality was detected
    if(strlen($detQualitiesJoined) > 0) {
        $wereQualitiesDetected = true;
    }
    else {
        $wereQualitiesDetected = false;
    }

    //TODO detect video-related words like Sub and Dub
    
    // strip out audio codecs
    $detectAudioCodecsOutput = detectAudioCodecs($detectQualitiesOutput['parsedTitle']);

    // detect episode
    $detectItemOutput = detectItem($detectAudioCodecsOutput['parsedTitle'], $wereQualitiesDetected);
    $seasonBatchEnd = $detectItemOutput['detectedSeasonBatchEnd'];
    $seasonBatchStart = $detectItemOutput['detectedSeasonBatchStart'];
    $episodeBatchEnd = $detectItemOutput['detectedEpisodeBatchEnd'];
    $episodeBatchStart = $detectItemOutput['detectedEpisodeBatchStart'];

    // parse episode output
    if($seasonBatchEnd > -1) {
        // found a ending season, probably detected other three values too
        if($seasonBatchEnd == $seasonBatchStart) {
            // within one season
            if($episodeBatchEnd == $episodeBatchStart && $episodeBatchEnd > -1) {
                // single episode
                if($seasonBatchEnd == 0) {
                    // date notation
                    $episode_guess = $episodeBatchEnd;
                }
                else {
                    $episode_guess = $seasonBatchEnd . 'x' . $episodeBatchEnd;
                }
            }
            else if($episodeBatchEnd == '') {
                // assume full season
                $episode_guess = "fullSeason"; //TODO figure out how to return season value
            }
            else {
                // $episodeBatchEnd = '' OR batch of episodes within one season
                // TODO handle episode ranges that are not full seasons like 09-12 or 02-03
            }
        }
        else if($seasonBatchEnd > $seasonBatchStart) {
            // batch spans multiple seasons, treat EpisodeStart as paired with SeasonStart and EpisodeEnd as paired with SeasonEnd
            if($episodeBatchEnd == '') {
                $episode_guess = "fullSeason"; //TODO figure out how to return season value, should be range of full seasons
            }
            else {
                //TODO For now, this outputs the LATEST episode of the LATEST season, but change this function to output a range
                $episode_guess = $seasonBatchEnd . 'x' . $episodeBatchEnd; //TODO block final output of Episode = 0
            }
        }
    }
    else {
        $episode_guess = "noShow";
    }
    //TODO handle PV and other numberSequence values
    //TODO handle "noShow", "singleEpisode", "range", or "fullSeason", etc.
    if($normalize === true) {
        // normalized title
        $title = $detectItemOutput['favoriteTitle'];
    }
    else {
        $title = $detectQualitiesOutput['parsedTitle'];
    }
    return [
        'title' => $title,
        'qualities' => $detQualitiesJoined,
        'episode' => $episode_guess,
        'isVideo' => $wereQualitiesDetected, //TODO replace this with mediaType
        'mediaType' => $detectItemOutput['mediaType'],
        'itemVersion' => $detectItemOutput['itemVersion'],
        'numberSequence' => $detectItemOutput['numberSequence'],
        'debugMatch' => $detectItemOutput['debugMatch']
    ];
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

    // Should be on the second line, but test the first 5 in case of doctype etc.
    for ($i = 0; $i < 5; $i++) {
        if (preg_match('/<feed xml/', $content[$i], $regs)) {
            return 'Atom';
        } else if (preg_match('/<rss/', $content[$i], $regs)) {
            return 'RSS';
        }
    }
    return "RSS";
}

function guess_atom_torrent($summary) {
    $wc = '[\/\:\w\.\+\?\&\=\%\;]+';
    // Detects: A HREF=\"http://someplace/with/torrent/in/the/name\"
    if (preg_match('/A HREF=\\\"(http' . $wc . 'torrent' . $wc . ')\\\"/', $summary, $regs)) {
        twxa_debug("guess_atom_torrent: $regs[1]\n", 2);
        return $regs[1];
    } else {
        twxa_debug("guess_atom_torrent: failed\n", 2);
    }
    return FALSE;
}
