<?php

namespace OroCRM\Bundle\AccountBundle\Migrations\Schema\v1_12;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\AttachmentBundle\Entity\Attachment;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\EntityMergeBundle\Model\MergeModes;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

use OroCRM\Bundle\AccountBundle\Entity\Account;

class UpdateEntityMergeOptions implements Migration, ExtendExtensionAwareInterface
{
    /** @var ExtendExtension */
    protected $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(
            new UpdateEntityConfigFieldValueQuery(
                Account::class,
                'extend_description',
                'merge',
                'autoescape',
                false
            )
        );
    }
}
