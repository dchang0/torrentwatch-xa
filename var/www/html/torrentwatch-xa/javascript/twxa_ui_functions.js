'use strict';
function adjustWebUIButton() {
    switch (window.client) {
        case "Transmission" :
            // hide the button entirely when there's no valid web UI URL
            if (!window.twxaWebUiUrl) {
                $("#torrentClientButton").hide();
                $("#torrentClientButtonLabel").hide();
                break;
            }
            // shrink/expand/hide/show Web UI button to fit window
            if ($(window).width() < 635) {
$("#torrentClientButton").hide();
$("#torrentClientButtonLabel").hide();
            } else if ($(window).width() < 710) { // was 620 before Super-Favorites
$("#torrentClientButton").show();
$("#torrentClientButtonLabel").hide();
            } else {
                $("#torrentClientButton").show();
$("#torrentClientButtonLabel").show();
            }
            break;
        case "folder" :
        default :
            $("li#torrentClientButton").hide();
    }
}
function adjustUIElements() {
    // NOTE: No need to overdo handling below 640px wide due to phone.css
    // shrink/expand Downloading
    if ($(window).width() < 650) {
        $("#filterbar_container li#filter_downloading.tab").hide();
    } else {
        $("#filterbar_container li#filter_downloading.tab").show();
    }
    // shrink/expand Downloaded
    if ($(window).width() < 650) {
        $("#filterbar_container li#filter_downloaded.tab").hide();
    } else {
        $("#filterbar_container li#filter_downloaded.tab").show();
    }
    adjustWebUIButton();
    // hide/show Filter field
    if ($(window).width() < 870) {
        $("li#filter_bytext").hide();
    } else {
        $("li#filter_bytext").show();
    }
}