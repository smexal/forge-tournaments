var forgeTournament = {

    formCallback : function(data) {
        var container = $(".forge-tournament-organisation-registration");
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
    }

};
