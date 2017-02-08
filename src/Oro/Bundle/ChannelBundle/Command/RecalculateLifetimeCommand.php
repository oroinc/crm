<?php

namespace Oro\Bundle\ChannelBundle\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedIdentityQueryResultIterator;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIteratorInterface;

abstract class RecalculateLifetimeCommand extends ContainerAwareCommand
{
    const READ_BATCH_SIZE = 1000;
    const WRITE_BATCH_SIZE = 200;
    const STATUS_UPDATE_BATCH_SIZE = 50;

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'Causes the re-calculated lifetime values to be physically saved into the database.'
            );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $channelSettings = $this->getChannelSettings($this->getChannelType());
        if (false === $channelSettings) {
            $output->writeln(sprintf('The "%s" channel does not exist.', $this->getChannelType()));

            return;
        }

        if (true === $input->getOption('force')) {
            $this->recalculateLifetimeValues($input, $output, $this->getChannelType(), $channelSettings);
        } else {
            $output->writeln('');
            $output->writeln(
                'Please run the command with <info>--force</info> option to save '
                . 're-calculated lifetime values into the database.'
            );
        }
    }

    /**
     * @return string
     */
    abstract protected function getChannelType();

    /**
     * @param EntityManager $em
     * @param object        $customer
     *
     * @return float
     */
    abstract protected function calculateCustomerLifetime(EntityManager $em, $customer);

    /**
     * @param string $channelType
     *
     * @return array|bool
     */
    protected function getChannelSettings($channelType)
    {
        $settingsProvider = $this->getContainer()->get('oro_channel.provider.settings_provider');
        $settings         = $settingsProvider->getLifetimeValueSettings();

        return isset($settings[$channelType])
            ? $settings[$channelType]
            : false;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $channelType
     * @param array           $channelSettings
     */
    protected function recalculateLifetimeValues(
        InputInterface $input,
        OutputInterface $output,
        $channelType,
        array $channelSettings
    ) {
        $customerClass = $this->getCustomerClass($channelSettings);
        $lifetimeField = $this->getLifetimeField($channelSettings);

        /** @var EntityManager $em */
        $em                    = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $customersQueryBuilder = $this->getCustomersQueryBuilder($em, $customerClass, $channelType);
        $customerDataIterator  = $this->getCustomersIterator($customersQueryBuilder);
        if (0 === $customerDataIterator->count()) {
            $output->writeln(sprintf(' The "%s" channel has no customers.', $this->getChannelType()));

            return;
        }

        $output->writeln(
            sprintf(' Found %d customer(s) for "%s" channel.', $customerDataIterator->count(), $this->getChannelType())
        );
        $this->startProcess($input, $output, $customerDataIterator->count());

        $customerRepo     = $em->getRepository($customerClass);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $unsavedItemCount = 0;
        foreach ($customerDataIterator as $customerRow) {
            $unsavedItemCount++;

            $customer              = $customerRepo->find($customerRow['customer_id']);
            $customerLifetimeValue = $this->calculateCustomerLifetime($em, $customer);
            $propertyAccessor->setValue($customer, $lifetimeField, $customerLifetimeValue);

            if ($unsavedItemCount >= static::WRITE_BATCH_SIZE) {
                $em->flush();
                $em->clear();
                $this->advanceProcess($input, $unsavedItemCount);
                $unsavedItemCount = 0;
            }
        }
        if ($unsavedItemCount > 0) {
            $em->flush();
            $em->clear();
            $this->advanceProcess($input, $unsavedItemCount);
        }

        $this->finishProcess($input);
    }

    /**
     * @param EntityManager $em
     * @param string        $customerClass
     * @param string        $channelType
     *
     * @return QueryBuilder
     */
    protected function getCustomersQueryBuilder(EntityManager $em, $customerClass, $channelType)
    {
        $customerIdField = $em->getClassMetadata($customerClass)->getSingleIdentifierFieldName();

        return $em->getRepository($customerClass)->createQueryBuilder('customer')
            ->select(sprintf('customer.%s as customer_id', $customerIdField))
            ->innerJoin('customer.dataChannel', 'channel')
            ->where('channel.channelType = :channelType')
            ->setParameter('channelType', $channelType);
    }

    /**
     * @param QueryBuilder $customersQueryBuilder
     *
     * @return BufferedQueryResultIteratorInterface
     */
    protected function getCustomersIterator(QueryBuilder $customersQueryBuilder)
    {
        $iterator = new BufferedIdentityQueryResultIterator($customersQueryBuilder);
        $iterator->setBufferSize(static::READ_BATCH_SIZE);

        return $iterator;
    }

    /**
     * Starts the progress output.
     *
     * @param InputInterface  $input  An Input instance
     * @param OutputInterface $output An Output instance
     * @param int|null        $max    Maximum steps
     */
    protected function startProcess(InputInterface $input, OutputInterface $output, $max)
    {
        if ($input->isInteractive()) {
            /** @var ProgressHelper $progress */
            $progress = $this->getHelper('progress');
            $progress->setFormat(' [%bar%] %percent%%');
            $progress->start($output, $max);
            $progress->display();
        }
    }

    /**
     * Advances the progress output X steps.
     *
     * @param InputInterface $input An Input instance
     * @param int            $step  Number of steps to advance
     */
    protected function advanceProcess(InputInterface $input, $step)
    {
        if ($input->isInteractive()) {
            /** @var ProgressHelper $progress */
            $progress = $this->getHelper('progress');
            $progress->advance($step);
        }
    }

    /**
     * Finishes the progress output.
     *
     * @param InputInterface $input An Input instance
     */
    protected function finishProcess(InputInterface $input)
    {
        if ($input->isInteractive()) {
            /** @var ProgressHelper $progress */
            $progress = $this->getHelper('progress');
            $progress->finish();
        }
    }

    /**
     * @param array $channelSettings
     *
     * @return mixed
     */
    protected function getCustomerClass($channelSettings)
    {
        return $channelSettings['entity'];
    }

    /**
     * @param array $channelSettings
     *
     * @return mixed
     */
    protected function getLifetimeField($channelSettings)
    {
        return $channelSettings['field'];
    }
}
