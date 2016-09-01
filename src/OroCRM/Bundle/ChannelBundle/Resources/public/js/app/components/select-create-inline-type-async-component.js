define(function(require) {
    'use strict';

    var SelectCreateInlineTypeAsyncComponent;
    var _ = require('underscore');
    var ParentComponent = require('oroform/js/app/components/select-create-inline-type-async-component');
    var mixin = require('./channel-aware-select-create-component-mixin');
    SelectCreateInlineTypeAsyncComponent = ParentComponent.extend(_.extend({}, mixin, {
        _super: function() {
            return SelectCreateInlineTypeAsyncComponent.__super__;
        }
    }));

    return SelectCreateInlineTypeAsyncComponent;
});
