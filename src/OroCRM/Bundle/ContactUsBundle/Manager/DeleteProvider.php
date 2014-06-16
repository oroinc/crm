<?php

namespace OroCRM\Bundle\ContactUsBundle\Manager;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Manager\DeleteProviderInterface;

class DeleteProvider implements DeleteProviderInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($channelType)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRelatedData(Channel $channel)
    {
        // delete contact requests
        $this->em->createQuery(
            'DELETE FROM OroCRMContactUsBundle:ContactRequest e WHERE e.channel = ' . $channel->getId()
        )->execute();
    }
}
