<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Entity;

use OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport;

class MagentoSoapTransportTest extends AbstractEntityTestCase
{
    /** @var MagentoSoapTransport */
    protected $entity;

    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport';
    }

    /**
     * {@inheritDoc}
     */
    public function getSetDataProvider()
    {
        $wsdlUrl = 'http://localhost/?wsdl';
        $apiUser = $apiKey = uniqid();
        $syncStartDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $syncRange = \DateInterval::createFromDateString('p1d');
        $websiteId = 123;
        $websites = [];
        $isExtensionInstalled = true;
        $adminUrl = 'http://localhost/admin';

        return [
            'wsdl_url'               => ['wsdlUrl',              $wsdlUrl, $wsdlUrl],
            'api_user'               => ['apiUser',              $apiUser, $apiUser],
            'api_key'                => ['apiKey',               $apiKey, $apiKey],
            'website_id'             => ['websiteId',            $websiteId, $websiteId],
            'websites'               => ['websites',             $websites, $websites],
            'syncStartDate'          => ['syncStartDate',        $syncStartDate, $syncStartDate],
            'syncRange'              => ['syncRange',            $syncRange, $syncRange],
            'is_extension_installed' => ['isExtensionInstalled', $isExtensionInstalled, $isExtensionInstalled],
            'admin_url'              => ['adminUrl',             $adminUrl, $adminUrl],
        ];
    }

    public function testSettingsBag()
    {
        $data = array(
            'api_user'        => 'test_user',
            'api_key'         => 'test_key',
            'wsdl_url'        => 'http://test.url/',
            'sync_range'      => new \DateInterval('P1D'),
            'wsi_mode'        => true,
            'website_id'      => 1,
            'start_sync_date' => new \DateTime('now'),
        );

        $this->entity
            ->setApiUser($data['api_user'])
            ->setApiKey($data['api_key'])
            ->setWsdlUrl($data['wsdl_url'])
            ->setSyncRange($data['sync_range'])
            ->setIsWsiMode($data['wsi_mode'])
            ->setWebsiteId($data['website_id'])
            ->setSyncStartDate($data['start_sync_date']);

        $settingsBag = $this->entity->getSettingsBag();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\ParameterBag', $settingsBag);
        $this->assertSame($settingsBag, $this->entity->getSettingsBag());
        $this->assertEquals($data, $settingsBag->all());
    }
}
