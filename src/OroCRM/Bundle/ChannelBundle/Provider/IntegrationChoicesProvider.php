<?php

namespace OroCRM\Bundle\ChannelBundle\Provider;

use Doctrine\ORM\EntityManager;

class IntegrationChoicesProvider
{
    /** @var EntityManager */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return array
     */
    public function getChoices()
    {
        $entities = $this->em->getRepository('OroIntegrationBundle:Channel')->findAll();
        $result   = [];

        foreach ($entities as $entity) {
            $result[$entity->getId()] = $entity->getName();
        }

        return $result;
    }
}
