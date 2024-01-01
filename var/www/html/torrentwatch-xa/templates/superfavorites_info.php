<form action="torrentwatch-xa.php?updateSuperFavorite=1"
<?php
print("class=\"superfavinfo\" id=\"superfavorite_" . $key . "\" ");
if (isset($style)) {
    echo $style;
}
?>>
    <input type="hidden" name="idx" id="superfavorite_<?php echo $key; ?>_idx" value="<?php echo $key; ?>">
    <div class="superfavorite_name">
        <div class="left">
            <label class="item" title="Name of the Super-Favorite">Name:</label>
        </div>
        <div class="right">
            <input type="text" class="text" name="name" id="superfavorite_<?php echo $key; ?>_name" value="<?php echo $item['Name']; ?>">
        </div>
    </div>
    <div class="superfavorite_filter">
        <div class="left">
            <label class="item" title="In RegEx Matching Style, use a PCRE Unicode regex such as .* to match all">Filter:</label>
        </div>
        <div class="right">
            <input type="text" class="text" name="filter" id="superfavorite_<?php echo $key; ?>_filter" value="<?php echo $item['Filter']; ?>">
        </div>
    </div>
    <div class="superfavorite_not">
        <div class="left">
            <label class="item" title="In RegEx Matching Style, use a PCRE Unicode regex to match items you don't want">Not:</label>
        </div>
        <div class="right">
            <input type="text" class="text" name="not" id="superfavorite_<?php echo $key; ?>_not" value="<?php echo $item['Not']; ?>"
                   title="Don't match titles with these words. You can add more than one word, separated by spaces">
        </div>
    </div>
    <!--<div class="superfavorite_downloaddir" id="superfavorite_downloaddir">
        <div class="left">
            <label class="item" title="Download Directory; overrides global Download Directory">Download Dir:</label>
        </div>
        <div class="right">
            <input type="text" class="text" name="downloaddir" id="superfavorite_<?php //echo $key; ?>_downloaddir" value="<?php //echo $item['Download Dir'];  ?>">
        </div>
    </div>-->
    <?php /* if ($config_values['Settings']['Client'] !== "folder" && $config_values['Settings']['Also Save Torrent Files']) { */ ?><!--<div class="superfavorite_alsosavedir" id="superfavorite_alsosavedir">
            <div class="left">
                <label class="item" title="Also Save Directory; overrides global default">Also Save Dir:</label>
            </div>
            <div class="right">
                <input type="text" class="text" name="alsosavedir" id="superfavorite_<?php //echo $key; ?>_alsosavedir" value="<?php //echo $item['Also Save Dir'];  ?>">
            </div>
        </div><?php //}  ?>-->
    <div class="superfavorite_episodes">
        <div class="left">
            <label class="item" title="Episode filter. ex.: 1x1-3x24 for Season 1 Episode 1 to Season 3 Episode 24. To just set one episode use: 2x10. You may use s01e12 instead of 1x12." >Episodes:</label>
        </div>
        <div class="right">
            <input type="text" class="text" name="episodes" id="superfavorite_<?php echo $key; ?>_episodes" title="Episodes filter. Please read the instructions on how to use this feature." value="<?php echo $item['Episodes']  ?>">
        </div>
    </div>
    <div class="superfavorite_feed">
        <div class="left">
            <label class="item" title="Feed to match against">Feed:</label>
        </div>
        <div class="right">
            <select name="feed" id="superfavorite_<?php echo $key; ?>_feed">
                <?php echo $feed_options; ?>
            </select>
        </div>
    </div>
    <div class="superfavorite_quality">
        <div class="left">
            <label class="item" title="Search for this quality in the full title">Quality:</label>
        </div>
        <div class="right">
            <input type="text" class="text" name="quality" id="superfavorite_<?php echo $key ?>_quality" value="<?php echo $item['Quality']; ?>">
        </div>
    </div>
    <!--<div class="superfavorite_seed" <?php /*
                  if ($config_values['Settings']['Client'] == "folder") {
                  echo 'style="display: none"';
                  } */
                ?>>
        <div class="left">
            <label class="seedratio item" title="Set maximum seed ratio till automatic pause (-1 = unlimited seeding till manually stopped)">Seed Ratio:</label>
        </div>
        <div class="right">
            <input type="text" class="seedratio text" <?php /*
      if ($config_values['Settings']['Client'] == "folder") {
      echo 'style="display: none"';
      } */
                ?> name="seedratio" id="superfavorite_<?php //echo $key; ?>_seedratio" value="<?php //echo getArrayValueByKey($item, 'seedRatio'); ?>">
        </div>
    </div>-->
    <div class="buttonContainer">
        <a class="submitForm button" id="Update" href="#superfavorite_<?php echo $key ?>">Update</a>
        <a class="submitForm button" id="Delete" href="#superfavorite_<?php echo $key ?>">Delete</a>
    </div>
</form>
