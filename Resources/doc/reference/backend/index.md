Backend Architecture
====================

#### Table of Contents

 - [Configuration](#configuration)
 - [Datagrid Managers](#datagrid-managers)
 - [Entity Builders](#entity-builders)
 - [Datagrid](#datagrid)
 - [Proxy Query](#proxy-query)
 - [Fields](#fields)
 - [Properties](#properties)
 - [Pager](#pager)
 - [Filters](#filters)
 - [Sorters](#sorters)
 - [Actions](#actions)
 - [Parameters](#parameters)
 - [Route Generator](#route-generator)


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

Alternatively flexible_manager attribute can be passed. This attribute must contain id of flexible manager service. In this case entity_name attribute will not be used to determine flexible manager service so it can be omitted. For example:

```
services:
    acme_demo_grid.user_grid.manager:
        class: Acme\Bundle\DemoGridBundle\Datagrid\UserDatagridManager
        tags:
            - name: oro_grid.datagrid.manager
              datagrid_name: users
              entity_hint: users
              flexible_manager: oro_user.manager.flexible
              route_name: user_flexible_manager
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
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;

class ProductDatagridManager extends DatagridManager
{
    /**
     * @var FieldDescriptionCollection
     */
    protected $fieldsCollection;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @param Router $router
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @return array
     */
    protected function getProperties()
    {
        return array(
            new UrlProperty('edit_link', $this->router, 'oro_product_edit', array('id'))
        );
    }

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
                'label'   => 'Edit',
                'icon'    => 'edit',
                'link'    => 'edit_link',
                'backUrl' => true,
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


Proxy Query
-----------

Proxy Query is an objects that encapsulates interaction with DB and provides getter for query object. Proxy Queries are made by Query Factory entities.

#### Class Description

* **Sonata \ AdminBundle \ Datagrid \ ProxyQueryInterface** - Sonata AdminBundle interface that provides methods to get and set query parameters and execute DB query;
* **Sonata \ DoctrintORMBundle \ Datagrid \ ProxyQuery** - implementation of Sonata proxy query interface;
* **Datagrid \ ProxyQueryInterface** - basic interface for Proxy Query fully extended from Sonata interface;
* **Datagrid \ ORM \ ProxyQuery** - implementation of Proxy Query entity extended from Sonata proxy query entity, provides getter for Query Builder;
* **Datagrid \ QueryFactoryInterface**  - interface for Query Factory entity, provide method to create query entity;
* **Datagrid \ ORM \ QueryFactory \ AbstractQueryFactory** - abstract implementation of Query Factory interface, has protected method to create Proxy Query entity;
* **Datagrid \ ORM \ QueryFactory \ QueryFactory** - extended from abstract Query Factory, receives Query Builder as source parameter and creates Proxy Query based on it;
* **Datagrid \ ORM \ QueryFactory \ EntityQueryFactory** - extended from abstract Query Factory, receives Doctrine entity, class name and alias as source parameters and creates Proxy Query based on Query Builder made by Doctrine Entity Repository.

#### Configuration

```
parameters:
    oro_grid.orm.query_factory.entity.class: Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory\EntityQueryFactory
    oro_grid.orm.query_factory.query.class:  Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory\QueryFactory
```


Fields
------

Field Description is an entity that contains all information about one grid column - name, type, filter/sorter flags etc. Filter Descriptions are stored in Field Description Collection.

#### Class Description

* **Field \ FieldDescriptionInterface** - basic interface for Field Description, provides setters an getters for field parameters and options;
* **Field \ FieldDescription** - Field Description implementation of basic interface, has method to extract field value from source object;
* **Field \ FieldDescriptionCollection** - storage for FieldDescription entities, implements ArrayAccess, Countable and IteratorAggregate interfaces and their methods.

Properties
----------

Property is an entity that responsible for providing values for grid results. It can for example be a value for some column. When grid results are converting to some format (e.g. json) all grid properties will be asked to provide a values for each result element. Property Collection aggregates list of Properties.

#### Class Description

* **Property \ PropertyInterface** - basic interface for Property, provides specific value of result data element;
* **Property \ AbstractProperty** - abstract class for Property, knows how to get values from arrays and objects using the most appropriate way - public methods "get<Name>" or "is<Name>" or public property;
* **Property \ FieldProperty** - by default Field Description has this type of property, it knows how to get right value from data based on field name and field type;
* **Property \ UrlProperty** - can generate URL as it's value using Router, route name and the list of data property names that should be used as route parameters;

#### Example of Getting Values

```
$data = array();
/** @var $datagrid \Oro\Bundle\GridBundle\Datagrid\Datagrid */
foreach ($datagrid->getResults() as $object) {
    $record = array();
    /** @var $property \Oro\Bundle\GridBundle\Property\PropertyInterface */
    foreach ($datagrid->getProperties() as $property) {
        $record[$property->getName()] = $property->getValue($object);
    }
    $data[] = $record;
}
```

#### Example of Creating URL Property

```
class UserDatagridManager extends FlexibleDatagridManager
{
    protected function getProperties()
    {
        return array(
            new UrlProperty('show_link', $this->router, 'oro_user_show', array('id')),
            new UrlProperty('edit_link', $this->router, 'oro_user_edit', array('id')),
            new UrlProperty('delete_link', $this->router, 'oro_api_delete_profile', array('id')),
        );
    }
    // ... other methods
}
```

Pager
-------------

Pager is an entity that provides information about pagination parameters on grid and applies it to DB request.

#### Class Description

* **Sonata \ AdminBundle \ Datagrid \ PagerInterface** - Sonata AdminBundle pager interface;
* **Sonata \ AdminBundle \ Datagrid \ Pager** - abstract implementation of Sonata pager interface;
* **Sonata \ DoctrineORMAdminBundle \ Datagrid \ Pager** - Sonata implementation of pager for Doctrine ORM extended from abstract pager;
* **Datagrid \ PagerInterface** - basic interface for Pager entity, provides getters and setters for pagination parameters, applies it and returns values of pagination parameters;
* **Datagrid \ ORM \ Pager** - Pager implementation of basic interface with all required methods.


Filters
---------------

Filters allows to apply additional conditions to DB request and show in grid only required rows. Filter entities are created by Filter Factory.

Filter functionality based on Sonata AdminBundle filters.

#### Class Description

* **Sonata \ AdminBundle \ Filter \ FilterInterface** - Sonata AdminBundle standard filter interface;
* **Sonata \ AdminBundle \ Filter \ Filter** - Sonata AdminBundle abstract filter implementation;
* **Sonata \ DoctirneORMAdminBundle \ Filter \ Filter** - Sonata AdminBundle abstract filter implementation for Doctrine ORM;
* **Filter \ FilterInterface** - basic interface for Grid Filter entities;
* **Filter \ ORM \ AbstractFilter** - abstract implementation of Filter entity;
* **Filter \ ORM \ NumberFilter** - ORM filter for number values;
* **Filter \ ORM \ StringFilter** - ORM filter for string values;
* **Filter \ ORM \ AbstractDateFilter** - abstract filter implementation to work with date/datetime values;
* **Filter \ ORM \ DateRangeFilter** - ORM filter for date and date range values;
* **Filter \ ORM \ DateTimeRangeFilter** - ORM filter for datetime and datetime range values;
* **Filter \ ORM \ Flexible \ AbstractFlexibleFilter** - abstract ORM filter to work with flexible attributes;
* **Filter \ ORM \ Flexible \ NumberFlexibleFilter** - ORM filter to work with number flexible attributes;
* **Filter \ ORM \ Flexible \ StringFlexibleFilter** - ORM filter to work with string flexible attributes;
* **Filter \ ORM \ Flexible \ OptionsFlexibleFilter** - ORM filter to work with options flexible attributes;
* **Sonata \ AdminBundle \ Filter \ FilterFactoryInterface** - Sonata AdminBundle interface for filter factory;
* **Filter \ FilterFactoryInterface** - basic interface for Filter Factory entity;
* **Filter \ FilterFactory** - basic implementation of Filter Factory entity to create Filter entities.

#### Configuration

**Configuration of Services**

```
parameters:
    oro_grid.filter.factory.class: Oro\Bundle\GridBundle\Filter\FilterFactory

services:
    oro_grid.filter.factory:
        class:     %oro_grid.filter.factory.class%
        arguments: ["@service_container", ~]
```

**Configuration of Filter Types**

```
services:
    oro_grid.orm.filter.type.date_range:
        class: Oro\Bundle\GridBundle\Filter\ORM\DateRangeFilter
        arguments: ["@translator"]
        tags:
            - { name: oro_grid.filter.type, alias: oro_grid_orm_date_range }

    oro_grid.orm.filter.type.datetime_range:
        class: Oro\Bundle\GridBundle\Filter\ORM\DateTimeRangeFilter
        arguments: ["@translator"]
        tags:
            - { name: oro_grid.filter.type, alias: oro_grid_orm_datetime_range }

    oro_grid.orm.filter.type.number:
        class:     Oro\Bundle\GridBundle\Filter\ORM\NumberFilter
        arguments: ["@translator"]
        tags:
            - { name: oro_grid.filter.type, alias: oro_grid_orm_number }

    oro_grid.orm.filter.type.string:
        class:     Oro\Bundle\GridBundle\Filter\ORM\StringFilter
        arguments: ["@translator"]
        tags:
            - { name: oro_grid.filter.type, alias: oro_grid_orm_string }

    oro_grid.orm.filter.type.flexible_number:
        class:     Oro\Bundle\GridBundle\Filter\ORM\Flexible\FlexibleNumberFilter
        arguments: ["@service_container", "@oro_grid.orm.filter.type.number"]
        tags:
            - { name: oro_grid.filter.type, alias: oro_grid_orm_flexible_number }

    oro_grid.orm.filter.type.flexible_string:
        class:     Oro\Bundle\GridBundle\Filter\ORM\Flexible\FlexibleStringFilter
        arguments: ["@service_container", "@oro_grid.orm.filter.type.string"]
        tags:
            - { name: oro_grid.filter.type, alias: oro_grid_orm_flexible_string }

    oro_grid.orm.filter.type.flexible_options:
        class:     Oro\Bundle\GridBundle\Filter\ORM\Flexible\FlexibleOptionsFilter
        arguments: ["@service_container"]
        tags:
            - { name: oro_grid.filter.type, alias: oro_grid_orm_flexible_options }
```


Sorters
---------------

Sorter is an entity that allows to add sort conditions to DB request. Sorters are created by Sorter Factory.

#### Class Description

* **Sorter \ SorterInterface** - basic interface for Sorter entity;
* **Sorter \ ORM \ Sorter** - Sorter implementation for Doctrine ORM;
* **Sorter \ ORM \ Flexible \ FlexibleSorter** - Sorter ORM implementation for flexible attributes;
* **Sorter \ SorterFactoryInterface** - basic interface for Sorter Factory entity;
* **Sorter \ SorterFactory** - basic implementation of Sorter Factory entity to create Sorter entities.

#### Configuration

**Configuration of Services**

```
parameters:
    oro_grid.sorter.factory.class: Oro\Bundle\GridBundle\Sorter\SorterFactory

services:
    oro_grid.sorter.factory:
        class:     %oro_grid.sorter.factory.class%
        arguments: ["@service_container"]
```

**Configuration of Sorter Types**

```
parameters:
    oro_grid.sorter.class:          Oro\Bundle\GridBundle\Sorter\ORM\Sorter
    oro_grid.sorter.flexible.class: Oro\Bundle\GridBundle\Sorter\ORM\Flexible\FlexibleSorter

services:
    oro_grid.sorter:
        class:     %oro_grid.sorter.class%
        scope:     prototype

    oro_grid.sorter.flexible:
        class:     %oro_grid.sorter.flexible.class%
        scope:     prototype
        arguments: ["@service_container"]
```


Actions
---------------

Action is an entity that represents grid action in some specific context - for example, row action. Actions are created by Action Factory.

#### Class Description

* **Action / ActionInterface** - basic interface for Action entity;
* **Action / AbstracAction** - abstract implementation of Action entity, includes route processing;
* **Action / RedirectAction** - redirect action implementation;
* **Action / DeleteAction** - delete action implementation;
* **Action / ActionFactoryInterface** - basic interface for Action Factory;
* **Action / ActionFactory** - Action Factory interface implementation to create Action entities.

#### Configuration

**Configuration of Services**

```
parameters:
    oro_grid.action.factory.class:       Oro\Bundle\GridBundle\Action\ActionFactory

services:
    oro_grid.action.factory:
        class:     %oro_grid.action.factory.class%
        arguments: ["@service_container", ~]
```

**Configuration of Action Types**

```
parameters:
    oro_grid.action.type.redirect.class: Oro\Bundle\GridBundle\Action\RedirectAction
    oro_grid.action.type.delete.class:   Oro\Bundle\GridBundle\Action\DeleteAction

services:
    oro_grid.action.type.redirect:
        class: %oro_grid.action.type.redirect.class%
        arguments: ["@oro_user.acl_manager"]
        tags:
            - { name: oro_grid.action.type, alias: oro_grid_action_redirect }

    oro_grid.action.type.delete:
        class: %oro_grid.action.type.delete.class%
        arguments: ["@oro_user.acl_manager"]
        tags:
            - { name: oro_grid.action.type, alias: oro_grid_action_delete }
```


Parameters
----------

Parameters entity encapsulates all parameters required for grid. Default implementation receives parameters from Request object.

#### Class Description

* **Datagrid / ParametersInterface** - basic interface for Parameters entity;
* **Datagrid / RequestParameters** - Parameters interface implementation, gets data from Request object.

#### Configuration

```
parameters:
    oro_grid.datagrid.parameters.class: Oro\Bundle\GridBundle\Datagrid\RequestParameters
```

Route Generator
---------------

Route Generator in an entity that generates all service URL's for grid backend and frontend parts based on source route name.

#### Class Description

* **Route \ RouteGeneratorInterface** - basic interface for Route Generator entity;
* **Route \ DefaultRouteGenerator** - implementation of Route generator that receives source data from Parameters entity.

#### Configuration

```
parameters:
    oro_grid.route.default_generator.class: Oro\Bundle\GridBundle\Route\DefaultRouteGenerator
```
