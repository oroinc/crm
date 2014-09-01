<?php

namespace OroCRM\Bundle\ChannelBundle\Migrations\Data\ORM;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\BatchBundle\ORM\Query\QueryCountCalculator;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Model\ChannelAwareInterface;

abstract class AbstractDefaultChannelDataFixture extends AbstractFixture
{
    const BATCH_SIZE = 50;

    /** @var ContainerInterface */
    protected $container;

    /** @var EntityManager */
    protected $em;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->em        = $container->get('doctrine')->getManager();
    }

    /**
     * @param string $entity
     *
     * @return int
     */
    protected function getRowCount($entity)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->em->getRepository($entity)
            ->createQueryBuilder('e');

        return QueryCountCalculator::calculateCount($qb->getQuery());
    }

    /**
     * @param Channel $channel
     * @param string  $entity
     *
     * @throws \Exception
     */
    protected function fillChannelToEntity(Channel $channel, $entity)
    {
        if (!in_array('OroCRM\\Bundle\\ChannelBundle\\Model\\ChannelAwareInterface', class_implements($entity))) {
            return;
        }

        /** @var QueryBuilder $qb */
        $qb = $this->em->getRepository($entity)
            ->createQueryBuilder('e');

        $iterator   = new BufferedQueryResultIterator($qb);
        $writeCount = 0;
        $toWrite    = [];
        try {
            $this->em->beginTransaction();
            /** @var ChannelAwareInterface $data */
            foreach ($iterator as $data) {
                $writeCount++;

                if (!$data->getDataChannel()) {
                    $data->setDataChannel($channel);
                    $toWrite[] = $data;
                }

                if (0 === $writeCount % static::BATCH_SIZE) {
                    $this->write($this->em, $toWrite);

                    $toWrite = [];
                }
            }

            if (count($toWrite) > 0) {
                $this->write($this->em, $toWrite);
            }

            $this->em->commit();
        } catch (\Exception $exception) {
            $this->em->rollback();

            throw $exception;
        }
    }

    /**
     * Do persist into EntityManager
     *
     * @param array $items
     */
    private function write(array $items)
    {
        foreach ($items as $item) {
            $this->em->persist($item);
        }
        $this->em->flush();
        $this->em->clear();
    }
}
