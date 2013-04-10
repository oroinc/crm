Frontend Architecture
=================

#### Table of Contents
 - [Overview](#overview)
 - [Backbone Developer Introduction](#backbone-developer-introduction)
 - [Backgrid Developer Introduction](#backgrid-developer-introduction)
 - [Basic Classes](#basic-classes)
 - [Actions](#actions)
 - [Filters](#filters)

Overview
--------

Grid bundle has rich representation of frontend side that is visible to end-user as UI widgets when grid is displayed. Frontend-side serves role of View of grid data. Main goals of grid frontend-side are trivial from perspective of View:

* display grid data
* manipulate grid data

More detailed responsibilities are based on requirements to grid UI. Among those requirements are:

* support of columns sorting
* pagination functionality
* provide row actions
* ability to apply filtering criteria
* use of filter, sorter or pager should change grid state
* able to change grid state without page reload using AJAX
* grid state should be saved in browser history
* apply browser's "Go Back" and "Go Forward" actions onto grid states history

Backbone Developer Introduction
-------------------------------

[Backgrid.js](http://wyuenho.github.com/backgrid/) is used as a basic library in OroGridBundle. It's JS modules extended from [Backgrid.js](http://wyuenho.github.com/backgrid/) modules to provide basic functionality of Grid widget. This library built using Backbone.js, and thus can be easily extended. If you don't familiar with [backbone.js](http://backbonejs.org/) look this reference http://backbonejs.org/.

Backbone.js provides several types of entities in the application:

* **View** - mix of View and Controller in classic MVC pattern
* **Model** - is a data container, behaves as active record and responsible for synchronining data with storage
* **Collection** - models composite, iterator, supports mass operations with models
* **Router** - component that allows you to implement the functionality of client side pages history by changing using URL hash fragments (#page). For purposes of routing History object is used. It serves as a global router (per frame) to handle hashchange events or pushState, match the appropriate route, and trigger callbacks

It should be noted that there might be also entities of other types if any type doesn't fit the requirements. In addition, there is a module of Events that is mixin of all Backbone modules. It gives object the ability to bind and trigger custom named events.

Backgrid Developer Introduction
-------------------------------

Detailed information on the library is here http://wyuenho.github.com/backgrid/. The main types of this library are:

* **Backgrid.Grid** - central object of Backgrid library, aggregate object that connects all grid's views and models together. Client should create instance of this object to have grid in it's UI. The default grid is HTML tag table. Grid has the ability to be configured with such data as:
collection - data source model, it's models will be displayed in grid
columns - information about what columns and in what way should be displayed in grid
* **Backgrid.Header** - header section of grid, responsible for outputting columns labels in cells of Backgrid.HeaderRow. By default represented with HTML tag thead.
* **Backgrid.Body** - body section of grid, responsible for outputting collection's models in it's rows (Backgrid.Row), each row in it's turn, consists of cells that match the corresponding grid columns. By default represented with HTML tag tbody.
* **Backgrid.Footer** - footer section of grid, responsible for output additional information of grid in footer section. By default represented with HTML tag tfoot.
* **Backgrid.Columns** - collection of grid columns
* **Backgrid.Column** - encapsulates model of модель grid column. Column module has next attributes:
 * **name** - unique column identifier. This identifier must be same as attribute of model
 * **label** - label of column displayed in grid header section
 * **sortable** - is allow sorting by column values
 * **editable** - is allow inline edit for column's cell
 * **renderable** - should column be rendered
 * **formatter** - instance of Backgrid.Formatter, this object responsible for converting corresponding model attribute to value that will be displayed in column cell
 * **cell** - instance of Backgrid.Cell, responsible for presentation of corresponding model attribute in column's cell of Backbone.Row
headerCell - instance of Backgrid.Cell, responsible for presentation of column cell in Backbone.HeaderRow
* **Backgrid.Row** - this object encapsulates representation of model in grid row. Row has embeds as many cells as available columns in grid. By default row represented with HTML tag tr.
* **Backgrid.HeaderRow** - encapsulates number of header cells. Extends from Backgrid.Row but unlike parent aggregates * Backgrid.HeaderCell's. As parent by default represented with HTML tag tr.
* **Backgrid.Cell** - is responsible for presenting model property in a row. Cell aggregates Backgrid.CellFormatter and Backgrid.CellEditor. By default cell represented with HTML tag td.
* **Backgrid.HeaderCell** - unlike Backgrid.Cell it doesn't have editor and formatter. Header cell displays column label and also provides UI controls for column sorting. By default cell represented with HTML tag th.
* **Backgrid.CellFormatter** - has one responsibility - convert value of model property with same name as in related column and return this value. Backgrid has formatters for main data types:
* **Backgrid.NumberFormatter** - for dealing with properties of number types
* **Backgrid.DatetimeFormatter** - for dealing with properties of date time types

Backbone.Grid is a class from backbone's View category. Any standard backbone's collection could be used together with grid. But to able to use the paginator in grid, you must first declare your collections to be a Backbone.PageableCollection, which is a simple subclass of the Backbone.js Collection with added pagination behavior.

Basic Classes
-------------

Bundle's JS module extends Backgrid.js and defines the following classes:

* **OroApp.PageableCollection**

Provides extended functionality of Backbone.PageableCollection. In particular, this object knows how to encode its state to string, and how to decode the string back to the state. This knowledge required by router of grid module that need representation of grid's collection state as a string.

In addition to everything else, this class holds filtering parameters that are used to request data. State is of collection is an object of next structure:
``` javascript
state: {
    firstPage: Integer, // pager position
    lastPage: Integer, // last available page
    currentPage: Integer, // current page
    pageSize: Integer, // page size
    totalPages: Integer, // total pages
    totalRecords: Integer, // total records in storage
    sortKey: Integer|null, // sort order
    order: -1|1|null, // Sort order: ascending or descending
    filters: Array // Array of applied filters
}
```

When the collection is requests data from storage, it sends a GET request using AJAX. This request contains all criteria based on which data storage is queried. Criteria parameters comes from the state of the collection. An example URL of collection's request to storage:
```
example.com/users/list.json?users[_pager][_page]=1&users[_pager][_per_page]=10
```

* **OroApp.DatagridRouter**

Inherited from OroApp.Router. This object acts as a router. Thanks to this class, user can for example select next page using pagination, change records number per page apply some sorting and then go back to original state using Back button. It also responsible for initializing collection with first state that came from URL that user requests.
An example URL that stores the state of grid:
```
example.com/users/list#g/i=2&p=25&s=email&o=-1
```
This line contains information about the page number (i = 2), the name of the field you are sorting (p = 25) and a ascending sort order (o = -1).

* **OroApp.Datagrid** In addition to basic grid, this class can work with loading mask, toolbar, set of filters, and set of actions.
* **Datagrid.LoadingMask** Serves to display the loading process to end-user when some request is in progress.
* **OroApp.DatagridToolbar** Aggregates control toolbar widgets, including paginator, and page size widgets.
OroApp.DatagridPagination and OroApp.DatagridPaginationInput
Paginator could have one of two possible presentations, using links as page numbers and using input field for entering and displaying page number.
* **OroApp.DatagridPageSize** This widget is used to control number of records displayed on one grid page.

Here is an example of code that initializes grid:
``` javascript
var collection = new OroApp.PageableCollection({
    inputName: "users",
    url: "/en/grid/users/list.json",
    state:{
        currentPage:1,
        pageSize:25,
        totalRecords:52
    }
});
var grid = new OroApp.Datagrid({
    collection: collection,
    columns:[
        {
            name:"id",
            label:"ID",
            sortable:true,
            editable:false,
            cell:Backgrid.IntegerCell.extend({ orderSeparator:'' })
        },
        {
            name:"username",
            label:"Username",
            sortable:true,
            editable:false,
            cell:Backgrid.StringCell
        },
        {
            name:"email",
            label:"Email",
            sortable:true,
            editable:false,
            cell:Backgrid.StringCell
        }
    ],
    entityHint: "Users",
    noDataHint: "No users were found to match your search. Try modifying your search criteria or creating a new ..."
});

$('#grid').html(grid.render().$el);
```

Actions
-------

If you need to allow a user to perform an action on records in the grid, this can be achieved by actions. Actions are designed thus that they can be used separately from the grid, but when you need to use actions in the grid, you just need to pass them into configuration. All added actions will be accessible in special actions column.

Action performs using instance of model and usually uses a link to do work on server. User can pass link using parameters:

* **link** (String) - Full link or property name in model where link is located;
* **backUrl** (Boolean or String) - if TRUE then additional parameter will be added to link, this parameter will have value of current window location. If *backUrl* is a String, that it will be used instead.
* **backUrlParameter** (String) - Parameter name used for *backUrl*, by default - "back".

Below is an example of initialization grid with actions:
``` javascript
var grid = new OroApp.Datagrid({
    actions: [
        OroApp.DatagridActionNavigate.extend({
            label: "Edit",
            icon: edit,
            placeholders: {"{id}":"id"},
            url: "/user/edit/{id}"
        }),
        OroApp.DatagridActionDelete.extend({
            label: "Delete",
            icon: "trash",
            placeholders: {"{id}":"id"},
            url: "/api/rest/latest/profiles/{id}.json"
        })
    ]
    // other configuration
});
```

Main classes and responsibilities:

* **OroApp.Datagrid** - grid contains collection of models and allowed actions that user can perform
* **OroApp.BackboneModel** - model that is represented by one of grid rows. Action is performed on concrete instances of models
* **OroApp.DatagridActionCell** - responsible for rendering grid's actions launchers
* **OroApp.DatagridAction** - abstract action that can be performed
* **OroApp.DatagridActionLauncher** - renders control that can be used by user to run action, for example a simple link
* **OroApp.DatagridActionDelete** - concrete action responsible for model delete
* **OroApp.DatagridActionNavigate** - concrete action responsible for navigating user to some URL

Filters
-------

Filters are used to change collection state according to criteria selected by user. Filters classes don't depend of grid. It couples only on collection.

Main classes and responsibilities:

* **OroApp.DatagridFilterList** - container for filters, renders all active filters, has a control to enable and disable filters
* **OroApp.DatagridFilter** - basic filter allows user to enter text value that will be used for data filtering
* **OroApp.DatagridFilterChoice** - filter that has input for value and inputs for operator, such as "contains", "not contains" and so on
* **OroApp.DatagridFilterSelect** - filter that allows to select one of available values
* **OroApp.DatagridFilterMultiSelect** - filter that allows to select any available values
* **OroApp.MultiSelectDecorator** - encapsulates additional logic related to select and multiselect widgets (filter list, select and multiselect filters)
* **OroApp.DatagridFilterData** and **OroApp.DatagridFilterDateTime** - used for filtering date and date time attributes
Backbone.Collection - collection of models that has particular state. By setting up filters user updates collection state. After it collection sends request to update it's data accordingly with new state that was applied with filters criteria

Below is example of creating filter list:
``` javascript
var filtersList = new OroApp.DatagridFilterList({
    collection:datagridCollection,
    addButtonHint:'+ Add more',
    filters:{
        username: OroApp.DatagridFilterChoice.extend({
            name:'username',
            label:'Username',
            enabled:true,
            choices:{"1": "contains", "2": "does not contain", "3": "is equal to"}
        }),
        gender: OroApp.DatagridFilterSelect.extend({
            name:'gender',
            label:'gender',
            enabled:false,
            options: {"18": "Male", "19": "Female"}
        })
    }
});
$('#filter').html(filtersList.render().$el);
```
