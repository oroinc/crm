<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageGrid;

/**
 * Class ContactGroups
 *
 * @package OroCRM\Bundle\ContactBundle\Tests\Selenium\Pages
 */
class ContactGroups extends AbstractPageGrid
{
    const URL = 'contact/group';

    public function __construct($testCase, $redirect = true)
    {
        $this->redirectUrl = self::URL;
        parent::__construct($testCase, $redirect);

    }

    public function open($entityData = array())
    {
        return;
    }
}
