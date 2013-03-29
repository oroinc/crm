$(document).ready(function () {
    $('#add-another-email').click(function () {
        var emailList = $('#email-fields-list');
        var newWidget = emailList.attr('data-prototype');
        newWidget = newWidget.replace(/__name__/g, emailCount);
        emailCount++;
        var newDiv = $('<div></div>').html(newWidget);
        newDiv.appendTo($('#email-fields-list'));
        return false;
    });

    $(document).on('click', '.removeRow', function (event) {
        var name = $(this).attr('data-related');
        $('*[data-content="' + name + '"]').remove();
        return false;
    });
});