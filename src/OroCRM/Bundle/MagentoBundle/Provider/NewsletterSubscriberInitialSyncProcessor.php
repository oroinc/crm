<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

use OroCRM\Bundle\MagentoBundle\Provider\Connector\InitialNewsletterSubscriberConnector;

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
        $parameters['initial_id'] = $this->getSyncedToId($integration);

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
     * @param Integration $integration
     *
     * @return int
     */
    protected function getSyncedToId(Integration $integration)
    {
        if (!$this->subscriberClassName) {
            throw new \InvalidArgumentException('NewsletterSubscriber class name is missing');
        }

        // Run initial sync from starting point even if some subscribers came from deltas.
        $hasStarted = $this->getLastStatusForConnector($integration, InitialNewsletterSubscriberConnector::TYPE);
        if (!$hasStarted) {
            return null;
        }

        /** @var EntityRepository $repository */
        $repository = $this->doctrineRegistry->getRepository($this->subscriberClassName);
        // API return newsletter subscribers sorted by DESC, this means that latest subscriber will have minimal id
        $qb = $repository->createQueryBuilder('e');
        $qb
            ->select('MIN(e.id)')
            ->where($qb->expr()->eq('e.channel', ':channel'))
            ->setParameter('channel', $integration);

        return $qb->getQuery()->getSingleScalarResult();
    }
}
