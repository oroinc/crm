<?php

namespace OroCRM\Bundle\CaseBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use OroCRM\Bundle\CaseBundle\Migrations\Schema\v1_0\OroCRMCaseBundle;
use OroCRM\Bundle\CaseBundle\Migrations\Schema\v1_1\OroCRMCaseBundle as OroCRMCaseBundle11;
use OroCRM\Bundle\CaseBundle\Migrations\Schema\v1_2\OroCRMCaseBundle as OroCRMCaseBundle12;

class OroCRMCaseBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_2';
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
    }
}
