import _ from 'underscore';
import ParentComponent from 'oroform/js/app/components/select-create-inline-type-component';
import mixin from './channel-aware-select-create-component-mixin';
const SelectCreateInlineTypeComponent = ParentComponent.extend(_.extend({}, mixin, {
    _super: function() {
        return SelectCreateInlineTypeComponent.__super__;
    }
}));

export default SelectCreateInlineTypeComponent;
