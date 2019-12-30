# OroActivityContactBundle

OroActivityContactBundle enables tracking of attempts to contact customers in OroCRM applications.

The bundle allows developers to register activity as contact activity. These logged activities are used during the contact attempts calculation and allow admin users to see contact attempts statistics for entities where contact activities are enabled.

Out-of-the-box, the bundle registers calls and emails as contact activities.

## General

The bundle introduces a `contacting activity` term that describes regular activity, some sort of communication process that has direction.
For example: `Call` and `Email` can come from the client or from the sales manager. If a client sends an email or makes a call to a manager, this is treated as an incoming activity. However, when a manager sends an email or makes a call to a client, the activity is an outgoing one.   

## Bundle responsibility

- Gether statistics of contacting activities
- Determine the activity direction
- Detect last datetime of the incoming/outgoing activities
- Show calculated data in the `Record Activities` block
- Recalculate activities

## How to do activity entity belongs to contacting activities type

Create a provider that implements [DirectionProviderInterface](Direction/DirectionProviderInterface.php)
and make it a tagged service, e.g.

```
    tags:
        - { name: oro_activity_direction.provider, class: Acme\Bundle\AppBundle\Entity\MyActivity }
```

## Install and update features

On application install or during the update from previous version:

 - via [ActivityContactMigrationQuery](Migration/ActivityContactMigrationQuery.php) required fields(for statistics storage) will be added for entities with enabled "Call" & "Email" activities 
 - job will be added to execute [oro:activity-contact:recalculate](Command/ActivityContactRecalculateCommand.php) command. Recalculation of existing activities.

Please note:

 - if cron is not configured you will have to manually run job daemon via UI or execute command from console.
 - the responsibility of command is to check entities for which contacting activities (Call & Email) is enabled, and recalculate contacting activities per each record of entities.

## EntityManagement

On enabling any contacting activity for entity via entity management, during "Schema Update" procedure if entity do not have statistics field - they will be added automatically.

Please note:

  - those fields are just regular custom fields, but with READ_ONLY mode 
  - they can NOT be deleted via UI by user
  - even on disabling all contacting activities fields will stay
  - by default fields disabled on view, edit and grid pages, but they can be enabled via UI (be careful with that)
  
## Reports and Segments

- Due to the fact that statistics field are regular custom fields, customer can build any report/segment with such fields without any restrictions or unpredictable cases.
- Even after disabling activity features for entity, all reports/segments based on such entity will work the same way. 
