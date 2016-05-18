<?php

namespace OroCRM\Bundle\ChannelBundle\EventListener;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\IntegrationBundle\Utils\EditModeUtils;
use OroCRM\Bundle\ChannelBundle\Event\ChannelChangeStatusEvent;
use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class ChangeIntegrationStatusListener
{
    /** @var RegistryInterface */
    protected $registry;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param ChannelChangeStatusEvent $event
     */
    public function onChannelStatusChange(ChannelChangeStatusEvent $event)
    {
        /** @var Channel $channel */
        $channel    = $event->getChannel();
        $dataSource = $channel->getDataSource();

        if ($dataSource instanceof Integration) {
            if (Channel::STATUS_ACTIVE === $channel->getStatus()) {
                $enabled = null !== $dataSource->getPreviouslyEnabled() ?
                    $dataSource->getPreviouslyEnabled() :
                    true;

                $dataSource->setEnabled($enabled);
                EditModeUtils::attemptChangeEditMode($dataSource, Integration::EDIT_MODE_RESTRICTED);
            } else {
                $dataSource->setPreviouslyEnabled($dataSource->isEnabled());
                $dataSource->setEnabled(false);
                EditModeUtils::attemptChangeEditMode($dataSource, Integration::EDIT_MODE_DISALLOW);
            }

            $this->getManager()->persist($dataSource);
            $this->getManager()->flush();
        }
    }

    /**
     * @return EntityManager
     */
    protected function getManager()
    {
        return $this->registry->getManager();
    }
}
