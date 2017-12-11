<?php

namespace Oro\Bundle\ContactBundle\Tests\Behat\Element;

use Oro\Bundle\FormBundle\Tests\Behat\Element\Select2Entity;

class Select2Share extends Select2Entity
{
    /**
     * @var string
     */
    protected $searchInputSelector = '.select2-search-field input';
}
