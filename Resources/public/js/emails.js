jQuery(document).ready(function () {
    jQuery('#add-another-email').click(function () {
        var emailList = jQuery('#email-fields-list');
        var newWidget = emailList.attr('data-prototype');
        newWidget = newWidget.replace(/__name__/g, emailCount);
        emailCount++;
        var newLi = jQuery('<li></li>').html(newWidget);
        newLi.appendTo(jQuery('#email-fields-list'));
        addTagFormDeleteLink($(newLi));
        return false;
    });

    jQuery('#email-fields-list').find('li').each(function () {
        addTagFormDeleteLink($(this));
    });

    function addTagFormDeleteLink($tagFormLi) {
        var $removeFormA = $('<a class="btn" href="#">Delete</a>');
        $tagFormLi.append($removeFormA);

        $removeFormA.on('click', function (e) {
            e.preventDefault();
            $tagFormLi.remove();
        });
    }
});