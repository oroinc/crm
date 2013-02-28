$(function() {
    $(window)
        .resize(function() {
            form = $('form.form-signin');

            form.css('margin-top', ($(window).height() - form.height()) / 2);
        })
        .trigger('resize');
});