<script type="text/javascript" src="javascript/configure.js"></script>
<div id="configDialog" class="dialog">
    <div class="dialogTitle">
        <a class="toggleDialog button titleClose" href="#"></a>
        Configure
    </div>
    <div class="dialog_window" id="configuration">
        <div id="configTabs">
            <ul>
                <li id="tabInt" class="toggleConfigTab left selTab"
                    onclick='javascript:$.toggleConfigTab("#config_interface", "#tabInt")'>Interface
                </li>
                <li id="tabClient" class="toggleConfigTab"
                    onclick='javascript:$.toggleConfigTab("#config_torClient", "#tabClient")'>Client
                </li>
                <li id="tabTor" class="toggleConfigTab"
                    onclick='javascript:$.toggleConfigTab("#config_tor", "#tabTor")'>Torrent
                </li>
                <li id="tabFavs" class="toggleConfigTab"
                    onclick='javascript:$.toggleConfigTab("#config_favorites", "#tabFavs")'>Favorites
                </li>
                <li id="tabFeeds" class="toggleConfigTab"
                    onclick='javascript:$.toggleConfigTab("#config_feeds", "#tabFeeds")'>Feeds
                </li>
                <li id="tabHideList" class="toggleConfigTab"
                    onclick='javascript:$.toggleConfigTab("#config_hideList", "#tabHideList")'>Hide List
                </li>
                <li id="tabOthers" class="toggleConfigTab right"
                    onclick='javascript:$.toggleConfigTab("#config_other", "#tabOthers")'>Other
                </li>
            </ul>
        </div>
        <div class="config_form">
            <form action="torrentwatch-xa.php?setGlobals=1" id="config_form" name="config_form">
                <div id="config_interface" class="configTab">
                    <div class="int_settings">
                        <!--<div id="config_webui">
                            <div class="left">
                                <label class="item select">Font Size:</label>
                            </div>
                            <div class="right">
                                <select name="webui" id="config_webui" onchange="changeFontSize(this.options[this.selectedIndex].value)">
                                    <option value="Small">Small</option>
                                    <option value="Medium" selected>Medium</option>
                                    <option value="Large">Large</option>
                                </select>
                            </div>
                        </div>--> <!-- TODO either make it so font size works everywhere or remove it -->
                        <div id="config_combinefeeds" title="Combine all feeds into one list.">
                            <div class="left">
                                <label class="item checkbox">Combine Feeds:</label>
                            </div>
                            <div class="right">
                                <input type="checkbox" name="combinefeeds" value="1" <?php echo $combinefeeds; ?>/>
                            </div>
                        </div>
                        <div id="config_disable_hidelist">
                            <div class="left">
                                <label class="item checkbox">Disable Hide List:</label>
                            </div>
                            <div class="right">
                                <input type="checkbox" name="dishidelist" value="1" <?php echo $dishidelist; ?>/>
                            </div>
                        </div>
                        <!--<div id="config_epi_only" title="Hide items without episode info.">
                            <div class="left">
                                <label class="item checkbox">Episodes Only:</label>
                            </div>
                            <div class="right">
                                <input type="checkbox" name="epionly" value="1" <?php echo $epionly; ?>/>
                            </div>
                        </div>-->
                        <div id="config_hide_donate" title="I have already donated.">
                            <div class="left">
                                <label class="item checkbox">Hide Donate Button:</label>
                            </div>
                            <div class="right">
                                <input type="checkbox" name="hidedonate" value="1" <?php echo $hidedonate; ?>/>
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
                    </div>
                </div>
                <div id="config_torClient" class="configTab hidden">
                    <div class="tor_client_settings">
                        <div id="config_torrentclient" title="Choose a torrent client or local folder.">
                            <div class="left">
                                <label class="item select">Client:</label>
                            </div>
                            <div class="right">
                                <select name="client" id="client" onchange="changeClient(this.options[this.selectedIndex].value)">
                                    <option value="Transmission" <?php echo $transmission; ?>>Transmission</option>
                                    <option value="folder" <?php echo $folderclient; ?>>Save torrent in folder</option>
                                </select>
                            </div>
                        </div>
                        <div id="config_folderclient">
                            <div class="left">
                                <label class="item">File Extension:</label>
                            </div>
                            <div class="right">
                                <input type="text" class="text" name="extension" value="<?php echo $config_values['Settings']['Extension']; ?>"/>
                            </div>
                        </div>
                        <div id="config_downloaddir" title="Default directory to start items in">
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
                                    <option value="Full" <?php echo $deepfull; ?>>Full Name</option>
                                    <option value="Title" <?php echo $deeptitle; ?>>Show Title</option>
                                    <option value="Title_Season" <?php echo $deepTitleSeason; ?>>Show Title + Season</option>
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
                                <input type="checkbox" name="autodel" value="1" <?php echo $autodel; ?>/>
                            </div>
                        </div>
                        <div id="config_watchdir" title="Directory to watch for new .torrent files">
                            <div class="left">
                                <label class="item textinput">Watch Dir:</label>
                            </div>
                            <div class="right">
                                <input type="text" class="text" name="watchdir" value="<?php echo $config_values['Settings']['Watch Dir']; ?>"/>
                            </div>
                        </div>
                        <div id="config_savetorrent" title="Also save .torrent files to download directory.">
                            <div class="left">
                                <label class="item checkbox">Save Torrent Files:</label>
                            </div>
                            <div class="right">
                                <input type="checkbox" name="savetorrents" value="1" <?php echo $savetorrent; ?>/>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="config_favorites" class="configTab hidden">
                    <div class="fav_settings">
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
                                <input type="checkbox" name="favdefaultall" value="1" <?php echo $favdefaultall; ?>/>
                            </div>
                        </div>
                        <div id="config_require_epi_info" title="When enabled only shows with episode information (S01E12, 1x12, etc... ) will be matched.">
                            <div class="left">
                                <label class="item checkbox">Require Episode Info:</label>
                            </div>
                            <div class="right">
                                <input type="checkbox" name="require_epi_info" value="1" <?php echo $require_epi_info; ?>/>
                            </div>
                        </div>
                        <div id="config_verifyepisodes" title="Try not to download duplicate episodes.">
                            <div class="left">
                                <label class="item checkbox">Verify Episodes:</label>
                            </div>
                            <div class="right">
                                <input type="checkbox" name="verifyepisodes" value="1" <?php echo $verifyepisode; ?>/>
                            </div>
                        </div>
                        <div>
                            <div class="left">
                                <label class="item checkbox">Newer Episodes Only:</label>
                            </div>
                            <div class="right">
                                <input type="checkbox" name="onlynewer" value="1" <?php echo $onlynewer; ?>/>
                            </div>
                        </div>
                        <div>
                            <div class="left">
                                <label class="item checkbox">Download Proper/Repack:</label>
                            </div>
                            <div class="right">
                                <input type="checkbox" name="fetchproper" value="1" <?php echo $fetchproper; ?>/>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="config_other" class="configTab hidden">
                    <div class="other_settings">
                        <div>
                            <div class="left">
                                <label class="item checkbox">Email Notifications:</label>
                            </div>
                            <div class="right">
                                <input type="checkbox" name="emailnotify" value="1" <?php echo $emailnotify; ?>/>
                            </div>
                        </div>
                        <div id="email_address" title="Enter an email address here to send downloads and errors to.">
                            <div class="left">
                                <label class="item">To: Email Address:</label>
                            </div>
                            <div class="right">
                                <input type="text" name="emailAddress" class="text"
                                       value="<?php echo $config_values['Settings']['Email Address']; ?>"/>
                            </div>
                        </div>
                        <div id="smtp_server">
                            <div class="left">
                                <label class="item">SMTP Server:</label>
                            </div>
                            <div class="right">
                                <input type="text" name="smtpServer" class="text"
                                       value="<?php echo $config_values['Settings']['SMTP Server']; ?>"/>
                            </div>
                        </div>
                        <!--<div id="script" title="Configured script to run on certain events.">
                            <div class="left">
                                <label class="item">Script:</label>
                            </div>
                            <div class="right">
                                <input type="text" class="text" readonly="readonly"
                                       value="<?php echo $config_values['Settings']['Script']; ?>"/>
                            </div>
                        </div>-->
                    </div>
                </div>
                <div class="buttonContainer">
                    <a class="submitForm button" id="Save" href="#" name="Save">Save</a>
                </div>
                <div id='linkButtons' class="buttonContainer">
                    <a class='toggleDialog button close' href='#'>Close</a>
                </div>
            </form>
            <div id="config_feeds" class="configTab hidden">
                <div id="addFeed">
                    <form action="torrentwatch-xa.php?updateFeed=1" class="feedform">
                        <a class="submitForm button" id="Add" href="#">Add</a>
                        <label class="item">Add Feed:</label>
                        <input type="text" class="feed_link" name="link">
                    </form>
                </div>
                <div id="feedItems">
                    <div id="feedItemTitles">
                        <div id="feedNameUrl">
                            <label class="item">Name</label>
                            <label class="item hidden">Link</label>
                        </div>
                        <label class="item">Ratio</label>
                    </div>
                    <?php if(isset($config_values['Feeds'])): ?>
                    <?php foreach($config_values['Feeds'] as $key => $feed): ?>
                    <?php print("<div id=\"feedItem_" . $key . "\" class=\"feeditem\">"); ?>
                    <form action="torrentwatch-xa.php?updateFeed=1" class="feedform">
                        <input type="hidden" name="idx" value="<?php echo $key; ?>">
                        <input class="feed_name" type="text" name="feed_name"
                               title="<?php echo $feed['Link']; ?>" value="<?php echo $feed['Name']; ?>"</input>
                        <input class="feed_url hidden" type="text" name="feed_link"
                               title="<?php echo $feed['Name']; ?>" value="<?php echo $feed['Link']; ?>"</input>
                        <input class="seed_ratio" type="text" name="seed_ratio" title="Set default seed ratio for this feed."
                               value="<?php echo $feed['seedRatio']; ?>"</input>
                        <a class="submitForm button" id="Delete" href="#feedItem_<?php echo $key; ?>">Del</a>
                        <a class="submitForm button" id="Update" href="#">Upd</a>
                    </form>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div id="showURL" title="Toggle between name and link input fields">
                <?php print("<input id=\"showURL\" type=\"checkbox\" onClick=\"$.toggleFeedNameUrl(" . $key . ")\">"); ?>
                </input>
                <label id="showURLlabel" class="item">Show Feed URL</label>
            </div>
            <div id='linkButtons' class="buttonContainer">
                <a class='toggleDialog button close' href='#'>Close</a>
            </div>
        </div>
        <form action="torrentwatch-xa.php?delHidden=1" id="hidelist_form" name="hidelist_form" class="hidden">
            <div id="config_hideList" class="hidden configTab">
                <div id="hideListContainer">
                    <ul class="hidelist">
                        <?php if($config_values['Hidden']): ?>
                        <?php ksort($config_values['Hidden'], SORT_STRING); ?>
                        <?php foreach($config_values['Hidden'] as $key => $item): ?>
                        <li>
                            <label class="item checkbox">
                                <input type="checkbox" name="unhide[]" value="<?=$key?>"/>
                                <span class="hiddenItem"><?php echo $key; ?></span></label>
                        </li>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <li><h2 style='color: red; text-align: center'>You have not hidden any shows.</h2></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div id="hideSearch">
                <input type="text" id="hideSearchText" name="hideSearch"></input>
            </div>
            <div class="buttonContainer">
                <a class="submitForm button" id="Unhide" href="#">Unhide</a>
            </div>
            <div id='linkButtons' class="buttonContainer">
                <a class='toggleDialog button close' href='#'>Close</a>
            </div>
        </form>
    </div>
</div>
