$(function() {
    $(document).on('click', '#btn-apigen', function (e) {
        el = $(this);

        $.get(el.attr('href'), function (data) {
            el.prev().text(data);
        })

        return false;
    });

    $(document).on('click', '#roles-list input', function (e) {
        inputs = $(this).closest('.controls');
        inputs.find(':checkbox').attr('required', inputs.find(':checked').length > 0 ? null : 'required');
    });
});