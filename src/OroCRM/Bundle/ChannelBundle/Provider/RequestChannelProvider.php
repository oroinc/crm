<?php

namespace OroCRM\Bundle\ChannelBundle\Provider;

use Doctrine\ORM\EntityManager;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Model\ChannelAwareInterface;

class RequestChannelProvider
{
    /** @var RegistryInterface */
    protected $registry;

    /** @var Request */
    protected $request;

    /**
     * @param Request           $request
     * @param RegistryInterface $registry
     */
    public function __construct(Request $request, RegistryInterface $registry)
    {
        $this->registry = $registry;
        $this->request  = $request;
    }

    /**
     * @param ChannelAwareInterface|mixed $entity
     */
    public function setDataChannel($entity)
    {
        $channel = $this->getChannelReference();

        if ($channel && $entity instanceof ChannelAwareInterface) {
            $entity->setDataChannel($channel);
        }
    }

    /**
     * @return Channel|bool
     */
    protected function getChannelReference()
    {
        $channelId = $this->request->query->get('channelId');

        if (!empty($channelId)) {
            /** @var EntityManager $em */
            $em = $this->registry->getManager();

            return $em->getReference('OroCRMChannelBundle:Channel', $channelId);
        }

        return false;
    }
}
