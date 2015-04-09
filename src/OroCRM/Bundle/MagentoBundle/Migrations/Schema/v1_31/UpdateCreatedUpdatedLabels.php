<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_31;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigIndexFieldValueQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class UpdateCreatedUpdatedLabels implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $fields = [
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\Address',
                'field' => 'created',
                'value' => 'oro.ui.created_at'
            ],
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\Address',
                'field' => 'updated',
                'value' => 'oro.ui.updated_at'
            ],
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\CartAddress',
                'field' => 'updated',
                'value' => 'oro.ui.updated_at'
            ],
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\CartAddress',
                'field' => 'created',
                'value' => 'oro.ui.created_at'
            ],
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\Cart',
                'field' => 'createdAt',
                'value' => 'oro.ui.created_at'
            ],
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\Cart',
                'field' => 'updatedAt',
                'value' => 'oro.ui.updated_at'
            ],
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\CartItem',
                'field' => 'createdAt',
                'value' => 'oro.ui.created_at'
            ],
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\CartItem',
                'field' => 'updatedAt',
                'value' => 'oro.ui.updated_at'
            ],
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\Customer',
                'field' => 'createdAt',
                'value' => 'oro.ui.created_at'
            ],
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\Customer',
                'field' => 'updatedAt',
                'value' => 'oro.ui.updated_at'
            ],
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\Order',
                'field' => 'createdAt',
                'value' => 'oro.ui.created_at'
            ],
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\Order',
                'field' => 'updatedAt',
                'value' => 'oro.ui.updated_at'
            ],
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\Product',
                'field' => 'createdAt',
                'value' => 'oro.ui.created_at'
            ],
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\Product',
                'field' => 'updatedAt',
                'value' => 'oro.ui.updated_at'
            ]
        ];

        foreach ($fields as $field) {
            $queries->addQuery(
                new UpdateEntityConfigFieldValueQuery(
                    $field['entityName'],
                    $field['field'],
                    'entity',
                    'label',
                    $field['value']
                )
            );
            $queries->addQuery(
                new UpdateEntityConfigIndexFieldValueQuery(
                    $field['entityName'],
                    $field['field'],
                    'entity',
                    'label',
                    $field['value']
                )
            );
        }
    }
}
