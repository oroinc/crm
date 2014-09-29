<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class DashboardControllerTest extends WebTestCase
{
    public function testAverageOrderAmountAction()
    {
        $this->initClient();
        $this->client->request('GET', $this->getUrl('orocrm_magento_dashboard_average_order_amount'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('Average order amount', $result->getContent());

        /** @var Channel[] $channels */
        $channels = $this->getContainer()->get('doctrine')->getRepository('OroCRMChannelBundle:Channel')->findAll();
        foreach ($channels as $channel) {
            $this->assertContains($channel->getName(), $result->getContent());
        }
    }

    public function testNewCustomersAction()
    {
        $this->initClient();
        $this->client->request('GET', $this->getUrl('orocrm_magento_dashboard_new_customers_chart'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('New Web Customers', $result->getContent());

        $aclHelper = $this->getContainer()->get('oro_security.acl_helper');

        /** @var array $channels */
        $channels = $this->getContainer()->get('doctrine')->getRepository('OroCRMChannelBundle:Channel')
            ->getAvailableChannelNames($aclHelper, 'magento');
        foreach ($channels as $channel) {
            $this->assertContains($channel['name'], $result->getContent());
        }
    }
}
