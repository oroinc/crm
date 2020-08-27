<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Stub;

use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class MagentoTransportProviderStub implements MagentoTransportInterface
{
    /**
     * @var string
     */
    private $extensionVersion;

    /**
     * @var string
     */
    private $magentoVersion;

    /**
     * @var string
     */
    private $requiredExtensionVersion;

    /**
     * @var boolean
     */
    private $isSupportedExtensionVersion;

    /**
     * @var boolean
     */
    private $isExtensionInstalled;

    /**
     * @var string
     */
    private $adminUrl;

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->setData($data);
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        foreach ($data as $property => $value) {
            $this->{$property} = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function initWithExtraOptions(Transport $transportEntity, array $clientExtraOptions)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isExtensionInstalled()
    {
        return $this->isExtensionInstalled;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdminUrl()
    {
        return $this->adminUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrders()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getCarts()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomers()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isCustomerHasUniqueEmail(Customer $customer)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerGroups()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getStores()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsites()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getRegions()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerInfo($originId)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerAddresses($originId)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorCode(\Exception $e)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderInfo($incrementId)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditMemos()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditMemoInfo($incrementId)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function createCustomer(array $customerData)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function updateCustomer($customerId, array $customerData)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function createCustomerAddress($customerId, array $item)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function updateCustomerAddress($customerAddressId, array $item)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerAddressInfo($customerAddressId)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getNewsletterSubscribers()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function createNewsletterSubscriber(array $subscriberData)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function updateNewsletterSubscriber($subscriberId, array $subscriberData)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isSupportedExtensionVersion()
    {
        return $this->isSupportedExtensionVersion;
    }

    /**
     * @return boolean
     */
    public function isSupportedOrderNoteExtensionVersion()
    {
        return $this->isSupportedExtensionVersion()
            && version_compare($this->getExtensionVersion(), '1.2.19', 'ge');
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionVersion()
    {
        return $this->extensionVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function getMagentoVersion()
    {
        return $this->magentoVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredExtensionVersion()
    {
        return $this->requiredExtensionVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderNoteRequiredExtensionVersion()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function resetInitialState()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getServerTime()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function init(Transport $transportEntity)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormType()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsEntityFQCN()
    {
    }
}
