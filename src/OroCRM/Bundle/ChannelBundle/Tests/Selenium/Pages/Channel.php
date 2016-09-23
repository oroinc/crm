<?php

namespace Oro\Bundle\ChannelBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageEntity;

/**
 * Class Channel
 * @package Oro\Bundle\ChannelBundle\Tests\Selenium\Pages
 * {@inheritdoc}
 */
class Channel extends AbstractPageEntity
{
    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        $element = $this->test
            ->select($this->test->byXpath("//*[@data-ftid='oro_channel_form_status']"));
        $element->selectOptionByLabel($status);

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $element = $this->test->byXpath("//*[@data-ftid='oro_channel_form_name']");
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
        $this->test->byXPath("//div[starts-with(@id,'s2id_oro_channel_form_channelType')]/a")->click();
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
        $xpath = "//li/div[@class='select2-result-label' and contains(text(), '{$entity}')]";
        $element = $this->test->byXpath($xpath);
        $this->test->moveto($element);
        $element->click();
        $this->test->byXPath("//a[@title='Add']")->click();

        return $this;
    }
}
