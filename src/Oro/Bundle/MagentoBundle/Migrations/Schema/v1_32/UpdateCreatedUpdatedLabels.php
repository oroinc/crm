<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_32;

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
        foreach ($this->getFieldsParamsForUpdate() as $field) {
            $queries->addQuery(
                new UpdateEntityConfigFieldValueQuery(
                    $field['entityName'],
                    $field['field'],
                    'entity',
                    'label',
                    $field['value'],
                    $field['replace']
                )
            );
            $queries->addQuery(
                new UpdateEntityConfigIndexFieldValueQuery(
                    $field['entityName'],
                    $field['field'],
                    'entity',
                    'label',
                    $field['value'],
                    $field['replace']
                )
            );
        }
    }

    /**
     * @return array
     */
    protected function getFieldsParamsForUpdate()
    {
        return [
            [
                'entityName' => 'Oro\Bundle\MagentoBundle\Entity\Address',
                'field'      => 'created',
                'value'      => 'oro.ui.created_at',
                'replace'    => 'oro.magento.address.created.label'
            ],
            [
                'entityName' => 'Oro\Bundle\MagentoBundle\Entity\Address',
                'field'      => 'updated',
                'value'      => 'oro.ui.updated_at',
                'replace'    => 'oro.magento.address.updated.label'
            ],
            [
                'entityName' => 'Oro\Bundle\MagentoBundle\Entity\CartAddress',
                'field'      => 'updated',
                'value'      => 'oro.ui.updated_at',
                'replace'    => 'oro.magento.cartaddress.updated.label'
            ],
            [
                'entityName' => 'Oro\Bundle\MagentoBundle\Entity\CartAddress',
                'field'      => 'created',
                'value'      => 'oro.ui.created_at',
                'replace'    => 'oro.magento.cartaddress.created.label'
            ],
            [
                'entityName' => 'Oro\Bundle\MagentoBundle\Entity\Cart',
                'field'      => 'createdAt',
                'value'      => 'oro.ui.created_at',
                'replace'    => 'oro.magento.cart.created_at.label'
            ],
            [
                'entityName' => 'Oro\Bundle\MagentoBundle\Entity\Cart',
                'field'      => 'updatedAt',
                'value'      => 'oro.ui.updated_at',
                'replace'    => 'oro.magento.cart.updated_at.label'
            ],
            [
                'entityName' => 'Oro\Bundle\MagentoBundle\Entity\CartItem',
                'field'      => 'createdAt',
                'value'      => 'oro.ui.created_at',
                'replace'    => 'oro.magento.cartitem.created_at.label'
            ],
            [
                'entityName' => 'Oro\Bundle\MagentoBundle\Entity\CartItem',
                'field'      => 'updatedAt',
                'value'      => 'oro.ui.updated_at',
                'replace'    => 'oro.magento.cartitem.updated_at.label'
            ],
            [
                'entityName' => 'Oro\Bundle\MagentoBundle\Entity\Customer',
                'field'      => 'createdAt',
                'value'      => 'oro.ui.created_at',
                'replace'    => 'oro.magento.customer.created_at.label'
            ],
            [
                'entityName' => 'Oro\Bundle\MagentoBundle\Entity\Customer',
                'field'      => 'updatedAt',
                'value'      => 'oro.ui.updated_at',
                'replace'    => 'oro.magento.customer.updated_at.label'
            ],
            [
                'entityName' => 'Oro\Bundle\MagentoBundle\Entity\Order',
                'field'      => 'createdAt',
                'value'      => 'oro.ui.created_at',
                'replace'    => 'oro.magento.order.created_at.label'
            ],
            [
                'entityName' => 'Oro\Bundle\MagentoBundle\Entity\Order',
                'field'      => 'updatedAt',
                'value'      => 'oro.ui.updated_at',
                'replace'    => 'oro.magento.order.updated_at.label'
            ],
            [
                'entityName' => 'Oro\Bundle\MagentoBundle\Entity\Product',
                'field'      => 'createdAt',
                'value'      => 'oro.ui.created_at',
                'replace'    => 'oro.magento.product.created_at.label'
            ],
            [
                'entityName' => 'Oro\Bundle\MagentoBundle\Entity\Product',
                'field'      => 'updatedAt',
                'value'      => 'oro.ui.updated_at',
                'replace'    => 'oro.magento.product.updated_at.label'
            ]
        ];
    }
}
