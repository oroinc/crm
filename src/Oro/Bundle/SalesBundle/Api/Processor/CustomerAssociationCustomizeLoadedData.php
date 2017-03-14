<?php

namespace Oro\Bundle\SalesBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Processor\CustomizeLoadedData\CustomizeLoadedDataContext;
use Oro\Bundle\ApiBundle\Util\ConfigUtil;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

class CustomerAssociationCustomizeLoadedData implements ProcessorInterface
{
    const ID_FIELD_KEY = 'id_field';

    /** @var ConfigProvider */
    protected $configProvider;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var string */
    protected $customerAssociationField;

    /** @var array [$customerField => [__class__ => $customerClass, 'id_field' => $classIdentifier], ...] */
    protected $customersDataMap;

    /**
     * @param ConfigProvider $configProvider
     * @param DoctrineHelper $doctrineHelper
     * @param string         $customerAssociationField
     */
    public function __construct(
        ConfigProvider $configProvider,
        DoctrineHelper $doctrineHelper,
        $customerAssociationField
    ) {
        $this->configProvider           = $configProvider;
        $this->doctrineHelper           = $doctrineHelper;
        $this->customerAssociationField = $customerAssociationField;
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
            $data[$this->customerAssociationField]['target'] = $this->getTargetCustomer(
                $data[$this->customerAssociationField]
            );
            $data['customer'] = $data[$this->customerAssociationField]['target'];

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
        foreach ($customersDataMap as $field => $customerData) {
            if (!empty($data[$field][$customerData[self::ID_FIELD_KEY]])) {
                return [
                    ConfigUtil::CLASS_NAME            => $customerData[ConfigUtil::CLASS_NAME],
                    $customerData[self::ID_FIELD_KEY] => $data[$field][$customerData[self::ID_FIELD_KEY]]
                ];
            }
        }

        return null;
    }

    /**
     * @return array
     */
    protected function getCustomersDataMap()
    {
        if (null === $this->customersDataMap) {
            $this->customersDataMap = [];
            foreach ($this->configProvider->getCustomerClasses() as $class) {
                $this->customersDataMap
                [AccountCustomerManager::getCustomerTargetField($class)] = [
                    ConfigUtil::CLASS_NAME => $class,
                    self::ID_FIELD_KEY     => $this->doctrineHelper->getSingleEntityIdentifierFieldName($class)
                ];
            }
        }

        return $this->customersDataMap;
    }
}
