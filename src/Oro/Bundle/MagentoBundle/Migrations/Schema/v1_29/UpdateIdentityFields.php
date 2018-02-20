<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_29;

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
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\Address', 'country');
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\Address', 'region');
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\Customer', 'email');
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\CustomerGroup', 'name');
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\Store', 'code');
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\Website', 'code');
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
