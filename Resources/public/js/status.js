var dialogBlock;

jQuery(document).ready(function () {
    jQuery(".update-status a").click(function () {
        var dialogOptions = {
            "title" : "Update status",
            "width" : 300,
            "height" : 180,
            "modal" : false,
            "resizable" : false
        };

        jQuery.ajax({
            url: status_path,
            dataType: "html",
            success: function (data) {
                dialogBlock = jQuery(data).dialog(dialogOptions);
            }
        });
        return false;
    });
});

function sendForm(){
    var form = jQuery("#create-status-form");
    jQuery.ajax({
        type:'POST',
        url: jQuery(form).attr('action'),
        data:jQuery(form).serialize(),
        success: function(response) {
            dialogBlock.dialog("destroy");
        }
    });

    return false;
}