/** @lends CollectionApiAccessor */
define(function(require) {
    'use strict';

    var CollectionApiAccessor;

    var _ = require('underscore');
    var ApiAccessor = require('oroui/js/tools/api-accessor');

    CollectionApiAccessor = ApiAccessor.extend(/** @exports CollectionApiAccessor.prototype */{
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
                body.entityId = urlParameters.id;
                body.primary = true;
            }

            return CollectionApiAccessor.__super__.send.apply(this, arguments);
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
            if (data && data[this.initialOptions.field_name] === '') {
                return true;
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

    return CollectionApiAccessor;
});