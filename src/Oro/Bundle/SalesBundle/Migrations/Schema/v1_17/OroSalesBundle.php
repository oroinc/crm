<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_17;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\EntityConfig\ActivityScope;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareTrait;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendNameGeneratorAwareTrait;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MigrationBundle\Migration\Extension\NameGeneratorAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroSalesBundle implements Migration, ActivityExtensionAwareInterface, NameGeneratorAwareInterface
{
    use ActivityExtensionAwareTrait;
    use ExtendNameGeneratorAwareTrait;

    #[\Override]
    public function up(Schema $schema, QueryBag $queries)
    {
        $relationTableName = $this->nameGenerator->generateManyToManyJoinTableName(
            'Oro\Bundle\EmailBundle\Entity\Email',
            ExtendHelper::buildAssociationName(
                'Oro\Bundle\SalesBundle\Entity\B2bCustomer',
                ActivityScope::ASSOCIATION_KIND
            ),
            'Oro\Bundle\SalesBundle\Entity\B2bCustomer'
        );
        if (!$schema->hasTable($relationTableName)) {
            $this->activityExtension->addActivityAssociation($schema, 'oro_email', 'orocrm_sales_b2bcustomer');
        }
    }
}
