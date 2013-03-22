jQuery(document).ready(function () {
    var dialogBlock;

    jQuery(".update-status a").click(function () {
        var dialogOptions = {
            "title" : "Update status",
            "width" : 300,
            "height" : 180,
            "modal" : false,
            "resizable" : false
        };

        $.get($(this).attr('href'), function(data) {
            dialogBlock = jQuery(data).dialog(dialogOptions);
        })

        return false;
    });

    $(document).on('submit', '#create-status-form', function(e) {
        $.ajax({
            type:'POST',
            url: jQuery($(this)).attr('action'),
            data:jQuery($(this)).serialize(),
            success: function(response) {
                dialogBlock.dialog("destroy");
            }
        });

         return false;
    });
});
