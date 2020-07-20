'use strict';
$(document).ready(function () {
    // binding for Menu Bar and other buttons that show/hide a dialog
    $(document).on("click", "a.toggleDialog", function () {
        $(this).toggleDialog();
    });
    // binding for Filter Bar buttons
    $("#filterbar_container li:not(#filter_bytext)").on("click", function () {
        if ($(this).is('.selected')) {
            return;
        }
        var filter = this.id;
        $("#torrentlist_container").show(function () {
            'use strict';
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
    // binding for Configure form and Favorites form ajax submit
    $(document).on("click", "a.submitForm", function (e) {
        window.input_change = 0;
        e.stopImmediatePropagation();
        $.submitForm(this);
        if (this.parentNode.id) {
            $('div#' + this.parentNode.id).hide();
        }
    });
    // binding for Clear History ajax submit
    $(document).on("click", "a#clearhistory", function () {
        $.get(this.href, '', function (html) {
            $("#history").html($(html).html());
        },
                'html');
        return false;
    });
    // binding for Clear Cache ajax submit
    $(document).on("click", "a.clear_cache", function (e) {
        $.get(this.href, '', $.loadDynamicData, 'html');
        return false;
    });
    $(window).on("focus", function (e) {
        // if browser gains focus, reset Mac Cmd key toggle to partially block Cmd-Tab
        window.ctrlKey = 0;
    });
    $(window).on("focusout", function (e) {
        // if browser loses focus, reset Mac Cmd key toggle to partially block Cmd-Tab
        window.ctrlKey = 0;
    });
    $(document).on("keyup", function (e) {
        if (e.keyCode === 27) {
            if ($('.dialog').length) {
                $('.dialog .close').trigger("click");
                $('div.contextMenu').hide();
            } else if ($('#clientButtons .move_data').is(":visible")) {
                $('#clientButtons .close').trigger("click");
            } else if ($('#torrentlist_container li.torrent.selected').length) {
                $('#torrentlist_container li.torrent.selected').removeClass('selected');
                updateClientButtons();
            }
        }
        if (e.keyCode === 13) {
            if ($('.dialog .confirm').length) {
                $('.dialog .confirm').trigger("click");
            } else if ($('#clientButtons .move_data').is(":visible")) {
                $('#clientButtons #Move').trigger("click");
            }
        }
        if (e.keyCode === 17 || e.keyCode === 91 || e.keyCode === 93 || e.keyCode === 224) { // Mac Cmd key
            window.ctrlKey = 0;
        }
    });
    $(document).on("keydown", function (e) {
        if (e.keyCode === 17 || e.keyCode === 91 || e.keyCode === 93 || e.keyCode === 224) { // Mac Cmd key
            window.ctrlKey = 1;
        }
        if (window.ctrlKey && e.keyCode === 65) {
            if ($('#torrentlist_container li.torrent.selected').length === $('#torrentlist_container li.torrent').length) {
                $('#torrentlist_container li.torrent').removeClass('selected');
            } else {
                $('#torrentlist_container li.torrent').addClass('selected');
            }
            updateClientButtons();
            return false;
        }
    });
    document.addEventListener("keypress", function (evt) {
        var evt = (evt) ? evt : ((event) ? event : null);
        var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
        if ((evt.keyCode === 13) && (node.type === "text")) {
            return false;
        }
    });
    // Ajax progress bar bindings
    $(document).ajaxStart(function () {
        window.ajaxActive = 1;
        if (!(window.hideProgressBar)) {
            $('#refresh a').html('<img src="images/ajax-loader-small.gif" alt="Working...">');
            if ($('div.dialog').is(":visible")) {
                $('#progress').removeClass('progress_full').fadeIn();
            }
            if ($('#clientButtons').is(":visible")) {
                window.visibleButtons = $('#clientButtonsHolder li.button').not('.hidden');
                window.hideButtonHolder = setTimeout(function () {
                    $(window.visibleButtons).hide();
                    $('#clientButtons').append('<div id="clientButtonsBusy"><img src="images/ajax-loader-small.gif" alt="Working...">Working...</div>');
                }, 500);
            }
        }
    }).ajaxStop(function () {
        window.ajaxActive = 0;
        $('#refresh a').html('<img src="images/refresh_32x32.png" alt="Refresh" width="16" height="16">');
        $('#progress').fadeOut();
        $('#clientButtonsBusy').remove();
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
    // set timeout for all Ajax queries
    $.ajaxSetup({timeout: '100000'}); // if adding many favorites and clicking the Refresh button times out, increase this value
});