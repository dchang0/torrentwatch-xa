<?php

function show_feed_lists_container() {
    global $html_out;
    $html_out .= "<div id='torrentlist_container'>\n";
}

function close_feed_lists_container() {
    global $html_out;
    $html_out .= "</div>\n";
}

function show_transmission_div() {
    global $html_out;
    $html_out .= '<div id="transmission_data" class="transmission">';
    $html_out .= '<ul id="transmission_list" class="torrentlist">';
}

function show_feed_item($item, $feed, $feedName, $alt, $torHash, $itemState, $id) {
    global $config_values, $html_out;
    $guess = detectMatch($item['title']);

    if (!$config_values['Settings']['Disable Hide List']) {
        if (isset($config_values['Hidden'][strtolower(trim(strtr($guess['favTitle'], array(":" => "", "," => "", "'" => "", "." => " ", "_" => " "))))])) {
            return;
        }
    }

    if ($config_values['Settings']['Client'] !== "folder") {
        switch ($itemState) {
            case "st_inCache":
            case "st_downloaded": //TODO remove this if PHP side is capable of verifying completed downloads
            case "st_downloading":
            case "st_favReady":
                $itemState = 'st_waitTorCheck';
        }
    }

    $ti = $item['title'];
    // Copy feed cookies to item
    $ulink = get_torrent_link($item);
    if (($pos = strpos($feed, ':COOKIE:')) !== false) {
        $ulink .= substr($feed, $pos);
    }

    ob_start();
    require('templates/feed_item.tpl');
    $html_out .= ob_get_contents();
    ob_end_clean();
}

// open and show the div which contains all the feed items (one div per feed list)
function show_feed_list($idx) {
    global $config_values, $html_out;
    if ($config_values['Settings']['Combine Feeds'] == 1) {
        $html_out .= '<div class="header combined">Combined Feeds</div>';
    }
    $html_out .= "<div class='feed' id='feed_$idx'>";
    if ($config_values['Settings']['Combine Feeds'] == 0) {
        $html_out .= "<div class=\"header\">\n";
        $html_out .= "<table width=\"100%\" cellspacing=\"0\"><tr><td class='hide_feed'>\n";
        $html_out .= "<span class=\"hide_feed_left\">\n";
        $html_out .= "<a href=\"#\" title=\"Hide this feed\" onclick=\"$.toggleFeed(" . $idx . ", 0)\">\n";
        $html_out .= "<img height='14' src=\"images/blank.gif\"></a></span></td>\n";
        if (!$config_values['Feeds'][$idx]['Name']) {
            $ti = $config_values['Feeds'][$idx]['Link'];
        } else {
            $ti = $config_values['Feeds'][$idx]['Name'];
        }
        $html_out .= "<td class='feed_title'><span>$ti</span><span class='matches'></span></td>\n";
        $html_out .= "<td class='hide_feed'>\n";
        $html_out .= "<span class=\"hide_feed_right\">\n";
        $html_out .= "<a href=\"#\" title=\"Hide this feed\" onclick=\"$.toggleFeed(" . $idx . ", 0)\">\n";
        $html_out .= "<img height='14' src=\"images/blank.gif\"></a></span></td>\n";
        $html_out .= "</tr></table></div>\n";
    }
    $html_out .= "<ul id='torrentlist' class='torrentlist'>";
}

function show_feed_down_header($idx) {
    global $config_values, $html_out;
    if (!$config_values['Feeds'][$idx]['Name']) {
        $ti = $config_values['Feeds'][$idx]['Link'];
    } else {
        $ti = $config_values['Feeds'][$idx]['Name'];
    }
    $html_out .= "<div class=\"errorHeader\">$ti is not available.</div>\n";
}

// close the div that contains all the feed items
function close_feed_list() {
    global $html_out;
    $html_out .= '</ul></div>';
}
