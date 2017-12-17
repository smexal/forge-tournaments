var forgeTournament = {
    slot_assignements: [],

    init : function() {
        var self = this;
        var loadFields = function() {
            self.loadSlotAssignements();
        };
        
        $(document).on("ajaxReload", loadFields);
        
        loadFields();
    },

    loadSlotAssignements : function() {
        var self = this;
        $("input[type=\"hidden\"].slots-output:not(.initialized)").each(function() {
            self.slot_assignements.push(
                new forge_tournaments.SlotAssignment($(this).closest('.sa-base')[0])
            );
            $(this).addClass('initialized');
        });
    },

    formCallback : function(data) {
        var container = $(".forge-tournament-formular");
        var form = container.find("form");

        // tell container, there is a message
        if(data.type == 'error') {
            form.removeClass('success');
            form.addClass(data.type);
        } else if (data.type == 'success') {
            form.slideUp();
            // add message
            container.find("p.message").each(function() {
                $(this).remove();
            });
            container.append('<p class="message '+data.type+'">'+data.message+'</p>');
        }
    },

    setEncounterWinner : function(elem, team, tournament, round, encounter) {
        console.log("elem: " + elem);
        console.log("team: " + team);
        console.log("tournament: " + tournament);
        console.log("round: " + round);
        console.log("encounter: " + encounter);
    }

};

$(document).ready(function() {
    forgeTournament.init();
});