# Class ChannelSelectType

This class brings **Channel selection** field type that returns channels to select depends on included entities and
current channel status (active or inactive). In order to filter choices by list of included entities field should be created
with  `entities` set.

## Example

```php
    $builder->add(
        'dataChannel',
        'oro_channel_select_type',
        [
            'required' => true,
            'label'    => 'oro.some_field_name.label',
            'entities' => [
                'Oro\\Bundle\\AcmeBundle\\Entity\\Entity1',
                'Oro\\Bundle\\AcmeBundle\\Entity\\Entity2'
            ]
        ]
    );
```

Field with configuration above will show channels that are currently in **active** state and has both entities included.
