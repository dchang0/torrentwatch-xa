<div id="legendDialog" class="dialog">
    <div class="dialogTitle">
        <a class="toggleDialog button titleClose" href="#"></a>
        Legend
    </div>
    <div class="dialog_window" id="show_legend">
        <ul id='torrentlist' class='show_legend'>
            <li class='legend match_notAMatch'>
                <span class='torrent_name'><b>Not a Match</b><br>Not a favorite nor in download cache. Okay to download manually.</span></li>
            <li class='legend match_ignFavBatch'>
                <span class='torrent_name'><b>Ignored Favorite Batch</b><br>Batch of favorites ignored by matching engine. Okay to download manually.</span></li>
            <li class='legend match_favTooOld'>
                <span class='torrent_name'><b>Old Favorite</b><br>Favorite matches but is not new enough in the series to trigger download.</span></li>
            <!--<li class='legend match_favReady'>
                <span class='torrent_name'><b>Favorite Ready</b><br>Ready to download.  Reload this page in your browser to start these.</span></li>-->
            <li class='legend match_justStarted'>
                <span class='torrent_name'><b>Started</b><br>Torrent has just started downloading or is verifying just before starting.</span></li>
            <li class='legend match_waitTorCheck'>
                <span class='torrent_name'><b>Waiting</b><br>Waiting for data from torrent client.</span></li>
            <!--<li class='legend match_cachehit'>
                <span class='torrent_name'><b>(match_cachehit)</b><br>Not sure what this does yet.</span></li>-->
            <li class='legend match_downloading'>
                <span class='torrent_name'><b>Downloading<!-- (match_downloading)--></b><br>Item is downloading.</span></li>
            <li class='legend match_downloaded'>
                <span class='torrent_name'><b>Downloaded / Seeding<!-- (match_downloaded, match_transmission)--></b><br>Item was just downloaded and/or is seeding.</span></li>
            <!--<li class='legend match_duplicate'>
                <span class='torrent_name'><b>Previously Downloaded (match_duplicate)</b><br>Found in download cache. Will not be downloaded.</span></li>-->
            <li class='legend match_inCacheNotActive'>
                <span class='torrent_name'><b>Previously Downloaded</b><br>Seen in download cache but no longer active in the torrent client.</span></li>
            <li class='legend paused'>
                <span class='torrent_name'><b>Paused</b><br>Download paused; can be resumed.</span></li>
        </ul>
        <a class="toggleDialog button close" href="#">Close</a>
    </div>
</div>
