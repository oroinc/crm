$(function() {
    $('#btn-apigen').on('click', function(e) {
        var el = $(this);

        $.get(el.attr('href'), function (data) {
            el.prev().text(data);
        })

        return false;
    });

    $('#btn-remove-profile').on('click', function(e) {
        var el = $(this),
            message = el.attr('data-message'),
            doAction = function() {
                $.ajax({
                    url: Routing.generate('oro_api_delete_profile', { id: el.attr('data-id') }),
                    type: 'DELETE',
                    success: function (data) {
                        window.location.href = Routing.generate('oro_user_index');
                    }
                });
            };

        if (!_.isUndefined(Oro.BootstrapModal)) {
            var confirm = new Oro.BootstrapModal({
                title: 'Delete Confirmation',
                content: message,
                okText: 'Yes, Delete',
                cancelText: 'Cancel'

            });
            confirm.on('ok', doAction);
            confirm.open();
        } else if (window.confirm(message)) {
            doAction();
        }

        return false;
    });

    $('#roles-list input')
        .on('click', function() {
            var inputs = $(this).closest('.controls');

            inputs.find(':checkbox').attr('required', inputs.find(':checked').length > 0 ? null : 'required');
        })
        .triggerHandler('click');

    $('#btn-enable input').on('change', function(e) {
//        if ($(this).is(':checked')) {
//
//        }
        $('.status-enabled').toggleClass('hide');
        $('.status-disabled').toggleClass('hide');
    });
});
