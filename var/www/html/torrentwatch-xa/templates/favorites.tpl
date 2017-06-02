<div id="favDialog" class="dialog">
    <div class="dialogTitle">
        <a class="toggleDialog button titleClose" href="#"></a>
        Favorites
    </div>
    <div class="dialog_window" id="favorites">
        <div class="favorite">
            <ul class="favorite">
                <li><a href="#favorite_new">New Favorite</a></li>
                <?php if(isset($config_values['Favorites'])): ?>
                <?php foreach($config_values['Favorites'] as $key => $item): ?>
                <?php print("<li id=\"fav_" . $key . "\"><a href=\"#favorite_" . $key . "\">" . $item['Name'] . "</a></li>"); ?>
                <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
        <div class=favinfo>
            <?php display_favorites_info(
            [
            'Name' => '',
            'Filter' => '',
            'Not' => '',
            'Save In' => 'Default',
            'Episodes' => '',
            'Feed' => '',
            'Quality' => '',
            'seedRatio' => $config_values['Settings']['Default Seed Ratio']
            ],
            "new"); ?>
            <?php if(isset($config_values['Favorites']))
            array_walk($config_values['Favorites'], 'display_favorites_info'); ?>
            <div id="favClose" class="buttonContainer">
                <a class="toggleDialog button close" id="Close" href="#">Close</a>
            </div>
        </div>
    </div>
</div>
