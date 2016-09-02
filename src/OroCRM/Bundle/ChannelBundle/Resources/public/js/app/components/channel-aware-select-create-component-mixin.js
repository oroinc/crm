define(function(require) {
    'use strict';

    var selectCreateComponentMixin;
    var $ = require('jquery');
    var _ = require('underscore');

    selectCreateComponentMixin = {
        channelFieldSelector: null,
        channelRequired: false,
        presetChannelId: null,

        _super: function() {
            throw new Error('_super() should be defined');
        },

        initialize: function(options) {
            _.extend(this, _.pick(options, ['channelFieldSelector', 'channelRequired', 'presetChannelId']));
            this._super().initialize.apply(this, arguments);
            $(this.channelFieldSelector).on('change.component' + this.cid, _.bind(this.changeHandler, this));
            this.changeHandler();
        },

        changeHandler: function(event) {
            var $el = $(this.channelFieldSelector);
            var parts = this.getUrlParts();
            var channelIds = [];
            var currentVal = $el.val();

            if (currentVal) {
                channelIds.push(currentVal);
            }
            if (this.presetChannelId) {
                channelIds.push(this.presetChannelId);
            }

            if (this.channelRequired) {
                this.view.setEnableState(channelIds.length > 0);
            }

            parts.grid.parameters.params.channelIds = channelIds.join(',');
            var channelId = this.presetChannelId || $el.val();
            if (parts.hasOwnProperty('create') && channelId) {
                parts.create.parameters.channelId = channelId;
                this.setUrlParts(parts);
            }
            if (event !== void 0) {
                this.setSelection(null);
            }
        },
        dispose: function() {
            if (this.disposed) {
                return;
            }
            $(this.channelFieldSelector).off('change.component' + this.cid);
            this._super().dispose.apply(this, arguments);
        }
    };

    return selectCreateComponentMixin;
});
