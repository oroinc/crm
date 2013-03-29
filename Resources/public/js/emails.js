jQuery(document).ready(function () {
    jQuery('#add-another-email').click(function () {
        var emailList = jQuery('#email-fields-list');
        var newWidget = emailList.attr('data-prototype');
        newWidget = newWidget.replace(/__name__/g, emailCount);
        emailCount++;
        var newDiv = jQuery('<div></div>').html(newWidget);
        newDiv.appendTo(jQuery('#email-fields-list'));
        return false;
    });

    jQuery('.removeRow').live('click', function (event) {
        var name = $(this).attr('data-related');
        jQuery('*[data-content="' + name + '"]').remove();
        return false;
    });
});