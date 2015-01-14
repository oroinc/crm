<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageEntity;

/**
 * Class Channel
 * @package OroCRM\Bundle\ChannelBundle\Tests\Selenium\Pages
 * {@inheritdoc}
 */
class Channel extends AbstractPageEntity
{
    public function __construct($testCase, $redirect = true)
    {
        parent::__construct($testCase, $redirect);
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        $element = $this->test->select($this->test->byId('orocrm_channel_form_status'));
        $element->selectOptionByLabel($status);

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $element = $this->test->byId('orocrm_channel_form_name');
        $element->clear();
        $element->value($name);

        return $this;
    }

    /**
     * @param $type
     * @return $this
     */
    public function setType($type)
    {
        $this->test->byXPath("//div[@id='s2id_orocrm_channel_form_channelType']/a")->click();
        $this->test->byXpath("//li/div[@class='select2-result-label' and contains(text(), '{$type}')]")->click();
        $this->waitForAjax();
        return $this;
    }

    /**
     * @param $entity
     * @return $this
     */
    public function addEntity($entity)
    {
        $this->test->byXpath(
            "//div[@class = 'query-designer-form entities-form-container']//a[starts-with(@class, 'select2-choice')]"
        )->click();
        $xpath = "//li/div[@class='select2-result-label' and contains(text(), '{$entity}')]/i";
        if (!$this->isElementPresent("//li/div[@class='select2-result-label' and contains(text(), '{$entity}')]/i")) {
            $xpath = "//li/div[@class='select2-result-label' and contains(text(), '{$entity}')]";
        }
        $element = $this->test->byXpath($xpath);
        $this->test->moveto($element);
        $element->click();
        $this->test->byXPath("//a[@title='Add']")->click();

        return $this;
    }
}
