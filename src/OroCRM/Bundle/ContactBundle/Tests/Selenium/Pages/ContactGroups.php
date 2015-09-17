<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageGrid;

/**
 * Class ContactGroups
 *
 * @package OroCRM\Bundle\ContactBundle\Tests\Selenium\Pages
 * @method ContactGroups openContactGroups(string $bundlePath)
 * @method ContactGroups open(array $filter)
 * {@inheritdoc}
 */
class ContactGroups extends AbstractPageGrid
{
    const URL = 'contact/group';

    public function entityNew()
    {
        return $this;
    }

    public function entityView()
    {
        return $this;
    }
}
