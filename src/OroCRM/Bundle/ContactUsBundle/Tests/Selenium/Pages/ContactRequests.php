<?php

namespace OroCRM\Bundle\ContactUsBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class ContactRequests
 * @package OroCRM\Bundle\ContactUsBundle\Tests\Selenium\Pages
 * @method ContactRequests openContactRequests openContactRequests(string)
 * {@inheritdoc}
 */
class ContactRequests extends AbstractPageFilteredGrid
{
    const URL = 'contact-us';

    public function __construct($testCase, $redirect = true)
    {
        $this->redirectUrl = self::URL;
        parent::__construct($testCase, $redirect);
    }

    /**
     * @return ContactRequest
     */
    public function add()
    {
        $this->test->byXPath("//a[@title='Create Contact Request']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();

        return new ContactRequest($this->test);
    }

    /**
     * @param array $entityData
     * @return ContactRequest
     */
    public function open($entityData = array())
    {
        $page = parent::open($entityData);

        return new ContactRequest($page->test);
    }
}
