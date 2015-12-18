<div id="episodeDialog" class="dialog">
    <div class="dialogTitle">
        <a class="toggleDialog button titleClose" href="#"></a>
        <?=$ti?>
    </div>
    <div class="dialog_window" id="show_episode">
        <? if($isShow && $epiInfo):?>
        <h1><?=$name?>: <?=$episode_name?></h1>
        <table><tr>
                <?if(!empty($image)):?>
                <td width="350">
                    <img style="margin-right:5px;margin-bottom:5px;" width="350" src="<?=$image?>" />
                </td>
                <?endif;?>
                <td valign="top">
                    <strong>Episode:</strong> <?=$episode_num?>
                    <br />
                    <strong>Air Date:</strong> <?=$airdate?>
                    <br />
                    <strong>Show Rating:</strong> <?=$rating?>
                    <br />
                    <?if(!empty($directors)):?>
                    <strong>Director(s):</strong> <?=implode(', ', $directors)?>
                    <br />
                    <? endif; ?>
                    <?if(!empty($writers)):?>
                    <strong>Writer(s):</strong> <?=implode(', ', $writers)?>
                    <br />
                    <? endif; ?>
                    <?if(!empty($actors)):?>
                    <strong>Actors:</strong> <?=implode(', ', $actors)?>
                    <br />
                    <? endif; ?>
                    <?if(!empty($guests)):?>
                    <strong>Guests:</strong> <?=implode(', ', $guests)?>
                    <br /><br />
                    <? endif; ?>
                </td>
            </tr></table>
        <?if(!empty($text)):?>
        <span class="text"><span class="firstletter"><?=substr($text, 0, 1)?></span><?=substr($text, 1)?></span>
        <br />
        <?endif;?>
        <? elseif($isShow && !$epiInfo): ?>
        <h1><?=$name?></h1>
        <strong>No episode information available</strong>
        <br />
        <br />
        <strong>Rating:</strong> <?=$show->rating?>
        <br /><br />
        <?if(!empty($image)):?>
        <img style="float:left;margin-right:5px;margin-bottom:5px;" width="150" src="<?=$image?>" />
        <?endif;?>
        <?if(!empty($text)):?>
        <span class="text"><span class="firstletter"><?=substr($text, 0, 1)?></span><?=substr($text, 1)?></span>
        <br />
        <?endif;?>
        <? else: ?>
        <h1><?=$name?> Not found on TheTVDB.com</h1>
        <?endif;?>
        <a class="toggleDialog button close" href="#">Close</a>
    </div>
</div>
