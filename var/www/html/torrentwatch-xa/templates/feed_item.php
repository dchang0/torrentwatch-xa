<?php

$utitle = null; // to contain HTML code of un-soft-hyphenated title of the item
$utitleJS = null; // to contain JS string literal of the raw title for onclick handlers
$description = null; // to contain HTML code of description of the item
$infoDiv = null; // to contain HTML code of each item's div.infoDiv
$hideItem = null; // to contain HTML code of div for Hide Show button on drop-down menu
$epiDiv = null; // to contain HTML code of div for Episode Info
// context menu state vars (kept defined to avoid PHP notices while context menu code is commented out)
$addFavHidden = null;
$torStart = null;
$torResume = null;
$torPause = null;
$torDelete = null;
$torTrash = null;
$progressBar = null; // to contain HTML code of div.progressBarContainer
$feedItem = null; // to contain HTML code of span containing the feed name if Combine Feeds = true
$showEpisodeNumber = null; // to contain HTML code of the show episode number, etc.
$pubDate = null; // to contain publication date of the item
$unixTime = null; // to contain UNIX timestamp
//TODO improve passing of $guess[], $id, $ulink, and $feed into this file

$ulinkJS = htmlspecialchars(json_encode($ulink ?? ''), ENT_QUOTES, 'UTF-8');
$feedJS = htmlspecialchars(json_encode($feed ?? ''), ENT_QUOTES, 'UTF-8');
$linkTypeJS = htmlspecialchars(json_encode($linkType ?? ''), ENT_QUOTES, 'UTF-8');
$idJS = htmlspecialchars(json_encode($id ?? ''), ENT_QUOTES, 'UTF-8');

$ulink = htmlspecialchars($ulink ?? '', ENT_QUOTES, 'UTF-8');
$feed = htmlspecialchars($feed ?? '', ENT_QUOTES, 'UTF-8');
$linkType = htmlspecialchars($linkType ?? '', ENT_QUOTES, 'UTF-8');
$id = htmlspecialchars($id ?? '', ENT_QUOTES, 'UTF-8');

if (isset($ulink) && !empty($ulink)) {
    $titleClasses = "torrent_title";
} else {
    $titleClasses = "torrent_title strikethrough";
}

if (isset($item['title'])) {
    $utitleRaw = str_replace('&shy;', '', $item['title']);
    $utitleJS = htmlspecialchars(json_encode($utitleRaw), ENT_QUOTES, 'UTF-8');
    $utitle = htmlspecialchars($utitleRaw, ENT_QUOTES, 'UTF-8');
}

if (isset($item['description'])) {
    $description = htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8');
}

if (isset($item['pubDate'])) {
    $pubDate = "<span class='pubDate'>" . htmlspecialchars($item['pubDate'], ENT_QUOTES, 'UTF-8') . "</span>";
    $unixTime = strtotime($item['pubDate']);
}

if (!isset($torHash) || !$torHash) {
    $torHash = '###torHash###';
}
$torHashJS = htmlspecialchars(json_encode($torHash), ENT_QUOTES, 'UTF-8');

if ($config_values['Settings']['Combine Feeds'] == 1) {
    $feedItem = "<span class=\"feed_name\">" . htmlspecialchars($feedName ?? '', ENT_QUOTES, 'UTF-8') . "</span>";
}

/* if (!$config_values['Settings']['Disable Hide List'] && $itemState === "st_notAMatch") {
    $hideItem = "<div class='contextItem hideItem' onclick='$.hideItem(" . $utitleJS . ")' title='Hide show'>Hide show</div>"; // adds Hide Show button to drop-down menu
} */

if ($config_values['Settings']['Client'] != "folder") {
    $progressBar = "<div class='progressBarContainer init'><div class='progressDiv' style='width: 0.07%; height: 3px; '></div></div>";
}

