var tournamentHelper = {
    init : function() {
        tournamentHelper.fixBracketHeight();
        tournamentHelper.connectBracket();
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
    },

    connectBracket : function() {
        $(".bracket").each(function() {
            $(this).find(".encounter").each(function() {
                if($(this).data('has-line')) {
                    return;
                }
                $(this).data('has-line', true);
                var connection = $(this).connections({ 
                    to: '.encounter.id-' + $(this).attr('data-winner-to'),
                    tag: 'div',
                    within: '.bracket'
                });
                $(window).on('resize', function() {
                    connection.connections('update');
                });
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
