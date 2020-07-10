'use strict';
// NOTE: These functions do not need to wait for document ready
Math.roundWithPrecision = function (floatnum, precision) {
    return Math.round(floatnum * Math.pow(10, precision)) / Math.pow(10, precision);
};
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
        // the file is less than one KB
    } else {
        size = bytes;
        unit = ' B';
    }
    // single-digit numbers have greater precision
    var precision = 2;
    size = Math.roundWithPrecision(size, precision);
    return size + unit;
};
function convertEta(eta) {
    // convert numeric eta in seconds to human-friendly string
    var etaString = '';
    if (isNaN(eta) || eta >= 86400) {
        var days = Math.floor(eta / 86400);
        var hours = Math.floor((eta / 3600) - (days * 24));
        var minutes = Math.round((eta / 60) - (days * 1440) - (hours * 60));
        if (minutes <= 9) {
            minutes = '0' + minutes;
        }
        etaString = 'Remaining: ' + days + ' days ' + hours + ' hr ' + minutes + ' min';
    } else if (eta >= 3600) {
        var hours = Math.floor(eta / 60 / 60);
        var minutes = Math.round((eta / 60) - (hours * 60));
        etaString = 'Remaining: ' + hours + ' hr ' + minutes + ' min';
    } else if (eta > 0) {
        var minutes = Math.round(eta / 60);
        var seconds = eta - (minutes * 60);
        if (seconds < 0) {
            minutes--;
            seconds = seconds + 60;
        }
        if (eta < 60) {
            etaString = 'Remaining: ' + eta + ' sec';
        } else {
            etaString = 'Remaining: ' + minutes + ' min ' + seconds + ' sec';
        }
    } else {
        etaString = 'Remaining: unknown';
    }
    return etaString;
}