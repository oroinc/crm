$(function() {
    $('#btn-apigen').on('click', function(e) {
        el = $(this);

        $.get(el.attr('href'), function (data) {
            el.prev().text(data);
        })

        return false;
    });

    dateFormat = $('.calendar input').attr('-data-format');
    $('.calendar input').datepicker({
        dateFormat : dateFormat
    });

    function checkRequired()
    {
        if ($('#oro_user_profile_form_rolesCollection input:checkbox:checked').length > 0) {
            $('#oro_user_profile_form_rolesCollection input:checkbox').prop('required', false);
        } else {
            $('#oro_user_profile_form_rolesCollection input:checkbox').prop('required', 'required');
        }
    }

    $('#oro_user_profile_form_rolesCollection input').click(function(){
        checkRequired();
    });

    checkRequired();
});