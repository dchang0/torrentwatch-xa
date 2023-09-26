<script type="text/javascript">
    // Hide Search -- By Text
    $("input#hideSearchText").on("keyup", function () {
        var hideSearchText = $(this).val().toLowerCase();
        $("#hideListContainer li").addClass('hidden').each(function () {
            if ($(this).find("span.hiddenItem").text().toLowerCase().match(hideSearchText)) {
                $(this).removeClass('hidden');
            }
        });
    });
    // binding for Test button on Trigger config tab
    $("a#testSMTPSettings").on("click",
            function () {
                $.get('torrentwatch-xa.php', {
                    sendTestEmail: 1,
                    fromName: $('input[name=fromName]').val(),
                    fromEmail: $('input[name=fromEmail]').val(),
                    toEmail: $('input[name=toEmail]').val(),
                    smtpServer: $('input[name=smtpServer]').val(),
                    smtpPort: $('input[name=smtpPort]').val(),
                    smtpAuthentication: $('select[name=smtpAuthentication]').val(),
                    smtpEncryption: $('select[name=smtpEncryption]').val(),
                    smtpUser: $('input[name=smtpUser]').val(),
                    smtpPassword: $('input[name=smtpPassword]').val(),
                    hELOOverride: $('input[name=hELOOverride]').val()
                }, function (response) {
                    $("div#smtpTestResult").html(response);
                }, 'html');
            });
