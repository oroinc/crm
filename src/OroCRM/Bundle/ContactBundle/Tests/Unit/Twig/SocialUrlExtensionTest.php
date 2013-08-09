<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\Twig;

use OroCRM\Bundle\ContactBundle\Twig\SocialUrlExtension;
use OroCRM\Bundle\ContactBundle\Model\Social;

class SocialUrlExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SocialUrlExtension
     */
    protected $twigExtension;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlFormatter;

    protected function setUp()
    {
        $this->urlFormatter = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Formatter\SocialUrlFormatter')
            ->setMethods(array('getSocialUrl'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->twigExtension = new SocialUrlExtension($this->urlFormatter);
    }

    protected function tearDown()
    {
        unset($this->urlFormatter);
        unset($this->twigExtension);
    }

    public function testGetFunctions()
    {
        $expectedFunctions = array(
            'oro_social_url' => 'getSocialUrl',
        );

        $actualFunctions = array();
        /** @var \Twig_SimpleFunction $function */
        foreach ($this->twigExtension->getFunctions() as $function) {
            $this->assertInstanceOf('\Twig_SimpleFunction', $function);
            $callable = $function->getCallable();
            $this->assertArrayHasKey(1, $callable);
            $actualFunctions[$function->getName()] = $callable[1];
        }

        $this->assertEquals($expectedFunctions, $actualFunctions);
    }

    /**
     * @param string $expectedUrl
     * @param string $socialType
     * @param string $username
     * @dataProvider socialUrlDataProvider
     */
    public function testGetSocialUrl($expectedUrl, $socialType, $username)
    {
        if ($socialType && $username) {
            $this->urlFormatter->expects($this->once())
                ->method('getSocialUrl')
                ->with($socialType, $username)
                ->will(
                    $this->returnCallback(
                        function ($socialType, $username) {
                            return 'http://' . $socialType . '/' . $username;
                        }
                    )
                );
        } else {
            $this->urlFormatter->expects($this->never())
                ->method('getSocialUrl');
        }

        $this->assertEquals($expectedUrl, $this->twigExtension->getSocialUrl($socialType, $username));
    }

    /**
     * @return array
     */
    public function socialUrlDataProvider()
    {
        return array(
            'no type' => array(
                'expectedUrl' => '#',
                'socialType'  => null,
                'username'    => 'me',
            ),
            'no username' => array(
                'expectedUrl' => '#',
                'socialType'  => Social::TWITTER,
                'username'    => null,
            ),
            'valid data' => array(
                'expectedUrl' => 'http://' . Social::TWITTER . '/me',
                'socialType'  => Social::TWITTER,
                'username'    => 'me',
            ),
        );
    }
}
