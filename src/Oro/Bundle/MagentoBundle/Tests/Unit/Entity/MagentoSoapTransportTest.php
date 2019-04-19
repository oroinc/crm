<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Entity;

use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class MagentoSoapTransportTest extends AbstractEntityTestCase
{
    /** @var MagentoTransport */
    protected $entity;

    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport';
    }

    /**
     * {@inheritDoc}
     */
    public function getSetDataProvider()
    {
        $wsdlUrl = 'http://localhost/?wsdl';
        $apiUser = $apiKey = uniqid();
        $syncStartDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $syncRange = \DateInterval::createFromDateString('1 day');
        $websiteId = 123;
        $guestCustomerSync = true;
        $websites = [];
        $isExtensionInstalled = true;
        $adminUrl = 'http://localhost/admin';

        return [
            'wsdl_url'               => ['ApiUrl',               $wsdlUrl, $wsdlUrl],
            'api_user'               => ['apiUser',              $apiUser, $apiUser],
            'api_key'                => ['apiKey',               $apiKey, $apiKey],
            'website_id'             => ['websiteId',            $websiteId, $websiteId],
            'websites'               => ['websites',             $websites, $websites],
            'syncStartDate'          => ['syncStartDate',        $syncStartDate, $syncStartDate],
            'syncRange'              => ['syncRange',            $syncRange, $syncRange],
            'is_extension_installed' => ['isExtensionInstalled', $isExtensionInstalled, $isExtensionInstalled],
            'guest_customer_sync'    => ['guestCustomerSync',    $guestCustomerSync, $guestCustomerSync],
            'admin_url'              => ['adminUrl',             $adminUrl, $adminUrl],
            'extension_version'      => ['extensionVersion',     '1.0.0', '1.0.0'],
            'magento_version'        => ['magentoVersion',       '1.0.0', '1.0.0'],
            'newsletter_subscriber_synced_to_id' => ['newsletter_subscriber_synced_to_id', 10, 10]
        ];
    }

    public function testSettingsBag()
    {
        $data = [
            'api_user' => 'test_user',
            'api_key' => 'test_key',
            'wsdl_url' => 'http://test.url/',
            'sync_range' => new \DateInterval('P1D'),
            'wsi_mode' => true,
            'guest_customer_sync' => true,
            'website_id' => 1,
            'start_sync_date' => new \DateTime('now'),
            'initial_sync_start_date' => new \DateTime('now'),
            'extension_version' => '1.1.0',
            'magento_version' => '1.8.0.0',
            'newsletter_subscriber_synced_to_id' => 10,
        ];

        $this->entity
            ->setApiUser($data['api_user'])
            ->setApiKey($data['api_key'])
            ->setApiUrl($data['wsdl_url'])
            ->setSyncRange($data['sync_range'])
            ->setIsWsiMode($data['wsi_mode'])
            ->setGuestCustomerSync($data['guest_customer_sync'])
            ->setWebsiteId($data['website_id'])
            ->setSyncStartDate($data['start_sync_date'])
            ->setInitialSyncStartDate($data['initial_sync_start_date'])
            ->setExtensionVersion('1.1.0')
            ->setMagentoVersion('1.8.0.0')
            ->setNewsletterSubscriberSyncedToId(10);

        $settingsBag = $this->entity->getSettingsBag();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\ParameterBag', $settingsBag);
        $this->assertSame($settingsBag, $this->entity->getSettingsBag());
        $this->assertEquals($data, $settingsBag->all());
    }

    /**
     * @dataProvider supportDataProvider
     *
     * @param bool $isExtensionInstalled
     * @param string $extensionVersion
     * @param bool $expected
     */
    public function testSupportedExtensionVersion($isExtensionInstalled, $extensionVersion, $expected)
    {
        $this->entity->setExtensionVersion($extensionVersion)
            ->setIsExtensionInstalled($isExtensionInstalled);

        $this->assertEquals($expected, $this->entity->isSupportedExtensionVersion());
    }

    /**
     * @return array
     */
    public function supportDataProvider()
    {
        return [
            [
                true, '0.1', false,
                true, SoapTransport::REQUIRED_EXTENSION_VERSION, true,
                false, '', false
            ]
        ];
    }

    public function testWsdlUrl()
    {
        $url = 'http://test.local/?wsdl=1';
        $cache = '/tmp/cached.wsdl';
        $this->entity->setApiUrl($url);

        $this->assertEquals($url, $this->entity->getSettingsBag()->get('wsdl_url'));

        $this->entity->setWsdlCachePath($cache);
        $this->assertEquals($cache, $this->entity->getSettingsBag()->get('wsdl_url'));
    }
}
