<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use Oro\Bundle\IntegrationBundle\Tests\Selenium\Pages\Integrations;

/**
 * Class CreateIntegrationTest
 *
 * @package OroCRM\Bundle\MagentoBundle\Tests\Selenium
 */
class CreateIntegrationTest extends Selenium2TestCase
{
    /**
     * @return string
     */
    public function testCreateIntegration()
    {
        $name = 'Magento integration_' . mt_rand(10, 99);

        $login = $this->login();
        /** @var Integrations $login */
        $login->openIntegrations('Oro\Bundle\IntegrationBundle')
            ->add()
            ->setName($name)
            ->setWsdlUrl(PHPUNIT_TESTSUITE_EXTENSION_MAGENTO_HOST . '/api/v2_soap/index/?wsdl=1')
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
