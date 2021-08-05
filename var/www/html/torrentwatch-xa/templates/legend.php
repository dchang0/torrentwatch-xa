<div id="legendDialog" class="dialog">
    <div class="dialogTitle">
        <a class="toggleDialog button titleClose" href="#"></a>
        Legend
    </div>
    <div class="dialog_window" id="show_legend">
        <ul id='torrentlist' class='show_legend'>
            <li class='legend st_notAMatch'>
                <span class='torrent_name'><b>Not a Match</b><br>Not a favorite nor in download cache. Okay to download manually.</span></li>
            <li class='legend st_ignoredFavBatch'>
                <span class='torrent_name'><b>Ignored Favorite Batch</b><br>Batch of favorites ignored by matching engine. Okay to download manually.</span></li>
            <li class='legend st_favTooOld'>
                <span class='torrent_name'><b>Old Favorite</b><br>Favorite matches but is not new enough in the series to trigger download.</span></li>
            <li class='legend st_favReady'>
                <span class='torrent_name'><b>Favorite Ready</b><br>Ready to download. Reload this page in your browser to start these.</span></li>
            <li class='legend st_waitTorCheck'>
                <span class='torrent_name'><b>Waiting</b><br>Waiting for torrent client.</span></li>
            <li class='legend st_downloading'>
                <span class='torrent_name'><b>Downloading</b><br>Item is downloading.</span></li>
            <li class='legend st_downloaded'>
                <span class='torrent_name'><b>Downloaded / Seeding</b><br>Item was just downloaded and is seeding in the torrent client.</span></li>
            <li class='legend st_inCacheNotActive'>
                <span class='torrent_name'><b>Previously Downloaded</b><br>Seen in download cache but no longer active in the torrent client.</span></li>
            <li class='legend tc_paused'>
                <span class='torrent_name'><b>Paused</b><br>Download paused; can be resumed.</span></li>
        </ul>
        <a class="toggleDialog button close" href="#">Close</a>
    </div>
</div>
