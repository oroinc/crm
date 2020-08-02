<?php
declare(strict_types=1);

namespace Oro\Bundle\SalesBundle\Migration;

use Oro\Bundle\EntityConfigBundle\Migration\RemoveAssociationQuery;
use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;
use Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtension;

/**
 * Removes visit event association from an entity and updates entity config.
 */
class RemoveCustomerAssociationQuery extends RemoveAssociationQuery
{
    public function __construct(string $targetEntityClass, string $targetTableName, bool $dropRelationColumnsAndTables)
    {
        $this->sourceEntityClass = Customer::class;
        $this->targetEntityClass = $targetEntityClass;
        $this->associationKind = CustomerScope::ASSOCIATION_KIND;
        $this->relationType = RelationType::MANY_TO_ONE;
        $this->sourceTableName = CustomerExtension::CUSTOMER_TABLE_NAME;
        $this->dropRelationColumnsAndTables = $dropRelationColumnsAndTables;
        $this->targetTableName = $targetTableName;
    }
}
