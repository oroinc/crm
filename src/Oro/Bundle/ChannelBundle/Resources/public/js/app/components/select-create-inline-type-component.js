define(function(require) {
    'use strict';

    const _ = require('underscore');
    const ParentComponent = require('oroform/js/app/components/select-create-inline-type-component');
    const mixin = require('./channel-aware-select-create-component-mixin');
    const SelectCreateInlineTypeComponent = ParentComponent.extend(_.extend({}, mixin, {
        _super: function() {
            return SelectCreateInlineTypeComponent.__super__;
        }
    }));

    return SelectCreateInlineTypeComponent;
});
