<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport;


use Doctrine\ORM\EntityManager;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

class RemoveChannelManager
{
    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getSupportedChannelType()
    {
        return 'magento';
    }

    public function processDelete(Channel $channel)
    {
        if ($channel->getType() !== 'magento') {
            throw new \Exception(sprintf('Channel with id %s is not Magento channel', $channel->getId()));
        }


        $this->em->remove($channel);

    }
}
