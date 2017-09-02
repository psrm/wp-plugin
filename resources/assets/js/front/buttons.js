jQuery(function($){
    $('form').submit(function(){
        var button = $('input[type="submit"][clicked="true"]');
        button.prop('disabled', true);
        button.val('Submitting...');
        return true;
    });

    $("form input[type=submit]").click(function() {
        $("input[type=submit]", $(this).parents("form")).removeAttr("clicked");
        $(this).attr("clicked", "true");
    });
});