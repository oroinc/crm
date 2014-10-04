<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use Oro\Bundle\IntegrationBundle\Tests\Selenium\Pages\Integrations;

use OroCRM\Bundle\MagentoBundle\Tests\Selenium\Pages\Integration;

/**
 * Class CreateIntegrationTest
 *
 * @package OroCRM\Bundle\MagentoBundle\Tests\Selenium
 */
class CreateIntegrationTest extends Selenium2TestCase
{

    protected function setUp()
    {
        $this->markTestSkipped("Skipped because added Channels Management");
        $url = PHPUNIT_TESTSUITE_EXTENSION_MAGENTO_HOST . '/api/v2_soap/index/?wsdl=1';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!($httpCode>=200 && $httpCode<300)) {
            $this->markTestSkipped('Magento instance is not available');
        }

        parent::setUp();
    }

    /**
     * @return string
     */
    public function testCreateIntegration()
    {
        $name = 'Magento integration_' . mt_rand(10, 99);

        $login = $this->login();
        /** @var Integrations $login */
        $login = $login->openIntegrations('Oro\Bundle\IntegrationBundle')
            ->add()
            ->setName($name)
            ->setType('magento');
        /** @var Integration $login */
        $login->setWsdlUrl(PHPUNIT_TESTSUITE_EXTENSION_MAGENTO_HOST . '/api/v2_soap/index/?wsdl=1')
            ->setApiUser('api_user')
            ->setApiKey('api-key')
            ->setSyncDate('Jan 1, 2013')
            ->checkConnection()
            ->selectWebsite('All web sites')
            ->setAdminUrl(PHPUNIT_TESTSUITE_EXTENSION_MAGENTO_HOST . '/admin/')
            ->setConnectors(array('Customer connector', 'Order connector', 'Cart connector'))
            ->setTwoWaySync()
            ->setSyncPriority('Remote wins')
            ->save()
            ->assertMessage('Integration saved');

        return $name;
    }

    /**
     * @depends testCreateIntegration
     * @param $name
     * @return string
     */
    public function testUpdateIntegration($name)
    {
        $newName = 'Update_' . $name;

        $login = $this->login();
        /** @var Integrations $login */
        $login->openIntegrations('Oro\Bundle\IntegrationBundle')
            ->filterBy('Name', $name)
            ->open(array($name))
            ->setName($newName)
            ->save()
            ->assertMessage('Integration saved');
        $login->openIntegrations('Oro\Bundle\IntegrationBundle')
            ->filterBy('Name', $name)
            ->assertNoDataMessage('No channel was found to match your search.');

        return $newName;
    }

    /**
     * @depends testUpdateIntegration
     * @param $name
     */
    public function testDeleteIntegration($name)
    {
        $login = $this->login();
        /** @var Integrations $login */
        $login->openIntegrations('Oro\Bundle\IntegrationBundle')
            ->filterBy('Name', $name)
            ->open(array($name))
            ->delete()
            ->assertMessage('Integration and all related data were deleted');
        /** @var Integrations $login */
        $login->openIntegrations('Oro\Bundle\IntegrationBundle');
        if ($login->getRowsCount() > 0) {
            $login->filterBy('Name', $name)
                ->assertNoDataMessage('No channel was found to match your search.');
        }
    }
}
