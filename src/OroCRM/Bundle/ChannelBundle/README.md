OroCRMChannelBundle
===================

This bundle brings "channel entity" to the system. Channel is a set of features which might be included in CRM.
Also channel may come with "customer datasource", it's basically integration that brings business entities into system.
_Feature_ means a set of entities and integration that covers business direction needs.

**For example**:
>Customer has B2B business and needs CRM that will provide complex B2B solution. So, in order to met this
requirements B2B channel could be created that will enable _leads_ and _opportunities_ or any other B2B feature in scope of this channel.
After this, the Sales menu appears on the UI and has Leads and Opportunities menus.

By default all specific to business direction features should be disabled, and will not be visible in reports, segments, menu etc.(except entity configuration )
In order to implement ability to enable feature in scope of channel  - configuration file should be created.

**Config example:**
```yml
      orocrm_channel:
          entity_data:
             -
                name: OroCRM\Bundle\SomeEntity\Entity\RealEntity                # Entity FQCN
                dependent:                                                      # Service entities that dependent on availability of main entity
                      - OroCRM\Bundle\SomeEntity\Entity\RealEntityStatus
                      - OroCRM\Bundle\SomeEntity\Entity\RealEntityCloseReason
                navigation_items:                                               # Navigation items that responsible for entity visibility
                      - menu.tab.real_entity_list

             -
                name: OroCRM\Bundle\AcmeDemoBundle\Entity\AnotherEntity
                dependent: ~
                navigation_items:
                    - menu.tab.entity_funnel_list
                    - menu.tab.some_tab.some_tab.some_value
                belongs_to:
                    integration: integration_type_name                   # If entity belongs to integration, correspondent node should be set
```

 - `name` - entity name
 - `dependent` - list of entities which will be shown/hidden too. (Related entities to the entity in field 'name')
 - `navigation_items` - list of menu items which should be enabled/disabled in any menu.
 - `belongs_to.integration` - integration type name

Menu item should be hidden by default in navigation configuration using parameter 'display' with value 'false'.

**Example:**
```yml
    oro_menu_config:
        items:
            menu_item:
                label: 'orocrm.some_entity.menu.tab.label'
                display: false
        tree:
            application_menu:
                children:
                    menu_item: ~
```
