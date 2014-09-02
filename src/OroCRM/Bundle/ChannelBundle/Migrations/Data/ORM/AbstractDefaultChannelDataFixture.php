<?php

namespace OroCRM\Bundle\ChannelBundle\Migrations\Data\ORM;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Oro\Bundle\BatchBundle\ORM\Query\QueryCountCalculator;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Model\ChannelAwareInterface;

abstract class AbstractDefaultChannelDataFixture extends AbstractFixture implements
    ContainerAwareInterface,
    DependentFixtureInterface
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
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return ['Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData'];
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
        $interfaces = class_implements($entity) ?: [];
        if (!in_array('OroCRM\\Bundle\\ChannelBundle\\Model\\ChannelAwareInterface', $interfaces)) {
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
                    $channelReference = $this->em->getReference(ClassUtils::getClass($channel), $channel->getId());
                    $data->setDataChannel($channelReference);
                    $toWrite[] = $data;
                }

                if (0 === $writeCount % static::BATCH_SIZE) {
                    $this->write($toWrite);

                    $toWrite = [];
                }
            }

            if (count($toWrite) > 0) {
                $this->write($toWrite);
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
        foreach ($items as $item) {
            $this->em->detach($item);
        }
    }
}
