<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

class NewsletterSubscriberInitialSyncProcessor extends AbstractInitialProcessor
{
    /**
     * @var string
     */
    protected $subscriberClassName;

    /**
     * {@inheritdoc}
     */
    public function process(Integration $integration, $connector = null, array $parameters = [])
    {
        $parameters['initial_id'] = $this->getSyncedToId();

        return parent::process($integration, $connector, $parameters);
    }

    /**
     * @param string $subscriberClassName
     * @return NewsletterSubscriberInitialSyncProcessor
     */
    public function setSubscriberClassName($subscriberClassName)
    {
        $this->subscriberClassName = $subscriberClassName;

        return $this;
    }

    /**
     * @return int
     */
    protected function getSyncedToId()
    {
        /** @var EntityRepository $repository */
        $repository = $this->doctrineRegistry->getRepository($this->subscriberClassName);
        $qb = $repository->createQueryBuilder('e')->select('MIN(e.id)');

        return $qb->getQuery()->getSingleScalarResult();
    }
}
