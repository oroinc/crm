<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Twig;

use Oro\Bundle\ContactBundle\Formatter\SocialUrlFormatter;
use Oro\Bundle\ContactBundle\Model\Social;
use Oro\Bundle\ContactBundle\Twig\ContactExtension;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;

class ContactExtensionTest extends \PHPUnit\Framework\TestCase
{
    use TwigExtensionTestCaseTrait;

    /** @var ContactExtension */
    protected $extension;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $urlFormatter;

    protected function setUp(): void
    {
        $this->urlFormatter = $this->getMockBuilder(SocialUrlFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = self::getContainerBuilder()
            ->add('oro_contact.social_url_formatter', $this->urlFormatter)
            ->getContainer($this);

        $this->extension = new ContactExtension($container);
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

        $this->assertEquals(
            $expectedUrl,
            self::callTwigFunction($this->extension, 'oro_social_url', [$socialType, $username])
        );
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
