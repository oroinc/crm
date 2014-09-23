<?php

namespace OroCRM\Bundle\ChannelBundle\Command;

use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManager;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        $this->setName('oro:cron:lifetime-avarage:aggregate');
        $this->addOption(
            'regenerate',
            'r',
            InputOption::VALUE_NONE,
            'This option allows to regenerate all history in orocrm_channel_dated_lifetime'
        );
        $this->setDescription('Update lifetime average value in history table');
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->getDescription());

        $regenerate = $input->getOption('regenerate');
        $today      = new \DateTime('now');

        if (true == $regenerate) {
            $this->truncateTable('orocrm_channel_dated_lifetime');
        }

        $averageAmount = $this->getEm()->getRepository('OroCRMChannelBundle:LifetimeValueHistory')->getAverageAmount();

        foreach ($averageAmount as $row) {
            $dataChannelId = $row['dataChannel'];
            $averageAmount = $row['avgAmount'];

            if (empty($dataChannelId) && empty($averageAmount)) {
                continue;
            }

            if (empty($this->dataChannels[$dataChannelId])) {
                $this->dataChannels[$dataChannelId] = $this->getDataChannelReference($dataChannelId);
            }

            if (true == $regenerate) {
                $this->updateDatedLifetime($dataChannelId);
                $output->writeln(sprintf('Data for chart were regenerated'));
                continue;
            }

            $output->writeln(
                sprintf(
                    'Update or create row in DatedLifetimeValue for channel %s, with average amount %s',
                    $dataChannelId,
                    $averageAmount
                )
            );

            $entity = $this->updateOrCreateDatedLifetimeValue($dataChannelId, $averageAmount, $today);

            $this->getEm()->persist($entity);
        }

        $this->getEm()->flush();

        $output->writeln('Completed');
    }

    /**
     * @param string $table
     */
    protected function truncateTable($table)
    {
        $connection = $this->getEm()->getConnection();
        $platform   = $connection->getDatabasePlatform();
        $connection->executeUpdate($platform->getTruncateTableSQL($table, true));
    }

    /**
     * @param string $dataChannelId
     *
     * @return object
     */
    protected function getDataChannelReference($dataChannelId)
    {
        return $this->getEm()->getReference('OroCRMChannelBundle:Channel', $dataChannelId);
    }

    /**
     * @param string    $dataChannelId
     * @param string    $avgAmount
     * @param \DateTime $date
     *
     * @return null|object
     */
    protected function updateOrCreateDatedLifetimeValue($dataChannelId, $avgAmount, \DateTime $date)
    {
        $entity = $this->getEm()->getRepository('OroCRMChannelBundle:DatedLifetimeValue')->findOneBy(
            [
                'dataChannel' => $dataChannelId,
                'month'       => $date->format('m'),
                'year'        => $date->format('Y')
            ]
        );

        if (!empty($entity) && $avgAmount !== $entity->getAmount()) {
            $entity->setAmount($avgAmount);
        } else {
            $entity = $this->createDatedLifetimeValue($dataChannelId, $avgAmount, $date);
        }

        return $entity;
    }

    /**
     * @param string    $dataChannelId
     * @param string    $avgAmount
     * @param \DateTime $date
     *
     * @return DatedLifetimeValue
     */
    protected function createDatedLifetimeValue($dataChannelId, $avgAmount, \DateTime $date = null)
    {
        $entity = new DatedLifetimeValue();

        if (!$date instanceof \DateTime) {
            $dateTimeFormatter = $this->getService('oro_locale.formatter.date_time');
            $date              = new \DateTime($dateTimeFormatter->format(new \DateTime('now')));
        }

        $entity->setDataChannel($this->dataChannels[$dataChannelId]);
        $entity->setAmount($avgAmount);
        $entity->setCreatedAt($date);

        return $entity;
    }

    /**
     * @param string $dataChannelId
     *
     * @return int
     */
    protected function updateDatedLifetime($dataChannelId)
    {
        $dates = $this->getLifetimePeriod();

        $period = new \DatePeriod(
            new \DateTime($dates['minDate']),
            new \DateInterval('P1M'),
            new \DateTime($dates['maxDate'])
        );

        $result = [];

        foreach ($period as $date) {
            /** @var \DateTime $date */
            $month = (int)$date->format('m');
            $year  = (int)$date->format('Y');

            if (empty($result[$year])) {
                $result[$year] = [];
            }

            array_push($result[$year], $month);

            $monthAverage = $this->getAverageByMonth($result);
            $entity       = $this->createDatedLifetimeValue(
                $dataChannelId,
                $monthAverage['amount'],
                $monthAverage['date']
            );

            $this->getEm()->persist($entity);
        }

        return 1;
    }

    /**
     * @return mixed
     */
    protected function getLifetimePeriod()
    {
        return $this->getEm()->getRepository('OroCRMChannelBundle:LifetimeValueHistory')->getMaxAndMinDate();
    }

    /**
     * @param array $date
     *
     * @return array
     */
    protected function getAverageByMonth(array $date)
    {
        $sql = 'SELECT avg(h.amount) as amount ' .
            'FROM orocrm_channel_lifetime_hist h ' .
            'JOIN ( ' .
            'SELECT MAX(h1.`id`) as id ' .
            'FROM orocrm_channel_lifetime_hist h1 ' .
            'WHERE ';

        $conditionCount = count($date);
        $lastMonth      = [];

        foreach ($date as $year => $month) {
            if ($conditionCount >= 1) {
                $sql .= '(EXTRACT(month from h1.created_at) in (' . implode(',', $month) . ')' .
                    'AND EXTRACT(year from h1.created_at) in (' . $year . ')) ';
                $conditionCount = 0;
            } else {
                $sql .= 'OR (EXTRACT(month from h1.created_at) in (' . implode(',', $month) . ')' .
                    'AND EXTRACT(year from h1.created_at) in (' . $year . ') ';
            }
            $lastMonth = [end($month), $year];
        }

        $sql .= 'group by h1.account_id ';
        $sql .= ') test2 ON test2.id = h.id';

        /** @var Statement $statement */
        $statement = $this->getEm()->getConnection()->prepare($sql);
        $status    = $statement->execute();
        $result    = [];

        if ($status) {
            $response = $statement->fetchAll();
            foreach ($response as $row) {
                $result['amount'] = $row['amount'];
                $result['date']   = new \DateTime(sprintf('%s-%s-01', $lastMonth[1], $lastMonth[0]));
            }
        }

        return $result;
    }

    /**
     * @return EntityManager object
     */
    protected function getEm()
    {
        return $this->getService('doctrine')->getManager();
    }

    /**
     * @param string $id
     *
     * @return object
     */
    protected function getService($id)
    {
        return $this->getContainer()->get($id);
    }
}
