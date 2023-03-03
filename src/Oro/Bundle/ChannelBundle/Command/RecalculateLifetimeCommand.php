<?php
declare(strict_types=1);

namespace Oro\Bundle\ChannelBundle\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedIdentityQueryResultIterator;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIteratorInterface;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract class for recalculate commands.
 */
abstract class RecalculateLifetimeCommand extends Command
{
    public const READ_BATCH_SIZE = 1000;
    public const WRITE_BATCH_SIZE = 200;
    public const STATUS_UPDATE_BATCH_SIZE = 50;

    protected ManagerRegistry $registry;
    protected SettingsProvider $settingsProvider;
    protected ProgressBar $progressBar;

    public function __construct(ManagerRegistry $registry, SettingsProvider $settingsProvider)
    {
        parent::__construct();

        $this->registry = $registry;
        $this->settingsProvider = $settingsProvider;
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    public function configure()
    {
        $this
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'Saves the recalculated lifetime values to the database.'
            );
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->progressBar = new ProgressBar($output);
        $channelSettings = $this->getChannelSettings($this->getChannelType());
        if (false === $channelSettings) {
            $output->writeln(sprintf('The "%s" channel does not exist.', $this->getChannelType()));

            return 1;
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

        return 0;
    }

    abstract protected function getChannelType(): string;

    abstract protected function calculateCustomerLifetime(EntityManager $em, object $customer): float;

    /**
     * @return array|bool
     */
    protected function getChannelSettings(string $channelType)
    {
        $settingsProvider = $this->settingsProvider;
        $settings         = $settingsProvider->getLifetimeValueSettings();

        return isset($settings[$channelType])
            ? $settings[$channelType]
            : false;
    }

    protected function recalculateLifetimeValues(
        InputInterface $input,
        OutputInterface $output,
        string $channelType,
        array $channelSettings
    ): void {
        $customerClass = $this->getCustomerClass($channelSettings);
        $lifetimeField = $this->getLifetimeField($channelSettings);

        /** @var EntityManager $em */
        $em                    = $this->registry->getManagerForClass($customerClass);
        $customersQueryBuilder = $this->getCustomersQueryBuilder($em, $customerClass, $channelType);
        $customerDataIterator  = $this->getCustomersIterator($customersQueryBuilder);
        if (0 === $customerDataIterator->count()) {
            $output->writeln(sprintf(' The "%s" channel has no customers.', $this->getChannelType()));

            return;
        }

        $output->writeln(
            sprintf(' Found %d customer(s) for "%s" channel.', $customerDataIterator->count(), $this->getChannelType())
        );
        $this->startProcess($input, $customerDataIterator->count());

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
        $output->writeln(''); // Adding a new line after progress bar
    }

    protected function getCustomersQueryBuilder(
        EntityManager $em,
        string $customerClass,
        string $channelType
    ): QueryBuilder {
        $customerIdField = $em->getClassMetadata($customerClass)->getSingleIdentifierFieldName();

        return $em->getRepository($customerClass)->createQueryBuilder('customer')
            ->select(sprintf('customer.%s as customer_id', $customerIdField))
            ->innerJoin('customer.dataChannel', 'channel')
            ->where('channel.channelType = :channelType')
            ->setParameter('channelType', $channelType);
    }

    protected function getCustomersIterator(QueryBuilder $customersQueryBuilder): BufferedQueryResultIteratorInterface
    {
        $iterator = new BufferedIdentityQueryResultIterator($customersQueryBuilder);
        $iterator->setBufferSize(static::READ_BATCH_SIZE);

        return $iterator;
    }

    protected function startProcess(InputInterface $input, ?int $max): void
    {
        if ($input->isInteractive()) {
            $this->progressBar->start($max);
            $this->progressBar->setFormat(' [%bar%] %percent%%');
            $this->progressBar->display();
        }
    }

    protected function advanceProcess(InputInterface $input, int $step): void
    {
        if ($input->isInteractive()) {
            $this->progressBar->advance($step);
        }
    }

    protected function finishProcess(InputInterface $input): void
    {
        if ($input->isInteractive()) {
            $this->progressBar->finish();
        }
    }

    /**
     * @return mixed
     */
    protected function getCustomerClass(array $channelSettings)
    {
        return $channelSettings['entity'];
    }

    /**
     * @return mixed
     */
    protected function getLifetimeField(array $channelSettings)
    {
        return $channelSettings['field'];
    }
}
