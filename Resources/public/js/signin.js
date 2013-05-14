$(function() {
    $(window)
        .resize(function() {
            form = $('form.form-signin');
            var thisHeight = $(window).height()/2 - form.height()/2;
            if  (thisHeight > 40) {
                thisHeight = thisHeight -40;
            }
            form.css('margin-top', thisHeight );
        })
        .trigger('resize');
});