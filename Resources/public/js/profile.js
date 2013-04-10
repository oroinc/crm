$(function() {
    $('#btn-apigen').on('click', function(e) {
        el = $(this);

        $.get(el.attr('href'), function (data) {
            el.prev().text(data);
        })

        return false;
    });

    $('.calendar input').datepicker({
        dateFormat: $('.calendar input').attr('-data-format')
    });

    $('#roles-list input')
        .on('click', function() {
            inputs = $(this).closest('.controls');

            inputs.find(':checkbox').attr('required', inputs.find(':checked').length > 0 ? null : 'required');
        })
        .triggerHandler('click');
});