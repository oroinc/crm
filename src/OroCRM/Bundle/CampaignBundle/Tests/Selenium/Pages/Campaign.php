<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageEntity;

/**
 * Class Campaign
 * @package OroCRM\Bundle\CampaignBundle\Tests\Selenium\Pages
 * {@inheritdoc}
 */
class Campaign extends AbstractPageEntity
{
    protected $owner = "//div[starts-with(@id,'s2id_orocrm_campaign_form_owner')]/a";

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $object = $this->test->byXpath("//*[@data-ftid='orocrm_campaign_form_name']");
        $object->clear();
        $object->value($name);

        return $this;
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $object = $this->test->byXpath("//*[@data-ftid='orocrm_campaign_form_code']");
        $object->clear();
        $object->value($code);

        return $this;
    }

    /**
     * @param $contact
     * @return $this
     */
    public function setStartDate($contact)
    {
    }

    public function setEndDate()
    {

    }

    public function setDescription()
    {

    }

    public function setBudget()
    {

    }

    /**
     * @return $this
     */
    public function edit()
    {
        $this->test->byXpath("//div[@class='pull-left btn-group icons-holder']/a[@title = 'Edit Campaign']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();

        return $this;
    }
}
