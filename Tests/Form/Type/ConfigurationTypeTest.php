<?php
namespace Oro\Bundle\DataFlowBundle\Tests\Form\Type;

use Oro\Bundle\DataFlowBundle\Tests\Form\Demo\MyConfigurationType;
use Symfony\Component\Form\FormBuilder;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class ConfigurationTypeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var MyConfigurationType
     */
    protected $formType;

    /**
     * Setup
     */
    public function setup()
    {
        $this->formType = new MyConfigurationType();
    }

    /**
     * Test related method
     */
    public function testBuildForm()
    {
        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $factory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $builder = new FormBuilder('name', null, $dispatcher, $factory);
        $this->formType->buildForm($builder, array());
    }
}
