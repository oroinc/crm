$(function() {
    $('#btn-apigen').on('click', function(e) {
        el = $(this);

        $.get(el.attr('href'), function (data) {
            el.prev().text(data);
        })

        return false;
    });

    $('#roles-list input')
        .on('click', function() {
            inputs = $(this).closest('.controls');

            inputs.find(':checkbox').attr('required', inputs.find(':checked').length > 0 ? null : 'required');
        })
        .triggerHandler('click');
});