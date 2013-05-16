$(function () {
    $(document).on('click', '#add-another-email', function (event) {
        var cList  = $('#email-fields-list'),
        cCount = cList.children().length;
        widget = cList.attr('data-prototype').replace(/__name__/g, cCount++);

        $('<div></div>').html(widget).appendTo(cList);

        return false;
    });

    $(document).on('click', '.removeRow', function (event) {
        name = $(this).attr('data-related');

        $('*[data-content="' + name + '"]').remove();

        return false;
    });
});