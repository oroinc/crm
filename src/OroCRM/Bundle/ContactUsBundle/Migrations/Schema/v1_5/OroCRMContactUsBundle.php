<?php

namespace OroCRM\Bundle\ContactUsBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

use Oro\Bundle\EntityBundle\Migrations\Extension\ChangeTypeExtension;
use Oro\Bundle\EntityBundle\Migrations\Extension\ChangeTypeExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMContactUsBundle implements Migration, ChangeTypeExtensionAwareInterface
{
    /**
     * @var ChangeTypeExtension
     */
    protected $changeTypeExtension;

    /**
     * {@inheritdoc}
     */
    public function setChangeTypeExtension(ChangeTypeExtension $changeTypeExtension)
    {
        $this->changeTypeExtension = $changeTypeExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->changeTypeExtension->changePrimaryKeyType(
            $schema,
            $queries,
            'orocrm_contactus_contact_rsn',
            'id',
            Type::INTEGER
        );
        $this->changeTypeExtension->changePrimaryKeyType(
            $schema,
            $queries,
            'orocrm_contact_group',
            'id',
            Type::INTEGER
        );
    }
}
