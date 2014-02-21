define(['jquery'], function($){
    return {

        /**
         * @desc Fire name link click
         * @callback
         */
        squadClickHandler: function(onClickEven){
            if($(onClickEven.target).hasClass('contact-squad-email-link')){
                return;
            }
            $(this).find('.contact-squad-name-link').click();
        },

        /**
         * @constructs
         */
        init: function(){
            $('.contact-squad').click(this.squadClickHandler);
        }
    };
});