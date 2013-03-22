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


OroGridBundle - Frontend Architecture
=====================================
