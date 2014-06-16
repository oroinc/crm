<?php

namespace OroCRM\Bundle\CaseBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use OroCRM\Bundle\CaseBundle\DependencyInjection\OroCRMCaseExtension;

class OroCRMCaseExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OroCRMCaseExtension
     */
    private $extension;

    /**
     * @var ContainerBuilder
     */
    private $container;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->extension = new OroCRMCaseExtension();
    }

    public function testLoad()
    {
        $this->extension->load(array(), $this->container);
    }
}
