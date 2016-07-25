//TODO 'use strict';
$(function () {
    // Menu Bar, and other buttons which show/hide a dialog
    $("a.toggleDialog").live('click',
            function () {
                $(this).toggleDialog();
    });

    // Vary the font-size
    /*changeFontSize = function (fontSize) {
        var f = fontSize;
        $.cookie('twFontSize', f, {expires: 666});
        switch (f) {
        case 'Small':
            $("body").css('font-size', '75%');
            break;
        case 'Medium':
            $("body").css('font-size', '85%');
            break;
        case 'Large':
            $("body").css('font-size', '100%');
            break;
        }
    };*/

    displayFilter = function (filter, empty) {
        var timeOut = 400;
        if (empty == 1 || navigator.appName == 'Microsoft Internet Explorer' || navigator.userAgent.toLowerCase().search('(iphone|ipod|android)') > -1) {
            $.fn.hideMe = function () {
                $(this).hide();
                timeOut = 0;
            };
        } else {
            $.fn.hideMe = function () {
                $(this).slideUp();
            };
        }
        if (empty == 1 || navigator.appName == 'Microsoft Internet Explorer' || navigator.userAgent.toLowerCase().search('(iphone|ipod|android)') > -1) {
            $.fn.showMe = function () {
                $(this).show();
                timeOut = 0;
            };
        } else {
            $.fn.showMe = function () {
                $(this).slideDown();
            };
        }
        clearInterval(window.filterInterval);
        $.cookie('TWFILTER', filter, {expires: 666});
        if (filter == 'all') {
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
        } else if (filter == 'matching') {
            if ($('.transmission').is(":visible")) {
                $('.transmission').hideMe();
                $('.header.combined').showMe();
                $('#torrentlist_container li.torrent.selected').removeClass('selected');
            } else {
                $('.feed').hideMe();
            }
            setTimeout(function () {
                var tor = $(".feed li.torrent").filter(".match_nomatch");
                $(tor).hide();
                tor = $(".feed li.torrent").not(".match_nomatch");
                $(tor).show();
                $('.feed').showMe();
                tor.markAlt().closest(".feed div.feed");
                updateMatchCounts();
            }, timeOut);
        } else if (filter == 'downloading') {
            if ($('.transmission').is(":visible")) {
                $('.transmission').hideMe();
                $('.header.combined').showMe();
                $('#torrentlist_container li.torrent.selected').removeClass('selected');
            } else {
                $('.feed').hideMe();
            }
            var showFilter = function () {
                var tor = $(".feed li.torrent").not('.match_downloading');
                $(tor).hide();
                tor = $(".feed li.torrent").filter('.match_downloading');
                $(tor).show();
                $('.feed').showMe();
                tor.markAlt().closest(".feed div.feed");
                updateMatchCounts();
            };
        } else if (filter == 'downloaded') {
            if ($('.transmission').is(":visible")) {
                $('.transmission').hideMe();
                $('.header.combined').showMe();
                $('#torrentlist_container li.torrent.selected').removeClass('selected');
            } else {
                $('.feed').hideMe();
            }
            var showFilter = function () {
                var tor = $(".feed li.torrent").not('.match_downloaded');
                $(tor).hide();
                tor = $(".feed li.torrent").filter('.match_downloaded');
                $(tor).show();
                $('.feed').showMe();
                tor.markAlt().closest(".feed div.feed");
                updateMatchCounts();
            };
        } else if (filter == 'transmission') {
            if ($('.feed').is(':visible')) {
                $('.feed').hideMe();
                $('.header.combined').hideMe();
                $('#torrentlist_container li.torrent.selected').removeClass('selected');
            }
            setTimeout(function () {
                $('.transmission').showMe();
                $('#transmission_list li.torrent').markAlt();
                updateMatchCounts();
            }, timeOut);
        }
        if (showFilter) {
            //TODO why does this call to showFilter only apply to Downloading and Downloaded filters?
            window.filterInterval = setInterval(function () {
                showFilter();
            }, 500);
        }
        setTimeout(updateClientButtons, timeOut);
        $.checkHiddenFeeds(1);
        $('#filter_' + filter).addClass('selected').siblings().removeClass("selected");
        $('#filter_search_input').val('');
    };

    // Filter Bar - Buttons
    $("ul#filterbar_container li:not(#filter_bytext)").click(function () {
        if ($(this).is('.selected')) {
            return;
        }
        var filter = this.id;
        $("div#torrentlist_container").show(function () {
            switch (filter) {
            case 'refresh':
                $.get('torrentwatch-xa.php', '', $.loadDynamicData, 'html');
                break;
            case 'filter_all':
                displayFilter('all');
                $.checkHiddenFeeds(1);
                break;
            case 'filter_matching':
                displayFilter('matching');
                break;
            case 'filter_downloading':
                displayFilter('downloading');
                break;
            case 'filter_downloaded':
                displayFilter('downloaded');
                break;
            case 'filter_transmission':
                displayFilter('transmission');
                break;
            }
        });
    });

    // Filter Bar -- By Text
    $("input#filter_search_input").keyup(function () {
        var filterText = $(this).val().toLowerCase();
        $("li.torrent").hide().each(function () {
            if ($(this).find(".torrent_name").text().toLowerCase().match(filterText)) {
                $(this).show();
            }
        }).markAlt();
    });

    // Switching visible items for different torrent clients
    changeClient = function (client) {
        $(".favorite_seedratio, #config_folderclient").css("display", "none");
        $("#torrent_settings").css("display", "block");
        switch (client) {
        case 'folder':
            $(".config_form .tor_settings, div.category tor_settings, #torrent_settings, div.favorite_savein, #config_tr_user, #config_tr_password, #config_tr_host, #config_tr_port, #filter_transmission, #tabTor").css("display", "none");
            $("#config_folderclient, #config_downloaddir").css("display", "block");
            $("#filter_transmission").removeClass('filter_right');
            $("#filter_downloaded").addClass('filter_right');
            $("form.favinfo, ul.favorite");
            $('li#webui').hide();
            window.client = 'folder';
            break;
        case 'Transmission':
            $(".config_form .tor_settings, div.category tor_settings, #config_tr_user, #config_tr_password, #config_tr_host, #config_tr_port, #config_downloaddir, div.favorite_seedratio, div.favorite_savein,#filter_transmission, #tabTor").css("display", "block");
            $("#filter_downloaded").removeClass('filter_right');
            $("#filter_transmission").addClass('filter_right');
            $("ul.favorite").css("height", 245);
            adjustWebUIButton();
            window.client = 'Transmission';
            break;
        }
    };

    // Perform the first load of the dynamic information
    $.get('torrentwatch-xa.php', '', $.loadDynamicData, 'html');

    // Configuration, wizard, and update/delete favorite ajax submit
    $("a.submitForm").live('click',
            function (e) {
            window.input_change = 0;
        e.stopImmediatePropagation();
        $.submitForm(this);
        if (this.parentNode.id) {
            $('div#' + this.parentNode.id).hide();
        }
    });
    // Clear History ajax submit
    $("a#clearhistory").live('click',
            function () {
                $.get(this.href, '',
                function (html) {
                            $("div#history").html($(html).html());
        },
                'html');
        return false;
    });
    // Clear Cache ajax submit
    $('a.clear_cache').live('click',
            function (e) {
                $.get(this.href, '', $.loadDynamicData, 'html');
        return false;
    });

    Math.formatBytes = function (bytes) {
        var size;
        var unit;

        // Terabytes (TB).
        if (bytes >= 1099511627776) {
            size = bytes / 1099511627776;
            unit = ' TB';

            // Gigabytes (GB).
        } else if (bytes >= 1073741824) {
            size = bytes / 1073741824;
            unit = ' GB';

            // Megabytes (MB).
        } else if (bytes >= 1048576) {
            size = bytes / 1048576;
            unit = ' MB';

            // Kilobytes (KB).
        } else if (bytes >= 1024) {
            size = bytes / 1024;
            unit = ' KB';

            // The file is less than one KB
        } else {
            size = bytes;
            unit = ' B';
        }

        // Single-digit numbers have greater precision
        var precision = 2;
        size = Math.roundWithPrecision(size, precision);

        return size + unit;
    };

    Math.roundWithPrecision = function (floatnum, precision) {
        return Math.round(floatnum * Math.pow(10, precision)) / Math.pow(10, precision);
    };

    torStartStopToggle = function (torHash) {
        var curObject = $('li.item_' + torHash + ' div.torStart');
        if (curObject.css('display') == 'block') {
            curObject.hide();
        } else {
            curObject.show();
        }
        curObject = $('li.item_' + torHash + ' div.torPause');
        if (curObject.css('display') == 'block') {
            curObject.hide();
        } else {
            curObject.show();
        }
        curObject = null;
    };

    toggleTorMove = function (torHash) {
        var curObject = $('ul#clientButtons li.move_data, ul#clientButtons li#Move');
        if (curObject.is(":visible")) {
            curObject.fadeOut('normal', updateClientButtons);
        } else {
            curObject.fadeIn('normal', updateClientButtons);
        }
        curObject = null;
    };

    getClientItem = function (item, clientData, liClass, Percentage) {
        var hideStop;
        var hideStart;
        if (item.status == 16) {
            hideStop = 'hidden';
        } else {
            hideStart = 'hidden';
        }

        var transmissionItem =
                '<li id="clientId_' + item.id + '" class="torrent item_' + item.hashString + ' match_transmission ' + liClass + '">' +
                '<table width="100%" cellspacing="0"><tr><td class="tr_identifier"></td>' +
                '<td class="torrent_name tor_client">' +
                '<div class="torrent_name"><span class="torrent_title">' + item.name.replace(/[._]/g, '.&shy;') + '</span></div>' +
                '<div style="width: 100%; margin-top: 2px; border: 1px solid #BFCEE3; background: #DFE3E8;">' +
                '<div class="progressDiv" style="width: ' + Percentage + '%; height: 3px;"></div></div>' +
                '<span class="dateAdded hidden">' + item.addedDate + '</span>' +
                '<div class="infoDiv"><span id=tor_' + item.id + ' class="torInfo tor_' + item.hashString + '">' + clientData + '</span>' +
                '<span class="torEta">' + item.eta + '</span></div>' +
                '<input type="hidden" class="path" value="' + item.downloadDir + '"></input>' +
                '</td></tr></table></li>';

        return (transmissionItem);
    };

    showClientError = function (error) {
        $('div#clientError p').html(error);
        $('div#clientError').slideDown();
    };

    window.clientErrorCount = 0;
    $(document).ajaxError(function (event, request, settings) {
        if (settings.url.match(/getClientData/)) {
            window.getfail = 1;
            var error = "Error connecting to " + window.client;
            window.clientErrorCount++;
            $('span.torInfo').html(error);
            $('div.feed span.torInfo').addClass('torInfoErr');
            $('li#filter_transmission a').addClass('error');
            if (window.clientErrorCount >= 3) {
                showClientError(error);
            }
        }
    });

    getClientData = function () {
        if (window.ajaxActive) {
            return;
        }
        if (window.client == 'Transmission') {
            var recent;
            window.updatingClientData = 1;
            if (window.gotAllData && window.getfail != 1) {
                recent = 1;
            } else {
                recent = 0;
            }

            window.hideProgressBar = 1;
            setTimeout(function () {
                if (window.updatingClientData) {
                    $('li#webui a span').addClass('altIcon');
                }
            }, 1500);

            $.get('torrentwatch-xa.php', {
                'getClientData': 1,
                'recent': recent
            },
                    function (json) {
                window.updatingClientData = 0;
                var check = json.match(/\S+/);
                if (check == 'null') {
                    window.getfail = 1;
                    var error = 'Got no data from ' + window.client;
                    showClientError(error);
                    $('span.torInfo').html(error);
                    $('div.feed span.torInfo').addClass('torInfoErr');
                    $('li#filter_transmission a').addClass('error');
                    return;
                }

                try {
                    json = JSON.parse(json);
                } catch (err) {
                    showClientError(json);
                    return;
                }

                window.clientErrorCount = 0;
                $('li#filter_transmission a').removeClass('error');
                $('div.feed span.torInfo').removeClass('torInfoErr');

                processClientData(json, recent);
                $('li#webui a span').removeClass('altIcon');

                if (json && recent) {
                    $.each(json['arguments']['removed'],
                            function (i, item) {
                        if ($('li.clientId_' + item).length) {
                            $('li.clientId_' + item + ' div.infoDiv').remove();
                            $('li.clientId_' + item + ' div.progressBarContainer').hide();
                            $('li.clientId_' + item + ' div.activeTorrent').hide();
                            $('li.clientId_' + item + ' div.dlTorrent').show();
                            $('li.clientId_' + item + ', li.clientId_' + item + ' td.buttons')
                                .removeClass('match_downloading match_downloaded downloading match_cachehit').addClass('match_old_download');
                            $('li.clientId_' + item).removeClass('clientId_' + item);
                        }
                        if ($('#transmission_data li#clientId_' + item).length) {
                            $('#transmission_data li#clientId_' + item).remove();
                        }
                    });
                }
            });
            window.hideProgressBar = null;
        }
    };

    updateMatchCounts = function () {
        // update filter and feed headers with total match counts
        var activeTorrents = $('#transmission_list li').length;
        $('#activeTorrents').html("(" + activeTorrents + ")");
        if (!activeTorrents) {
            window.gotAllData = 1;
        }
        var totalMatching = $('.feed li.torrent').not('.match_nomatch').length;
        var totalDownloaded = $('.feed li.match_downloaded').length;
        var totalDownloading = $('.feed li.match_downloading').length;
        $('#Matching, #Downloaded, #Downloading').html('');
        if (totalMatching) {
            $('#Matching').html('(' + totalMatching + ')');
        }
        if (totalDownloaded) {
            $('#Downloaded').html('(' + totalDownloaded + ')');
        }
        if (totalDownloading) {
            $('#Downloading').html('(' + totalDownloading + ')');
        }
        $.each($('.feed'), function (i, item) {
            var matches = $('#' + item.id + ' li.torrent').not('.match_nomatch').not(':hidden').length;
            $('#' + item.id + ' span.matches').html('(' + matches + ')');
        });
        listSelector();
        updateClientButtons();
    };

    processClientData = function (json, recent) {
        if (json === null) {
            $('div#clientError p').html('Torrent client did not return any data.' +
                    'This usually happens when the client is not active.');
            $('div#clientError').slideDown();
            window.errorActive = 1;
            return;
        }

        if (window.errorActive == 1) {
            $('div#clientError').slideUp();
            window.errorActive = null;
        }

        //var oldStatus;
        var liClass;
        var clientData;
        var clientItem;
        var torListHtml = "";
        var upSpeed = 0;
        var downSpeed = 0;

        if (!(window.oldStatus)) {
            window.oldStatus = [];
        }
        if (!(window.oldClientData)) {
            window.oldClientData = [];
        }

        $.each(json['arguments']['torrents'],
                function (i, item) {
            var Ratio = Math.roundWithPrecision(item.uploadedEver / item.downloadedEver, 2);
            var Percentage = Math.roundWithPrecision(((item.totalSize - item.leftUntilDone) / item.totalSize) * 100, 2);
            var validProgress = Math.roundWithPrecision((100 * item.recheckProgress), 2);

            if (!(Ratio > 0)) {
                Ratio = 0;
            }

            if (!(Percentage > 0)) {
                Percentage = 0;
            }

            // Remap Transmission <v2.4 status codes to v2.4
            if (item.status == 0) { // stopped
                item.status = 16; // stopped
            }
            if (item.status == 3) { // download waiting
                item.status = 4; // downloading
            }
            if (item.status == 5) { // seed waiting
                item.status = 8; // seeding
            }
            if (item.status == 6) { // seeding
                item.status = 8; // seeding
            }

            $('li.item_' + item.hashString + ' div.progressBarContainer').show();
            $('li.item_' + item.hashString + ' div.progressDiv').width(Percentage + "%").height(3);

            if (item.errorString || item.status == 4) {
                if (item.eta >= 86400) {
                    var days = Math.floor(item.eta / 86400);
                    var hours = Math.floor((item.eta / 3600) - (days * 24));
                    var minutes = Math.round((item.eta / 60) - (days * 1440) - (hours * 60));
                    if (minutes <= 9) {
                        minutes = '0' + minutes;
                    }
                    item.eta = 'Remaining: ' + days + ' days ' + hours + ' hr ' + minutes + ' min';
                } else if (item.eta >= 3600) {
                    var hours = Math.floor(item.eta / 60 / 60);
                    var minutes = Math.round((item.eta / 60) - (hours * 60));
                    item.eta = 'Remaining: ' + hours + ' hr ' + minutes + ' min';
                } else if (item.eta > 0) {
                    var minutes = Math.round(item.eta / 60);
                    var seconds = item.eta - (minutes * 60);
                    if (seconds < 0) {
                        minutes--;
                        seconds = seconds + 60;
                    }
                    if (item.eta < 60) {
                        item.eta = 'Remaining: ' + item.eta + ' sec';
                    } else {
                        item.eta = 'Remaining: ' + minutes + ' min ' + seconds + ' sec';
                    }
                } else {
                    item.eta = 'Remaining: unknown';
                }
            } else {
                item.eta = '';
            }

            liClass = 'normal';
            if (item.status == 1) {
                clientData = 'Waiting to verify...';
                liClass = 'waiting';
                $('li.torrent span.torEta').html('');
            } else if (item.status == 2) {
                clientData = 'Verifying files (' + validProgress + '%)';
                liClass = 'verifying';
                $('li.torrent span.torEta').html('');
            } else if (item.status == 4) {
                clientData = 'Downloading from ' + item.peersSendingToUs + ' of ' +
                        item.peersConnected + ' peers: ' +
                        Math.formatBytes(item.totalSize - item.leftUntilDone) + ' of ' +
                        Math.formatBytes(item.totalSize) +
                        ' (' + Percentage + '%)  -  Ratio: ' + Ratio;
                liClass = "downloading";
            } else if (item.status == 8) {
                clientData = 'Seeding to ' + item.peersGettingFromUs + ' of ' +
                        item.peersConnected + ' peers  -  Ratio: ' + Ratio;
                $('li.item_' + item.hashString).removeClass('match_downloading').addClass('match_downloaded');
                $('li.torrent span.torEta').html('');
            } else if (item.status == 16) {
                if (Ratio >= item.seedRatioLimit && Percentage == 100) {
                    clientData = "Downloaded and seed ratio met. This torrent can be removed.";
                    $('li.item_' + item.hashString).removeClass('match_downloading').addClass('match_downloaded');
                    // auto-delete seeded torrents
                    $.get('torrentwatch-xa.php', { get_autodel: 1 }, function(autodel) {
                    if (autodel) {
                        $.delTorrent(item.hashString, false, true);
                    }
                    });
                } else {
                    clientData = "Paused";
                }
                liClass = 'paused';
                $('li.torrent span.torEta').html('paused');
            }

            if (item.errorString) {
                clientData = item.errorString;
            }

            $('li.match_old_download span.torInfo, li.match_old_download span.torEta').html('');
            if (recent == 1) {
                clientItem = getClientItem(item, clientData, liClass, Percentage);

                if (!$('#transmission_list li#clientId_' + item.id).length) {
                    $('#transmission_list').prepend(clientItem);
                }

                $('li.item_' + item.hashString + ' span.torInfo').text(clientData);
                if (item.status == 4) {
                    $('li.item_' + item.hashString + ' span.torEta').text(item.eta);
                }

                if (window.oldStatus[item.id] != item.id + '_' + item.status) {
                    if (item.status == 16 || item.errorString) {
                        $('li.item_' + item.hashString + ' div.torPause').hide();
                        $('li.item_' + item.hashString + ' div.torStart').show();
                    } else {
                        var curTorrent = $('li.item_' + item.hashString + ' div.torStart');
                        if (curTorrent.is(":visible")) {
                            torStartStopToggle(item.hashString);
                        }
                    }

                    $('li.item_' + item.hashString).addClass('clientId_' + item.id);

                    if (item.leftUntilDone === 0) {
                        $('.item_' + item.hashString + '.match_downloading')
                                .removeClass('match_downloading').addClass('match_cachehit');
                    }
                }
            } else {
                if (window.getfail) {
                    window.getfail = 0;
                }
                //console.info("item = " + JSON.stringify(item)); //TODO remove this
                clientItem = getClientItem(item, clientData, liClass, Percentage);
                torListHtml += clientItem;
                $('li.item_' + item.hashString + ' span.torInfo').text(clientData);
                if (item.status == 4) {
                    $('li.item_' + item.hashString + ' span.torEta').text(item.eta);
                }
                if (item.status <= 16) {
                    var match = null;
                    if (item.status == 8 || item.leftUntilDone == 0) {
                        //TODO is it safe to assume that if item.status == 8, it really is fully downloaded?
                        match = 'match_downloaded';
                    } else {
                        match = 'match_downloading';
                    }
                    $('li.item_' + item.hashString + ' ,li.item_' + item.hashString + ' .buttons')
                            .removeClass('match_to_check').addClass(match);
                    $('li.item_' + item.hashString + ' div.torDelete').show();
                    $('li.item_' + item.hashString + ' div.torTrash').show();
                    if (item.status == 16) {
                        $('li.item_' + item.hashString + ' div.torPause').hide();
                        $('li.item_' + item.hashString + ' div.torStart').show();
                    } else {
                        $('li.item_' + item.hashString + ' div.torStart').hide();
                        $('li.item_' + item.hashString + ' div.torPause').show();
                    }
                    $('li.item_' + item.hashString + ' div.dlTorrent').hide();
                }

                $('#transmission_list').empty();
                $('li.item_' + item.hashString).addClass('clientId_' + item.id);
                window.gotAllData = 1;
            }

            $('#torrentlist_container li.item_' + item.hashString)
                    .removeClass('paused downloading verifying waiting').addClass(liClass);

            upSpeed = upSpeed + item.rateUpload;
            downSpeed = downSpeed + item.rateDownload;

            window.oldClientData[item.id] = clientData;
            window.oldStatus[item.id] = item.id + '_' + item.status;
            function count(arrayObj) {
                return arrayObj.length;
            }
        });

        if (!json['arguments']['torrents'].length) {
            window.gotAllData = 1;
        }

        if (!isNaN(downSpeed) && !isNaN(upSpeed)) {
            $('li#rates').html('D: ' + Math.formatBytes(downSpeed) + '/s&nbsp;&nbsp;</br>' + 'U: ' + Math.formatBytes(upSpeed) + '/s');
        }

        if (recent === 0 && torListHtml) {
            $('#transmission_list').append(torListHtml);
        }

        if (window.gotAllData) {
            //TODO do not hide progressBarContainer for items that are currently downloading (check torrent status after browser refresh)
            $('#torrentlist_container li.match_to_check, #torrentlist_container li.match_to_check .buttons').addClass('match_old_download').removeClass('match_to_check');
            $('#torrentlist_container li.match_old_download .progressBarContainer, #torrentlist_container li.match_old_download .activeTorrent.torDelete, #torrentlist_container li.match_old_download .activeTorrent.torTrash').hide();
            $('#torrentlist_container li.match_old_download div.infoDiv, #torrentlist_container li.match_old_download .torEta').remove();
            $('#torrentlist_container li.match_old_download div.torPause').hide();
            $('#torrentlist_container li.match_old_download div.dlTorrent').show();
        }

        setTimeout(updateMatchCounts(), 100);

        $('#transmission_list>li').tsort('span.dateAdded', {order: 'desc'});
        $('#transmission_list li.torrent').markAlt();
    };

    listSelector = function () {
        $('#torrentlist_container li.torrent').not('.selActive').each(function () {
            $(this).addClass('selActive');
            $('#' + this.id).mousedown(function () {
                toggleSelect(this);
            });
        });
        $('li.torrent').find('a').mousedown(function () {
            return false;
        });
        $('li.torrent').find('div.contextItem').mousedown(function () {
            return false;
        });
    };

    toggleSelect = function (item) {
        if ($(item).hasClass('selected')) {
            $(item).removeClass('selected');
        } else {
            $(item).addClass('selected');
        }
        if ($('#torrentlist_container li.torrent.selected').length) {
            updateClientButtons();
            $('input#moveTo').val($('#' + item.id + ' input.path').val());
        } else {
            updateClientButtons();
            $('input#moveTo').val('');
        }
    };

    updateClientButtons = function (fast) {
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
        if ($('#torrentlist_container .feed  li.torrent.selected.match_nomatch').length) {
            tor['hide'] = 1;
        }
        if ($('#torrentlist_container .feed li.selected').not('.match_downloading').not('.match_downloaded').length) {
            tor['start'] = 1;
        }

        if (window.client != 'folder') {
            if ($('#torrentlist_container li.selected.paused').length) {
                tor['resume'] = 1;
                tor['del'] = 1;
                tor['trash'] = 1;
                if ($('#transmission_data').is(':visible')) {
                    tor['move'] = 1;
                }
            }
            if ($('#torrentlist_container li.selected.match_downloading:not(.paused),' +
                    '#torrentlist_container li.selected.match_downloaded:not(.paused),' +
                    '#torrentlist_container li.selected.match_transmission:not(.paused)').length) {
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
            if (obj == 'start') {
                buttons += "#clientButtons .start,";
            }
            if (obj == 'fav') {
                buttons += "#clientButtons .add_fav,";
            }
            if (obj == 'hide') {
                buttons += "#clientButtons .hide_item,";
            }
            if (obj == 'pause') {
                buttons += "#clientButtons .pause,";
            }
            if (obj == 'resume') {
                buttons += "#clientButtons .resume,";
            }
            if (obj == 'del') {
                buttons += "#clientButtons .delete,";
            }
            if (obj == 'trash') {
                buttons += "#clientButtons .trash,";
            }
            if (obj == 'move') {
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
                if ($('#clientButtonsHolder').is(':visible') == false) {
                    $('#clientButtonsHolder').show();
                }
            } else {
                if ($('#clientButtonsHolder').is(':visible') == false) {
                    $('#clientButtonsHolder').fadeIn();
                }
            }
            if (navigator.userAgent.toLowerCase().search('(iphone|ipod|android)') > -1) {
                if ($('#moveTo').is(':focus')) {
                    return;
                }
                if (navigator.userAgent.match('iPhone OS 5')) {
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
                if ($('#clientButtonsHolder').is(':visible') == true) {
                    $('#clientButtonsHolder').hide();
                }
            } else {
                if ($('#clientButtonsHolder').is(':visible') == true) {
                    $('#clientButtonsHolder').fadeOut(200);
                }
            }
            $('#clientButtons .move_data').hide();
        }
    };

    function getScrollBarWidth() {
        var inner = document.createElement('p');
        inner.style.width = "100%";
        inner.style.height = "200px";

        var outer = document.createElement('div');
        outer.style.position = "absolute";
        outer.style.top = "0px";
        outer.style.left = "0px";
        outer.style.visibility = "hidden";
        outer.style.width = "200px";
        outer.style.height = "150px";
        outer.style.overflow = "hidden";
        outer.appendChild(inner);

        document.body.appendChild(outer);
        var w1 = inner.offsetWidth;
        outer.style.overflow = 'scroll';
        var w2 = inner.offsetWidth;
        if (w1 == w2) {
            w2 = outer.clientWidth;
        }

        document.body.removeChild(outer);

        return (w1 - w2);
    }

    function adjustWebUIButton() {
        switch (window.client) {
            case "Transmission" :
                // shrink/expand/hide/show Web UI button to fit window
                if ($(window).width() < 545) {
                    $("li#webui").hide();
                    $("span#webui").hide();
                }
                else if ($(window).width() < 620) {
                    $("li#webui").show();
                    $("span#webui").hide();
                }
                else {
                    $("li#webui").show();
                    $("span#webui").show();
                }
                break;
            case "folder" :
            default :
            $("li#webui").hide();
        }
    }
    
    function adjustUIElements() {
        // NOTE: No need to overdo handling below 640px wide due to phone.css
        // shrink/expand Downloading
        if ($(window).width() < 650) {
            $("ul#filterbar_container li#filter_downloading.tab").hide();
        }
        else {
            $("ul#filterbar_container li#filter_downloading.tab").show();
        }
        // shrink/expand Downloaded
        if ($(window).width() < 650) {
            $("ul#filterbar_container li#filter_downloaded.tab").hide();
        }
        else {
            $("ul#filterbar_container li#filter_downloaded.tab").show();
        }
        adjustWebUIButton();
        // hide/show Filter field
        if ($(window).width() < 870) {
            $("li#filter_bytext").hide();
        }
        else {
            $("li#filter_bytext").show();
        }
    }

    $(document).ready(function () {
        adjustUIElements();
        var supportsOrientationChange = "onorientationchange" in window,
                orientationEvent = supportsOrientationChange ? "orientationchange" : "resize";
        window.addEventListener(orientationEvent, toggleClientButtons, false);
        window.onresize = function (event) {
            adjustUIElements();
        };
        var waitForDynData = setInterval(function () {
            if ($('#dynamicdata').length) {
                listSelector();
                clearInterval(waitForDynData);
            }
        }, 500);
    });

    $(document).keyup(function (e) {
        if (e.keyCode == '27') {
            if ($('.dialog').length) {
                $('.dialog .close').click();
                $('div.contextMenu').hide();
            } else if ($('#clientButtons .move_data').is(":visible")) {
                $('#clientButtons .close').click();
            } else if ($('#torrentlist_container li.torrent.selected').length) {
                $('#torrentlist_container li.torrent.selected').removeClass('selected');
                updateClientButtons();
            }
        }
        if (e.keyCode == '13') {
            if ($('.dialog .confirm').length) {
                $('.dialog .confirm').click();
            } else if ($('#clientButtons .move_data').is(":visible")) {
                $('#clientButtons #Move').click();
            }
        }
        if (e.keyCode == '17' || e.keyCode == '91' || e.keyCode == '224') {
            window.ctrlKey = 0;
        }
    });

    $(document).keydown(function (e) {
        if (e.keyCode == '17' || e.keyCode == '91' || e.keyCode == '224') {
            window.ctrlKey = 1;
        }
        if (window.ctrlKey && e.keyCode == '65') {
            //TODO fix the bug where it thinks Cmd is being held down when it is not immediately after Cmd-Tab to switch to the browser
            if ($('#torrentlist_container li.torrent.selected').length == $('#torrentlist_container li.torrent').length) {
                $('#torrentlist_container li.torrent').removeClass('selected');
            } else {
                $('#torrentlist_container li.torrent').addClass('selected');
            }
            updateClientButtons();
            return false;
        }
    });

    // Ajax progress bar
    $('#refresh').ajaxStart(function () {
        window.ajaxActive = 1;
        if (!(window.hideProgressBar)) {
            $('#refresh a').html('<img src="images/ajax-loader-small.gif">');
            if ($('div.dialog').is(":visible")) {
                $('#progress').removeClass('progress_full').fadeIn();
            }
            if ($('ul#clientButtons').is(":visible")) {
                window.visibleButtons = $('div#clientButtonsHolder li.button').not('.hidden');
                window.hideButtonHolder = setTimeout(function () {
                    $(window.visibleButtons).hide();
                    $('ul#clientButtons').append('<div id="clientButtonsBusy"><img src="images/ajax-loader-small.gif">Working...</div>');
                }, 500);
            }
        }
    }).ajaxStop(function () {
        window.ajaxActive = 0;
        $('#refresh a').html('<img src="images/refresh.png">');
        $('#progress').fadeOut();
        $('div#clientButtonsBusy').remove();
        if (window.hideButtonHolder) {
            clearTimeout(hideButtonHolder);
        }
        if (window.visibleButtons) {
            $(window.visibleButtons).show();
        }
        updateClientButtons();
        setTimeout(function () {
            $('#transmission_list li.torrent').markAlt();
        }, 500);
    });

    // set timeout for all ajax queries to 20 seconds.
    $.ajaxSetup({timeout: '20000'});

});

(function ($) {
    var current_favorite,
            current_dialog;

    // Remove old dynamic content, replace it with passed html(ajax success function)
    $.loadDynamicData = function (html) {
        window.gotAllData = 0;
        $("#dynamicdata").remove();
        $('ul#mainoptions li a').removeClass('selected');
        setTimeout(function () {
            var dynamic = $("<div id='dynamicdata' class='dyndata'/>");
            // Use innerHTML because some browsers choke with $(html) when html is many KB
            dynamic[0].innerHTML = html;
            dynamic.find("ul.favorite > li").initFavorites().end()
                    .find("form").initForm().end().initConfigDialog().appendTo("body");
            setTimeout(function () {
                var container = $("#torrentlist_container");

                var filter = $.cookie('TWFILTER');
                $('li.torrent:not(.match_to_check) div.progressBarContainer').hide();
                if (!(filter)) {
                    filter = 'all';
                }
                if ($('#transmission_data').length) {
                    $('a#torClient ').show().html('Transmission');
                } else {
                    $('a#torClient').hide();
                    $('div.activeTorrent.torDelete').hide();
                    $('div.activeTorrent.torTrash').hide();
                    if (filter == 'transmission') {
                        filter = 'all';
                    }
                }
                if (filter == 'transmission') {
                    $('#torrentlist_container .feed').hide();
                } else {
                    $('.transmission').hide();
                }
                $("#torrentlist_container li").hide();
                container.show(0, function () {
                    displayFilter(filter, 1);
                    $('#dynamicdata').css('height', $(window).height() - ($('#topmenu').css('height') + 1));
                    if ('ontouchmove' in document.documentElement && navigator.userAgent.toLowerCase().search('android') == -1) {
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
                            window.getDataLoop = setInterval(getClientData, 5000);
                        }
                    } else {
                        setTimeout(getClientData, 10);
                    }
                }, 500);

                window.client = $('#clientId').html();
                changeClient(window.client);
                /*fontSize = $.cookie('twFontSize');
                changeFontSize(fontSize);*/
            }, 50);
            if ($('#torrentlist_container div.header.combined').length == 1) {
                $('.torrentlist>li').tsort('#unixTime', {order: 'desc'});
            }
            setTimeout(function () {
                var versionCheck = $.cookie('VERSION-CHECK');
                if (!versionCheck) {
                    $.get('torrentwatch-xa.php', {version_check: 1}, function (data) {
                        $('#dynamicdata').append(data);
                        setTimeout(function () {
                            $('#newVersion').slideUp().remove();
                        }, 15000);
                        $.cookie('VERSION-CHECK', '1', {expires: 1});
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
        if (button.id == "Delete") {
            $.get(form.get(0).action, form.buildDataString(button));
            if (button.href.match(/#feedItem/)) {
                var id = button.href.match(/#feedItem_(\d+)/)[1];
                $("#feedItem_" + id).remove();
                $("#feed_" + id).remove();
            }
            if (button.href.match(/#favorite/)) {
                var id = button.href.match(/#favorite_(\d+)/)[1];
                $("#favorite_" + id).toggleFavorite();
                $("#favorite_" + id).remove();
                $("#fav_" + id).remove();
                window.dialog = 1;
            }
        } else if(button.id == "Update") {
            //TODO finish code to "pin open" Feeds panel when Update button is pressed
            //TODO finish code to "pin open" Favorites panel when Update button (at favorites_info.tpl:87) is pressed
            $.get(form.get(0).action, form.buildDataString(button), $.loadDynamicData, 'html');
        } else {
            $.get(form.get(0).action, form.buildDataString(button), $.loadDynamicData, 'html');
        }
    };

    $.fn.toggleDialog = function () {
        this.each(function () {
            if (window.input_change && this.text != 'Next') {
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
                $(last).fadeOut("normal");
                $('#favorites, #configuration, #feeds, #history, #hidelist').remove();
                $('ul#mainoptions li a').removeClass('selected');
                $('#dynamicdata .dialog .dialog_window, .dialogTitle').remove();
                $('#dynamicdata .dialog').addClass('dialog_last');
            }
            if (current_dialog && this.hash != '#') {
                $.get('torrentwatch-xa.php', {get_dialog_data: this.hash}, function (data) {
                    $('#dynamicdata.dyndata').append(data);
                    $('#dynamicdata').find("ul.favorite > li").initFavorites().end().find("form").initForm().end().initConfigDialog();
                    $('#dynamicdata .dialog_last').remove();
                    if (navigator.appName == 'Microsoft Internet Explorer' || last) {
                        $('.dialog').show();
                    } else {
                        $('.dialog').fadeIn();
                    }
                    $(current_dialog).fadeIn("normal");
                    setTimeout(function () {
                        $("#dynamicdata .dialog_window input, #dynamicdata .dialog_window select").change(function () {
                            window.input_change = 1;
                        });
                    }, 500);
                    window.dialog = 1;
                    $(current_dialog + ' a.submitForm').click(function () {
                        window.dialog = 0;
                    });
                });
                $("li#id_" + this.parentNode.id + " a").addClass("selected");
            } else {
                $('#dynamicdata .dialog').fadeOut();
                setTimeout(function () {
                    $('#dynamicdata .dialog').remove();
                }, 400);
            }
            if (last == '#configuration') {
                $.get('torrentwatch-xa.php', {get_client: 1}, function (client) {
                    window.client = client;
                    changeClient(client);
                });
            }
        });
        return this;
    };

    $.fn.initFavorites = function () {
        var selector = this.selector;
        setTimeout(function () {
            $(selector + ":first a").toggleFavorite();
            $('#favorite_new a#Update').addClass('disabled').removeClass('submitForm');
        },
                300);
        this.not(":first").tsort('a');
        return this.not(":first").end().click(function () {
            $(this).find("a").toggleFavorite();
        });
    };
    $.fn.initForm = function () {
        this.submit(function (e) {
            e.stopImmediatePropagation();
            $.submitForm(this);
            return false;
        });
        /*var f = $.cookie('twFontSize'); //TODO search for and remove other twFontSize
        if (f) {
            this.find("#config_webui").val(f).change();
        }*/
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
            $("#favorites input").keyup(function () {
                if ($(current_favorite + ' input:text[name=name]').val().length &&
                        $(current_favorite + ' input:text[name=filter]').val().length) {
                    $(current_favorite + ' a#Update').removeClass('disabled').addClass('submitForm');
                } else {
                    $(current_favorite + ' a#Update').addClass('disabled').removeClass('submitForm');
                }
            });

            if (!last) {
                $(current_favorite).show();
            } else {
                $(last).fadeOut(400,
                        function () {
                            $(current_favorite).fadeIn(400);
                            $(current_favorite).resetForm();
                        });
            }
        });
        return this;
    };
    $.fn.initConfigDialog = function () {
        $('select#client').change();
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
        window.favving = 1;
        $.get('torrentwatch-xa.php', {
            matchTitle: 1,
            title: title,
            rss: feed
        }, function (response) {
            if (response.match(/^Error:/)) {
                var errorID = new Date().getTime();
                $('#twError').show().append('<p id="error_' + errorID + '">' + response + '</p>');
                setTimeout(function () {
                    $('#twError p#error_' + errorID).remove();
                    if (!$('#twError p').length) {
                        $('#twError').hide();
                    }
                }, 5000);
            } else {
                response = $.parseJSON(response);
                $.each($("ul#torrentlist li"), function (i, item) {
                    if ($('li#' + item.id + ' input.show_title').val().toLowerCase().match(response.title.toLowerCase()) &&
                            $('li#' + item.id + ' input.show_quality').val().toLowerCase().match(response.quality.toLowerCase()) &&
                            ($.urlencode($('li#' + item.id + ' input.feed_link').val()).match(response.feed) ||
                                    response.feed == 'All')) {
                        $('li#' + item.id).removeClass('match_nomatch').addClass('match_test');
                    }
                });
            }
            window.favving = 0;
        }, 'html');
    };

    $.dlTorrent = function (title, link, feed, id) {
        $.get("torrentwatch-xa.php", {
            dlTorrent: 1,
            title: title,
            link: link,
            feed: feed,
            id: id
        },
        function (torHash) {
            if (link.match(/^magnet:/) && window.client == 'folder') {
                alert('Can not save magnet links to a folder');
                return;
            }
            if (torHash.match(/Error:\s\w+/) && window.client != 'folder') {
                alert('Something went wrong while adding this torrent. ' + torHash);
                return;
            }
            $('li#id_' + id).removeClass('match_nomatch match_old_download').addClass('match_downloading');
            $('li#id_' + id + ' td.buttons').removeClass('match_nomatch match_old_download').addClass('match_downloading');
            if (!$('li#id_' + id + ' span.torInfo').length) {
                $('li#id_' + id + ' td.torrent_name')
                        .append('<div class="infoDiv"><span id=tor_' + id + ' class="torInfo tor_' + torHash.match(/\w+/) + '">' +
                                'Waiting for client data...</span><span class="torEta"></span></div>');
            }

            $('li#id_' + id + ' div.hideItem').hide();
            if (window.client != 'folder') {
                $('li#id_' + id + ' div.progressBarContainer').show();
            }
            $('li#id_' + id + ' div.dlTorrent').hide();
            $('li#id_' + id + ' div.torPause').show();
            if (window.client == 'folder') {
                return;
            }
            $('li#id_' + id + ' div.torTrash').show();
            $('li#id_' + id + ' div.torDelete').show();

            $('li#id_' + id).removeClass('item_###torHash###').addClass('item_' + torHash);

            var item = $('li#id_' + id);
            item.html(item.html().replace(/###torHash###/g, torHash));

            setTimeout(getClientData, 10);
        });
    };

    $.delTorrent = function (torHash, trash, batch, sure) {
        if (trash && sure != 'true' && !$.cookie('TorTrash')) {
            var dialog = '<div id="confirmTrash" class="dialog confirm" style="display: block; ">' +
                    '<div class="dialog_window" id="trash_tor_data"><div>Are you sure?<br />This will remove the torrent along with its data.</div>' +
                    '<div class="buttonContainer"><a class="button confirm trash_tor_data" ' +
                    'onclick="$(\'#confirmTrash\').remove(); $.delTorrent(\'' + torHash + '\',\'true\', \'' + batch + '\', \'true\');">Yes</a>' +
                    '<a class="button trash_tor_data wide" ' +
                    'onclick="$(\'#confirmTrash\').remove();' +
                    '$.cookie(\'TorTrash\', 1, { expires: 30 });' +
                    '$.delTorrent(\'' + torHash + '\',\'true\', \'true\');">' +
                    'Yes, don\'t ask again</a>' +
                    '<a class="button trash_tor_data close" onclick="$(\'#confirmTrash\').remove()">No</a>' +
                    '</div>' +
                    '</div>';
            $('body').append(dialog);
            $('#confirmTrash').css('height', $(document).height() + 'px');
            $('#trash_tor_data').css('top', window.pageYOffset + (($(window).height() / 2) - $('#trash_tor_data').height()) + 'px');
        } else {
            sure = 1;
        }

        if (sure) {
            $.getJSON('torrentwatch-xa.php', {
                'delTorrent': torHash,
                'trash': trash,
                'batch': batch
            },
            function (json) {
                if (json.result == "success") {
                    setTimeout(getClientData, 10);
                } else {
                    alert('Request failed');
                }
            });
        }
    };

    $.episodeInfo = function (torrentName) {
        $('#progress').show();
        this.hash = '#show_info';
        current_dialog = this.hash;
        $.get('torrentwatch-xa.php', {
            get_dialog_data: this.hash,
            episode_name: torrentName
        }, function (data) {
            $('#dynamicdata.dyndata').append(data);
            $('#dynamicdata .dialog_last').remove();
            $('.dialog').show();
            window.dialog = 1;
        });
    };

    $.stopStartTorrent = function (stopStart, torHash, batch) {
        var param;
        if (stopStart == 'stop') {
            param = {stopTorrent: torHash, batch: batch};
        } else if (stopStart == 'start') {
            param = {startTorrent: torHash, batch: batch};
        }
        $.getJSON('torrentwatch-xa.php', param,
            function (json) {
                if (json.result == "success") {
                    $('li.item_' + torHash + ' div.dlTorrent').hide();
                    torStartStopToggle(torHash);
                    setTimeout(getClientData, 10);
                } else {
                    alert('Request failed');
                }
            });
    };

    $.moveTorrent = function (torHash, batch) {
        path = $('input#moveTo')[0].value;

        $.getJSON('torrentwatch-xa.php', {
            'moveTo': path,
            'torHash': torHash,
            'batch': batch
        },
        function (json) {
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
            if (response.match(/^ERROR:/)) {
                var errorID = new Date().getTime();
                $('#twError').show().append('<p id="error_' + errorID + '">' + response.match(/^ERROR:(.*)/)[1] + '</p>');
                setTimeout(function () {
                    $('#twError p#error_' + errorID).remove();
                    if (!$('#twError p').length) {
                        $('#twError').hide();
                    }
                }, 5000);
            } else {
                $.each($('#torrentlist_container li'), function () {
                    if ($('#' + this.id + ' input.show_title').val() == response) {
                        $(this).removeClass('selected').remove();
                    }
                });
            }
            window.hiding = null;
        });
    };

    $.toggleFeed = function (feed, speed) {
        if (speed == 1) {
            if ($.cookie('feed_' + feed) == 1) {
                $("#feed_" + feed + " ul").removeClass("hiddenFeed").show();
                $("#feed_" + feed + " .header").removeClass("header_hidden");
                $.cookie('feed_' + feed, null, {expires: 666});
            } else {
                $("#feed_" + feed + " ul").hide().addClass("hiddenFeed");
                $("#feed_" + feed + " .header").addClass("header_hidden");
                $.cookie('feed_' + feed, 1, {expires: 666});
            }
        } else {
            if ($.cookie('feed_' + feed) == 1) {
                $("#feed_" + feed + " ul").removeClass("hiddenFeed").slideDown();
                $("#feed_" + feed + " .header").removeClass("header_hidden");
                $.cookie('feed_' + feed, null, {expires: 666});
            } else {
                $("#feed_" + feed + " ul").slideUp().addClass("hiddenFeed");
                $("#feed_" + feed + " .header").addClass("header_hidden");
                $.cookie('feed_' + feed, 1, {expires: 666});
            }
        }
    };

    $.checkHiddenFeeds = function (speed) {
        $.each($('#torrentlist_container .feed'), function () {
            if ($.cookie(this.id)) {
                if (speed == 1) {
                    $("#feed_" + this.id.match(/feed_(\d)/)[1] + " ul").hide().addClass("hiddenFeed");
                } else {
                    $("#feed_" + this.id.match(/feed_(\d)/)[1] + " ul").slideUp().addClass("hiddenFeed");
                }
                $("#feed_" + this.id.match(/feed_(\d)/)[1] + " .header").addClass("header_hidden");
            }
        });
    };

    $.toggleConfigTab = function (tab, button) {
        $(".toggleConfigTab").removeClass("selTab");
        $(button).addClass("selTab");
        $('.configTab').hide();
        $('#configuration form').hide();
        if (tab == "#config_feeds") {
            $("#configuration .feedform").show();
        } else if (tab == "#config_hideList") {
            $("#hidelist_form").show();
        } else {
            $('#config_form').show();
        }
        $(tab).animate({opacity: 'toggle'}, 500);
    };

    $.toggleContextMenu = function (item_id, id) {
        if ($('div.contextMenu').not(item_id).is(":visible")) {
            $('div.contextMenu').hide();
        }
        if ($(item_id).is(":visible")) {
            $(item_id).slideUp('fast');
        } else {
            $(item_id).slideDown('fast');
            $('div.contextMenu, a.contextButton').mouseleave(function () {
                var contextTimeout = setTimeout(function () {
                    $(item_id).fadeOut('fast');
                }, 500);
                $('div.contextMenu, a#contextButton_' + id).mouseenter(function () {
                    clearTimeout(contextTimeout);
                });
                $('div.contextItem').mouseover(function () {
                    $(this).addClass('alt');
                    $(this).mouseleave(function () {
                        $(this).removeClass('alt');
                    });
                });
                $(item_id).click(function () {
                    $(this).slideUp('fast');
                });
            });
        }
    };

    $.processSelected = function (action) {
        if (!$('#torrentlist_container .torrent.selected').length) {
            return;
        }

        var list = '';
        $.each($('#torrentlist_container li.torrent.selected'), function (i, item) {
            var trItem = '';
            if (this.className.match(/item_\w+/) && !this.className.match(/item_###torHash###/)) {
                if (list) {
                    list = list + ',' + this.className.match(/item_(\w+)/)[1];
                } else {
                    list = list + this.className.match(/item_(\w+)/)[1];
                }
            }
        });

        if (action == 'trash') {
            var trash = 1;
        }
        if ((action == 'delete') || (action == 'trash')) {
            $.delTorrent(list, trash, true);
        }
        if (action == 'start') {
            $.stopStartTorrent('start', list, true);
        }
        if (action == 'stop') {
            $.stopStartTorrent('stop', list, true);
        }
        if (action == 'move') {
            $.moveTorrent(list, true);
        }

        $.each($('#torrentlist_container .feed li.selected'), function () {
            var title = $('li#' + this.id + ' input.title').val();
            var link = $('li#' + this.id + ' input.link').val();
            var feedLink = $('li#' + this.id + ' input.feed_link').val();
            var id = $('li#' + this.id + ' input.client_id').val();

            if (action == 'addFavorite') {
                var favInterval = setInterval(function () {
                    if (window.favving != 1) {
                        $.addFavorite(feedLink, title);
                        clearInterval(favInterval);
                    }
                }, 100);
            }
            if (!$(this).hasClass('match_downloading') && !$(this).hasClass('match_downloaded')) {
                if (action == 'dlTorrent') {
                    $.dlTorrent(title, link, feedLink, id);
                }
                if (action == 'hideItem') {
                    var hideInterval = setInterval(function () {
                        if (window.hiding != 1) {
                            $.hideItem(title, id);
                            clearInterval(hideInterval);
                        }
                    }, 100);
                }
            }
        });
    };

    $.noEnter = function (evt) {
        var evt = (evt) ? evt : ((event) ? event : null);
        var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
        if ((evt.keyCode == 13) && (node.type == "text")) {
            return false;
        }
    };
    document.onkeypress = $.noEnter;
})(jQuery);
