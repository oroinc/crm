<?php

namespace OroCRM\Bundle\ContactUsBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class ContactRequests
 * @package OroCRM\Bundle\ContactUsBundle\Tests\Selenium\Pages
 * @method ContactRequests openContactRequests(string $bundlePath)
 * @method ContactRequest add()
 * @method ContactRequest open(array $filter)
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
