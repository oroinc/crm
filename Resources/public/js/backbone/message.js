var Oro = Oro || {};

Oro.Messages = Oro.Messages || {};

Oro.Messages.messageContainer = '#flash-messages';

Oro.Messages.messageContentContainer = 'alert-empty';

$(function() {
    Oro.Messages.showMessage = function(type, message) {
        var newMessage = $(Oro.Messages.messageContainer + ' .' + Oro.Messages.messageContentContainer).clone();
        newMessage.find('.message').html(message);
        newMessage.removeClass(Oro.Messages.messageContentContainer);
        newMessage.addClass('alert-' + type);
        $(Oro.Messages.messageContainer).append(newMessage);
    }
});