</script>
<div id="configDialog" class="dialog">
    <div class="dialogTitle">
        <a class="toggleDialog button titleClose" href="#"></a>
        Configure
    </div>
    <div class="dialog_window" id="configuration">
        <div id="configTabs">
            <ul>
                <li id="tabInt" class="toggleConfigTab left selTab"
                    onclick='javascript:$.toggleConfigTab("#config_interface", "#tabInt");'>Interface
                </li>
                <li id="tabClient" class="toggleConfigTab"
                    onclick='javascript:$.toggleConfigTab("#config_torClient", "#tabClient");'>Client
                </li>
                <li id="tabTor" class="toggleConfigTab"
                    onclick='javascript:$.toggleConfigTab("#config_tor", "#tabTor");'>Torrent
                </li>
                <li id="tabFavs" class="toggleConfigTab"
                    onclick='javascript:$.toggleConfigTab("#config_favorites", "#tabFavs");'>Favorites
                </li>
                <li id="tabTrigger" class="toggleConfigTab"
                    onclick='javascript:$.toggleConfigTab("#config_trigger", "#tabTrigger");'>Trigger
                </li>
                <li id="tabFeeds" class="toggleConfigTab"
                    onclick='javascript:$.toggleConfigTab("#config_feeds", "#tabFeeds");'>Feeds
                </li>
                <li id="tabHideList" class="toggleConfigTab right"
                    onclick='javascript:$.toggleConfigTab("#config_hideList", "#tabHideList");'>Hide List
                </li>
            </ul>
        </div>
        <div class="config_form_container">
            <form action="torrentwatch-xa.php?setGlobals=1" id="config_form" name="config_form">
                <div id="config_interface" class="configTab">
                    <div class="int_settings">
                        <div id="config_combinefeeds" title="Combine all feeds into one list.">
                            <div class="left">
                                <label class="item checkbox">Combine Feeds:</label>
                            </div>
                            <div class="right">
                                <input type="checkbox" name="combinefeeds" value="1" <?php echo $combinefeeds; ?> />
                            </div>
                        </div>
                        <div id="config_disable_hidelist">
                            <div class="left">
                                <label class="item checkbox">Disable Hide List:</label>
                            </div>
                            <div class="right">
                                <input type="checkbox" name="dishidelist" value="1" <?php echo $dishidelist; ?> />
                            </div>
                        </div>
                        <div id="config_show_debug" title="Show season and episode detection engine's 'debugMatch' and 'show_title' values for each item in feed list.">
                            <div class="left">
                                <label class="item checkbox">Show Item Debug Info:</label>
                            </div>
                            <div class="right">
                                <input type="checkbox" name="showdebug" value="1" <?php echo $showdebug; ?> />
                            </div>
                        </div>
                        <div id="config_check_updates">
                            <div class="left">
                                <label class="item checkbox">Check for Updates:</label>
                            </div>
                            <div class="right">
                                <input type="checkbox" name="checkversion" value="1" <?php echo $checkversion; ?> />
                            </div>
                        </div>
                        <div id="tz" title="Set your Time Zone (Default UTC). See http://php.net/manual/en/timezones.php for a list of supported timezones. Change won't appear to take effect in feed lists until next feed cache refresh.">
                            <div class="left">
                                <label class="item">Time Zone:</label>
                            </div>
                            <div class="right">
                                <input type="text" name="tz" class="text"
                                       value="<?php echo $config_values['Settings']['Time Zone']; ?>"/>
                            </div>
                        </div>
                        <div id="config_log_level">
                            <div class="left">
                                <label class="item select">Log Level:</label>
                            </div>
                            <div class="right">
                                <select name="loglevel">
                                    <!--<option value="-1" <?php echo $loglevelalert; ?>>ALERT</option>-->
                                    <option value="0" <?php echo $loglevelerror; ?>>ERROR</option>
                                    <option value="1" <?php echo $loglevelinfo; ?>>INFO</option>
                                    <option value="2" <?php echo $logleveldebug; ?>>DEBUG</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="config_torClient" class="configTab hidden">
                    <div class="tor_client_settings">
                        <div id="config_torrentclient" title="Choose a torrent client or local folder.">
                            <div class="left">
                                <label class="item select">Client:</label>
                            </div>
                            <div class="right">
                                <select name="client" id="client" onchange="changeClient(this.options[this.selectedIndex].value);">
                                    <option value="Transmission" <?php echo $transmission; ?>>Transmission</option>
                                    <option value="folder" <?php echo $folderclient; ?>>Save .torrent/magnet: Files In Folder</option>
                                </select>
                            </div>
                        </div>
                        <div id="config_downloaddir" title="Default directory to start items in; can be overwritten by Favorite's Download Dir">
                            <div class="left">
                                <label class="item textinput">Download Dir:</label>
                            </div>
                            <div class="right">
                                <input type="text" class="text" name="downdir" value="<?php echo $config_values['Settings']['Download Dir']; ?>"/>
                            </div>
                        </div>
                        <div id="config_tr_host" title="Hostname">
                            <div class="left">
                                <label class="item textinput">Hostname:</label>
                            </div>
                            <div class="right">
                                <input type="text" class="text" name="trhost" value="<?php echo $config_values['Settings']['Transmission Host']; ?>"/>
                            </div>
                        </div>
                        <div id="config_tr_port" title="Port">
                            <div class="left">
                                <label class="item textinput">Port:</label>
                            </div>
                            <div class="right">
                                <input type="text" class="text" name="trport" value="<?php echo $config_values['Settings']['Transmission Port']; ?>"/>
                            </div>
                        </div>
                        <div id="config_tr_user" title="Username">
                            <div class="left">
                                <label class="item textinput">Username:</label>
                            </div>
                            <div class="right">
                                <input type="text" class="text" name="truser" value="<?php echo $config_values['Settings']['Transmission Login']; ?>"/>
                            </div>
                        </div>
                        <div id="config_tr_password" title="Password">
                            <div class="left">
                                <label class="item textinput">Password:</label>
                            </div>
                            <div class="right">
                                <input type="password" class="password" name="trpass" value="<?php echo $config_values['Settings']['Transmission Password']; ?>"/>
                            </div>
                        </div>
                        <div id="config_alsosavetorrentfiles" title="Also save .torrent files or magnet: links as files to 'Also Save Dir.'">
                            <div class="left">
                                <label class="item checkbox">Also Save .torrent/magnet: Files:</label>
                            </div>
                            <div class="right">
                                <input type="checkbox" name="alsosavetorrentfiles" value="1" <?php echo $alsosavetorrentfiles; ?> />
                            </div>
                        </div>
                        <div id="config_alsosavedir" title="Directory to save .torrent files in; can be overridden by Favorite's 'Also Save Dir'">
                            <div class="left">
                                <label class="item textinput">Also Save Dir:</label>
                            </div>
                            <div class="right">
                                <input type="text" class="text" name="alsosavedir" value="<?php echo $config_values['Settings']['Also Save Dir']; ?>"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="config_tor" class="configTab hidden">
                    <div class="tor_settings">
                        <div id="config_deepdir" title="Save downloads in multi-directory structure.">
                            <div class="left">
                                <label class="item select">Deep Directories:</label>
                            </div>
                            <div class="right">
                                <select name="deepdir">
                                    <option value="Title" <?php echo $deeptitle; ?>>Show Title</option>
                                    <option value="Title_Season" <?php echo $deepTitleSeason; ?>>Show Title and Season</option>
                                    <option value="0" <?php echo $deepoff; ?>>Off</option>
                                </select>
                            </div>
                        </div>
                        <div id="default_ratio">
                            <div class="left">
                                <label class="item textinput">Default Seed Ratio:</label>
                            </div>
                            <div class="right">
                                <input type="text" class="text" name="defaultratio" value="<?php echo $config_values['Settings']['Default Seed Ratio']; ?>"/>
                            </div>
                        </div>
                        <div id="config_autodel" title="Automatically delete completely downloaded and seeded torrents from torrent client (does not trash the torrent's contents).">
                            <div class="left">
                                <label class="item checkbox">Auto-Del Seeded Torrents:</label>
                            </div>
                            <div class="right">
                                <input type="checkbox" name="autodel" value="1" <?php echo $autodel; ?> />
                            </div>
                        </div>
                    </div>
                </div>
                <div id="config_favorites" class="configTab hidden">
                    <div class="fav_settings">
                        <div title="Enable Super-Favorites">
                            <div class="left">
                                <label class="item checkbox">Enable Super-Favorites:</label>
                            </div>
                            <div class="right">
                                <input type="checkbox" name="enablesuperfavorites" value="1" <?php echo $enablesuperfavorites; ?> />
                            </div>
                        </div>
                        <div id="config_matchstyle" title="Type of text-matching to use">
                            <div class="left">
                                <label class="item select">Matching Style:</label>
                            </div>
                            <div class="right">
                                <select name="matchstyle">
                                    <option value="regexp" <?php echo $matchregexp; ?>>RegExp</option>
                                    <option value="glob" <?php echo $matchglob; ?>>Glob</option>
                                    <option value="simple" <?php echo $matchsimple; ?>>Simple</option>
                                </select>
                            </div>
                        </div>
                        <div title="Set feed to All when adding favorites. (This doesn't affect existing favorites.)">
                            <div class="left">
                                <label class="item checkbox">Set Default Feed to "All":</label>
                            </div>
                            <div class="right">
                                <input type="checkbox" name="favdefaultall" value="1" <?php echo $favdefaultall; ?> />
                            </div>
                        </div>
                        <div id="config_require_epi_info" title="When enabled only shows with episode information (S01E12, 1x12, etc... ) will be matched.">
                            <div class="left">
                                <label class="item checkbox">Require Episode Info:</label>
                            </div>
                            <div class="right">
                                <input type="checkbox" name="require_epi_info" value="1" <?php echo $require_epi_info; ?> />
                            </div>
                        </div>
                        <div>
                            <div class="left">
                                <label class="item checkbox">Newer Episodes Only:</label>
                            </div>
                            <div class="right">
                                <input type="checkbox" name="onlynewer" value="1" <?php echo $onlynewer; ?> />
                            </div>
                        </div>
                        <div>
                            <div class="left" title="Download later versions of items, even if first version is already downloaded.">
                                <label class="item checkbox">Download Versions &gt;1:</label>
                            </div>
                            <div class="right">
                                <input type="checkbox" name="fetchversions" value="1" <?php echo $fetchversions; ?> />
                            </div>
                        </div>
                        <div>
                            <div class="left">
                                <label class="item checkbox">Ignore Batches:</label>
                            </div>
                            <div class="right">
                                <input type="checkbox" name="ignorebatches" value="1" <?php echo $ignorebatches; ?> />
                            </div>
                        </div>
                        <div>
                            <div class="left">
                                <label class="item checkbox">New <b>Add to Favorites</b> Get:</label>
                            </div>
                            <div class="right">
                                <select name="resolutionsonly">
                                    <option value="all" <?php echo $resoallqualities; ?>>All Detected Resolutions and Qualities</option>
                                    <option value="yes" <?php echo $resoresolutionsonly; ?>>Detected Resolutions Only</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="config_trigger" class="configTab hidden">
                    <div class="trigger_settings">
                        <div id="enableScript">
                            <div class="left">
                                <label class="item checkbox">Enable Script:</label>
                            </div>
                            <div class="right">
                                <input type="checkbox" name="enableScript" value="1" <?php echo $enableScript; ?> />
                            </div>
                        </div>
                        <div id="script" title="Full path to script to run on certain events--must have executable permissions by process owner">
                            <div class="left">
                                <label class="item">Script:</label>
                            </div>
                            <div class="right">
                                <input type="text" name="script" class="text"
                                       value="<?php echo $config_values['Settings']['Script']; ?>"/>
                            </div>
                        </div>
                        <div id="enableSMTP">
                            <div class="left">
                                <label class="item checkbox">SMTP Notifications:</label>
                            </div>
                            <div class="right">
                                <input type="checkbox" name="enableSMTP" value="1" <?php echo $enableSMTP; ?> />
                            </div>
                        </div>
                        <div id="from_email" title="Valid email address required">
                            <div class="left">
                                <label class="item">From: Email:</label>
                            </div>
                            <div class="right">
                                <input type="text" name="fromEmail" class="text" value="<?php echo $config_values['Settings']['From Email']; ?>"/>
                            </div>
                        </div>
                        <div id="from_name" title="Optional From Name or Display Name">
                            <div class="left">
                                <label class="item">From: Name:</label>
                            </div>
                            <div class="right">
                                <input type="text" name="fromName" class="text" value="<?php echo $config_values['Settings']['From Name']; ?>"/>
                            </div>
                        </div>
                        <div id="to_email" title="Valid email address required">
                            <div class="left">
                                <label class="item">To: Email:</label>
                            </div>
                            <div class="right">
                                <input type="text" name="toEmail" class="text" value="<?php echo $config_values['Settings']['To Email']; ?>"/>
                            </div>
                        </div>
                        <div id="smtp_server" title="Valid FQDN or IP address of SMTP server required">
                            <div class="left">
                                <label class="item">SMTP Server:</label>
                            </div>
                            <div class="right">
                                <input type="text" name="smtpServer" class="text" value="<?php echo $config_values['Settings']['SMTP Server']; ?>"/>
                            </div>
                        </div>
                        <div id="smtp_port" title="Leave blank to default to 25 or specify integer from 0-65535.">
                            <div class="left">
                                <label class="item">SMTP Port:</label>
                            </div>
                            <div class="right">
                                <input type="text" name="smtpPort" class="text" value="<?php echo $config_values['Settings']['SMTP Port']; ?>"/>
                            </div>
                        </div>
                        <div id="smtp_authentication" title="Only PLAIN or LOGIN are supported, not NTLM, OAUTH etc.">
                            <div class="left">
                                <label class="item">SMTP Authentication:</label>
                            </div>
                            <div class="right">
                                <select name="smtpAuthentication" id="smtpAuthentication">
                                    <option value="None" <?php echo $smtpAuthNone; ?>>None</option>
                                    <option value="PLAIN" <?php echo $smtpAuthPLAIN; ?>>PLAIN</option>
                                    <option value="LOGIN" <?php echo $smtpAuthLOGIN; ?>>LOGIN</option>
                                </select>
                            </div>
                        </div>
                        <div id="smtp_encryption">
                            <div class="left">
                                <label class="item">SMTP Encryption:</label>
                            </div>
                            <div class="right">
                                <select name="smtpEncryption" id="smtpEncryption">
                                    <option value="None" <?php echo $smtpEncNone; ?>>None</option>
                                    <option value="TLS" <?php echo $smtpEncTLS; ?>>STARTTLS or TLS</option>
                                    <option value="SSL" <?php echo $smtpEncSSL; ?>>SSL</option>
                                </select>
                            </div>
                        </div>
                        <div id="smtp_user" title="Required for SMTP Authentication">
                            <div class="left">
                                <label class="item">SMTP User:</label>
                            </div>
                            <div class="right">
                                <input type="text" name="smtpUser" class="text" value="<?php echo $config_values['Settings']['SMTP User']; ?>"/>
                            </div>
                        </div>
                        <div id="smtp_password" title="Required for SMTP Authentication">
                            <div class="left">
                                <label class="item">SMTP Password:</label>
                            </div>
                            <div class="right">
                                <input type="password" class="password" name="smtpPassword" class="text" value="<?php echo $config_values['Settings']['SMTP Password']; ?>"/>
                            </div>
                        </div>
                        <div id="helo_override" title="Optional HELO override; leave blank for default. If provided, must be a valid FQDN">
                            <div class="left">
                                <label class="item">HELO Override:</label>
                            </div>
                            <div class="right">
                                <input type="text" class="text" name="hELOOverride" class="text" value="<?php echo $config_values['Settings']['HELO Override']; ?>"/>
                            </div>
                        </div>
                        <div id="smtp_test" title="Tests current SMTP settings">
                            <div class="left smtpTestLeft">
                                <a href="#" id="testSMTPSettings" class="button">Test</a>
                            </div>
                            <div class="right smtpTestRight">
                                <div id="smtpTestResult"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="buttonContainer">
                    <a class="submitForm button" id="Save" href="#" name="Save">Save</a>
                </div>
                <div class="buttonContainer linkButtons">
                    <a class='toggleDialog button close' href='#'>Close</a>
                </div>
            </form>
            <form action="torrentwatch-xa.php?updateFeed=1" class="feedform">
                <div id="config_feeds" class="configTab hidden">
                    <div id="feedItemTitles">
                        <div class="feedURLName"><span id="feedURLNameLabel">URL / Name</span></div>
                        <div class="feedOn">On</div>
                        <div class="seedRatio">Ratio</div>
                        <div class="feedDelete">Delete</div>
                    </div>
                    <div id="feedItems">
                        <?php if (isset($config_values['Feeds'])): ?>
                            <?php foreach ($config_values['Feeds'] as $key => $feed): ?>
                                <?php print("<div id=\"feedItem_" . $key . "\" class=\"feeditem\">"); ?>
                                <div class="feedURLName">
                                    <input class="feed_url" type="text" name="feed_url_<?php echo $key; ?>" title="Feed URL" value="<?php echo $feed['Link']; ?>" placeholder="Feed URL (required)"></input>
                                    <input class="feed_name" type="text" name="feed_name_<?php echo $key; ?>" title="Feed Name (optional)" value="<?php echo $feed['Name']; ?>" placeholder="Feed Name (leave blank for official feed name)"></input>
                                    <input class="feed_website" type="text" name="feed_website_<?php echo $key; ?>" title="Feed Website URL (optional)" value="<?php echo $feed['Website']; ?>" placeholder="Feed Website URL (leave blank for official feed website)"></input>
                                </div>
                                <div class="feedOn">
                                    <?php if ($feed['enabled'] == 1): ?>
                                        <input class="feed_on_off" type="checkbox" name="feed_on_<?php echo $key; ?>" value="feed_on" checked></input>
                                    <?php else: ?>
                                        <input class="feed_on_off" type="checkbox" name="feed_on_<?php echo $key; ?>" value="feed_on"></input>
                                    <?php endif; ?>
                                </div>
                                <div class="seedRatio">
                                    <input class="seed_ratio" type="text" name="seed_ratio_<?php echo $key; ?>" title="Set default seed ratio for this feed." value="<?php echo $feed['seedRatio']; ?>"></input>
                                </div>
                                <div class="feedDelete">
                                    <input class="feed_delete" type="checkbox" name="feed_delete_<?php echo $key; ?>" value="feed_delete"></input>
                                </div>
                                <?php print("</div>"); ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <div id="feedItem_new" class="feeditem">
                            <div class="feedURLName">
                                <input class="feed_url" type="text" name="feed_url_new" title="Add new feed URL..." placeholder="New Feed URL (required to add feed)"></input>
                                <input class="feed_name" type="text" name="feed_name_new" title="Override new feed name or leave blank for official feed name..." placeholder="New Feed Name (optional)"></input>
                                <input class="feed_website" type="text" name="feed_website_new" title="Override new feed website or leave blank for official feed website..." placeholder="New Feed Website URL (optional)"></input>
                            </div>
                            <div class="feedOn">
                                <input class="feed_on_off" type="checkbox" name="feed_on_new" value="feed_on" checked></input>
                            </div>
                            <div class="seedRatio">
                                <input class="seed_ratio" type="text" name="seed_ratio_new" title="Set default seed ratio for this feed."></input>
                            </div>
                            <div class="feedDelete">
                            </div>
                        </div>
                    </div>
                    <div class="buttonContainer">
                        <a class="submitForm button" id="Save" href="#" name="Save">Save</a>
                    </div>
                    <div class="buttonContainer linkButtons">
                        <a class='toggleDialog button close' href='#'>Close</a>
                    </div>
            </form>
        </div>
        <form action="torrentwatch-xa.php?delHidden=1" id="hidelist_form" name="hidelist_form" class="hidden">
            <div id="config_hideList" class="hidden configTab">
                <?php if ($config_values['Settings']['Disable Hide List']): ?>
                    <div style='border-bottom: 1px solid #979797'><span class="hiddenItem">&nbsp;Hide List is disabled: entries below are ignored.</span></div>
                <?php endif; ?>
                <div id="hideListContainer">
                    <ul class="hidelist">
                        <?php if ($config_values['Hidden']): ?>
                            <?php ksort($config_values['Hidden'], SORT_STRING); ?>
                            <?php foreach ($config_values['Hidden'] as $key => $item): ?>
                                <li>
                                    <label class="item checkbox">
                                        <input type="checkbox" name="unhide[]" value="<?= $key ?>"/>
                                        <span class="hiddenItem"><?php echo $item; ?></span></label>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>&nbsp;Hide List is empty.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div id="hideSearch">
                <input type="text" id="hideSearchText" placeholder="Filter" name="hideSearch"></input>
            </div>
            <div class="buttonContainer">
                <a class="submitForm button" id="Unhide" href="#">Unhide</a>
            </div>
            <div class="buttonContainer linkButtons">
                <a class='toggleDialog button close' href='#'>Close</a>
            </div>
        </form>
    </div>
</div>
