// Hide Search -- By Text
$("input#hideSearchText").keyup(function() {
    var hideSearchText = $(this).val().toLowerCase();
    $("#hideListContainer li").addClass('hidden').each(function() {
        if ($(this).find("span.hiddenItem").text().toLowerCase().match(hideSearchText)) {
            $(this).removeClass('hidden');
        }
    })  
});  
