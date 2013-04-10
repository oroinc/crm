Overview
========

Table of Contents
-----------------
 - [Main Components](#main-components)
 - [Example Of Usage](#example-of-usage)
 - [Dependencies](#dependencies)

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
* JQuery Numeric - https://github.com/byllc/jquery-numeric;
* JQuery UI - https://github.com/jquery/jquery-ui;
* JQuery UI MultiSelect - https://github.com/ehynds/jquery-ui-multiselect-widget.
