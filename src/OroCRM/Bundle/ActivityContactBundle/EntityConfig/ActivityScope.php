<?php

namespace Oro\Bundle\ActivityContactBundle\EntityConfig;

use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigModel;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;

class ActivityScope
{
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
     * Contacting statistics storage custom fields default configuration
     *
     * @var array
     */
    public static $fieldsConfiguration = [
        self::LAST_CONTACT_DATE     => [
            'type'    => 'datetime',
            'mode'    => ConfigModel::MODE_READONLY,
            'options' => [
                'entity'       => [
                    'label'       => 'oro.activity_contact.ac_last_contact_date.label',
                    'description' => 'oro.activity_contact.ac_last_contact_date.description',
                ],
                'extend'       => [
                    'owner'     => ExtendScope::OWNER_CUSTOM,
                    'state'     => ExtendScope::STATE_ACTIVE,
                    'is_extend' => true
                ],
                'form'         => ['is_enabled' => false],
                'datagrid'     => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                'view'         => ['is_displayable' => false],
                'dataaudit'    => ['auditable' => false],
                'importexport' => ['excluded' => true],
                'security'     => ['permissions' => 'VIEW']
            ]
        ],
        self::LAST_CONTACT_DATE_IN  => [
            'type'    => 'datetime',
            'mode'    => ConfigModel::MODE_READONLY,
            'options' => [
                'entity'       => [
                    'label'       => 'oro.activity_contact.ac_last_contact_date_in.label',
                    'description' => 'oro.activity_contact.ac_last_contact_date_in.description',
                ],
                'extend'       => [
                    'owner'     => ExtendScope::OWNER_CUSTOM,
                    'state'     => ExtendScope::STATE_ACTIVE,
                    'is_extend' => true
                ],
                'form'         => ['is_enabled' => false],
                'datagrid'     => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                'view'         => ['is_displayable' => false],
                'dataaudit'    => ['auditable' => false],
                'importexport' => ['excluded' => true],
                'security'     => ['permissions' => 'VIEW']
            ]
        ],
        self::LAST_CONTACT_DATE_OUT => [
            'type'    => 'datetime',
            'mode'    => ConfigModel::MODE_READONLY,
            'options' => [
                'entity'       => [
                    'label'       => 'oro.activity_contact.ac_last_contact_date_out.label',
                    'description' => 'oro.activity_contact.ac_last_contact_date_out.description',
                ],
                'extend'       => [
                    'owner'     => ExtendScope::OWNER_CUSTOM,
                    'state'     => ExtendScope::STATE_ACTIVE,
                    'is_extend' => true
                ],
                'form'         => ['is_enabled' => false],
                'datagrid'     => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                'view'         => ['is_displayable' => false],
                'dataaudit'    => ['auditable' => false],
                'importexport' => ['excluded' => true],
                'security'     => ['permissions' => 'VIEW']
            ]
        ],
        self::CONTACT_COUNT         => [
            'type'    => 'integer',
            'mode'    => ConfigModel::MODE_READONLY,
            'options' => [
                'entity'       => [
                    'label'       => 'oro.activity_contact.ac_contact_count.label',
                    'description' => 'oro.activity_contact.ac_contact_count.description',
                ],
                'extend'       => [
                    'owner'     => ExtendScope::OWNER_CUSTOM,
                    'state'     => ExtendScope::STATE_ACTIVE,
                    'is_extend' => true
                ],
                'form'         => ['is_enabled' => false],
                'datagrid'     => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                'view'         => ['is_displayable' => false],
                'dataaudit'    => ['auditable' => false],
                'importexport' => ['excluded' => true],
                'security'     => ['permissions' => 'VIEW']
            ]
        ],
        self::CONTACT_COUNT_IN      => [
            'type'    => 'integer',
            'mode'    => ConfigModel::MODE_READONLY,
            'options' => [
                'entity'       => [
                    'label'       => 'oro.activity_contact.ac_contact_count_in.label',
                    'description' => 'oro.activity_contact.ac_contact_count_in.description',
                ],
                'extend'       => [
                    'owner'     => ExtendScope::OWNER_CUSTOM,
                    'state'     => ExtendScope::STATE_ACTIVE,
                    'is_extend' => true
                ],
                'form'         => ['is_enabled' => false],
                'datagrid'     => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                'view'         => ['is_displayable' => false],
                'dataaudit'    => ['auditable' => false],
                'importexport' => ['excluded' => true],
                'security'     => ['permissions' => 'VIEW']
            ]
        ],
        self::CONTACT_COUNT_OUT     => [
            'type'    => 'integer',
            'mode'    => ConfigModel::MODE_READONLY,
            'options' => [
                'entity'       => [
                    'label'       => 'oro.activity_contact.ac_contact_count_out.label',
                    'description' => 'oro.activity_contact.ac_contact_count_out.description',
                ],
                'extend'       => [
                    'owner'     => ExtendScope::OWNER_CUSTOM,
                    'state'     => ExtendScope::STATE_ACTIVE,
                    'is_extend' => true
                ],
                'form'         => ['is_enabled' => false],
                'datagrid'     => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                'view'         => ['is_displayable' => false],
                'dataaudit'    => ['auditable' => false],
                'importexport' => ['excluded' => true],
                'security'     => ['permissions' => 'VIEW']
            ]
        ]
    ];
}
