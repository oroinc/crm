<?php

namespace OroCRM\Bundle\CaseBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtension;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

use OroCRM\Bundle\CaseBundle\Migrations\Schema\v1_0\OroCRMCaseBundle;
use OroCRM\Bundle\CaseBundle\Migrations\Schema\v1_1\OroCRMCaseBundle as OroCRMCaseBundle11;
use OroCRM\Bundle\CaseBundle\Migrations\Schema\v1_2\OroCRMCaseBundle as OroCRMCaseBundle12;
use OroCRM\Bundle\CaseBundle\Migrations\Schema\v1_3\OroCRMCaseBundle as OroCRMCaseBundle13;
use OroCRM\Bundle\CaseBundle\Migrations\Schema\v1_4\OroCRMCaseBundle as OroCRMCaseBundle14;
use OroCRM\Bundle\CaseBundle\Migrations\Schema\v1_5\OroCRMCaseBundle as OroCRMCaseBundle15;

class OroCRMCaseBundleInstaller implements
    Installation,
    AttachmentExtensionAwareInterface
{
    /** @var AttachmentExtension */
    protected $attachmentExtension;

    /**
     * {@inheritdoc}
     */
    public function setAttachmentExtension(AttachmentExtension $attachmentExtension)
    {
        $this->attachmentExtension = $attachmentExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_5';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $migration = new OroCRMCaseBundle();
        $migration->up($schema, $queries);

        $migration11 = new OroCRMCaseBundle11();
        $migration11->up($schema, $queries);

        OroCRMCaseBundle12::addOrganization($schema);

        $migration13 = new OroCRMCaseBundle13();
        $migration13->up($schema, $queries);

        $migration14 = new OroCRMCaseBundle14();
        $migration14->up($schema, $queries);

        OroCRMCaseBundle15::addOroEmailMailboxProcessorColumns($schema);
        OroCRMCaseBundle15::addOroEmailMailboxProcessorForeignKeys($schema);
    }
}
