<?php
declare(strict_types=1);

namespace Oro\Bundle\ContactUsBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\DemoDataBundle\Migrations\Data\ORM\LoadRolesData;
use Oro\Bundle\MigrationBundle\Entity\DataFixture;
use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractLoadAclData;

/**
 * Loads orocrm_contact_us_contact_request workflow ACL data unless it has been already loaded by Magento 1 connector.
 */
class LoadWorkflowAclData extends AbstractLoadAclData
{
    public function getDependencies()
    {
        return [
            LoadRolesData::class,
            LoadWorkflowData::class
        ];
    }

    protected function getDataPath()
    {
        return '@OroContactUsBundle/Migrations/Data/ORM/data/workflows.yml';
    }

    public function load(ObjectManager $manager)
    {
        /** @noinspection ClassConstantCanBeUsedInspection */
        $alreadyLoadedDataFixture = $manager->getRepository(DataFixture::class)->findOneBy([
            'className' => 'Oro\Bundle\MagentoContactUsBundle\Migrations\Data\ORM\LoadWorkflowAclData'
        ]);
        if (!$alreadyLoadedDataFixture) {
            parent::load($manager);
        }
    }
}
