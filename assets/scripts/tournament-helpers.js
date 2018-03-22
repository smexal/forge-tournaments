var tournamentHelper = {
    init : function() {
        tournamentHelper.fixBracketHeight();
    },

    fixBracketHeight : function() {
        var winnerHeight = false;
        var loserHeight = false;
        $(".bracket").find(".round").each(function() {
            $(this).find(".winner").each(function() {
                if(! winnerHeight) {
                    winnerHeight = $(this).outerHeight(true);
                }
                $(this).height(winnerHeight);
            });
            $(this).find(".lower").each(function() {
                if(! loserHeight) {
                    loserHeight = $(this).outerHeight(true);
                }
                $(this).height(loserHeight);
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
