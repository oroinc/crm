$(function() {
    $(window)
        .resize(function() {
            form = $('form.form-signin');
            form.css('margin-top', ($(window).height()/2 - form.height()));
        })
        .trigger('resize');
});