<?php

namespace Oro\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\EmailBundle\Async\Topic\UpdateVisibilitiesTopic;
use Oro\Component\DependencyInjection\ContainerAwareInterface;
use Oro\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Schedule the update of visibilities for emails and email addresses after Lead entity was markes as Public.
 */
class UpdateEmailVisibilities extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    #[\Override]
    public function load(ObjectManager $manager)
    {
        if (!$this->container->get(ApplicationState::class)->isInstalled()) {
            return;
        }

        $this->container->get('oro_message_queue.message_producer')->send(UpdateVisibilitiesTopic::getName(), []);
    }
}
