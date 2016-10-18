OroChannelBundle
===================

Entity data block
-----------------

This bundle brings "channel entity" to the system. Channel is a set of features which might be included in CRM.
Also channel may come with "customer datasource", it's basically integration that brings business entities into system.
_Feature_ means a set of entities and integration that covers business direction needs.

**For example**:
>Customer has B2B business and needs CRM that will provide complex B2B solution. So, in order to met this
requirements B2B channel could be created that will enable _leads_ and _opportunities_ or any other B2B feature in scope of this channel.
After this, the Sales menu appears on the UI and has Leads and Opportunities menus.

By default all specific to business direction features should be disabled, and will not be visible in reports, segments, menu etc.(except entity configuration)
In order to implement ability to enable feature in scope of channel `YourBundle/Resources/config/oro/channels.yml` configuration file should be created.

**Config example:**
```yml
      channels:
          entity_data:
             -
                name: Oro\Bundle\SomeEntity\Entity\RealEntity                # Entity FQCN
                dependent:                                                      # Service entities that dependent on availability of main entity
                      - Oro\Bundle\SomeEntity\Entity\RealEntityStatus
                      - Oro\Bundle\SomeEntity\Entity\RealEntityCloseReason
                navigation_items:                                               # Navigation items that responsible for entity visibility
                      - menu.tab.real_entity_list

             -
                name: Oro\Bundle\AcmeDemoBundle\Entity\AnotherEntity
                dependent: ~
                navigation_items:
                    - menu.tab.entity_funnel_list
                    - menu.tab.some_tab.some_tab.some_value
                belongs_to:
                    integration: integration_type_name                   # If entity belongs to integration, correspondent node should be set
                    connector:   another                                 # connector name
```


| Option                   | Description                                                                                       |
|--------------------------|---------------------------------------------------------------------------------------------------|
| `name`                   | Entity name                                                                                       |
| `dependent`              | List of entities which will be shown/hidden too. (Related entities to the entity in field 'name') |
| `navigation_items`       | List of menu items which should be enabled/disabled in any menu.                                  |
| `belongs_to.integration` | Integration type name                                                                             |
| `belongs_to.connector`   | Integration connector name                                                                        |

Menu item should be hidden by default in navigation configuration using parameter 'display' with value 'false'.

**Example:**
```yml
    menu_config:
        items:
            menu_item:
                label: 'oro.some_entity.menu.tab.label'
                display: false
        tree:
            application_menu:
                children:
                    menu_item: ~
```

Channel types block
-------------------

Channel is configured by `Channel Type` and `Entities` fields. Some types of channels that bring customers,
also bring the `integration` field to configure the integration. It should be described in configuration block:

**Config example:**
```yml
  channel_types:
        customer_channel_type:
            label: Channel type name
            entities:
                - Oro\Bundle\AcmeBundle\Entity\Entity
                - Oro\Bundle\AcmeBundle\Entity\Customer
            integration_type: some_type
            customer_identity: Oro\Bundle\ChannelBundle\Entity\CustomerIdentity
            lifetime_value: field
            priority: -10
```

| Option              | Description                                                                                                         | Required |
|---------------------|---------------------------------------------------------------------------------------------------------------------|----------|
| `label`             | Label of the channel type                                                                                           | yes      |
| `entities`          | Determines which fields will be defined in `entities` field after channel type has been selected                    | no       |
| `integration_type`  | Determines which integration type should be created in scope of particular channel that is based on the current ype | no       |
| `customer_identity` | Determines entity that will be used as customer identifier for channels that are based of the current ype           | no       |
| `lifetime_value`    | Determines which fields will be used from `customer_identity` for calculating lifetime sales value                  | no       |
| `priority`          | Uses to sort channel types by priority. Default value is 0                                                          | no       |


By default, if `customer_identity` option is not set `Oro\Bundle\ChannelBundle\Entity\CustomerIdentity` will be used as *customer identity* and
will be included automatically.

Lifetime sales value
--------------------

In order to bring full 360 degrees view of account in scope of the channel "Channel lifetime sales value" was defined.
Each channel type could define field from _customer identity_ entity that should be used as indicator of aggregated
amount for single customer.

OroChannel bundle provides mechanism for tracking changes of lifetime sales value per customer and stores history of those changes.
Developer needs just to configure lifetime field for channel type to enable tracking.

In order to use data from history **Amount provider** was implemented. It's registered as service for DIC with `oro_channel.provider.lifetime.amount_provider` identifier.
Also if you need to display **Life time** on the page you can use `oro_channel_lifetime_value` twig extension that brings `oro_channel_account_lifetime` twig function.

**Examples of usage:**
```twig
    Lifetime for {{ channel.name }}: {{ oro_channel_account_lifetime(account, channel)|oro_format_currency }}
```

