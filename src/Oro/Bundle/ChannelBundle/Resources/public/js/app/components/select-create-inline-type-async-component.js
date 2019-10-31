define(function(require) {
    'use strict';

    const _ = require('underscore');
    const ParentComponent = require('oroform/js/app/components/select-create-inline-type-async-component');
    const mixin = require('./channel-aware-select-create-component-mixin');
    const SelectCreateInlineTypeAsyncComponent = ParentComponent.extend(_.extend({}, mixin, {
        _super: function() {
            return SelectCreateInlineTypeAsyncComponent.__super__;
        }
    }));

    return SelectCreateInlineTypeAsyncComponent;
});
