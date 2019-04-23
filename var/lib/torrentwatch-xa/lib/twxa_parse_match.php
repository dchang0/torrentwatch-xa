<?php

// contains the casading switch-case blocks of matchTitle() logic functions

function matchTitle6_($ti, $seps, $wereQualitiesDetected = false) {
    // exactly six numbers found
    switch (true) {
        case true:
            // scan for ##x##v#-##x##v#
            $result = matchTitle6_1($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true:
            // scan for YYYY MM DD-YYYY MM DD
            $result = matchTitle6_2($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true:
            // isolated E1 E2 E3 E4 E5 E6
            $result = matchTitle6_3($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        default:
            $result['favTi'] = $ti;
            $result['matFnd'] = "6_";
    }
    return $result;
}

function matchTitle5_($ti, $seps, $wereQualitiesDetected = false) {
    // exactly five numbers found
    switch (true) {
        case true:
            // ##x## - ##x##v#
            $result = matchTitle5_1($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true:
            // ##x##v# - ##x##
            $result = matchTitle5_2($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true:
            // isolated E1 E2 E3 E4 E5
            $result = matchTitle5_3($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        default:
            $result['favTi'] = $ti;
            $result['matFnd'] = "5_";
    }
    return $result;
}

function matchTitle4_($ti, $seps, $wereQualitiesDetected = false) {
    // exactly four numbers found
    switch (true) {
        case true:
            // scan for ##x##-##x##
            $result = matchTitle4_1($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true:
            // isolated E1 E2 E3 E4
            $result = matchTitle4_2($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        default:
            $result['favTi'] = $ti;
            $result['matFnd'] = "4_";
    }
    return $result;
}

function matchTitle3_($ti, $seps, $wereQualitiesDetected = false) {
    // exactly three numbers found
    switch (true) {
        case true :
            // isolated YYYY MM DD or MM DD YYYY
            $result = matchTitle3_1($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // explicit S## E### - ### or S## ( E### - ### )
            $result = matchTitle3_2($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // isolated S##E## - E## range
            $result = matchTitle3_3($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // isolated S# - ####.#
            $result = matchTitle3_4($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // S# - ### - ###
            $result = matchTitle3_5($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // Japanese YYYY MM DD Print Media
            $result = matchTitle3_6($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // explicit S##E###.#
            $result = matchTitle3_7($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // YYYY.SSxEE
            $result = matchTitle3_8($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // ## Episode ###.# but not ##E##.#
            $result = matchTitle3_9($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // Episode ##.# - ##
            $result = matchTitle3_10($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // Episode ## - ##.#
            $result = matchTitle3_11($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // ### to ###.# episodes
            $result = matchTitle3_12($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // ###.# to ### episodes
            $result = matchTitle3_13($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // (YYYY) EE - EE (must precede (YYYY) - EE (EEE))
            $result = matchTitle3_14($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // (YYYY) - EE (EEE) (must precede (YYYY) - EE)
            $result = matchTitle3_15($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // isolated S## ##v# (Season ## Episode ## Version #) (must precede isolated SS ##v#)
            $result = matchTitle3_16($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // isolated SS ##v# (Season ## Episode ## Version #)
            $result = matchTitle3_17($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // #### (EE - EE)
            $result = matchTitle3_18($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // #### Ep EE - EE
            $result = matchTitle3_19($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // #### EE - EE
            $result = matchTitle3_20($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // isolated E1 E2 E3
            $result = matchTitle3_21($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        default :
            $result['favTi'] = $ti;
            $result['matFnd'] = "3_";
    }
    return $result;
}

function matchTitle2_($ti, $seps, $wereQualitiesDetected = false) {
    // exactly two numbers found
    switch (true) {
        case true :
            // S01v2 or S01.v2
            $result = matchTitle2_1($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // S01E10
            $result = matchTitle2_2($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // ## - v# (Episode ## Version #)
            $result = matchTitle2_3($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // short-circuit Seasons ## through|thru|to ##
            $result = matchTitle2_4($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // short-circuit Seasons ## - ##
            $result = matchTitle2_5($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // short-circuit EP## - EP##
            $result = matchTitle2_6($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // short-circuit S1 - ###
            $result = matchTitle2_7($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // (Season, Temporada ##) - ##
            $result = matchTitle2_8($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // Season, Temporada ## - ##
            $result = matchTitle2_9($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // 1st|2nd|3rd Season ##
            $result = matchTitle2_10($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // ### - ###END
            $result = matchTitle2_11($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // V##.## (Software Version ##.##)
            $result = matchTitle2_12($ti, $seps, $wereQualitiesDetected);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // - ##x##
            $result = matchTitle2_13($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // isolated ##x##
            $result = matchTitle2_14($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // Volume ## of ##
            $result = matchTitle2_15($ti, $seps, $wereQualitiesDetected);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // Part ## of ##
            $result = matchTitle2_16($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // isolated ## of ##
            $result = matchTitle2_17($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // Volume ## & ##
            $result = matchTitle2_18($ti, $seps, $wereQualitiesDetected);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // Chapter ## & ##
            $result = matchTitle2_19($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // Season ## & ##
            $result = matchTitle2_20($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // Episode ## & ##
            $result = matchTitle2_21($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // Part ## & ##
            $result = matchTitle2_22($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // isolated ## & ##
            $result = matchTitle2_23($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // Volume ## - ##
            $result = matchTitle2_24($ti, $seps, $wereQualitiesDetected);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // Volume ## Chapter ##
            $result = matchTitle2_25($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // Chapter ## Volume ##
            $result = matchTitle2_26($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // Chapter ##-##
            $result = matchTitle2_27($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // c## (v##)
            $result = matchTitle2_28($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // isolated SS - Episode ##
            $result = matchTitle2_29($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // Japanese ##-## Print Media Books/Volumes
            $result = matchTitle2_30($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // Japanese YYYY MM or YYYY ## Print Media
            $result = matchTitle2_31($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // #nd EE
            $result = matchTitle2_32($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // isolated ###.#
            $result = matchTitle2_33($ti);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // isolated YYYY-MM or or YYYY-EE or title #### - EE
            $result = matchTitle2_34($ti, $seps, $wereQualitiesDetected);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // isolated MM-YYYY
            $result = matchTitle2_35($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // (YYYY) - EE or title (####) - EE
            $result = matchTitle2_36($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // EEE - (YYYY)
            $result = matchTitle2_37($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // isolated No.##-No.##, Print Media Book/Volume
            $result = matchTitle2_38($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // isolated S1 #10
            $result = matchTitle2_39($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // isolated ## to ##
            $result = matchTitle2_40($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // ID-## - ## (different spacing around minuses)
            $result = matchTitle2_41($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // isolated ##-##
            $result = matchTitle2_42($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // S (EEE)
            $result = matchTitle2_43($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // isolated SS EE, SS EEE, or isolated EE EE
            $result = matchTitle2_44($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // two numbers separated by words
            $result = matchTitle2_45($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        default :
            $result['favTi'] = $ti;
            $result['matFnd'] = "2_";
    }
    return $result;
}

function matchTitle1_($ti, $seps, $wereQualitiesDetected = false) {
    // only one integer found, probably anime-style episode number, but look for preceding words
    preg_match("/(\d+)/u", $ti, $matNum);
    $matNumLen = strlen($matNum[1]);

    switch ($matNumLen) {
        case 1 :
        case 2 :
        case 3 :
            // three digits or less
            // NOTE: Yes, the switch-case-if-break control structure is stupid, but it is a result
            // of how PHP handles the assignment of a return value from a function called inside
            // a conditional. In the statement if ($output = function()), $output = 1 if the
            // function call succeeds and null if it doesn't, not the value returned by function().
            // The cheesy switch-case-if-break still beats a deep if-else if-else control structure.
            switch (true) {
                case true :
                    // Season, Temporada; should also catch Season ## Complete
                    $result = matchTitle1_1_1($ti, $seps);
                    if (is_string($result['matFnd'])) {
                        break;
                    }
                case true :
                    // ##rd/##th Season
                    $result = matchTitle1_1_2($ti, $seps);
                    if (is_string($result['matFnd'])) {
                        break;
                    }
                case true :
                    // Volume, Volumen ##
                    $result = matchTitle1_1_3($ti, $seps, $wereQualitiesDetected);
                    if (is_string($result['matFnd'])) {
                        break;
                    }
                case true :
                    // V. ##--Volume, not version, and not titles like ARC-V
                    $result = matchTitle1_1_4($ti, $seps, $wereQualitiesDetected);
                    if (is_string($result['matFnd'])) {
                        break;
                    }
                case true :
                    // Chapter, Capitulo ##
                    $result = matchTitle1_1_5($ti, $seps);
                    if (is_string($result['matFnd'])) {
                        break;
                    }
                case true :
                    // Movie ##
                    $result = matchTitle1_1_6($ti, $seps);
                    if (is_string($result['matFnd'])) {
                        break;
                    }
                case true :
                    // Movie v##
                    $result = matchTitle1_1_7($ti, $seps);
                    if (is_string($result['matFnd'])) {
                        break;
                    }
                case true :
                    // Film ##
                    $result = matchTitle1_1_8($ti, $seps);
                    if (is_string($result['matFnd'])) {
                        break;
                    }
                case true :
                    // Film v##
                    $result = matchTitle1_1_9($ti, $seps);
                    if (is_string($result['matFnd'])) {
                        break;
                    }
                case true :
                    // Part ##
                    $result = matchTitle1_1_10($ti, $seps);
                    if (is_string($result['matFnd'])) {
                        break;
                    }
                case true :
                    // Episode ##
                    $result = matchTitle1_1_11($ti, $seps);
                    if (is_string($result['matFnd'])) {
                        break;
                    }
                case true :
                    // Special v##
                    $result = matchTitle1_1_12($ti, $seps);
                    if (is_string($result['matFnd'])) {
                        break;
                    }
                case true :
                    // 02 - Special
                    $result = matchTitle1_1_13($ti, $seps);
                    if (is_string($result['matFnd'])) {
                        break;
                    }
                case true :
                    // Special - 02
                    // Spec02
                    // SP# (Special #)
                    $result = matchTitle1_1_14($ti, $seps);
                    if (is_string($result['matFnd'])) {
                        break;
                    }
                case true :
                    // OVA v##
                    $result = matchTitle1_1_15($ti, $seps);
                    if (is_string($result['matFnd'])) {
                        break;
                    }
                case true :
                    // OVA - ##
                    $result = matchTitle1_1_16($ti, $seps);
                    if (is_string($result['matFnd'])) {
                        break;
                    }
                case true :
                    // OVA (####) or OVA (YYYY) or OVA ####
                    $result = matchTitle1_1_17($ti, $seps);
                    if (is_string($result['matFnd'])) {
                        break;
                    }
                case true :
                    // Roman numeral SS-EE
                    $result = matchTitle1_1_18($ti, $seps);
                    if (is_string($result['matFnd'])) {
                        break;
                    }
                case true :
                    // pound sign and number
                    $result = matchTitle1_1_19($ti, $seps);
                    if (is_string($result['matFnd'])) {
                        break;
                    }
                case true :
                    // apostrophe ## (abbreviated year)
                    $result = matchTitle1_1_20($ti, $seps);
                    if (is_string($result['matFnd'])) {
                        break;
                    }
                case true :
                    // PV ##
                    $result = matchTitle1_1_21($ti, $seps);
                    if (is_string($result['matFnd'])) {
                        break;
                    }
                case true :
                    // standalone Version ##
                    $result = matchTitle1_1_22($ti, $seps);
                    if (is_string($result['matFnd'])) {
                        break;
                    }
                default :
                    // assume it's an anime-style episode number
                    switch (true) {
                        case true :
                            // Japanese ## Episode
                            $result = matchTitle1_1_30_1($ti, $seps);
                            if (is_string($result['matFnd'])) {
                                break;
                            }
                        case true :
                            // Japanese ## Print Media Book/Volume
                            $result = matchTitle1_1_30_2($ti, $seps);
                            if (is_string($result['matFnd'])) {
                                break;
                            }
                        case true :
                            // Japanese ##
                            $result = matchTitle1_1_30_3($ti, $seps);
                            if (is_string($result['matFnd'])) {
                                break;
                            }
                        case true :
                            // Chinese sequel ##
                            $result = matchTitle1_1_30_4($ti, $seps);
                            if (is_string($result['matFnd'])) {
                                break;
                            }
                        case true :
                            // Chinese ## preview image/video
                            $result = matchTitle1_1_30_5($ti, $seps);
                            if (is_string($result['matFnd'])) {
                                break;
                            }
                        case true :
                            // isolated EEE
                            $result = matchTitle1_1_30_6($ti, $seps);
                            if (is_string($result['matFnd'])) {
                                break;
                            }
                        case true :
                            // isolated or buttressed EEE
                            $result = matchTitle1_1_30_7($ti, $seps);
                            if (is_string($result['matFnd'])) {
                                break;
                            }
                        default :
                            $result['favTi'] = $ti;
                            $result['matFnd'] = "1_1_30";
                    }
                    if (is_null($result)) {
                        $result['favTi'] = $ti;
                        $result['matFnd'] = "1_1";
                    }
            }
            break;
        case 4 :
            // check if YYYY or MMDD or DDMM or MMYY or YYMM, otherwise assume ####
            // 1896 was year of first moving picture
            $thisYear = getdate()['year'];
            if ($matNum[1] > 1895 && $matNum[1] <= $thisYear) {
                // probably YYYY
                $result['numSeq'] = 2;
                $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                $result['episEd'] = $result['episSt'] = $matNum[1];
                $result['matFnd'] = "1_2-1";
            } else {
                $pair1 = substr($matNum[1], 0, 2);
                $pair2 = substr($matNum[1], 2);
                if (checkdate((int)$pair2, (int)$pair1, $thisYear)) {
                    // probably DDMM (assume YYYY is current year)
                    $result['numSeq'] = 2;
                    $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                    $result['episEd'] = $result['episSt'] = $pair2 . $pair1;
                    $result['matFnd'] = "1_2-2-1";
                } else if (checkdate((int)$pair1, (int)$pair2, $thisYear)) {
                    // probably MMDD (assume YYYY is current year)
                    $result['numSeq'] = 2;
                    $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                    $result['episEd'] = $result['episSt'] = $matNum[1];
                    $result['matFnd'] = "1_2-2-2";
                }
                // we don't handle MMYY or YYMM because it is too tedious to figure out YYYY from YY
                else {
                    $result['seasEd'] = $result['seasSt'] = 1; // episode notation gets Season 1
                    $result['episEd'] = $result['episSt'] = $matNum[1];
                    $result['matFnd'] = "1_2-2-3";
                }
            }
            $result['favTi'] = preg_replace("/\d+.*/", "", $ti);
            break;
        case 8 :
            // YYYYMMDD
            // YYYYDDMM
            // MMDDYYYY
            // DDMMYYYY
            // ######## (not likely)
            // 8-digit numeric checksum (should have been filtered out by now)
            // split into four pairs of numerals
            $four1 = substr($matNum[1], 0, 4);
            $four2 = substr($matNum[1], 4, 4);
            $pair1 = substr($four1, 0, 2);
            $pair2 = substr($four1, 2, 2);
            $pair3 = substr($four2, 0, 2);
            $pair4 = substr($four2, 2, 2);
            $thisYear = getdate()['year'];
            if (checkdate((int)$pair3, (int)$pair4, (int)$four1) && (int)$four1 <= $thisYear && (int)$four1 > 1895) {
                // YYYYMMDD
                $result['numSeq'] = 2;
                $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                $result['episEd'] = $result['episSt'] = $matNum[1];
                $result['matFnd'] = "1_3-1";
            } else if (checkdate((int)$pair4, (int)$pair3, (int)$four1) && (int)$four1 <= $thisYear && (int)$four1 > 1895) {
                // YYYYDDMM
                $result['numSeq'] = 2;
                $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                $result['episEd'] = $result['episSt'] = $four1 . $pair4 . $pair3;
                $result['matFnd'] = "1_3-2";
            } else if (checkdate((int)$pair1, (int)$pair2, (int)$four2) && (int)$four2 <= $thisYear && (int)$four2 > 1895) {
                // MMDDYYYY
                $result['numSeq'] = 2;
                $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                $result['episEd'] = $result['episSt'] = $four2 . $four1;
                $result['matFnd'] = "1_3-3";
            } else if (checkdate((int)$pair2, (int)$pair1, (int)$four2) && (int)$four2 <= $thisYear && (int)$four2 > 1895) {
                // DDMMYYYY
                $result['numSeq'] = 2;
                $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                $result['episEd'] = $result['episSt'] = $four2 . $pair2 . $pair1;
                $result['matFnd'] = "1_3-4";
            } else {
                // Unknown ########
                if ($wereQualitiesDetected) {
                    $result['medTyp'] = 1;
                } else {
                    $result['medTyp'] = 0;
                }
                $result['numSeq'] = 0;
                $result['matFnd'] = "1_3-5";
            }
            $result['favTi'] = preg_replace("/\d+.*/", "", $ti);
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
            $pair1 = substr($matNum[1], 0, 2);
            $pair2 = substr($matNum[1], 2, 2);
            $pair3 = substr($matNum[1], 4, 2);
            $thisYear = getdate()['year'];
            $thisYearPair1 = substr($thisYear, 0, 2);
            if (checkdate((int)$pair3, 1, (int)($pair1 . $pair2)) && (int)($pair1 . $pair2) <= $thisYear && (int)($pair1 . $pair2) > 1895) {
                // YYYYMM
                $result['numSeq'] = 2;
                $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                $result['episEd'] = $result['episSt'] = $matNum[1];
                $result['matFnd'] = "1_4-1";
            } else if (checkdate((int)$pair1, 1, $pair2 . (int)$pair3) && (int)($pair2 . $pair3) <= $thisYear && (int)($pair2 . $pair3) > 1895) {
                // MMYYYY
                $result['numSeq'] = 2;
                $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                $result['episEd'] = $result['episSt'] = $pair2 . $pair3 . $pair1;
                $result['matFnd'] = "1_4-2";
            } else if (checkdate((int)$pair2, (int)$pair3, (int)($thisYearPair1 . $pair1)) && (int)($thisYearPair1 . $pair1) <= $thisYear && (int)($thisYearPair1 . $pair1) > 1895) {
                // YYMMDD
                $result['numSeq'] = 2;
                $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                $result['episEd'] = $result['episSt'] = $thisYearPair1 . $pair1 . $pair2 . $pair3;
                $result['matFnd'] = "1_4-3";
            } else if (checkdate((int)$pair1, (int)$pair2, (int)($thisYearPair1 . $pair3)) && (int)($thisYearPair1 . $pair3) <= $thisYear && (int)($thisYearPair1 . $pair3) > 1895) {
                // MMDDYY
                $result['numSeq'] = 2;
                $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                $result['episEd'] = $result['episSt'] = $thisYearPair1 . $pair3 . $pair1 . $pair2;
                $result['matFnd'] = "1_4-4";
            } else if (checkdate((int)$pair2, (int)$pair1, (int)($thisYearPair1 . $pair3)) && (int)($thisYearPair1 . $pair3) <= $thisYear && (int)($thisYearPair1 . $pair3) > 1895) {
                // DDMMYY
                $result['numSeq'] = 2;
                $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                $result['episEd'] = $result['$episSt'] = $thisYearPair1 . $pair3 . $pair2 . $pair1;
                $result['matFnd'] = "1_4-5";
            } else if (checkdate((int)$pair3, (int)$pair2, (int)($thisYearPair1 . $pair1)) && (int)($thisYearPair1 . $pair1) <= $thisYear && (int)($thisYearPair1 . $pair1) > 1895) {
                // YYDDMM
                $result['numSeq'] = 2;
                $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
                $result['episEd'] = $result['episSt'] = $thisYearPair1 . $pair1 . $pair3 . $pair2;
                $result['matFnd'] = "1_4-6";
            } else {
                // Unknown ######
                $result['numSeq'] = 0;
                if ($wereQualitiesDetected) {
                    $result['medTyp'] = 1;
                } else {
                    $result['medTyp'] = 0;
                }
                $result['matFnd'] = "1_4-7";
            }
            $result['favTi'] = preg_replace("/\d+.*/", "", $ti);
            break;
        case 12 :
            // YYYYMMDDHHMM
            $result['numSeq'] = 2;
            $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
            $result['episEd'] = $result['episSt'] = substr($matNum[1], 0, 8); // truncate the lengthy Date notation
            $result['favTi'] = preg_replace("/\d+.*/", "", $ti);
            $result['matFnd'] = "1_5";
            break;
        case 14 :
            // YYYYMMDDHHMMSS
            $result['numSeq'] = 2;
            $result['seasEd'] = $result['seasSt'] = 0; // date notation gets Season 0
            $result['episEd'] = $result['episSt'] = substr($matNum[1], 0, 8); // truncate the lengthy Date notation
            $result['favTi'] = preg_replace("/\d+.*/", "", $ti);
            $result['matFnd'] = "1_6";
            break;
        default:
            // unidentifiable #
            $result['numSeq'] = 0;
            if ($wereQualitiesDetected) {
                $result['medTyp'] = 1;
            } else {
                $result['medTyp'] = 0;
            }
            $result['favTi'] = $ti;
            $result['matFnd'] = "1_";
    }
    return $result;
}

function matchTitle0_($ti, $seps, $wereQualitiesDetected = false) {
    // exactly zero numbers found
    switch (true) {
        case true :
            // bounded word Special
            $result = matchTitle0_1($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // bounded word OVA
            $result = matchTitle0_2($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        case true :
            // isolated PV
            $result = matchTitle0_3($ti, $seps);
            if (is_string($result['matFnd'])) {
                break;
            }
        default :
            $result['favTi'] = $ti;
            $result['matFnd'] = "0_";
    }
    return $result;
}
