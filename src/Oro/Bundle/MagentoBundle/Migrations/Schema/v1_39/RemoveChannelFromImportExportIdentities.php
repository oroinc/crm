<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_39;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class RemoveChannelFromImportExportIdentities implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\Product');
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\CartAddress');
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\Store');
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\Website');
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\NewsletterSubscriber');
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\CustomerGroup');
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\OrderAddress');
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\CartItem');
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\OrderItem');
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\Cart');
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\Customer');
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\Order');
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\Address');
    }

    /**
     * @param QueryBag $queries
     * @param string $class
     */
    protected function removeIdentity(QueryBag $queries, $class)
    {
        $queries->addQuery(new UpdateEntityConfigFieldValueQuery($class, 'channel', 'importexport', 'identity', false));
    }
}
