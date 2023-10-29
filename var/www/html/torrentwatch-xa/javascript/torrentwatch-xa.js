//'use strict';
$(document).ready(function () { // first binding to document ready (while torrentwatch-xa.js is called before other Javascript files)
    displayFilter = function (filter, empty) {
        // define hideMe function by browser/userAgent/device
        var timeOut = 400;
        if (empty === true ||
                navigator.userAgent.toLowerCase().search('(iphone|ipod|android)') > -1) {
            $.fn.hideMe = function () {
                $(this).hide();
                //this.style.display = 'none'; //TODO this appears to be many elements--needs a loop
                timeOut = 0;
            };
        } else {
            $.fn.hideMe = function () {
                $(this).slideUp();
            };
        }
        // define showMe function by browser/userAgent/device
        if (empty === true ||
                navigator.userAgent.toLowerCase().search('(iphone|ipod|android)') > -1) {
            $.fn.showMe = function () {
                $(this).show();
                //this.style.display = ''; //TODO this appears to be many elements--needs a loop
                timeOut = 0;
            };
        } else {
            $.fn.showMe = function () {
                $(this).slideDown();
            };
        }
        // draw the item list based on selected filter/view
        clearInterval(window.filterInterval); // stop the timer window.filterInterval
        window.Cookies.remove('TWXAFILTER', {sameSite: 'lax'});
        window.Cookies.set('TWXAFILTER', filter, {expires: 30, sameSite: 'lax', path: ''}); // store the selected filter in cookie to survive browser refresh
        window.activeFilter = filter; // store the selected filter for use in updateMatchCounts
        switch (filter) {
            case 'matching':
                if ($('.transmission').is(":visible")) {
                    $('.transmission').hideMe();
                    $('.header.combined').showMe();
                    $('#torrentlist_container li.torrent.selected').removeClass('selected');
                } else {
                    $('.feed').hideMe();
                }
                setTimeout(function () {
                    var tor = $(".feed li.torrent").filter(".st_notAMatch");
                    $(tor).hide();
                    tor = $(".feed li.torrent").not(".st_notAMatch");
                    $(tor).show();
                    $('.feed').showMe();
                    tor.markAlt().closest(".feed div.feed");
                    updateMatchCounts();
                }, timeOut);
                break;
            case 'downloading':
                if ($('.transmission').is(":visible")) {
                    $('.transmission').hideMe();
                    $('.header.combined').showMe();
                    $('#torrentlist_container li.torrent.selected').removeClass('selected');
                } else {
                    $('.feed').hideMe();
                }
                var showFilter = function () {
                    var tor = $(".feed li.torrent").not('.st_downloading');
                    $(tor).hide();
                    tor = $(".feed li.torrent").filter('.st_downloading');
                    $(tor).show();
                    $('.feed').showMe();
                    tor.markAlt().closest(".feed div.feed");
                    updateMatchCounts();
                };
                break;
            case 'downloaded':
                if ($('.transmission').is(":visible")) {
                    $('.transmission').hideMe();
                    $('.header.combined').showMe();
                    $('#torrentlist_container li.torrent.selected').removeClass('selected');
                } else {
                    $('.feed').hideMe();
                }
                var showFilter = function () {
                    var tor = $(".feed li.torrent").not('.st_downloaded, .st_inCacheNotActive');
                    $(tor).hide();
                    tor = $(".feed li.torrent").filter('.st_downloaded, .st_inCacheNotActive');
                    $(tor).show();
                    $('.feed').showMe();
                    tor.markAlt().closest(".feed div.feed");
                    updateMatchCounts();
                };
                break;
            case 'transmission':
                if ($('.feed').is(':visible')) {
                    $('.feed').hideMe();
                    $('.header.combined').hideMe();
                    $('#torrentlist_container li.torrent.selected').removeClass('selected');
                }
                setTimeout(function () {
                    $('.transmission').showMe();
                    $("#transmission_list").find("li.torrent").markAlt();
                    updateMatchCounts();
                }, timeOut);
                break;
            case 'all':
            default:
                if ($('.transmission').is(":visible")) {
                    $('.transmission').hideMe();
                    $('.header.combined').showMe();
                    $('#torrentlist_container li.torrent.selected').removeClass('selected');
                } else {
                    $('.feed').hideMe();
                }
                setTimeout(function () {
                    var tor = $(".feed li.torrent").not(".hiddenFeed");
                    $(tor).show();
                    $('.feed').showMe();
                    tor.markAlt().closest(".feed div.feed");
                    updateMatchCounts();
                }, timeOut);
        }
        if (showFilter) {
            // restart the timer window.filterInterval (so that Downloading and Downloaded can dynamically update faster)
            window.filterInterval = setInterval(function () {
                showFilter();
            }, 500);
        }
        setTimeout(updateClientButtons, timeOut); // update the clientButtons button bar in timeOut ms
        $.checkHiddenFeeds(1); // check the hidden (rolled-up) feeds
        $('#filter_' + filter).addClass('selected').siblings().removeClass("selected"); // this filter is selected, the others are deselected
        document.querySelector('#filter_search_input').value = ''; // clear the search input field
    };
    processSearchInput = function () {
        // process search input including clearing
        var filterText = this.value.toLowerCase();
        $("li.torrent").hide().each(function () { //TODO replace hide() with style.display = 'none'
            if ($(this).find(".torrent_name").text().toLowerCase().match(filterText)) {
                this.style.display = '';
            }
        }).markAlt();
    };
    // keyup binding for Filter Bar search input key events
    $("#filter_search_input").on("keyup", processSearchInput);
    // on search binding for Filter Bar search input key and click events
    $("#filter_search_input").on("search", processSearchInput);
    updateMatchCounts = function () {
        var feed = $('.feed');
        // update filter and feed headers with total match counts
        var activeTorrents = $('#transmission_list li').length;
        $('#activeTorrents').html("(" + activeTorrents + ")");
        if (!activeTorrents) {
            window.gotAllData = true; //TODO true when Transmission list is empty (but transmission-daemon might not really be empty)
        }
        var totalMatching = feed.find('li.torrent').not('.st_notAMatch').length;
        var totalDownloaded = feed.find('li.st_downloaded, li.st_inCacheNotActive').length;
        var totalDownloading = feed.find('li.st_downloading').length;
        var matching = $('#Matching');
        if (totalMatching) {
            matching.html('(' + totalMatching + ')');
        } else {
            matching.html('');
        }
        var downloaded = $('#Downloaded');
        if (totalDownloaded) {
            downloaded.html('(' + totalDownloaded + ')');
        } else {
            downloaded.html('');
        }
        var downloading = $('#Downloading');
        if (totalDownloading) {
            downloading.html('(' + totalDownloading + ')');
        } else {
            downloading.html('');
        }
        switch (window.activeFilter) { //TODO maybe move this block outside the .each loop
            case 'downloaded':
                $.each(feed, function (i, item) {
                    var itemid = $('#' + item.id);
                    itemid.find('span.matches').html('(' + itemid.find('li.st_downloaded, li.st_inCacheNotActive').length + ')');
                });
                break;
            case 'downloading':
                $.each(feed, function (i, item) {
                    var itemid = $('#' + item.id);
                    itemid.find('span.matches').html('(' + itemid.find('li.st_downloading').length + ')');
                });
                break;
            case 'matching':
            default: // All
                $.each(feed, function (i, item) {
                    var itemid = $('#' + item.id);
                    itemid.find('span.matches').html('(' + itemid.find('li.torrent').not('.st_notAMatch').length + ')');
                });
        }
        listSelector();
        updateClientButtons();
    };
    // toggle visible web UI elements for different torrent clients
    changeClient = function (client) {
        switch (client) {
            case "folder":
                $("#config_tr_user, #config_tr_password, #config_tr_host, #config_tr_port, #filter_downloading, #filter_transmission, #tabTor, #config_alsosavetorrentfiles, #config_alsosavedir").css("display", "none");
                $("#filter_transmission").removeClass('filter_right');
                $("#filter_downloaded").addClass('filter_right');
                window.client = "folder";
                adjustWebUIButton();
                break;
            case 'Transmission':
                $("#config_tr_user, #config_tr_password, #config_tr_host, #config_tr_port, #filter_downloading, #filter_transmission, #tabTor, #config_alsosavetorrentfiles, #config_alsosavedir").css("display", "block");
                $("#filter_downloaded").removeClass('filter_right');
                $("#filter_transmission").addClass('filter_right');
                window.client = 'Transmission';
                adjustWebUIButton();
        }
    };
    // perform the first load of the dynamic information
    $.get('torrentwatch-xa.php', '', $.loadDynamicData, 'html');
    // toggle between Resume or Pause in context menu based on current state
    toggleTorResumePause = function (torHash) {
        var curObject = $('li.item_' + torHash + ' div.torResume');
        if (curObject.css('display') === 'block') {
            curObject.hide();
        } else {
            curObject.show();
        }
        curObject = $('li.item_' + torHash + ' div.torPause');
        if (curObject.css('display') === 'block') {
            curObject.hide();
        } else {
            curObject.show();
        }
        curObject = null;
    };
    // hides or shows Move button in button bar
    toggleTorMove = function (torHash) {
        var curObject = $('#clientButtons li.move_data, #clientButtons li#Move');
        if (curObject.is(":visible")) {
            curObject.fadeOut('fast', updateClientButtons); // fadeOut('fast') doesn't set width and height to 0 like hide() does
        } else {
            curObject.fadeIn('fast', updateClientButtons);
        }
        curObject = null;
    };
    // assemble html for item in only the Transmission filter list
    getClientItem = function (item, clientData, liClass, percentage, eta) {
        var transmissionItem =
                '<li id="clientId_' + item.id + '" class="torrent item_' + item.hashString + ' clientId_' + item.id + ' st_transmission ' + liClass + '">' +
                '<table width="100%" cellspacing="0"><tr><td class="tr_identifier"></td>' +
                '<td class="torrent_name tor_client">' +
                '<div class="torrent_name"><span class="torrent_title">' + item.name + '</span></div>' +
                '<div style="width: 100%; margin-top: 2px; border: 1px solid #BFCEE3; background: #DFE3E8;">' +
                '<div class="progressDiv" style="width: ' + percentage + '%; height: 3px;"></div></div>' +
                '<span class="dateAdded hidden">' + item.addedDate + '</span>' +
                '<div class="infoDiv"><span id=tor_' + item.id + ' class="torInfo tor_' + item.hashString + '">' + clientData + '</span>' +
                '<span class="torEta">' + eta + '</span></div>' +
                '<input type="hidden" class="path" value="' + item.downloadDir + '"></input>' +
                '</td></tr></table></li>';
        return (transmissionItem);
    };
    // show #clientError div (not the same as #twError div shown by $.fn.showErrorPanel())
    showClientError = function (error) {
        $('#clientError p').html(error);
        $('#clientError').slideDown();
    };
    // register handler for ajax error; for Transmission connection errors
    window.clientErrorCount = 0;
    $(document).ajaxError(function (event, request, settings) {
        if (settings.url.match(/getClientData/)) {
            //window.getfail = true; // set getFail to true when error occurred getting client data
            var error = "Error connecting to " + window.client;
            window.clientErrorCount++;
            $('.torInfo').html(error);
            $('div.feed .torInfo').addClass('torInfoErr');
            $('li#filter_transmission a').addClass('error');
            if (window.clientErrorCount >= 3) {
                showClientError(error);
            }
        }
    });
    getClientData = function () {
        if (window.ajaxActive) {
            return; // quit if ajax request is active
        }
        // get the state of Disable Hide List
        window.hideProgressBar = true; // prevents recurring progress bar in Configure dialog
        $.get('torrentwatch-xa.php', {getDisableHideList: 1}, function (response) {
            // do not put this inside toggleClientButtons or updateClientButtons as it will slow down the browser
            if (response) {
                window.disableHideList = true;
            } else {
                window.disableHideList = false; // need this to avoid undefined
            }
        });
        window.hideProgressBar = false;
        if (window.client === "Transmission") {
            window.updatingClientData = true;
            window.hideProgressBar = true; // setting this to true turns off progress bar via ajaxStart
            // set timeout to add spinning busy icon in 1500ms if still updatingClientData
            setTimeout(function () {
                if (window.updatingClientData) {
                    $('li#webui a span').addClass('altIcon'); // adds spinning busy icon
                }
            }, 1500);
            // get torrent list from transmission-daemon via PHP
            $.get('torrentwatch-xa.php', {
                'getClientData': 1
            }, function (json) {
                window.updatingClientData = false; // set to false now to indicate getClientData is done getting data

//TODO simplify JSON error handling
                if (json.match(/\S+/) === null) { // nothing useful in json output, show error
                    //window.getfail = true; // set getFail to true when error occurred getting client data
                    var error = 'Got no data from ' + window.client;
                    showClientError(error);
                    $('.torInfo').html(error);
                    $('div.feed .torInfo').addClass('torInfoErr');
                    $('li#filter_transmission a').addClass('error');
                    return;
                }

                // attempt to parse JSON output from getClientData
                try {
                    json = JSON.parse(json);
                } catch (err) {
                    showClientError(json);
                    return;
                }

                // show error div if no response from Transmission
                if (json === null) {
                    showClientError("Transmission did not return any data and might not be active.");
                    window.errorActive = true;
                    return;
                }
                if (window.errorActive === true) {
                    $('#clientError').slideUp();
                    window.errorActive = false;
                }

                // reset error counter to 0 (error dialog pops up on 3rd error)
                window.clientErrorCount = 0;
                $('li#filter_transmission a').removeClass('error');
                $('div.feed .torInfo').removeClass('torInfoErr');
//TODO end simplify JSON error handling

                processTransmissionData(json);
                $('li#webui a span').removeClass('altIcon'); // remove the spinning busy icon
            });
            window.hideProgressBar = false; // setting this to false turns on progress bar via ajaxStart
        } // end window.client === "Transmission"
    };
    processTransmissionData = function (json) {
        var upSpeed = 0;
        var downSpeed = 0;
        var transmissionItemIds = []; // for later removal of items not in the array
        // loop through each torrent in transmission-daemon
        $.each(json['arguments']['torrents'],
                function (i, item) {
                    transmissionItemIds.push("clientId_" + item.id);
                    ///// compile torrent item for Transmission filter list

                    // remap Transmission pre-2.4 status codes to 2.4
                    if (item.status === 16) { // pre-2.4 TR_STATUS_STOPPED
                        item.status = 0; // 2.4 TR_STATUS_STOPPED
                    } else if (item.status === 8) { // pre-2.4 TR_STATUS_SEED
                        item.status = 6; // 2.4 TR_STATUS_SEED
                    } // the other TR_STATUS codes do not need remapping

                    // progress bar
                    var percentage = Math.roundWithPrecision(100 * item.percentDone, 2);
                    /* infoDiv's torInfo
                     * item.recheckProgress: When tr_stat.activity is TR_STATUS_CHECK or TR_STATUS_CHECK_WAIT,
                     * this is the percentage of how much of the files has been
                     * verified. When it gets to 1, the verify process is done.
                     * Range is [0..1] */
                    var validProgress = Math.roundWithPrecision((100 * item.recheckProgress), 2);
                    // use item.uploadRatio for seed ratio

                    /* infoDiv's torEta
                     * item.eta: If downloading, estimated number of seconds left until the torrent is done.
                     * If seeding, estimated number of seconds left until seed ratio is reached. */
                    var convertedEta = convertEta(item.eta); // convertedEta will be shown for every status except tc_paused

                    var liClass = ""; // liClass sets legend color via CSS
                    var clientData = "";
                    switch (item.status) {
                        // handle by Transmission status code
                        case 1: // TR_STATUS_CHECK_WAIT in both pre-2.4 and 2.4: Queued to check files
                            clientData = "Waiting to verify (" + validProgress + "%)";
                            liClass = "tc_waiting";
                            break;
                        case 2: // TR_STATUS_CHECK in both pre-2.4 and 2.4: Checking files
                            clientData = "Verifying files (" + validProgress + "%)";
                            liClass = "tc_verifying";
                            break;
                        case 3: // TR_STATUS_DOWNLOAD_WAIT in 2.4: Queued to download
                            clientData = "Waiting to download";
                            liClass = "tc_waiting";
                            break;
                        case 4: // TR_STATUS_DOWNLOAD in both pre-2.4 and 2.4: Downloading
                            clientData = "Downloading from " + item.peersSendingToUs + " of " +
                                    item.peersConnected + " peers: " +
                                    Math.formatBytes(item.totalSize - item.leftUntilDone) + " of " +
                                    Math.formatBytes(item.totalSize) +
                                    " (" + percentage + "%); seed ratio " + item.uploadRatio +
                                    " of limit " + item.seedRatioLimit;
                            liClass = "tc_downloading";
                            break;
                        case 5: // TR_STATUS_SEED_WAIT in 2.4: Queued to seed
                            clientData = "Waiting to seed: seed ratio " + item.uploadRatio +
                                    " of limit " + item.seedRatioLimit;
                            liClass = "tc_waiting";
                        case 6: // TR_STATUS_SEED in 2.4 : Seeding
                            clientData = "Seeding to " + item.peersGettingFromUs + " of " +
                                    item.peersConnected + " peers: seed ratio " + item.uploadRatio +
                                    " of limit " + item.seedRatioLimit;
                            liClass = "tc_seeding";
                            break;
                        case 0: // TR_STATUS_STOPPED in 2.4: Torrent is stopped
                            //TODO detect if item is in download cache (is managed by torrentwatch-xa) and change appearance somehow
                            if (item.uploadRatio >= item.seedRatioLimit && percentage === 100) {
                                clientData = "Downloaded and seed ratio limit of " + item.seedRatioLimit + " met. This torrent can be removed.";
                            } else {
                                clientData = "Paused";
                            }
                            liClass = "tc_paused";
                            convertedEta = "Paused"; // override "Remaining: unknown"
                    }
                    // replace clientData if there's an error for this one torrent
                    if (item.errorString) {
                        clientData = item.errorString;
                    }

                    ///// find matching item in current li#transmission_list (not div#transmission_data) by item.hashString or item.id
                    var torListElmt; // torrent list element
                    if ($("#transmission_list").find("li.item_" + item.hashString).length) {
                        torListElmt = $("li.item_" + item.hashString);
                    } else if ($("#transmission_list").find("li.clientId_" + item.id).length) {
                        torListElmt = $("li.clientId_" + item.id); // note that we use class="clientId_" and not id="clientId_" so all filters are affected
                    }
                    if (torListElmt !== undefined) {
                        ///// if in list, update it
                        // handle Transmission status 0
                        if (item.status === 0) {
                            if (item.uploadRatio >= item.seedRatioLimit && percentage === 100) {
                                ///// completely downloaded and completed seeding
                                // perform auto-delete if it is enabled
                                $.get('torrentwatch-xa.php', {get_autodel: 1}, function (autodel) {
                                    if (autodel) {
                                        $.delTorrent(item.hashString, false, true, true); // torHash, trash, sure, checkCache
                                        // NOTE: .delTorrent() performs some important changes to the list item(s) on successful deletion
                                    }
                                });
                                // remove infoDiv and hide progressBarContainer from completed items across all filters
                                if (torListElmt.find(".torInfo").length) {
                                    torListElmt.find("div.infoDiv").remove();
                                }
                                torListElmt.find("div.progressBarContainer").hide();
                            } else if (percentage < 100) {
                                ///// paused, not yet completely downloaded
                                torListElmt.not(".st_transmission")
                                        .removeClass("st_downloaded st_favReady st_waitTorCheck st_inCacheNotActive")
                                        .addClass("st_downloading");
                            } else {
                                ///// paused, completed download but not completed seeding
                                torListElmt.not(".st_transmission")
                                        .removeClass("st_downloading st_favReady st_waitTorCheck st_inCacheNotActive")
                                        .addClass("st_downloaded");
                            }
                        }

                        // set the item's torrent_title text in only the Transmission filter (all other filters will already have correct title)
                        if ($("#transmission_list").find(torListElmt).find(".torrent_title").text() === item.hashString &&
                                item.name !== item.hashString) {
                            $("#transmission_list").find(torListElmt).find(".torrent_title").text(item.name);
                        }
                        // update progress bar for item in all filters
                        torListElmt.find("div.progressBarContainer").show(); // must use item.hashString as other filters don't have clientId_
                        torListElmt.find("div.progressDiv").width(percentage + "%").height(3);
                        ///// add the empty infoDiv and torEta to active torrent items if they don't have one
                        $.each(torListElmt.find("td.torrent_name"), function () {
                            /* loop through each item that matches the identifier
                             * We do this because on a browser refresh, the item in #transmission_list matches and already has div.infoDiv
                             * AND the item in the other filters does not have div.infoDiv. Adding infoDiv using implicit iterator results
                             * in multiple infoDivs in one item */
                            if (!$(this).children("div.infoDiv").length) {
                                $(this).append('<div class="infoDiv"><span class="torInfo"></span><span class="torEta"></span></div>');
                            }
                        });
                        // set torInfo and torEta for item in all filters
                        torListElmt.find(".torInfo").text(clientData);
                        torListElmt.find("span.torEta").text(convertedEta);
                        // set class for legend coloring in only the Transmission filter
                        torListElmt.removeClass('tc_paused tc_downloading tc_seeding tc_verifying tc_waiting').addClass(liClass); // only handles liClass

                        ///// take status-based actions
                        switch (item.status) {
                            case 4:
                            case 6:
                                // add to the upSpeed and downSpeed totals
                                if (!isNaN(upSpeed)) {
                                    upSpeed += item.rateUpload;
                                }
                                if (!isNaN(downSpeed)) {
                                    downSpeed += item.rateDownload;
                                }
                        }
                        // change match classes for legend and filtering
                        switch (item.status) {
                            case 1:
                            case 2:
                                torListElmt.not(".st_transmission")
                                        .removeClass("st_downloaded st_favReady st_waitTorCheck st_inCacheNotActive");
                                // .addClass("tc_verifying") is handled earlier
                                break;
                            case 3:
                            case 4:
                                // switch to st_downloading in all filters
                                torListElmt.not(".st_transmission")
                                        .removeClass("st_downloaded st_favReady st_waitTorCheck st_inCacheNotActive")
                                        .addClass("st_downloading");
                                break;
                            case 5:
                            case 6:
                                // switch from st_downloading to st_downloaded in all filters
                                torListElmt.not(".st_transmission")
                                        .removeClass("st_downloading st_favReady st_waitTorCheck st_inCacheNotActive")
                                        .addClass("st_downloaded");
                                break;
                                // no case 0 here--handled earlier due to conditional and auto-delete
                        }

                        // hide/show context menu buttons
                        if (item.status >= 0) {
                            torListElmt.find("div.torStart").hide();
                            torListElmt.find("div.torStart").addClass("hidden");
                            torListElmt.find("div.torDelete").show();
                            torListElmt.find("div.torDelete").removeClass("hidden");
                            torListElmt.find("div.torTrash").show();
                            torListElmt.find("div.torTrash").removeClass("hidden");
                            if (item.status === 0) {
                                torListElmt.find("div.torPause").hide();
                                torListElmt.find("div.torPause").addClass("hidden");
                                torListElmt.find("div.torResume").show();
                                torListElmt.find("div.torResume").removeClass("hidden");
                            } else {
                                torListElmt.find("div.torPause").show();
                                torListElmt.find("div.torPause").removeClass("hidden");
                                torListElmt.find("div.torResume").hide();
                                torListElmt.find("div.torResume").addClass("hidden");
                            }
                        }
                        //TODO possibly update button bar for items in filters other than Transmission

                    } // end torListElmt !== undefined
                    else {
                        ///// if not in list, add it
                        $("#transmission_list").prepend(getClientItem(item, clientData, liClass, percentage, convertedEta)); // gets Transmission item html
                        /* add class="clientId_" to all items in filters including Transmission
                         * item in Transmission filter will already have the class from getClientItem above
                         * must use "li.item_" because "clientId_" doesn't exist yet
                         * we're counting on the PHP side to put the hash into the item for the next line to work */
                        $("li.item_" + item.hashString).addClass("clientId_" + item.id);
                        //TODO optional: add update of progress bar, infoDiv, torInfo, and torEta from prior block here, just without identifier
                    }
                }); // end $.each(json['arguments']['torrents']

        // post the upSpeed and downSpeed totals
        if (!isNaN(downSpeed) && !isNaN(upSpeed)) {
            $("#rates").html("D: " + Math.formatBytes(downSpeed) + "/s&nbsp;&nbsp;</br>U: " + Math.formatBytes(upSpeed) + "/s");
        }

        ///// remove torrents in #transmission_list that are not in the transmission-daemon
        $.each($("#transmission_list").find("li"),
                function (i, item) {
                    // search through transmissionItemIds array
                    if (jQuery.inArray(item.id, transmissionItemIds) === -1) { // relies on id="clientId_" instead of class="clientId_"
                        // item in Transmission filter is not found in transmission-daemon
                        // first, remove the class="clientId_###" from items in all filters using item.id, which is also "clientId_###"
                        $("li." + item.id).removeClass(item.id);
                        // then, remove the item from the Transmission filter
                        item.remove(); // essentially removes item from Transmission filter by id="clientId_" not class="clientId_"
                    }
                });
        ///// process items not in Transmission across all filters other than Transmission

        // loop through in #torrentlist_container that have st_waitTorCheck
        // NOTE: #torrentlist_container is parent of #transmission_list
        $.each($("#torrentlist_container").find(".st_waitTorCheck, .tc_downloading"), function () { //TODO test .tc_downloading; might need to separate from .st_waitTorCheck
            // check if it does not have a class starting with "clientId_"
            var classList = $(this).prop("className").split(/\s+/);
            var clientIdClass = "";
            for (var k = 0; k < classList.length; k++) {
                if (classList[k].substr(0, 9) === "clientId_") {
                    clientIdClass = classList[k];
                }
            }
            if (clientIdClass === "") {
                $(this).removeClass("tc_paused tc_downloading tc_seeding tc_verifying tc_waiting"); // remove all Transmission-related classes
                // set the context menu buttons
                $(this).find("div.torStart").show();
                $(this).find("div.torStart").removeClass("hidden");
                $(this).find("div.torPause").hide();
                $(this).find("div.torPause").addClass("hidden");
                $(this).find("div.torResume").hide();
                $(this).find("div.torResume").addClass("hidden");
                $(this).find("div.torStop").hide();
                $(this).find("div.torStop").addClass("hidden");
                $(this).find("div.torDelete").hide();
                $(this).find("div.torDelete").addClass("hidden");
                $(this).find("div.torTrash").hide();
                $(this).find("div.torTrash").addClass("hidden");
                // hide progress bar and remove infoDiv
                //TODO we might need to do this only for .tc_downloading
                if ($(this).find(".torInfo").length) {
                    //TODO might need to set .torInfo text to nothing
                    $(this).find("div.infoDiv").remove();
                }
                $(this).find("div.progressBarContainer").hide();

                // finally, change st_waitTorCheck to st_inCacheNotActive for leftover items
                $(this).removeClass("st_waitTorCheck").addClass("st_inCacheNotActive");
                $(this).removeClass("st_waitTorCheck");
            }
        });
        setTimeout(updateMatchCounts(), 100); // update match counts in UI
        $("#transmission_list>li").tsort("span.dateAdded", {order: "desc"}); // sort the items in the Transmission filter
        $("#transmission_list").find("li.torrent").markAlt();
        window.gotAllData = true;
    }; // end processTransmissionData

    listSelector = function () {
        // handles event bindings for selecting/highlighting items in the list
        var torrentlistcontainer = $('#torrentlist_container').find('li.torrent').not('.selActive');
        torrentlistcontainer.addClass('selActive');
        torrentlistcontainer.on("mousedown", function () {
            toggleSelect(this);
        });
        var litorrent = $('li.torrent');
        litorrent.find('a').off("mousedown");
        litorrent.find('div.contextItem').off("mousedown");
    };
    toggleSelect = function (item) {
        if ($(item).hasClass('selected')) {
            $(item).removeClass('selected');
        } else {
            $(item).addClass('selected');
        }
        if ($('#torrentlist_container').find('li.torrent.selected').length) {
            updateClientButtons();
            $('#moveTo').val($('#' + item.id + ' input.path').val());
            //document.querySelector('#moveTo').value = item.querySelector('input.path').value; //TODO didn't work when querySelector returns null
        } else {
            updateClientButtons();
            $('#moveTo').val('');
            //document.querySelector('#moveTo').value = ''; //TODO test the switch from .val() to .value
        }
    };
    updateClientButtons = function (fast) {
        var obj;
        var tor = [];
        if ($('#transmission_data').is(":visible")) {
            $('#clientButtons .add_fav, #clientButtons .start, #clientButtons .hide_item').hide();
            $('#clientButtons .move_button').show();
        } else {
            $('#clientButtons .add_fav, #clientButtons .start, #clientButtons .hide_item').show();
            $('#clientButtons .move_button').hide();
        }

        if ($('#torrentlist_container .feed  li.torrent.selected').length) {
            tor['fav'] = 1;
        }
        if ($('#torrentlist_container .feed  li.torrent.selected.st_notAMatch').length && window.disableHideList !== true) {
            tor['hide'] = 1;
        }
        //TODO maybe block other interim states (do not show start button on st_waitTorCheck, etc.)
        if ($('#torrentlist_container .feed li.selected').not('.st_downloading').not('.st_downloaded').length) {
            tor['start'] = 1;
        }

        if (window.client !== "folder") {
            if ($('#torrentlist_container li.selected.tc_paused').length) {
                tor['resume'] = 1;
                tor['del'] = 1;
                tor['trash'] = 1;
                if ($('#transmission_data').is(':visible')) {
                    tor['move'] = 1;
                }
            }
            if ($('#torrentlist_container li.selected.st_downloading:not(.tc_paused),' +
                    '#torrentlist_container li.selected.st_downloaded:not(.tc_paused),' +
                    '#torrentlist_container li.selected.st_transmission:not(.tc_paused)').length) {
                tor['pause'] = 1;
                tor['del'] = 1;
                tor['trash'] = 1;
                if ($('#transmission_data').is(':visible')) {
                    tor['move'] = 1;
                }
            }
        } else {
            $('#clientButtons .resume, #clientButtons .pause, #clientButtons .trash, #clientButtons .delete, #clientButtons .move_data, #clientButtons .move_button').hide();
        }
        var buttons = '';
        for (obj in tor) {
            switch (obj) {
                case 'start':
                    buttons += "#clientButtons .start,";
                    break;
                case 'fav':
                    buttons += "#clientButtons .add_fav,";
                    break;
                case 'hide':
                    buttons += "#clientButtons .hide_item,";
                    break;
                case 'pause':
                    buttons += "#clientButtons .pause,";
                    break;
                case 'resume':
                    buttons += "#clientButtons .resume,";
                    break;
                case 'del':
                    buttons += "#clientButtons .delete,";
                    break;
                case 'trash':
                    buttons += "#clientButtons .trash,";
                    break;
                case 'move':
                    buttons += "#clientButtons .move_button, #clientButtons #Move,";
            }
        }
        buttons = buttons.slice(0, buttons.length - 1);
        $('#clientButtons li.button:not(buttons)').addClass('disabled');
        $(buttons).removeClass('disabled');
        toggleClientButtons(fast);
    };
    toggleClientButtons = function (fast) {
        if (navigator.userAgent.toLowerCase().search('(iphone|ipod|ipad|android)') > -1) {
            fast = 1;
        }
        if ($('#torrentlist_container li.selected').length) {
            if (fast || $('#torrentlist_container li.selected').length > 1) {
                if ($('#clientButtonsHolder').is(':visible') === false) {
                    $('#clientButtonsHolder').show();
                }
            } else {
                if ($('#clientButtonsHolder').is(':visible') === false) {
                    $('#clientButtonsHolder').fadeIn('fast');
                }
            }
            if (navigator.userAgent.toLowerCase().search('(iphone|ipod|android)') > -1) {
                if ($('#moveTo').is(':focus')) {
                    return;
                }
                // set the vertical positioning
                if (navigator.userAgent.toLowerCase().search('(iphone|ipod|ipad|android)') > -1) {
                    document.getElementById('clientButtonsHolder').style.top =
                            (window.innerHeight - $('#clientButtonsHolder').height() - 6) + 'px';
                } else {
                    document.getElementById('clientButtonsHolder').style.top =
                            (window.pageYOffset + window.innerHeight - $('#clientButtonsHolder').height() - 6) + 'px';
                }
                $('#clientButtons').css('min-width', $('#clientButtons li.button:visible').length * 42);
                $('#clientButtons').css('max-width', ($(window).width() - 15));
            }
        } else {
            if (fast) {
                if ($('#clientButtonsHolder').is(':visible') === true) {
                    $('#clientButtonsHolder').hide();
                }
            } else {
                if ($('#clientButtonsHolder').is(':visible') === true) {
                    $('#clientButtonsHolder').fadeOut('fast');
                }
            }
            $('#clientButtons .move_data').hide();
        }
    };
    adjustUIElements();
    var supportsOrientationChange = "onorientationchange" in window,
            orientationEvent = supportsOrientationChange ? "orientationchange" : "resize";
    window.addEventListener(orientationEvent, toggleClientButtons, false);
    var waitForDynData = setInterval(function () {
        if ($('#dynamicdata').length) {
            listSelector();
            clearInterval(waitForDynData);
        }
    }, 500);
});
(function ($) {
    var current_superfavorite, current_favorite, current_dialog;
    // Remove old dynamic content, replace it with passed html(ajax success function)
    $.loadDynamicData = function (html) {
        window.gotAllData = false;
        $("#dynamicdata").remove();
        $('#mainoptions li a').removeClass('selected');
        setTimeout(function () {
            var dynamic = $("<div id='dynamicdata' class='dyndata'/>");
            // Use innerHTML because some browsers choke with $(html) when html is many KB
            dynamic[0].innerHTML = html;
            dynamic.find("ul.superfavorite > li").initSuperFavorites().end().find("form").initForm().end().initConfigDialog().appendTo("body");
            dynamic.find("ul.favorite > li").initFavorites().end().find("form").initForm().end().initConfigDialog().appendTo("body");
            setTimeout(function () {
                var container = $("#torrentlist_container");
                var filter = window.Cookies.get('TWXAFILTER');
                window.activeFilter = filter;
                $('li.torrent:not(.st_waitTorCheck) div.progressBarContainer').hide(); // hides progressBarContainer on all items but st_waitTorCheck
                $("li.torrent.st_waitTorCheck div.progressBarContainer").hide(); // hide progressBarContainer even on waitTorCheck
                if (!(filter)) {
                    filter = 'all';
                }
                if ($('#transmission_data').length) {
                    $('a#torClient ').show().html('Transmission');
                } else {
                    $('a#torClient').hide();
                    $('div.activeTorrent.torDelete').hide();
                    $('div.activeTorrent.torTrash').hide();
                    if (filter === 'transmission') {
                        filter = 'all';
                    }
                }
                if (filter === 'transmission') {
                    $('#torrentlist_container .feed').hide();
                } else {
                    $('.transmission').hide();
                }
                $("#torrentlist_container li").hide();
                container.show(0, function () {
                    displayFilter(filter, true);
                    $('#dynamicdata').css('height', $(window).height() - ($('#topmenu').css('height') + 1));
                    if ('ontouchmove' in document.documentElement && navigator.userAgent.toLowerCase().search('android') === -1) {
                        $('#torrentlist_container').bind('touchstart', function () {
                            $('#torrentlist_container').bind('touchmove', function () {
                                $('#clientButtonsHolder').hide();
                            });
                        });
                    } else if ('ontouchstart' in document.documentElement) {
                        $('#torrentlist_container').bind('touchstart', function () {
                            $('#clientButtonsHolder').hide();
                        });
                    }

                    if (window.addEventListener) {
                        window.addEventListener('scroll', function () {
                            toggleClientButtons(1);
                        }, false);
                    }
                });
                setTimeout(getClientData, 10);
                var initGetData = setInterval(function () {
                    if (window.gotAllData) {
                        clearInterval(initGetData);
                        $('div.progressBarContainer').removeClass('init');
                        if (window.getDataLoop) {
                            clearInterval(window.getDataLoop);
                        }
                        if (navigator.userAgent.toLowerCase().search('(iphone|ipod|ipad|android)') > -1) {
                            window.getDataLoop = setInterval(getClientData, 10000);
                        } else {
                            window.getDataLoop = setInterval(getClientData, 5000); //TODO lowering this value causes getClientData to update the active torrents in the filters other than Transmission faster after a browser refresh
                        }
                    } else {
                        setTimeout(getClientData, 10);
                    }
                }, 500);
                window.client = $('#clientType').html();
                changeClient(window.client);
            }, 50);
            if ($('#torrentlist_container div.header.combined').length === 1) {
                $('.torrentlist>li').tsort('.unixTime', {order: 'desc'});
            }
            setTimeout(function () {
                var versionCheck = window.Cookies.get('VERSION-CHECK');
                if (versionCheck !== '1') {
                    $.get('torrentwatch-xa.php', {checkVersion: 1}, function (data) {
                        $('#dynamicdata').append(data);
                        setTimeout(function () {
                            $('#newVersion').slideUp().remove();
                        }, 15000);
                        window.Cookies.remove('VERSION-CHECK', {sameSite: 'lax'});
                        window.Cookies.set('VERSION-CHECK', '1', {expires: 7, sameSite: 'lax', path: ''});
                    });
                }
            }, 1000);
        }, 100);
    };
    $.submitForm = function (button) {
        var form;
        if ($(button).is('form')) {
            // User pressed enter
            form = $(button);
            button = form.find('a')[0];
        } else {
            form = $(button).closest("form");
        }
        //if (button.id.substr(-6) === "Delete") {
        if (button.id === "Delete") {
            // Delete button is only in the Favorites and Super-Favorites dialogs
            $.get(form.get(0).action, form.buildDataString(button));
            //TODO handle error message in json response with $.fn.showErrorPanel()
            if (button.href.match(/#superfavorite/)) {
                var id = button.href.match(/#superfavorite_(\d+)/)[1];
                $("#superfavorite_" + id).toggleSuperFavorite();
                $("#superfavorite_" + id).remove();
                $("#superfav_" + id).remove();
                window.dialog = 1;
            } else if (button.href.match(/#favorite/)) {
                var id = button.href.match(/#favorite_(\d+)/)[1];
                $("#favorite_" + id).toggleFavorite();
                $("#favorite_" + id).remove();
                $("#fav_" + id).remove();
                window.dialog = 1;
            }
            //} else if (button.id.substr(-6) === "Update") {
        } else if (button.id === "Update") {
            // Update button is only in the Favorites and Super-Favorites dialogs
            //$.get(form.get(0).action, form.buildDataString(button), $.loadDynamicData, 'html'); // returned full html page gets fed into $.loadDynamicData, which rebuilds dynamic data on page using output of main section from torrentwatch-xa.php
            $.get(form.get(0).action, form.buildDataString(button), function (response) {
                // handle error message in json response
                if (response.errorCode !== 0 && response.errorMessage !== "") {
                    $.fn.showErrorPanel(response.errorMessage);
                } else {
                    if (response.type === "superfavorite") {
                        // response json is used to set the values of a single Super-Favorite in the Super-Favorites dialog
                        if (response.idx !== null && response.idx.toString() !== "") {
                            // if response.name changed, change the name in the left pane (no re-sort until page reload)
                            $("#superfav_" + response.idx + "_anchor").text(response.name);
                            // does the <form> with id "superfavorite_### exist?"
                            var superfavoriteiDPrefix = "#superfavorite_" + response.idx;
                            if ($(superfavoriteiDPrefix)) {
                                //$(superfavoriteiDPrefix + "_alsosavedir").attr("value", response.alsosavedir);
                                //$(superfavoriteiDPrefix + "_downloaddir").attr("value", response.downloaddir);
                                //$(superfavoriteiDPrefix + "_episodes").attr("value", response.episodes);
                                // remove selected from all _feed options
                                $(superfavoriteiDPrefix + "_feed option").each(function () {
                                    $(this).removeAttr("selected");
                                    //$(this).prop("selected", false); // prop is supposed to be better than attr but it doesn't work as of 1.9.0
                                });
                                // set _feed select option to response.feed
                                $(superfavoriteiDPrefix + "_feed option[value='" + response.feed + "']").attr("selected", "selected").change();
                                //$(superfavoriteiDPrefix + "_feed option[value='" + response.feed + "']").prop("selected", true).change();  // prop is supposed to be better than attr but it doesn't work as of 1.9.0; change is required for it to take effect
                                window.input_change = 0; // cancel the change alert dialog set by the change event handler
                                $(superfavoriteiDPrefix + "_filter").attr("value", response.filter);
                                $(superfavoriteiDPrefix + "_name").attr("value", response.name);
                                $(superfavoriteiDPrefix + "_not").attr("value", response.not);
                                $(superfavoriteiDPrefix + "_quality").attr("value", response.quality);
                                //$(superfavoriteiDPrefix + "_seedratio").attr("value", response.seedratio);
                            } else {
                                $.fn.showErrorPanel("Cannot target Super-Favorite with idx = '" + response.idx + "' in Favorites dialog for refresh.");
                            }
                        } else {
                            $.fn.showErrorPanel("No idx for Super-Favorite; update must have failed.");
                        }
                    } else {
                        // response json is used to set the values of a single Favorite in the Favorites dialog
                        if (response.idx !== null && response.idx.toString() !== "") {
                            // if response.name changed, change the name in the left pane (no re-sort until page reload)
                            $("#fav_" + response.idx + "_anchor").text(response.name);
                            // does the <form> with id "favorite_### exist?"
                            var favoriteiDPrefix = "#favorite_" + response.idx;
                            if ($(favoriteiDPrefix)) {
                                $(favoriteiDPrefix + "_alsosavedir").attr("value", response.alsosavedir);
                                $(favoriteiDPrefix + "_downloaddir").attr("value", response.downloaddir);
                                $(favoriteiDPrefix + "_episode").attr("value", response.episode);
                                $(favoriteiDPrefix + "_episodes").attr("value", response.episodes);
                                // remove selected from all _feed options
                                $(favoriteiDPrefix + "_feed option").each(function () {
                                    $(this).removeAttr("selected");
                                    //$(this).prop("selected", false); // prop is supposed to be better than attr but it doesn't work as of 1.9.0
                                });
                                // set _feed select option to response.feed
                                $(favoriteiDPrefix + "_feed option[value='" + response.feed + "']").attr("selected", "selected").change();
                                //$(favoriteiDPrefix + "_feed option[value='" + response.feed + "']").prop("selected", true).change();  // prop is supposed to be better than attr but it doesn't work as of 1.9.0; change is required for it to take effect
                                window.input_change = 0; // cancel the change alert dialog set by the change event handler
                                $(favoriteiDPrefix + "_filter").attr("value", response.filter);
                                $(favoriteiDPrefix + "_name").attr("value", response.name);
                                $(favoriteiDPrefix + "_not").attr("value", response.not);
                                $(favoriteiDPrefix + "_quality").attr("value", response.quality);
                                $(favoriteiDPrefix + "_season").attr("value", response.season);
                                $(favoriteiDPrefix + "_seedratio").attr("value", response.seedratio);
                            } else {
                                $.fn.showErrorPanel("Cannot target Favorite with idx = '" + response.idx + "' in Favorites dialog for refresh.");
                            }
                        } else {
                            $.fn.showErrorPanel("No idx for Favorite; update must have failed.");
                        }
                    }
                }
            }, 'json');
            window.dialog = 1;
        } else {
            //TODO finish code to "pin open" Feeds panel when Save button is pressed
            // Handle all other buttons including Close, Save
            $.get(form.get(0).action, form.buildDataString(button), $.loadDynamicData, 'html');
        }
    };
    $.fn.showErrorPanel = function (errorMessage) {
        // check if there is an errorMessage to display
        var errorMessageString = errorMessage.toString();
        if (errorMessageString !== "") {
            var errorID = new Date().getTime();
            $('#twError').show().append('<p id="error_' + errorID + '">' + errorMessage + '</p>');
            setTimeout(function () {
                $('#twError p#error_' + errorID).remove();
                if (!$('#twError p').length) {
                    $('#twError').hide();
                }
            }, 5000);
        }
    };
    $.fn.toggleDialog = function () {
        this.each(function () {
            if (window.input_change && this.text !== 'Next') {
                var answer = confirm('You have unsaved changes.\nAre you sure you want to continue?');
                if (!(answer)) {
                    return;
                }
                window.input_change = 0;
            }
            var last = current_dialog === '#' ? '' : current_dialog;
            var target = this.hash === '#' ? '#' + $(this).closest('.dialog_window').id : this.hash;
            current_dialog = target === last && window.dialog === 1 ? '' : this.hash;
            if (last) {
                $(last).fadeOut('fast');
                $('#favorites, #configuration, #history, #show_legend, #clear_cache').remove(); //TODO does this really need #show_legend and #clear_cache?
                $('#mainoptions li a').removeClass('selected');
                $('#dynamicdata .dialog .dialog_window, .dialogTitle').remove();
                $('#dynamicdata .dialog').addClass('dialog_last');
            }
            if (current_dialog && this.hash !== '#') {
                $.get('torrentwatch-xa.php', {get_dialog_data: this.hash}, function (data) {
                    $('#dynamicdata.dyndata').append(data);
                    $('#dynamicdata').find("ul.superfavorite > li").initSuperFavorites().end().find("form").initForm().end().initConfigDialog();
                    $('#dynamicdata').find("ul.favorite > li").initFavorites().end().find("form").initForm().end().initConfigDialog();
                    $('#dynamicdata .dialog_last').remove();
                    if (last) {
                        $('.dialog').show();
                    } else {
                        $('.dialog').fadeIn('fast');
                    }
                    $(current_dialog).fadeIn('fast');
                    setTimeout(function () {
                        $("#dynamicdata .dialog_window input, #dynamicdata .dialog_window select").on("change", function () {
                            window.input_change = 1;
                        });
                    }, 500);
                    window.dialog = 1;
                    $(current_dialog + ' a.submitForm').on("click", function () {
                        window.dialog = 0;
                    });
                });
                $("li#id_" + this.parentNode.id + " a").addClass("selected");
            } else {
                $('#dynamicdata .dialog').fadeOut('fast');
                setTimeout(function () {
                    $('#dynamicdata .dialog').remove();
                }, 400);
            }
            if (last === '#configuration') {
                $.get('torrentwatch-xa.php', {get_client: 1}, function (client) {
                    window.client = client;
                    changeClient(client);
                });
            }
        });
        return this;
    };
    $.fn.initSuperFavorites = function () {
        setTimeout(function () {
            $("ul.superfavorite").find(":first a").toggleSuperFavorite();
            $('#superfavorite_new a#Update').addClass('disabled').removeClass('submitForm');
            $('#superfavorite_new a#Delete').addClass('disabled').removeClass('submitForm');
        }, 300);
        this.not(":first").tsort('a');
        return this.not(":first").end().on("click", function () {
            $(this).find("a").toggleSuperFavorite();
        });
    };
    $.fn.initFavorites = function () {
        setTimeout(function () {
            $("ul.favorite").find(":first a").toggleFavorite();
            $('#favorite_new a#Update').addClass('disabled').removeClass('submitForm');
            $('#favorite_new a#Delete').addClass('disabled').removeClass('submitForm');
        }, 300);
        this.not(":first").tsort('a');
        return this.not(":first").end().on("click", function () {
            $(this).find("a").toggleFavorite();
        });
    };
    $.fn.initForm = function () {
        this.on("submit", function (e) {
            e.stopImmediatePropagation();
            $.submitForm(this);
            return false;
        });
        return this;
    };
    $.fn.toggleSuperFavorite = function () {
        this.each(function () {
            if (window.input_change) {
                var answer = confirm('You have unsaved changes.\nAre you sure you want to continue?');
                if (!(answer)) {
                    return;
                }
                window.input_change = 0;
            }
            var last = current_superfavorite;
            current_superfavorite = this.hash;
            $("#superfavorites input").on("keyup", function () {
                if ($(current_superfavorite + ' input:text[name=name]').val().length &&
                        $(current_superfavorite + ' input:text[name=filter]').val().length) {
                    $(current_superfavorite + ' a#Update').removeClass('disabled').addClass('submitForm');
                } else {
                    $(current_superfavorite + ' a#Update').addClass('disabled').removeClass('submitForm');
                }
            });
            if (current_superfavorite === "#superfavorite_new") {
                $(current_superfavorite + ' a#Delete').addClass('disabled').removeClass('submitForm');
            } else {
                $(current_superfavorite + ' a#Delete').removeClass('disabled').addClass('submitForm');
            }
            if (!last) {
                $(current_superfavorite).show();
            } else {
                $(last).fadeOut('fast',
                        function () {
                            $(current_superfavorite).fadeIn('fast');
                            $(current_superfavorite).resetForm();
                        });
            }
        });
        return this;
    };
    $.fn.toggleFavorite = function () {
        this.each(function () {
            if (window.input_change) {
                var answer = confirm('You have unsaved changes.\nAre you sure you want to continue?');
                if (!(answer)) {
                    return;
                }
                window.input_change = 0;
            }
            var last = current_favorite;
            current_favorite = this.hash;
            $("#favorites input").on("keyup", function () {
                if ($(current_favorite + ' input:text[name=name]').val().length &&
                        $(current_favorite + ' input:text[name=filter]').val().length) {
                    $(current_favorite + ' a#Update').removeClass('disabled').addClass('submitForm');
                } else {
                    $(current_favorite + ' a#Update').addClass('disabled').removeClass('submitForm');
                }
            });
            if (current_favorite === "#favorite_new") {
                $(current_favorite + ' a#Delete').addClass('disabled').removeClass('submitForm');
            } else {
                $(current_favorite + ' a#Delete').removeClass('disabled').addClass('submitForm');
            }
            if (!last) {
                $(current_favorite).show();
            } else {
                $(last).fadeOut('fast',
                        function () {
                            $(current_favorite).fadeIn('fast');
                            $(current_favorite).resetForm();
                        });
            }
        });
        return this;
    };
    $.fn.initConfigDialog = function () {
        $('select#client').trigger("change");
        return this;
    };
    $.fn.buildDataString = function (buttonElement) {
        var dataString = $(this).filter('form').serialize();
        if (buttonElement) {
            dataString += (dataString.length === 0 ? '' : '&') + 'button=' + buttonElement.id;
        }
        return dataString;
    };
    $.fn.markAlt = function () {
        return this.filter(":visible").removeClass('alt').filter(":visible:even").addClass('alt');
    };
    $.urlencode = function (str) {
        return escape(str).replace(/\+/g, '%2B').replace(/%20/g, '+').replace(/\*/g, '%2A').replace(/\//g, '%2F').replace(/@/g, '%40');
    };
    $.addFavorite = function (feed, title) {
        window.favving = 1; //TODO may not need window.favving any more after changing addFavoriteFromgET()
        $.get('torrentwatch-xa.php', {
            addFavorite: 1,
            title: title,
            feed: feed
        }, function (response) {
            //if (response.match(/^Error:/)) {
            if (response.errorCode !== 0) {
                $.fn.showErrorPanel(response.errorMessage);
            } else {
                // find all items that match the name, quality, feed and
                $.each($("ul#torrentlist li"), function (i, item) {
                    //if ($('li#' + item.id + ' input.show_title').val().toLowerCase().match(response.title.toLowerCase()) &&
                    if ($('li#' + item.id + ' input.show_title').val().toLowerCase().match(response.name.toLowerCase()) &&
                            $('li#' + item.id + ' input.show_quality').val().toLowerCase().match(response.quality.toLowerCase()) &&
                            ($.urlencode($('li#' + item.id + ' input.feed_link').val()).match(response.feed) ||
                                    response.feed === 'All')) {
                        $('li#' + item.id).removeClass('st_notAMatch').addClass('st_favReady');
                    }
                });
            }
            window.favving = 0;
            //}, 'html');
        }, 'json');
    };
    $.dlTorrent = function (title, link, linkType, feed, id) {
        $.get("torrentwatch-xa.php", {
            dlTorrent: 1,
            title: title,
            link: link,
            linkType: linkType,
            feed: feed,
            id: id
        },
                function (torHash) {
                    if (torHash.match(/Error:\s\w+/)) {
                        $.fn.showErrorPanel('Something went wrong while adding this torrent. ' + torHash);
                        return;
                    }
                    if (window.client === "folder") {
                        $('li#id_' + id)
                                .removeClass('tc_paused tc_downloading tc_seeding tc_verifying tc_waiting st_downloading st_favReady st_waitTorCheck st_inCacheNotActive')
                                .addClass('st_downloaded');
                        $('li#id_' + id + ' div.hideItem').hide();
                        $('li#id_' + id + ' div.hideItem').addClass("hidden");
                        $('li#id_' + id + ' div.torStart').hide();
                        $('li#id_' + id + ' div.torStart').addClass("hidden");
//                        $('li#id_' + id + ' div.torResume').hide();
//                        $('li#id_' + id + ' div.torResume').addClass("hidden");
//                        $('li#id_' + id + ' div.torPause').hide();
//                        $('li#id_' + id + ' div.torPause').addClass("hidden");
//                        $('li#id_' + id + ' div.torTrash').hide();
//                        $('li#id_' + id + ' div.torTrash').addClass("hidden");
//                        $('li#id_' + id + ' div.torDelete').hide();
//                        $('li#id_' + id + ' div.torDelete').addClass("hidden");
                    } else {
                        $('li#id_' + id)
                                .removeClass('tc_paused tc_downloading tc_seeding tc_verifying st_downloaded st_favReady st_waitTorCheck st_inCacheNotActive')
                                .addClass('st_downloading tc_waiting');
                        if (!$('li#id_' + id + ' .torInfo').length) {
                            $('li#id_' + id + ' td.torrent_name')
                                    .append('<div class="infoDiv"><span id=tor_' + id + ' class="torInfo tor_' + torHash.match(/\w+/) + '">' +
                                            'Waiting for client data...</span><span class="torEta"></span></div>');
                        }
                        $('li#id_' + id + ' div.progressBarContainer').show();
                        $('li#id_' + id + ' div.hideItem').hide();
                        $('li#id_' + id + ' div.hideItem').addClass("hidden");
                        $('li#id_' + id + ' div.torStart').hide();
                        $('li#id_' + id + ' div.torStart').addClass("hidden");
                        $('li#id_' + id + ' div.torResume').hide();
                        $('li#id_' + id + ' div.torResume').addClass("hidden");
                        $('li#id_' + id + ' div.torPause').show();
                        $('li#id_' + id + ' div.torPause').removeClass("hidden");
                        $('li#id_' + id + ' div.torTrash').show();
                        $('li#id_' + id + ' div.torTrash').removeClass("hidden");
                        $('li#id_' + id + ' div.torDelete').show();
                        $('li#id_' + id + ' div.torDelete').removeClass("hidden");
                        $('li#id_' + id).removeClass('item_###torHash###').addClass('item_' + torHash);
                        var item = $('li#id_' + id);
                        item.html(item.html().replace(/###torHash###/g, torHash));
                        setTimeout(getClientData, 10);
                    }
                });
    };
    $.delTorrent = function (torHash, trash, sure, checkCache) {
        if (trash && sure !== true && sure !== 'true' && window.Cookies.get('TorTrash') !== '1') {
            var dialog = '<div id="confirmTrash" class="dialog confirm" style="display: block; ">' +
                    '<div class="dialog_window" id="trash_tor_data"><div>Are you sure?<br />This will remove the torrent along with its data.</div>' +
                    '<div class="buttonContainer"><a class="button confirm" ' +
                    'onclick="$(\'#confirmTrash\').remove(); $.delTorrent(\'' + torHash + '\',\'true\', \'true\', \'false\');">Yes</a>' +
                    '<a class="button trash_tor_data wide" ' +
                    'onclick="$(\'#confirmTrash\').remove();' +
                    'window.Cookies.remove(\'TorTrash\');' +
                    'window.Cookies.set(\'TorTrash\', \'1\', {expires: 30, sameSite: \'lax\', path: \'\'});' +
                    '$.delTorrent(\'' + torHash + '\',\'true\', \'true\', \'false\');">' +
                    'Yes, don\'t ask again</a>' +
                    '<a class="button close" onclick="$(\'#confirmTrash\').remove()">No</a>' +
                    '</div>' +
                    '</div>';
            $('body').append(dialog);
            $('#confirmTrash').css('height', $(document).height() + 'px');
            $('#trash_tor_data').css('top', window.pageYOffset + (($(window).height() / 2) - $('#trash_tor_data').height()) + 'px');
        } else {
            $.getJSON('torrentwatch-xa.php', {
                'delTorrent': torHash,
                'trash': trash,
                'checkCache': checkCache
            },
                    function (json) {
                        if (json.result === "success") {
                            // there is no useful response other than the result
                            var torHashes = torHash.split(",");
                            // loop through each torrent hash in torHash array
                            for (var i = 0; i < torHashes.length; i++) {
                                if ($('li.item_' + torHashes[i]).length) {
                                    $('li.item_' + torHashes[i] + ' div.infoDiv').remove();
                                    $('li.item_' + torHashes[i] + ' div.progressBarContainer').hide();
                                    $('li.item_' + torHashes[i] + ' div.activeTorrent').hide();
                                    $('li.item_' + torHashes[i] + ' div.torStart').show();
                                    $('li.item_' + torHashes[i] + ' div.torStart').removeClass("hidden");
                                    $('li.item_' + torHashes[i])
                                            .removeClass('tc_waiting tc_verifying tc_downloading tc_seeding tc_paused st_favReady st_waitTorCheck st_downloading st_downloaded')
                                            .addClass('st_inCacheNotActive');
                                    // find the unknown class clientId_### and remove it
                                    //TODO this block might be unnecessary since the same cleanup is done in processClientData
                                    var classList = $('li.item_' + torHashes[i]).prop("className").split(/\s+/);
                                    var clientIdClass = "";
                                    for (var k = 0; k < classList.length; k++) {
                                        if (classList[k].substr(0, 9) === "clientId_") {
                                            clientIdClass = classList[k];
                                        }
                                    }
                                    if (clientIdClass !== "") {
                                        $('li.item_' + torHashes[i]).removeClass(clientIdClass);
                                    }
                                }
                                // completely remove the item from the Transmission filter
                                if ($('#transmission_data li.item_' + torHashes[i]).length) {
                                    $('#transmission_data li.item_' + torHashes[i]).remove();
                                }
                                //TODO double-check to make sure the list length is reduced by 1
                            }
                            setTimeout(getClientData, 10);
                        } else {
                            // as of 0.5.1, torrentwatch-xa.php's delTorrent returns result string "nothing to delete" here
                            $.fn.showErrorPanel("No torrent to delete.");
                        }
                    });
        }
    };
    $.stopStartTorrent = function (stopStart, torHash) {
        var param;
        if (stopStart === 'stop') {
            param = {stopTorrent: torHash};
        } else if (stopStart === 'start') {
            param = {startTorrent: torHash};
        }
        $.getJSON('torrentwatch-xa.php', param,
                function (json) {
                    if (json.result === "success") {
                        $('li.item_' + torHash + ' div.torStart').hide();
                        $('li.item_' + torHash + ' div.torStart').addClass("hidden");
                        toggleTorResumePause(torHash);
                        setTimeout(getClientData, 10);
                    } else {
                        $.fn.showErrorPanel("Failed to " + stopStart + " torrent.");
                    }
                });
    };
    $.moveTorrent = function (torHash) {
        var path = $('#moveTo').val();
        $.getJSON('torrentwatch-xa.php', {
            'moveTo': path,
            'torHash': torHash
        },
                function (json) { // Transmission RPC's set-torrent-location produces no response
                    toggleTorMove(torHash);
                    setTimeout(getClientData, 10);
                });
    };
    $.toggleFeedNameUrl = function (idx) {
        $('div.feeditem .feed_name').toggle();
        $('div.feeditem .feed_url').toggle();
        $('#feedNameUrl .item').toggle();
    };
    $.hideItem = function (title, id) {
        window.hiding = 1;
        $.get('torrentwatch-xa.php', {hide: title}, function (response) {
            if (response.match(/^twxa-ERROR:/)) {
                $.fn.showErrorPanel(response.match(/^twxa-ERROR:(.*)/)[1]);
            } else {
                $.each($('#torrentlist_container li'), function () {
                    if (this.querySelector('input.show_title').value === response) {
                        $(this).removeClass('selected').remove();
                    }
                });
            }
            window.hiding = null;
        });
    };
    $.toggleFeed = function (feed, speed) {
        if (speed === 1 || speed === '1') {
            if (window.Cookies.get('feed_' + feed) === '1') { // was ==
                $("#feed_" + feed + " ul").removeClass("hiddenFeed").show();
                $("#feed_" + feed + " .header").removeClass("header_hidden");
                window.Cookies.remove('feed_' + feed, {sameSite: 'lax'});
                window.Cookies.set('feed_' + feed, '0', {expires: 30, sameSite: 'lax', path: ''});
            } else {
                $("#feed_" + feed + " ul").hide().addClass("hiddenFeed");
                $("#feed_" + feed + " .header").addClass("header_hidden");
                window.Cookies.remove('feed_' + feed, {sameSite: 'lax'});
                window.Cookies.set('feed_' + feed, '1', {expires: 30, sameSite: 'lax', path: ''});
            }
        } else {
            if (window.Cookies.get('feed_' + feed) === '1') { // was ==
                $("#feed_" + feed + " ul").removeClass("hiddenFeed").slideDown();
                $("#feed_" + feed + " .header").removeClass("header_hidden");
                window.Cookies.remove('feed_' + feed, {sameSite: 'lax'});
                window.Cookies.set('feed_' + feed, '0', {expires: 30, sameSite: 'lax', path: ''});
            } else {
                $("#feed_" + feed + " ul").slideUp().addClass("hiddenFeed");
                $("#feed_" + feed + " .header").addClass("header_hidden");
                window.Cookies.remove('feed_' + feed, {sameSite: 'lax'});
                window.Cookies.set('feed_' + feed, '1', {expires: 30, sameSite: 'lax', path: ''});
            }
        }
    };
    $.checkHiddenFeeds = function (speed) {
        $.each($("#torrentlist_container .feed"), function () {
            if (window.Cookies.get(this.id) === '1') {
                if (speed === 1) {
                    $("#" + this.id + " ul").hide().addClass("hiddenFeed");
                } else {
                    $("#" + this.id + " ul").slideUp().addClass("hiddenFeed");
                }
                $("#" + this.id + " .header").addClass("header_hidden");
            }
        });
    };
    $.toggleConfigTab = function (tab, button) {
        $(".toggleConfigTab").removeClass("selTab");
        $(button).addClass("selTab");
        $(".configTab").hide();
        $("#configuration form").hide();
        if (tab === "#config_feeds") {
            $("#configuration .feedform").show();
        } else if (tab === "#config_hideList") {
            $("#hidelist_form").show();
        } else {
            $("#config_form").show();
        }
        $(tab).animate({opacity: "toggle"}, 500);
    };
    $.toggleContextMenu = function (item_id, id) {
        // do NOT use slideUp() or slideDown() in this function as they are too slow and get interrupted
        if ($("div.contextMenu").not(item_id).is(":visible")) {
            $("div.contextMenu").hide();
        }
        if ($(item_id).is(":visible")) {
            $(item_id).hide();
        } else {
            $(item_id).show();
            $("div.contextMenu, a.contextButton").on("mouseleave", function () {
                var contextTimeout = setTimeout(function () {
                    $(item_id).hide();
                }, 500);
                $("div.contextMenu, a#contextButton_" + id).on("mouseenter", function () {
                    clearTimeout(contextTimeout);
                });
                $("div.contextItem").on("mouseover", function () {
                    $(this).addClass("alt");
                    $(this).on("mouseleave", function () {
                        $(this).removeClass("alt");
                    });
                });
                $(item_id).on("click", function () {
                    this.style.display = 'none';
                });
            });
        }
    };
    $.processSelected = function (action) {
        if (!$('#torrentlist_container .torrent.selected').length) {
            return;
        }
        var list = '';
        $.each($('#torrentlist_container .torrent.selected'), function () {
            if (this.className.match(/item_\w+/) && !this.className.match(/item_###torHash###/)) {
                if (list) {
                    list = list + ',' + this.className.match(/item_(\w+)/)[1];
                } else {
                    list = list + this.className.match(/item_(\w+)/)[1];
                }
            }
        });
        var trash = false;
        switch (action) {
            case 'trash':
                trash = true;
                // no break!
            case 'delete':
                $.delTorrent(list, trash, false, false); // torHash, trash, sure, checkCache
                break;
            case 'start':
                $.stopStartTorrent('start', list);
                break;
            case 'stop':
                $.stopStartTorrent('stop', list);
                break;
            case 'move':
                $.moveTorrent(list);
        }
        $.each($('#torrentlist_container .feed li.selected'), function () {
            var title = this.querySelector('input.title').value;
            var link = this.querySelector('input.link').value;
            var linkType = this.querySelector('input.link_type').value;
            var feedLink = this.querySelector('input.feed_link').value;
            var id = this.querySelector('input.client_id').value;
            if (action === 'addFavorite') {
                var favInterval = setInterval(function () {
                    if (window.favving !== 1) {
                        $.addFavorite(feedLink, title);
                        clearInterval(favInterval);
                    }
                }, 100);
            }
            if (!$(this).hasClass('st_downloading') && !$(this).hasClass('st_downloaded')) {
                switch (action) {
                    case 'dlTorrent':
                        $.dlTorrent(title, link, linkType, feedLink, id);
                        break;
                    case 'hideItem':
                        var hideInterval = setInterval(function () {
                            if (window.hiding !== 1) {
                                $.hideItem(title, id);
                                clearInterval(hideInterval);
                            }
                        }, 100);
                }
            }
        });
    };
})(jQuery);