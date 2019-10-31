define(function(require) {
    'use strict';


    const _ = require('underscore');
    const ApiAccessor = require('oroui/js/tools/api-accessor');

    const ContactApiAccessor = ApiAccessor.extend(/** @lends ContactApiAccessor.prototype */{
        /**
         * Validates url parameters
         *
         * @param {Object} urlParameters - Url parameters to compose the url
         * @returns {boolean} - true, if parameters are valid and route url could be built
         */
        validateUrlParameters: function(urlParameters) {
            const originalRouteName = this.route.get('routeName');
            const originalHttpMethod = this.httpMethod;

            this.initRoute(urlParameters);
            const parameters = this.prepareUrlParameters(urlParameters);

            const valid = this.route.validateParameters(parameters);

            /**
             * We need to reset changed stuff as this method is called from inline-editing-plugin.js::isEditable
             * which is called because of row.js::delegateEventToCell - mouseenter event
             * which causes incorrect changing route and http method after it is set correctly in "send" method
             *
             * @todo this method should be refactored in CRM-6003 so that it doesn't change any state
             */
            this.route.set('routeName', originalRouteName);
            this.httpMethod = originalHttpMethod;

            return valid;
        },

        send: function(urlParameters, body, headers, options) {
            urlParameters = _.clone(urlParameters);
            this.initRoute(urlParameters, body);

            if (this.isActiveCreateEntityRoute()) {
                body.contactId = urlParameters.id;
                body.primary = true;
            }

            return ContactApiAccessor.__super__.send.call(this, urlParameters, body, headers, options);
        },

        initRoute: function(urlParameters, body) {
            let _urlParameters = _.clone(urlParameters);

            this.setUpdateEntityRoute();
            _urlParameters = this.prepareUrlParameters(_urlParameters);

            if (!this.route.validateParameters(_urlParameters)) {
                this.setCreateEntityRoute();
            }

            if (this.route.validateParameters(_urlParameters)) {
                if (this.canSetDeleteEntityRoute(body)) {
                    this.setDeleteEntityRoute();
                }
            }
        },

        /** @returns {boolean} */
        canSetDeleteEntityRoute: function(data) {
            if (data) {
                if (data.email === '') {
                    return true;
                }

                if (data.phone === '') {
                    return true;
                }
            }

            return false;
        },

        setUpdateEntityRoute: function() {
            this.route.set('routeName', this.initialOptions.route);
            this.httpMethod = this.initialOptions.http_method;
        },

        setCreateEntityRoute: function() {
            this.route.set('routeName', this.initialOptions.route_create_entity.name);
            this.httpMethod = this.initialOptions.route_create_entity.http_method;
        },

        setDeleteEntityRoute: function() {
            this.route.set('routeName', this.initialOptions.route_delete_entity.name);
            this.httpMethod = this.initialOptions.route_delete_entity.http_method;
        },

        /** @returns {boolean} */
        isActiveCreateEntityRoute: function() {
            return this.route.get('routeName') === this.initialOptions.route_create_entity.name;
        },

        /** @returns {boolean} */
        isActiveDeleteEntityRoute: function() {
            return this.route.get('routeName') === this.initialOptions.route_delete_entity.name;
        }
    });

    return ContactApiAccessor;
});
