<?php

namespace OroCRM\Bundle\ChannelBundle\Command;

use Doctrine\ORM\EntityManager;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;

use OroCRM\Bundle\ChannelBundle\Entity\DatedLifetimeValue;

class LifetimeOfDayUpdateCommand extends ContainerAwareCommand implements CronCommandInterface
{
    /** @var array */
    protected $dataChannels = [];

    /**
     * @inheritdoc
     */
    public function getDefaultDefinition()
    {
        return '00 4 * * *';
    }

    /**
     * @inheritdoc
     */
    public function configure()
    {
        $this->setName('oro:cron:lifetime-of-day:update');
        $this->setDescription('Update lifetime of day history table');
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->getDescription());

        /** @var EntityManager $entityManager */
        $em = $this->getService('doctrine.orm.entity_manager');

        $results = $this->getDataFromHistory($em);

        foreach ($results as $result) {
            $dataChannelId = $result['dataChannel'];
            $averageAmount = $result['avg_amount'];

            $output->writeln(
                sprintf(
                    'Create row in DatedLifetimeValue for channel %s, with average amount %s',
                    $dataChannelId,
                    $averageAmount
                )
            );

            if (empty($this->dataChannels[$dataChannelId])) {
                $this->dataChannels[$dataChannelId] = $this->getDataChannelReference($em, $dataChannelId);
            }

            $entity = $this->createDatedLifetimeValue($dataChannelId, $averageAmount);

            $em->persist($entity);
        }

        $em->flush();

        $output->writeln('Completed');
    }

    /**
     * @param EntityManager $em
     *
     * @return array
     */
    protected function getDataFromHistory(EntityManager $em)
    {
        $qb = $em->createQueryBuilder();

        $qb->from('OroCRMChannelBundle:LifetimeValueHistory', 'l');
        $qb->addselect('(l.dataChannel) as dataChannel');
        $qb->addSelect($qb->expr()->avg('l.amount') . ' as avg_amount');
        $qb->andWhere('l.status = 1');

        return $qb->getQuery()->getResult();
    }

    /**
     * Get service from DI container by id
     *
     * @param string $id
     *
     * @return object
     */
    protected function getService($id)
    {
        return $this->getContainer()->get($id);
    }

    /**
     * @param EntityManager $em
     * @param string        $id
     *
     * @return object
     */
    protected function getDataChannelReference(EntityManager $em, $id)
    {
        return $em->getReference('OroCRMChannelBundle:Channel', $id);
    }

    /**
     * @param string $dataChannelId
     * @param string $avgAmount
     *
     * @return DatedLifetimeValue
     */
    protected function createDatedLifetimeValue($dataChannelId, $avgAmount)
    {
        $entity = new DatedLifetimeValue();
        $entity->setDataChannel($this->dataChannels[$dataChannelId]);
        $entity->setAmount($avgAmount);

        return $entity;
    }
}
