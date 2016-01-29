OroCRMActivityContactBundle
=============================

General
--------
In scope of bundle we represent new terminology "contacting activity". It describes regular activity, but such activity can represent some sort of communication process and can have direction.
For example: "Call" and "Email", each of them can act from client or from manager, so if client send email or call to manager - it will be incoming action, and if manager send email or call to client - it will be outgoing action.   


Bundle responsibility
-----------------------

- Contacting activities counting and calculation.
- Determination of activity direction.
- Detecting last datetime per incoming/outgoing activities.
- Showing calculated data in "Record Activities" block of view pages.
- Recalculation of activities

How to do activity entity belongs to contacting activities type
-----------------------------------------------------------------

In the simplest way:

- You will need provider which should implement [DirectionProviderInterface](Direction/DirectionProviderInterface.php)
- This provider should be a tagged service, e.g.

```
    tags:
        - {name: orocrm_activity_direction.provider}
```

Install and update features
-----------------------------

On application install or during update from previous version:

 - via [ActivityContactMigrationQuery](Migration/ActivityContactMigrationQuery.php) required fields(for statistics storage) will be added for entities with enabled "Call" & "Email" activities 
 - job will be added to execute [oro:activity-contact:recalculate](Command/ActivityContactRecalculateCommand.php) command. Recalculation of existing activities.

Please note:

 - if cron is not configured you will have to manually run job daemon via UI or execute command from console.
 - the responsibility of command is to check entities for which contacting activities (Call & Email) is enabled, and recalculate contacting activities per each record of entities.

EntityManagement
------------------

On enabling any contacting activity for entity via entity management, during "Schema Update" procedure if entity do not have statistics field - they will be added automatically.

Please note:

  - those fields are just regular custom fields, but with READ_ONLY mode 
  - they can NOT be deleted via UI by user
  - even on disabling all contacting activities fields will stay
  - by default fields disabled on view, edit and grid pages, but they can be enabled via UI (be careful with that)
  
Reports and Segments
----------------------

- Due to the fact that statistics field are regular custom fields, customer can build any report/segment with such fields without any restrictions or unpredictable cases.
- Even after disabling activity features for entity, all reports/segments based on such entity will work the same way. 
