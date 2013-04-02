$(function () {
    var cList  = $('#email-fields-list'),
        cCount = cList.children().length;

    $('#add-another-email').on('click', function () {
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