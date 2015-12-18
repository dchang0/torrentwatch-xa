<?php
// variables
//TODO figure out if NULL or '' is better for logic
$utitle = NULL; // to contain HTML code of un-soft-hyphenated title of the item
$description = NULL; // to contain HTML code of description of the item
$infoDiv = NULL; // to contain HTML code of each item's div.infoDiv
$hideItem = NULL; // to contain HTML code of div for Hide Show button on drop-down menu
$epiDiv = NULL; // to contain HTML code of div for Episode Info
$progressBar = NULL; // to contain HTML code of div.progressBarContainer
$feedItem = NULL; // to contain HTML code of span containing the feed name if Combine Feeds = TRUE
$showEpisodeNumber = NULL; // to contain HTML code of the show episode number, etc.
$pubDate = NULL; // to contain publication date of the item
$unixTime = NULL; // to contain UNIX timestamp

//TODO improve passing of $id into this file
//TODO improve passing of $torInfo into this file
//TODO figure out what $alt does and improve its passing into this file
//TODO improve passing of $ulink into this file
//TODO improve passing of $feed into this file

if(isset($item['title'])) {
    $utitle = preg_replace('/&shy;/', '', $item['title']);
}

if(isset($item['description'])) {
    $description = $item['description'];
}

if(isset($item['pubDate'])) {
    $pubDate = $item['pubDate'];
    $unixTime = strtotime($item['pubDate']);
}

if(!($torHash)) { //TODO improve passing of $torHash into this file
    $torHash = '###torHash###';
}

if($config_values['Settings']['Combine Feeds'] == 1) {
    $feedItem = "<span class=\"feed_name\">$feedName - </span>";
}

if($torInfo['dlStatus'] != '') { //TODO is this the best key to check out of $torInfo as to whether to show infoDiv?
//print_r($torInfo); //TODO remove me
    $stats = $torInfo['stats'];
    $infoDiv = "<div class='infoDiv'><span id='tor_$id' class='torInfo tor_$torHash'>$stats</span><span class='torEta'></span></div>";
    if($torInfo['status'] == 4) { //TODO figure out why 'status' is undefined
        $matched = "downloading"; //TODO $matched seems to never get set to "downloading" because 'status' is undefined
    }
}
else if((!$config_values['Settings']['Disable Hide List']) && ($matched == "nomatch"))  {
    $hideItem = "<div class='contextItem hideItem' onclick='$.hideItem(\"$utitle\")' title='Hide show'>Hide show</div>"; // adds Hide Show button to drop-down menu
}

if($config_values['Settings']['Client'] != 'folder') {
    $progressBar = "<div class='progressBarContainer init'><div class='progressDiv' style='width: 0.07%; height: 3px; '></div></div>";
}

// hide or show choices in contextMenu
if($matched == "downloading" || $matched == "downloaded" || $matched == "cachehit" || $matched == "match" || $torInfo['dlStatus'] == "to_check") {
    $dlTorrent = "dlTorrent hidden";
    if ($torInfo['status'] == 16) { //TODO figure out why 'status' is undefined
        $torStart = "torStart";
        $torPause = "torPause hidden";
    } 
    else {
        $torStart = "torStart hidden";
        $torPause = "torPause";
    }
    $torDelete = "torDelete";
    $torTrash = "torTrash";
} else {
    $dlTorrent = "dlTorrent";
    $torStart = "torStart hidden";
    $torPause = "torPause hidden";
    $torDelete = "torDelete hidden";
    $torTrash = "torTrash hidden";
}

$guessed = detectMatch($utitle, TRUE); // $guessed is the normalized version of $guess
$showTitle = $guessed['title'];
$showQuality = $guessed['qualities'];
$debugMatch = $guessed['debugMatch'];

if($guess['episode'] != 'noShow') {
    if($guess['episode'] != 'fullSeason') {
        if($guess['episode'] != '') {
        //    $showEpisodeNumber = $guess['episode'] . "&nbsp;&nbsp;&nbsp;(" . $guessed['episode'] . ")" . "&nbsp;&nbsp;&nbsp;";
        $showEpisodeNumber = $showEpisodeNumber . "<b>" . $guessed['episode'] . "</b>&nbsp;&nbsp;&nbsp;";
        }
    }
    //$epiDiv = "<div class=\"contextItem episodeInfo\" onclick='javascript:$.episodeInfo(\"$utitle\")'>Episode Info</p></div>"; //TODO part of tvDB
}

//TODO what does 'client_id" below do if it is the same as $id?

print <<< EOH
<li id=id_$id name=$id class="torrent match_$matched $alt item_$torHash">
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
<span class="contextButton"><a id="contextButton_$id" class="contextButton" onclick='$.toggleContextMenu("#divContext_$id", "$id");'></a></span>
<span class='torrent_title' title="$description">$ti</span>
<span class='torrent_pubDate'>$feedItem $showEpisodeNumber $pubDate</span>
</div>
<div id="divContext_$id" class="contextMenu">
<div class='contextItem addFavorite' onclick='javascript:$.addFavorite("$feed","$utitle")' title="Add this show to favorites">Add to favorites</div>
<div class='contextItem $dlTorrent' onclick='javascript:$.dlTorrent("$utitle","$ulink","$feed","$id")' title="Download this torrent">Download</div>
<div class="contextItem activeTorrent $torStart" onclick='javascript:$.stopStartTorrent("start", "$torHash")' title="Resume download">Resume transfer</div>
<div class="contextItem activeTorrent $torPause" onclick='javascript:$.stopStartTorrent("stop", "$torHash")' title="Pause download">Pause transfer</div>
<div class="contextItem activeTorrent $torDelete" onclick='javascript:$.delTorrent("$torHash", false)' title="Delete torrent but keep data">Remove from client</div>
<div class="contextItem activeTorrent $torTrash" onclick='javascript:$.delTorrent("$torHash", true)' title="Delete torrent and its data">Remove & trash data</div>
$hideItem
$epiDiv
</div>
$progressBar
$infoDiv
<span class='hidden' id='debugMatch'>$debugMatch</span>
<span class='hidden' id='unixTime'>$unixTime</span>
</td>
</tr>
</table>
</li>
EOH;
