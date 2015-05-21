<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Selenium\Pages;

use Oro\Bundle\IntegrationBundle\Tests\Selenium\Pages\Integration as ParentIntegration;
use Oro\Bundle\IntegrationBundle\Tests\Selenium\Pages\Integrations;

/**
 * Class Integration
 * @package OroCRM\Bundle\MagentoBundle\Tests\Selenium\Pages
 * {@inheritdoc}
 */
class Integration extends ParentIntegration
{

    /**
     * @param string $url
     * @return $this
     */
    public function setWsdlUrl($url)
    {
        $field = $this->test->byXpath("//*[@data-ftid='oro_integration_channel_form_transport_wsdlUrl']");
        $field->clear();
        $field->value($url);

        return $this;
    }

    /**
     * @param string $user
     * @return $this
     */
    public function setApiUser($user)
    {
        $field = $this->test->byXpath("//*[@data-ftid='oro_integration_channel_form_transport_apiUser']");
        $field->clear();
        $field->value($user);

        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setApiKey($key)
    {
        $field = $this->test->byXpath("//*[@data-ftid='oro_integration_channel_form_transport_apiKey']");
        $field->clear();
        $field->value($key);

        return $this;
    }

    /**
     * @return $this
     */
    public function setWsiCompliance()
    {
        $this->test->byXPath("//input[@data-ftid='oro_integration_channel_form_transport_isWsiMode']")->click();
        $this->waitForAjax();

        return $this;
    }

    /**
     * @param string $date
     * @return $this
     */
    public function setSyncDate($date)
    {
        $field = $this->test->byXpath("//*[@data-ftid='oro_integration_channel_form_transport_syncStartDate']/..".
            "/following-sibling::input[contains(@class,'datepicker-input')]");
        $field->clear();
        $field->value($date);

        return $this;
    }

    /**
     * @return $this
     */
    public function checkConnection()
    {
        $this->test->byXPath("//button[@id='oro_integration_channel_form_transport_check']")->click();
        $this->waitForAjax();

        return $this;
    }

    /**
     * @param string $website
     * @return $this
     */
    public function selectWebsite($website)
    {
        $select = $this->test
            ->select($this->test->byXpath("//*[@data-ftid='oro_integration_channel_form_transport_websiteId']"));
        $select->selectOptionByLabel($website);

        return $this;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setAdminUrl($url)
    {
        $field = $this->test->byXpath("//*[@data-ftid='oro_integration_channel_form_transport_adminUrl']");
        $field->clear();
        $field->value($url);

        return $this;
    }

    /**
     * @param array $connectors
     * @return $this
     */
    public function setConnectors($connectors = array())
    {
        foreach ($connectors as $connector) {
            $this->test->byXPath(
                "//div[@data-ftid='oro_integration_channel_form_connectors']//label[contains(., '{$connector}')]"
            )->click();
            $this->waitForAjax();
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function setTwoWaySync()
    {
        $this->test->byXPath(
            "//input[@data-ftid='oro_integration_channel_form_synchronizationSettings_isTwoWaySyncEnabled']"
        )->click();
        $this->waitForAjax();

        return $this;
    }

    /**
     * @param string $priority
     * @return $this
     */
    public function setSyncPriority($priority)
    {
        $select = $this->test
            ->byXpath("//*[@data-ftid='oro_integration_channel_form_synchronizationSettings_syncPriority']");
        $select = $this->test->select($select);
        $select->selectOptionByLabel($priority);

        return $this;
    }
}
