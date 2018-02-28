<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_31;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class UpdateIdentityFields implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\CartAddress', 'street');
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\CartAddress', 'city');
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\CartAddress', 'postalCode');
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\CartAddress', 'country');
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\CartAddress', 'region');

        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\OrderAddress', 'country');
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\OrderAddress', 'region');
    }

    /**
     * @param QueryBag $queries
     * @param string $class
     * @param string $field
     */
    protected function removeIdentity(QueryBag $queries, $class, $field)
    {
        $queries->addQuery(new UpdateEntityConfigFieldValueQuery($class, $field, 'importexport', 'identity', false));
    }
}
