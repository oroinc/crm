<?php

namespace OroCRM\Bundle\ActivityContactBundle\EntityConfig;

class ActivityScope
{
    /** Activity group name for activities that represent communications. */
    const GROUP_ACTIVITY_CONTACT = 'activity_contact';

    /**
     * Activity entity classes that should be added to Counting group
     *
     * @var array
     */
    public static $contactingActivityClasses = [
        'OroCRM\Bundle\CallBundle\Entity\Call',
        'Oro\Bundle\EmailBundle\Entity\Email'
    ];

    /** Last contact activity (datetime) */
    const LAST_CONTACT_DATE = 'ac_last_contact_date';

    /** Last contact activity from our side (datetime) */
    const LAST_CONTACT_DATE_OUT = 'ac_last_contact_date_out';

    /** Last contact activity from client's side (datetime) */
    const LAST_CONTACT_DATE_IN = 'ac_last_contact_date_in';

    /** Total activities count */
    const CONTACT_COUNT = 'ac_contact_count';

    /** Total activities count from our side */
    const CONTACT_COUNT_OUT = 'ac_contact_count_out';

    /** Total activities count from client's side */
    const CONTACT_COUNT_IN = 'ac_contact_count_in';

    /**
     * Fields configuration
     *
     * @var array
     */
    public static $fieldsConfiguration = [
        self::LAST_CONTACT_DATE => [
            'type'    => 'datetime',
            'options' => [
                'entity'    => [
                    'label'       => 'orocrm.activity_contact.ac_last_contact_date.label',
                    'description' => 'orocrm.activity_contact.ac_last_contact_date.description',
                ],
                'extend'    => ['owner' => 'Custom', 'state' => 'Active', 'is_extend' => true],
                'form'      => ['is_enabled' => false],
                'datagrid'  => ['is_visible' => false],
                'view'      => ['is_displayable' => false],
                'dataaudit' => ['auditable' => false]
            ]
        ],
        self::LAST_CONTACT_DATE_IN => [
            'type'    => 'datetime',
            'options' => [
                'entity'    => [
                    'label'       => 'orocrm.activity_contact.ac_last_contact_date_in.label',
                    'description' => 'orocrm.activity_contact.ac_last_contact_date_in.description',
                ],
                'extend'    => ['owner' => 'Custom', 'state' => 'Active', 'is_extend' => true],
                'form'      => ['is_enabled' => false],
                'datagrid'  => ['is_visible' => false],
                'view'      => ['is_displayable' => false],
                'dataaudit' => ['auditable' => false]
            ]
        ],
        self::LAST_CONTACT_DATE_OUT => [
            'type'    => 'datetime',
            'options' => [
                'entity'    => [
                    'label'       => 'orocrm.activity_contact.ac_last_contact_date_out.label',
                    'description' => 'orocrm.activity_contact.ac_last_contact_date_out.description',
                ],
                'extend'    => ['owner' => 'Custom', 'state' => 'Active', 'is_extend' => true],
                'form'      => ['is_enabled' => false],
                'datagrid'  => ['is_visible' => false],
                'view'      => ['is_displayable' => false],
                'dataaudit' => ['auditable' => false]
            ]
        ],
        self::CONTACT_COUNT     => [
            'type'    => 'integer',
            'options' => [
                'entity' => [
                    'label'       => 'orocrm.activity_contact.ac_contact_count.label',
                    'description' => 'orocrm.activity_contact.ac_contact_count.description',
                ],
                'extend'    => ['owner' => 'Custom', 'state' => 'Active', 'is_extend' => true],
                'form'      => ['is_enabled' => false],
                'datagrid'  => ['is_visible' => false],
                'view'      => ['is_displayable' => false],
                'dataaudit' => ['auditable' => false]
            ]
        ],
        self::CONTACT_COUNT_IN  => [
            'type'    => 'integer',
            'options' => [
                'entity' => [
                    'label'       => 'orocrm.activity_contact.ac_contact_count_in.label',
                    'description' => 'orocrm.activity_contact.ac_contact_count_in.description',
                ],
                'extend'    => ['owner' => 'Custom', 'state' => 'Active', 'is_extend' => true],
                'form'      => ['is_enabled' => false],
                'datagrid'  => ['is_visible' => false],
                'view'      => ['is_displayable' => false],
                'dataaudit' => ['auditable' => false]
            ]
        ],
        self::CONTACT_COUNT_OUT => [
            'type'    => 'integer',
            'options' => [
                'entity' => [
                    'label'       => 'orocrm.activity_contact.ac_contact_count_out.label',
                    'description' => 'orocrm.activity_contact.ac_contact_count_out.description',
                ],
                'extend'    => ['owner' => 'Custom', 'state' => 'Active', 'is_extend' => true],
                'form'      => ['is_enabled' => false],
                'datagrid'  => ['is_visible' => false],
                'view'      => ['is_displayable' => false],
                'dataaudit' => ['auditable' => false]
            ]
        ]
    ];
}
