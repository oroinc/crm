Javascript Widgets
------------------

On frontend side filter form types are represented by javascript widgets. 
They are located in Resources/public/js directory and use javascript namespace OroApp.Filter.

###Table of Contents

- [OroApp.Filter.List](#oroappfilterlist)
- [OroApp.Filter.AbstractFilter](#oroappfilterabstractfilter)
- [OroApp.Filter.TextFilter](#oroappfiltertextfilter)
- [OroApp.Filter.ChoiceFilter](#oroappfilterchoicefilter)
- [OroApp.Filter.NumberFilter](#oroappfilternumberfilter)
- [OroApp.Filter.SelectFilter](#oroappfilterselectfilter)
- [OroApp.Filter.MultiSelectFilter](#oroappfiltermultiselectfilter)
- [OroApp.Filter.MultiSelectDecorator](#oroappfiltermultiselectdecorator)
- [OroApp.Filter.DateFilter](#oroappfilterdatefilter)
- [OroApp.Filter.DateTimeFilter](#oroappfilterdatetimefilter)
- [Example of Usage](#example-of-usage)
- [References](#references)

###OroApp.Filter.List

Container for filters, renders all active filters, has a control to enable and disable filters

**Rendered As**

Combobox with Add button

**Parent**

_Backbone.View_

**Properties**

* filters: Object
* addButtonHint: String

**Properties Description**

* **filters** - Named list of filters, instances of OroApp.Filter.Abstract.
* **addButtonHint** - Test of button that is used for adding filters to the list.


###OroApp.Filter.AbstractFilter

Abstract filter that has common methods for all filters.

**Parent**

_Backbone.View_

**Properties**

* name: String
* label: String
* enabled: Boolean

**Properties Description**

* **name** - Unique name name of filter.
* **label** - Label of filter. This values is used for displaying filter in list options
and in rendering of filter html template.
* **enabled** - Is filter enabled or not. If filter is not enabled it will not be displayed in filter list.

###OroApp.Filter.TextFilter

Has only one text input that can be filled by user. Operator type is not supported.

**Rendered As**

Clickable control with filter value hint.
When control is clicked a popup container with text input and update button is shown.

**Parent**

_OroApp.Filter.AbstractFilter_

**Inherit Properties**

* name
* label
* enabled

###OroApp.Filter.ChoiceFilter

This widget supports value input and operator type input.

**Rendered As**

Same as parent widget but also contains radio buttons for operator choices

**Parent**

_OroApp.Filter.TextFilter_

**Options**

* choices: Object

**Inherit Properties**

* name
* label
* enabled

**Properties Description**

* **choices** - List of filter types (f.e. contains, not contains for text filter)

###OroApp.Filter.NumberFilter

Filter that has an operator and additionally able to format value as a number (integer, decimal)

**Rendered As**

Same as parent widget but has behavior of parsing numbers as input value

**Parent**

_OroApp.Filter.ChoiceFilter_

**Options**

* formatter: OroApp.Filter.NumberFormatter
* formatterOptions: Object

**Inherit Properties**

* name
* label
* enabled
* choices

**Properties Description**

* **formatter** - Instance of OroApp.Filter.NumberFormatter, this object is responsible for converting string
to number and backward.
* **formatterOptions** - This value will be used as argument for OroApp.Filter.NumberFormatter.
It contains next options:
    * decimals: Integer - Number of decimals to display. Must be an integer.
    * decimalSeparator: String - The separator to use whendisplaying decimals.
    * orderSeparator: String - The separator to use to separator thousands. May be an empty string.

###OroApp.Filter.SelectFilter

Filter that allows to select one of available values

**Rendered As**

Clickable control with filter value hint. When control is clicked a combobox with available values is displayed.

**Parent**

_OroApp.Filter.AbstractFilter_

**Options**

* options: Object

**Inherit Properties**

* name
* label
* enabled

**Properties Description**

* **options** - List of available options for select and multiselect filters.

###OroApp.Filter.MultiSelectFilter

Filter that allows to select any available values.

**Rendered As**

Same as parent.

**Parent**

_OroApp.Filter.SelectFilter_

**Inherit Properties**

* name
* label
* enabled
* options

###OroApp.Filter.MultiSelectDecorator

Encapsulates additional logic related to select and multiselect widgets (filter list, select and multiselect filters).

###OroApp.Filter.DateFilter

Used for filtering date values.

**Rendered As**

Popup container has inputs for start and end dates. Each input is clickable calendar.
Available operators displayed as radio buttons.

**Parent**

_OroApp.Filter.ChoiceFilter_

**Properties**

* typeValues
* externalWidgetOptions

**Inherit Properties**

* name
* label
* enabled
* choices

**Properties Description**

* **typeValues** - List of date/datetime type values for between/not between filter types.
* **externalWidgetOptions** - Additional date/datetime widget options, gets from form type.

###OroApp.Filter.DateTimeFilter

Used for filtering date time values.

**Rendered As**

Same as parent but clickable calendars also display controls for setting time

**Parent**

_OroApp.Filter.DateFilter_

**Properties**

* typeValues
* externalWidgetOptions

**Inherit Properties**

* name
* label
* enabled
* choices
* typeValues
* externalWidgetOptions

###Example of Usage

Below is example of creating filter list:

```
var filtersList = new OroApp.Filter.List({
    addButtonHint: '+ Add more',
    filters: {
        username: OroApp.Filter.ChoiceFilter.extend({
            name:'username',
            label:'Username',
            enabled:true,
            choices:{"1": "contains", "2": "does not contain", "3": "is equal to"}
        }),
        gender: OroApp.Filter.SelectFilter.extend({
            name:'gender',
            label:'gender',
            enabled:false,
            options: {"18": "Male", "19": "Female"}
        },
        salary: OroApp.Filter.NumberFilter.extend({
            name:'salary',
            label:'salary',
            enabled:false,
            choices:{"1": "=", "2": ">", "3": "<"},
            formatterOptions: {"decimals": 0, "grouping": false, "orderSeparator": "", "decimalSeparator": "."}
        })
    }
});
$('#filter').html(filtersList.render().$el);
```

###References

* Backbone.js - http://backbonejs.org/
