/*global define*/
/*jslint nomen: true*/
define(function (require) {
    'use strict';

    var $ = require('jquery');
    var _ = require('underscore');
    require('jquery.select2');

    /**
     * @export orocrm/case/CaseRelation
     * @class  orocrm.case.CaseRelation
     */
    return {

        /**
         * @property {Object}
         */
        options: {
            selector: null,
            control: null
        },

        /**
         * @param {Array} options
         */
        initialize: function (options) {
            this.options = _.extend({}, this.options, options);

            this.updateVisibility();

            var self = this;
            $(this.options.control).on('change', _.bind(function () {
                self.updateVisibility();
            }, this));
        },

        updateVisibility: function () {
            $(this.options.selector).each(function () {
                $(this).find('input.select2').select2('val', '');
                $(this).hide();
            });

            $('[id*="' + $(this.options.control).val() + '"]')
                .parents(this.options.selector)
                .show();
        }
    }
});
