<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Entity;

class MagentoSoapTransportTest extends AbstractEntityTestCase
{
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

        return [
            'wsdl_url'      => ['wsdlUrl',       $wsdlUrl, $wsdlUrl],
            'api_user'      => ['apiUser',       $apiUser, $apiUser],
            'api_key'       => ['apiKey',        $apiKey, $apiKey],
            'website_id'    => ['websiteId',     $websiteId, $websiteId],
            'websites'      => ['websites',      $websites, $websites],
            'syncStartDate' => ['syncStartDate', $syncStartDate, $syncStartDate],
            'syncRange'     => ['syncRange',     $syncRange, $syncRange],
        ];
    }

    public function testSettingsBag()
    {
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\ParameterBag', $this->entity->getSettingsBag());
    }
}
