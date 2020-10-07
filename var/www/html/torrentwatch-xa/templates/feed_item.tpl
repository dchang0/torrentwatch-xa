<?php
$utitle = null; // to contain HTML code of un-soft-hyphenated title of the item
$description = null; // to contain HTML code of description of the item
$infoDiv = null; // to contain HTML code of each item's div.infoDiv
$hideItem = null; // to contain HTML code of div for Hide Show button on drop-down menu
$epiDiv = null; // to contain HTML code of div for Episode Info
$progressBar = null; // to contain HTML code of div.progressBarContainer
$feedItem = null; // to contain HTML code of span containing the feed name if Combine Feeds = true
$showEpisodeNumber = null; // to contain HTML code of the show episode number, etc.
$pubDate = null; // to contain publication date of the item
$unixTime = null; // to contain UNIX timestamp

//TODO improve passing of $guess[], $id, $ulink, and $feed into this file

if(isset($item['title'])) {
    $utitle = str_replace('&shy;', '', $item['title']);
}

if(isset($item['description'])) {
    $description = $item['description'];
}

if(isset($item['pubDate'])) {
    $pubDate = "<span class='pubDate'>" . $item['pubDate'] . "</span>";
    $unixTime = strtotime($item['pubDate']);
}

if(!($torHash)) {
    $torHash = '###torHash###';
}

if($config_values['Settings']['Combine Feeds'] == 1) {
    $feedItem = "<span class=\"feed_name\">$feedName</span>";
}

if(!$config_values['Settings']['Disable Hide List'] && $itemState === "st_notAMatch")  {
    $hideItem = "<div class='contextItem hideItem' onclick='$.hideItem(\"$utitle\")' title='Hide show'>Hide show</div>"; // adds Hide Show button to drop-down menu
}

if($config_values['Settings']['Client'] != "folder") {
    $progressBar = "<div class='progressBarContainer init'><div class='progressDiv' style='width: 0.07%; height: 3px; '></div></div>";
}

// hide or show choices in contextMenu
switch ($itemState) {
    case "st_favReady":
    case "st_waitTorCheck":
    case "st_inCache":
        // hide every choice in these interim states
        $torStart = "torStart hidden";
        $torResume = "torResume hidden";
        $torPause = "torPause hidden";
        $torDelete = "torDelete hidden";
        $torTrash = "torTrash hidden";
        break;
    default:
        $torStart = "torStart";
        $torResume = "torResume hidden";
        $torPause = "torPause hidden";
        $torDelete = "torDelete hidden";
        $torTrash = "torTrash hidden";
}

$showTitle = $guess['favTitle'];
$showQuality = $guess['qualities'];
$debugMatch = $guess['debugMatch'];

if($config_values['Settings']['Show Debug']) {
    $showEpisodeNumber = "<span class=\"debugLabel\">$showTitle</span><span class=\"debugLabel\"><b>$debugMatch</b></span>";
} else {
    $showEpisodeNumber = '';
}

if($guess['episode'] != '' && $guess['episode'] != 'notSerialized') {
    $showEpisodeNumber .= "<span class=\"episodeNum\" title=\"$debugMatch\"><b>" . $guess['episode'] . "</b></span>";
} else {
    $showEpisodeNumber .= "<span class=\"episodeNum\" title=\"$debugMatch\"><b>(\"_&nbsp;)</b></span>";
}

print <<< EOH
<li id=id_$id name=$id class="torrent $itemState $alt item_$torHash">
<input type="hidden" class="title" value="$utitle"/>
<input type="hidden" class="show_title" value="$showTitle"/>
<input type="hidden" class="show_quality" value="$showQuality"/>
<input type="hidden" class="link" value="$ulink"/>
<input type="hidden" class="feed_link" value="$feed"/>
<input type="hidden" class="client_id" value="$id"/>
<table width="100%" cellspacing="0">
<tr>
<td class="identifier"></td>
<td class="torrent_name">
<div class='torrent_name'>
<span class="contextButtonContainer"><a id="contextButton_$id" class="contextButton" onclick='$.toggleContextMenu("#divContext_$id", "$id");'></a></span>
<span class='torrent_title' title="$description">$ti</span>
<span class='torrent_pubDate'>$feedItem $showEpisodeNumber $pubDate</span>
</div>
<div id="divContext_$id" class="contextMenu">
<div class='contextItem addFavorite' onclick='javascript:$.addFavorite("$feed","$utitle")' title="Add this show to favorites">Add to favorites</div>
<div class='contextItem $torStart' onclick='javascript:$.dlTorrent("$utitle","$ulink","$feed","$id")' title="Download this torrent">Download</div>
<div class="contextItem activeTorrent $torResume" onclick='javascript:$.stopStartTorrent("start", "$torHash")' title="Resume download">Resume transfer</div>
<div class="contextItem activeTorrent $torPause" onclick='javascript:$.stopStartTorrent("stop", "$torHash")' title="Pause download">Pause transfer</div>
<div class="contextItem activeTorrent $torDelete" onclick='javascript:$.delTorrent("$torHash", false, false)' title="Delete torrent but keep data">Remove from client</div>
<div class="contextItem activeTorrent $torTrash" onclick='javascript:$.delTorrent("$torHash", true, false)' title="Delete torrent and its data">Remove & trash data</div>
$hideItem
$epiDiv
</div>
$progressBar
<span class='hidden' id='debugMatch'>$debugMatch</span>
<span class='hidden' id='unixTime'>$unixTime</span>
</td>
</tr>
</table>
</li>
EOH;
