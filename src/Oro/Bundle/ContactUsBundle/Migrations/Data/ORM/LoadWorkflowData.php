<?php
declare(strict_types=1);

namespace Oro\Bundle\ContactUsBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\MigrationBundle\Entity\DataFixture;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Activate orocrm_contact_us_contact_request workflow unless it was added to the system before by Magento 1 connector.
 */
class LoadWorkflowData extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        /** @noinspection ClassConstantCanBeUsedInspection */
        $alreadyLoadedDataFixture = $manager->getRepository(DataFixture::class)->findOneBy([
            'className' => 'Oro\Bundle\MagentoContactUsBundle\Migrations\Data\ORM\LoadWorkflowData'
        ]);
        if (null === $alreadyLoadedDataFixture) {
            $this->container->get('oro_workflow.manager.system')->activateWorkflow('orocrm_contact_us_contact_request');
        }
    }
}
