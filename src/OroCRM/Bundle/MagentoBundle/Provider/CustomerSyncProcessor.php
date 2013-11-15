<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\IntegrationBundle\Provider\SyncProcessorInterface;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class CustomerSyncProcessor implements SyncProcessorInterface
{
    const JOB_VALIDATE_IMPORT = 'entity_import_validation';
    const JOB_IMPORT  = 'entity_import';

    /** @var EntityManager */
    protected $em;

    /** @var ProcessorRegistry */
    protected $processorRegistry;

    /** @var JobExecutor */
    protected $jobExecutor;

    /**
     * @param EntityManager $em
     * @param ProcessorRegistry $processorRegistry
     * @param JobExecutor $jobExecutor
     */
    public function __construct(
        EntityManager $em,
        ProcessorRegistry $processorRegistry,
        JobExecutor $jobExecutor
    ) {
        $this->em = $em;
        $this->processorRegistry = $processorRegistry;
        $this->jobExecutor = $jobExecutor;
    }

    public function process($batchData)
    {
        $entityName = 'OroCRM\Bundle\MagentoBundle\Entity\Customer';

        $processorAliases = $this->processorRegistry->getProcessorAliasesByEntity(
            ProcessorRegistry::TYPE_IMPORT,
            $entityName
        );
        $processorAlias = reset($processorAliases);

        // TODO: decide if we need to use it
        $result = $this->processValidation($entityName, $processorAlias);
var_dump($result);
        $result = $this->processImport($entityName, $processorAlias);
var_dump($result);
die();
    }

    /** sample */
    public function indexAction($name)
    {
        /** @var $item ChannelTypeInterface */
        $channel = $this->getDoctrine()
            ->getRepository('OroCRMIntegrationBundle:ChannelType')->findOneBy(['name' => $name]);

        /** @var MageCustomerConnector $customerConnector */
        $customerConnector = $this->get('oro_integration.mage.customer_connector')
            ->setChannel($channel);

        $customerList = $customerConnector->getCustomersList();
        $customerData = $customerConnector->getCustomerData($customerList[0]->customer_id, true, true);

        return [
            'name' => $name,
            'customerData' => $customerData,
            'customerList' => $customerList,
        ];
    }

    /**
     * @param string $entityName
     * @param string $processorAlias
     * @return array
     */
    public function processImport($entityName, $processorAlias)
    {
        $configuration = array(
            'import' => array(
                'processorAlias' => $processorAlias,
                'entityName' => $entityName,
            ),
        );

        $jobResult = $this->jobExecutor->executeJob(
            ProcessorRegistry::TYPE_IMPORT,
            self::JOB_IMPORT,
            $configuration
        );

        if ($jobResult->isSuccessful()) {
            $message = 'oro_importexport.import.import_success';
        } else {
            $message = 'oro_importexport.import.import_error';
        }

        return [
            'success' => $jobResult->isSuccessful(),
            'message' => $message,
            'exceptions' => $jobResult->getFailureExceptions(),
        ];
    }

    /**
     *
     * @param string $entityName
     * @param string $processorAlias
     * @return array
     */
    public function processValidation($entityName, $processorAlias)
    {
        $configuration = array(
            'import_validation' => array(
                'processorAlias' => $processorAlias,
                'entityName' => $entityName,
            ),
        );

        $jobResult = $this->jobExecutor->executeJob(
            ProcessorRegistry::TYPE_IMPORT_VALIDATION,
            self::JOB_VALIDATE_IMPORT,
            $configuration
        );

        /** @var ContextInterface $contexts */
        $context = $jobResult->getContext();

        $counts = array();
        $counts['errors'] = count($jobResult->getFailureExceptions());
        if ($context) {
            $counts['process'] = 0;
            $counts['read'] = $context->getReadCount();
            $counts['process'] += $counts['add'] = $context->getAddCount();
            $counts['process'] += $counts['replace'] = $context->getReplaceCount();
            $counts['process'] += $counts['update'] = $context->getUpdateCount();
            $counts['process'] += $counts['delete'] = $context->getDeleteCount();
            $counts['process'] -= $counts['error_entries'] = $context->getErrorEntriesCount();
            $counts['errors'] += count($context->getErrors());
        }

        $errorsAndExceptions = [];
        if (!empty($counts['errors'])) {
            $errorsAndExceptions = array_slice(
                array_merge(
                    $jobResult->getFailureExceptions(),
                    $context ? $context->getErrors() : []
                ),
                0,
                100
            );
        }

        return array(
            'isSuccessful' => $jobResult->isSuccessful() && isset($counts['process']) && $counts['process'] > 0,
            'processorAlias' => $processorAlias,
            'counts' => $counts,
            'errors' => $errorsAndExceptions,
            'entityName' => $entityName,
        );
    }

    /**
     * @param $batchData
     * @return bool
     */
    public function processOld($batchData)
    {
        foreach ($batchData as $item) {
            $this->em->persist($this->createCustomer($item));
        }

        $this->em->flush();

        return true;
    }

    /**
     * @param $customerData
     *
     * @return Customer
     */
    protected function createCustomer($customerData)
    {
        $customer = new Customer();
        $customer->fillFromArray($customerData);

        return $customer;
    }
}
