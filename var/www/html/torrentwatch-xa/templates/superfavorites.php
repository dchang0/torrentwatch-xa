<div id="superfavDialog" class="dialog">
    <div class="dialogTitle">
        <a class="toggleDialog button titleClose" href="#"></a>
        Super-Favorites
        <?php if (empty($config_values['Settings']['Enable Super-Favorites'])) { print("- Globally Disabled"); } ?>
    </div>
    <div class="dialog_window" id="superfavorites">
        <div class="superfavorite">
            <ul class="superfavorite" id="superfavoriteList">
                <li><a href="#superfavorite_new">New Super-Favorite</a></li>
                <?php if (isset($config_values['Super-Favorites'])): ?>
                    <?php foreach ($config_values['Super-Favorites'] as $key => $item): ?>
                        <?php print("<li id=\"superfav_" . $key . "\"><a id=\"superfav_" . $key . "_anchor\" href=\"#superfavorite_" . $key . "\">" . $item['Name'] . "</a></li>"); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
        <div class=superfavinfo id="superfavInfo">
            <?php
            display_superfavorites_info(
                    [
                        'Name' => '',
                        'Filter' => '',
                        'Not' => '',
                        //'Download Dir' => '',
                        //'Also Save Dir' => '',
                        //'Episodes' => '',
                        'Feed' => '',
                        'Quality' => '',
                    //'seedRatio' => ''
                    ],
                    "new");
            ?>
            <?php
            if (isset($config_values['Super-Favorites'])) {
                foreach ($config_values['Super-Favorites'] as $superfavKey => $superfavValue) {
                    display_superfavorites_info($config_values['Super-Favorites'][$superfavKey], $superfavKey);
                }
            }
            ?>
            <div id="superfavClose" class="buttonContainer">
                <a class="toggleDialog button close" id="Close" href="#">Close</a>
            </div>
        </div>
    </div>
</div>
