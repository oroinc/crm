define(function(require) {
    'use strict';

    var SelectCreateInlineTypeComponent;
    var _ = require('underscore');
    var ParentComponent = require('oroform/js/app/components/select-create-inline-type-component');
    var mixin = require('./channel-aware-select-create-component-mixin');
    SelectCreateInlineTypeComponent = ParentComponent.extend(_.extend({}, mixin, {
        _super: function() {
            return SelectCreateInlineTypeComponent.__super__;
        }
    }));

    return SelectCreateInlineTypeComponent;
});
