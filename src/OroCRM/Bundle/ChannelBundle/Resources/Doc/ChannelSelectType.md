Class ChannelSelectType
=======================

This class will return Channel select type which depend on channel status, it should be active,
and entities array which should be given in `form type` field config block.

Example:
-------

```php

    $builder->add(
        'dataChannel',
        'orocrm_channel_select_type',
        [
            'required' => true,
            'label'    => 'orocrm.some_field_name.label',
            'configs' =>[
                'entities' => [
                    'OroCRM\\Bundle\\AcmeBundle\\Entity\\Entity1',
                    'OroCRM\\Bundle\\AcmeBundle\\Entity\\Entity2'
                ],
            ]
        ]
    );
```

Entities will compare with `channel_types` block in channel_configuration.yml and show only channel which
has this entities collection. At result you should get all channels which has only `OroCRM\\Bundle\\AcmeBundle\\Entity\\Entity`
and `OroCRM\\Bundle\\AcmeBundle\\Entity\\Entity2`.
