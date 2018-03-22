var tournamentHelper = {
    init : function() {
        tournamentHelper.fixBracketHeight();
    },

    fixBracketHeight : function() {
        var height = false;
        $(".bracket").find(".round").each(function() {
            $(this).find(".winner").each(function() {
                if(! height) {
                    height = $(this).outerHeight(true);
                }
                $(this).height(height);
            });
            $(this).find(".lower").each(function() {
                if(! height) {
                    height = $(this).outerHeight(true);
                }
                $(this).height(height);
            });
        });
    }
}
$(document).ready(function() {
    tournamentHelper.init();
});
$(document).on("ajaxReload", function()  {
    tournamentHelper.init();
});
