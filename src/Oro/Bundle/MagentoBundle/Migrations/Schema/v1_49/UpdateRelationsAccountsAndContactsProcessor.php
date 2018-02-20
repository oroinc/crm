<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_49;

use Doctrine\DBAL\Connection;
use Oro\Bundle\EntityBundle\ORM\NativeQueryExecutorHelper;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;

class UpdateRelationsAccountsAndContactsProcessor implements MessageProcessorInterface
{
    /**
     *  The topic name declared at processor because this is disposable topic that will be used
     *  only during the system update.
     */
    const TOPIC_NAME = 'oro_magento.upgrade_relations_accounts_contacts';
    const BATCH_SIZE = 200;

    /** @var MessageProducerInterface */
    protected $messageProducer;

    /** @var NativeQueryExecutorHelper */
    protected $queryHelper;

    /**
     * @param MessageProducerInterface  $messageProducer
     * @param NativeQueryExecutorHelper $queryHelper
     */
    public function __construct(
        MessageProducerInterface $messageProducer,
        NativeQueryExecutorHelper $queryHelper
    ) {
        $this->messageProducer = $messageProducer;
        $this->queryHelper = $queryHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        if ($message->getBody() !== '') {
            // we have page number we should process, so now process this page
            $body = json_decode($message->getBody(), true);
            $this->processBatch((int)$body['batch_number']);
        } else {
            // we have no page number we should process, so now split work to batches
            $this->scheduleMigrateProcesses();
        }

        return self::ACK;
    }

    /**
     * Split work to batches
     */
    protected function scheduleMigrateProcesses()
    {
        /** @var Connection $connection */
        $connection = $this->queryHelper->getManager(Customer::class)->getConnection();
        $maxItemNumber = $connection
            ->fetchColumn(
                sprintf(
                    'select max(id) from %s',
                    $this->queryHelper->getTableName(Customer::class)
                )
            );
        $jobsCount = floor((int)$maxItemNumber / self::BATCH_SIZE);
        for ($i = 0; $i <= $jobsCount; $i++) {
            $this->messageProducer->send(self::TOPIC_NAME, json_encode(['batch_number' => $i]));
        }
    }

    /**
     * Process one data batch
     *
     * @param integer $pageNumber
     */
    protected function processBatch($pageNumber)
    {
        $startId = self::BATCH_SIZE * $pageNumber;
        $endId = $startId + self::BATCH_SIZE - 1;

        /** @var Connection $connection */
        $em = $this->queryHelper->getManager(Customer::class);

        // we should use ORM because of logic in Account's setDefaultContact method
        $customers = $em->getRepository(Customer::class)->createQueryBuilder('customer')
            ->select('customer', 'account', 'contact')
            ->join('customer.account', 'account')
            ->join('customer.contact', 'contact')
            ->where('customer.id BETWEEN :startId AND :endID')
            ->andWhere('customer.contact is not null')
            ->andWhere('customer.account is not null')
            ->andWhere('contact.email = customer.email')
            ->setParameter('startId', $startId)
            ->setParameter('endID', $endId)
            ->getQuery()
            ->getResult();

        if (empty($customers)) {
            return;
        }

        /** @var Customer $customer */
        foreach ($customers as $customer) {
            $account = $customer->getAccount();
            $contact = $customer->getContact();

            if (!$account->getContacts()->contains($contact)) {
                $account->addContact($contact);
                if (!$account->getDefaultContact()) {
                    $account->setDefaultContact($contact);
                }
            }
        }

        $em->flush();
    }
}
