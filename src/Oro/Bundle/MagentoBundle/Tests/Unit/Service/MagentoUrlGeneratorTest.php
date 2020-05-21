<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Service;

use Oro\Bundle\MagentoBundle\Exception\ExtensionRequiredException;
use Oro\Bundle\MagentoBundle\Service\MagentoUrlGenerator;

class MagentoUrlGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $router;

    /**
     * @var MagentoUrlGenerator
     */
    private $urlGenerator;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $channel;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $transport;

    protected function setUp(): void
    {
        $this->router = $this->getMockBuilder('Symfony\Component\Routing\Router')
                        ->disableOriginalConstructor()
                        ->getMock();
        $this->urlGenerator = new MagentoUrlGenerator($this->router);
        $this->channel = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Channel')
            ->disableOriginalConstructor()
            ->getMock();
        $this->transport = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Entity\MagentoTransport')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown(): void
    {
        unset(
            $this->urlGenerator,
            $this->router,
            $this->channel,
            $this->transport
        );
    }

    public function testConstruct()
    {
        $cleanStr = '';
        $this->assertNull($this->urlGenerator->getChannel());
        $this->assertEquals($cleanStr, $this->urlGenerator->getError());
        $this->assertEquals($cleanStr, $this->urlGenerator->getFlowName());
        $this->assertEquals($cleanStr, $this->urlGenerator->getSourceUrl());
        $this->assertSame($this->router, $this->urlGenerator->getRouter());
    }

    /**
     * @dataProvider  getSetDataProvider
     *
     * @param string $property
     * @param mixed  $value
     * @param mixed  $expected
     */
    public function testSetGet($property, $value = null, $expected = null)
    {
        if ($value !== null) {
            call_user_func_array(array($this->urlGenerator, 'set' . ucfirst($property)), array($value));
        }
        $this->assertEquals(
            $expected,
            call_user_func_array(array($this->urlGenerator, 'get' . ucfirst($property)), array())
        );
    }

    /**
     * @return array
     */
    public function getSetDataProvider()
    {
        $error = 'Some error text';
        $flowName = 'some flow name';
        $newOrderRoute = MagentoUrlGenerator::NEW_ORDER_ROUTE;
        $origin = 'customer';

        return [
            'channel'      => ['channel', $this->channel, $this->channel],
            'error'        => ['error', $error, $error],
            'flowName'     => ['flowName', $flowName, $flowName],
            'magentoRoute' => ['magentoRoute', $newOrderRoute, $newOrderRoute],
            'origin'       => ['origin', $origin, $origin],
        ];
    }

    public function testIsError()
    {
        $this->assertFalse($this->urlGenerator->isError());
        $error = 'some error text';
        $this->urlGenerator->setError($error);
        $this->assertTrue($this->urlGenerator->isError());
    }

    /**
     * @dataProvider  getUrlProvider
     *
     * @param string $url
     */
    public function testGetAdminUrl($url)
    {
        $this->transport
            ->expects($this->atLeastOnce())
            ->method('getAdminUrl')
            ->will(
                $this->returnValue($url)
            );
        $this->channel
            ->expects($this->atLeastOnce())
            ->method('getTransport')
            ->will(
                $this->returnValue($this->transport)
            );
        $this->urlGenerator->setChannel($this->channel);
        $this->assertEquals(
            $url,
            $this->urlGenerator->getAdminUrl()
        );
    }

    public function getUrlProvider()
    {
        return [
            ['http://localhost/magento/admin'],
            [new ExtensionRequiredException],
        ];
    }

    /**
     * @dataProvider getSourceProvider
     *
     * @param int $id
     * @param string $successRoute
     * @param string $errorRoute
     * @param string $adminUrl
     * @param string $successUrl
     * @param string $errorUrl
     * @param string $flowName
     * @param string $magentoRoute
     * @param string $origin
     */
    public function testGenerate(
        $id,
        $successRoute,
        $errorRoute,
        $adminUrl,
        $successUrl,
        $errorUrl,
        $flowName,
        $magentoRoute,
        $origin
    ) {
        $result = $adminUrl .
                  '/oro_gateway/do?'.$origin.'=' .
                  $id .
                  '&route=' . $magentoRoute .
                  '&workflow=' . $flowName .
                  '&success_url=' . urlencode($successUrl) .
                  '&error_url=' . urlencode($errorUrl);

        $this->transport->expects($this->atLeastOnce())->method('getAdminUrl')->will($this->returnValue($adminUrl));

        $this->channel->expects($this->atLeastOnce())
            ->method('getTransport')
            ->will($this->returnValue($this->transport));

        $this->router
            ->expects($this->at(0))
            ->method('generate')
            ->with($this->equalTo($successRoute))
            ->will($this->returnValue($successUrl));
        $this->router
            ->expects($this->at(1))
            ->method('generate')
            ->with($this->equalTo($errorRoute))
            ->will($this->returnValue($errorUrl));
        $urlGenerator = new MagentoUrlGenerator($this->router);
        $urlGenerator->setChannel($this->channel);
        $urlGenerator->setFlowName($flowName);
        $urlGenerator->setMagentoRoute($magentoRoute);
        $urlGenerator->setOrigin($origin);
        $urlGenerator->generate($id, $successRoute, $errorRoute);
        $this->assertEquals(
            $result,
            $urlGenerator->getSourceUrl()
        );
    }

    public function getSourceProvider()
    {
        $adminUrl = 'http://localhost/magento/admin';
        $successRoute = 'successRoute';
        $errorRoute = 'errorRoute';
        $successUrl = 'http://localhost/magento/success';
        $errorUrl = 'http://localhost/magento/error';
        $flowName = 'flowName';
        $newOrderRoute = MagentoUrlGenerator::NEW_ORDER_ROUTE;
        $checkoutRoute = MagentoUrlGenerator::CHECKOUT_ROUTE;
        $origin = 'customer';
        $exception = new ExtensionRequiredException;

        return [
            [144, $successRoute, $errorRoute, $adminUrl,  $successUrl, $errorUrl, $flowName, $newOrderRoute, $origin],
            [356, $successRoute, $errorRoute, $exception, $successUrl, $errorUrl, $flowName, $checkoutRoute, $origin],
            [543, $exception,    $errorRoute, $adminUrl,  $successUrl, $errorUrl, $flowName, $newOrderRoute, $origin],
            [632, $successRoute, $exception,  $adminUrl,  $successUrl, $errorUrl, $flowName, $newOrderRoute, $origin],
        ];
    }
}
