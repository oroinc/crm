<?php

namespace OroCRM\Bundle\ChannelBundle\Provider;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\HttpFoundation\Request;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Model\ChannelAwareInterface;

class ChannelFromRequest
{
    /** @var ObjectManager */
    protected $manager;

    /**
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return bool
     */
    public function isDataChannelSet()
    {
        return !empty($this->channel);
    }

    /**
     * @param Request               $request
     * @param ChannelAwareInterface $entity
     */
    public function setDataChannel(Request $request, $entity)
    {
        $channel = $this->getChannel($request);

        if ($entity instanceof ChannelAwareInterface and !empty($channel)) {
            $entity->setDataChannel($channel);
        }
    }

    /**
     * @param Request $request
     *
     * @return Channel|bool
     */
    protected function getChannel(Request $request)
    {
        $channelId = $request->query->get('channelIds');

        if (!empty($channelId)) {
            return $this->manager->getRepository('OroCRMChannelBundle:Channel')
                ->findOneById($request->query->get('channelIds'));
        }

        return false;
    }
}
