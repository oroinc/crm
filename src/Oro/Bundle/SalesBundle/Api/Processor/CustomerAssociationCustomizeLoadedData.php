<?php

namespace Oro\Bundle\SalesBundle\Api\Processor;

use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

use Oro\Bundle\ApiBundle\Processor\CustomizeLoadedData\CustomizeLoadedDataContext;
use Oro\Bundle\ApiBundle\Util\ConfigUtil;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;

class CustomerAssociationCustomizeLoadedData implements ProcessorInterface
{
    const ID_FIELD_KEY = 'id_field';

    /** @var ConfigProvider */
    protected $configProvider;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var string */
    protected $customerAssociationField;

    /** @var string */
    protected $targetField;

    /** @var string */
    protected $accountField;

    /** @var array [$customerField => [__class__ => $customerClass, 'id_field' => $classIdentifier], ...] */
    protected $customersDataMap;

    /**
     * @param ConfigProvider $configProvider
     * @param DoctrineHelper $doctrineHelper
     * @param string         $customerAssociationField
     * @param string         $accountField
     * @param string         $targetField
     */
    public function __construct(
        ConfigProvider $configProvider,
        DoctrineHelper $doctrineHelper,
        $customerAssociationField,
        $accountField = 'account',
        $targetField = 'target'
    ) {
        $this->configProvider           = $configProvider;
        $this->doctrineHelper           = $doctrineHelper;
        $this->customerAssociationField = $customerAssociationField;
        $this->accountField             = $accountField;
        $this->targetField              = $targetField;
    }

    /**
     * Set corresponding target data to customer association field
     *
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var CustomizeLoadedDataContext $context */
        $data = $context->getResult();
        if (!is_array($data)) {
            return;
        }
        if (isset($data[$this->customerAssociationField])) {
            $data[$this->customerAssociationField] = $this->getTargetCustomer($data[$this->customerAssociationField]);

            $context->setResult($data);
        }
    }

    /**
     * Return customer target id and class in case if one of the customers data is set
     * and account id and class otherwise
     *
     * @param array $data
     *
     * @return array
     */
    protected function getTargetCustomer(array $data)
    {
        $customersDataMap = $this->getCustomersDataMap();
        foreach ($customersDataMap['targets'] as $field => $customerData) {
            if (!empty($data[$field][$customerData[self::ID_FIELD_KEY]])) {
                return [
                    ConfigUtil::CLASS_NAME            => $customerData[ConfigUtil::CLASS_NAME],
                    $customerData[self::ID_FIELD_KEY] => $data[$field][$customerData[self::ID_FIELD_KEY]]
                ];
            }
        }

        return [
            ConfigUtil::CLASS_NAME                           => Account::class,
            $customersDataMap['account'][self::ID_FIELD_KEY] =>
                $data[$customersDataMap['account'][self::ID_FIELD_KEY]],
        ];
    }

    /**
     * @return array
     */
    protected function getCustomersDataMap()
    {
        if (null === $this->customersDataMap) {
            $this->customersDataMap = [
                'targets' => [],
                'account' => [
                    ConfigUtil::CLASS_NAME => Account::class,
                    self::ID_FIELD_KEY     => $this->doctrineHelper->getSingleEntityIdentifierFieldName(Account::class)
                ]
            ];
            foreach ($this->configProvider->getCustomerClasses() as $class) {
                $this->customersDataMap['targets']
                [AccountCustomerManager::getCustomerTargetField($class)] = [
                    ConfigUtil::CLASS_NAME => $class,
                    self::ID_FIELD_KEY     => $this->doctrineHelper->getSingleEntityIdentifierFieldName($class)
                ];
            }
        }

        return $this->customersDataMap;
    }
}
