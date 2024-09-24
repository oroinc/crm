<?php

namespace Oro\Bundle\ContactUsBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\EntityBundle\Migrations\Extension\ChangeTypeExtensionAwareInterface;
use Oro\Bundle\EntityBundle\Migrations\Extension\ChangeTypeExtensionAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroContactUsBundle implements Migration, ChangeTypeExtensionAwareInterface
{
    use ChangeTypeExtensionAwareTrait;

    #[\Override]
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->changeTypeExtension->changePrimaryKeyType(
            $schema,
            $queries,
            'orocrm_contactus_contact_rsn',
            'id',
            Types::INTEGER
        );
        $this->changeTypeExtension->changePrimaryKeyType(
            $schema,
            $queries,
            'orocrm_contact_group',
            'id',
            Types::INTEGER
        );
    }
}
