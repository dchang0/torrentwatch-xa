<div id="historyDialog" class="dialog">
    <div class="dialogTitle">
        <a class="toggleDialog button titleClose" href="#"></a>
        History
    </div>
    <div class="dialog_window" id="history">
        <ul id="historyItems">
            <?php foreach($history as $item): ?>
            <li><?php echo $item['Date'].' - '.$item['Title']; ?></li>
            <?php endforeach; ?>
        </ul>
            <div class="buttonContainer">
            <a class="button toggleDialog close" href="#">Close</a>
            <a class="button" id="clearhistory" href="torrentwatch-xa.php?clearHistory=1">Clear</a>
        </div>
    </div>
</div>
