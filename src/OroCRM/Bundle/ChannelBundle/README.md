OroCRMChannelBundle
===================

Bundle provides channel. It allows you to create channels linking integration. When channel has been created,
entities will be shown and hide when it has been deleted. Entities hide from all selects but u can find them on
EntityManager page. For using this ability you should create yaml file channel_configuration.yml
Config example:

      orocrm_channel:
          entity_data:
             -
                name: OroCRM\Bundle\SomeEntity\Entity\RealEntity
                dependent:
                      - OroCRM\Bundle\SomeEntity\Entity\RealEntityStatus
                      - OroCRM\Bundle\SomeEntity\Entity\RealEntityCloseReason
                navigation_items:
                      - menu.tab.real_entity_list
                      - menu.tab1.real_entity_list
                      - menu.tab1.submenu.real_entity_list

             -
                name: OroCRM\Bundle\SomeEntity\Entity\UnepicEntity
                dependent:
                      - OroCRM\Bundle\SomeEntity\Entity\UnepicEntityStatus
                navigation_items:
                      - menu.tab.unepic_entity_list
                      - menu.tab2.unepic_entity_list
                      - menu.tab1.submenu.unepic_entity_list

             -
                name: OroCRM\Bundle\SomeEntity\Entity\EntityFunnel
                dependent: ~
                dependencies:
                    - OroCRM\Bundle\SomeEntity\Entity\RealEntity
                    - OroCRM\Bundle\SomeEntity\Entity\UnepicEntity
                dependencies_condition: OR
                navigation_items:
                    - menu.tab.entity_funnel_list
                    - menu.tab.some_tab.some_tab.some_value

 - name - entity which will be shown/hidden
 - dependent - list of entities which will be shown/hidden too. (Related entities to the entity in field 'name')
 - navigation_items - list of menu items which will be shown.

Menu item should be hidden by hand in navigation.yml using parameter 'display' with value 'false'.
Example:

    oro_menu_config:
        items:
            tab:
                label: 'orocrm.some_entity.menu.tab.label'
                uri:   '#'
                display: false
                extras:
                    icon: icon
                    position: 20
