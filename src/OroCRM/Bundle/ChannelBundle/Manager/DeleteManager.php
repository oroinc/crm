<?php

namespace OroCRM\Bundle\ChannelBundle\Manager;

use Doctrine\ORM\EntityManager;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class DeleteManager
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
     * Delete integration
     *
     * @param Channel $channel
     *
     * @return bool
     */
    public function delete(Channel $channel)
    {
        $this->em->remove($channel);
        $this->em->flush();
        return true;
    }
}
