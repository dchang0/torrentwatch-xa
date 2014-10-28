<?php
if(isset($item['title'])) $utitle = preg_replace('/&shy;/', '', $item['title']);
if(isset($item['description'])) {
$description = $item['description'];
} else {
$description = '';
}
if(isset($item['pubDate'])) {
$pubDate = $item['pubDate'];
$unixTime = strtotime($item['pubDate']);
} else {
$pubDate = '';
}
if(!($torHash)) $torHash = '###torHash###';

if($config_values['Settings']['Combine Feeds'] == 1) {
$feedItem = "<span class=\"feed_name\">$feedName - </span>";
$combined = "combined";
}

if(isset($torInfo)) {
$stats = $torInfo['stats'];
$clientId = $torInfo['clientId'];
$infoDiv = "<div class='infoDiv'><span id='tor_$id' class='torInfo tor_$torHash'>$stats</span><span class='torEta'>$eta</span></div>";
if($torInfo['status'] == 4) $matched = "downloading";
} else if((!$config_values['Settings']['Disable Hide List']) && ($matched == "nomatch"))  {
$hideItem = "<div class='contextItem hideItem' onclick='$.hideItem(\"$utitle\")' title='Hide show'>Hide show</div>";
}

if($config_values['Settings']['Client'] != 'folder') $progressBar = "<div class='progressBarContainer init'><div class='progressDiv' style='width: 0.07%; height: 3px; '></div></div>";

if($matched == "downloading" || $matched == "downloaded" || $matched == "cachehit" || $matched == "match" ||  $torInfo['dlStatus'] == "to_check") { 
$hidden = ""; 
$dlTorrent = "dlTorrent hidden";
if ($torInfo['status'] == 16) {
$torStart = "torStart";
$torStop= "torStop hidden";
} else {
$torStart = "torStart hidden";
$torStop= "torStop";
}
} else {
$dlTorrent = "dlTorrent";
$torStart = "torStart hidden";
$torStop = "torStop hidden";
$hidden = "hidden";
} 

if(!isset($infoDiv)) $infoDiv = '';
if(!isset($hideItem)) $hideItem = '';
if(!isset($feedItem)) $feedItem = '';
if(!isset($torInfo)) $torInfo = '';
if(!isset($unixTime)) $unixTime = '';
if(!isset($pubDateClass)) $pubDateClass = '';

$guessed = detectMatch($utitle, TRUE); // $guessed is the normalized version of $guess
$showTitle = $guessed['key'];
$showQuality = $guessed['data'];
if($guess['episode'] != 'noShow') {
    if($guess['episode'] != 'fullSeason') {
        if($guess['episode'] != '') {
        //    $showEpisodeNumber = $guess['episode'] . "&nbsp;&nbsp;&nbsp;(" . $guessed['episode'] . ")" . "&nbsp;&nbsp;&nbsp;";
            $showEpisodeNumber = $guessed['episode'] . "&nbsp;&nbsp;&nbsp;";
        }
    }
    //$epiDiv = "<div class=\"contextItem episodeInfo\" onclick='javascript:$.episodeInfo(\"$utitle\")'>Episode Info</p></div>";
} else {
    $epiDiv = ''; //TODO used to be 'nothing' but set to ''; test if supposed to be 'nothing'
}

print <<< EOH

<li id=id_$id name=$id class="torrent match_$matched $alt item_$torHash">
<input type="hidden" class="title" value="$utitle" />
<input type="hidden" class="show_title" value="$showTitle" />
<input type="hidden" class="show_quality" value="$showQuality" />
<input type="hidden" class="link" value="$ulink" />
<input type="hidden" class="feed_link" value="$feed" />
<input type="hidden" class="client_id" value="$id" />

<table width="100%" cellspacing="0">
<tr>
<td class="identifier"></td>
<td class="torrent_name">
<div class='torrent_name'>
<span class="contextButton"><a id="contextButton_$id" class="contextButton" onclick='$.toggleContextMenu("#divContext_$id", "$id");'></a></span>
<span class='torrent_title' title="$description">$title</span>
<span class='torrent_pubDate'>$feedItem $showEpisodeNumber $pubDate</span>
</div>
<div id="divContext_$id" class="contextMenu">
<div class='contextItem addFavorite' onclick='javascript:$.addFavorite("$feed","$utitle")' title="Add this show to favorites">Add to favorites</div>
<div class='contextItem $dlTorrent' onclick='javascript:$.dlTorrent("$utitle","$ulink","$feed","$id")' title="Download this torrent">Download</div>
<div class="contextItem activeTorrent $torStart" onclick='javascript:$.stopStartTorrent("start", "$torHash")' title="Resume download">Resume transfer</div>
<div class="contextItem activeTorrent $torStop" onclick='javascript:$.stopStartTorrent("stop", "$torHash")' title="Pause download">Pause transfer</div>
<div class="contextItem activeTorrent delete $hidden" onclick='javascript:$.delTorrent("$torHash", "false")' title="Delete torrent but keep data">Remove from client</div>
<div class="contextItem activeTorrent trash $hidden" onclick='javascript:$.delTorrent("$torHash", "true")' title="Delete torrent and its data">Remove & trash data</div>
$hideItem
$epiDiv
</div>
$progressBar
$infoDiv
<span class='hidden' id=unixTime>$unixTime</span>
</td>
</tr>
</table>

</li>
EOH;
?>
