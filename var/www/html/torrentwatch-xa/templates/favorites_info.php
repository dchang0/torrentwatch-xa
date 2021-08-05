<form action="torrentwatch-xa.php?updateFavorite=1"
      <?php print("class=\"favinfo\" id=\"favorite_" . $key . "\" ");
      if(isset($style)) {
      echo $style;
      }?>>
    <!--<input type="hidden" name="idx" id="idx" value="<?php echo $key; ?>">-->
    <input type="hidden" name="idx" id="<?php echo 'idx_' . $key; ?>" value="<?php echo $key; ?>">
    <div class="favorite_name">
        <div class="left">
            <label class="item" title="Name of the Favorite">Name:</label>
        </div>
        <div class="right">
            <input type="text" class="text" name="name" value="<?php echo $item['Name']; ?>">
        </div>
    </div>
    <div class="favorite_filter">
        <div class="left">
            <label class="item" title="In RegEx Matching Style, use a PCRE Unicode regex such as .* to match all">Filter:</label>
        </div>
        <div class="right">
            <input type="text" class="text" name="filter" value="<?php echo $item['Filter']; ?>">
        </div>
    </div>
    <div class="favorite_not">
        <div class="left">
            <label class="item" title="In RegEx Matching Style, use a PCRE Unicode regex to match items you don't want">Not:</label>
        </div>
        <div class="right">
            <input type="text" class="text" name="not" value="<?php echo $item['Not']; ?>"
                   title="Don't match titles with these words. You can add more than one word, separated by spaces">
        </div>
    </div>
    <div class="favorite_downloaddir" id="favorite_downloaddir">
        <div class="left">
            <label class="item" title="Download Directory; overrides global Download Directory">Download Dir:</label>
        </div>
        <div class="right">
            <input type="text" class="text" name="downloaddir" value="<?php echo $item['Download Dir']; ?>">
        </div>
    </div>
    <?php if($config_values['Settings']['Client'] !== "folder" && $config_values['Settings']['Also Save Torrent Files']) { ?><div class="favorite_alsosavedir" id="favorite_alsosavedir">
        <div class="left">
            <label class="item" title="Also Save Directory; overrides global default">Also Save Dir:</label>
        </div>
        <div class="right">
            <input type="text" class="text" name="alsosavedir" value="<?php echo $item['Also Save Dir']; ?>">
        </div>
    </div><?php } ?>
    <div class="favorite_episodes">
        <div class="left">
            <label class="item" title="Episode filter. ex.: 1x1-3x24 for Season 1 Episode 1 to Season 3 Episode 24. To just set a starting point use: 2x10. You may use s01e12 instead of 1x12." >Episodes:</label>
        </div>
        <div class="right">
            <input type="text" class="text" name="episodes" title="Episodes filter. Please read the instructions on how to use this feature." value="<?php echo $item['Episodes'] ?>">
        </div>
    </div>
    <div class="favorite_feed">
        <div class="left">
            <label class="item" title="Feed to match against">Feed:</label>
        </div>
        <div class="right">
            <select name="feed">
                <?php echo $feed_options; ?>
            </select>
        </div>
    </div>
    <div class="favorite_quality">
        <div class="left">
            <label class="item" title="Search for this quality in the full title">Quality:</label>
        </div>
        <div class="right">
            <input type="text" class="text" name="quality" value="<?php echo $item['Quality']; ?>">
        </div>
    </div>
    <div class="favorite_seed_and_episode">
        <div class="left" <?php if($config_values['Settings']['Client'] == "folder") { echo 'style="display: none"'; } ?>>
            <label class="seedratio item" title="Set maximum seed ratio till automatic pause (-1 = unlimited seeding till manually stopped)">Seed Ratio:</label>
        </div>
        <div class="right">
            <input type="text" class="seedratio text" <?php if($config_values['Settings']['Client'] == "folder") { echo 'style="display: none"'; } ?> name="seedratio" value="<?php echo getArrayValueByKey($item, 'seedRatio'); ?>">
            <label class="lastSeason item" title="SSxEE or YYYYMMDD notation only" <?php if($config_values['Settings']['Client'] == "folder") { echo 'style="padding-left: 137px"'; } ?>>Last Download:</label>
            <?php if(isset($item['Episode']) && $item['Season'] !== 0 && !preg_match('/^(\d{8})$/', $item['Episode'])) { ?>
            <input class='lastSeason text' type="text" name="season" value="<?php echo $item['Season']; ?>">
            <label class="lastEpisode item">x</label>
            <input class='lastEpisode text' type="text" name="episode" value="<?php echo $item['Episode']; ?>">
            <?php } else { ?>
            <input class='lastEpisode text' type="text" name="episode" value="<?php if(isset($item['Episode'])) echo $item['Episode']; ?>">
            <?php } ?>
        </div>
    </div>
    <div class="buttonContainer">
        <a class="submitForm button" id="Update" href="#">Update</a>
        <a class="submitForm button" id="Delete" href="#favorite_<?php echo $key ?>">Delete</a>
    </div>
</form>
