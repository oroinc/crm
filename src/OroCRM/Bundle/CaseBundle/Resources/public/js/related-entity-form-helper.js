/*global define*/
/*jslint nomen: true*/
define(function (require) {
    'use strict';

    var $ = require('jquery');
    var _ = require('underscore');
    require('jquery.select2');

    /**
     * @export orocrm/case/related-entity-form-helper
     * @class  orocrm.case.RelatedEntityFormHelper
     */
    return {
        /**
         * @param {Object} options
         * @param {string} options.selector
         * @param {string} options.control
         */
        initialize: function (options) {
            this.updateVisibility(options);

            var self = this;
            $(options.control).on('change', _.bind(function () {
                self.updateVisibility(options);
            }, this));
        },

        /**
         * @param {Object} options
         * @param {string} options.selector
         * @param {string} options.control
         */
        updateVisibility: function (options) {
            $(options.selector).each(function () {
                $(this).hide();
            });

            $('[id*="' + $(options.control).val() + '"]')
                .parents(options.selector)
                .show();
        }
    }
});
