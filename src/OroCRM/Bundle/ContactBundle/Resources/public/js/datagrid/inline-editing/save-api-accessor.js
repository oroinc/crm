/** @lends ContactApiAccessor */
define(function(require) {
    'use strict';

    var ContactApiAccessor;

    var _ = require('underscore');
    var ApiAccessor = require('oroui/js/tools/api-accessor');

    ContactApiAccessor = ApiAccessor.extend(/** @exports ContactApiAccessor.prototype */{
        DEFAULT_HTTP_METHOD: 'GET',

        formName: void 0,
        /**
         * @returns {boolean}
         */
        isActiveCreateEntityRoute: function() {
            return this.route.get('routeName') === this.initialOptions.route_create_entity.route;
        },

        setUpdateEntityRoute: function() {
            this.route.set('routeName', this.initialOptions.route);
            this.httpMethod = this.initialOptions.http_method;
        },

        setCreateEntityRoute: function() {
            this.route.set('routeName', this.initialOptions.route_create_entity.route);
            this.httpMethod = this.initialOptions.route_create_entity.http_method;
        },

        initRoute: function(urlParameters) {
            var _urlParameters = _.clone(urlParameters);

            this.setUpdateEntityRoute();
            _urlParameters = this.prepareUrlParameters(_urlParameters);

            if (!this.route.validateParameters(_urlParameters)) {
                this.setCreateEntityRoute();
            }
        },

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
            this.initRoute(urlParameters);

            if (this.isActiveCreateEntityRoute()) {
                body.contactId = urlParameters.id;
                body.primary = true;
            }

            return ContactApiAccessor.__super__.send.apply(this, arguments);
        }
    });

    return ContactApiAccessor;
});
