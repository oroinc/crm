<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Oro\Bundle\GridBundle\Action\ActionUrlGenerator;
use Symfony\Component\Routing\RequestContext;

class ActionUrlGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test parameters
     */
    const TEST_ROUTE_NAME = 'test_route_name';
    const TEST_BASE_URL   = '/test_base_url';

    /**
     * @var ActionUrlGenerator
     */
    protected $model;

    protected function tearDown()
    {
        unset($this->model);
    }

    /**
     * @param Route $router
     */
    protected function initializeActionUrlGenerator(Route $router = null)
    {
        $routeCollection = new RouteCollection();
        if ($router) {
            $routeCollection->add(self::TEST_ROUTE_NAME, $router);
        }

        $context = new RequestContext(self::TEST_BASE_URL);

        $router = $this->getMockForAbstractClass(
            'Symfony\Component\Routing\RouterInterface',
            array(),
            '',
            false,
            true,
            true,
            array('getRouteCollection', 'getContext')
        );
        $router->expects($this->any())
            ->method('getRouteCollection')
            ->will($this->returnValue($routeCollection));
        $router->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue($context));

        $this->model = new ActionUrlGenerator($router);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage There is no route with name "test_route_name".
     */
    public function testGenerateWithoutRoute()
    {
        $this->initializeActionUrlGenerator();

        $this->model->generate(self::TEST_ROUTE_NAME);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage There is no placeholder with name "{parameter}" for route "test_route_name".
     */
    public function testGenerateWithoutRouteParameter()
    {
        $route = new Route('/test/pattern/with/{parameter}');
        $this->initializeActionUrlGenerator($route);

        $this->model->generate(self::TEST_ROUTE_NAME);
    }

    /**
     * Data provider for testGenerate
     *
     * @return array
     */
    public function generateDataProvider()
    {
        return array(
            'no_parameters_no_placeholders' => array(
                '$expectedUrl'   => self::TEST_BASE_URL . '/no/parameters/no/placeholders',
                '$routePattern'  => '/no/parameters/no/placeholders',
            ),
            'no_parameters_with_placeholders' => array(
                '$expectedUrl'   => self::TEST_BASE_URL . '/no/parameters/with/{place}/{holder}',
                '$routePattern'  => '/no/parameters/with/{place}/{holder}',
                '$routeDefaults' => array(),
                '$parameters'    => array(),
                '$placeholders'  => array(
                    'place'  => 'place_key',
                    'holder' => 'holder_key',
                ),
            ),
            'with_parameters_with_placeholders' => array(
                '$expectedUrl'   => self::TEST_BASE_URL . '/with/parameter_key/with/{place}/{holder}',
                '$routePattern'  => '/with/{parameter}/with/{place}/{holder}',
                '$routeDefaults' => array(
                    'parameter' => 'parameter_default'
                ),
                '$parameters'    => array(
                    'parameter' => 'parameter_key'
                ),
                '$placeholders'  => array(
                    'place'  => 'place_key',
                    'holder' => 'holder_key',
                ),
            ),
            'with_defaults_no_placeholders' => array(
                '$expectedUrl'   => self::TEST_BASE_URL . '/with/default_key/no/placeholders',
                '$routePattern'  => '/with/{default}/no/placeholders',
                '$routeDefaults' => array(
                    'default' => 'default_key'
                ),
            ),
        );
    }

    /**
     * @param string $expectedUrl
     * @param string $routePattern
     * @param array $routeDefaults
     * @param array $parameters
     * @param array $placeholders
     *
     * @dataProvider generateDataProvider
     */
    public function testGenerate(
        $expectedUrl,
        $routePattern,
        array $routeDefaults = array(),
        array $parameters = array(),
        array $placeholders = array()
    ) {
        $route = new Route($routePattern, $routeDefaults);
        $this->initializeActionUrlGenerator($route);

        $this->assertEquals(
            $expectedUrl,
            $this->model->generate(self::TEST_ROUTE_NAME, $parameters, $placeholders)
        );
    }
}
