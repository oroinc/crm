<?php

namespace OroCRM\Bundle\ContactUsBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class ContactRequests
 * @package OroCRM\Bundle\ContactUsBundle\Tests\Selenium\Pages
 * @method ContactRequests openContactRequests openContactRequests(string)
 * @method ContactRequest add add()
 * @method ContactRequest open open()
 * {@inheritdoc}
 */
class ContactRequests extends AbstractPageFilteredGrid
{
    const NEW_ENTITY_BUTTON = "//a[@title='Create Contact Request']";
    const URL = 'contact-us';

    public function entityNew()
    {
        return new ContactRequest($this->test);
    }

    public function entityView()
    {
        return new ContactRequest($this->test);
    }
}
