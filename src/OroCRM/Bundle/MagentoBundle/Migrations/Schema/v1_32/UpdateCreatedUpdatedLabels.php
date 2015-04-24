<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_32;

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
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\Address',
                'field'      => 'created',
                'value'      => 'oro.ui.created_at',
                'replace'    => 'orocrm.magento.address.created.label'
            ],
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\Address',
                'field'      => 'updated',
                'value'      => 'oro.ui.updated_at',
                'replace'    => 'orocrm.magento.address.updated.label'
            ],
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\CartAddress',
                'field'      => 'updated',
                'value'      => 'oro.ui.updated_at',
                'replace'    => 'orocrm.magento.cartaddress.updated.label'
            ],
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\CartAddress',
                'field'      => 'created',
                'value'      => 'oro.ui.created_at',
                'replace'    => 'orocrm.magento.cartaddress.created.label'
            ],
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\Cart',
                'field'      => 'createdAt',
                'value'      => 'oro.ui.created_at',
                'replace'    => 'orocrm.magento.cart.created_at.label'
            ],
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\Cart',
                'field'      => 'updatedAt',
                'value'      => 'oro.ui.updated_at',
                'replace'    => 'orocrm.magento.cart.updated_at.label'
            ],
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\CartItem',
                'field'      => 'createdAt',
                'value'      => 'oro.ui.created_at',
                'replace'    => 'orocrm.magento.cartitem.created_at.label'
            ],
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\CartItem',
                'field'      => 'updatedAt',
                'value'      => 'oro.ui.updated_at',
                'replace'    => 'orocrm.magento.cartitem.updated_at.label'
            ],
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\Customer',
                'field'      => 'createdAt',
                'value'      => 'oro.ui.created_at',
                'replace'    => 'orocrm.magento.customer.created_at.label'
            ],
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\Customer',
                'field'      => 'updatedAt',
                'value'      => 'oro.ui.updated_at',
                'replace'    => 'orocrm.magento.customer.updated_at.label'
            ],
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\Order',
                'field'      => 'createdAt',
                'value'      => 'oro.ui.created_at',
                'replace'    => 'orocrm.magento.order.created_at.label'
            ],
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\Order',
                'field'      => 'updatedAt',
                'value'      => 'oro.ui.updated_at',
                'replace'    => 'orocrm.magento.order.updated_at.label'
            ],
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\Product',
                'field'      => 'createdAt',
                'value'      => 'oro.ui.created_at',
                'replace'    => 'orocrm.magento.product.created_at.label'
            ],
            [
                'entityName' => 'OroCRM\Bundle\MagentoBundle\Entity\Product',
                'field'      => 'updatedAt',
                'value'      => 'oro.ui.updated_at',
                'replace'    => 'orocrm.magento.product.updated_at.label'
            ]
        ];
    }
}
