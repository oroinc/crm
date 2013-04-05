$(function() {
    $('#btn-apigen').on('click', function(e) {
        el = $(this);

        $.get(el.attr('href'), function (data) {
            el.prev().text(data);
        })

        return false;
    });

    dateFormat = $('.calendar input').attr('-data-format');
    $('.calendar input').datepicker({
        dateFormat : dateFormat
    });
});