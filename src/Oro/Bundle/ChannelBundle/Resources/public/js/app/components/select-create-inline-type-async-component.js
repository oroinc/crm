import _ from 'underscore';
import ParentComponent from 'oroform/js/app/components/select-create-inline-type-async-component';
import mixin from './channel-aware-select-create-component-mixin';
const SelectCreateInlineTypeAsyncComponent = ParentComponent.extend(_.extend({}, mixin, {
    _super: function() {
        return SelectCreateInlineTypeAsyncComponent.__super__;
    }
}));

export default SelectCreateInlineTypeAsyncComponent;
