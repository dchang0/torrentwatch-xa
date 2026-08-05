'use strict';
$(document).ready(function () {
    // binding for Menu Bar and other buttons that show/hide a dialog
    $(document).on("click", "a.toggleDialog", function () {
        $(this).toggleDialog();
    });
    // set href for the Web UI button; hide the button if no valid web UI URL is available
    $.get('torrentwatch-xa.php', {get_web_ui_url: 1}, function (url) {
        window.twxaWebUiUrl = url;
        if (url) {
            $('#torrentClientWebUi').attr('href', url);
        }
        adjustWebUIButton();
    });
    // binding for Filter Bar buttons
    $("#filterbar_container li:not(#filter_bytext)").on("click", function () {
        if ($(this).is('.selected')) {
            return;
        }
        var filter = this.id;
        $("#torrentlist_container").show(function () {
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
                case 'filterTorrentClient':
                    displayFilter('torrentClient');
            }
        });
    });
    // binding for Configure form, Favorites form, and Super-Favorites form ajax submit
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
    window.onresize = function () {
        adjustUIElements();
    };
    $(window).on("focus", function (e) {
        // if browser gains focus, reset Mac Cmd key toggle to partially block Cmd-Tab
        window.ctrlKey = 0;
    });
    $(window).on("focusout", function (e) {
        // if browser loses focus, reset Mac Cmd key toggle to partially block Cmd-Tab
        window.ctrlKey = 0;
    });
    document.addEventListener("keyup", function (e) {
        if (e.key === "Escape") {
            var dialog = document.querySelector('.dialog');
            if (dialog) {
                dialog.querySelector('.close')?.click();
                var ctx = document.querySelector('div.contextMenu');
                if (ctx) ctx.style.display = 'none';
            } else {
                var moveData = document.querySelector('#clientButtons .move_data');
                if (moveData && moveData.offsetParent !== null) {
                    document.querySelector('#clientButtons .close')?.click();
                } else if (document.querySelectorAll('#torrentlist_container li.torrent.selected').length) {
                    document.querySelectorAll('#torrentlist_container li.torrent.selected')
                        .forEach(function (el) { el.classList.remove('selected'); });
                    updateClientButtons();
                }
            }
        }
        if (e.key === "Enter") {
            var confirmBtn = document.querySelector('.dialog .confirm');
            if (confirmBtn) {
                confirmBtn.click();
            } else {
                var moveData = document.querySelector('#clientButtons .move_data');
                if (moveData && moveData.offsetParent !== null) {
                    document.querySelector('#clientButtons #Move')?.click();
                }
            }
        }
        if (e.key === "Control" || e.key === "Meta") {
            window.ctrlKey = 0;
        }
    });
    document.addEventListener("keydown", function (e) {
        if (e.key === "Control" || e.key === "Meta") {
            window.ctrlKey = 1;
        }
        if (window.ctrlKey && e.key.toLowerCase() === "a") {
            var allTors = document.querySelectorAll('#torrentlist_container li.torrent');
            var selTors = document.querySelectorAll('#torrentlist_container li.torrent.selected');
            if (selTors.length === allTors.length) {
                allTors.forEach(function (el) { el.classList.remove('selected'); });
            } else {
                allTors.forEach(function (el) { el.classList.add('selected'); });
            }
            updateClientButtons();
            e.preventDefault();
        }
    });
    document.addEventListener("keypress", function (e) {
        if (e.key === "Enter" && e.target && e.target.type === "text") {
            e.preventDefault();
        }
    });
    // Ajax progress bar bindings
    $(document).ajaxStart(function () {
        window.ajaxActive = 1;
        if (!(window.hideProgressBar)) {
            $('#refresh a').html('<img src="images/ajax-loader-small.gif" alt="Working...">');
            if ($('div.dialog').is(":visible")) {
                $('#progress').removeClass('progress_full').fadeIn('fast');
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
        $('#progress').fadeOut('fast');
        $('#clientButtonsBusy').remove();
        if (window.hideButtonHolder) {
            clearTimeout(window.hideButtonHolder);
        }
        if (window.visibleButtons) {
            $(window.visibleButtons).show();
            window.visibleButtons = null;
        }
        updateClientButtons();
        setTimeout(function () {
            $('#torrentClientList li.torrent').markAlt();
        }, 500);
    });
    // set timeout for all Ajax queries
    $.ajaxSetup({timeout: '100000'}); // if adding many favorites and clicking the Refresh button times out, increase this value
});