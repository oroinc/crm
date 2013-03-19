<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;

use Oro\Bundle\GridBundle\DependencyInjection\Compiler\AddFilterTypeCompilerPass;

class AddFilterTypeCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddFilterTypeCompilerPass
     */
    private $compiler;

    /**
     * @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    protected function setUp()
    {
        $this->compiler = new AddFilterTypeCompilerPass();
        $this->container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @dataProvider processDataProvider
     *
     * @param array $getDefinitionValueMap
     * @param array $findTaggedServiceIdsValueMap
     * @param array $expectedArguments
     * @param array $expectedScopes
     */
    public function testProcess(
        array $getDefinitionValueMap,
        array $findTaggedServiceIdsValueMap,
        array $expectedArguments,
        array $expectedScopes = array()
    ) {
        $this->container->expects($this->any())
            ->method('getDefinition')
            ->will($this->returnValueMap($getDefinitionValueMap));

        $this->container->expects($this->any())
            ->method('findTaggedServiceIds')
            ->will($this->returnValueMap($findTaggedServiceIdsValueMap));

        $this->compiler->process($this->container);

        foreach ($expectedArguments as $serviceId => $secondArgument) {
            $definition = $this->container->getDefinition($serviceId);

            $this->assertInstanceOf('Symfony\Component\DependencyInjection\Definition', $definition);
            $this->assertEquals($secondArgument, $definition->getArgument(1));
        }

        foreach ($expectedScopes as $serviceId => $scope) {
            $definition = $this->container->getDefinition($serviceId);

            $this->assertInstanceOf('Symfony\Component\DependencyInjection\Definition', $definition);
            $this->assertEquals($scope, $definition->getScope());
        }
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return array(
            'Collect actions and filters' => array(
                'getDefinitionValueMap' => array(
                    // Filter factory and filters definitions
                    array('oro_grid.filter.factory', $this->getStubDefinition(null, range(1, 2))),
                    array('oro_grid.orm.filter.type.string', $this->getStubDefinition()),

                    // Action factory and actions definitions
                    array('oro_grid.action.factory', $this->getStubDefinition(null, range(1, 2))),
                    array('oro_grid.action.type.redirect', $this->getStubDefinition()),
                    array('oro_grid.action.type.delete', $this->getStubDefinition()),
                    array('oro_grid.action.type.edit', $this->getStubDefinition()),
                ),
                'findTaggedServiceIdsValueMap' => array(
                    array(
                        // Services tagged with "oro_grid.filter.type"
                        'oro_grid.filter.type', array(
                            'oro_grid.orm.filter.type.string' => array(
                                array(
                                    'name' => 'oro_grid.filter.type',
                                    'alias' => 'oro_grid_orm_string',
                                ),
                            ),
                        ),
                    ),
                    array(
                        // Services tagged with "oro_grid.action.type"
                        'oro_grid.action.type', array(
                            'oro_grid.action.type.redirect' => array(
                                array(
                                    'name' => 'oro_grid.action.type',
                                    'alias' => 'oro_grid_action_redirect',
                                ),
                            ),
                            'oro_grid.action.type.delete' => array(
                                array(
                                    'name' => 'oro_grid.action.type',
                                    'alias' => 'oro_grid_action_delete',
                                ),
                            ),
                            'oro_grid.action.type.edit' => array(
                                array(
                                    'name' => 'oro_grid.action.type',
                                ),
                            ),
                        ),
                    ),
                ),
                'expectedArguments' => array(
                    // Second argument of filter factory
                    'oro_grid.filter.factory' => array(
                        'oro_grid_orm_string' => 'oro_grid.orm.filter.type.string',
                    ),
                    // Second argument of action factory
                    'oro_grid.action.factory' => array(
                        'oro_grid_action_redirect' => 'oro_grid.action.type.redirect',
                        'oro_grid_action_delete' => 'oro_grid.action.type.delete',
                        'oro_grid.action.type.edit' => 'oro_grid.action.type.edit',
                    ),
                ),
                // Changed scopes of filters and actions services
                'expectedScopes' => array(
                    'oro_grid.orm.filter.type.string' => ContainerInterface::SCOPE_PROTOTYPE,
                    'oro_grid.action.type.redirect' => ContainerInterface::SCOPE_PROTOTYPE,
                    'oro_grid.action.type.delete' => ContainerInterface::SCOPE_PROTOTYPE,
                    'oro_grid.action.type.edit' => ContainerInterface::SCOPE_PROTOTYPE,
                ),
            ),
            'Empty tags' => array(
                'getDefinitionValueMap' => array(
                    // Filter and action factories
                    array('oro_grid.filter.factory', $this->getStubDefinition(null, array(null, array()))),
                    array('oro_grid.action.factory', $this->getStubDefinition(null, array(null, array()))),
                ),
                'findTaggedServiceIdsValueMap' => array(
                    array('oro_grid.filter.type', array()), // No services tagged with "oro_grid.filter.type"
                    array('oro_grid.action.type', array()), // No services tagged with "oro_grid.action.type"
                ),
                // No changes in factories arguments because there were no tagged services
                'expectedArguments' => array(
                    'oro_grid.filter.factory' => array(),
                    'oro_grid.action.factory' => array(),
                )
            ),
        );
    }

    /**
     * @param string $class
     * @param array $arguments
     * @return Definition
     */
    private function getStubDefinition($class = null, $arguments = array())
    {
        if (!$class) {
            $class = uniqid('StubDefinitionClassName');
        }
        return new Definition($class, $arguments);
    }
}
