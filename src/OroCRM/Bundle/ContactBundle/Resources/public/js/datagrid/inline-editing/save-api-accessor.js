/** @lends ContactApiAccessor */
define(function(require) {
    'use strict';

    var ContactApiAccessor;

    var _ = require('underscore');
    var ApiAccessor = require('oroui/js/tools/api-accessor');

    ContactApiAccessor = ApiAccessor.extend(/** @exports ContactApiAccessor.prototype */{
        /**
         * Validates url parameters
         *
         * @param {Object} urlParameters - Url parameters to compose the url
         * @returns {boolean} - true, if parameters are valid and route url could be built
         */
        validateUrlParameters: function(urlParameters) {
            this.initRoute(urlParameters);
            var parameters = this.prepareUrlParameters(urlParameters);

            return this.route.validateParameters(parameters);
        },

        send: function(urlParameters, body, headers, options) {
            this.initRoute(urlParameters, body);

            if (this.isActiveCreateEntityRoute()) {
                body.contactId = urlParameters.id;
                body.primary = true;
            }

            if (this.isActiveDeleteEntityRoute()) {
                body = {data: [urlParameters[this.initialOptions.route_delete_entity.entityId]]};
            }

            return ContactApiAccessor.__super__.send.call(this, urlParameters, body, headers, options);
        },

        initRoute: function(urlParameters, body) {
            var _urlParameters = _.clone(urlParameters);

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
        },

        prepareUrlParameters: function(urlParameters) {
            if (this.isActiveDeleteEntityRoute()) {
                return urlParameters;
            }

            return ContactApiAccessor.__super__.prepareUrlParameters.apply(this, arguments);
        }
    });

    return ContactApiAccessor;
});