/* // hide or show choices in contextMenu
switch ($itemState) {
    case "st_favReady":
    case "st_waitTorCheck":
    case "st_inCache":
        // hide every choice in these interim states
        $addFavHidden = "hidden";
        $torStart = "torStart hidden";
        $torResume = "torResume hidden";
        $torPause = "torPause hidden";
        $torDelete = "torDelete hidden";
        $torTrash = "torTrash hidden";
        break;
    default:
        $addFavHidden = "";
        $torStart = "torStart";
        $torResume = "torResume hidden";
        $torPause = "torPause hidden";
        $torDelete = "torDelete hidden";
        $torTrash = "torTrash hidden";
} */

$showTitle = htmlspecialchars($guess['favTitle'] ?? '', ENT_QUOTES, 'UTF-8');
$showQuality = htmlspecialchars($guess['qualities'] ?? '', ENT_QUOTES, 'UTF-8');
$debugMatch = htmlspecialchars($guess['debugMatch'] ?? '', ENT_QUOTES, 'UTF-8');

if ($config_values['Settings']['Show Debug']) {
    $showEpisodeNumber = "<span class=\"debugLabel\">$showTitle</span><span class=\"debugLabel\"><b>$debugMatch</b></span>";
} else {
    $showEpisodeNumber = '';
}

if ($guess['episode'] != '' && $guess['episode'] != 'notSerialized') {
    $showEpisodeNumber .= "<span class=\"episodeNum\" title=\"$debugMatch\"><b>" . htmlspecialchars($guess['episode'] ?? '', ENT_QUOTES, 'UTF-8') . "</b></span>";
} else {
    $showEpisodeNumber .= "<span class=\"episodeNum\" title=\"$debugMatch\"><b>(\"_&nbsp;)</b></span>";
}

$ti = htmlspecialchars($ti ?? '', ENT_QUOTES, 'UTF-8');

print <<< EOH
<li id="id_$id" name="$id" class="torrent $itemState $alt item_$torHash">
<input type="hidden" class="title" value="$utitle"/>
<input type="hidden" class="show_title" value="$showTitle"/>
<input type="hidden" class="show_quality" value="$showQuality"/>
<input type="hidden" class="link" value="$ulink"/>
<input type="hidden" class="link_type" value="$linkType"/>
<input type="hidden" class="feed_link" value="$feed"/>
<input type="hidden" class="feed_item_id" value="$id"/>
<table width="100%" cellspacing="0">
<tr>
<td class="identifier"></td>
<td class="torrent_name">
<div class='torrent_name'>
<!-- <span class="contextButtonContainer"><a id="contextButton_$id" class="contextButton" onclick='$.toggleContextMenu("#divContext_" + $idJS, $idJS);'></a></span> -->
<span class="$titleClasses" title="$description">$ti</span>
<span class='torrent_pubDate'>$feedItem $showEpisodeNumber $pubDate</span>
</div>
<!-- <div id="divContext_$id" class="contextMenu">
<div class='contextItem addFavorite <?= $addFavHidden ?>' onclick='javascript:$.addFavorite($feedJS,$utitleJS)' title="Add this show to favorites">Add to favorites</div>
<div class='contextItem $torStart' onclick='javascript:$.dlTorrent($utitleJS,$ulinkJS,$linkTypeJS,$feedJS,$idJS)' title="Download this torrent">Download</div>
<div class="contextItem activeTorrent $torResume" onclick='javascript:$.stopStartTorrent("start", $torHashJS)' title="Resume download">Resume transfer</div>
<div class="contextItem activeTorrent $torPause" onclick='javascript:$.stopStartTorrent("stop", $torHashJS)' title="Pause transfer">Pause transfer</div>
<div class="contextItem activeTorrent $torDelete" onclick='javascript:$.delTorrent($torHashJS, false, false)' title="Delete torrent but keep data">Remove from client</div>
<div class="contextItem activeTorrent $torTrash" onclick='javascript:$.delTorrent($torHashJS, true, false)' title="Delete torrent and its data">Remove &amp; trash data</div>
$hideItem
$epiDiv
</div> -->
$progressBar
<span class='debugMatch hidden' id='debugMatch_$id'>$debugMatch</span>
<span class='unixTime hidden' id='unixTime_$id'>$unixTime</span>
</td>
</tr>
</table>
</li>
EOH;
