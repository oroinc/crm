/*global define*/
define(['backbone', 'routing', 'oroemail/js/email/template/model'
    ], function (Backbone, routing, EmailTemplateModel) {
    'use strict';

    /**
     * @export  orocrmcampaign/js/email/template/collection
     * @class   orocrmcampaign.email.template.Collection
     * @extends Backbone.Collection
     */
    return Backbone.Collection.extend({
        route: 'orocrm_api_get_emailcampaign_email_templates',
        url: null,
        model: EmailTemplateModel,

        /**
         * Constructor
         */
        initialize: function () {
            this.url = routing.generate(this.route, {id: null});
        },

        /**
         * Regenerate route for selected entity
         *
         * @param id {String}
         */
        setEntityId: function (id) {
            this.url = routing.generate(this.route, {id: id});
        }
    });
});
