<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Service\Provider;

use OroCRM\Bundle\MagentoBundle\Service\MagentoUrlGenerator;
use OroCRM\Bundle\MagentoBundle\Exception\ExtensionRequiredException;

class MagentoUrlGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var MagentoUrlGenerator
     */
    private $urlGenerator;

    /**
     * @var \Oro\Bundle\IntegrationBundle\Entity\Channel
     */
    private $channel;

    /**
     * @var \OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport
     */
    private $transport;

    public function setUp()
    {
        $this->router = $this->getMockBuilder('Symfony\Component\Routing\Router')
                        ->disableOriginalConstructor()
                        ->getMock();

        $this->urlGenerator = new MagentoUrlGenerator($this->router);

        $this->channel = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Channel')
            ->disableOriginalConstructor()
            ->getMock();

        $this->transport = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport')
            ->disableOriginalConstructor()
            ->getMock();

    }

    public function tearDown()
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
        $this->assertFalse($this->urlGenerator->getChannel());
        $this->assertFalse($this->urlGenerator->getError());
        $this->assertFalse($this->urlGenerator->getFlowName());
        $this->assertFalse($this->urlGenerator->getSourceUrl());
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
        $origin = 'customer';

        return [
            'channel'  => ['channel', $this->channel, $this->channel],
            'error'    => ['error', $error, $error],
            'flowName' => ['flowName', $flowName, $flowName],
            'origin'   => ['origin', $origin, $origin],
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
            ->expects($this->once())
            ->method('getAdminUrl')
            ->will(
                $this->returnValue($url)
            );

        $this->channel
            ->expects($this->once())
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
        $origin
    ) {
        $result = $adminUrl .
                  '/oro_gateway/do?'.$origin.'=' .
                  $id .
                  '&route=oro_sales/newOrder' .
                  '&workflow=' . $flowName .
                  '&success_url=' . urlencode($successUrl) .
                  '&error_url=' . urlencode($errorUrl);

        $this->transport->expects($this->once())->method('getAdminUrl')->will($this->returnValue($adminUrl));
        $this->channel->expects($this->once())->method('getTransport')->will($this->returnValue($this->transport));

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
        $urlGenerator->setorigin($origin);
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
        $origin = 'cusomer';
        $Exception = new ExtensionRequiredException;

        return [
            [144, $successRoute, $errorRoute, $adminUrl,  $successUrl, $errorUrl, $flowName, $origin],
            [356, $successRoute, $errorRoute, $Exception, $successUrl, $errorUrl, $flowName, $origin],
            [543, $Exception,    $errorRoute, $adminUrl,  $successUrl, $errorUrl, $flowName, $origin],
            [632, $successRoute, $Exception,  $adminUrl,  $successUrl, $errorUrl, $flowName, $origin],
        ];
    }
}
