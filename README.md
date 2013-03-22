OroGridBundle - Overview
===========================================

OroGridBundle provides functionality to create and show grid with some information. Grid provides functionality to display user information by pages, apply filters, sort information and perform some specific row actions.

Grid functionality consists of backend and frontend parts. Backend part responsible for interaction with DB, obtaining of source data and sending this data to frontend. Frontend part is fully functional JavaScript grid which process all user actions on UI and synchronize grid content by interacting with backend using AJAX.

#### Table of Contents

- [Overview](#orogridbundle---overview)
    - [Main Components](#main-components)
    - [Example Of Usage](#example-of-usage)
    - [Dependencies](#dependencies)
- [Backend Architecture](#orogridbundle---backend-architecture)
    - [Configuration](#configuration)
    - [Datagrid Managers](#datagrid-managers)
    - [Entity Builders](#entity-builders)
    - [Datagrid](#datagrid)
- [Frontend Architecture](#orogridbundle---frontend-architecture)


Main Components
---------------

#### Backend Components

* **Datagrid Manager** - main entity that provides all required interfaces and methods to create and initialize grid (builders, factories, parameters, route generator). Datagrid Manager encapsulates grid configuration and passes it to builder entities to initialize grid with specified parameters. Also it receives request information and pass it to Datagrid entity.

* **Datagrid** - entity that contains grid information and responsible for applying of request parameters. It contains all specific entities responsible for generating of database request (pager, filters, sorters) and binds request parameters to appropriate entities. Datagrid returns array of Data Entities as a result of request processing.

* **Data Entity** - stores information for one grid row, can be either Doctrine entity or simple flat array. Provides interface to get row data that will be displayed in grid.

#### Frontend Components

* **Datagrid JS Objects** - main JS objects (datagrid, collection) which stores grid information and performs synchronization requests to backend in case of change of parameters. They encapsulate logic related to data storing and processing, and contain and render Datagrid JS Views.

* **Datagrid JS Views** - JS objects responsible for displaying of all UI components (datagrid, pager, filters, sorters, row actions). They process actions of user on UI and inform Datagrid JavaScript components about performed changes.


Example Of Usage
----------------

To create simple datagrid user must create Datagrid Manager class with configuration, create it's instance, build and pass Datagrid object to template and insert appropriate template.

#### Datagrid Manager

``` php
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

class DemoDatagridManager extends DatagridManager
{
    protected function getListFields()
    {
        $fieldId = new FieldDescription();
        $fieldId->setName('id');
        $fieldId->setOptions(
            array(
                'type'  => FieldDescriptionInterface::TYPE_INTEGER,
                'label' => 'ID',
            )
        );
        $fieldName = new FieldDescription();
        $fieldName->setName('name');
        $fieldName->setOptions(
            array(
                'type'  => FieldDescriptionInterface::TYPE_TEXT,
                'label' => 'name',
            )
        );
        return array($fieldId, $fieldName);
    }
}
```

#### Datagrid Manager Configuration

```
services:
    acme_demo_grid.demo_grid.manager:
        class: My\Bundle\Namespace\DemoDatagridManager
        tags:
            - name: oro_grid.datagrid.manager
              datagrid_name: demo
              route_name: my_controller_action_route
```

#### Controller Action

``` php
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory\QueryFactory;
use My\Bundle\Namespace\DemoDatagridManager;

class DemoController extends Controller
{
    /**
     * @Route("/demo/grid", name="my_controller_action_route")
     */
    public function gridAction(Request $request)
    {
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder
            ->select('id', 'name')
            ->from('MyBundle:Entity', 'e');

        /** @var $queryFactory QueryFactory */
        $queryFactory = $this->get('acme_demo_grid.demo_grid.manager.default_query_factory');
        $queryFactory->setQueryBuilder($queryBuilder);

        /** @var $datagridManager DemoDatagridManager */
        $datagridManager = $this->get('acme_demo_grid.demo_grid.manager');
        $datagrid = $datagridManager->getDatagrid();

        if ('json' == $request->getRequestFormat()) {
            $view = 'OroGridBundle:Datagrid:list.json.php';
        } else {
            $view = 'MyBundle:Demo:grid.html.twig';
        }
        return $this->render($view, array('datagrid' => $datagrid));
    }
}
```

#### Twig Template

```
{% include 'OroGridBundle:Include:javascript.html.twig' with {'datagrid': datagrid, 'selector': '#backgrid'} %}
{% include 'OroGridBundle:Include:stylesheet.html.twig' %}

<div id="backgrid"></div>
```


Dependencies
------------

#### Backend Dependencies

* Oro FlexibleEntityBundle - https://github.com/laboro/FlexibleEntityBundle;
* Oro UIBundle - https://github.com/laboro/UIBundle;
* Sonata AdminBundle  2.1 (Oro fork) - https://github.com/laboro/SonataAdminBundle;
* Sonata DoctrineORM AdminBundle 2.1 - https://github.com/sonata-project/SonataDoctrineORMAdminBundle.

#### Frontend Dependencies

* Backbone.js - https://github.com/documentcloud/backbone;
* Underscore.js - https://github.com/documentcloud/underscore;
* Backbone BootstrapModal - https://github.com/powmedia/backbone.bootstrap-modal;
* Backbone Pageable - http://github.com/wyuenho/backbone-pageable;
* Backgrid + extensions - http://github.com/wyuenho/backgrid;
* Moment.js - https://github.com/timrwood/moment/;
* JQuery UI Datepicker - https://github.com/jquery/jquery-ui;
* JQuery Timepicker - https://github.com/trentrichardson/jQuery-Timepicker-Addon;
* JQuery Select2 - https://github.com/ivaynberg/select2;
* JQuery Numeric - https://github.com/byllc/jquery-numeric.


OroGridBundle - Backend Architecture
====================================

Datagird backend consists of several entities, which are used to perform specific actions. Every entity implements interface, so every part can be easy extended and replaced with external component.

Datagrid entities use standard Symfony interfaces to perform translation, validation and form data processing. Also some interfaces and entities are extended from Sonata AdminBundle classes, so basic Sonata classes can be injected into datagrid entities.

#### Used External Interfaces

**Symfony**

* Translator - Symfony\Component\Translation\TranslatorInterface;
* Validator - Symfony\Component\Validator\ValidatorInterface;
* Form Factory - Symfony\Component\Form\FormFactoryInterface.

**Sonata AdminBundle**

* Datagrid - Sonata\AdminBundle\Datagrid\DatagridInterface;
* Filter - Sonata\AdminBundle\Filter\FilterInterface;
* Filter Factory - Sonata\AdminBundle\Filter\FilterFactoryInterface;
* Pager - Sonata\AdminBundle\Datagrid\PagerInterface;
* Proxy Query - Sonata\AdminBundle\Datagrid\ProxyQueryInterface.


Configuration
-------------

Configuration files must contains services configuration for Datagrid Managers and all theirs custom dependencies.
Datagrid Manager dependencies should be passed using either tag attributes, or manually using
[setter method injection](http://symfony.com/doc/master/book/service_container.html#optional-dependencies-setter-injection).

#### Datagrid Manager Configuration

Datagrid Manager receives parameters through tag attributes. List of parameters and attributes is presented below.

* **class** - Datagrid Manager class name;
* **name** - reserved Datagrid Manager tag name;
* **datagrid\_name** - datagrid unique ID, used to set form name and isolate separate grids from each other; setter method is *setName*;
* **entity\_hint** (optional) - string which is used to set UI datagrid name; setter method is *setEntityHint*;
* **entity\_name** (optional) - string that represents Doctrine entity name which should be used to select;
* **query\_entity\_alias** (optional) - string that represents Doctrine entity alias which should be used in request;
* **route\_name** - used to create default Route Generator based on specified route name; can be optional if user specified route_generator parameter;
* **query\_factory** (optional) - Query Factory service ID which will be passed to Datagrid Manager; setter method is *setQueryFactory*;
* **route\_generator** (optional) - Route Generator service ID which will be passed to Datagrid Manager; setter method is *setRouteGenerator*;
* **parameters** (optional) - Parameters service ID which will be passed to Datagrid Manager; setter method is *setParameters*;
* **datagrid\_builder** (optional) - Datagrid Builder service ID which will be passed to Datagrid Manager; setter method is *setDatagridBuilder*;
* **list\_builder** (optional) - List Builder service ID which will be passed to Datagrid Manager; setter method is *setListBuilder*;
* **translator** (optional) - Symfony Translator service ID which will be passed to Datagrid Manager; setter method is *setTranslator*;
* **validator** (optional) - Symfony Validator service ID which will be passed to Datagrid Manager; setter method is *setValidator*.

```
services:
    acme_demo_grid.product_grid.manager:
        class: Acme\Bundle\DemoGridBundle\Datagrid\ProductDatagridManager
        tags:
            - name: oro_grid.datagrid.manager
              datagrid_name: product
              entity_hint: products
              entity_name: ~
              query_entity_alias: ~
              route_name: acme_demo_gridbundle_product_list
              query_factory: ~
              route_generator: ~
              parameters: ~
              datagrid_builder: ~
              list_builder: ~
              translator: ~
              validator: ~
```

#### Flexible Datagrid Manager Configuration

Flexible Datagrid Manager has the same configuration as regular Datagird Manager
with the exceptions of **flexible** attribute - it must has true value, and **entity\_name** attribute - it is obligatory.

```
services:
    acme_demo_grid.user_grid.manager:
        class: Acme\Bundle\DemoGridBundle\Datagrid\UserDatagridManager
        tags:
            - name: oro_grid.datagrid.manager
              datagrid_name: users
              entity_name: Oro\Bundle\UserBundle\Entity\User
              entity_hint: users
              flexible: true
              route_name: acme_demo_gridbundle_user_list
```


Datagrid Managers
-----------------

Datagrid Managers provides inner interface for developer to work with grid. They receive dependencies through setter methods, store configuration and build datagrid entity.

There are two types of Datagrid Manager - regular and flexible. Regular Datagrid Manager works with regular Doctrine entities and flat arrays as source data, Flexible Datagrid Manager works with Flexible Entities which provides by OroFlexibleEntityBundle.

#### Class Description

* **Datagrid \ DatagridManagerInterface** - general interface for all Datagrid Managers, provides setter method to inject dependencies through Symfony Container;
* **Datagrid \ DatagridManager** - abstract Datagrid Manager which implements basic method to get Datagrid, contains methods to specify grid configuration;
* **Datagrid \ FlexibleDatagridManager** - abstract Flexible Datagrid Manager, provides setter for Flexible Entity Manager, getter for flexible attributes and methods to convert flexible types to regular field and filter types.

#### Configuration

Following example shows configuration of two datagrid managers - regular and flexible.

```
parameters:
    acme_demo_grid.user_grid.manager.class: Acme\Bundle\DemoGridBundle\Datagrid\UserDatagridManager
    acme_demo_grid.product_grid.manager.class: Acme\Bundle\DemoGridBundle\Datagrid\ProductDatagridManager

services:
    acme_demo_grid.user_grid.manager:
        class: %acme_demo_grid.user_grid.manager.class%
        tags:
            - name: oro_grid.datagrid.manager
              datagrid_name: users
              entity_name: Oro\Bundle\UserBundle\Entity\User
              entity_hint: users
              flexible: true
              route_name: acme_demo_gridbundle_user_list

    acme_demo_grid.product_grid.manager:
        class: %acme_demo_grid.product_grid.manager.class%
        tags:
            - name: oro_grid.datagrid.manager
              datagrid_name: product
              entity_hint: products
              route_name: acme_demo_gridbundle_product_list
```

#### Code Example

Following example shows simple Datagrid Manager with two fields, filters, sorters and row action.

``` php
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;

class ProductDatagridManager extends DatagridManager
{
    /**
     * @var FieldDescriptionCollection
     */
    protected $fieldsCollection;

    /**
     * @return FieldDescriptionCollection
     */
    protected function getFieldDescriptionCollection()
    {
        if (!$this->fieldsCollection) {
            $this->fieldsCollection = new FieldDescriptionCollection();
            $fieldManufacturerId = new FieldDescription();
            $fieldManufacturerId->setName('id');
            $fieldManufacturerId->setOptions(
                array(
                    'type'         => FieldDescriptionInterface::TYPE_INTEGER,
                    'label'        => 'ID',
                    'entity_alias' => 'm',
                    'field_name'   => 'id',
                    'filter_type'  => FilterInterface::TYPE_NUMBER,
                    'required'     => false,
                    'sortable'     => true,
                    'filterable'   => true,
                    'show_filter'  => true,
                )
            );
            $this->fieldsCollection->add($fieldManufacturerId);

            $fieldManufacturerName = new FieldDescription();
            $fieldManufacturerName->setName('name');
            $fieldManufacturerName->setOptions(
                array(
                    'type'        => FieldDescriptionInterface::TYPE_TEXT,
                    'label'        => 'Name',
                    'entity_alias' => 'm',
                    'field_name'   => 'name',
                    'filter_type'  => FilterInterface::TYPE_STRING,
                    'required'     => false,
                    'sortable'     => true,
                    'filterable'   => true,
                    'show_filter'  => true,
                )
            );
            $this->fieldsCollection->add($fieldManufacturerName);
        }
        return $this->fieldsCollection;
    }

    /**
     * {@inheritdoc}
     */
    protected function getListFields()
    {
        return $this->getFieldDescriptionCollection()->getElements();
    }

    /**
     * {@inheritdoc}
     */
    protected function getSorters()
    {
        $fields = array();
        /** @var $fieldDescription FieldDescription */
        foreach ($this->getFieldDescriptionCollection() as $fieldDescription) {
            if ($fieldDescription->isSortable()) {
                $fields[] = $fieldDescription;
            }
        }
        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilters()
    {
        $fields = array();
        /** @var $fieldDescription FieldDescription */
        foreach ($this->getFieldDescriptionCollection() as $fieldDescription) {
            if ($fieldDescription->isFilterable()) {
                $fields[] = $fieldDescription;
            }
        }
        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRowActions()
    {
        $editAction = array(
            'name'         => 'edit',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label'        => 'Edit',
                'icon'         => 'edit',
                'route'        => 'product_edit',
                'placeholders' => array(
                    'id' => 'id',
                ),
            )
        );
        return array($editAction);
    }
}
```


Entity Builders
---------------

Entity Builders provides functionality to build specific types of service entities for Datagrids and Datagrid Managers.

#### Class Description

* **Builder \ DatagridBuilderInterface** - basic interface for Datagrid Builder, provides getter for Datagrid entity and methods to inject additional service entities (filters, sorters, row actions);
* **Builder \ ORM \ DatagridBuilder** - implements Datagrid Builder interface, receives form and additional entities factories to create entity instances, and creates Pager entity;
* **Builder \ ListBuilderInterface** - basic interface to build Field Description entities and add it to Field Collection;
* **Builder \ ORM \ ListBuilder** - implements List Builder interface and all its methods.

#### Configuration

```
parameters:
    oro_grid.builder.datagrid.class: Oro\Bundle\GridBundle\Builder\ORM\DatagridBuilder
    oro_grid.builder.list.class:     Oro\Bundle\GridBundle\Builder\ORM\ListBuilder

services:
    oro_grid.builder.datagrid:
        class:     %oro_grid.builder.datagrid.class%
        arguments:
            - @form.factory
            - @oro_grid.filter.factory
            - @oro_grid.sorter.factory
            - @oro_grid.action.factory
            - %oro_grid.datagrid.class%

    oro_grid.builder.list:
        class:     %oro_grid.builder.list.class%
```


Datagrid
--------

Datagrid is a main entity that contains fields, additional entities, DB query, form and parameters, process it and returns results - data that will be rendered on UI.

#### Class Description

* **Sonata \ AdminBundle \ Datagrid \ DatagridInterface** - Sonata AdminBundle datagrid interface, that provides basic method signatures to work with fields, filters, pager and result.
* **Datagrid \ DatagridInterface** - basic datagrid interface, that provides additional methods to work with sorters, actions, router and names.
* **Datagrid \ Datagrid** - Datagrid entity implementation of Datagrid interface, implements all methods and has protected methods to apply additional entities parameters to DB request and bind source parameters.

#### Configuration

```
parameters:
    oro_grid.datagrid.class: Oro\Bundle\GridBundle\Datagrid\Datagrid
```


OroGridBundle - Frontend Architecture
=====================================


### Overview

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

### Backbone Developer Introduction

[Backgrid.js](http://wyuenho.github.com/backgrid/) is used as a basic library in OroGridBundle. It's JS modules extended from [Backgrid.js](http://wyuenho.github.com/backgrid/) modules to provide basic functionality of Grid widget. This library built using Backbone.js, and thus can be easily extended. If you don't familiar with [backbone.js](http://backbonejs.org/) look this reference http://backbonejs.org/.

Backbone.js provides several types of entities in the application:

* **View** - mix of View and Controller in classic MVC pattern
* **Model** - is a data container, behaves as active record and responsible for synchronining data with storage
* **Collection** - models composite, iterator, supports mass operations with models
* **Router** - component that allows you to implement the functionality of client side pages history by changing using URL hash fragments (#page). For purposes of routing History object is used. It serves as a global router (per frame) to handle hashchange events or pushState, match the appropriate route, and trigger callbacks

It should be noted that there might be also entities of other types if any type doesn't fit the requirements. In addition, there is a module of Events that is mixin of all Backbone modules. It gives object the ability to bind and trigger custom named events.

### Backgrid Developer Introduction

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

### Bundle Frontend Architecture

#### Basic Classes

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

#### Actions
If you need to allow a user to perform an action on records in the grid, this can be achieved by actions. Actions are designed thus that they can be used separately from the grid, but when you need to use actions in the grid, you just need to pass them into configuration. All added actions will be accessible in special actions column.

Below is an example of initialization grid with actions:
``` javascript
var grid = new OroApp.Datagrid({
   actions: [{
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

#### Filters
Filters are used to change collection state according to criteria selected by user. Filters classes don't depend of grid. It couples only on collection.

Main classes and responsibilities:

* **OroApp.DatagridFilterList** - container for filters, renders all active filters, has a control to enable and disable filters
* **OroApp.DatagridFilter** - basic filter allows user to enter text value that will be used for data filtering
* **OroApp.DatagridFilterChoice** - filter that has input for value and inputs for operator, such as "contains", "not contains" and so on
* **OroApp.DatagridFilterSelect** - filter that allows to select one of available values
* **OroApp.DatagridFilterMultiSelect** - filter that allows to select any available values
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

Select2 is a jQuery based replacement for select boxes. It supports searching, remote data sets, and infinite scrolling of results.
This library was borrowed to implement select widgets in grid's filters.
