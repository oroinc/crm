$(function() {
    $('#btn-apigen').on('click', function(e) {
        el = $(this);

        $.get(el.attr('href'), function (data) {
            el.prev().text(data);
        })

        return false;
    });

    $('.calendar input').datepicker({
        dateFormat: 'dd-mm-yy'
    });
});